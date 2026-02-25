<template>
  <div v-if="product">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
      <router-link to="/" class="hover:text-primary-600">Home</router-link>
      <span>/</span>
      <router-link :to="`/categories/${product.category?.slug}`" class="hover:text-primary-600">
        {{ product.category?.name }}
      </router-link>
      <span>/</span>
      <span class="text-gray-900">{{ product.name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Product Images -->
      <div>
        <!-- Main Image -->
        <div class="card p-4 mb-4">
          <div class="aspect-square rounded-xl overflow-hidden bg-gray-100">
            <img
              v-if="currentImage"
              :src="currentImage.url"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
              <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <!-- Thumbnail Images -->
          <div v-if="product.images?.length > 1" class="flex gap-2 mt-4 overflow-x-auto">
            <button
              v-for="image in product.images"
              :key="image.id"
              @click="currentImage = image"
              :class="[
                'w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 border-2 transition-colors',
                currentImage?.id === image.id ? 'border-primary-600' : 'border-transparent hover:border-gray-300'
              ]"
            >
              <img :src="image.url" :alt="product.name" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>
      </div>

      <!-- Product Info -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>

        <!-- Vendor Info -->
        <div v-if="product.vendor" class="flex items-center gap-3 mb-4 p-4 bg-gray-50 rounded-xl">
          <div class="w-12 h-12 bg-primary-200 rounded-full flex items-center justify-center">
            <span class="text-primary-700 font-bold">{{ product.vendor.shop_name?.[0]?.toUpperCase() }}</span>
          </div>
          <div>
            <p class="font-medium text-gray-900">{{ product.vendor.shop_name }}</p>
            <div class="flex items-center text-sm text-gray-500">
              <span v-if="product.vendor.rating_average > 0">
                ⭐ {{ product.vendor.rating_average }}
              </span>
              <span v-if="product.vendor.total_sales > 0" class="ml-2">
                {{ product.vendor.total_sales }} sold
              </span>
            </div>
          </div>
        </div>

        <!-- Rating -->
        <div v-if="product.rating_average > 0" class="flex items-center gap-2 mb-4">
          <div class="flex text-warning-500">
            <span v-for="i in 5" :key="i" class="text-lg">
              <svg v-if="i <= Math.round(product.rating_average)" class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </span>
          </div>
          <span class="text-sm text-gray-500">{{ product.total_reviews }} reviews</span>
        </div>

        <!-- Price -->
        <div class="mb-6">
          <div class="flex items-baseline gap-3">
            <span class="text-3xl font-bold text-gray-900">Rp {{ formatPrice(product.price) }}</span>
            <span v-if="product.has_discount" class="text-xl text-gray-400 line-through">
              Rp {{ formatPrice(product.original_price) }}
            </span>
            <span v-if="product.has_discount" class="badge badge-error">
              -{{ product.discount_percentage }}%
            </span>
          </div>
        </div>

        <!-- Stock Info -->
        <div v-if="product.stock > 0" class="mb-4 p-3 bg-success-50 text-success-700 rounded-xl text-sm">
          ✓ In Stock ({{ product.stock }} available)
        </div>
        <div v-else class="mb-4 p-3 bg-error-50 text-error-700 rounded-xl text-sm">
          ✗ Out of Stock
        </div>

        <!-- Quantity Selector -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
          <div class="flex items-center gap-3">
            <button
              @click="decrementQuantity"
              class="w-10 h-10 rounded-xl border border-gray-300 flex items-center justify-center hover:bg-gray-50"
            >
              -
            </button>
            <input
              v-model.number="quantity"
              type="number"
              :min="product.min_order || 1"
              :max="product.max_order || product.stock"
              class="w-20 text-center input"
            />
            <button
              @click="incrementQuantity"
              class="w-10 h-10 rounded-xl border border-gray-300 flex items-center justify-center hover:bg-gray-50"
            >
              +
            </button>
          </div>
        </div>

        <!-- Add to Cart -->
        <div class="flex gap-3">
          <button
            @click="handleAddToCart"
            :disabled="product.stock === 0"
            class="btn-primary flex-1 py-3"
          >
            Add to Cart
          </button>
          <button class="w-12 h-12 rounded-xl border border-gray-300 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>
        </div>

        <!-- Product Details -->
        <div class="mt-8 card">
          <h3 class="font-bold text-gray-900 mb-4">Product Details</h3>
          <div class="prose prose-sm max-w-none text-gray-600" v-html="product.description"></div>
        </div>

        <!-- Specifications -->
        <div v-if="product.specifications" class="mt-4 card">
          <h3 class="font-bold text-gray-900 mb-4">Specifications</h3>
          <dl class="space-y-2">
            <div v-for="(value, key) in product.specifications" :key="key" class="flex">
              <dt class="w-40 text-sm text-gray-500 capitalize">{{ formatKey(key) }}</dt>
              <dd class="text-sm text-gray-900">{{ value }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>

  <div v-else-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="card h-96 skeleton"></div>
    <div class="space-y-4">
      <div class="h-8 bg-gray-200 rounded skeleton"></div>
      <div class="h-4 bg-gray-200 rounded skeleton"></div>
      <div class="h-20 bg-gray-200 rounded skeleton"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { productsApi } from '@/services/api';
import { useCartStore } from '@/stores/cart';

const route = useRoute();
const cartStore = useCartStore();

const product = ref(null);
const loading = ref(true);
const quantity = ref(1);
const currentImage = ref(null);

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const formatKey = (key) => {
  return key.replace(/_/g, ' ');
};

const incrementQuantity = () => {
  const max = product.value.max_order || product.value.stock;
  if (quantity.value < max) {
    quantity.value++;
  }
};

const decrementQuantity = () => {
  const min = product.value.min_order || 1;
  if (quantity.value > min) {
    quantity.value--;
  }
};

const handleAddToCart = async () => {
  const result = await cartStore.addToCart(product.value.id, quantity.value);
  if (result.success) {
    alert('Added to cart!');
  } else {
    alert(result.error || 'Failed to add to cart');
  }
};

onMounted(async () => {
  try {
    const response = await productsApi.show(route.params.slug);
    product.value = response.data.data.product;
    currentImage.value = product.value.primary_image || product.value.images?.[0];
  } catch (error) {
    console.error('Failed to fetch product:', error);
  } finally {
    loading.value = false;
  }
});
</script>
