<template>
  <div v-if="order" class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
      <router-link to="/" class="hover:text-primary-600">Home</router-link>
      <span>/</span>
      <router-link to="/account/orders" class="hover:text-primary-600">My Orders</router-link>
      <span>/</span>
      <span class="text-gray-900">{{ order.order_number }}</span>
    </nav>

    <!-- Order Status Header -->
    <div class="card text-center py-8 mb-6" :class="statusBgClass">
      <div :class="[
        'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4',
        statusColorClass
      ]">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2 capitalize">{{ order.status }} Order</h1>
      <p class="text-gray-600">Order #{{ order.order_number }}</p>
      <p class="text-sm text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
    </div>

    <!-- Order Timeline -->
    <div class="card mb-6">
      <h2 class="text-lg font-bold text-gray-900 mb-6">Order Timeline</h2>
      <div class="relative">
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
        <div
          v-for="(history, index) in order.status_history"
          :key="history.id"
          class="relative pl-12 pb-6 last:pb-0"
        >
          <div :class="[
            'absolute left-0 w-8 h-8 rounded-full flex items-center justify-center border-4 border-white',
            index === order.status_history.length - 1 ? 'bg-primary-600' : 'bg-gray-300'
          ]">
            <svg v-if="index === order.status_history.length - 1" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 capitalize">{{ history.status }}</p>
            <p class="text-sm text-gray-500">{{ formatDateTime(history.created_at) }}</p>
            <p v-if="history.notes" class="text-sm text-gray-600 mt-1">{{ history.notes }}</p>
          </div>
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
          <div class="w-24 h-24 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
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
            <div class="flex items-center gap-4 mt-1">
              <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
              <p class="text-sm font-bold text-gray-900">Rp {{ formatPrice(item.price) }}</p>
            </div>
            <p class="text-sm font-bold text-gray-900 mt-1">Subtotal: Rp {{ formatPrice(item.subtotal) }}</p>
          </div>
          <div class="flex flex-col items-end justify-between">
            <button
              v-if="order.status === 'delivered'"
              @click="writeReview(item.product_id)"
              class="btn-primary text-sm"
            >
              Write Review
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Shipping & Payment Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Shipping Address -->
      <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping Address</h2>
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <div>
            <p class="font-medium text-gray-900">{{ order.shipping_name }}</p>
            <p class="text-sm text-gray-600">{{ order.shipping_phone }}</p>
            <p class="text-sm text-gray-600 mt-2">{{ order.shipping_address }}</p>
            <p class="text-sm text-gray-600">
              {{ order.shipping_district }}, {{ order.shipping_city }}, {{ order.shipping_province }} {{ order.shipping_postal_code }}
            </p>
          </div>
        </div>
      </div>

      <!-- Payment Info -->
      <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Information</h2>
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          <div>
            <p class="font-medium text-gray-900 capitalize">{{ order.payment_method || 'Pending' }}</p>
            <p v-if="order.paid_at" class="text-sm text-success-600 mt-1">
              ✓ Paid on {{ formatDate(order.paid_at) }}
            </p>
            <p v-else class="text-sm text-warning-600 mt-1">
              ⚠ Payment pending
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Price Summary -->
    <div class="card mb-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
      <div class="space-y-2 max-w-md ml-auto">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Subtotal ({{ totalItems }} items)</span>
          <span class="font-medium">Rp {{ formatPrice(order.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Shipping Cost</span>
          <span class="font-medium">Rp {{ formatPrice(order.shipping_cost) }}</span>
        </div>
        <div v-if="order.discount > 0" class="flex justify-between text-sm">
          <span class="text-gray-600">Discount</span>
          <span class="font-medium text-error-600">-Rp {{ formatPrice(order.discount) }}</span>
        </div>
        <hr class="border-gray-200 my-2" />
        <div class="flex justify-between text-lg font-bold">
          <span class="text-gray-900">Total Paid</span>
          <span class="text-gray-900">Rp {{ formatPrice(order.total) }}</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4">
      <router-link to="/account/orders" class="btn-secondary flex-1 text-center">
        ← Back to Orders
      </router-link>
      <button
        v-if="order.status === 'pending'"
        @click="cancelOrder"
        class="bg-error-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-error-700 transition-colors"
      >
        Cancel Order
      </button>
      <button
        v-if="order.status === 'delivered'"
        @click="writeReview"
        class="btn-primary flex-1"
      >
        Write Review
      </button>
    </div>
  </div>

  <div v-else class="text-center py-12">
    <div class="w-16 h-16 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto mb-4"></div>
    <p class="text-gray-500">Loading order details...</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ordersApi } from '@/services/api';

const route = useRouter();
const router = useRoute();
const order = ref(null);

const totalItems = computed(() => {
  return order.value?.items?.reduce((sum, item) => sum + item.quantity, 0) || 0;
});

const statusColorClass = computed(() => {
  const colors = {
    pending: 'bg-warning-500',
    paid: 'bg-blue-500',
    processing: 'bg-primary-500',
    shipped: 'bg-purple-500',
    delivered: 'bg-success-500',
    cancelled: 'bg-error-500',
    refunded: 'bg-error-500',
  };
  return colors[order.value?.status] || 'bg-gray-500';
});

const statusBgClass = computed(() => {
  const colors = {
    pending: 'bg-warning-50',
    paid: 'bg-blue-50',
    processing: 'bg-primary-50',
    shipped: 'bg-purple-50',
    delivered: 'bg-success-50',
    cancelled: 'bg-error-50',
    refunded: 'bg-error-50',
  };
  return colors[order.value?.status] || 'bg-gray-50';
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const cancelOrder = async () => {
  if (!confirm('Are you sure you want to cancel this order?')) return;

  try {
    await ordersApi.cancel(order.value.id);
    alert('Order cancelled successfully');
    fetchOrder();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to cancel order');
  }
};

const writeReview = (productId) => {
  // TODO: Navigate to review form
  alert('Review feature coming soon!' + (productId ? ` Product: ${productId}` : ''));
};

const fetchOrder = async () => {
  try {
    const response = await ordersApi.show(route.params.orderNumber);
    order.value = response.data.data.order;
  } catch (error) {
    console.error('Failed to fetch order:', error);
  }
};

onMounted(() => {
  fetchOrder();
});
</script>
