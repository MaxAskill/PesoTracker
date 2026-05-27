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
        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">
            Scan Receipt
          </label>
        
          <div class="grid gap-3">
            <input
              type="file"
              accept="image/*"
              capture="environment"
              @change="handleReceiptUpload"
              class="block w-full text-sm text-slate-400
              file:mr-4 file:py-3 file:px-4
              file:rounded-xl file:border-0
              file:bg-emerald-500 file:text-white
              hover:file:bg-emerald-600"
            />

            <div class="flex flex-wrap items-center gap-3">
              <button
                v-if="!cameraActive"
                type="button"
                :disabled="cameraLoading || ocrLoading"
                @click="startCamera"
                class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500 hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ cameraLoading ? 'Opening camera...' : 'Use Camera' }}
              </button>

              <button
                v-if="cameraActive"
                type="button"
                :disabled="ocrLoading"
                @click="captureReceipt"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-70"
              >
                <span
                  v-if="ocrLoading"
                  class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                ></span>
                {{ ocrLoading ? 'Scanning...' : 'Capture Receipt' }}
              </button>

              <button
                v-if="cameraActive"
                type="button"
                @click="stopCamera"
                class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-700"
              >
                Cancel Camera
              </button>
            </div>

            <div v-if="cameraActive" class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950">
              <video
                ref="cameraVideo"
                autoplay
                muted
                playsinline
                class="aspect-video w-full object-cover"
              ></video>
            </div>

            <canvas ref="cameraCanvas" class="hidden"></canvas>

            <div v-if="ocrLoading" class="text-emerald-400 text-sm">
              Scanning...
            </div>

            <p v-if="cameraError" class="text-sm text-red-400">
              {{ cameraError }}
            </p>
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
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue'
import api from '../services/api'
const ocrLoading = ref(false)
const cameraActive = ref(false)
const cameraError = ref('')
const cameraLoading = ref(false)
const saving = ref(false)
const cameraVideo = ref(null)
const cameraCanvas = ref(null)
let cameraStream = null

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
      cameraError.value = ''

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
      stopCamera()
    }
  }
)

const currentType = computed(() => {
  return props.transaction ? props.transaction.type : props.type
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
  stopCamera()
  emit('close')
}

const handleReceiptUpload = async (event) => {

  const file = event.target.files[0]

  if (!file) return

  await scanReceipt(file)
  event.target.value = ''
}

const startCamera = async () => {
  if (cameraLoading.value || ocrLoading.value) return

  cameraError.value = ''
  cameraLoading.value = true

  if (!navigator.mediaDevices?.getUserMedia) {
    cameraError.value = 'Camera access is not supported in this browser.'
    cameraLoading.value = false
    return
  }

  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: { ideal: 'environment' }
      },
      audio: false
    })

    cameraActive.value = true
    await nextTick()

    if (cameraVideo.value) {
      cameraVideo.value.srcObject = cameraStream
    }
  } catch (err) {
    cameraError.value = 'Could not open the camera. Please allow camera access and try again.'
  } finally {
    cameraLoading.value = false
  }
}

const stopCamera = () => {
  if (cameraStream) {
    cameraStream.getTracks().forEach(track => track.stop())
    cameraStream = null
  }

  if (cameraVideo.value) {
    cameraVideo.value.srcObject = null
  }

  cameraActive.value = false
}

const captureReceipt = async () => {
  if (ocrLoading.value) return

  cameraError.value = ''

  const video = cameraVideo.value
  const canvas = cameraCanvas.value

  if (!video || !canvas || !video.videoWidth || !video.videoHeight) {
    cameraError.value = 'Camera preview is not ready yet.'
    return
  }

  const maxSize = 1600
  const scale = Math.min(maxSize / video.videoWidth, maxSize / video.videoHeight, 1)

  canvas.width = Math.round(video.videoWidth * scale)
  canvas.height = Math.round(video.videoHeight * scale)

  const context = canvas.getContext('2d')
  context.drawImage(video, 0, 0, canvas.width, canvas.height)

  const blob = await new Promise(resolve => {
    canvas.toBlob(resolve, 'image/jpeg', 0.92)
  })

  if (!blob) {
    cameraError.value = 'Could not capture the receipt image.'
    return
  }

  const file = new File([blob], `receipt-${Date.now()}.jpg`, {
    type: 'image/jpeg'
  })

  await scanReceipt(file)
  stopCamera()
}

const scanReceipt = async (file) => {
  if (ocrLoading.value) return

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
    cameraError.value = 'Receipt scanning failed. Try a clearer photo with good lighting.'

  } finally {

    ocrLoading.value = false

  }
}

const formatDate = (dateString) => {

  const parts = dateString.split(/[\/\-]/)

  if (parts.length !== 3) return ''

  return `${parts[2]}-${parts[0]}-${parts[1]}`
}

onBeforeUnmount(() => {
  stopCamera()
})
</script>
