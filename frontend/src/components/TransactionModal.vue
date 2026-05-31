<template>
  <AppModal
    :show="show"
    :title="transaction ? 'Edit Transaction' : currentType === 'income' ? 'Add Income' : 'Add Expense'"
    :subtitle="currentType === 'expense' ? 'Record spending and optionally scan a receipt.' : 'Record income manually.'"
    size="md"
    @close="closeModal"
  >
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
            class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-emerald-400"
            @click="openReceiptScanner"
          >
            Scan Receipt
          </button>
        </div>
      </div>

      <div>
        <label class="mb-2 block text-sm font-semibold text-slate-300">
          Title
        </label>

        <input
          v-model="form.title"
          type="text"
          placeholder="e.g. Jollibee Lunch"
          class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
        />
      </div>

      <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-300">
            Amount
          </label>

          <AppMoneyInput
            v-model="form.amount"
            placeholder="0.00"
          />
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-300">
            Date
          </label>

          <AppDatePicker
            v-model="form.transaction_date"
            placeholder="Select date"
          />
        </div>
      </div>

      <div>
        <label class="mb-2 block text-sm font-semibold text-slate-300">
          Category
        </label>

        <AppSelect
          v-model="form.category"
          :options="categoryOptions"
          placeholder="Select Category"
        />
      </div>

      <div>
        <label class="mb-2 block text-sm font-semibold text-slate-300">
          Note
        </label>

        <textarea
          v-model="form.note"
          rows="3"
          placeholder="Optional note..."
          class="w-full resize-none rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
        ></textarea>
      </div>

      <p v-if="error" class="text-sm font-medium text-red-400">
        {{ error }}
      </p>

      <button
        type="submit"
        :disabled="saving"
        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 py-3.5 font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70"
      >
        <span
          v-if="saving"
          class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"
        ></span>
        {{ saving ? 'Saving...' : 'Save Transaction' }}
      </button>
    </form>

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
  </AppModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import api from '../services/api'
import AppDatePicker from './AppDatePicker.vue'
import AppMoneyInput from './AppMoneyInput.vue'
import AppSelect from './AppSelect.vue'
import AppModal from './AppModal.vue'
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
        form.amount = String(props.transaction.amount ?? '')
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

const categoryOptions = computed(() => {
  const options = currentType.value === 'income'
    ? ['Salary', 'Allowance', 'Freelance', 'Business']
    : ['Food', 'Transportation', 'Bills', 'Shopping', 'Electricity', 'Utilities']

  return options.map(category => ({
    label: category,
    value: category
  }))
})

watch(canScanReceipt, (value) => {
  if (!value) {
    closeReceiptScanner()
  }
})

const validateAmount = () => {
  const value = String(form.amount ?? '').trim()

  if (!value) {
    error.value = 'Enter a valid amount.'
    return false
  }

  if (!/^\d+(\.\d{1,2})?$/.test(value)) {
    error.value = 'Use up to 2 decimal places only.'
    return false
  }

  if (Number(value) <= 0) {
    error.value = 'Amount must be greater than 0.'
    return false
  }

  form.amount = value
  return true
}

const submitTransaction = async () => {
  if (saving.value) return

  error.value = ''

  if (!validateAmount()) return

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
