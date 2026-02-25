import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || '/api/v1';

// Create axios instance
const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - handle errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authApi = {
  login: (credentials) => apiClient.post('/auth/login', credentials),
  register: (data) => apiClient.post('/auth/register', data),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get('/auth/me'),
  updateProfile: (data) => apiClient.put('/auth/update-profile', data),
  changePassword: (data) => apiClient.post('/auth/change-password', data),
  forgotPassword: (email) => apiClient.post('/auth/forgot-password', email),
  resetPassword: (data) => apiClient.post('/auth/reset-password', data),
};

// Products API
export const productsApi = {
  list: (params) => apiClient.get('/products', { params }),
  featured: () => apiClient.get('/products/featured'),
  search: (query) => apiClient.get('/products/search', { params: { q: query } }),
  show: (slug) => apiClient.get(`/products/${slug}`),
};

// Categories API
export const categoriesApi = {
  list: () => apiClient.get('/categories'),
  show: (slug) => apiClient.get(`/categories/${slug}`),
};

// Cart API
export const cartApi = {
  get: () => apiClient.get('/cart'),
  add: (data) => apiClient.post('/cart/add', data),
  update: (id, data) => apiClient.patch(`/cart/update/${id}`, data),
  remove: (id) => apiClient.delete(`/cart/remove/${id}`),
  clear: () => apiClient.post('/cart/clear'),
};

// Orders API
export const ordersApi = {
  list: () => apiClient.get('/orders'),
  show: (orderNumber) => apiClient.get(`/orders/${orderNumber}`),
  create: (data) => apiClient.post('/orders', data),
  cancel: (id) => apiClient.post(`/orders/${id}/cancel`),
};

// Vendor API
export const vendorApi = {
  products: {
    list: () => apiClient.get('/vendor/products'),
    create: (data) => apiClient.post('/vendor/products', data),
    show: (id) => apiClient.get(`/vendor/products/${id}`),
    update: (id, data) => apiClient.put(`/vendor/products/${id}`, data),
    delete: (id) => apiClient.delete(`/vendor/products/${id}`),
  },
  orders: {
    list: () => apiClient.get('/vendor/orders'),
    show: (id) => apiClient.get(`/vendor/orders/${id}`),
    updateStatus: (id, data) => apiClient.patch(`/vendor/orders/${id}/status`, data),
  },
};

// Admin API
export const adminApi = {
  dashboard: () => apiClient.get('/admin/dashboard'),
  users: () => apiClient.get('/admin/users'),
  vendors: () => apiClient.get('/admin/vendors'),
  products: () => apiClient.get('/admin/products'),
  orders: () => apiClient.get('/admin/orders'),
  approveVendor: (id) => apiClient.post(`/admin/vendors/${id}/approve`),
  rejectVendor: (id, reason) => apiClient.post(`/admin/vendors/${id}/reject`, { reason }),
};

export default apiClient;
