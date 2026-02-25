<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Welcome Back</h2>

    <form @submit.prevent="handleLogin">
      <!-- Email -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input
          v-model="form.email"
          type="email"
          required
          class="input"
          placeholder="you@example.com"
        />
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input
          v-model="form.password"
          type="password"
          required
          class="input"
          placeholder="••••••••"
        />
      </div>

      <!-- Remember & Forgot -->
      <div class="flex items-center justify-between mb-6">
        <label class="flex items-center">
          <input v-model="form.remember" type="checkbox" class="w-4 h-4 text-primary-600 rounded" />
          <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
        <router-link to="/forgot-password" class="text-sm text-primary-600 hover:text-primary-700">
          Forgot password?
        </router-link>
      </div>

      <!-- Error Message -->
      <div v-if="authStore.error" class="mb-4 p-3 bg-error-50 text-error-700 rounded-xl text-sm">
        {{ authStore.error }}
      </div>

      <!-- Submit Button -->
      <button type="submit" :disabled="authStore.loading" class="btn-primary w-full">
        <span v-if="authStore.loading">Signing in...</span>
        <span v-else>Sign In</span>
      </button>
    </form>

    <!-- Register Link -->
    <p class="mt-6 text-center text-sm text-gray-600">
      Don't have an account?
      <router-link to="/register" class="text-primary-600 hover:text-primary-700 font-medium">
        Register
      </router-link>
    </p>

    <!-- Demo Credentials -->
    <div class="mt-6 p-4 bg-gray-50 rounded-xl">
      <p class="text-xs font-medium text-gray-500 mb-2">Demo Credentials:</p>
      <div class="text-xs text-gray-600 space-y-1">
        <p>Admin: admin@marketplace.com / password</p>
        <p>Vendor: vendor@marketplace.com / password</p>
        <p>Customer: customer@marketplace.com / password</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const handleLogin = async () => {
  const result = await authStore.login({
    email: form.email,
    password: form.password,
  });

  if (result.success) {
    const redirect = route.query.redirect || '/';
    router.push(redirect);
  }
};
</script>
