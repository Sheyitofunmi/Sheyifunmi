/**
 * Portfolio Dark/Light Mode Toggle
 * Persists user preference in a cookie and localStorage.
 */
(function() {
	'use strict';

	var COOKIE_NAME = 'portfolio_theme';
	var STORAGE_KEY = 'portfolio_theme';

	function getPreference() {
		// Check localStorage first, then cookie
		var stored = localStorage.getItem(STORAGE_KEY);
		if (stored) return stored;

		var cookies = document.cookie.split(';');
		for (var i = 0; i < cookies.length; i++) {
			var cookie = cookies[i].trim();
			if (cookie.indexOf(COOKIE_NAME + '=') === 0) {
				return cookie.substring(COOKIE_NAME.length + 1);
			}
		}
		return null;
	}

	function setPreference(theme) {
		localStorage.setItem(STORAGE_KEY, theme);
		document.cookie = COOKIE_NAME + '=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
	}

	function applyTheme(theme) {
		document.documentElement.setAttribute('data-theme', theme);
	}

	function init() {
		var preference = getPreference();
		if (preference) {
			applyTheme(preference);
		}

		// Create toggle button
		var toggle = document.createElement('button');
		toggle.className = 'theme-toggle';
		toggle.setAttribute('aria-label', 'Toggle dark/light mode');
		toggle.innerHTML =
			'<svg class="theme-toggle__icon--sun" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
				'<circle cx="12" cy="12" r="5"/>' +
				'<line x1="12" y1="1" x2="12" y2="3"/>' +
				'<line x1="12" y1="21" x2="12" y2="23"/>' +
				'<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>' +
				'<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>' +
				'<line x1="1" y1="12" x2="3" y2="12"/>' +
				'<line x1="21" y1="12" x2="23" y2="12"/>' +
				'<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>' +
				'<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>' +
			'</svg>' +
			'<svg class="theme-toggle__icon--moon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
				'<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>' +
			'</svg>';

		toggle.addEventListener('click', function() {
			var current = document.documentElement.getAttribute('data-theme');
			var next;
			if (current === 'dark') {
				next = 'light';
			} else if (current === 'light') {
				next = 'dark';
			} else {
				// No explicit theme set — check system preference
				next = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'light' : 'dark';
			}
			applyTheme(next);
			setPreference(next);
		});

		document.body.appendChild(toggle);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
