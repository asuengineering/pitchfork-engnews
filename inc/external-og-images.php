<?php
/**
 * Engineering News - External News featured image scripts.
 *
 * @package pitchfork_engnews
 */

add_action( 'acf/save_post', 'itn_cache_og_image_from_mainurl', 20 );
function itn_cache_og_image_from_mainurl( $post_id ) {
    // Ignore if not a real post
    if ( strpos( $post_id, 'post' ) === false && ! is_numeric( $post_id ) ) {
        return;
    }

    // Get the main URL field, make sure that the URL is valid.
    $main_url = get_field( '_itn_mainurl', $post_id );
    if ( empty( $main_url ) || ! filter_var( $main_url, FILTER_VALIDATE_URL ) ) {
        return;
    }

    // Fetch remote HTML
    $response = wp_remote_get( $main_url, [ 'timeout' => 10 ] );
    if ( is_wp_error( $response ) ) {
        return;
    }

    $html = wp_remote_retrieve_body( $response );
    if ( empty( $html ) ) {
        return;
    }

    // HTML Tag processer usage for parsing the returned results.
    $image_url = null;
    $alt_text  = null;

    $tag_processor = new WP_HTML_Tag_Processor( $html );

    while ( $tag_processor->next_tag( array( 'tag_name' => 'meta' ) ) ) {
        $property = $tag_processor->get_attribute( 'property' );
        $name     = $tag_processor->get_attribute( 'name' );
        $content  = $tag_processor->get_attribute( 'content' );

        // Image URL candidates
        if ( ! $image_url && (
            $property === 'og:image' ||
            $property === 'og:image:secure_url' ||
            $name === 'twitter:image'
        ) ) {
            if ( filter_var( $content, FILTER_VALIDATE_URL ) ) {
                $image_url = $content;
            }
        }

        // Twitter alt text
        if ( ! $alt_text && $name === 'twitter:image:alt' ) {
            $alt_text = sanitize_text_field( $content );
        }

        // Fallback alt from OG title
        if ( ! $alt_text && $property === 'og:title' ) {
            $alt_text = sanitize_text_field( $content );
        }

        // Exit early if we have both values
        if ( $image_url && $alt_text ) {
            break;
        }
    }

    /**
     * If we have an OG image URL but no alt_text, try to find an <img> in the page
     * with the same filename/path (or a matching srcset candidate) and use its alt.
     */
    if ( $image_url && empty( $alt_text ) ) {

        /**
         * Resolve possibly-relative src against the page URL.
         * Handles:
         *  - absolute URLs (return as-is)
         *  - protocol-relative //example.com/foo.jpg
         *  - root-relative /foo.jpg
         *  - relative path foo.jpg or images/foo.jpg
         */
        $resolve_url = function( $src, $base_url ) {
            if ( empty( $src ) ) {
                return '';
            }

            // already absolute
            if ( filter_var( $src, FILTER_VALIDATE_URL ) ) {
                return $src;
            }

            // protocol-relative
            if ( strpos( $src, '//' ) === 0 ) {
                $scheme = parse_url( $base_url, PHP_URL_SCHEME ) ?: 'https';
                return $scheme . ':' . $src;
            }

            // root-relative or relative path
            $base_parts = wp_parse_url( $base_url );
            if ( ! isset( $base_parts['scheme'] ) || ! isset( $base_parts['host'] ) ) {
                return $src; // give up, return as-is
            }

            $scheme = $base_parts['scheme'];
            $host   = $base_parts['host'];
            $port   = isset( $base_parts['port'] ) ? ':' . $base_parts['port'] : '';
            $base   = $scheme . '://' . $host . $port;

            // root relative
            if ( strpos( $src, '/' ) === 0 ) {
                return $base . $src;
            }

            // relative: build using base path of $base_url
            $path = isset( $base_parts['path'] ) ? $base_parts['path'] : '/';
            // remove filename from path if present
            if ( substr( $path, -1 ) !== '/' ) {
                $path = dirname( $path ) . '/';
            }
            return $base . rtrim( $path, '/' ) . '/' . ltrim( $src, '/' );
        };

        /**
         * Helper to pull a single candidate url from a srcset string
         * and resolve it to a full url.
         */
        $srcset_to_urls = function( $srcset ) use ( $resolve_url, $main_url ) {
            $urls = [];
            if ( empty( $srcset ) ) {
                return $urls;
            }
            // srcset is comma-separated: "image-320w.jpg 320w, image-640w.jpg 640w"
            $candidates = explode( ',', $srcset );
            foreach ( $candidates as $cand ) {
                $parts = preg_split( '/\s+/', trim( $cand ) );
                if ( ! empty( $parts[0] ) ) {
                    $urls[] = $resolve_url( $parts[0], $main_url );
                }
            }
            return $urls;
        };

        /**
         * Basic url comparison:
         * prefer exact path match (host+path), otherwise match by basename.
         * Returns true if urls look like they point to the same file.
         */
        $urls_match_file = function( $a, $b ) {
            if ( empty( $a ) || empty( $b ) ) {
                return false;
            }
            $a_parts = parse_url( $a );
            $b_parts = parse_url( $b );

            // if both have host and path, compare host+path strictly (after decoding)
            if ( isset( $a_parts['host'] ) && isset( $a_parts['path'] ) && isset( $b_parts['host'] ) && isset( $b_parts['path'] ) ) {
                $a_host_path = strtolower( $a_parts['host'] ) . rawurldecode( $a_parts['path'] );
                $b_host_path = strtolower( $b_parts['host'] ) . rawurldecode( $b_parts['path'] );
                if ( $a_host_path === $b_host_path ) {
                    return true;
                }
            }

            // fallback: compare basenames
            $a_base = basename( isset( $a_parts['path'] ) ? $a_parts['path'] : $a );
            $b_base = basename( isset( $b_parts['path'] ) ? $b_parts['path'] : $b );
            if ( ! empty( $a_base ) && ! empty( $b_base ) && strcasecmp( $a_base, $b_base ) === 0 ) {
                return true;
            }

            return false;
        };

        // Walk <img> tags searching for matching src or srcset candidates.
        $img_proc = new WP_HTML_Tag_Processor( $html );
        while ( $img_proc->next_tag( array( 'tag_name' => 'img' ) ) ) {
            $src    = $img_proc->get_attribute( 'src' );
            $srcset = $img_proc->get_attribute( 'srcset' );
            $alt    = $img_proc->get_attribute( 'alt' );

            // resolve src and srcset entries to absolute urls when possible
            $resolved_src = $resolve_url( $src, $main_url );

            // check src first
            if ( $resolved_src && $urls_match_file( $resolved_src, $image_url ) ) {
                if ( ! empty( $alt ) ) {
                    $alt_text = sanitize_text_field( $alt );
                    break;
                }
            }

            // check any srcset candidates
            if ( $srcset ) {
                $candidates = $srcset_to_urls( $srcset );
                foreach ( $candidates as $candidate_url ) {
                    if ( $urls_match_file( $candidate_url, $image_url ) ) {
                        if ( ! empty( $alt ) ) {
                            $alt_text = sanitize_text_field( $alt );
                            break 2; // break both loops
                        }
                    }
                }
            }
        }
    }

    // Validate the image URL with a HEAD request
    if ( $image_url ) {
        $head_response = wp_remote_head( $image_url );
        $status_code   = wp_remote_retrieve_response_code( $head_response );

        if ( $status_code === 200 ) {
            update_post_meta( $post_id, '_itn_og_image_url', esc_url_raw( $image_url ) );

            if ( $alt_text ) {
                update_post_meta( $post_id, '_itn_og_image_alt', $alt_text );
            }
            return;
        }
    }

    // If no valid image found or it's unreachable, remove any previous one
    delete_post_meta( $post_id, '_itn_og_image_url' );
    delete_post_meta( $post_id, '_itn_og_image_alt' );
}

