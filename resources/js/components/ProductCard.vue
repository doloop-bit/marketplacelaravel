<template>
  <router-link :to="`/products/${product.slug}`" class="card group p-4 h-full flex flex-col">
    <!-- Product Image -->
    <div class="relative aspect-square mb-4 rounded-xl overflow-hidden bg-gray-100">
      <img
        v-if="product.primary_image?.url || product.images?.[0]?.url"
        :src="product.primary_image?.url || product.images?.[0]?.url"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>

      <!-- Discount Badge -->
      <div v-if="product.has_discount" class="absolute top-2 left-2 badge badge-error">
        -{{ product.discount_percentage }}%
      </div>

      <!-- Add to Cart Button (on hover) -->
      <button
        @click.prevent="handleAddToCart"
        class="absolute bottom-2 right-2 w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-primary-700 shadow-lg"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </button>
    </div>

    <!-- Product Info -->
    <h3 class="font-medium text-gray-900 mb-1 line-clamp-2 flex-1">{{ product.name }}</h3>

    <!-- Vendor -->
    <p v-if="product.vendor" class="text-xs text-gray-500 mb-2">{{ product.vendor.shop_name }}</p>

    <!-- Rating -->
    <div v-if="product.rating_average > 0" class="flex items-center mb-2">
      <div class="flex text-warning-500">
        <span v-for="i in 5" :key="i" class="text-sm">
          <svg v-if="i <= Math.round(product.rating_average)" class="w-4 h-4 fill-current" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          <svg v-else class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </span>
      </div>
      <span class="ml-1 text-xs text-gray-500">({{ product.total_reviews }})</span>
    </div>

    <!-- Price -->
    <div class="flex items-center gap-2">
      <span class="text-lg font-bold text-gray-900">Rp {{ formatPrice(product.price) }}</span>
      <span v-if="product.has_discount" class="text-sm text-gray-400 line-through">Rp {{ formatPrice(product.original_price) }}</span>
    </div>

    <!-- Stock Info -->
    <p v-if="product.stock <= 10 && product.stock > 0" class="text-xs text-error-600 mt-1">
      Only {{ product.stock }} left!
    </p>
    <p v-else-if="product.stock === 0" class="text-xs text-error-600 mt-1">
      Out of stock
    </p>
  </router-link>
</template>

<script setup>
import { defineProps } from 'vue';
import { useCartStore } from '@/stores/cart';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const cartStore = useCartStore();

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const handleAddToCart = async () => {
  if (props.product.stock === 0) return;
  
  const result = await cartStore.addToCart(props.product.id, 1);
  if (result.success) {
    // Show toast notification (to be implemented)
    console.log('Added to cart');
  }
};
</script>
