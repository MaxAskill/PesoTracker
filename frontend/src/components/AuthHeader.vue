<template>
  <header class="fixed left-0 right-0 top-0 z-50 border-b border-white/5 bg-slate-950/25 px-4 py-4 backdrop-blur-sm sm:px-6">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
      <RouterLink to="/" class="min-w-0">
        <AppLogo />
      </RouterLink>

      <RouterLink
        to="/"
        class="rounded-full border border-white/10 bg-slate-950/70 px-4 py-2 text-sm font-bold text-slate-200 transition hover:border-emerald-400/30 hover:bg-emerald-400/10 hover:text-emerald-100"
      >
        Back to home
      </RouterLink>
    </div>
  </header>
</template>

<script setup>
import AppLogo from './AppLogo.vue'
</script>
