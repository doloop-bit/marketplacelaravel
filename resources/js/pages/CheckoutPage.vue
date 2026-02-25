<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Checkout Form -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Shipping Address -->
        <div class="card">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping Address</h2>
          
          <div v-if="addresses.length > 0" class="space-y-3 mb-4">
            <div
              v-for="address in addresses"
              :key="address.id"
              @click="selectedAddress = address"
              :class="[
                'p-4 rounded-xl border-2 cursor-pointer transition-colors',
                selectedAddress?.id === address.id
                  ? 'border-primary-600 bg-primary-50'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="flex items-start justify-between">
                <div>
                  <p class="font-medium text-gray-900">{{ address.name }}</p>
                  <p class="text-sm text-gray-600">{{ address.phone }}</p>
                  <p class="text-sm text-gray-600 mt-1">{{ address.street_address }}</p>
                  <p class="text-sm text-gray-600">
                    {{ address.district?.name }}, {{ address.city?.name }}, {{ address.province?.name }} {{ address.postal_code }}
                  </p>
                </div>
                <div v-if="address.is_default" class="badge badge-primary">Default</div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-500">
            <p>No addresses found. Please add a shipping address.</p>
            <router-link to="/account/addresses" class="text-primary-600 hover:text-primary-700 mt-2 inline-block">
              Add Address →
            </router-link>
          </div>

          <router-link
            v-if="addresses.length > 0"
            to="/account/addresses"
            class="text-sm text-primary-600 hover:text-primary-700"
          >
            + Add New Address
          </router-link>
        </div>

        <!-- Shipping Method -->
        <div class="card">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping Method</h2>
          
          <div class="space-y-3">
            <div
              v-for="method in shippingMethods"
              :key="method.id"
              @click="selectedMethod = method"
              :class="[
                'p-4 rounded-xl border-2 cursor-pointer transition-colors',
                selectedMethod?.id === method.id
                  ? 'border-primary-600 bg-primary-50'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-900">{{ method.name }}</p>
                  <p class="text-sm text-gray-500">{{ method.estimated_days }} days estimated delivery</p>
                </div>
                <p class="font-bold text-gray-900">Rp {{ formatPrice(method.price) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="card">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Method</h2>
          
          <div class="space-y-3">
            <div
              v-for="method in paymentMethods"
              :key="method.id"
              @click="selectedPayment = method"
              :class="[
                'p-4 rounded-xl border-2 cursor-pointer transition-colors',
                selectedPayment?.id === method.id
                  ? 'border-primary-600 bg-primary-50'
                  : 'border-gray-200 hover:border-gray-300'
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ method.name }}</p>
                  <p class="text-sm text-gray-500">{{ method.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="card">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Order Notes (Optional)</h2>
          <textarea
            v-model="notes"
            rows="3"
            placeholder="Any special instructions for your order?"
            class="input"
          ></textarea>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="lg:col-span-1">
        <div class="card sticky top-24">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>

          <!-- Cart Items -->
          <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
            <div
              v-for="item in cartStore.items"
              :key="item.id"
              class="flex gap-3"
            >
              <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                <img
                  v-if="item.product?.primary_image?.url"
                  :src="item.product.primary_image.url"
                  :alt="item.product?.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ item.product?.name }}</p>
                <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                <p class="text-sm font-bold text-gray-900">Rp {{ formatPrice(item.subtotal) }}</p>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 my-4" />

          <!-- Price Breakdown -->
          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-medium">Rp {{ formatPrice(cartStore.total) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Shipping</span>
              <span class="font-medium">Rp {{ formatPrice(shippingCost) }}</span>
            </div>
          </div>

          <hr class="border-gray-200 my-4" />

          <div class="flex justify-between mb-6">
            <span class="font-bold text-gray-900">Total</span>
            <span class="font-bold text-xl text-gray-900">Rp {{ formatPrice(totalAmount) }}</span>
          </div>

          <!-- Place Order Button -->
          <button
            @click="placeOrder"
            :disabled="!canPlaceOrder || placingOrder"
            class="btn-primary w-full py-3"
          >
            <span v-if="placingOrder">Processing...</span>
            <span v-else>Place Order</span>
          </button>

          <p v-if="!selectedAddress" class="text-xs text-error-600 mt-2 text-center">
            Please select a shipping address
          </p>
          <p v-if="!selectedMethod" class="text-xs text-error-600 mt-2 text-center">
            Please select a shipping method
          </p>
          <p v-if="!selectedPayment" class="text-xs text-error-600 mt-2 text-center">
            Please select a payment method
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ordersApi } from '@/services/api';
import { useCartStore } from '@/stores/cart';

const router = useRouter();
const cartStore = useCartStore();

const addresses = ref([]);
const selectedAddress = ref(null);
const selectedMethod = ref(null);
const selectedPayment = ref(null);
const notes = ref('');
const placingOrder = ref(false);

const shippingMethods = ref([
  { id: 'regular', name: 'Regular Shipping', price: 15000, estimated_days: '3-5' },
  { id: 'express', name: 'Express Shipping', price: 30000, estimated_days: '1-2' },
]);

const paymentMethods = ref([
  { id: 'bank_transfer', name: 'Bank Transfer', description: 'Transfer to our bank account' },
  { id: 'credit_card', name: 'Credit/Debit Card', description: 'Pay with card securely' },
  { id: 'ewallet', name: 'E-Wallet', description: 'Pay with GoPay, OVO, Dana' },
]);

const shippingCost = computed(() => selectedMethod.value?.price || 0);
const totalAmount = computed(() => cartStore.total + shippingCost.value);

const canPlaceOrder = computed(() => {
  return selectedAddress.value && selectedMethod.value && selectedPayment.value && cartStore.itemCount > 0;
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price);
};

const placeOrder = async () => {
  if (!canPlaceOrder.value) return;

  placingOrder.value = true;

  try {
    const response = await ordersApi.create({
      address_id: selectedAddress.value.id,
      shipping_method: selectedMethod.value.id,
      payment_method: selectedPayment.value.id,
      notes: notes.value,
    });

    // Clear cart
    cartStore.setCart(null);

    // Redirect to order success page
    router.push(`/account/success/${response.data.data.order.order_number}`);
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to place order');
  } finally {
    placingOrder.value = false;
  }
};

// Fetch user addresses
onMounted(async () => {
  // TODO: Implement addresses API
  // For now, using mock data
  addresses.value = [
    {
      id: 1,
      name: 'John Doe',
      phone: '081234567890',
      street_address: 'Jl. Example No. 123',
      district: { name: 'Kecamatan Example' },
      city: { name: 'Kota Example' },
      province: { name: 'Provinsi Example' },
      postal_code: '12345',
      is_default: true,
    },
  ];
  selectedAddress.value = addresses.value[0];
  selectedMethod.value = shippingMethods.value[0];
  selectedPayment.value = paymentMethods.value[0];
});
</script>
