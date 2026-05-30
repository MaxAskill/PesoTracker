<template>
  <main class="magic-bg min-h-screen overflow-hidden text-white">
    <header class="relative z-50 px-4 py-5 sm:px-6">
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
        <RouterLink to="/" class="flex min-w-0 items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-400 text-xl font-black text-slate-950 shadow-lg shadow-emerald-500/25 sm:h-11 sm:w-11">
            P
          </div>
          <div class="min-w-0">
            <h1 class="truncate text-lg font-black sm:text-xl">Peso<span class="text-emerald-300">Tracker</span></h1>
            <p class="hidden text-xs text-slate-400 sm:block">Finance Assistant</p>
          </div>
        </RouterLink>

        <div class="flex shrink-0 items-center gap-2">
          <RouterLink to="/login" class="rounded-full bg-slate-950/70 px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-emerald-400/10 hover:text-emerald-100 sm:px-4 sm:text-sm">
            Login
          </RouterLink>
          <RouterLink to="/register" class="rounded-full bg-emerald-400 px-3 py-2 text-xs font-black text-slate-950 shadow-md shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-300 sm:px-4 sm:text-sm">
            Get Started
          </RouterLink>
        </div>
      </div>
    </header>

    <section class="relative mx-auto grid min-h-[calc(100vh-80px)] max-w-7xl items-center gap-12 px-6 py-14 lg:grid-cols-2 lg:py-16">
      <div class="absolute left-1/2 top-20 h-72 w-72 -translate-x-1/2 rounded-full bg-emerald-400/10 blur-3xl"></div>

      <div class="relative z-10">
        <div class="mb-6 inline-flex rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-100 shadow-lg shadow-emerald-500/10">
          PesoTracker finance workspace
        </div>

        <h1 class="max-w-3xl text-5xl font-black leading-tight tracking-tight md:text-7xl">
          Track your money with a little
          <span class="text-emerald-300">magic.</span>
        </h1>

        <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">
          Monitor expenses, budgets, savings goals, receipts, and smart spending insights in one clean PesoTracker dashboard.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <span class="pt-chip">Expense Tracking</span>
          <span class="pt-chip">Budget Alerts</span>
          <span class="pt-chip">AI Insights</span>
          <span class="pt-chip">Receipt Scanner</span>
          <span class="pt-chip">Reports</span>
        </div>

        <div class="mt-10 flex flex-wrap gap-4">
          <RouterLink to="/register" class="pt-primary">Start Tracking</RouterLink>
          <RouterLink to="/login" class="pt-secondary">Login</RouterLink>
        </div>
      </div>

      <div class="relative">
        <div class="floating-card pt-stat absolute -left-5 top-12 hidden w-40 rotate-[-8deg] text-sm text-slate-200 sm:block">
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Savings</p>
          <p class="mt-2 text-2xl font-black">PHP 18,600</p>
          <div class="mt-3 h-2 rounded-full bg-slate-800">
            <div class="h-2 w-[62%] rounded-full bg-emerald-400"></div>
          </div>
        </div>

        <div class="dashboard-preview pt-card-glow relative p-5 sm:p-6">
          <div class="absolute right-6 top-6 h-28 w-28 rounded-full bg-emerald-300/20 blur-3xl"></div>

          <div class="relative mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Monthly Balance</p>
              <h2 class="mt-2 text-4xl font-black sm:text-5xl">PHP 42,850</h2>
            </div>
            <div class="rounded-2xl bg-emerald-400/15 px-4 py-2 font-black text-emerald-200">
              +12%
            </div>
          </div>

          <div class="relative grid gap-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="pt-stat">
                <p class="text-sm text-slate-400">Income</p>
                <p class="mt-2 text-2xl font-black text-emerald-300">PHP 30,000</p>
              </div>
              <div class="pt-stat">
                <p class="text-sm text-slate-400">Expenses</p>
                <p class="mt-2 text-2xl font-black text-red-300">PHP 22,500</p>
              </div>
            </div>

            <div class="pt-stat">
              <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-400">Food Spending</p>
                <p class="text-sm font-black text-emerald-300">72%</p>
              </div>
              <p class="text-2xl font-black">PHP 8,240</p>
              <div class="mt-4 h-2 rounded-full bg-slate-800">
                <div class="h-2 w-[72%] rounded-full bg-emerald-400"></div>
              </div>
            </div>

            <div class="pt-stat">
              <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-400">Emergency Fund</p>
                <p class="text-sm font-black text-emerald-300">PHP 30,000 target</p>
              </div>
              <div class="h-3 rounded-full bg-slate-800">
                <div class="h-3 w-2/3 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/40"></div>
              </div>
            </div>

            <div class="pt-stat">
              <p class="text-sm font-semibold text-emerald-300">AI Insight</p>
              <p class="mt-2 text-slate-200">You spent 18% less on shopping this month.</p>
            </div>
          </div>
        </div>

        <div class="floating-coin absolute -right-5 -top-6 rounded-full bg-emerald-300 px-5 py-4 text-3xl font-black text-slate-950 shadow-xl shadow-emerald-500/30">
          P
        </div>
        <div class="floating-coin-delayed absolute -bottom-7 left-10 rounded-full border border-emerald-300/30 bg-slate-950/90 px-4 py-3 text-2xl font-black text-emerald-200 shadow-xl">
          P
        </div>
      </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-4 px-6 pb-16 md:grid-cols-5">
      <div v-for="feature in features" :key="feature" class="pt-card px-5 py-4 text-center text-sm font-semibold text-slate-200">
        {{ feature }}
      </div>
    </section>
  </main>
</template>

<script setup>
const features = ['Budget Control', 'Savings Goals', 'AI Insights', 'Receipts', 'Reports']
</script>
