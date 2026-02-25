<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-md">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <router-link to="/" class="flex items-center space-x-2">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-xl">M</span>
            </div>
            <span class="text-xl font-bold text-gray-900">Marketplace</span>
          </router-link>

          <!-- Search Bar - Desktop -->
          <div class="hidden md:flex flex-1 max-w-xl mx-8">
            <div class="relative w-full">
              <input
                type="text"
                v-model="searchQuery"
                @keyup.enter="handleSearch"
                placeholder="Search products..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none"
              />
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>

          <!-- Right Navigation -->
          <nav class="flex items-center space-x-4">
            <!-- Cart -->
            <router-link to="/cart" class="relative p-2 hover:bg-gray-100 rounded-xl transition-colors">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span v-if="cartStore.totalQuantity > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center">
                {{ cartStore.totalQuantity }}
              </span>
            </router-link>

            <!-- Auth Links -->
            <template v-if="authStore.isAuthenticated">
              <!-- User Menu -->
              <div class="relative" v-if="authStore.user">
                <button @click="showUserMenu = !showUserMenu" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-xl transition-colors">
                  <img v-if="authStore.user.avatar" :src="authStore.user.avatar" class="w-8 h-8 rounded-full object-cover" />
                  <div v-else class="w-8 h-8 bg-primary-200 rounded-full flex items-center justify-center">
                    <span class="text-primary-700 font-medium">{{ authStore.user.name?.[0]?.toUpperCase() }}</span>
                  </div>
                  <span class="hidden md:block text-sm font-medium text-gray-700">{{ authStore.user.name }}</span>
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <!-- Dropdown Menu -->
                <div v-show="showUserMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100">
                  <router-link to="/account/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Orders</router-link>
                  <router-link to="/account/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</router-link>
                  <router-link to="/account/wishlist" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Wishlist</router-link>
                  <template v-if="authStore.isVendor">
                    <hr class="my-2" />
                    <router-link to="/vendor/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Vendor Dashboard</router-link>
                  </template>
                  <template v-if="authStore.isAdmin">
                    <hr class="my-2" />
                    <router-link to="/admin/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Admin Dashboard</router-link>
                  </template>
                  <hr class="my-2" />
                  <button @click="handleLogout" class="w-full text-left px-4 py-2 text-sm text-error-600 hover:bg-error-50">Logout</button>
                </div>
              </div>
            </template>

            <template v-else>
              <router-link to="/login" class="btn-secondary">Login</router-link>
              <router-link to="/register" class="btn-primary">Register</router-link>
            </template>
          </nav>
        </div>

        <!-- Mobile Search -->
        <div class="md:hidden pb-4">
          <div class="relative">
            <input
              type="text"
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              placeholder="Search products..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none"
            />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
      <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 class="font-bold text-lg mb-4">Marketplace</h3>
            <p class="text-gray-600 text-sm">Your trusted online shopping destination for quality products from verified sellers.</p>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Shop</h4>
            <ul class="space-y-2 text-sm text-gray-600">
              <li><router-link to="/products" class="hover:text-primary-600">All Products</router-link></li>
              <li><router-link to="/products?featured=true" class="hover:text-primary-600">Featured</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Account</h4>
            <ul class="space-y-2 text-sm text-gray-600">
              <li><router-link to="/account/orders" class="hover:text-primary-600">Orders</router-link></li>
              <li><router-link to="/account/profile" class="hover:text-primary-600">Profile</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Support</h4>
            <ul class="space-y-2 text-sm text-gray-600">
              <li><a href="#" class="hover:text-primary-600">Help Center</a></li>
              <li><a href="#" class="hover:text-primary-600">Contact Us</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-200 mt-8 pt-8 text-center text-sm text-gray-500">
          &copy; {{ new Date().getFullYear() }} Marketplace. All rights reserved.
        </div>
      </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
      <div class="grid grid-cols-4 h-16">
        <router-link to="/" class="flex flex-col items-center justify-center text-gray-500 hover:text-primary-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span class="text-xs mt-1">Home</span>
        </router-link>
        <router-link to="/products" class="flex flex-col items-center justify-center text-gray-500 hover:text-primary-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          <span class="text-xs mt-1">Products</span>
        </router-link>
        <router-link to="/cart" class="flex flex-col items-center justify-center text-gray-500 hover:text-primary-600 relative">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span v-if="cartStore.totalQuantity > 0" class="absolute top-1 right-6 w-4 h-4 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center">{{ cartStore.totalQuantity }}</span>
          <span class="text-xs mt-1">Cart</span>
        </router-link>
        <router-link :to="authStore.isAuthenticated ? '/account/orders' : '/login'" class="flex flex-col items-center justify-center text-gray-500 hover:text-primary-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="text-xs mt-1">Account</span>
        </router-link>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();

const searchQuery = ref('');
const showUserMenu = ref(false);

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/products', query: { search: searchQuery.value } });
  }
};

const handleLogout = async () => {
  await authStore.logout();
  showUserMenu.value = false;
  router.push('/');
};

// Fetch cart on mount if authenticated
if (authStore.isAuthenticated) {
  cartStore.fetchCart();
}

// Close dropdown when clicking outside
import { onMounted, onUnmounted } from 'vue';
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showUserMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
