/**
 * QLink - Main JavaScript File
 * Common functionality and utility functions
 */

// Global variables
let csrfToken = null;
let isOnline = navigator.onLine;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize application
function initializeApp() {
    // Get CSRF token from meta tag or form
    csrfToken = getCSRFToken();
    
    // Setup global event listeners
    setupGlobalEventListeners();
    
    // Initialize tooltips and popovers
    initializeBootstrapComponents();
    
    // Setup offline/online detection
    setupOnlineDetection();
    
    // Initialize notifications
    initializeNotifications();
    
    // Setup auto-refresh for CSRF tokens
    setupCSRFAutoRefresh();
}

// Get CSRF token
function getCSRFToken() {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Try to get from hidden input
    const hiddenInput = document.querySelector('input[name="csrf_token"]');
    if (hiddenInput) {
        return hiddenInput.value;
    }
    
    // Try to get from session storage
    return sessionStorage.getItem('csrf_token');
}

// Setup global event listeners
function setupGlobalEventListeners() {
    // Form submission with CSRF token
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.method.toLowerCase() === 'post') {
            addCSRFTokenToForm(form);
        }
    });
    
    // AJAX request interceptor
    setupAjaxInterceptor();
    
    // Keyboard shortcuts
    setupKeyboardShortcuts();
    
    // Auto-save forms
    setupAutoSave();
}

// Add CSRF token to form
function addCSRFTokenToForm(form) {
    if (!csrfToken) return;
    
    let csrfInput = form.querySelector('input[name="csrf_token"]');
    if (!csrfInput) {
        csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        form.appendChild(csrfInput);
    }
    csrfInput.value = csrfToken;
}

// Setup AJAX interceptor
function setupAjaxInterceptor() {
    // Intercept fetch requests
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        // Add CSRF token to headers if it's a POST request
        if (options.method && options.method.toLowerCase() === 'post') {
            if (!options.headers) {
                options.headers = {};
            }
            if (csrfToken) {
                options.headers['X-CSRF-Token'] = csrfToken;
            }
        }
        
        return originalFetch(url, options);
    };
    
    // Intercept XMLHttpRequest
    const originalXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
        this._method = method;
        this._url = url;
        return originalXHROpen.call(this, method, url, async, user, password);
    };
    
    const originalXHRSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function(data) {
        if (this._method && this._method.toLowerCase() === 'post' && csrfToken) {
            if (!this.getRequestHeader('X-CSRF-Token')) {
                this.setRequestHeader('X-CSRF-Token', csrfToken);
            }
        }
        return originalXHRSend.call(this, data);
    };
}

// Setup keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit forms
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const form = e.target.closest('form');
            if (form) {
                e.preventDefault();
                form.submit();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal.show');
            if (modal) {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        }
        
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"], input[placeholder*="search" i]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

// Setup auto-save for forms
function setupAutoSave() {
    const forms = document.querySelectorAll('form[data-auto-save]');
    forms.forEach(form => {
        const formId = form.getAttribute('data-auto-save');
        if (formId) {
            setupFormAutoSave(form, formId);
        }
    });
}

// Setup form auto-save
function setupFormAutoSave(form, formId) {
    const inputs = form.querySelectorAll('input, textarea, select');
    const saveDelay = 2000; // 2 seconds
    let saveTimeout;
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveFormData(form, formId);
            }, saveDelay);
        });
    });
}

// Save form data to localStorage
function saveFormData(form, formId) {
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem(`form_${formId}`, JSON.stringify(data));
    showToast('Form data saved automatically', 'info');
}

// Load saved form data
function loadSavedFormData(form, formId) {
    const saved = localStorage.getItem(`form_${formId}`);
    if (saved) {
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            });
        } catch (e) {
            console.error('Error loading saved form data:', e);
        }
    }
}

// Initialize Bootstrap components
function initializeBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Initialize modals
    const modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'));
    modalTriggerList.map(function (modalTriggerEl) {
        return new bootstrap.Modal(modalTriggerEl);
    });
}

// Setup online/offline detection
function setupOnlineDetection() {
    window.addEventListener('online', function() {
        isOnline = true;
        showToast('You are back online!', 'success');
        document.body.classList.remove('offline');
    });
    
    window.addEventListener('offline', function() {
        isOnline = false;
        showToast('You are offline. Some features may not work.', 'warning');
        document.body.classList.add('offline');
    });
}

// Initialize notifications
function initializeNotifications() {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    
    // Setup service worker for push notifications (if supported)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('Service Worker registered:', registration);
            })
            .catch(error => {
                console.log('Service Worker registration failed:', error);
            });
    }
}

// Setup CSRF auto-refresh
function setupCSRFAutoRefresh() {
    // Refresh CSRF token every 15 minutes
    setInterval(() => {
        refreshCSRFToken();
    }, 15 * 60 * 1000);
}

// Refresh CSRF token
function refreshCSRFToken() {
    fetch('/api/auth/refresh-csrf.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.token) {
                csrfToken = data.token;
                updateCSRFTokenInForms();
                console.log('CSRF token refreshed');
            }
        })
        .catch(error => {
            console.error('Failed to refresh CSRF token:', error);
        });
}

// Update CSRF token in all forms
function updateCSRFTokenInForms() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        addCSRFTokenToForm(form);
    });
    
    // Update meta tag
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        metaTag.setAttribute('content', csrfToken);
    }
}

// Utility Functions

// Show toast notification
function showToast(message, type = 'info', duration = 5000) {
    // Check if Bootstrap toast is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toastContainer = document.querySelector('.toast-container');
        if (toastContainer) {
            const toastHtml = `
                <div class="toast" role="alert">
                    <div class="toast-header">
                        <i class="bi bi-info-circle text-${type} me-2"></i>
                        <strong class="me-auto">QLink</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>
            `;
            
            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHtml;
            const toast = toastElement.firstElementChild;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Auto-remove after duration
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, duration);
            
            return;
        }
    }
    
    // Fallback to alert if Bootstrap toast is not available
    alert(`${type.toUpperCase()}: ${message}`);
}

// Show loading spinner
function showLoading(element, text = 'Loading...') {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    
    if (!element) return;
    
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">${text}</span>
        </div>
        <div class="mt-2">${text}</div>
    `;
    
    element.appendChild(spinner);
    element.classList.add('loading');
}

// Hide loading spinner
function hideLoading(element) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    
    if (!element) return;
    
    const spinner = element.querySelector('.loading-spinner');
    if (spinner) {
        spinner.remove();
    }
    
    element.classList.remove('loading');
}

// Format date
function formatDate(date, format = 'Y-m-d H:i:s') {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    
    return format
        .replace('Y', year)
        .replace('m', month)
        .replace('d', day)
        .replace('H', hours)
        .replace('i', minutes)
        .replace('s', seconds);
}

// Get time ago
function getTimeAgo(date) {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffDays > 0) return `${diffDays}d ago`;
    if (diffHours > 0) return `${diffHours}h ago`;
    if (diffMins > 0) return `${diffMins}m ago`;
    return 'Just now';
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy to clipboard
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Copied to clipboard!', 'success');
    } catch (err) {
        showToast('Failed to copy to clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Download file
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Validate email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate phone number
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

// Sanitize HTML
function sanitizeHTML(html) {
    const div = document.createElement('div');
    div.textContent = html;
    return div.innerHTML;
}

// Generate random string
function generateRandomString(length = 10) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Format currency
function formatCurrency(amount, currency = 'PHP') {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Smooth scroll to element
function smoothScrollTo(element, offset = 0) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    
    if (!element) return;
    
    const elementPosition = element.offsetTop - offset;
    window.scrollTo({
        top: elementPosition,
        behavior: 'smooth'
    });
}

// Export functions to global scope
window.QLink = {
    showToast,
    showLoading,
    hideLoading,
    formatDate,
    getTimeAgo,
    debounce,
    throttle,
    copyToClipboard,
    downloadFile,
    isValidEmail,
    isValidPhone,
    sanitizeHTML,
    generateRandomString,
    formatNumber,
    formatCurrency,
    isInViewport,
    smoothScrollTo,
    getCSRFToken: () => csrfToken,
    isOnline: () => isOnline
};
