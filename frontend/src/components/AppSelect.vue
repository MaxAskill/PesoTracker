<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="flex h-12 w-full items-center justify-between gap-3 rounded-2xl border border-slate-700/70 bg-slate-950/90 px-4 text-left text-sm text-slate-100 shadow-lg shadow-slate-950/10 transition hover:border-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10"
      :aria-expanded="isOpen"
      @click="toggle"
      @keydown.escape.prevent="close"
    >
      <span class="truncate" :class="selectedLabel ? 'text-slate-100' : 'text-slate-400'">
        {{ selectedLabel || placeholder }}
      </span>
      <svg
        class="h-4 w-4 shrink-0 text-slate-400 transition"
        :class="isOpen ? 'rotate-180 text-emerald-300' : ''"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true"
      >
        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="-translate-y-1 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="-translate-y-1 opacity-0"
    >
      <div
        v-if="isOpen"
        class="absolute left-0 right-0 z-50 mt-2 max-h-60 overflow-y-auto rounded-2xl border border-slate-700/70 bg-slate-950 p-2 shadow-2xl shadow-black/50"
      >
        <button
          v-for="option in normalizedOptions"
          :key="`${option.value}-${option.label}`"
          type="button"
          class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition"
          :class="option.value === modelValue ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'"
          @click="selectOption(option.value)"
        >
          <span class="truncate">{{ option.label }}</span>
          <svg
            v-if="option.value === modelValue"
            class="h-4 w-4 shrink-0 text-emerald-300"
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
          >
            <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, null],
    default: ''
  },
  options: {
    type: Array,
    required: true
  },
  placeholder: {
    type: String,
    default: 'Select option'
  }
})

const emit = defineEmits(['update:modelValue'])

const root = ref(null)
const isOpen = ref(false)

const normalizedOptions = computed(() => {
  return props.options.map((option) => {
    if (typeof option === 'object') {
      return {
        label: option.label,
        value: option.value
      }
    }

    return {
      label: String(option),
      value: option
    }
  })
})

const selectedLabel = computed(() => {
  return normalizedOptions.value.find(option => option.value === props.modelValue)?.label || ''
})

const toggle = () => {
  isOpen.value = !isOpen.value
}

const close = () => {
  isOpen.value = false
}

const selectOption = (value) => {
  emit('update:modelValue', value)
  close()
}

const handleDocumentClick = (event) => {
  if (!root.value?.contains(event.target)) {
    close()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    close()
  }
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('keydown', handleEscape)
})
</script>
