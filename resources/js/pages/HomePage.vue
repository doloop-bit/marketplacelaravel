<template>
  <div>
    <!-- Hero Section -->
    <section class="mb-8">
      <div class="relative bg-gradient-to-r from-primary-600 to-primary-700 rounded-3xl overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative px-8 py-16 md:py-24">
          <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
            Welcome to Marketplace
          </h1>
          <p class="text-lg text-white/90 mb-8 max-w-xl">
            Discover amazing products from verified sellers. Shop with confidence and enjoy great deals.
          </p>
          <router-link to="/products" class="inline-block bg-white text-primary-600 px-8 py-3 rounded-xl font-semibold hover:bg-gray-100 transition-colors">
            Shop Now
          </router-link>
        </div>
        <!-- Decorative circles -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full"></div>
        <div class="absolute -right-10 -bottom-20 w-48 h-48 bg-white/10 rounded-full"></div>
      </div>
    </section>

    <!-- Categories -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Shop by Category</h2>
        <router-link to="/products" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
          View All →
        </router-link>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div
          v-for="category in categories"
          :key="category.id"
          @click="goToCategory(category.slug)"
          class="card cursor-pointer hover:scale-105 transition-transform"
        >
          <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mb-3">
            <span class="text-2xl">{{ category.icon || '📦' }}</span>
          </div>
          <h3 class="font-medium text-gray-900">{{ category.name }}</h3>
          <p class="text-sm text-gray-500">{{ category.products_count }} products</p>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Featured Products</h2>
        <router-link to="/products?featured=true" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
          View All →
        </router-link>
      </div>

      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="card h-64 skeleton"></div>
      </div>

      <div v-else-if="featuredProducts.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <ProductCard
          v-for="product in featuredProducts"
          :key="product.id"
          :product="product"
        />
      </div>

      <div v-else class="card text-center py-12">
        <p class="text-gray-500">No featured products available</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { categoriesApi, productsApi } from '@/services/api';
import ProductCard from '@/components/ProductCard.vue';

const router = useRouter();

const categories = ref([]);
const featuredProducts = ref([]);
const loading = ref(true);

const fetchCategories = async () => {
  try {
    const response = await categoriesApi.list();
    categories.value = response.data.data.categories;
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

const fetchFeaturedProducts = async () => {
  try {
    const response = await productsApi.featured();
    featuredProducts.value = response.data.data.products;
  } catch (error) {
    console.error('Failed to fetch featured products:', error);
  } finally {
    loading.value = false;
  }
};

const goToCategory = (slug) => {
  router.push(`/categories/${slug}`);
};

onMounted(() => {
  fetchCategories();
  fetchFeaturedProducts();
});
</script>
