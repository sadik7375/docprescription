import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global Axios Response Interceptor for Session Expiry & Auth Redirection
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && [401, 419].includes(error.response.status)) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);