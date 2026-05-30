<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4"
  >
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto bg-slate-900 border border-slate-800 rounded-3xl p-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <p class="text-emerald-400 font-semibold text-sm">
            PesoTracker
          </p>

         <h2 class="text-3xl font-bold text-white">
           {{ transaction ? 'Edit Transaction' : currentType === 'income' ? 'Add Income' : 'Add Expense' }}
         </h2>
        </div>

        <button
          @click="closeModal"
          class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300"
        >
          ✕
        </button>
      </div>

      <!-- Form -->
      <form class="space-y-5" @submit.prevent="submitTransaction">
        <div
          v-if="canScanReceipt"
          class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4"
        >
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-semibold text-emerald-300">
                Expense Receipt Scanner
              </p>
              <p class="mt-1 text-sm text-slate-300">
                Open a full-screen camera scanner, then review the draft before saving.
              </p>
            </div>

            <button
              type="button"
              class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-emerald-400"
              @click="openReceiptScanner"
            >
              Scan Receipt
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">
            Title
          </label>

          <input
            v-model="form.title"
            type="text"
            placeholder="e.g. Jollibee Lunch"
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Amount
            </label>

            <input
              v-model="form.amount"
              type="number"
              placeholder="0.00"
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">
              Date
            </label>

            <input
              v-model="form.transaction_date"
              type="date"
              class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">
            Category
          </label>

          <select
            v-model="form.category"
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
          >
            <option disabled value="">Select Category</option>
          
            <template v-if="currentType === 'expense'">
              <option value="Food">Food</option>
              <option value="Transportation">Transportation</option>
              <option value="Bills">Bills</option>
              <option value="Shopping">Shopping</option>
              <option value="Electricity">Electricity</option>
              <option value="Utilities">Utilities</option>
            </template>
          
            <template v-if="currentType === 'income'">
              <option value="Salary">Salary</option>
              <option value="Allowance">Allowance</option>
              <option value="Freelance">Freelance</option>
              <option value="Business">Business</option>
            </template>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">
            Note
          </label>

          <textarea
            v-model="form.note"
            rows="3"
            placeholder="Optional note..."
            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition resize-none"
          ></textarea>
        </div>

        <p v-if="error" class="text-red-400 text-sm font-medium">
          {{ error }}
        </p>

        <button
          type="submit"
          :disabled="saving"
          class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 py-3.5 font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-70"
        >
          <span
            v-if="saving"
            class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
          ></span>
          {{ saving ? 'Saving...' : 'Save Transaction' }}
        </button>
      </form>
    </div>

    <ReceiptScannerModal
      v-if="canScanReceipt"
      :show="showReceiptScanner"
      @close="showReceiptScanner = false"
      @draft="openReceiptDraft"
    />

    <ReceiptDraftForm
      v-if="canScanReceipt"
      :show="showReceiptDraft"
      :draft="receiptDraft"
      @close="showReceiptDraft = false"
      @saved="handleReceiptDraftSaved"
    />
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import api from '../services/api'
import ReceiptScannerModal from './ReceiptScannerModal.vue'
import ReceiptDraftForm from './ReceiptDraftForm.vue'

const saving = ref(false)
const showReceiptScanner = ref(false)
const showReceiptDraft = ref(false)
const receiptDraft = ref(null)

const props = defineProps({
  show: Boolean,
  type: String,
  transaction: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const error = ref('')

const form = reactive({
  title: '',
  amount: '',
  category: '',
  transaction_date: '',
  note: ''
})

watch(
  () => props.show,
  (value) => {
    if (value) {
      if (props.transaction) {
        form.title = props.transaction.title
        form.amount = props.transaction.amount
        form.category = props.transaction.category
        form.transaction_date = props.transaction.transaction_date
        form.note = props.transaction.note
      } else {
        form.title = ''
        form.amount = ''
        form.category = ''
        form.transaction_date = ''
        form.note = ''
      }
    } else {
      closeReceiptScanner()
    }
  }
)

const currentType = computed(() => {
  return props.transaction ? props.transaction.type : props.type
})

const canScanReceipt = computed(() => {
  return currentType.value === 'expense' && !props.transaction
})

watch(canScanReceipt, (value) => {
  if (!value) {
    closeReceiptScanner()
  }
})

const submitTransaction = async () => {
  if (saving.value) return

  error.value = ''
  saving.value = true

  try {

    const payload = {
      ...form,
      type: props.transaction
        ? props.transaction.type
        : props.type
    }

    if (props.transaction) {

      await api.put(
        `/transactions/${props.transaction.id}`,
        payload
      )

    } else {

      await api.post('/transactions', payload)

    }

    emit('saved')
    closeModal()

  } catch (err) {

    error.value =
      err.response?.data?.message ||
      'Failed to save transaction.'
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  closeReceiptScanner()
  emit('close')
}

const openReceiptScanner = () => {
  if (!canScanReceipt.value) return

  showReceiptScanner.value = true
}

const openReceiptDraft = (draft) => {
  if (!canScanReceipt.value) return

  receiptDraft.value = draft
  showReceiptScanner.value = false
  showReceiptDraft.value = true
}

const handleReceiptDraftSaved = () => {
  showReceiptDraft.value = false
  receiptDraft.value = null
  emit('saved')
  closeModal()
}

const closeReceiptScanner = () => {
  showReceiptScanner.value = false
  showReceiptDraft.value = false
  receiptDraft.value = null
}
</script>
