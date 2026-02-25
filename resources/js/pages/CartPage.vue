<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Shopping Cart</h1>

    <div v-if="!cartStore.loading && cartStore.itemCount > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Cart Items -->
      <div class="lg:col-span-2 space-y-4">
        <div
          v-for="item in cartStore.items"
          :key="item.id"
          class="card flex gap-4"
        >
          <!-- Product Image -->
          <div class="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100">
            <img
              v-if="item.product?.primary_image?.url || item.product?.images?.[0]?.url"
              :src="item.product?.primary_image?.url || item.product?.images?.[0]?.url"
              :alt="item.product?.name"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Product Info -->
          <div class="flex-1">
            <router-link
              :to="`/products/${item.product?.slug}`"
              class="font-medium text-gray-900 hover:text-primary-600 line-clamp-2"
            >
              {{ item.product?.name }}
            </router-link>
            <p v-if="item.product?.vendor" class="text-sm text-gray-500 mt-1">
              {{ item.product.vendor.shop_name }}
            </p>

            <div class="flex items-center justify-between mt-3">
              <!-- Quantity Controls -->
              <div class="flex items-center gap-2">
                <button
                  @click="updateQuantity(item.id, item.quantity - 1)"
                  class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                >
                  -
                </button>
                <span class="w-10 text-center">{{ item.quantity }}</span>
                <button
                  @click="updateQuantity(item.id, item.quantity + 1)"
                  class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                >
                  +
                </button>
              </div>

              <!-- Price -->
              <div class="text-right">
                <p class="font-bold text-gray-900">Rp {{ formatPrice(item.subtotal) }}</p>
                <p class="text-xs text-gray-500">Rp {{ formatPrice(item.price) }} each</p>
              </div>
            </div>
          </div>

          <!-- Remove Button -->
          <button
            @click="removeFromCart(item.id)"
            class="text-gray-400 hover:text-error-600 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>

        <!-- Clear Cart Button -->
        <button @click="clearCart" class="text-sm text-error-600 hover:text-error-700">
          Clear Cart
        </button>
      </div>

      <!-- Order Summary -->
      <div class="lg:col-span-1">
        <div class="card sticky top-24">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>

          <div class="space-y-3 mb-4">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Subtotal ({{ cartStore.totalQuantity }} items)</span>
              <span class="font-medium">Rp {{ formatPrice(cartStore.total) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Shipping</span>
              <span class="font-medium text-success-600">Calculated at checkout</span>
            </div>
          </div>

          <hr class="border-gray-200 my-4" />

          <div class="flex justify-between mb-6">
            <span class="font-bold text-gray-900">Total</span>
            <span class="font-bold text-xl text-gray-900">Rp {{ formatPrice(cartStore.total) }}</span>
          </div>

          <router-link to="/checkout" class="btn-primary w-full block text-center py-3">
            Proceed to Checkout
          </router-link>

          <router-link to="/products" class="block text-center mt-4 text-sm text-primary-600 hover:text-primary-700">
            Continue Shopping
          </router-link>
        </div>
      </div>
    </div>

    <!-- Empty Cart State -->
    <div v-else-if="!cartStore.loading" class="card text-center py-12">
      <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <h2 class="text-xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
      <p class="text-gray-500 mb-6">Add some products to get started!</p>
      <router-link to="/products" class="btn-primary inline-block">
        Browse Products
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-else class="space-y-4">
      <div v-for="i in 3" :key="i" class="card h-32 skeleton"></div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';

const router = useRouter();
const cartStore = useCartStore();

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const updateQuantity = async (itemId, newQuantity) => {
  if (newQuantity < 1) return;
  
  const result = await cartStore.updateQuantity(itemId, newQuantity);
  if (!result.success) {
    alert(result.error || 'Failed to update quantity');
  }
};

const removeFromCart = async (itemId) => {
  const result = await cartStore.removeFromCart(itemId);
  if (!result.success) {
    alert(result.error || 'Failed to remove item');
  }
};

const clearCart = async () => {
  if (confirm('Are you sure you want to clear your cart?')) {
    await cartStore.clearCart();
  }
};

onMounted(() => {
  cartStore.fetchCart();
});
</script>
