/**
 * ==============================================================================
 * NNACO_SA CLIENT-SIDE THEME MANAGER
 * ==============================================================================
 * Real-time Theme Controller supporting Light, Dark, and System Mode.
 * Features:
 * - Zero-flicker pre-render synchronization
 * - Dynamic OS prefers-color-scheme listener
 * - LocalStorage persistence + Server Session sync for logged-in users
 * - Instant UI icon & state updates without full page reloads
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'nnaco_theme_preference';
    const VALID_THEMES = ['light', 'dark', 'system'];

    const ThemeManager = {
        mediaQuery: window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null,
        
        /**
         * Get current saved preference ('light', 'dark', or 'system')
         */
        getPreference: function() {
            try {
                const stored = localStorage.getItem(STORAGE_KEY);
                if (stored && VALID_THEMES.includes(stored)) {
                    return stored;
                }
            } catch (e) {}

            // Fallback to server preference if passed via window
            if (window.NNACO_USER_THEME && VALID_THEMES.includes(window.NNACO_USER_THEME)) {
                return window.NNACO_USER_THEME;
            }

            return 'system';
        },

        /**
         * Resolve the active visual theme ('light' or 'dark') based on preference and OS
         */
        getResolvedTheme: function(preference) {
            const pref = preference || this.getPreference();
            if (pref === 'system') {
                return (this.mediaQuery && this.mediaQuery.matches) ? 'dark' : 'light';
            }
            return pref;
        },

        /**
         * Set and apply theme
         * @param {string} preference - 'light' | 'dark' | 'system'
         * @param {boolean} persistServer - Whether to sync with database for logged-in user
         */
        setTheme: function(preference, persistServer = true) {
            if (!VALID_THEMES.includes(preference)) {
                preference = 'system';
            }

            const resolved = this.getResolvedTheme(preference);

            // Add transition class for smooth visual fade
            if (document.body) {
                document.body.classList.add('theme-transition-active');
            }

            // Apply attributes
            document.documentElement.setAttribute('data-theme', resolved);
            document.documentElement.setAttribute('data-theme-preference', preference);
            if (document.body) {
                document.body.setAttribute('data-theme', resolved);
            }

            // Save to localStorage
            try {
                localStorage.setItem(STORAGE_KEY, preference);
            } catch (e) {}

            // Update UI elements
            this.updateUiElements(preference, resolved);

            // Clean up transition class after animation completes
            setTimeout(function() {
                if (document.body) {
                    document.body.classList.remove('theme-transition-active');
                }
            }, 300);

            // Sync with backend if user is logged in
            if (persistServer && window.NNACO_IS_LOGGED_IN && window.NNACO_BASEURL) {
                this.syncWithServer(preference);
            }

            // Dispatch global event for widgets & charts
            window.dispatchEvent(new CustomEvent('nnaco:themechange', {
                detail: {
                    preference: preference,
                    resolved: resolved
                }
            }));
        },

        /**
         * Sync preference to server without page reload
         */
        syncWithServer: function(preference) {
            try {
                fetch(window.NNACO_BASEURL + '/profile/updateTheme', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ theme: preference })
                }).catch(function(err) {
                    console.warn('[ThemeManager] Server sync warning:', err);
                });
            } catch (e) {}
        },

        /**
         * Update UI buttons, dropdown items, icons, and cards
         */
        updateUiElements: function(preference, resolved) {
            // 1. Update toggle button icons
            const toggleButtons = document.querySelectorAll('.theme-selector-btn, [data-theme-toggle-btn]');
            toggleButtons.forEach(function(btn) {
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = '';
                    if (preference === 'light') {
                        icon.className = 'bi bi-sun-fill text-warning';
                    } else if (preference === 'dark') {
                        icon.className = 'bi bi-moon-stars-fill text-primary';
                    } else {
                        icon.className = 'bi bi-display text-info';
                    }
                }

                // Update accessible title
                const label = preference === 'light' ? 'Mode Terang' : (preference === 'dark' ? 'Mode Gelap' : 'Ikuti Sistem');
                btn.setAttribute('title', 'Tema Tampilan: ' + label);
                btn.setAttribute('aria-label', 'Tema Tampilan: ' + label);
            });

            // 2. Update dropdown option items
            const optionItems = document.querySelectorAll('.theme-option-item, [data-theme-value]');
            optionItems.forEach(function(item) {
                const val = item.getAttribute('data-theme-value');
                if (val === preference) {
                    item.classList.add('active');
                    item.setAttribute('aria-selected', 'true');
                } else {
                    item.classList.remove('active');
                    item.setAttribute('aria-selected', 'false');
                }
            });

            // 3. Update theme cards (in Profile & Settings page)
            const cards = document.querySelectorAll('.theme-card-option');
            cards.forEach(function(card) {
                const val = card.getAttribute('data-theme-value');
                const radio = card.querySelector('input[type="radio"]');
                if (val === preference) {
                    card.classList.add('active');
                    if (radio) radio.checked = true;
                } else {
                    card.classList.remove('active');
                    if (radio) radio.checked = false;
                }
            });

            // 4. Update any text indicators
            const labelSpans = document.querySelectorAll('.theme-current-label');
            labelSpans.forEach(function(span) {
                if (preference === 'light') span.textContent = 'Terang';
                else if (preference === 'dark') span.textContent = 'Gelap';
                else span.textContent = 'Ikuti Sistem';
            });
        },

        /**
         * Initialize event listeners
         */
        init: function() {
            const self = this;
            const currentPref = this.getPreference();

            // Apply immediately
            this.setTheme(currentPref, false);

            // Listen to OS color scheme changes (prefers-color-scheme)
            if (this.mediaQuery) {
                const handler = function(e) {
                    if (self.getPreference() === 'system') {
                        self.setTheme('system', false);
                    }
                };

                if (this.mediaQuery.addEventListener) {
                    this.mediaQuery.addEventListener('change', handler);
                } else if (this.mediaQuery.addListener) {
                    this.mediaQuery.addListener(handler);
                }
            }

            // Bind click handlers for any theme options
            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('[data-set-theme]');
                if (trigger) {
                    e.preventDefault();
                    const newTheme = trigger.getAttribute('data-set-theme');
                    self.setTheme(newTheme, true);
                }
            });

            // Refresh UI on DOM ready
            document.addEventListener('DOMContentLoaded', function() {
                self.updateUiElements(self.getPreference(), self.getResolvedTheme());
            });
        }
    };

    // Expose globally
    window.ThemeManager = ThemeManager;

    // Run initialization
    ThemeManager.init();
})();
