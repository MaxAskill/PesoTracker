<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[75] overflow-y-auto bg-slate-950 text-white"
  >
    <div class="mx-auto flex min-h-full w-full max-w-3xl flex-col p-4 sm:p-6">
      <header class="mb-6 flex items-center justify-between gap-4">
        <div>
          <p class="text-sm font-semibold text-emerald-400">
            Review before saving
          </p>
          <h2 class="text-2xl font-bold sm:text-3xl">
            Expense Receipt Draft
          </h2>
        </div>

        <button
          type="button"
          class="h-11 w-11 rounded-2xl bg-slate-800 text-slate-300 transition hover:bg-slate-700"
          @click="emit('close')"
        >
          x
        </button>
      </header>

      <div class="mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-100">
        We prepared a draft from your receipt. Please review before saving.
      </div>

      <div class="grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900">
          <img
            v-if="form.receipt_image_url"
            :src="form.receipt_image_url"
            alt="Receipt preview"
            class="max-h-[70vh] w-full object-contain"
          />

          <div v-else class="flex h-80 items-center justify-center text-slate-500">
            No receipt preview.
          </div>
        </div>

        <form class="space-y-5 rounded-3xl border border-slate-800 bg-slate-900 p-5 sm:p-6" @submit.prevent="saveDraft">
          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-300">
              Type
            </label>
            <input
              value="Expense"
              disabled
              class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-slate-400"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-300">
              Amount
            </label>
            <input
              v-model="form.amount"
              type="number"
              min="0.01"
              step="0.01"
              placeholder="0.00"
              class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
            />
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
              Merchant / Description
            </label>
            <input
              v-model="form.merchant"
              type="text"
              placeholder="e.g. Grocery store"
              class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-300">
              Date
            </label>
            <input
              v-model="form.date"
              type="date"
              class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-semibold text-slate-300">
              Notes
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Optional notes..."
              class="w-full resize-none rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
            ></textarea>
          </div>

          <p v-if="error" class="text-sm font-medium text-red-400">
            {{ error }}
          </p>

          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              class="rounded-xl bg-slate-800 py-3.5 font-bold text-slate-100 transition hover:bg-slate-700"
              @click="emit('close')"
            >
              Back
            </button>

            <button
              type="submit"
              :disabled="saving"
              class="rounded-xl bg-emerald-500 py-3.5 font-bold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ saving ? 'Saving...' : 'Save Expense' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import api from '../services/api'
import AppSelect from './AppSelect.vue'

const props = defineProps({
  show: Boolean,
  draft: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const saving = ref(false)
const error = ref('')
const form = reactive({
  amount: '',
  category: '',
  merchant: '',
  date: '',
  notes: '',
  receipt_image_url: ''
})

const categoryOptions = [
  { label: 'Food', value: 'Food' },
  { label: 'Transportation', value: 'Transportation' },
  { label: 'Bills', value: 'Bills' },
  { label: 'Shopping', value: 'Shopping' },
  { label: 'Electricity', value: 'Electricity' },
  { label: 'Utilities', value: 'Utilities' }
]

watch(
  () => props.draft,
  (draft) => {
    if (!draft) return

    form.amount = draft.amount ?? ''
    form.category = draft.category ?? ''
    form.merchant = draft.merchant ?? ''
    form.date = draft.date ?? new Date().toISOString().slice(0, 10)
    form.notes = draft.notes ?? 'Created from receipt scanner'
    form.receipt_image_url = draft.receipt_image_url ?? ''
    error.value = ''
  },
  { immediate: true }
)

const saveDraft = async () => {
  if (saving.value) return

  error.value = ''
  saving.value = true

  try {
    await api.post('/transactions', {
      type: 'expense',
      title: form.merchant || 'Receipt expense',
      amount: form.amount,
      category: form.category,
      transaction_date: form.date,
      note: form.notes
    })

    emit('saved')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save expense. Please review the required fields.'
  } finally {
    saving.value = false
  }
}
</script>
