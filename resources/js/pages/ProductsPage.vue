<template>
  <div>
    <div class="flex flex-col md:flex-row gap-6">
      <!-- Filters Sidebar -->
      <aside class="w-full md:w-64 flex-shrink-0">
        <div class="card sticky top-24">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900">Filters</h3>
            <button @click="clearFilters" class="text-sm text-primary-600 hover:text-primary-700">
              Clear All
            </button>
          </div>

          <!-- Search -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="filters.search"
              @input="debouncedSearch"
              type="text"
              placeholder="Search products..."
              class="input text-sm"
            />
          </div>

          <!-- Category Filter -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select v-model="filters.category_id" @change="applyFilters" class="input text-sm">
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Price Range -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
            <div class="flex gap-2">
              <input
                v-model="filters.min_price"
                @input="applyFilters"
                type="number"
                placeholder="Min"
                class="input text-sm"
              />
              <input
                v-model="filters.max_price"
                @input="applyFilters"
                type="number"
                placeholder="Max"
                class="input text-sm"
              />
            </div>
          </div>

          <!-- Sort By -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select v-model="filters.sort_by" @change="applyFilters" class="input text-sm">
              <option value="latest">Latest</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="popular">Most Popular</option>
              <option value="rating">Highest Rating</option>
            </select>
          </div>
        </div>
      </aside>

      <!-- Products Grid -->
      <main class="flex-1">
        <div class="mb-4 flex items-center justify-between">
          <p class="text-sm text-gray-600">
            Showing {{ products.length }} of {{ meta.total }} products
          </p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="i in 6" :key="i" class="card h-64 skeleton"></div>
        </div>

        <!-- Products Grid -->
        <div v-else-if="products.length > 0" class="grid grid-cols-2 lg:grid-cols-3 gap-4">
          <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>

        <!-- Empty State -->
        <div v-else class="card text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <p class="text-gray-500">No products found</p>
          <button @click="clearFilters" class="mt-4 btn-primary">Clear Filters</button>
        </div>

        <!-- Pagination -->
        <div v-if="products.length > 0 && meta.last_page > 1" class="mt-8 flex justify-center">
          <div class="flex gap-2">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="[
                'w-10 h-10 rounded-xl font-medium transition-colors',
                page === meta.current_page
                  ? 'bg-primary-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-100'
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { productsApi, categoriesApi } from '@/services/api';
import ProductCard from '@/components/ProductCard.vue';

const route = useRoute();
const router = useRouter();

const products = ref([]);
const categories = ref([]);
const loading = ref(true);

const filters = reactive({
  search: route.query.search || '',
  category_id: route.query.category_id || '',
  min_price: route.query.min_price || '',
  max_price: route.query.max_price || '',
  sort_by: route.query.sort_by || 'latest',
  page: parseInt(route.query.page) || 1,
});

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
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

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  const query = {
    ...filters,
    page: 1, // Reset to first page when filters change
  };
  Object.keys(query).forEach(key => {
    if (!query[key]) delete query[key];
  });
  router.push({ path: '/products', query });
  filters.page = 1;
};

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = key === 'page' ? 1 : '';
  });
  router.push({ path: '/products' });
};

const goToPage = (page) => {
  filters.page = page;
  const query = { ...filters };
  Object.keys(query).forEach(key => {
    if (!query[key]) delete query[key];
  });
  router.push({ path: '/products', query });
};

const fetchProducts = async () => {
  loading.value = true;
  try {
    const params = { ...filters };
    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });
    
    const response = await productsApi.list(params);
    products.value = response.data.data.products;
    meta.value = response.data.data.meta;
  } catch (error) {
    console.error('Failed to fetch products:', error);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await categoriesApi.list();
    categories.value = response.data.data.categories;
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

onMounted(() => {
  fetchCategories();
  fetchProducts();
});
</script>
