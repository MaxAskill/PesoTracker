<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 py-6 backdrop-blur-md"
  >
    <div
      class="max-h-[90vh] w-full overflow-y-auto rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-950 via-slate-950 to-slate-900 p-6 text-white shadow-2xl shadow-black/60 sm:p-8"
      :class="sizeClass"
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
</template>

<script setup>
import { computed } from 'vue'

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

const sizeClass = computed(() => {
  return {
    sm: 'max-w-md',
    md: 'max-w-xl',
    lg: 'max-w-2xl'
  }[props.size] || 'max-w-xl'
})
</script>
