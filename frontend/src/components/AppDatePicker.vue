<template>
  <div ref="root" class="relative">
    <button
      ref="buttonRef"
      type="button"
      class="date-picker-button"
      :aria-expanded="isOpen"
      @click="toggle"
      @keydown.escape.prevent="close"
    >
      <span class="truncate" :class="modelValue ? 'text-slate-100' : 'text-slate-400'">
        {{ modelValue ? formattedValue : placeholder }}
      </span>
      <svg class="h-4 w-4 shrink-0 text-emerald-300" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
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
          class="fixed z-[96] rounded-3xl border border-slate-700/70 bg-slate-950/95 p-4 text-white shadow-2xl shadow-black/50"
          :style="menuStyle"
        >
          <div class="mb-4 flex items-center justify-between gap-3">
            <button type="button" class="date-nav-button" aria-label="Previous month" @click="moveMonth(-1)">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
            <div class="text-center">
              <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Select Date</p>
              <p class="mt-1 text-base font-black text-white">{{ monthNames[visibleMonth] }} {{ visibleYear }}</p>
            </div>
            <button type="button" class="date-nav-button" aria-label="Next month" @click="moveMonth(1)">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-7 gap-1 text-center text-[0.68rem] font-bold uppercase text-slate-500">
            <span v-for="day in weekDays" :key="day">{{ day }}</span>
          </div>

          <div class="mt-2 grid grid-cols-7 gap-1">
            <span v-for="blank in leadingBlanks" :key="`blank-${blank}`"></span>
            <button
              v-for="day in daysInMonth"
              :key="day"
              type="button"
              class="date-day-button"
              :class="dayClass(day)"
              @click="selectDate(day)"
            >
              {{ day }}
            </button>
          </div>

          <button
            type="button"
            class="mt-4 w-full rounded-2xl border border-slate-800 bg-slate-900/80 px-4 py-2.5 text-sm font-bold text-slate-300 transition hover:border-emerald-400/30 hover:text-emerald-200"
            @click="selectToday"
          >
            Today
          </button>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select date'
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
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const root = ref(null)
const buttonRef = ref(null)
const menuRef = ref(null)
const isOpen = ref(false)
const menuStyle = ref({})
const visibleYear = ref(new Date().getFullYear())
const visibleMonth = ref(new Date().getMonth())

const formattedValue = computed(() => {
  if (!props.modelValue) return ''

  const date = parseDate(props.modelValue)
  if (!date) return props.modelValue

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
})

const daysInMonth = computed(() => new Date(visibleYear.value, visibleMonth.value + 1, 0).getDate())
const leadingBlanks = computed(() => new Date(visibleYear.value, visibleMonth.value, 1).getDay())

const parseDate = (value) => {
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '')
  if (!match) return null

  const [, year, month, day] = match.map(Number)
  return new Date(year, month - 1, day)
}

const formatDateValue = (date) => {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const toggle = () => {
  isOpen.value = !isOpen.value

  if (isOpen.value) {
    syncVisibleDate()
    nextTick(updateMenuPosition)
  }
}

const close = () => {
  isOpen.value = false
}

const syncVisibleDate = () => {
  const date = parseDate(props.modelValue) || new Date()
  visibleYear.value = date.getFullYear()
  visibleMonth.value = date.getMonth()
}

const updateMenuPosition = () => {
  const rect = buttonRef.value?.getBoundingClientRect()
  if (!rect) return

  const menuWidth = Math.min(320, Math.max(288, rect.width))
  const left = Math.min(Math.max(16, rect.left), window.innerWidth - menuWidth - 16)
  const spaceBelow = window.innerHeight - rect.bottom
  const openAbove = spaceBelow < 360 && rect.top > spaceBelow

  menuStyle.value = {
    left: `${left}px`,
    top: openAbove ? 'auto' : `${rect.bottom + 8}px`,
    bottom: openAbove ? `${window.innerHeight - rect.top + 8}px` : 'auto',
    width: `${menuWidth}px`
  }
}

const moveMonth = (amount) => {
  const date = new Date(visibleYear.value, visibleMonth.value + amount, 1)
  visibleYear.value = date.getFullYear()
  visibleMonth.value = date.getMonth()
}

const selectDate = (day) => {
  emit('update:modelValue', formatDateValue(new Date(visibleYear.value, visibleMonth.value, day)))
  close()
}

const selectToday = () => {
  emit('update:modelValue', formatDateValue(new Date()))
  close()
}

const isSelected = (day) => {
  return props.modelValue === formatDateValue(new Date(visibleYear.value, visibleMonth.value, day))
}

const isToday = (day) => {
  const today = new Date()
  return today.getFullYear() === visibleYear.value &&
    today.getMonth() === visibleMonth.value &&
    today.getDate() === day
}

const dayClass = (day) => {
  if (isSelected(day)) return 'bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/20'
  if (isToday(day)) return 'border-emerald-400/50 bg-emerald-500/10 text-emerald-200'
  return 'border-slate-800 bg-slate-900/70 text-slate-300 hover:border-emerald-400/30 hover:bg-emerald-500/10 hover:text-emerald-200'
}

const handleDocumentClick = (event) => {
  if (!root.value?.contains(event.target) && !menuRef.value?.contains(event.target)) {
    close()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape') close()
}

watch(() => props.modelValue, syncVisibleDate, { immediate: true })

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

<style scoped>
.date-picker-button {
  display: flex;
  height: 3rem;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23);
  padding: 0 1rem;
  text-align: left;
  font-size: 0.875rem;
  outline: none;
  transition: 0.2s ease;
}

.date-picker-button:hover,
.date-picker-button:focus-visible {
  border-color: rgb(16 185 129);
  box-shadow: 0 0 0 4px rgb(16 185 129 / 0.1);
}

.date-nav-button {
  display: inline-flex;
  height: 2.5rem;
  width: 2.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.875rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(15 23 42 / 0.85);
  color: rgb(203 213 225);
  transition: 0.2s ease;
}

.date-nav-button:hover {
  border-color: rgb(52 211 153 / 0.4);
  color: rgb(110 231 183);
}

.date-day-button {
  display: inline-flex;
  height: 2.25rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.875rem;
  border-width: 1px;
  font-size: 0.875rem;
  font-weight: 800;
  transition: 0.2s ease;
}
</style>
