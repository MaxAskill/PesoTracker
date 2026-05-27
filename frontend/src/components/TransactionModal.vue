<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 px-4"
  >
    <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-8">
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
          @click="$emit('close')"
          class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300"
        >
          ✕
        </button>
      </div>

      <!-- Form -->
      <form class="space-y-5" @submit.prevent="submitTransaction">
        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">
            Scan Receipt
          </label>
        
          <div class="flex items-center gap-4">
            <input
              type="file"
              accept="image/*"
              @change="handleReceiptUpload"
              class="block w-full text-sm text-slate-400
              file:mr-4 file:py-3 file:px-4
              file:rounded-xl file:border-0
              file:bg-emerald-500 file:text-white
              hover:file:bg-emerald-600"
            />
        
            <div v-if="ocrLoading" class="text-emerald-400 text-sm">
              Scanning...
            </div>
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
          class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition"
        >
          Save Transaction
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import api from '../services/api'
const ocrLoading = ref(false)

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
    }
  }
)

const currentType = computed(() => {
  return props.transaction ? props.transaction.type : props.type
})

const submitTransaction = async () => {
  error.value = ''

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
    emit('close')

  } catch (err) {

    error.value =
      err.response?.data?.message ||
      'Failed to save transaction.'
  }
}

const handleReceiptUpload = async (event) => {

  const file = event.target.files[0]

  if (!file) return

  const formData = new FormData()

  formData.append('receipt', file)

  ocrLoading.value = true

  try {

    const response = await api.post(
      '/receipts/scan',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    const data = response.data

    if (data.store_name) {
      form.title = data.store_name
    }

    if (data.amount) {
      form.amount = data.amount
    }

    if (data.date) {
      form.transaction_date = formatDate(data.date)
    }

    if (data.category) {
      form.category = data.category
    }

  } catch (error) {

    console.error(error)

  } finally {

    ocrLoading.value = false

  }
}

const formatDate = (dateString) => {

  const parts = dateString.split(/[\/\-]/)

  if (parts.length !== 3) return ''

  return `${parts[2]}-${parts[0]}-${parts[1]}`
}
</script>