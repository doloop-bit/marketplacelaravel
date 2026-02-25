<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

    <!-- Order Status Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto">
      <button
        v-for="status in orderStatuses"
        :key="status.value"
        @click="selectedStatus = status.value"
        :class="[
          'px-4 py-2 rounded-xl font-medium whitespace-nowrap transition-colors',
          selectedStatus === status.value
            ? 'bg-primary-600 text-white'
            : 'bg-white text-gray-600 hover:bg-gray-100'
        ]"
      >
        {{ status.label }}
      </button>
    </div>

    <!-- Orders List -->
    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="card h-40 skeleton"></div>
    </div>

    <div v-else-if="orders.length > 0" class="space-y-4">
      <div
        v-for="order in orders"
        :key="order.id"
        class="card"
      >
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-200">
          <div>
            <p class="text-sm text-gray-500">Order Number</p>
            <router-link :to="`/account/orders/${order.order_number}`" class="font-mono font-bold text-primary-600 hover:text-primary-700">
              {{ order.order_number }}
            </router-link>
            <p class="text-sm text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="flex items-center gap-4">
            <span :class="[
              'px-3 py-1 rounded-full text-sm font-medium capitalize',
              statusClass(order.status)
            ]">
              {{ order.status }}
            </span>
            <router-link :to="`/account/orders/${order.order_number}`" class="btn-secondary text-sm">
              View Details
            </router-link>
          </div>
        </div>

        <!-- Order Items Preview -->
        <div class="pt-4 flex items-center gap-4">
          <div class="flex -space-x-2">
            <div
              v-for="item in order.items?.slice(0, 3)"
              :key="item.id"
              class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 border-2 border-white"
            >
              <img
                v-if="item.product?.primary_image?.url"
                :src="item.product.primary_image.url"
                :alt="item.product_name"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-600">
              <span class="font-bold text-gray-900">{{ order.items?.length }}</span> items
              <span v-if="order.items?.length > 3" class="text-gray-400">+{{ order.items.length - 3 }} more</span>
            </p>
            <p class="font-bold text-gray-900">Rp {{ formatPrice(order.total) }}</p>
          </div>
          <div class="flex gap-2">
            <button
              v-if="order.status === 'pending'"
              @click="cancelOrder(order.id)"
              class="text-sm text-error-600 hover:text-error-700"
            >
              Cancel Order
            </button>
            <button
              v-if="order.status === 'delivered'"
              @click="reviewOrder(order.id)"
              class="btn-primary text-sm"
            >
              Write Review
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="goToPage(page)"
          :class="[
            'w-10 h-10 rounded-xl font-medium transition-colors',
            meta.current_page === page
              ? 'bg-primary-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card text-center py-12">
      <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
      </svg>
      <h2 class="text-xl font-bold text-gray-900 mb-2">No orders yet</h2>
      <p class="text-gray-500 mb-6">Start shopping to see your orders here!</p>
      <router-link to="/products" class="btn-primary inline-block">
        Browse Products
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ordersApi } from '@/services/api';

const orders = ref([]);
const loading = ref(true);
const selectedStatus = ref('all');

const orderStatuses = [
  { value: 'all', label: 'All Orders' },
  { value: 'pending', label: 'Pending' },
  { value: 'processing', label: 'Processing' },
  { value: 'shipped', label: 'Shipped' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'cancelled', label: 'Cancelled' },
];

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, meta.value.current_page - 2);
  const end = Math.min(meta.value.last_page, meta.value.current_page + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const statusClass = (status) => {
  const classes = {
    pending: 'bg-warning-100 text-warning-800',
    paid: 'bg-blue-100 text-blue-800',
    processing: 'bg-primary-100 text-primary-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-success-100 text-success-800',
    cancelled: 'bg-error-100 text-error-800',
    refunded: 'bg-error-100 text-error-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

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

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await ordersApi.list();
    orders.value = response.data.data.orders;
    meta.value = response.data.data.meta;
  } catch (error) {
    console.error('Failed to fetch orders:', error);
  } finally {
    loading.value = false;
  }
};

const cancelOrder = async (orderId) => {
  if (!confirm('Are you sure you want to cancel this order?')) return;

  try {
    await ordersApi.cancel(orderId);
    alert('Order cancelled successfully');
    fetchOrders();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to cancel order');
  }
};

const reviewOrder = async (orderId) => {
  // TODO: Navigate to review form
  alert('Review feature coming soon!');
};

const goToPage = (page) => {
  meta.value.current_page = page;
  fetchOrders();
};

onMounted(() => {
  fetchOrders();
});
</script>
