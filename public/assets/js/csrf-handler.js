/**
 * CSRF Token Handler
 * Automatically refreshes CSRF tokens and handles 419 errors
 */

(function() {
    'use strict';

    // Get CSRF token from meta tag
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : null;
    }

    // Update CSRF token in all forms
    function updateCsrfTokenInForms() {
        const token = getCsrfToken();
        if (!token) return;

        // Update all hidden CSRF input fields
        const csrfInputs = document.querySelectorAll('input[name="_token"]');
        csrfInputs.forEach(input => {
            input.value = token;
        });

        // Update AJAX headers
        if (window.axios) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
        if (window.jQuery) {
            window.jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
    }

    // Refresh CSRF token from server
    function refreshCsrfToken() {
        return fetch('/refresh-csrf-token', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                // Update meta tag
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.token);
                }
                
                // Update forms
                updateCsrfTokenInForms();
                
                console.log('CSRF token refreshed successfully');
            }
        })
        .catch(error => {
            console.warn('Failed to refresh CSRF token:', error);
        });
    }

    // Handle 419 errors globally
    function handle419Error() {
        // Show user-friendly message
        const message = document.createElement('div');
        message.className = 'alert alert-warning alert-dismissible fade show';
        message.style.position = 'fixed';
        message.style.top = '20px';
        message.style.right = '20px';
        message.style.zIndex = '9999';
        message.innerHTML = `
            <strong>Session Expired!</strong> Your session has expired. Please refresh the page and try again.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(message);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (message.parentNode) {
                message.parentNode.removeChild(message);
            }
        }, 5000);
    }

    // Intercept fetch requests to handle 419 errors
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                if (response.status === 419) {
                    handle419Error();
                    // Try to refresh token and retry once
                    return refreshCsrfToken().then(() => {
                        // Update the request with new token
                        if (args[1] && args[1].headers) {
                            args[1].headers['X-CSRF-TOKEN'] = getCsrfToken();
                        }
                        return originalFetch.apply(this, args);
                    });
                }
                return response;
            });
    };

    // Intercept jQuery AJAX requests
    if (window.jQuery) {
        window.jQuery(document).ajaxError(function(event, xhr, settings) {
            if (xhr.status === 419) {
                handle419Error();
                // Try to refresh token and retry once
                refreshCsrfToken().then(() => {
                    // Retry the original request
                    window.jQuery.ajax(settings);
                });
            }
        });
    }

    // Auto-refresh CSRF token every 30 minutes
    setInterval(refreshCsrfToken, 30 * 60 * 1000);

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCsrfTokenInForms();
        
        // Refresh token on page visibility change (user comes back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshCsrfToken();
            }
        });
    });

    // Expose functions globally for manual use
    window.CSRFHandler = {
        refresh: refreshCsrfToken,
        update: updateCsrfTokenInForms,
        getToken: getCsrfToken
    };

})();
