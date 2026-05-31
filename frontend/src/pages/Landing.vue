<template>
  <main class="magic-bg min-h-screen overflow-x-hidden text-white">
    <header class="sticky top-0 z-50 border-b border-slate-800/60 bg-slate-950/75 px-4 py-4 backdrop-blur-xl sm:px-6">
      <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4">
        <RouterLink to="/" class="flex min-w-0 items-center gap-3">
          <img src="/logo.png" alt="PesoTracker" class="h-11 w-11 shrink-0 rounded-2xl shadow-lg shadow-emerald-500/20" />
          <div class="min-w-0">
            <h1 class="truncate text-lg font-black sm:text-xl">Peso<span class="text-emerald-300">Tracker</span></h1>
            <p class="hidden text-xs text-slate-400 sm:block">Finance Assistant</p>
          </div>
        </RouterLink>

        <div class="hidden items-center gap-7 text-sm font-bold text-slate-300 lg:flex">
          <a
            v-for="link in navLinks"
            :key="link.href"
            :href="link.href"
            class="transition hover:text-emerald-300"
            :class="navLinkClass(link.href)"
            @click.prevent="scrollToSection(link.href)"
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

      <div v-if="mobileMenuOpen" class="mx-auto mt-4 grid max-w-7xl gap-2 rounded-3xl border border-white/10 bg-slate-950 p-4 sm:hidden">
        <a
          v-for="link in navLinks"
          :key="link.href"
          :href="link.href"
          class="rounded-2xl px-4 py-3 text-sm font-bold text-slate-300 transition hover:bg-emerald-500/10 hover:text-emerald-200"
          :class="activeSection === link.href.slice(1) ? 'bg-emerald-500/10 text-emerald-200' : ''"
          @click.prevent="scrollToSection(link.href)"
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
    </header>

    <section class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:py-24">
      <div class="absolute left-1/2 top-10 h-80 w-80 -translate-x-1/2 rounded-full bg-emerald-400/10 blur-3xl"></div>

      <div class="relative z-10">
        <div class="mb-6 inline-flex rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-sm font-bold text-emerald-100 shadow-lg shadow-emerald-500/10">
          Smart finance workspace
        </div>

        <h1 class="max-w-4xl text-5xl font-black leading-tight tracking-tight md:text-7xl">
          Track your money with a dashboard that
          <span class="text-emerald-300">thinks with you.</span>
        </h1>

        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
          Monitor income, expenses, budgets, savings goals, receipt scans, and smart insights in one clean PesoTracker workspace.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <span v-for="chip in featureChips" :key="chip" class="pt-chip">{{ chip }}</span>
        </div>

        <div class="mt-10 flex flex-col gap-3 sm:flex-row">
          <RouterLink to="/register" class="pt-primary text-center">Start Tracking</RouterLink>
          <a href="#preview" class="pt-secondary text-center" @click.prevent="scrollToSection('#preview')">View App Preview</a>
        </div>
      </div>

      <HeroDashboardPreview />
    </section>

    <section id="preview" class="mx-auto max-w-7xl scroll-mt-28 px-4 py-16 sm:px-6">
      <SectionHeader
        eyebrow="App preview"
        title="See how PesoTracker works"
        text="Explore the public demo layout with sample data only. Your real dashboard stays private after login."
      />

      <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="item in appPreviews" :key="item.title" class="pt-card p-5">
          <div class="mb-5 flex items-start justify-between gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-400/20 bg-emerald-500/10 text-lg font-black text-emerald-300">
              {{ item.icon }}
            </div>
            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-slate-400">Preview</span>
          </div>
          <h3 class="text-xl font-black">{{ item.title }}</h3>
          <p class="mt-3 min-h-12 text-sm leading-6 text-slate-400">{{ item.description }}</p>
          <div class="mt-5 rounded-3xl border border-white/10 bg-slate-950/80 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">{{ item.sampleLabel }}</p>
            <div class="mt-3 space-y-3">
              <div v-for="line in item.sample" :key="line" class="flex items-center justify-between gap-3 rounded-2xl bg-slate-900/80 px-3 py-2 text-sm">
                <span class="truncate text-slate-300">{{ line }}</span>
                <span class="h-2 w-16 rounded-full bg-emerald-400/60"></span>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section id="features" class="mx-auto max-w-7xl scroll-mt-28 px-4 py-16 sm:px-6">
      <SectionHeader
        eyebrow="Features"
        title="A finance workspace shaped like the real app"
        text="Dashboard cards, progress bars, assistant prompts, reports, and scanner flows use the same dark emerald product language."
      />

      <div class="mt-10 grid auto-rows-[minmax(220px,auto)] gap-5 lg:grid-cols-4">
        <article
          v-for="feature in bentoFeatures"
          :key="feature.title"
          class="pt-card-glow p-6"
          :class="feature.span"
        >
          <p class="text-sm font-bold uppercase tracking-wide text-emerald-300">{{ feature.kicker }}</p>
          <h3 class="mt-3 text-2xl font-black">{{ feature.title }}</h3>
          <p class="mt-3 text-sm leading-6 text-slate-400">{{ feature.text }}</p>
          <div class="mt-6 space-y-3">
            <div v-for="bar in feature.bars" :key="bar.label" class="rounded-2xl border border-white/10 bg-slate-950/80 p-3">
              <div class="mb-2 flex items-center justify-between text-sm">
                <span class="text-slate-300">{{ bar.label }}</span>
                <span class="font-black text-emerald-300">{{ bar.value }}</span>
              </div>
              <div class="h-2 rounded-full bg-slate-800">
                <div class="h-2 rounded-full bg-emerald-400" :style="{ width: bar.width }"></div>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section id="how" class="mx-auto max-w-7xl scroll-mt-28 px-4 py-16 sm:px-6">
      <SectionHeader
        eyebrow="How it works"
        title="From records to better habits"
        text="PesoTracker keeps the workflow simple: record, plan, then review what your money is doing."
      />

      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <article v-for="step in steps" :key="step.title" class="pt-card p-6">
          <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-400 text-xl font-black text-slate-950">
            {{ step.number }}
          </div>
          <h3 class="text-xl font-black">{{ step.title }}</h3>
          <p class="mt-3 text-sm leading-6 text-slate-400">{{ step.text }}</p>
          <div class="mt-6 rounded-3xl border border-white/10 bg-slate-950/80 p-4">
            <div class="h-3 rounded-full bg-slate-800">
              <div class="h-3 rounded-full bg-emerald-400" :style="{ width: step.width }"></div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section id="insights" class="mx-auto max-w-7xl scroll-mt-28 px-4 py-16 sm:px-6">
      <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <div>
          <p class="text-sm font-black uppercase tracking-wide text-emerald-300">Demo insights</p>
          <h2 class="mt-3 text-4xl font-black md:text-5xl">Understand your spending without digging.</h2>
          <p class="mt-4 text-base leading-7 text-slate-400">
            These sample cards show the kind of rule-based guidance PesoTracker can generate from your recorded data.
          </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <article v-for="insight in insights" :key="insight" class="rounded-3xl border border-white/10 bg-slate-950/75 p-5 shadow-xl shadow-slate-950/20">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-300">Sample data</p>
            <p class="mt-3 text-sm leading-6 text-slate-200">{{ insight }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-6 px-4 py-16 sm:px-6 lg:grid-cols-2">
      <article class="pt-card-glow p-6 sm:p-8">
        <p class="text-sm font-black uppercase tracking-wide text-emerald-300">Receipt scanner</p>
        <h2 class="mt-3 text-3xl font-black">Scan receipts, review before saving</h2>
        <p class="mt-4 text-sm leading-7 text-slate-400">
          Use your camera or upload a receipt image, then review the transaction draft before adding it to your records.
        </p>
        <div class="mt-8 rounded-[2rem] border border-slate-700/70 bg-black p-4">
          <div class="relative flex min-h-80 items-center justify-center rounded-3xl bg-slate-950">
            <div class="absolute inset-6 rounded-[2rem] border-2 border-emerald-400/60 shadow-[0_0_30px_rgba(52,211,153,0.18)]"></div>
            <div class="w-44 rounded-2xl border border-slate-700 bg-slate-900 p-4 text-sm text-slate-300">
              <p class="font-black text-white">Receipt</p>
              <p class="mt-3">Amount: PHP 840.00</p>
              <p>Date: Today</p>
              <p>Category: Food</p>
            </div>
          </div>
        </div>
        <RouterLink to="/register" class="mt-6 inline-flex rounded-2xl bg-emerald-400 px-5 py-3 font-black text-slate-950 transition hover:bg-emerald-300">
          Try it after signing up
        </RouterLink>
      </article>

      <article class="pt-card p-6 sm:p-8">
        <p class="text-sm font-black uppercase tracking-wide text-emerald-300">Smart assistant</p>
        <h2 class="mt-3 text-3xl font-black">Ask about your finances</h2>
        <p class="mt-4 text-sm leading-7 text-slate-400">
          Ask about spending, budgets, savings progress, and monthly summaries. Insights are based on your recorded PesoTracker data.
        </p>

        <div class="mt-8 space-y-4">
          <div class="ml-auto max-w-sm rounded-3xl bg-emerald-400 px-5 py-4 text-sm font-bold text-slate-950">
            Which category am I overspending on?
          </div>
          <div class="max-w-md rounded-3xl border border-white/10 bg-slate-950 p-5 text-sm leading-6 text-slate-300">
            Your highest spending category is Food. You spent PHP 7,200 this month.
          </div>
        </div>
      </article>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
      <div class="overflow-hidden rounded-[2.5rem] border border-emerald-400/20 bg-gradient-to-br from-emerald-500/15 via-slate-950 to-slate-950 p-8 text-center shadow-2xl shadow-emerald-950/20 sm:p-12">
        <p class="text-sm font-black uppercase tracking-wide text-emerald-300">Start free</p>
        <h2 class="mx-auto mt-3 max-w-3xl text-4xl font-black md:text-5xl">Ready to make your money easier to understand?</h2>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-400">
          Start tracking your income, expenses, budgets, and savings goals in one focused dashboard.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
          <RouterLink to="/register" class="pt-primary text-center">Create Free Account</RouterLink>
          <RouterLink to="/login" class="pt-secondary text-center">Login</RouterLink>
        </div>
      </div>
    </section>

    <footer class="border-t border-white/10 px-4 py-10 sm:px-6">
      <div class="mx-auto flex max-w-7xl flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
          <img src="/logo.png" alt="PesoTracker" class="h-11 w-11 rounded-2xl" />
          <div>
            <p class="font-black">Peso<span class="text-emerald-300">Tracker</span></p>
            <p class="max-w-md text-sm text-slate-500">A personal finance workspace for tracking expenses, budgets, receipts, and savings.</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-4 text-sm font-bold text-slate-400">
          <RouterLink to="/login" class="hover:text-emerald-300">Login</RouterLink>
          <RouterLink to="/register" class="hover:text-emerald-300">Register</RouterLink>
          <a href="#features" class="hover:text-emerald-300" @click.prevent="scrollToSection('#features')">Features</a>
        </div>
        <p class="text-sm text-slate-600">Copyright 2026 PesoTracker.</p>
      </div>
    </footer>
  </main>
</template>

<script setup>
import { defineComponent, h, onBeforeUnmount, onMounted, ref } from 'vue'

const mobileMenuOpen = ref(false)
const activeSection = ref('')
let sectionObserver = null

const navLinks = [
  { label: 'Features', href: '#features' },
  { label: 'Preview', href: '#preview' },
  { label: 'How it works', href: '#how' },
  { label: 'Insights', href: '#insights' }
]

const scrollToSection = (href) => {
  mobileMenuOpen.value = false

  const target = document.querySelector(href)
  if (!target) return

  window.history.pushState(null, '', href)
  target.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  })
}

const navLinkClass = (href) => {
  return activeSection.value === href.slice(1)
    ? 'text-emerald-300'
    : 'text-slate-300'
}

onMounted(() => {
  const targets = navLinks
    .map(link => document.querySelector(link.href))
    .filter(Boolean)

  sectionObserver = new IntersectionObserver((entries) => {
    const visible = entries
      .filter(entry => entry.isIntersecting)
      .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0]

    if (visible?.target?.id) {
      activeSection.value = visible.target.id
    }
  }, {
    rootMargin: '-96px 0px -58% 0px',
    threshold: [0.12, 0.3, 0.6]
  })

  targets.forEach(target => sectionObserver.observe(target))
})

onBeforeUnmount(() => {
  sectionObserver?.disconnect()
})

const featureChips = ['Expense Tracking', 'Budget Alerts', 'Savings Goals', 'Receipt Scanner', 'Smart Insights']

const appPreviews = [
  {
    icon: 'D',
    title: 'Dashboard Overview',
    description: 'View your balance, income, expenses, and savings score at a glance.',
    sampleLabel: 'Sample overview',
    sample: ['Balance PHP 28,000', 'Savings score 85%', 'Net cash flow positive']
  },
  {
    icon: 'T',
    title: 'Transactions',
    description: 'Record income and expenses with clear categories, dates, and notes.',
    sampleLabel: 'Sample records',
    sample: ['Salary income', 'Food expense', 'Transport expense']
  },
  {
    icon: 'B',
    title: 'Budgets',
    description: 'Set monthly category limits and see if you are still on track.',
    sampleLabel: 'Sample budget',
    sample: ['Food 72% used', 'Transport 48% used', 'Bills 64% used']
  },
  {
    icon: 'S',
    title: 'Savings Goals',
    description: 'Track your target amount, saved amount, deadline, and progress.',
    sampleLabel: 'Sample goal',
    sample: ['Emergency fund 62%', 'Laptop fund 35%', 'Travel fund 20%']
  },
  {
    icon: 'R',
    title: 'Reports',
    description: 'Understand spending patterns using simple visual summaries.',
    sampleLabel: 'Sample report',
    sample: ['Monthly income vs expenses', 'Category breakdown', 'Recent trend']
  },
  {
    icon: 'A',
    title: 'AI Assistant',
    description: 'Get rule-based financial insights and helpful spending suggestions.',
    sampleLabel: 'Demo insight',
    sample: ['Biggest category: Food', 'Budget status: Safe', 'Savings progress: 62%']
  }
]

const bentoFeatures = [
  {
    kicker: 'Dashboard',
    title: 'Smart Dashboard',
    text: 'A premium overview of balances, totals, savings score, charts, and recent activity.',
    span: 'lg:col-span-2',
    bars: [
      { label: 'Current Balance', value: 'PHP 28k', width: '78%' },
      { label: 'Savings Score', value: '85%', width: '85%' }
    ]
  },
  {
    kicker: 'Transactions',
    title: 'Transaction Center',
    text: 'Keep income and expenses organized with categories, dates, notes, and receipt drafts.',
    span: '',
    bars: [
      { label: 'Income', value: 'PHP 35k', width: '88%' },
      { label: 'Expenses', value: 'PHP 7k', width: '32%' }
    ]
  },
  {
    kicker: 'Budgets',
    title: 'Budget Monitoring',
    text: 'See remaining limits before spending gets away from you.',
    span: '',
    bars: [
      { label: 'Food', value: '72%', width: '72%' },
      { label: 'Transport', value: '48%', width: '48%' }
    ]
  },
  {
    kicker: 'Scanner',
    title: 'Receipt Scanner',
    text: 'Capture receipts and prepare editable expense drafts for review.',
    span: '',
    bars: [
      { label: 'Draft ready', value: 'Review', width: '64%' }
    ]
  },
  {
    kicker: 'Reports',
    title: 'Financial Reports',
    text: 'Review monthly behavior with clean visual summaries and category totals.',
    span: 'lg:col-span-2',
    bars: [
      { label: 'Food', value: 'Highest', width: '76%' },
      { label: 'Shopping', value: 'Lower', width: '42%' }
    ]
  },
  {
    kicker: 'Assistant',
    title: 'AI Financial Insights',
    text: 'Rule-based insights explain spending, budgets, savings, and monthly summaries.',
    span: '',
    bars: [
      { label: 'Helpful tips', value: 'On', width: '92%' }
    ]
  }
]

const steps = [
  {
    number: '1',
    title: 'Add your income and expenses',
    text: 'Record transactions manually or start from a receipt draft.',
    width: '33%'
  },
  {
    number: '2',
    title: 'Set budgets and savings goals',
    text: 'Create monthly limits and track progress toward personal targets.',
    width: '66%'
  },
  {
    number: '3',
    title: 'Review insights and improve habits',
    text: 'Use reports and assistant tips to understand what changed.',
    width: '100%'
  }
]

const insights = [
  'You spent PHP 1,200 less on shopping this month.',
  'Food is your highest spending category.',
  'You are 62% toward your savings goal.',
  'Your expenses are 20% of your recorded income.',
  'You are still within your monthly budget.'
]

const SectionHeader = defineComponent({
  props: {
    eyebrow: { type: String, required: true },
    title: { type: String, required: true },
    text: { type: String, required: true }
  },
  setup(props) {
    return () => h('div', { class: 'mx-auto max-w-3xl text-center' }, [
      h('p', { class: 'text-sm font-black uppercase tracking-wide text-emerald-300' }, props.eyebrow),
      h('h2', { class: 'mt-3 text-4xl font-black md:text-5xl' }, props.title),
      h('p', { class: 'mt-4 text-base leading-7 text-slate-400' }, props.text)
    ])
  }
})

const HeroDashboardPreview = defineComponent({
  setup() {
    return () => h('div', { class: 'relative' }, [
      h('div', { class: 'floating-card pt-stat absolute -left-5 top-12 hidden w-44 rotate-[-8deg] text-sm text-slate-200 sm:block' }, [
        h('p', { class: 'text-xs font-semibold uppercase tracking-wide text-emerald-300' }, 'Sample data'),
        h('p', { class: 'mt-2 text-2xl font-black' }, '85%'),
        h('p', { class: 'mt-1 text-slate-400' }, 'Savings Score')
      ]),
      h('div', { class: 'dashboard-preview pt-card-glow relative p-5 sm:p-6' }, [
        h('div', { class: 'absolute right-6 top-6 h-28 w-28 rounded-full bg-emerald-300/20 blur-3xl' }),
        h('div', { class: 'relative mb-5 flex items-start justify-between gap-4' }, [
          h('div', [
            h('p', { class: 'text-sm font-semibold uppercase tracking-wide text-slate-400' }, 'Sample dashboard preview'),
            h('h2', { class: 'mt-2 text-4xl font-black sm:text-5xl' }, 'PHP 28,000')
          ]),
          h('div', { class: 'rounded-2xl bg-emerald-400/15 px-4 py-2 font-black text-emerald-200' }, '+12%')
        ]),
        h('div', { class: 'relative grid gap-4' }, [
          h('div', { class: 'grid gap-4 sm:grid-cols-3' }, [
            stat('Income', 'PHP 35,000', 'text-emerald-300'),
            stat('Expenses', 'PHP 7,000', 'text-red-300'),
            stat('Score', '85%', 'text-amber-300')
          ]),
          h('div', { class: 'pt-stat' }, [
            h('div', { class: 'mb-3 flex items-center justify-between' }, [
              h('p', { class: 'text-sm text-slate-400' }, 'Monthly Income vs Expenses'),
              h('p', { class: 'text-sm font-black text-emerald-300' }, 'Preview')
            ]),
            h('div', { class: 'flex h-36 items-end gap-3' }, [
              bar('70%'), bar('45%', 'bg-red-300'), bar('82%'), bar('38%', 'bg-red-300'), bar('76%'), bar('31%', 'bg-red-300')
            ])
          ]),
          h('div', { class: 'grid gap-4 sm:grid-cols-2' }, [
            progressCard('Food Spending', 'PHP 7,200', '72%'),
            progressCard('Emergency Fund', 'PHP 18,600', '62%')
          ]),
          h('div', { class: 'pt-stat' }, [
            h('p', { class: 'text-sm font-semibold text-emerald-300' }, 'AI Insight'),
            h('p', { class: 'mt-2 text-slate-200' }, 'Your expenses are 20% of your recorded income.')
          ])
        ])
      ])
    ])
  }
})

const stat = (label, value, color) => h('div', { class: 'pt-stat' }, [
  h('p', { class: 'text-sm text-slate-400' }, label),
  h('p', { class: `mt-2 text-xl font-black ${color}` }, value)
])

const bar = (height, color = 'bg-emerald-400') => h('div', { class: `w-full rounded-t-xl ${color}`, style: { height } })

const progressCard = (label, value, percent) => h('div', { class: 'pt-stat' }, [
  h('div', { class: 'mb-3 flex items-center justify-between' }, [
    h('p', { class: 'text-sm text-slate-400' }, label),
    h('p', { class: 'text-sm font-black text-emerald-300' }, percent)
  ]),
  h('p', { class: 'text-2xl font-black' }, value),
  h('div', { class: 'mt-4 h-2 rounded-full bg-slate-800' }, [
    h('div', { class: 'h-2 rounded-full bg-emerald-400', style: { width: percent } })
  ])
])
</script>
