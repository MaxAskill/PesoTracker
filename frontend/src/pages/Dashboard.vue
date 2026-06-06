<template>
  <main class="flex min-h-screen overflow-x-hidden bg-[#020617] text-white lg:h-screen lg:overflow-hidden">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <section class="magic-bg min-w-0 flex-1 overflow-x-hidden overflow-y-auto p-4 pb-28 pt-24 sm:p-6 sm:pb-28 lg:h-screen lg:pt-6">
      <!-- Top Bar -->
      <header class="motion-fade-up relative z-10 mb-8 flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-slate-950/80 p-5 shadow-2xl shadow-slate-950/30 backdrop-blur xl:flex-row xl:items-start xl:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">PesoTracker Overview</p>
          <h2 class="mt-2 text-3xl font-bold text-white md:text-4xl">{{ displayName }}</h2>
          <p v-if="isDashboardLoading" class="mt-2 text-sm text-emerald-300">
            Loading your financial overview...
          </p>
          <p v-else-if="isRefreshingDashboard" class="mt-2 text-sm text-slate-500">
            Refreshing latest data...
          </p>
        </div>

        <div class="flex w-full flex-col gap-3 xl:w-auto xl:items-end">
          <div class="flex items-center gap-3">
            <input
              type="text"
              placeholder="Search transaction..."
              class="min-w-0 flex-1 rounded-2xl border border-white/10 bg-slate-950/80 px-5 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 xl:w-80"
            />

            <div class="relative">
              <button
                ref="notificationTrigger"
                @click.stop="toggleNotifications"
                class="relative flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-slate-950/90 text-amber-300 transition hover:border-emerald-400/40"
                aria-label="Open notifications"
              >
                🔔
            
                <span
                  v-if="unreadCount > 0"
                  class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-6 h-6 rounded-full flex items-center justify-center font-bold"
                >
                  {{ unreadCount }}
                </span>
              </button>

            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full xl:w-auto">
            <button
              @click="showExpenseModal = true"
              class="h-12 inline-flex items-center justify-center gap-2 rounded-2xl border border-red-500/30 bg-red-500/10 px-5 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500 hover:text-white xl:min-w-40"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20 text-base leading-none">
                -
              </span>
              Add Expense
            </button>

            <button
              @click="showIncomeModal = true"
              class="h-12 inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-300/50 bg-gradient-to-r from-emerald-400 to-teal-300 px-5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-teal-200 xl:min-w-40"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-base leading-none">
                +
              </span>
              Add Income
            </button>
          </div>
        </div>
      </header>

      <div
        v-if="dashboardError"
        class="mb-6 flex flex-col gap-3 rounded-2xl border border-red-500/20 bg-red-500/10 p-5 text-red-100 sm:flex-row sm:items-center sm:justify-between"
      >
        <p class="text-sm">{{ dashboardError }}</p>
        <button
          type="button"
          class="rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-400"
          @click="refreshDashboard"
        >
          Retry
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

        <template v-if="isDashboardLoading">
          <div
            v-for="index in 4"
            :key="`summary-skeleton-${index}`"
            class="motion-fade-up motion-shimmer relative min-h-44 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20"
            :style="{ animationDelay: `${index * 0.05}s` }"
          >
            <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-500/10 blur-2xl"></div>
            <div class="h-4 w-28 rounded-full bg-slate-800/80 animate-pulse"></div>
            <div class="mt-7 h-11 w-44 rounded-2xl bg-slate-800/70 animate-pulse"></div>
            <div class="mt-6 flex items-center justify-between">
              <div class="h-4 w-24 rounded-full bg-emerald-500/10 animate-pulse"></div>
              <div class="h-9 w-9 rounded-2xl bg-slate-800/80 animate-pulse"></div>
            </div>
          </div>
        </template>

        <template v-else>
        <div class="motion-card-hover motion-fade-up relative min-h-44 overflow-hidden rounded-[2rem] border border-emerald-400/20 bg-gradient-to-br from-emerald-400/15 via-slate-950/80 to-slate-950 p-6 shadow-2xl shadow-emerald-950/20" :class="loadingClass">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/20 blur-2xl"></div>
          <div class="relative flex h-full flex-col justify-between">
            <div class="flex items-start justify-between gap-4">
              <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Current Balance</p>
              <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-400/20 bg-emerald-400/10 text-emerald-200">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H18a2 2 0 0 1 2 2v10.5a2 2 0 0 1-2 2H6.5A2.5 2.5 0 0 1 4 17V7.5Z" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M16 12h4v4h-4a2 2 0 0 1 0-4Z" stroke="currentColor" stroke-width="1.8"/>
                  <path d="M7 8h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </span>
            </div>
            <h3 class="mt-5 text-4xl font-black tracking-tight text-white md:text-5xl">{{ formatPeso(dashboard.balance) }}</h3>
            <div class="mt-5 flex items-center justify-between">
              <p class="text-sm text-emerald-200">Available funds</p>
              <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full w-2/3 rounded-full bg-emerald-400"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="motion-card-hover motion-fade-up motion-delay-1 relative min-h-44 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20" :class="loadingClass">
          <div class="absolute right-6 top-6 flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-400/20 bg-emerald-400/10 text-emerald-300">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 19V5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
              <path d="m6.5 10.5 5.5-5.5 5.5 5.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M5 19h14" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Total Income</p>
          <h3 class="mt-6 text-4xl font-black tracking-tight text-emerald-300">
            {{ formatPeso(dashboard.total_income) }}
          </h3>
          <p class="mt-5 text-sm text-slate-500">All income recorded</p>
        </div>

        <div class="motion-card-hover motion-fade-up motion-delay-2 relative min-h-44 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20" :class="loadingClass">
          <div class="absolute right-6 top-6 flex h-14 w-14 items-center justify-center rounded-2xl border border-red-400/20 bg-red-400/10 text-red-300">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 5v14" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
              <path d="m6.5 13.5 5.5 5.5 5.5-5.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M5 5h14" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Total Expenses</p>
          <h3 class="mt-6 text-4xl font-black tracking-tight text-red-300">
            {{ formatPeso(dashboard.total_expenses) }}
          </h3>
          <p class="mt-5 text-sm text-slate-500">All expenses recorded</p>
        </div>

        <div class="motion-card-hover motion-fade-up motion-delay-3 relative min-h-44 overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20" :class="loadingClass">
          <div
            class="absolute right-6 top-6 flex h-14 w-14 items-center justify-center rounded-full border"
            :class="scoreIconClass"
          >
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M19 5 5 19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
              <path d="M7.5 8.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" stroke="currentColor" stroke-width="1.9"/>
              <path d="M16.5 19.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" stroke="currentColor" stroke-width="1.9"/>
            </svg>
          </div>
          <div class="flex items-center gap-2">
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Savings Score</p>
            <button
              type="button"
              class="flex h-7 w-7 items-center justify-center rounded-full border border-white/10 bg-slate-900 text-xs font-black text-slate-300 transition hover:border-emerald-400/40 hover:text-emerald-200"
              aria-label="Explain Savings Score"
              title="How Savings Score works"
              @click="showSavingsScoreModal = true"
            >
              i
            </button>
          </div>
          <h3 class="mt-6 text-4xl font-black tracking-tight md:text-5xl" :class="scoreTextClass">
            {{ financialHealth.score }}%
          </h3>
          <p class="mt-5 text-sm text-slate-500">
            {{ financialHealth.status || 'Add data to calculate your score' }}
          </p>
        </div>
        </template>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

        <!-- Expense by Category -->
        <div class="motion-fade-up relative min-h-[410px] overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20" :class="loadingClass">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Expense Mix</p>
              <h3 class="mt-1 text-2xl font-black">Expenses by Category</h3>
            </div>
            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-slate-400">This month</span>
          </div>

          <div v-if="isDashboardLoading" class="h-72 flex items-center justify-center">
            <div class="relative h-48 w-48 rounded-full border-[28px] border-slate-800/80 animate-pulse">
              <div class="absolute inset-8 rounded-full bg-slate-950"></div>
              <div class="absolute -right-3 top-8 h-10 w-10 rounded-full bg-emerald-400/20 blur-xl"></div>
            </div>
          </div>

            <div
            v-else-if="analytics.expense_by_category.length"
            class="h-72 flex items-center justify-center"
            >
            <div class="w-full max-w-[280px]">
                <ExpenseCategoryChart :categories="analytics.expense_by_category" />
            </div>
            </div>
        
          <div v-else class="flex h-72 items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-950/50 p-6 text-center text-slate-500">
            <div>
              <div class="mx-auto mb-4 h-14 w-14 rounded-2xl bg-emerald-400/10"></div>
              <p>No expense data yet.</p>
            </div>
          </div>
        </div>

        <!-- Monthly Income vs Expenses -->
        <div class="motion-fade-up motion-delay-1 relative min-h-[410px] overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20 xl:col-span-2" :class="loadingClass">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Cash Flow</p>
              <h3 class="mt-1 text-2xl font-black">Monthly Income vs Expenses</h3>
            </div>
            <div class="hidden gap-2 text-xs font-bold sm:flex">
              <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-emerald-300">Income</span>
              <span class="rounded-full bg-red-400/10 px-3 py-1 text-red-300">Expenses</span>
            </div>
          </div>

          <div v-if="isDashboardLoading" class="flex h-72 items-end justify-center gap-4 rounded-3xl bg-slate-950/40 px-4 pb-6">
            <div
              v-for="height in [42, 66, 54, 80, 48, 72]"
              :key="height"
              class="w-10 rounded-t-2xl bg-slate-800/80 animate-pulse"
              :style="{ height: height + '%' }"
            ></div>
          </div>

          <div v-else-if="analytics.monthly_summary.length">
            <MonthlySummaryChart :monthly-summary="analytics.monthly_summary" />
          </div>
        
          <div v-else class="flex h-72 items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-950/50 p-6 text-center text-slate-500">
            <div>
              <div class="mx-auto mb-4 h-14 w-20 rounded-2xl bg-emerald-400/10"></div>
              <p>No monthly data yet.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- AI Insights and Health -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-stretch">
        <div class="motion-fade-up relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-6 shadow-2xl shadow-slate-950/20 xl:col-span-2" :class="loadingClass">
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-emerald-400 font-semibold text-sm uppercase tracking-wide">Smart Signals</p>
              <h3 class="mt-1 text-2xl font-black">Financial Insights</h3>
            </div>
        
            <span class="bg-emerald-500/10 text-emerald-400 px-4 py-2 rounded-xl text-sm font-semibold">
              Smart Tips
            </span>
          </div>
        
          <div v-if="isDashboardLoading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="index in 2"
              :key="`insight-skeleton-${index}`"
              class="motion-shimmer min-h-44 rounded-3xl border border-white/10 bg-slate-950/80 p-5"
            >
              <div class="mb-4 h-10 w-10 rounded-xl bg-slate-800/80 animate-pulse"></div>
              <div class="mb-3 h-5 w-36 rounded-full bg-slate-800/80 animate-pulse"></div>
              <div class="space-y-2">
                <div class="h-3 w-full rounded-full bg-slate-800/70 animate-pulse"></div>
                <div class="h-3 w-4/5 rounded-full bg-slate-800/70 animate-pulse"></div>
                <div class="h-3 w-2/3 rounded-full bg-slate-800/70 animate-pulse"></div>
              </div>
            </div>
          </div>

          <div v-else-if="insights.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(insight, index) in insights"
              :key="insight.title"
              class="motion-card-hover motion-fade-up min-h-44 rounded-3xl border border-white/10 bg-slate-950/80 p-5 transition hover:border-emerald-400/20"
              :style="{ animationDelay: `${index * 0.05}s` }"
            >
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center mb-4"
                :class="{
                  'bg-emerald-500/10 text-emerald-400': insight.type === 'success',
                  'bg-amber-500/10 text-amber-400': insight.type === 'warning',
                  'bg-red-500/10 text-red-400': insight.type === 'danger',
                  'bg-slate-800 text-slate-400': insight.type === 'neutral'
                }"
              >
                ✦
              </div>
        
              <h4 class="font-bold mb-2">
                {{ insight.title }}
              </h4>
        
              <p class="text-slate-400 text-sm leading-relaxed">
                {{ insight.message }}
              </p>
            </div>
          </div>

          <div v-else class="flex min-h-44 items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-950/50 p-6 text-center text-slate-500">
            <div>
              <div class="mx-auto mb-4 h-14 w-14 rounded-2xl bg-emerald-400/10"></div>
              <p>Add transactions to unlock financial insights.</p>
            </div>
          </div>
        </div>
        <div class="motion-fade-up motion-delay-1 relative overflow-hidden rounded-[2rem] border border-emerald-400/15 bg-gradient-to-br from-slate-950/80 via-slate-950/70 to-emerald-950/20 p-6 shadow-2xl shadow-slate-950/20" :class="loadingClass">
          <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-400/10 blur-2xl"></div>
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-emerald-400 font-semibold text-sm uppercase tracking-wide">
                AI Financial Health
              </p>
        
              <h3 class="mt-1 text-2xl font-black">
                Finance Score
              </h3>
            </div>
        
            <div
              v-if="isDashboardLoading"
              class="w-16 h-16 rounded-2xl bg-slate-800/80 animate-pulse"
            ></div>

            <div
              v-else
              class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black"
              :class="scoreBadgeClass"
            >
              {{ financialHealth.score }}
            </div>
          </div>
        
          <div v-if="isDashboardLoading" class="mb-5">
            <div class="mb-3 flex justify-between">
              <div class="h-4 w-16 rounded-full bg-slate-800/80 animate-pulse"></div>
              <div class="h-4 w-20 rounded-full bg-slate-800/80 animate-pulse"></div>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-800">
              <div class="h-3 w-2/3 rounded-full bg-emerald-500/20 animate-pulse"></div>
            </div>
          </div>

          <div v-else class="mb-5">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-400">
                Status
              </span>
        
              <span class="font-semibold">
                {{ financialHealth.status }}
              </span>
            </div>
        
            <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
              <div
                class="h-3 rounded-full transition-all duration-500"
                :style="{ width: financialHealth.score + '%' }"
                :class="scoreBarClass"
              ></div>
            </div>
          </div>
        
          <div v-if="isDashboardLoading" class="rounded-3xl border border-white/10 bg-slate-950/80 p-5">
            <div class="space-y-2">
              <div class="h-3 w-full rounded-full bg-slate-800/70 animate-pulse"></div>
              <div class="h-3 w-5/6 rounded-full bg-slate-800/70 animate-pulse"></div>
              <div class="h-3 w-2/3 rounded-full bg-slate-800/70 animate-pulse"></div>
            </div>
          </div>

          <div v-else class="rounded-3xl border border-white/10 bg-slate-950/80 p-5">
            <p class="text-slate-400 text-sm leading-relaxed">
              {{ financialHealth.recommendation }}
            </p>
            <button
              type="button"
              class="mt-4 rounded-2xl border border-emerald-400/25 bg-emerald-500/10 px-4 py-2 text-sm font-bold text-emerald-200 transition hover:bg-emerald-500 hover:text-slate-950"
              @click="showSavingsScoreModal = true"
            >
              View breakdown
            </button>
          </div>
        </div>
        
      </div>

      <!-- Recent Transactions -->
      <div class="motion-fade-up overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 shadow-2xl shadow-slate-950/20" :class="loadingClass">
        <div class="flex items-center justify-between border-b border-slate-800 p-6">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-300">Activity</p>
            <h3 class="mt-1 text-2xl font-black">Recent Transactions</h3>
          </div>
          <router-link to="/transactions" class="text-emerald-400 text-sm font-semibold">
            View All
          </router-link>
        </div>

        <div v-if="isDashboardLoading">
          <div
            v-for="index in 4"
            :key="`transaction-skeleton-${index}`"
            class="grid grid-cols-1 gap-3 border-b border-slate-800 px-6 py-5 md:grid-cols-[minmax(0,1.5fr)_minmax(8rem,0.8fr)_minmax(8rem,0.8fr)_7rem] md:items-center"
          >
            <div class="space-y-2">
              <div class="h-4 w-40 rounded-full bg-slate-800/80 animate-pulse"></div>
              <div class="h-3 w-24 rounded-full bg-slate-800/60 animate-pulse"></div>
            </div>

            <div class="h-4 w-28 rounded-full bg-slate-800/70 animate-pulse"></div>
            <div class="h-5 w-24 rounded-full bg-slate-800/80 animate-pulse"></div>
            <div class="h-7 w-20 rounded-full bg-slate-800/70 animate-pulse"></div>
          </div>
        </div>

        <div v-else-if="dashboard.recent_transactions?.length">
          <div
            v-for="(transaction, index) in dashboard.recent_transactions"
            :key="transaction.id"
            class="motion-fade-up grid grid-cols-1 gap-3 border-b border-slate-800 px-6 py-5 transition hover:bg-slate-900/60 md:grid-cols-[minmax(0,1.5fr)_minmax(8rem,0.8fr)_minmax(8rem,0.8fr)_7rem] md:items-center"
            :style="{ animationDelay: `${index * 0.04}s` }"
          >
            <div>
              <p class="font-semibold">{{ transaction.title }}</p>
              <p class="text-sm text-slate-500">{{ transaction.category }}</p>
            </div>

            <p class="text-slate-400">{{ transaction.transaction_date }}</p>

            <p
              class="font-bold"
              :class="transaction.type === 'income' ? 'text-emerald-400' : 'text-red-400'"
            >
              {{ transaction.type === 'income' ? '+' : '-' }}{{ formatPeso(transaction.amount) }}
            </p>

            <span
              class="text-center px-3 py-1 rounded-full text-sm"
              :class="transaction.type === 'income' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'"
            >
              {{ transaction.type }}
            </span>
          </div>
        </div>

        <div v-else class="flex min-h-56 items-center justify-center p-10 text-center text-slate-500">
          <div>
            <div class="mx-auto mb-4 h-14 w-14 rounded-2xl bg-emerald-400/10"></div>
            <p>No transactions yet.</p>
          </div>
        </div>
      </div>
    </section>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showNotifications"
        class="fixed inset-0 z-[60] bg-slate-950/70 backdrop-blur-sm md:hidden"
        @click="closeNotifications"
      ></div>
    </Transition>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-4 opacity-0 scale-95 md:translate-y-0 md:translate-x-3"
      enter-to-class="translate-y-0 opacity-100 scale-100 md:translate-x-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100 scale-100 md:translate-x-0"
      leave-to-class="translate-y-4 opacity-0 scale-95 md:translate-y-0 md:translate-x-3"
    >
      <div
        v-if="showNotifications"
        ref="notificationPanel"
        class="notification-panel fixed inset-x-4 bottom-4 z-[70] max-h-[78vh] overflow-hidden rounded-3xl border border-slate-700/70 bg-slate-950 shadow-2xl shadow-black/70 md:inset-x-auto md:bottom-auto md:right-6 md:top-24 md:w-[min(420px,calc(100vw-2rem))] lg:right-8"
        @click.stop
      >
        <div class="flex items-center justify-between gap-3 border-b border-slate-800 bg-slate-950 px-5 py-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Updates</p>
            <h3 class="font-black text-white">Notifications</h3>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="markAllNotificationsRead"
              class="rounded-xl bg-emerald-500/10 px-3 py-2 text-sm font-bold text-emerald-300 transition hover:bg-emerald-500 hover:text-slate-950"
            >
              Mark all read
            </button>
            <button
              type="button"
              class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-slate-300 transition hover:bg-slate-800 hover:text-white md:hidden"
              aria-label="Close notifications"
              @click="closeNotifications"
            >
              X
            </button>
          </div>
        </div>

        <div v-if="notifications.length" class="notification-scroll max-h-[calc(78vh-88px)] overflow-y-auto bg-[#020617] py-2 md:max-h-[460px]">
          <div
            v-for="(notification, index) in notifications"
            :key="notification.id"
            class="motion-fade-up relative border-b border-slate-800/70 px-5 py-4 transition hover:bg-slate-900"
            :class="notification.is_read ? 'bg-[#020617]' : 'bg-slate-950'"
            :style="{ animationDelay: `${index * 0.035}s` }"
          >
            <span
              v-if="!notification.is_read"
              class="absolute left-2 top-6 h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.9)]"
            ></span>

            <div class="flex gap-3">
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10"
                :class="{
                  'bg-emerald-500/10 text-emerald-400': notification.type === 'success',
                  'bg-amber-500/10 text-amber-400': notification.type === 'warning',
                  'bg-red-500/10 text-red-400': notification.type === 'danger',
                  'bg-slate-800 text-slate-400': notification.type === 'info'
                }"
              >
                !
              </div>

              <div class="min-w-0">
                <h4 class="font-bold text-slate-100">
                  {{ notification.title }}
                </h4>

                <p class="mt-1 text-sm leading-relaxed text-slate-400">
                  {{ notification.message }}
                </p>
                <p v-if="notification.created_at" class="mt-2 text-xs text-slate-600">
                  {{ notification.created_at }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="bg-[#020617] p-10 text-center">
          <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300">
            !
          </div>
          <p class="font-bold text-slate-200">No notifications yet.</p>
          <p class="mt-2 text-sm text-slate-500">
            Updates about your transactions will appear here.
          </p>
        </div>
      </div>
    </Transition>

    <AppModal
      :show="showSavingsScoreModal"
      label="Savings Score"
      title="How your Savings Score works"
      subtitle="Your score is calculated from four weighted factors: savings rate, budget discipline, savings goal progress, and positive balance."
      size="lg"
      @close="showSavingsScoreModal = false"
    >
      <div class="space-y-5">
        <div class="rounded-[2rem] border border-emerald-400/20 bg-emerald-500/10 p-5">
          <p class="text-sm font-bold text-slate-400">Final Score</p>
          <div class="mt-3 flex flex-wrap items-end gap-3">
            <p class="text-5xl font-black" :class="scoreTextClass">{{ financialHealth.score }}%</p>
            <p class="pb-1 text-xl font-black text-white">{{ financialHealth.status || 'Not enough data yet' }}</p>
          </div>
          <p class="mt-4 text-sm leading-6 text-slate-400">
            This score updates as you add income, expenses, budgets, and savings goals.
          </p>
          <p class="mt-2 text-sm leading-6 text-slate-500">
            Budgets and goals improve the accuracy of your score.
          </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
          <article
            v-for="item in savingsScoreBreakdownItems"
            :key="item.key"
            class="rounded-3xl border border-white/10 bg-slate-950/80 p-4"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <h4 class="font-black text-white">{{ item.label }}</h4>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ item.description }}</p>
              </div>
              <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-black text-emerald-300">
                {{ item.weight }}%
              </span>
            </div>

            <div class="mt-4">
              <div class="mb-2 flex items-center justify-between text-xs font-bold text-slate-400">
                <span>{{ item.score }}/100</span>
                <span>{{ item.contribution }} of {{ item.maxPoints }} pts</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full rounded-full bg-emerald-400" :style="{ width: `${item.score}%` }"></div>
              </div>
            </div>
          </article>
        </div>

        <div class="rounded-3xl border border-white/10 bg-slate-950/70 p-5 text-sm leading-6 text-slate-400">
          Higher scores mean stronger savings habits and better spending discipline.
        </div>
      </div>
    </AppModal>

    <TransactionModal
      :show="showExpenseModal"
      type="expense"
      @close="showExpenseModal = false"
      @saved="refreshDashboard"
    />
    
    <TransactionModal
      :show="showIncomeModal"
      type="income"
      @close="showIncomeModal = false"
      @saved="refreshDashboard"
    />
    <SmartAssistantWidget
      v-if="!showExpenseModal && !showIncomeModal && !showNotifications && !isMobileSidebarOpen"
      :income="dashboard.total_income"
      :expenses="dashboard.total_expenses"
      :balance="dashboard.balance"
      :loading="isDashboardLoading"
    />
  </main>
  <!-- Floating Button -->
  <div v-if="false" class="fixed bottom-6 right-6 z-50">
    <button
      @click="showAssistant = !showAssistant"
      class="w-16 h-16 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white text-2xl shadow-2xl shadow-emerald-500/30 transition"
    >
      ✦
    </button>
  </div>
  
  <!-- Chatbot -->
  <div
    v-if="showAssistant"
    class="finance-panel fixed bottom-24 left-4 right-4 z-50 flex h-[min(600px,calc(100vh-8rem))] flex-col overflow-hidden sm:bottom-28 sm:left-auto sm:right-6 sm:w-[380px]"
  >
  
    <!-- Header -->
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
      <div>
        <p class="text-emerald-400 text-sm font-semibold">
          AI Assistant
        </p>
  
        <h3 class="font-bold text-white">
          PesoTracker Finance Chat
        </h3>
      </div>
  
      <button
        @click="showAssistant = false"
        class="w-10 h-10 rounded-xl bg-slate-800 text-slate-300"
      >
        ✕
      </button>
    </div>
  
    <!-- Body -->
    <div ref="chatBody" class="flex-1 overflow-y-auto p-5">
    
      <!-- Greeting -->
      <div class="finance-surface mb-4 p-4">
        <p class="text-sm text-slate-300 leading-relaxed">
          Hi! I am your PesoTracker finance assistant.
          Ask me about your spending, savings,
          budgets, and financial health.
        </p>
      </div>
    
      <!-- Messages -->
      <div class="space-y-4">
        <div
          v-for="(message, index) in assistantMessages"
          :key="index"
          class="flex"
          :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[80%] px-4 py-3 rounded-2xl text-sm leading-relaxed"
            :class="
              message.role === 'user'
                ? 'bg-emerald-500 text-white'
                : 'bg-slate-800 text-slate-200'
            "
          >
            {{ message.text }}
          </div>
        </div>
    
        <div v-if="assistantLoading" class="text-slate-400 text-sm">
          Assistant is typing...
        </div>
    
        <!-- Suggestions at bottom -->
        <div
          v-if="!assistantLoading"
          class="flex flex-wrap gap-2 pt-2"
        >
          <button
            v-for="suggestion in suggestions"
            :key="suggestion"
            @click="useSuggestion(suggestion)"
            class="bg-slate-800 hover:bg-emerald-500 text-slate-300 hover:text-white px-4 py-2 rounded-xl text-sm transition"
          >
            {{ suggestion }}
          </button>
        </div>
      </div>
    </div>
  
    <!-- Input -->
    <form
      @submit.prevent="sendAssistantMessage"
      class="p-5 border-t border-slate-800 flex gap-3"
    >
      <input
        v-model="assistantInput"
        type="text"
        placeholder="Ask about your finances..."
        class="flex-1 px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition"
      />
  
      <button
        type="submit"
        class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 rounded-2xl font-bold transition"
      >
        Send
      </button>
    </form>
  </div>
</template>

<script setup>
import { computed, onActivated, onBeforeUnmount, onDeactivated, onMounted, reactive, ref, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import api, { isCanceledRequest } from '../services/api'
import TransactionModal from '../components/TransactionModal.vue'
import ExpenseCategoryChart from '../components/ExpenseCategoryChart.vue'
import MonthlySummaryChart from '../components/MonthlySummaryChart.vue'
import Sidebar from '../components/Sidebar.vue'
import SmartAssistantWidget from '../components/SmartAssistantWidget.vue'
import AppModal from '../components/AppModal.vue'
import { formatPeso } from '../utils/currency'
import { assistantErrorMessage } from '../utils/apiErrors'
import {
  loadDashboardSnapshot,
  preloadAuthenticatedData,
  saveDashboardSnapshot
} from '../services/preload'
import { useAuth } from '../composables/useAuth'

const insights = ref([])

const notifications = ref([])
const unreadCount = ref(0)
const showNotifications = ref(false)
const isMobileSidebarOpen = ref(false)
const notificationPanel = ref(null)
const notificationTrigger = ref(null)
const dashboardError = ref('')
const showSavingsScoreModal = ref(false)

let refreshInterval = null
let dashboardEffectsAttached = false

const suggestionGroups = {
  default: [
    'How much did I spend on food this month?',
    'What is my biggest expense category?',
    'How much have I saved?',
    'What are my total expenses?',
    'Do I have active budgets?'
  ],

  food: [
    'How much did I spend on transportation?',
    'What are my latest food expenses?',
    'Which category do I spend the most on?'
  ],

  savings: [
    'How many savings goals do I have?',
    'What is my financial health score?',
    'How much do I save monthly?'
  ],

  expenses: [
    'Did I exceed my budget?',
    'What is my highest expense?',
    'How much are my recurring expenses?'
  ]
}

const suggestions = ref(suggestionGroups.default)

const useSuggestion = async (text) => {

  assistantInput.value = text

  await sendAssistantMessage()

  const lower = text.toLowerCase()

  if (lower.includes('food')) {

    suggestions.value = suggestionGroups.food

  } else if (
    lower.includes('save') ||
    lower.includes('savings')
  ) {

    suggestions.value = suggestionGroups.savings

  } else if (
    lower.includes('expense') ||
    lower.includes('budget')
  ) {

    suggestions.value = suggestionGroups.expenses

  } else {

    suggestions.value = suggestionGroups.default
  }
}

const chatBody = ref(null)

const scrollChatToBottom = async () => {
  await nextTick()

  if (chatBody.value) {
    chatBody.value.scrollTop = chatBody.value.scrollHeight
  }
}

const router = useRouter()
const { isAuthenticated } = useAuth()
const user = ref(JSON.parse(localStorage.getItem('user')))
const displayName = computed(() => {
  return [user.value?.first_name, user.value?.last_name]
    .filter(Boolean)
    .join(' ') || user.value?.name || 'User'
})

const dashboard = reactive({
  total_income: 0,
  total_expenses: 0,
  balance: 0,
  savings_score: 0,
  savings_status: '',
  score_breakdown: null,
  recent_transactions: []
})

const dashboardLoaded = ref(false)
const hasCachedDashboard = ref(false)
const isRefreshingDashboard = ref(false)
const isDashboardLoading = computed(() => !dashboardLoaded.value && !hasCachedDashboard.value)
const loadingClass = computed(() => {
  return isRefreshingDashboard.value ? 'opacity-80 transition-opacity' : ''
})

const firstDefined = (...values) => {
  return values.find((value) => value !== undefined && value !== null)
}

const numericValue = (...values) => {
  const value = firstDefined(...values)
  const number = Number(value)

  return Number.isFinite(number) ? number : 0
}

const applyDashboardSnapshot = (snapshot) => {
  if (!snapshot) return

  if (snapshot.dashboard) {
    dashboard.total_income = numericValue(snapshot.dashboard.total_income, snapshot.dashboard.income)
    dashboard.total_expenses = numericValue(snapshot.dashboard.total_expenses, snapshot.dashboard.expenses)
    dashboard.balance = numericValue(snapshot.dashboard.balance)
    dashboard.savings_score = snapshot.dashboard.savings_score ?? snapshot.dashboard.financial_health?.score ?? 0
    dashboard.savings_status = snapshot.dashboard.savings_status ?? snapshot.dashboard.financial_health?.status ?? ''
    dashboard.score_breakdown = snapshot.dashboard.score_breakdown ?? snapshot.dashboard.financial_health?.score_breakdown ?? null
    dashboard.recent_transactions = snapshot.dashboard.recent_transactions ?? []

    if (snapshot.dashboard.financial_health) {
      financialHealth.value = snapshot.dashboard.financial_health
    }
  }

  if (snapshot.analytics) {
    analytics.expense_by_category = snapshot.analytics.expense_by_category ?? []
    analytics.monthly_summary = snapshot.analytics.monthly_summary ?? []
    analytics.highest_expense_category = snapshot.analytics.highest_expense_category ?? null
  }

  if (snapshot.insights) {
    insights.value = snapshot.insights
  }

  if (snapshot.financialHealth) {
    financialHealth.value = snapshot.financialHealth
  }

  if (snapshot.notifications) {
    notifications.value = snapshot.notifications
  }

  if (snapshot.unreadCount !== null && snapshot.unreadCount !== undefined) {
    unreadCount.value = snapshot.unreadCount
  }
}

const getDashboard = async () => {
  if (!isAuthenticated.value) return

  try {
    const response = await api.get('/dashboard')

    dashboard.total_income = numericValue(response.data.total_income, response.data.income)
    dashboard.total_expenses = numericValue(response.data.total_expenses, response.data.expenses)
    dashboard.balance = numericValue(response.data.balance)
    dashboard.savings_score = response.data.savings_score ?? response.data.financial_health?.score ?? 0
    dashboard.savings_status = response.data.savings_status ?? response.data.financial_health?.status ?? ''
    dashboard.score_breakdown = response.data.score_breakdown ?? response.data.financial_health?.score_breakdown ?? null
    dashboard.recent_transactions = response.data.recent_transactions

    if (response.data.financial_health) {
      financialHealth.value = response.data.financial_health
    }
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}

const showExpenseModal = ref(false)
const showIncomeModal = ref(false)

const analytics = reactive({
  expense_by_category: [],
  monthly_summary: [],
  highest_expense_category: null
})


const getAnalytics = async () => {
  if (!isAuthenticated.value) return

  try {
    const response = await api.get('/analytics/summary')

    analytics.expense_by_category = response.data.expense_by_category
    analytics.monthly_summary = response.data.monthly_summary
    analytics.highest_expense_category = response.data.highest_expense_category
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}

const getInsights = async () => {
  if (!isAuthenticated.value) return

  try {
    const response = await api.get('/insights')
    insights.value = response.data
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}

const getNotifications = async () => {
  if (!isAuthenticated.value) return

  const response = await api.get('/notifications')
  notifications.value = response.data
}

const getUnreadCount = async () => {
  if (!isAuthenticated.value) return

  const response = await api.get('/notifications/unread-count')
  unreadCount.value = response.data.count
}

const closeNotifications = () => {
  showNotifications.value = false
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value

  if (showNotifications.value) {
    window.dispatchEvent(new CustomEvent('pesotracker-close-sidebar'))
  }
}

const markAllNotificationsRead = async () => {
  if (!isAuthenticated.value) return

  await api.post('/notifications/read-all')
  unreadCount.value = 0
  await getNotifications()
  closeNotifications()
}

const financialHealth = ref({
  score: 0,
  status: '',
  recommendation: '',
  score_breakdown: null,
  savings_score: 0,
  savings_status: '',
  month: '',
  income: 0,
  expenses: 0,
  balance: 0
})

const scoreTone = computed(() => {
  if (financialHealth.value.score >= 75) return 'healthy'
  if (financialHealth.value.score >= 50) return 'attention'
  return 'risk'
})

const scoreTextClass = computed(() => ({
  'text-emerald-300': scoreTone.value === 'healthy',
  'text-amber-300': scoreTone.value === 'attention',
  'text-red-300': scoreTone.value === 'risk'
}))

const scoreIconClass = computed(() => ({
  'border-emerald-300/20 bg-emerald-300/10 text-emerald-200': scoreTone.value === 'healthy',
  'border-amber-300/20 bg-amber-300/10 text-amber-200': scoreTone.value === 'attention',
  'border-red-300/20 bg-red-300/10 text-red-200': scoreTone.value === 'risk'
}))

const scoreBadgeClass = computed(() => ({
  'bg-emerald-500/10 text-emerald-400': scoreTone.value === 'healthy',
  'bg-amber-500/10 text-amber-400': scoreTone.value === 'attention',
  'bg-red-500/10 text-red-400': scoreTone.value === 'risk'
}))

const scoreBarClass = computed(() => ({
  'bg-emerald-500': scoreTone.value === 'healthy',
  'bg-amber-500': scoreTone.value === 'attention',
  'bg-red-500': scoreTone.value === 'risk'
}))

const savingsScoreBreakdownItems = computed(() => {
  const breakdown = financialHealth.value.score_breakdown || dashboard.score_breakdown || {}
  const weighted = breakdown.weighted || {}

  const items = [
    {
      key: 'savings_rate',
      label: 'Savings Rate',
      description: 'How much income remains after expenses.',
      weight: 40,
      maxPoints: 40,
      score: breakdown.savings_rate_score ?? 0,
      contribution: weighted.savings_rate
    },
    {
      key: 'budget_discipline',
      label: 'Budget Discipline',
      description: 'How well your spending stays within budgets.',
      weight: 30,
      maxPoints: 30,
      score: breakdown.budget_discipline_score ?? 0,
      contribution: weighted.budget_discipline
    },
    {
      key: 'savings_goal_progress',
      label: 'Savings Goal Progress',
      description: 'How much progress you made toward active goals.',
      weight: 20,
      maxPoints: 20,
      score: breakdown.savings_goal_progress_score ?? 0,
      contribution: weighted.savings_goal_progress
    },
    {
      key: 'positive_balance',
      label: 'Positive Balance',
      description: 'Whether your income is higher than expenses.',
      weight: 10,
      maxPoints: 10,
      score: breakdown.positive_balance_score ?? 0,
      contribution: weighted.positive_balance
    }
  ]

  return items.map((item) => ({
    ...item,
    score: Math.max(0, Math.min(100, Math.round(Number(item.score) || 0))),
    contribution: formatContribution(item.contribution ?? ((Number(item.score) || 0) * (item.weight / 100)))
  }))
})

const formatContribution = (value) => {
  const number = Number(value) || 0
  return Number.isInteger(number) ? String(number) : number.toFixed(1)
}

const getFinancialHealth = async () => {
  if (!isAuthenticated.value) return

  try {
    const response = await api.get('/financial-health')

    financialHealth.value = response.data
  } catch (error) {
    if (isCanceledRequest(error)) return
    console.error(error)
  }
}

const assistantMessages = ref([])

const assistantInput = ref('')
const assistantLoading = ref(false)
const showAssistant = ref(false)

const sendAssistantMessage = async () => {

  if (!assistantInput.value.trim() || !isAuthenticated.value) return

  const userMessage = assistantInput.value

  assistantMessages.value.push({
    role: 'user',
    text: userMessage
  })

  await scrollChatToBottom()


  assistantInput.value = ''

  assistantLoading.value = true

  try {

    const response = await api.post('/ai/assistant', {
      message: userMessage
    })

    assistantMessages.value.push({
      role: 'assistant',
      text: response.data.reply || response.data.message
    })

  } catch (error) {
    if (isCanceledRequest(error)) return

    assistantMessages.value.push({
      role: 'assistant',
      text: assistantErrorMessage(error)
    })

  } finally {

    assistantLoading.value = false
  }

  await scrollChatToBottom()
}

const refreshDashboard = () => {
  if (!isAuthenticated.value) return

  dashboardError.value = ''
  isRefreshingDashboard.value = true

  preloadAuthenticatedData()
    .then((snapshot) => {
      const hasDashboardData = snapshot?.dashboard ||
        snapshot?.analytics ||
        snapshot?.insights ||
        snapshot?.financialHealth

      if (hasDashboardData) {
        applyDashboardSnapshot(snapshot)
        saveDashboardSnapshot(snapshot)
      } else {
        dashboardError.value = 'We could not load your dashboard right now. Please try again.'
      }
    })
    .catch((error) => {
      if (isCanceledRequest(error)) return
      console.error(error)
      dashboardError.value = 'We could not load your dashboard right now. Please try again.'
    })
    .finally(() => {
      dashboardLoaded.value = true
      isRefreshingDashboard.value = false
    })
}

const handleDocumentClick = (event) => {
  if (!showNotifications.value) return

  const panel = notificationPanel.value
  const trigger = notificationTrigger.value

  if (panel?.contains(event.target) || trigger?.contains(event.target)) {
    return
  }

  closeNotifications()
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    closeNotifications()
  }
}

const handleSidebarState = (event) => {
  isMobileSidebarOpen.value = Boolean(event.detail?.isOpen)

  if (isMobileSidebarOpen.value) {
    closeNotifications()
    return
  }

  if (showNotifications.value) {
    document.body.classList.add('overflow-hidden')
  }
}

const handleModalOpen = () => {
  closeNotifications()
}

const startDashboardPolling = () => {
  if (refreshInterval || !isAuthenticated.value) return

  refreshInterval = setInterval(() => {
    if (!isAuthenticated.value) {
      stopDashboardPolling()
      return
    }

    getNotifications().catch((error) => {
      if (!isCanceledRequest(error)) console.error(error)
    })
    getUnreadCount().catch((error) => {
      if (!isCanceledRequest(error)) console.error(error)
    })
    getInsights()
    getFinancialHealth()
  }, 10000)
}

const stopDashboardPolling = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
}

const cleanupDashboardEffects = () => {
  if (!dashboardEffectsAttached) {
    stopDashboardPolling()
    return
  }

  dashboardEffectsAttached = false
  document.body.classList.remove('overflow-hidden')
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('keydown', handleEscape)
  window.removeEventListener('pesotracker-sidebar-state', handleSidebarState)
  window.removeEventListener('pesotracker-modal-open', handleModalOpen)
  window.removeEventListener('pesotracker-auth-cleared', cleanupDashboardEffects)
  stopDashboardPolling()
}

const attachDashboardEffects = () => {
  if (dashboardEffectsAttached) return

  dashboardEffectsAttached = true
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('keydown', handleEscape)
  window.addEventListener('pesotracker-sidebar-state', handleSidebarState)
  window.addEventListener('pesotracker-modal-open', handleModalOpen)
  window.addEventListener('pesotracker-auth-cleared', cleanupDashboardEffects)
}

watch(showNotifications, (value) => {
  if (value) {
    document.body.classList.add('overflow-hidden')
    return
  }

  if (!isMobileSidebarOpen.value) {
    document.body.classList.remove('overflow-hidden')
  }
})

onMounted(() => {
  attachDashboardEffects()

  const snapshot = loadDashboardSnapshot()

  if (snapshot) {
    hasCachedDashboard.value = true
    dashboardLoaded.value = true
    applyDashboardSnapshot(snapshot)
  }

  refreshDashboard()
  startDashboardPolling()
})

onActivated(() => {
  if (isAuthenticated.value) {
    attachDashboardEffects()
    startDashboardPolling()
  }
})

onDeactivated(() => {
  cleanupDashboardEffects()
})

onBeforeUnmount(cleanupDashboardEffects)
</script>

<style scoped>
.notification-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgb(51 65 85) rgb(2 6 23);
}

.notification-scroll::-webkit-scrollbar {
  width: 8px;
}

.notification-scroll::-webkit-scrollbar-track {
  background: rgb(2 6 23);
}

.notification-scroll::-webkit-scrollbar-thumb {
  background: rgb(51 65 85);
  border-radius: 999px;
  border: 2px solid rgb(2 6 23);
}

.notification-panel {
  isolation: isolate;
  opacity: 1;
  background: #020617 !important;
  backdrop-filter: none;
}

.notification-panel::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: -1;
  background: #020617;
}
</style>
