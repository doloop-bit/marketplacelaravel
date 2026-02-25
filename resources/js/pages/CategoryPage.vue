<template>
  <div class="text-center py-12">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Category: {{ category?.name || 'Loading...' }}</h1>
    <p class="text-gray-500">Category products will be displayed here.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { categoriesApi } from '@/services/api';

const route = useRoute();
const category = ref(null);

onMounted(async () => {
  try {
    const response = await categoriesApi.show(route.params.slug);
    category.value = response.data.data.category;
  } catch (error) {
    console.error('Failed to fetch category:', error);
  }
});
</script>
