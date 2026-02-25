<template>
  <div v-if="order" class="max-w-4xl mx-auto">
    <!-- Order Success Header -->
    <div class="card text-center py-8 mb-6 bg-success-50 border border-success-200">
      <div class="w-16 h-16 bg-success-500 text-white rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
      <p class="text-gray-600">Your order number: <span class="font-mono font-bold">{{ order.order_number }}</span></p>
    </div>

    <!-- Order Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Order Status -->
      <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Status</h2>
        <div class="flex items-center gap-3">
          <div :class="[
            'w-12 h-12 rounded-full flex items-center justify-center',
            statusColor
          ]">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <p class="font-bold text-gray-900 capitalize">{{ order.status }}</p>
            <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Payment Info -->
      <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Method</h2>
        <p class="font-medium text-gray-900 capitalize">{{ order.payment_method || 'Pending' }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ paymentInstructions }}</p>
      </div>
    </div>

    <!-- Shipping Address -->
    <div class="card mb-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping Address</h2>
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <div>
          <p class="font-medium text-gray-900">{{ order.shipping_name }}</p>
          <p class="text-sm text-gray-600">{{ order.shipping_phone }}</p>
          <p class="text-sm text-gray-600 mt-1">{{ order.shipping_address }}</p>
          <p class="text-sm text-gray-600">
            {{ order.shipping_district }}, {{ order.shipping_city }}, {{ order.shipping_province }} {{ order.shipping_postal_code }}
          </p>
        </div>
      </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Order Items</h2>
      <div class="space-y-4">
        <div
          v-for="item in order.items"
          :key="item.id"
          class="flex gap-4 pb-4 border-b border-gray-200 last:border-0 last:pb-0"
        >
          <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
            <img
              v-if="item.product?.primary_image?.url"
              :src="item.product.primary_image.url"
              :alt="item.product_name"
              class="w-full h-full object-cover"
            />
          </div>
          <div class="flex-1">
            <router-link :to="`/products/${item.product_slug}`" class="font-medium text-gray-900 hover:text-primary-600">
              {{ item.product_name }}
            </router-link>
            <p class="text-sm text-gray-500 mt-1">Qty: {{ item.quantity }}</p>
            <p class="text-sm font-bold text-gray-900 mt-1">Rp {{ formatPrice(item.subtotal) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Price Summary -->
    <div class="card mb-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Subtotal</span>
          <span class="font-medium">Rp {{ formatPrice(order.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Shipping</span>
          <span class="font-medium">Rp {{ formatPrice(order.shipping_cost) }}</span>
        </div>
        <div v-if="order.discount > 0" class="flex justify-between text-sm">
          <span class="text-gray-600">Discount</span>
          <span class="font-medium text-error-600">-Rp {{ formatPrice(order.discount) }}</span>
        </div>
      </div>
      <hr class="border-gray-200 my-4" />
      <div class="flex justify-between">
        <span class="font-bold text-gray-900">Total Paid</span>
        <span class="font-bold text-xl text-gray-900">Rp {{ formatPrice(order.total) }}</span>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
      <router-link to="/account/orders" class="btn-secondary flex-1 text-center">
        View All Orders
      </router-link>
      <router-link to="/products" class="btn-primary flex-1 text-center">
        Continue Shopping
      </router-link>
    </div>
  </div>

  <div v-else class="text-center py-12">
    <div class="w-16 h-16 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto mb-4"></div>
    <p class="text-gray-500">Loading order details...</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ordersApi } from '@/services/api';

const route = useRoute();
const order = ref(null);

const statusColor = computed(() => {
  const colors = {
    pending: 'bg-warning-500',
    paid: 'bg-success-500',
    processing: 'bg-primary-500',
    shipped: 'bg-blue-500',
    delivered: 'bg-success-500',
    cancelled: 'bg-error-500',
    refunded: 'bg-error-500',
  };
  return colors[order.value?.status] || 'bg-gray-500';
});

const paymentInstructions = computed(() => {
  const instructions = {
    bank_transfer: 'Please transfer to our bank account within 24 hours',
    credit_card: 'Payment will be processed immediately',
    ewallet: 'Please complete payment in your e-wallet app',
  };
  return instructions[order.value?.payment_method] || 'Payment pending';
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

onMounted(async () => {
  try {
    const response = await ordersApi.show(route.params.orderNumber);
    order.value = response.data.data.order;
  } catch (error) {
    console.error('Failed to fetch order:', error);
  }
});
</script>
