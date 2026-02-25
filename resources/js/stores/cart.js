import { defineStore } from 'pinia';
import { cartApi } from '@/services/api';

export const useCartStore = defineStore('cart', {
  state: () => ({
    cart: null,
    loading: false,
    error: null,
  }),

  getters: {
    items: (state) => state.cart?.items || [],
    total: (state) => state.cart?.total || 0,
    totalQuantity: (state) => state.cart?.total_quantity || 0,
    itemCount: (state) => state.cart?.items_count || 0,
    isEmpty: (state) => !state.cart || state.cart.items.length === 0,
  },

  actions: {
    async fetchCart() {
      this.loading = true;
      try {
        const response = await cartApi.get();
        this.cart = response.data.data.cart;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch cart';
      } finally {
        this.loading = false;
      }
    },

    async addToCart(productId, quantity = 1) {
      this.loading = true;
      try {
        const response = await cartApi.add({ product_id: productId, quantity });
        this.cart = response.data.data.cart_item.cart;
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to add to cart';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async updateQuantity(itemId, quantity) {
      try {
        const response = await cartApi.update(itemId, { quantity });
        this.cart = response.data.data.cart;
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update cart';
        return { success: false, error: this.error };
      }
    },

    async removeFromCart(itemId) {
      try {
        await cartApi.remove(itemId);
        await this.fetchCart();
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to remove item';
        return { success: false, error: this.error };
      }
    },

    async clearCart() {
      try {
        await cartApi.clear();
        this.cart = null;
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to clear cart';
        return { success: false, error: this.error };
      }
    },

    setCart(cart) {
      this.cart = cart;
    },

    clearError() {
      this.error = null;
    },
  },
});
