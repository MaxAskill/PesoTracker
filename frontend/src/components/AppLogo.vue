<template>
  <div class="flex min-w-0 items-center gap-3">
    <span
      class="flex shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-400 shadow-lg shadow-emerald-500/25"
      :class="markSizeClass"
    >
      <img
        v-if="!logoFailed"
        src="/brand-mark.svg"
        alt=""
        class="h-full w-full object-contain"
        @error="logoFailed = true"
      />
      <span v-else class="font-black text-slate-950" :class="fallbackSizeClass">P</span>
    </span>

    <span v-if="showText" class="min-w-0">
      <span class="block truncate font-black text-white" :class="titleSizeClass">
        Peso<span class="text-emerald-300">Tracker</span>
      </span>
      <span v-if="subtitle" class="block truncate text-slate-400" :class="subtitleSizeClass">
        {{ subtitle }}
      </span>
    </span>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md'
  },
  subtitle: {
    type: String,
    default: 'Finance Assistant'
  },
  showText: {
    type: Boolean,
    default: true
  }
})

const logoFailed = ref(false)

const markSizeClass = computed(() => ({
  sm: 'h-10 w-10',
  md: 'h-11 w-11',
  lg: 'h-14 w-14'
}[props.size] || 'h-11 w-11'))

const titleSizeClass = computed(() => ({
  sm: 'text-lg',
  md: 'text-xl',
  lg: 'text-3xl'
}[props.size] || 'text-xl'))

const subtitleSizeClass = computed(() => props.size === 'lg' ? 'text-sm' : 'text-xs')
const fallbackSizeClass = computed(() => props.size === 'lg' ? 'text-3xl' : 'text-2xl')
</script>
