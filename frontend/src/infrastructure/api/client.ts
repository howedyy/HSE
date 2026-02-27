import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
    headers: {
        'Content-Type': 'application/json',
    },
    withCredentials: true, // Required for secure session cookies
});

// Request interceptor to add JWT if using token-based auth
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('hse_auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response.data,
    (error) => {
        if (error.response?.status === 401) {
            // Clear local storage and redirect to login if unauthorized
            localStorage.removeItem('hse_auth_token');
            // Only redirect to login if not already there to avoid infinite loops
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default api;
