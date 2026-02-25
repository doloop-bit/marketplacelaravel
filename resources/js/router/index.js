import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Layout components
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

const routes = [
  // Public Routes with Default Layout
  {
    path: '/',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@/pages/HomePage.vue'),
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('@/pages/ProductsPage.vue'),
      },
      {
        path: 'products/:slug',
        name: 'product-detail',
        component: () => import('@/pages/ProductDetailPage.vue'),
      },
      {
        path: 'categories/:slug',
        name: 'category',
        component: () => import('@/pages/CategoryPage.vue'),
      },
      {
        path: 'cart',
        name: 'cart',
        component: () => import('@/pages/CartPage.vue'),
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: () => import('@/pages/CheckoutPage.vue'),
        meta: { requiresAuth: true },
      },
    ],
  },

  // Auth Routes
  {
    path: '/',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('@/pages/auth/LoginPage.vue'),
        meta: { guest: true },
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('@/pages/auth/RegisterPage.vue'),
        meta: { guest: true },
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: () => import('@/pages/auth/ForgotPasswordPage.vue'),
        meta: { guest: true },
      },
    ],
  },

  // Protected Routes - Customer
  {
    path: '/account',
    component: DefaultLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/pages/account/OrdersPage.vue'),
      },
      {
        path: 'orders/:orderNumber',
        name: 'order-detail',
        component: () => import('@/pages/account/OrderDetailPage.vue'),
      },
      {
        path: 'success/:orderNumber',
        name: 'order-success',
        component: () => import('@/pages/account/OrderSuccessPage.vue'),
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/pages/account/ProfilePage.vue'),
      },
      {
        path: 'addresses',
        name: 'addresses',
        component: () => import('@/pages/account/AddressesPage.vue'),
      },
      {
        path: 'wishlist',
        name: 'wishlist',
        component: () => import('@/pages/account/WishlistPage.vue'),
      },
    ],
  },

  // Vendor Dashboard
  {
    path: '/vendor',
    component: DefaultLayout,
    meta: { requiresAuth: true, role: 'vendor' },
    children: [
      {
        path: 'dashboard',
        name: 'vendor-dashboard',
        component: () => import('@/pages/vendor/DashboardPage.vue'),
      },
      {
        path: 'products',
        name: 'vendor-products',
        component: () => import('@/pages/vendor/ProductsPage.vue'),
      },
      {
        path: 'products/create',
        name: 'vendor-product-create',
        component: () => import('@/pages/vendor/ProductFormPage.vue'),
      },
      {
        path: 'products/:id/edit',
        name: 'vendor-product-edit',
        component: () => import('@/pages/vendor/ProductFormPage.vue'),
      },
      {
        path: 'orders',
        name: 'vendor-orders',
        component: () => import('@/pages/vendor/OrdersPage.vue'),
      },
    ],
  },

  // Admin Dashboard
  {
    path: '/admin',
    component: DefaultLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: () => import('@/pages/admin/DashboardPage.vue'),
      },
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('@/pages/admin/UsersPage.vue'),
      },
      {
        path: 'vendors',
        name: 'admin-vendors',
        component: () => import('@/pages/admin/VendorsPage.vue'),
      },
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('@/pages/admin/ProductsPage.vue'),
      },
      {
        path: 'orders',
        name: 'admin-orders',
        component: () => import('@/pages/admin/OrdersPage.vue'),
      },
    ],
  },

  // 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFoundPage.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

// Navigation Guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;
  const userRole = authStore.user?.roles?.[0] || null;

  // Protected routes
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } });
    return;
  }

  // Guest routes (redirect if already logged in)
  if (to.meta.guest && isAuthenticated) {
    next({ name: 'home' });
    return;
  }

  // Role-based access
  if (to.meta.role && userRole !== to.meta.role) {
    next({ name: 'home' });
    return;
  }

  next();
});

export default router;
