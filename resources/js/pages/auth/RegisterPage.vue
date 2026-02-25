<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Account</h2>

    <form @submit.prevent="handleRegister">
      <!-- Name -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
        <input
          v-model="form.name"
          type="text"
          required
          class="input"
          placeholder="John Doe"
        />
      </div>

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

      <!-- Phone -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Phone (Optional)</label>
        <input
          v-model="form.phone"
          type="tel"
          class="input"
          placeholder="081234567890"
        />
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input
          v-model="form.password"
          type="password"
          required
          minlength="8"
          class="input"
          placeholder="••••••••"
        />
      </div>

      <!-- Confirm Password -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          required
          minlength="8"
          class="input"
          placeholder="••••••••"
        />
      </div>

      <!-- Error Message -->
      <div v-if="authStore.error" class="mb-4 p-3 bg-error-50 text-error-700 rounded-xl text-sm">
        {{ authStore.error }}
      </div>

      <!-- Submit Button -->
      <button type="submit" :disabled="authStore.loading" class="btn-primary w-full">
        <span v-if="authStore.loading">Creating account...</span>
        <span v-else>Create Account</span>
      </button>
    </form>

    <!-- Login Link -->
    <p class="mt-6 text-center text-sm text-gray-600">
      Already have an account?
      <router-link to="/login" class="text-primary-600 hover:text-primary-700 font-medium">
        Sign In
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

const handleRegister = async () => {
  if (form.password !== form.password_confirmation) {
    authStore.error = 'Passwords do not match';
    return;
  }

  const result = await authStore.register({
    name: form.name,
    email: form.email,
    phone: form.phone,
    password: form.password,
    password_confirmation: form.password_confirmation,
  });

  if (result.success) {
    router.push('/');
  }
};
</script>
