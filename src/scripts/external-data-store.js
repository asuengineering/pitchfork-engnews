/**
 * Data store JS file for interactivity API and /external post loop
*/

import { store } from '@wordpress/interactivity';

store('externalFilters', {
	state: {
		search: new URLSearchParams(location.search).get('s') || '',
		publication: new URLSearchParams(location.search).get('publication') || '',
		asuPerson: new URLSearchParams(location.search).get('asu_person') || '',
	},
	actions: {
		setSearch({ event, state }) {
			state.search = event.target.value;
		},
		setPublication({ event, state }) {
			state.publication = event.target.value;
		},
		setAsuPerson({ event, state }) {
			state.asuPerson = event.target.value;
		},
		applyFilters({ state }) {
			const params = new URLSearchParams();
			if (state.search) params.set('s', state.search);
			if (state.publication) params.set('publication', state.publication);
			if (state.asuPerson) params.set('asu_person', state.asuPerson);
			window.location.search = params.toString(); // triggers reload with new query args
		}
	}
});
