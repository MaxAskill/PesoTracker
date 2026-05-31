<template>
  <div ref="root" class="relative">
    <button
      ref="buttonRef"
      type="button"
      class="flex h-12 w-full items-center justify-between gap-3 rounded-2xl border border-slate-700/70 bg-slate-950/90 px-4 text-left text-sm text-slate-100 shadow-lg shadow-slate-950/10 transition hover:border-slate-500 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-400/10"
      :class="[
        disabled ? 'cursor-not-allowed opacity-60 hover:border-slate-700/70' : '',
        error ? 'border-red-400/60 focus:border-red-400 focus:ring-red-400/10' : ''
      ]"
      :aria-expanded="isOpen"
      :disabled="disabled"
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

    <Teleport to="body">
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
          ref="menuRef"
          class="fixed z-[95] max-h-60 overflow-y-auto rounded-2xl border border-slate-700/70 bg-slate-950 p-2 shadow-2xl shadow-black/50"
          :style="menuStyle"
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
    </Teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

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
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const root = ref(null)
const buttonRef = ref(null)
const menuRef = ref(null)
const isOpen = ref(false)
const menuStyle = ref({})

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
  if (props.disabled) return

  isOpen.value = !isOpen.value

  if (isOpen.value) {
    nextTick(updateMenuPosition)
  }
}

const close = () => {
  isOpen.value = false
}

const selectOption = (value) => {
  emit('update:modelValue', value)
  close()
}

const updateMenuPosition = () => {
  const rect = buttonRef.value?.getBoundingClientRect()
  if (!rect) return

  const spaceBelow = window.innerHeight - rect.bottom
  const openAbove = spaceBelow < 260 && rect.top > spaceBelow

  menuStyle.value = {
    left: `${rect.left}px`,
    top: openAbove ? 'auto' : `${rect.bottom + 8}px`,
    bottom: openAbove ? `${window.innerHeight - rect.top + 8}px` : 'auto',
    width: `${rect.width}px`
  }
}

const handleDocumentClick = (event) => {
  if (!root.value?.contains(event.target) && !menuRef.value?.contains(event.target)) {
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
  window.addEventListener('resize', updateMenuPosition)
  window.addEventListener('scroll', updateMenuPosition, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('keydown', handleEscape)
  window.removeEventListener('resize', updateMenuPosition)
  window.removeEventListener('scroll', updateMenuPosition, true)
})
</script>
