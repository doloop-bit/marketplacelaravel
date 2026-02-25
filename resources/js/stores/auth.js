import { defineStore } from 'pinia';
import { authApi } from '@/services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isVendor: (state) => state.user?.roles?.includes('vendor') || false,
    isAdmin: (state) => state.user?.roles?.includes('admin') || false,
    isCustomer: (state) => state.user?.roles?.includes('customer') || false,
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await authApi.login(credentials);
        const { user, token } = response.data.data;

        this.token = token;
        this.user = user;

        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));

        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async register(data) {
      this.loading = true;
      this.error = null;

      try {
        const response = await authApi.register(data);
        const { user, token } = response.data.data;

        this.token = token;
        this.user = user;

        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));

        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await authApi.logout();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
      }
    },

    async fetchUser() {
      if (!this.token) return;

      try {
        const response = await authApi.me();
        this.user = response.data.data.user;
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (error) {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
      }
    },

    async updateProfile(data) {
      try {
        const response = await authApi.updateProfile(data);
        this.user = response.data.data.user;
        localStorage.setItem('user', JSON.stringify(this.user));
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Update failed';
        return { success: false, error: this.error };
      }
    },

    clearError() {
      this.error = null;
    },
  },
});
