<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Forgot Password</h2>
    <p class="text-gray-600 text-sm mb-6">
      Enter your email address and we'll send you a link to reset your password.
    </p>

    <form @submit.prevent="handleForgotPassword">
      <!-- Email -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input
          v-model="form.email"
          type="email"
          required
          class="input"
          placeholder="you@example.com"
        />
      </div>

      <!-- Success Message -->
      <div v-if="success" class="mb-4 p-3 bg-success-50 text-success-700 rounded-xl text-sm">
        Password reset link has been sent to your email.
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mb-4 p-3 bg-error-50 text-error-700 rounded-xl text-sm">
        {{ error }}
      </div>

      <!-- Submit Button -->
      <button type="submit" :disabled="loading" class="btn-primary w-full">
        <span v-if="loading">Sending...</span>
        <span v-else>Send Reset Link</span>
      </button>
    </form>

    <!-- Back to Login -->
    <p class="mt-6 text-center text-sm text-gray-600">
      <router-link to="/login" class="text-primary-600 hover:text-primary-700 font-medium">
        ← Back to Login
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { authApi } from '@/services/api';

const form = reactive({
  email: '',
});

const loading = ref(false);
const success = ref(false);
const error = ref('');

const handleForgotPassword = async () => {
  loading.value = true;
  error.value = '';
  success.value = false;

  try {
    await authApi.forgotPassword({ email: form.email });
    success.value = true;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to send reset link';
  } finally {
    loading.value = false;
  }
};
</script>
