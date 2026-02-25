<template>
  <div class="text-center py-12">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ isEdit ? 'Edit' : 'Add' }} Product</h1>
    <p class="text-gray-500">Product form will be displayed here.</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const isEdit = computed(() => !!route.params.id);
</script>
