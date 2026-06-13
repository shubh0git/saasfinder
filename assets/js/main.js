/**
 * SaasFinder — Main JS (vanilla, no jQuery)
 *
 * Handles:
 * - AJAX archive filtering
 * - Search autocomplete
 * - Countdown timers (deal pages)
 * - Sticky CTA visibility (mobile)
 * - Affiliate click tracking (custom events for GTM)
 * - Mobile nav toggle
 *
 * @package SaasFinder
 */

(function() {
    'use strict';

    // =========================================================================
    // AJAX Archive Filtering
    // =========================================================================
    const filterContainer = document.getElementById('review-filters');
    const resultsContainer = document.getElementById('review-results');

    if (filterContainer && resultsContainer) {
        const selects = filterContainer.querySelectorAll('.archive-filters__select');
        let currentPage = 1;

        selects.forEach(function(select) {
            select.addEventListener('change', function() {
                currentPage = 1;
                fetchFilteredReviews();
            });
        });

        function fetchFilteredReviews() {
            const formData = new FormData();
            formData.append('action', 'filter_reviews');
            formData.append('nonce', saasfinder.nonce);
            formData.append('page', currentPage);

            selects.forEach(function(select) {
                const key = select.getAttribute('data-filter');
                if (select.value) {
                    formData.append(key, select.value);
                }
            });

            resultsContainer.style.opacity = '0.5';

            fetch(saasfinder.ajax_url, {
                method: 'POST',
                body: formData,
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    resultsContainer.innerHTML = data.data.html;
                }
                resultsContainer.style.opacity = '1';
            })
            .catch(function() {
                resultsContainer.style.opacity = '1';
            });
        }
    }

    // =========================================================================
    // Countdown Timer (Deal pages)
    // =========================================================================
    const countdownElements = document.querySelectorAll('[data-countdown]');

    countdownElements.forEach(function(el) {
        const targetDate = new Date(el.getAttribute('data-countdown')).getTime();
        const timerEl = el.querySelector('.countdown-timer');

        if (!timerEl || isNaN(targetDate)) return;

        function updateCountdown() {
            const now = Date.now();
            const diff = targetDate - now;

            if (diff <= 0) {
                timerEl.textContent = 'Expired';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            let text = '';
            if (days > 0) text += days + 'd ';
            text += hours.toString().padStart(2, '0') + ':';
            text += minutes.toString().padStart(2, '0') + ':';
            text += seconds.toString().padStart(2, '0');

            timerEl.textContent = text;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    });

    // =========================================================================
    // Affiliate Click Tracking (fires custom events for GTM)
    // =========================================================================
    document.addEventListener('click', function(e) {
        const link = e.target.closest('[data-track]');
        if (!link) return;

        const eventData = {
            event: 'affiliate_click',
            click_type: link.getAttribute('data-track'),
            tool_name: link.getAttribute('data-tool') || '',
            destination: link.href || '',
            page_url: window.location.href,
        };

        // Push to dataLayer for GTM
        if (window.dataLayer) {
            window.dataLayer.push(eventData);
        }

        // Also fire as a custom DOM event for any other listener
        document.dispatchEvent(new CustomEvent('saasfinder:affiliate_click', {
            detail: eventData,
        }));
    });

    // =========================================================================
    // Mobile Nav Toggle
    // =========================================================================
    const navToggle = document.querySelector('.site-nav__toggle');
    const navList = document.querySelector('.site-nav__list');

    if (navToggle && navList) {
        // Show toggle on mobile
        if (window.innerWidth <= 640) {
            navToggle.style.display = 'block';
        }

        navToggle.addEventListener('click', function() {
            const expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', !expanded);
            navList.style.display = expanded ? 'none' : 'flex';
            navList.style.flexDirection = 'column';
            navList.style.position = 'absolute';
            navList.style.top = '100%';
            navList.style.left = '0';
            navList.style.right = '0';
            navList.style.background = '#fff';
            navList.style.padding = '1rem';
            navList.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        });
    }

    // =========================================================================
    // Search Autocomplete (hero search bar)
    // =========================================================================
    const searchInput = document.getElementById('hero-search');

    if (searchInput) {
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                removeSearchResults();
                return;
            }

            debounceTimer = setTimeout(function() {
                const formData = new FormData();
                formData.append('action', 'saasfinder_search');
                formData.append('nonce', saasfinder.nonce);
                formData.append('query', query);

                fetch(saasfinder.ajax_url, {
                    method: 'POST',
                    body: formData,
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success && data.data.results.length > 0) {
                        showSearchResults(data.data.results);
                    } else {
                        removeSearchResults();
                    }
                });
            }, 300);
        });

        function showSearchResults(results) {
            removeSearchResults();
            const dropdown = document.createElement('div');
            dropdown.className = 'search-autocomplete';
            dropdown.style.cssText = 'position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--border-default);border-radius:0 0 8px 8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);z-index:100;';

            results.forEach(function(result) {
                const item = document.createElement('a');
                item.href = result.url;
                item.style.cssText = 'display:flex;justify-content:space-between;padding:12px 16px;text-decoration:none;color:var(--text-primary);border-bottom:1px solid var(--border-default);';
                item.innerHTML = '<span>' + result.title + '</span><span style="font-size:0.75rem;color:var(--text-secondary);">' + result.type + '</span>';
                dropdown.appendChild(item);
            });

            searchInput.parentElement.style.position = 'relative';
            searchInput.parentElement.appendChild(dropdown);
        }

        function removeSearchResults() {
            const existing = document.querySelector('.search-autocomplete');
            if (existing) existing.remove();
        }

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-bar')) {
                removeSearchResults();
            }
        });
    }

})();
