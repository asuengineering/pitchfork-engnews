/**
 * isotope-init.js (drop-in replacement)
 * Initializes Isotope on .news-feed containers and manages filter UI:
 * - Only one select filter active at a time
 * - Programmatic month-empty selection (disabled placeholder) when other filters active
 * - Reset button restores defaults
 *
 * Note: This file intentionally avoids dispatching synthetic 'change' events
 *       to prevent recursive onSelectChange calls. Instead it sets values
 *       programmatically and uses a guard to ignore programmatic updates.
 */

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
	// Month select rules:
	//  - '.latest' => enforce .latest (default)
	//  - '' (empty) => do NOT enforce .latest (search across all posts)
	//  - '.month-YYYY-MM' => use that month selector
	// ---------------------------------------------------------------------
	function buildFilterString(selects) {
		var pieces = [];
		var monthSelect = null;

		selects.forEach(function (sel) {
			if (!sel) return;
			// identify month select by id or name
			if (sel.id === 'filter-month' || sel.name === 'filter-month') {
				monthSelect = sel;
				return; // handle month explicitly below
			}
			var val = (sel.value || '').trim();
			if (!val) return;
			pieces.push(val);
		});

		// Handle month select explicitly
		if (monthSelect) {
			var mv = (monthSelect.value || '').trim();
			if (mv === '.latest' || mv === '') {
				// if mv === '' -> search ALL posts (do NOT push .latest)
				// if mv === '.latest' -> enforce latest
				if (mv === '.latest') {
					pieces.push('.latest');
				}
				// if mv === '' do nothing (no .latest)
			} else {
				// month option chosen: use that value (e.g. '.month-2025-12')
				pieces.push(mv);
			}
		} else {
			// No month control present: default to latest to be safe
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
			filter: '.latest'
		});

		// after iso is created
		function doLayout() {
			try { iso.layout(); }
			catch (e) { console.warn('isotope layout error', e); }
			// small double-check after a tick
			setTimeout(function () { try { iso.layout(); } catch (e) { } }, 200);
		}

		// imagesLoaded + fonts handling
		if (typeof imagesLoaded !== 'undefined') {
			imagesLoaded(container, function () {
				if (document.fonts && document.fonts.ready) {
					document.fonts.ready.then(doLayout).catch(doLayout);
				} else {
					doLayout();
				}
			});
		} else {
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

		// capture each select's initial default value (so we can restore it later)
		var defaultValues = selects.map(function (s) { return s.value; });

		// programmatic-change guard to avoid reacting to programmatic updates
		var programmaticChange = false;

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

		// Helper to get index of a select
		function indexOfSelect(s) {
			for (var i = 0; i < selects.length; i++) {
				if (selects[i] === s) return i;
			}
			return -1;
		}

		// Programmatically set month select to the disabled "empty" placeholder.
		// NOTE: do NOT dispatch a synthetic 'change' event here to avoid recursion.
		function setMonthToEmpty() {
			// Find the month select in the selects array
			var month = selects.find(function (s) {
				return s && (s.id === 'filter-month' || s.name === 'filter-month');
			});
			if (!month) return;

			// Try to find an option with value = ""
			var opt = month.querySelector('option[value=""]');
			programmaticChange = true; // suppress onSelectChange while we update
			try {
				if (opt) {
					// Programmatically select it; browsers typically allow setting this value
					opt.selected = true;
					month.value = '';
				} else {
					// Fallback: create a temporary selected option (hidden) and then remove it later.
					var tmp = document.createElement('option');
					tmp.value = '';
					tmp.textContent = '-- select a month --';
					tmp.selected = true;
					tmp.hidden = true;
					month.appendChild(tmp);
					month.value = '';
					// remove after next tick to avoid residual DOM element
					setTimeout(function () {
						try { month.removeChild(tmp); } catch (e) { }
					}, 50);
				}
			} catch (e) {
				console.warn('isotope-init: fallback month selection failed', e);
			} finally {
				// small timeout ensures any browser internals settle before we re-enable listening
				setTimeout(function () { programmaticChange = false; }, 0);
			}
		}

		// Apply "only-one-active" policy:
		// - when a select changes to a non-default value -> set others to default & disable them
		// - when the active select is returned to its default -> re-enable all
		function onSelectChange(ev) {
			if (programmaticChange) {
				// ignore programmatic updates
				return;
			}

			var sel = this;
			var selIndex = indexOfSelect(sel);
			var selDefault = defaultValues[selIndex];
			var selIsDefault = ((sel.value || '') === (selDefault || ''));

			if (!selIsDefault) {
				// Activate this select: reset and disable others
				selects.forEach(function (otherSel, i) {
					if (otherSel === sel) {
						// leave it alone (active)
						otherSel.disabled = false;
						var parent = otherSel.closest('.form-group');
						if (parent) parent.classList.remove('disabled');
						return;
					}

					// reset other selects to their default values, disable them, mark visually
					otherSel.value = defaultValues[i] || '';
					otherSel.disabled = true;
					var p = otherSel.closest('.form-group');
					if (p) p.classList.add('disabled');
				});
			} else {
				// sel has been returned to default -> enable all selects and remove disabled class
				selects.forEach(function (otherSel, i) {
					otherSel.disabled = false;
					var p = otherSel.closest('.form-group');
					if (p) p.classList.remove('disabled');
					otherSel.value = defaultValues[i] || '';
				});
			}

			// if the changed select is NOT the month select, force the month select to "empty"
			// so filters operate across ALL posts (not just .latest)
			if (sel.id !== 'filter-month' && sel.name !== 'filter-month') {
				setMonthToEmpty();
			}

			// run the filter update
			applyFilters();
		}

		// Bind change + enter handlers to each select (use the onSelectChange wrapper)
		selects.forEach(function (s) {
			s.addEventListener('change', onSelectChange);
			s.addEventListener('keydown', function (ev) {
				if (ev.key === 'Enter') {
					// apply immediately (mirrors change behavior)
					onSelectChange.call(s, ev);
				}
			});
		});

		// Reset button behavior: restore defaults and re-enable all selects
		var resetBtn = blockRoot.querySelector('#filter-reset');
		if (resetBtn) {
			resetBtn.addEventListener('click', function (ev) {
				ev.preventDefault();
				// restore default values and enable all selects
				programmaticChange = true;
				selects.forEach(function (s, i) {
					s.value = defaultValues[i] || '';
					s.disabled = false;
					var p = s.closest('.form-group');
					if (p) p.classList.remove('disabled');
				});
				// small timeout to re-enable listening
				setTimeout(function () { programmaticChange = false; }, 0);
				// reapply filters (this will pick up default state e.g. '.latest')
				applyFilters();
			});
		}

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
