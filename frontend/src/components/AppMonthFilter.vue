<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="month-filter-button"
      :class="modelValue ? 'border-emerald-400/40 bg-emerald-500/10' : ''"
      @click="toggle"
    >
      <span class="month-filter-icon" aria-hidden="true">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
          <path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </span>

      <span class="min-w-0 flex-1 text-left">
        <span class="block text-[0.68rem] font-bold uppercase tracking-wide text-slate-500">
          {{ modelValue ? 'Month' : 'Filter by month' }}
        </span>
        <span class="mt-0.5 block truncate text-sm font-bold" :class="modelValue ? 'text-emerald-100' : 'text-slate-300'">
          {{ modelValue ? formattedValue : 'All months' }}
        </span>
      </span>

      <button
        v-if="modelValue"
        type="button"
        class="month-filter-clear"
        aria-label="Clear month filter"
        @click.stop="clear"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" />
        </svg>
      </button>

      <svg v-else class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
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
        class="absolute left-0 right-0 top-full z-50 mt-3 overflow-hidden rounded-3xl border border-slate-700/70 bg-slate-950 p-4 shadow-2xl shadow-black/50"
      >
        <div class="flex items-center justify-between gap-3">
          <button type="button" class="year-button" aria-label="Previous year" @click="activeYear -= 1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Choose Month</p>
            <p class="mt-1 text-lg font-black text-white">{{ activeYear }}</p>
          </div>
          <button type="button" class="year-button" aria-label="Next year" @click="activeYear += 1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2">
          <button
            v-for="(month, index) in monthNames"
            :key="month"
            type="button"
            class="month-option"
            :class="isSelected(index) ? 'border-emerald-300/50 bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/20' : 'border-slate-800 bg-slate-900/80 text-slate-300 hover:border-emerald-400/30 hover:bg-emerald-500/10 hover:text-emerald-200'"
            @click="selectMonth(index)"
          >
            {{ month.slice(0, 3) }}
          </button>
        </div>

        <button
          type="button"
          class="mt-4 w-full rounded-2xl border border-slate-800 bg-slate-900/80 px-4 py-3 text-sm font-bold text-slate-300 transition hover:border-emerald-400/30 hover:text-emerald-200"
          @click="clear"
        >
          Clear month filter
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const monthNames = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December'
]

const root = ref(null)
const isOpen = ref(false)
const activeYear = ref(new Date().getFullYear())

const formattedValue = computed(() => formatMonthValue(props.modelValue))

const formatMonthValue = (value) => {
  if (!value) return ''

  const [year, month] = value.split('-').map(Number)
  if (!year || !month) return ''

  return `${monthNames[month - 1]} ${year}`
}

const toggle = () => {
  isOpen.value = !isOpen.value
}

const selectMonth = (index) => {
  emit('update:modelValue', `${activeYear.value}-${String(index + 1).padStart(2, '0')}`)
  isOpen.value = false
}

const clear = () => {
  emit('update:modelValue', '')
  isOpen.value = false
}

const isSelected = (index) => {
  return props.modelValue === `${activeYear.value}-${String(index + 1).padStart(2, '0')}`
}

const handleDocumentClick = (event) => {
  if (!root.value?.contains(event.target)) {
    isOpen.value = false
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    isOpen.value = false
  }
}

watch(() => props.modelValue, (value) => {
  if (!value) return

  const year = Number(value.slice(0, 4))
  if (year) {
    activeYear.value = year
  }
}, { immediate: true })

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('keydown', handleEscape)
})
</script>

<style scoped>
.month-filter-button {
  display: flex;
  min-height: 3.25rem;
  width: 100%;
  min-width: 0;
  align-items: center;
  gap: 0.75rem;
  border-radius: 1rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23 / 0.9);
  padding: 0.6rem 0.8rem;
  color: white;
  outline: none;
  transition: 0.2s ease;
}

.month-filter-button:hover,
.month-filter-button:focus-visible {
  border-color: rgb(52 211 153 / 0.65);
  box-shadow: 0 0 0 4px rgb(52 211 153 / 0.1);
}

.month-filter-icon {
  display: inline-flex;
  height: 2rem;
  width: 2rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 0.85rem;
  border: 1px solid rgb(52 211 153 / 0.22);
  background: rgb(16 185 129 / 0.1);
  color: rgb(110 231 183);
}

.month-filter-clear,
.year-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.85rem;
  transition: 0.2s ease;
}

.month-filter-clear {
  height: 2rem;
  width: 2rem;
  flex-shrink: 0;
  color: rgb(148 163 184);
}

.month-filter-clear:hover {
  background: rgb(15 23 42);
  color: white;
}

.year-button {
  height: 2.5rem;
  width: 2.5rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(15 23 42 / 0.85);
  color: rgb(203 213 225);
}

.year-button:hover {
  border-color: rgb(52 211 153 / 0.4);
  color: rgb(110 231 183);
}

.month-option {
  min-height: 2.5rem;
  border-radius: 1rem;
  border-width: 1px;
  font-size: 0.875rem;
  font-weight: 800;
  transition: 0.2s ease;
}
</style>
