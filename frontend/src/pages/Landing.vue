<template>
  <main class="min-h-screen bg-slate-950 text-white overflow-hidden">

    <!-- Navbar -->
    <header class="border-b border-slate-800 bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">

        <div class="flex items-center gap-3">
          <img
            src="/logo.png"
            class="w-12 h-12 rounded-2xl"
          />

          <div>
            <h1 class="text-2xl font-bold">
              Peso<span class="text-emerald-400">Tracker</span>
            </h1>

            <p class="text-slate-400 text-sm">
              Smart Finance Manager
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <RouterLink
            to="/login"
            class="px-5 py-2.5 rounded-xl text-slate-300 hover:text-white transition"
          >
            Login
          </RouterLink>

          <RouterLink
            to="/register"
            class="bg-emerald-500 hover:bg-emerald-600 px-5 py-2.5 rounded-xl font-semibold transition"
          >
            Get Started
          </RouterLink>
        </div>

      </div>
    </header>

    <!-- Hero -->
    <section class="relative">

      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[700px] bg-emerald-500/10 blur-3xl rounded-full"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-6 py-24 lg:py-32">

        <div class="grid lg:grid-cols-2 gap-16 items-center">

          <!-- Left -->
          <div>

            <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-2 rounded-full text-sm font-semibold mb-8">
              ✦ AI-Powered Expense Tracking
            </div>

            <h1 class="text-5xl lg:text-7xl font-black leading-tight mb-8">
              Take Control of Your
              <span class="text-emerald-400">
                Financial Life
              </span>
            </h1>

            <p class="text-xl text-slate-400 leading-relaxed mb-10 max-w-2xl">
              PesoTracker helps you manage expenses, budgets,
              savings goals, recurring payments, and AI-powered
              financial insights in one modern platform.
            </p>

            <div class="flex flex-wrap gap-4">

              <RouterLink
                to="/register"
                class="bg-emerald-500 hover:bg-emerald-600 px-8 py-4 rounded-2xl font-bold text-lg shadow-2xl shadow-emerald-500/20 transition"
              >
                Start Tracking
              </RouterLink>

              <RouterLink
                to="/login"
                class="border border-slate-700 hover:border-slate-500 bg-slate-900 px-8 py-4 rounded-2xl font-bold text-lg transition"
              >
                Login
              </RouterLink>

            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6 mt-16">

              <div>
                <h3 class="text-4xl font-black text-emerald-400">
                  AI
                </h3>

                <p class="text-slate-400 mt-2">
                  Smart Insights
                </p>
              </div>

              <div>
                <h3 class="text-4xl font-black text-emerald-400">
                  OCR
                </h3>

                <p class="text-slate-400 mt-2">
                  Receipt Scanner
                </p>
              </div>

              <div>
                <h3 class="text-4xl font-black text-emerald-400">
                  PWA
                </h3>

                <p class="text-slate-400 mt-2">
                  Installable App
                </p>
              </div>

            </div>

          </div>

          <!-- Right -->
          <div class="relative">

            <div class="bg-slate-900 border border-slate-800 rounded-[2rem] p-6 shadow-2xl">

              <!-- Top -->
              <div class="flex items-center justify-between mb-8">

                <div>
                  <p class="text-slate-400 text-sm">
                    Total Balance
                  </p>

                  <h2 class="text-5xl font-black mt-2">
                    ₱128,450
                  </h2>
                </div>

                <div class="bg-emerald-500/10 text-emerald-400 px-4 py-2 rounded-2xl font-bold">
                  +12%
                </div>

              </div>

              <!-- Cards -->
              <div class="space-y-4">

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
                  <div>
                    <p class="text-slate-400 text-sm">
                      Food Budget
                    </p>

                    <h3 class="text-2xl font-bold mt-1">
                      ₱8,500
                    </h3>
                  </div>

                  <div class="text-emerald-400 font-bold">
                    72%
                  </div>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
                  <div>
                    <p class="text-slate-400 text-sm">
                      Savings Goal
                    </p>

                    <h3 class="text-2xl font-bold mt-1">
                      ₱45,000
                    </h3>
                  </div>

                  <div class="text-emerald-400 font-bold">
                    80%
                  </div>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold">
                      Financial Health
                    </h3>

                    <span class="bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-xl text-sm font-bold">
                      Excellent
                    </span>
                  </div>

                  <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-3 w-[85%] bg-emerald-500 rounded-full"></div>
                  </div>
                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

    </section>

    <!-- Features -->
    <section class="max-w-7xl mx-auto px-6 py-24">

      <div class="text-center mb-20">

        <p class="text-emerald-400 font-semibold mb-4">
          FEATURES
        </p>

        <h2 class="text-5xl font-black mb-6">
          Everything You Need
        </h2>

        <p class="text-slate-400 text-xl max-w-3xl mx-auto">
          Built with modern fintech features to help you
          understand and improve your finances.
        </p>

      </div>

      <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">

        <div
          v-for="feature in features"
          :key="feature.title"
          class="bg-slate-900 border border-slate-800 rounded-3xl p-8 hover:border-emerald-500/40 transition"
        >
          <div class="text-5xl mb-6">
            {{ feature.icon }}
          </div>

          <h3 class="text-2xl font-bold mb-4">
            {{ feature.title }}
          </h3>

          <p class="text-slate-400 leading-relaxed">
            {{ feature.description }}
          </p>
        </div>

      </div>

    </section>

    <!-- CTA -->
    <section class="max-w-5xl mx-auto px-6 pb-24">

      <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-[2rem] p-12 text-center">

        <h2 class="text-5xl font-black mb-6">
          Start Managing Your Finances Today
        </h2>

        <p class="text-emerald-50 text-xl mb-10 max-w-2xl mx-auto">
          Track expenses, manage budgets, monitor savings,
          and unlock smarter financial insights with PesoTracker.
        </p>

        <RouterLink
          to="/register"
          class="inline-flex bg-white text-slate-950 hover:bg-slate-100 px-8 py-4 rounded-2xl font-black text-lg transition"
        >
          Create Free Account
        </RouterLink>

      </div>

    </section>

  </main>
</template>

<script setup>
const features = [
  {
    icon: '📊',
    title: 'Analytics',
    description: 'Visualize spending trends with interactive financial charts and reports.'
  },
  {
    icon: '🤖',
    title: 'AI Assistant',
    description: 'Ask questions about your finances using the built-in finance assistant.'
  },
  {
    icon: '📷',
    title: 'OCR Scanner',
    description: 'Scan receipts instantly and automatically create transactions.'
  },
  {
    icon: '🔔',
    title: 'Smart Alerts',
    description: 'Get notified about budgets, recurring payments, and financial health.'
  }
]
</script>