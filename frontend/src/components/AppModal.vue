<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-[130] flex items-center justify-center bg-slate-950/85 px-4 py-5 backdrop-blur-md sm:py-6"
      >
        <div
          class="motion-scale-in relative z-[140] max-h-[92vh] w-full overflow-y-auto rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-950 via-slate-950 to-slate-900 p-6 text-white shadow-2xl shadow-black/60 sm:max-h-[90vh] sm:p-8"
          :class="sizeClass"
          @click.stop
        >
          <div class="mb-7 flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">
                {{ label }}
              </p>
              <h2 class="mt-1 text-2xl font-black text-white sm:text-3xl">
                {{ title }}
              </h2>
              <p v-if="subtitle" class="mt-2 text-sm text-slate-400">
                {{ subtitle }}
              </p>
            </div>

            <button
              type="button"
              aria-label="Close modal"
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-slate-900 text-slate-300 transition hover:bg-slate-800 hover:text-white"
              @click="$emit('close')"
            >
              X
            </button>
          </div>

          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  label: {
    type: String,
    default: 'PesoTracker'
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md'
  }
})

defineEmits(['close'])

const setBodyScrollLock = (value) => {
  document.body.classList.toggle('overflow-hidden', value)
}

watch(() => props.show, (value) => {
  if (value) {
    window.dispatchEvent(new CustomEvent('pesotracker-modal-open'))
    window.dispatchEvent(new CustomEvent('pesotracker-close-sidebar'))
    setBodyScrollLock(true)
    return
  }

  setBodyScrollLock(false)
}, { immediate: true })

onBeforeUnmount(() => {
  setBodyScrollLock(false)
})

const sizeClass = computed(() => {
  return {
    sm: 'max-w-md',
    md: 'max-w-xl',
    lg: 'max-w-2xl'
  }[props.size] || 'max-w-xl'
})
</script>
