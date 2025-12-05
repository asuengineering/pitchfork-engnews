/**
 * Init isotope, conditionally loaded when page slug is "in-the-media"
*/

// isotope-init.js (old-school DOMContentLoaded wrapper)
document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	// ---------------------------------------------------------------------
	// Small debounce helper
	// ---------------------------------------------------------------------
	function debounce(fn, wait) {
		var t;
		return function () {
			var ctx = this, args = arguments;
			clearTimeout(t);
			t = setTimeout(function () {
				fn.apply(ctx, args);
			}, wait);
		};
	}

	// ---------------------------------------------------------------------
	// Build combined filter string from all selects (AND behavior)
	// ---------------------------------------------------------------------
	function buildFilterString(selects) {
		var pieces = [];

		// Track whether a month selector was present and whether it had a value
		var monthPresent = false;
		var monthHasValue = false;

		selects.forEach(function (sel) {
			var val = (sel.value || '').trim();

			// If this is the month select, note presence
			if (sel.id === 'filter-month' || sel.name === 'filter-month') {
				monthPresent = true;
				if (val) {
					monthHasValue = true;
				}
			}

			if (!val) return;
			pieces.push(val);
		});

		// If month select exists but had no chosen value, enforce latest
		if (monthPresent && !monthHasValue) {
			pieces.push('.latest');
		}

		if (pieces.length === 0) return '*';
		return pieces.join('');
	}

	// ---------------------------------------------------------------------
	// Initialize one Isotope container
	// ---------------------------------------------------------------------
	function initContainer(container) {
		if (!container) return;

		console.info('isotope-init: initializing container', container);

		var itemSelector = '.news-post'; // each post
		var sizer = container.querySelector('.grid-sizer');
		var columnWidth = sizer ? '.grid-sizer' : undefined;

		// instantiate Isotope, start filtered to .latest
		var iso = new Isotope(container, {
			itemSelector: itemSelector,
			layoutMode: 'fitRows',
			percentPosition: true,
			columnWidth: columnWidth,
			filter: '.latest',
		});

		// after iso is created
		function doLayout() {
			try { iso.layout(); }
			catch (e) { console.warn('isotope layout error', e); }
			// small double-check after a tick
			setTimeout(function () { try { iso.layout(); } catch (e) { } }, 200);
		}

		// if imagesLoaded is available, prefer it
		if (typeof imagesLoaded !== 'undefined') {
			imagesLoaded(container, function () {
				// wait for fonts too (if browser supports document.fonts)
				if (document.fonts && document.fonts.ready) {
					document.fonts.ready.then(doLayout).catch(doLayout);
				} else {
					doLayout();
				}
			});
		} else {
			// fallback: wait for window load and fonts
			window.addEventListener('load', function () {
				if (document.fonts && document.fonts.ready) {
					document.fonts.ready.then(doLayout).catch(doLayout);
				} else {
					doLayout();
				}
			});
		}

		// -------------------------------------------------------------
		// Find filter controls (select elements)
		// -------------------------------------------------------------
		var blockRoot = container.closest('.wp-block-external-news') || document;
		var selects = Array.prototype.slice.call(
			blockRoot.querySelectorAll('select.filter')
		);

		if (!selects.length) {
			selects = Array.prototype.slice.call(
				document.querySelectorAll('select.filter')
			);
		}

		// Change handler
		var applyFilters = debounce(function () {
			var filterString = buildFilterString(selects);
			console.info('isotope-init: applying filter ->', filterString);
			try {
				iso.arrange({ filter: filterString });
			} catch (err) {
				console.error('isotope-init: error while applying filter', err);
			}
		}, 120);

		// Bind listeners
		selects.forEach(function (sel) {
			sel.addEventListener('change', applyFilters);

			// Optional: press Enter to apply while focused
			sel.addEventListener('keydown', function (ev) {
				if (ev.key === 'Enter') {
					applyFilters();
				}
			});
		});

		// Expose for debugging
		container._iso = iso;
	}

	// ---------------------------------------------------------------------
	// Main initAll() logic
	// ---------------------------------------------------------------------
	function initAll() {
		if (typeof Isotope === 'undefined') {
			console.error('isotope-init: Isotope library not loaded.');
			return;
		}

		var containers = Array.prototype.slice.call(
			document.querySelectorAll('.news-feed')
		);

		if (!containers.length) {
			console.warn('isotope-init: No .news-feed containers found.');
			return;
		}

		containers.forEach(initContainer);

		console.info('isotope-init: Init complete for', containers.length, 'container(s)');
	}

	// ---------------------------------------------------------------------
	// Kick it off
	// ---------------------------------------------------------------------
	initAll();
});
