/**
 * NNACO SA - Reusable AJAX Table Filter & Live Pagination System
 * Automatically handles search inputs, filter dropdowns, date pickers, reset buttons, and pagination.
 */
(function() {
    'use strict';

    function initAjaxTableFilter() {
        const wrappers = document.querySelectorAll('.ajax-table-wrapper');
        if (!wrappers.length) return;

        wrappers.forEach(wrapper => {
            if (wrapper.dataset.ajaxInitialized) return;
            wrapper.dataset.ajaxInitialized = 'true';
            setupWrapper(wrapper);
        });
    }

    function setupWrapper(wrapper) {
        const form = wrapper.querySelector('.ajax-filter-form') || wrapper.querySelector('form');
        const contentContainer = wrapper.querySelector('.ajax-table-content') || wrapper.querySelector('.table-responsive')?.parentElement || wrapper;
        let searchTimer = null;
        let currentAbortController = null;

        // Create or get loading overlay
        let overlay = wrapper.querySelector('.ajax-loading-overlay');
        if (!overlay && contentContainer) {
            overlay = document.createElement('div');
            overlay.className = 'ajax-loading-overlay';
            overlay.innerHTML = `
                <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill shadow-sm border">
                    <div class="spinner-border spinner-border-sm text-primary" role="status" style="width: 1rem; height: 1rem;"></div>
                    <span class="text-xs fw-semibold text-dark">Memfilter data...</span>
                </div>
            `;
            if (getComputedStyle(contentContainer).position === 'static') {
                contentContainer.style.position = 'relative';
            }
            contentContainer.appendChild(overlay);
        }

        function showLoading() {
            if (overlay) overlay.classList.add('active');
            if (contentContainer) contentContainer.classList.add('is-loading');
        }

        function hideLoading() {
            if (overlay) overlay.classList.remove('active');
            if (contentContainer) contentContainer.classList.remove('is-loading');
        }

        function performFetch(url, pushUrl = true) {
            if (currentAbortController) {
                currentAbortController.abort();
            }
            currentAbortController = new AbortController();

            showLoading();

            fetch(url, {
                signal: currentAbortController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                return res.text();
            })
            .then(html => {
                hideLoading();

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Swap table content
                const newContent = doc.querySelector('.ajax-table-content') || doc.querySelector('.table-responsive');
                const oldContent = wrapper.querySelector('.ajax-table-content') || wrapper.querySelector('.table-responsive');
                if (newContent && oldContent) {
                    oldContent.innerHTML = newContent.innerHTML;
                }

                // 1b. Swap modals container if present
                const newModals = doc.querySelector('.ajax-modals-container');
                const oldModals = wrapper.querySelector('.ajax-modals-container') || document.querySelector('.ajax-modals-container');
                if (newModals && oldModals) {
                    oldModals.innerHTML = newModals.innerHTML;
                }

                // 2. Swap count badge / summary text
                const newCount = doc.querySelector('.ajax-count-badge');
                const oldCount = wrapper.querySelector('.ajax-count-badge');
                if (newCount && oldCount) {
                    oldCount.innerHTML = newCount.innerHTML;
                }

                // 3. Swap summary metrics if present (e.g. Owner Report)
                const newSummary = doc.querySelector('.ajax-summary-cards');
                const oldSummary = document.querySelector('.ajax-summary-cards');
                if (newSummary && oldSummary) {
                    oldSummary.innerHTML = newSummary.innerHTML;
                }

                // 4. Swap pagination
                const newPagination = doc.querySelector('.ajax-pagination-container');
                const oldPagination = wrapper.querySelector('.ajax-pagination-container');
                if (oldPagination) {
                    if (newPagination && newPagination.innerHTML.trim() !== '') {
                        oldPagination.innerHTML = newPagination.innerHTML;
                        oldPagination.classList.remove('d-none');
                    } else {
                        oldPagination.innerHTML = '';
                        oldPagination.classList.add('d-none');
                    }
                    bindPaginationLinks();
                }

                // 5. Update browser URL
                if (pushUrl && window.history && window.history.pushState) {
                    window.history.pushState({ ajaxUrl: url }, '', url);
                }

                // 6. Dispatch custom event for page-specific listeners (e.g. charts)
                document.dispatchEvent(new CustomEvent('ajaxTableUpdated', { detail: { doc, html, url } }));
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                hideLoading();
                console.error('AJAX Table Filter Error:', err);
            });
        }

        function buildFilterUrl(page = 1) {
            if (!form) return window.location.href;
            const action = form.getAttribute('action') || window.location.pathname;
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                const trimmed = typeof value === 'string' ? value.trim() : value;
                if (trimmed !== '') {
                    params.append(key, trimmed);
                }
            }

            if (page && page > 1) {
                params.set('page', page);
            } else {
                params.delete('page');
            }

            const qs = params.toString();
            return action + (qs ? '?' + qs : '');
        }

        // Handle live typing in search inputs (debounced)
        if (form) {
            const textInputs = form.querySelectorAll('input[type="text"], input[type="search"]');
            textInputs.forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => {
                        const url = buildFilterUrl(1);
                        performFetch(url, true);
                    }, 280);
                });
            });

            // Handle dropdown selects and date pickers
            const selectInputs = form.querySelectorAll('select, input[type="date"]');
            selectInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const url = buildFilterUrl(1);
                    performFetch(url, true);
                });
            });

            // Handle form submission (e.g. Enter key pressed)
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearTimeout(searchTimer);
                const url = buildFilterUrl(1);
                performFetch(url, true);
            });

            // Handle Reset button
            const resetBtns = form.querySelectorAll('[data-ajax-reset], .btn-reset-filter');
            resetBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    form.reset();
                    form.querySelectorAll('input[type="text"], input[type="search"]').forEach(i => i.value = '');
                    form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
                    form.querySelectorAll('input[type="date"]').forEach(d => d.value = '');
                    const action = form.getAttribute('action') || window.location.pathname;
                    performFetch(action, true);
                });
            });
        }

        // Handle Pagination clicks
        function bindPaginationLinks() {
            const pag = wrapper.querySelector('.ajax-pagination-container') || wrapper.querySelector('.pagination');
            if (!pag) return;

            const links = pag.querySelectorAll('a.btn, a.page-link');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:')) return;
                    e.preventDefault();
                    performFetch(href, true);
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            });
        }

        bindPaginationLinks();

        // Handle popstate for browser Back/Forward buttons
        window.addEventListener('popstate', function() {
            const currentUrl = window.location.href;
            performFetch(currentUrl, false);
            if (form) {
                const urlObj = new URL(window.location.href);
                form.querySelectorAll('input, select').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name && urlObj.searchParams.has(name)) {
                        field.value = urlObj.searchParams.get(name);
                    } else if (name) {
                        if (field.tagName === 'SELECT') field.selectedIndex = 0;
                        else field.value = '';
                    }
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAjaxTableFilter);
    } else {
        initAjaxTableFilter();
    }

    window.initAjaxTableFilter = initAjaxTableFilter;
})();
