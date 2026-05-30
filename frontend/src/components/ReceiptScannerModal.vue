<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[70] bg-slate-950 text-white"
  >
    <div class="relative flex h-full min-h-0 flex-col overflow-hidden">
      <video
        v-show="cameraReady && !capturedPreviewUrl"
        ref="video"
        autoplay
        muted
        playsinline
        class="absolute inset-0 h-full w-full object-cover"
      ></video>

      <img
        v-if="capturedPreviewUrl"
        :src="capturedPreviewUrl"
        alt="Captured receipt preview"
        class="absolute inset-0 h-full w-full object-contain bg-slate-950"
      />

      <div class="absolute inset-0 bg-black/35"></div>

      <div class="relative z-10 flex h-full flex-col">
        <header class="flex items-center justify-between px-4 py-4 sm:px-6">
          <button
            type="button"
            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950/70 text-lg font-bold text-white backdrop-blur transition hover:bg-slate-800"
            @click="closeScanner"
          >
            x
          </button>

          <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">
              Expense Receipt
            </p>
            <h2 class="text-base font-bold text-white sm:text-lg">
              {{ capturedPreviewUrl ? 'Review Photo' : 'Scan Receipt' }}
            </h2>
          </div>

          <button
            type="button"
            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950/70 text-sm font-bold text-white backdrop-blur transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="!canSwitchCamera || capturedPreviewUrl"
            @click="switchCamera"
          >
            Flip
          </button>
        </header>

        <main class="flex min-h-0 flex-1 items-center justify-center px-5">
          <div
            v-if="!capturedPreviewUrl"
            class="relative flex aspect-[3/4] w-full max-w-sm items-center justify-center rounded-[2rem] border-2 border-emerald-300/80 shadow-[0_0_40px_rgba(16,185,129,0.28)]"
          >
            <span class="absolute -top-12 text-center text-sm font-semibold text-white">
              Align your receipt inside the frame
            </span>
            <span class="absolute -bottom-10 text-center text-xs text-slate-300">
              Make sure the amount and date are visible
            </span>
            <div class="absolute left-4 top-4 h-10 w-10 rounded-tl-3xl border-l-4 border-t-4 border-emerald-300"></div>
            <div class="absolute right-4 top-4 h-10 w-10 rounded-tr-3xl border-r-4 border-t-4 border-emerald-300"></div>
            <div class="absolute bottom-4 left-4 h-10 w-10 rounded-bl-3xl border-b-4 border-l-4 border-emerald-300"></div>
            <div class="absolute bottom-4 right-4 h-10 w-10 rounded-br-3xl border-b-4 border-r-4 border-emerald-300"></div>
          </div>

          <div
            v-if="statusMessage"
            class="absolute left-4 right-4 top-24 rounded-2xl border border-slate-700 bg-slate-950/85 p-4 text-center text-sm text-slate-200 backdrop-blur sm:left-1/2 sm:w-96 sm:-translate-x-1/2"
          >
            {{ statusMessage }}
          </div>

          <div
            v-if="error"
            class="absolute left-4 right-4 top-24 rounded-2xl border border-red-500/30 bg-red-500/15 p-4 text-center text-sm text-red-100 backdrop-blur sm:left-1/2 sm:w-96 sm:-translate-x-1/2"
          >
            <p>{{ error }}</p>
            <button
              type="button"
              class="mt-3 rounded-xl bg-slate-900 px-4 py-2 font-semibold text-white"
              @click="openGallery"
            >
              Upload instead
            </button>
          </div>
        </main>

        <footer class="relative z-10 border-t border-white/10 bg-slate-950/80 px-4 py-5 backdrop-blur sm:px-6">
          <div v-if="capturedPreviewUrl" class="grid grid-cols-2 gap-3">
            <button
              type="button"
              class="rounded-2xl bg-slate-800 px-4 py-4 font-bold text-slate-100 transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="processing"
              @click="retake"
            >
              Retake
            </button>

            <button
              type="button"
              class="rounded-2xl bg-emerald-500 px-4 py-4 font-bold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="processing"
              @click="usePhoto"
            >
              {{ processing ? 'Processing...' : 'Use Photo' }}
            </button>
          </div>

          <div v-else class="grid grid-cols-[1fr_auto_1fr] items-center gap-4">
            <button
              type="button"
              class="rounded-2xl bg-slate-800 px-4 py-3 text-sm font-bold text-slate-100 transition hover:bg-slate-700"
              @click="openGallery"
            >
              Upload
            </button>

            <button
              type="button"
              aria-label="Capture receipt"
              class="h-20 w-20 rounded-full border-4 border-white bg-emerald-400 shadow-2xl shadow-emerald-500/30 transition hover:bg-emerald-300 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="!cameraReady || processing"
              @click="capture"
            ></button>

            <button
              type="button"
              class="rounded-2xl bg-slate-800 px-4 py-3 text-sm font-bold text-slate-100 transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="!canUseTorch"
              @click="toggleTorch"
            >
              {{ torchOn ? 'Flash Off' : 'Flash' }}
            </button>
          </div>
        </footer>

        <canvas ref="canvas" class="hidden"></canvas>
        <input
          ref="fileInput"
          type="file"
          accept="image/jpeg,image/jpg,image/png,image/webp"
          class="hidden"
          @change="handleUpload"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import api from '../services/api'

const props = defineProps({
  show: Boolean
})

const emit = defineEmits(['close', 'draft'])

const video = ref(null)
const canvas = ref(null)
const fileInput = ref(null)
const cameraReady = ref(false)
const processing = ref(false)
const error = ref('')
const statusMessage = ref('')
const capturedPreviewUrl = ref('')
const capturedFile = ref(null)
const videoDevices = ref([])
const activeDeviceId = ref('')
const canUseTorch = ref(false)
const torchOn = ref(false)
let stream = null

const canSwitchCamera = computed(() => videoDevices.value.length > 1)

watch(
  () => props.show,
  async (value) => {
    if (value) {
      await startCamera()
    } else {
      resetScanner()
    }
  }
)

const startCamera = async (deviceId = '') => {
  error.value = ''
  statusMessage.value = 'Opening camera...'
  cameraReady.value = false

  releasePreview()
  stopCamera()

  if (!navigator.mediaDevices?.getUserMedia) {
    error.value = 'Browser does not support camera access. You can upload a receipt image instead.'
    statusMessage.value = ''
    return
  }

  if (!window.isSecureContext && !['localhost', '127.0.0.1'].includes(window.location.hostname)) {
    error.value = 'Camera access requires HTTPS. You can upload a receipt image instead.'
    statusMessage.value = ''
    return
  }

  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: deviceId
        ? { deviceId: { exact: deviceId } }
        : { facingMode: { ideal: 'environment' } },
      audio: false
    })

    await nextTick()

    if (video.value) {
      video.value.srcObject = stream
    }

    await loadDevices()
    updateTrackCapabilities()

    cameraReady.value = true
    statusMessage.value = ''
  } catch (err) {
    error.value = cameraErrorMessage(err)
    statusMessage.value = ''
  }
}

const loadDevices = async () => {
  const devices = await navigator.mediaDevices.enumerateDevices()
  videoDevices.value = devices.filter(device => device.kind === 'videoinput')
  activeDeviceId.value = stream?.getVideoTracks()[0]?.getSettings()?.deviceId || ''
}

const updateTrackCapabilities = () => {
  const track = stream?.getVideoTracks()[0]
  const capabilities = track?.getCapabilities?.()

  canUseTorch.value = Boolean(capabilities?.torch)
  torchOn.value = false
}

const switchCamera = async () => {
  if (!canSwitchCamera.value) return

  const currentIndex = videoDevices.value.findIndex(device => device.deviceId === activeDeviceId.value)
  const nextDevice = videoDevices.value[(currentIndex + 1) % videoDevices.value.length]

  if (nextDevice) {
    await startCamera(nextDevice.deviceId)
  }
}

const toggleTorch = async () => {
  const track = stream?.getVideoTracks()[0]

  if (!track || !canUseTorch.value) return

  try {
    await track.applyConstraints({
      advanced: [{ torch: !torchOn.value }]
    })
    torchOn.value = !torchOn.value
  } catch (err) {
    error.value = 'Flashlight is not available on this camera.'
  }
}

const capture = async () => {
  const videoElement = video.value
  const canvasElement = canvas.value

  if (!videoElement || !canvasElement || !videoElement.videoWidth || !videoElement.videoHeight) {
    error.value = 'Camera preview is not ready yet.'
    return
  }

  const maxSize = 1800
  const scale = Math.min(maxSize / videoElement.videoWidth, maxSize / videoElement.videoHeight, 1)

  canvasElement.width = Math.round(videoElement.videoWidth * scale)
  canvasElement.height = Math.round(videoElement.videoHeight * scale)
  canvasElement.getContext('2d').drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height)

  const blob = await new Promise(resolve => {
    canvasElement.toBlob(resolve, 'image/jpeg', 0.9)
  })

  if (!blob) {
    error.value = 'Could not capture the receipt image.'
    return
  }

  capturedFile.value = new File([blob], `receipt-${Date.now()}.jpg`, {
    type: 'image/jpeg'
  })
  capturedPreviewUrl.value = URL.createObjectURL(blob)
  stopCamera()
}

const handleUpload = (event) => {
  const file = event.target.files?.[0]

  if (!file) return

  releasePreview()
  capturedFile.value = file
  capturedPreviewUrl.value = URL.createObjectURL(file)
  stopCamera()
  event.target.value = ''
}

const openGallery = () => {
  fileInput.value?.click()
}

const retake = async () => {
  capturedFile.value = null
  releasePreview()
  await startCamera(activeDeviceId.value)
}

const usePhoto = async () => {
  if (!capturedFile.value || processing.value) return

  const formData = new FormData()
  formData.append('receipt', capturedFile.value)

  processing.value = true
  error.value = ''

  try {
    const response = await api.post('/receipts/scan', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    emit('draft', response.data.draft)
    resetScanner()
  } catch (err) {
    error.value = err.response?.data?.message || 'Receipt upload failed. Please try another photo.'
  } finally {
    processing.value = false
  }
}

const closeScanner = () => {
  resetScanner()
  emit('close')
}

const resetScanner = () => {
  stopCamera()
  releasePreview()
  capturedFile.value = null
  cameraReady.value = false
  processing.value = false
  error.value = ''
  statusMessage.value = ''
}

const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop())
    stream = null
  }

  if (video.value) {
    video.value.srcObject = null
  }

  canUseTorch.value = false
  torchOn.value = false
}

const releasePreview = () => {
  if (capturedPreviewUrl.value) {
    URL.revokeObjectURL(capturedPreviewUrl.value)
  }

  capturedPreviewUrl.value = ''
}

const cameraErrorMessage = (err) => {
  if (err?.name === 'NotAllowedError' || err?.name === 'SecurityError') {
    return 'Camera permission was denied. You can allow camera access or upload a receipt image instead.'
  }

  if (err?.name === 'NotFoundError' || err?.name === 'OverconstrainedError') {
    return 'No camera was found. You can upload a receipt image instead.'
  }

  return 'Camera is not available. You can upload a receipt image instead.'
}

onBeforeUnmount(() => {
  resetScanner()
})
</script>
