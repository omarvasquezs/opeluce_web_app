import axios from 'axios';
window.axios = axios;

// Set base URL for API requests
// Use DDEV URL when in development mode, otherwise use current origin
const isDevelopment = import.meta.env.DEV;
const ddevUrl = 'https://opeluce.ddev.site';

if (isDevelopment && window.location.hostname === 'localhost') {
    // Running in Vite dev server, use DDEV URL for API calls
    window.axios.defaults.baseURL = ddevUrl;
    window.axios.defaults.withCredentials = true; // Include cookies for CORS
} else {
    // Running in production or served by Laravel, use current origin
    window.axios.defaults.baseURL = window.location.origin;
}

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Set CSRF token header from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

// Set up authentication token from localStorage
const authToken = localStorage.getItem('auth_token');
if (authToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

// Add response interceptor to handle authentication errors
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Clear stored auth data and redirect to login
            localStorage.removeItem('user');
            localStorage.removeItem('auth_token');
            delete window.axios.defaults.headers.common['Authorization'];
            
            // Only redirect if not already on login page
            if (!window.location.pathname.includes('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

// Bootstrap CSS and JS
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
