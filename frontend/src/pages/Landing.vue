<template>
  <main class="magic-bg min-h-screen overflow-hidden text-white">
    <header class="relative z-50 px-6 py-6">
      <div class="mx-auto flex max-w-7xl items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-400 text-2xl font-black text-slate-950 shadow-lg shadow-emerald-500/25">
            ₱
          </div>
          <div>
            <h1 class="text-xl font-black">Peso<span class="text-emerald-300">Tracker</span></h1>
            <p class="text-xs text-slate-400">Finance Assistant</p>
          </div>
        </RouterLink>

        <div class="flex items-center gap-2">
          <RouterLink to="/login" class="rounded-full bg-white/[0.06] px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-emerald-400/10 hover:text-emerald-100">
            Login
          </RouterLink>
          <RouterLink to="/register" class="magic-button px-5 py-2.5 text-sm">
            Get Started
          </RouterLink>
        </div>
      </div>
    </header>

    <section class="relative mx-auto grid min-h-[calc(100vh-80px)] max-w-7xl items-center gap-12 px-6 py-14 lg:grid-cols-2 lg:py-16">
      <div class="relative z-10">
        <div class="mb-6 inline-flex rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-sm text-emerald-100 shadow-lg shadow-emerald-500/10">
          ✦ PesoTracker Finance Assistant
        </div>

        <h1 class="max-w-3xl text-5xl font-black leading-tight tracking-tight md:text-7xl">
          Track your money with a little
          <span class="text-emerald-300">magic.</span>
        </h1>

        <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">
          Monitor expenses, budgets, savings goals, receipts, and smart AI spending insights in one clean PesoTracker dashboard.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <span class="magic-chip">₱ Expense Tracking</span>
          <span class="magic-chip">Budget Alerts</span>
          <span class="magic-chip">AI Insights</span>
          <span class="magic-chip">Receipt OCR</span>
          <span class="magic-chip">Analytics</span>
        </div>

        <div class="mt-10 flex flex-wrap gap-4">
          <RouterLink to="/register" class="magic-button">Start Tracking</RouterLink>
          <RouterLink to="/login" class="rounded-full border border-white/10 px-6 py-3 font-semibold text-slate-200 transition hover:bg-white/10">
            Login
          </RouterLink>
        </div>
      </div>

      <div class="relative">
        <div class="floating-card glass-card absolute -left-5 top-12 hidden w-36 rotate-[-8deg] p-4 text-sm text-slate-200 shadow-emerald-500/10 sm:block">
          <p class="text-emerald-300">Savings</p>
          <p class="mt-1 text-2xl font-black">₱18,600.00</p>
        </div>

        <div class="dashboard-preview glass-card relative overflow-hidden p-5 sm:p-6">
          <div class="absolute right-6 top-6 h-28 w-28 rounded-full bg-emerald-300/20 blur-3xl"></div>

          <div class="relative mb-6 flex items-center justify-between gap-4">
            <div>
              <p class="text-sm text-slate-400">Monthly Balance</p>
              <h2 class="mt-1 text-4xl font-black sm:text-5xl">₱42,850.00</h2>
            </div>
            <div class="rounded-2xl bg-emerald-400/15 px-4 py-2 font-bold text-emerald-200">
              +12%
            </div>
          </div>

          <div class="relative grid gap-4">
            <div class="glass-card p-4">
              <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-400">Food Spending</p>
                <p class="text-sm font-semibold text-emerald-300">72%</p>
              </div>
              <p class="text-2xl font-bold">₱8,240.00</p>
              <div class="mt-4 h-2 rounded-full bg-slate-900">
                <div class="h-2 w-[72%] rounded-full bg-emerald-400"></div>
              </div>
            </div>

            <div class="glass-card p-4">
              <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-400">Savings Goal</p>
                <p class="text-sm font-semibold text-emerald-300">₱30,000.00 target</p>
              </div>
              <div class="h-3 rounded-full bg-slate-900">
                <div class="h-3 w-2/3 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/40"></div>
              </div>
            </div>

            <div class="glass-card p-4">
              <p class="text-sm text-emerald-300">✦ AI Insight</p>
              <p class="mt-1 text-slate-200">You spent 18% less on shopping this month.</p>
            </div>
          </div>
        </div>

        <div class="floating-coin absolute -right-5 -top-6 rounded-full bg-emerald-300 px-5 py-4 text-3xl font-black text-slate-950 shadow-xl shadow-emerald-500/30">
          ₱
        </div>
        <div class="floating-coin-delayed absolute -bottom-7 left-10 rounded-full border border-emerald-300/30 bg-slate-950/80 px-4 py-3 text-2xl font-black text-emerald-200 shadow-xl">
          ₱
        </div>
      </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-4 px-6 pb-16 md:grid-cols-5">
      <div v-for="feature in features" :key="feature" class="glass-card px-5 py-4 text-center text-sm font-semibold text-slate-200">
        {{ feature }}
      </div>
    </section>
  </main>
</template>

<script setup>
const features = ['Budget', 'Savings', 'AI Insights', 'Receipts', 'Analytics']
</script>
