<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-secondary-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex items-center space-x-2">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center shadow-lg">
            <span class="text-white font-bold text-2xl">M</span>
          </div>
          <span class="text-2xl font-bold text-gray-900">Marketplace</span>
        </router-link>
      </div>

      <!-- Auth Card -->
      <div class="card">
        <router-view />
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-6">
        <router-link to="/" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">
          ← Back to Home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Auth layout for login, register, and password reset pages
</script>
