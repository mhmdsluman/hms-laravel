<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  bill: Object,
  flash: Object,
});

// UI state
const showPaymentModal = ref(false);
const showDiscountModal = ref(false);

// Computed balance due (prefers patient_co_pay when available)
const balanceDue = computed(() => {
  const total = (props.bill?.patient_co_pay ?? props.bill?.total_amount ?? 0);
  const discount = props.bill?.discount_amount ?? 0;
  const paid = props.bill?.paid_amount ?? 0;
  return Math.max(0, total - discount - paid);
});

// Forms (useForm for Inertia-friendly validation/requests)
const paymentForm = useForm({
  paid_amount: balanceDue.value,
  payment_method: 'Cash',
});

const discountForm = useForm({
  discount_amount: 0,
  discount_reason: '',
});

// Open payment modal and set current suggested amount
function openPaymentModal() {
  paymentForm.paid_amount = balanceDue.value;
  showPaymentModal.value = true;
}

// Submit payment
function submitPayment() {
  paymentForm.post(route('billing.recordPayment', props.bill.id), {
    onStart: () => {},
    onSuccess: () => {
      showPaymentModal.value = false;
      // refresh suggested amount
      paymentForm.paid_amount = balanceDue.value;
    },
    onError: () => {},
    onFinish: () => {},
  });
}

// Submit discount
function submitDiscount() {
  // client-side guard
  if (Number(discountForm.discount_amount) <= 0) {
    discountForm.errors.set('discount_amount', ['Discount must be greater than zero.']);
    return;
  }

  discountForm.post(route('billing.applyDiscount', props.bill.id), {
    onStart: () => {},
    onSuccess: () => {
      showDiscountModal.value = false;
      discountForm.reset();
      // update payment suggested amount
      paymentForm.paid_amount = balanceDue.value;
    },
    onError: () => {},
    onFinish: () => {},
  });
}

// Void bill (admin only)
function voidBill() {
  if (confirm('Are you sure you want to void this bill?')) {
    router.delete(route('billing.destroy', { bill: props.bill.id }));
  }
}
</script>

<template>
  <Head :title="`Bill #${bill.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bill #{{ bill.id }}</h2>
          <div class="mt-1 text-sm text-gray-500">
            Patient:
            <template v-if="bill.patient">
              {{ bill.patient.full_name }} ({{ bill.patient.patient_no ?? '—' }})
            </template>
            <template v-else>
              —
            </template>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <a v-if="bill && bill.id" :href="(function(){ try { return route('print.billInvoice', { bill: bill.id }) } catch(e){ return '/print/bill/' + bill.id } })()" target="_blank" class="px-3 py-2 bg-gray-600 text-white rounded-md text-sm font-medium hover:bg-gray-700">
            Print Invoice
          </a>

          <Link :href="route('billing.index')" class="text-sm text-blue-600 hover:underline">Back to Billing</Link>

          <div v-if="$page.props.auth.user.role === 'admin'">
            <button @click="voidBill" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 ml-2">
              Void Bill
            </button>
          </div>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <div v-if="flash?.success" class="mb-4 text-green-600">{{ flash.success }}</div>

          <div class="mb-6">
            <div class="flex justify-between">
              <span class="font-medium">Status:</span>
              <span :class="{
                'text-green-600': bill.status === 'Paid',
                'text-red-600': bill.status === 'Unpaid',
                'text-gray-500': bill.status === 'Void'
              }">{{ bill.status }}</span>
            </div>

            <div class="flex justify-between mt-2"><span class="font-medium">Total Amount:</span><span>${{ (bill.total_amount ?? 0).toFixed(2) }}</span></div>
            <div class="flex justify-between mt-2"><span class="font-medium">Patient Co-Pay:</span><span>${{ (bill.patient_co_pay ?? 0).toFixed(2) }}</span></div>
            <div class="flex justify-between mt-2"><span class="font-medium">Discount Applied:</span><span>${{ (bill.discount_amount ?? 0).toFixed(2) }}</span></div>
            <div class="flex justify-between mt-2"><span class="font-medium">Balance Due:</span><span>${{ balanceDue.toFixed(2) }}</span></div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Bill Items</h3>
            <table class="w-full border border-gray-200">
              <thead class="bg-gray-100">
                <tr>
                  <th class="border px-3 py-2 text-left">Service</th>
                  <th class="border px-3 py-2">Qty</th>
                  <th class="border px-3 py-2">Unit Price</th>
                  <th class="border px-3 py-2">Total Price</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in bill.items" :key="item.id">
                  <td class="border px-3 py-2">{{ item.service.name }}</td>
                  <td class="border px-3 py-2 text-center">{{ item.quantity }}</td>
                  <td class="border px-3 py-2 text-right">${{ item.unit_price.toFixed(2) }}</td>
                  <td class="border px-3 py-2 text-right">${{ item.total_price.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-end gap-2">
            <button v-if="bill.status !== 'Paid' && bill.status !== 'Void'" @click="openPaymentModal" class="px-4 py-2 bg-blue-600 text-white rounded">Make Payment</button>
            <button v-if="$page.props.auth.user.role === 'admin'" @click="showDiscountModal = true" class="px-4 py-2 bg-yellow-500 text-white rounded">Apply Discount</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Make Payment</h3>

        <form @submit.prevent="submitPayment">
          <div v-if="Object.keys(paymentForm.errors).length" class="mb-4 text-red-600">
            <div v-for="(errs, key) in paymentForm.errors" :key="key">{{ errs.join(' ') }}</div>
          </div>

          <div class="mb-4">
            <label class="block mb-1 font-medium">Payment Method</label>
            <select v-model="paymentForm.payment_method" class="w-full border rounded px-3 py-2">
              <option value="Cash">Cash</option>
              <option value="Card">Card</option>
              <option value="Insurance">Insurance</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block mb-1 font-medium">Amount</label>
            <input type="number" v-model.number="paymentForm.paid_amount" step="0.01" class="w-full border rounded px-3 py-2" />
            <div class="text-sm text-gray-500 mt-1">Suggested: ${{ balanceDue.toFixed(2) }}</div>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" @click="showPaymentModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="paymentForm.processing">
              <span v-if="paymentForm.processing">Processing…</span>
              <span v-else>Submit Payment</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Discount Modal -->
    <div v-if="showDiscountModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h3 class="text-lg font-medium mb-4">Apply Discount</h3>

        <form @submit.prevent="submitDiscount">
          <div v-if="Object.keys(discountForm.errors).length" class="mb-4 text-red-600">
            <div v-for="(errs, key) in discountForm.errors" :key="key">{{ errs.join(' ') }}</div>
          </div>

          <div class="mb-4">
            <label class="block mb-1 font-medium">Discount Amount</label>
            <input type="number" v-model.number="discountForm.discount_amount" step="0.01" :max="balanceDue" class="w-full border rounded px-3 py-2" />
            <div class="text-sm text-gray-500 mt-1">Max: ${{ balanceDue.toFixed(2) }}</div>
          </div>

          <div class="mb-4">
            <label class="block mb-1 font-medium">Reason</label>
            <input type="text" v-model="discountForm.discount_reason" class="w-full border rounded px-3 py-2" />
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" @click="showDiscountModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded" :disabled="discountForm.processing">
              <span v-if="discountForm.processing">Applying…</span>
              <span v-else>Apply Discount</span>
            </button>
          </div>
        </form>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<style scoped>
/* basic modal fade / alignment tweaks */
</style>
