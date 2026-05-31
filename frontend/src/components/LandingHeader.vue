<template>
  <header class="fixed left-0 right-0 top-0 z-[999] border-b border-slate-800/70 bg-slate-950/85 px-4 py-4 backdrop-blur-xl sm:px-6">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4">
      <RouterLink to="/" class="flex min-w-0 items-center gap-3">
        <AppLogo />
      </RouterLink>

      <div class="hidden items-center gap-7 text-sm font-bold lg:flex">
        <a
          v-for="link in links"
          :key="link.href"
          :href="link.href"
          class="transition hover:text-emerald-300"
          :class="activeSection === link.href.slice(1) ? 'text-emerald-300' : 'text-slate-300'"
          @click.prevent="$emit('navigate', link.href)"
        >
          {{ link.label }}
        </a>
      </div>

      <div class="hidden shrink-0 items-center gap-2 sm:flex">
        <RouterLink to="/login" class="rounded-full border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:border-emerald-400/30 hover:text-emerald-200">
          Login
        </RouterLink>
        <RouterLink to="/register" class="rounded-full bg-emerald-400 px-4 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/25 transition hover:-translate-y-0.5 hover:bg-emerald-300">
          Get Started
        </RouterLink>
      </div>

      <button
        type="button"
        class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-slate-950/80 text-lg font-black text-emerald-200 sm:hidden"
        aria-label="Open navigation"
        @click="mobileMenuOpen = !mobileMenuOpen"
      >
        {{ mobileMenuOpen ? 'X' : '=' }}
      </button>
    </nav>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="-translate-y-2 opacity-0 scale-95"
      enter-to-class="translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100 scale-100"
      leave-to-class="-translate-y-2 opacity-0 scale-95"
    >
      <div v-if="mobileMenuOpen" class="relative z-[1000] mx-auto mt-4 grid max-w-7xl gap-2 rounded-3xl border border-white/10 bg-slate-950 p-4 shadow-2xl shadow-black/40 sm:hidden">
        <a
          v-for="link in links"
          :key="link.href"
          :href="link.href"
          class="rounded-2xl px-4 py-3 text-sm font-bold text-slate-300 transition hover:bg-emerald-500/10 hover:text-emerald-200"
          :class="activeSection === link.href.slice(1) ? 'bg-emerald-500/10 text-emerald-200' : ''"
          @click.prevent="handleMobileNavigate(link.href)"
        >
          {{ link.label }}
        </a>
        <div class="mt-2 grid grid-cols-2 gap-2">
          <RouterLink to="/login" class="rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-center text-sm font-bold text-slate-200">
            Login
          </RouterLink>
          <RouterLink to="/register" class="rounded-2xl bg-emerald-400 px-4 py-3 text-center text-sm font-black text-slate-950">
            Get Started
          </RouterLink>
        </div>
      </div>
    </Transition>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import AppLogo from './AppLogo.vue'

defineProps({
  links: {
    type: Array,
    required: true
  },
  activeSection: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['navigate'])
const mobileMenuOpen = ref(false)

const handleMobileNavigate = (href) => {
  mobileMenuOpen.value = false
  emit('navigate', href)
}
</script>
