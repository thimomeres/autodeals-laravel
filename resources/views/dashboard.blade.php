<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Dashboard Control Center</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>

  <body class="bg-[#F5F7FB]">
    @include('sidebar')

    <div class="flex min-h-screen">
      <main class="ml-[280px] flex-1">
        <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Dashboard Overview</h2>
            <p class="text-gray-500 mt-1 text-sm">
            Welcome back, Pak Rendra. Here is your showroom performance today.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <button 
            type="button" 
            class="w-15 h-15 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-2xl flex items-center justify-center border border-gray-200 relative transition cursor-pointer"
            title="Notifications"
            >
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute top-3 right-3 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white animate-pulse"></span>
            </button>
        </div>
        </header>

        <section class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-white p-6 border border-gray-200 rounded-3xl flex items-center gap-5 shadow-sm">
              <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                <i data-lucide="wallet" class="w-7 h-7"></i>
              </div>
              <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Total Inventory Value
                </p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">
                  Rp {{ $totalInventoryValue }} B
                </h3>
              </div>
            </div>

            <div class="bg-white p-6 border border-gray-200 rounded-3xl shadow-sm">
              <div class="flex justify-between items-center mb-3">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Monthly Sales Target
                </p>
                <span class="text-xs font-bold text-green-600 bg-green-50 px-2.5 py-1 rounded-lg">
                  {{ $targetPercentage }}% Achieved
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-gray-900">{{ $unitsSold }}</h3>
                <span class="text-gray-400 text-sm">/ {{ $salesTarget }} Units Sold</span>
              </div>
              <div class="w-full bg-gray-100 h-2 rounded-full mt-3 overflow-hidden">
                <div class="bg-green-500 h-full rounded-full" style="width: {{ $targetPercentage }}%"></div>
              </div>
            </div>

            <div class="bg-white p-6 border border-gray-200 rounded-3xl flex items-center gap-5 shadow-sm">
              <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center relative">
                <i data-lucide="message-square-dashed" class="w-7 h-7"></i>
                <span class="absolute top-3 right-3 w-2.5 h-2.5 bg-rose-500 rounded-full animate-pulse"></span>
              </div>
              <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Active Pending Offers
                </p>
                <h3 class="text-2xl font-black text-gray-900 mt-1">5 Offers</h3>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-gray-200 rounded-3xl lg:col-span-2 p-8 shadow-sm">
              <div class="flex justify-between items-center mb-6">
                <div>
                  <h4 class="text-lg font-bold text-gray-900">
                    Monthly Sales Trend (2026)
                  </h4>
                  <p class="text-gray-400 text-xs mt-1">
                    Overview of unit sales performance dynamics.
                  </p>
                </div>
                <select class="text-xs font-semibold bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none">
                  <option>This Year</option>
                  <option>Last Year</option>
                </select>
              </div>

              <div class="h-64 flex items-end justify-between gap-4 pt-4 border-b border-gray-100 px-2">
                <div class="w-full flex flex-col items-center gap-2">
                  <div class="bg-blue-600/20 w-full h-32 rounded-t-lg hover:bg-blue-600 transition duration-300"></div>
                  <span class="text-xs text-gray-400 font-semibold">Jan</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                  <div class="bg-blue-600/20 w-full h-40 rounded-t-lg hover:bg-blue-600 transition duration-300"></div>
                  <span class="text-xs text-gray-400 font-semibold">Feb</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                  <div class="bg-blue-600/20 w-full h-24 rounded-t-lg hover:bg-blue-600 transition duration-300"></div>
                  <span class="text-xs text-gray-400 font-semibold">Mar</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                  <div class="bg-blue-600/20 w-full h-48 rounded-t-lg hover:bg-blue-600 transition duration-300"></div>
                  <span class="text-xs text-gray-400 font-semibold">Apr</span>
                </div>
                <div class="w-full flex flex-col items-center gap-2">
                  <div class="bg-blue-600 w-full h-56 rounded-t-lg shadow-sm"></div>
                  <span class="text-xs text-gray-900 font-bold">May</span>
                </div>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm flex flex-col justify-between">
              <div>
                <h4 class="text-lg font-bold text-gray-900">
                  Stock Distribution
                </h4>
                <p class="text-gray-400 text-xs mt-1">
                  Inventory share grouped by automotive brands.
                </p>
              </div>

              <div class="space-y-4 my-6">
                <div>
                  <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-gray-700">Toyota ({{ $toyotaPercent }}%)</span>
                    <span class="text-gray-400">{{ $toyotaCount }} Units</span>
                  </div>
                  <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-blue-500 h-full" style="width: {{ $toyotaPercent }}%"></div>
                  </div>
                </div>
                
                <div>
                  <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-gray-700">Honda ({{ $hondaPercent }}%)</span>
                    <span class="text-gray-400">{{ $hondaCount }} Units</span>
                  </div>
                  <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full" style="width: {{ $hondaPercent }}%"></div>
                  </div>
                </div>

                <div>
                  <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-gray-700">BMW & Mercedes ({{ $bmwPercent }}%)</span>
                    <span class="text-gray-400">{{ $bmwMercedesCount }} Units</span>
                  </div>
                  <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-purple-500 h-full" style="width: {{ $bmwPercent }}%"></div>
                  </div>
                </div>
              </div>

              <a
                href="{{ route('inventory') }}"
                class="text-xs text-center font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 py-3 rounded-xl block transition"
              >
                View Full Inventory
              </a>
            </div>
          </div>

          <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-900">
                  Recent Action Required
                </h3>
                <p class="text-gray-500 text-sm mt-1">
                  Offers submitted by clients waiting for your confirmation.
                </p>
              </div>
              <span class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                Attention Needed
              </span>
            </div>

            <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Buyer Name</th>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Target Vehicle</th>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Price Offered</th>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-center p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr class="hover:bg-gray-50/60 transition">
                    <td class="p-4 font-bold text-gray-950">Hendra Wijaya</td>
                    <td class="p-4">
                      <span class="font-medium text-gray-900 block">Fortuner GR Sport</span>
                      <span class="text-xs text-gray-400 font-mono">AD-001</span>
                    </td>
                    <td class="p-4 font-bold text-blue-600">Rp 445.000.000</td>
                    <td class="p-4">
                      <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-xs font-semibold">Pending Review</span>
                    </td>
                    <td class="p-4 text-center">
                      <button
                        onclick="window.location.href='#'"
                        class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-blue-600 hover:text-white text-gray-700 font-bold rounded-lg transition cursor-pointer"
                      >
                        Review Offer
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
  </body>
</html>