<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Sales History</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>

  <body class="bg-[#F5F7FB] min-h-screen flex">
    @include('sidebar')

    <main class="ml-[280px] flex-1">
      <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Sales History</h2>
          <p class="text-gray-500 mt-1 text-sm">
            Closed deals from accepted offers — revenue and margin tracking.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <a
            href="{{ route('sales.export') }}"
            class="px-5 h-12 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-bold flex items-center gap-2 transition"
          >
            <i data-lucide="download" class="w-4 h-4"></i>
            Export CSV
          </a>
          @if($unreadNotificationsCount > 0)
            <a
              href="{{ route('dashboard') }}"
              class="flex items-center gap-2 px-4 h-12 bg-amber-50 text-amber-700 border border-amber-200 rounded-2xl text-sm font-bold hover:bg-amber-100 transition"
            >
              <i data-lucide="bell" class="w-4 h-4"></i>
              {{ $unreadNotificationsCount }} pending
            </a>
          @endif
        </div>
      </header>

      <section class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white p-6 border border-gray-200 rounded-3xl flex items-center gap-5 shadow-sm">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
              <i data-lucide="banknote" class="w-7 h-7"></i>
            </div>
            <div>
              <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                Total Revenue
              </p>
              <h3 class="text-2xl font-black text-gray-900 mt-1">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
              </h3>
            </div>
          </div>

          <div class="bg-white p-6 border border-gray-200 rounded-3xl flex items-center gap-5 shadow-sm">
            <div class="w-14 h-14 {{ $totalProfit >= 0 ? 'bg-blue-50 text-blue-600' : 'bg-rose-50 text-rose-600' }} rounded-2xl flex items-center justify-center">
              <i data-lucide="pie-chart" class="w-7 h-7"></i>
            </div>
            <div>
              <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                Net Profit Margins
              </p>
              <h3 class="text-2xl font-black mt-1 {{ $totalProfit >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                Rp {{ number_format($totalProfit, 0, ',', '.') }}
              </h3>
            </div>
          </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm">
          <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h4 class="text-lg font-bold text-gray-900">Accepted Deals Ledger</h4>
              <p class="text-gray-400 text-xs mt-1">
                {{ $salesData->count() }} completed transaction{{ $salesData->count() !== 1 ? 's' : '' }} on record
              </p>
            </div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Backoffice Sales</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="text-left p-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Invoice Date</th>
                  <th class="text-left p-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Buyer Name</th>
                  <th class="text-left p-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Target Vehicle</th>
                  <th class="text-left p-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Base Cost</th>
                  <th class="text-left p-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Deal Price</th>
                  <th class="text-right p-5 text-xs font-bold text-gray-400 uppercase tracking-wider">Net Profit</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @forelse($salesData as $sale)
                  @php
                    $baseCost = $sale->car->price ?? 0;
                    $dealPrice = $sale->price_offered ?? 0;
                    $netProfit = $dealPrice - $baseCost;
                  @endphp
                  <tr class="hover:bg-gray-50/60 transition">
                    <td class="p-5 text-gray-700 font-medium whitespace-nowrap">
                      {{ $sale->updated_at->format('d M Y') }}
                    </td>
                    <td class="p-5">
                      <span class="font-bold text-gray-900">{{ $sale->buyer_name }}</span>
                    </td>
                    <td class="p-5">
                      <div class="flex flex-col gap-0.5">
                        <span class="font-bold text-gray-900">
                          {{ $sale->car->brand ?? '—' }} {{ $sale->car->model ?? '' }}
                        </span>
                        <span class="text-xs font-mono text-gray-400 tracking-wider">
                          {{ $sale->car->stock_code ?? 'N/A' }}
                        </span>
                      </div>
                    </td>
                    <td class="p-5 text-gray-600 font-medium">
                      Rp {{ number_format($baseCost, 0, ',', '.') }}
                    </td>
                    <td class="p-5 font-bold text-gray-900">
                      Rp {{ number_format($dealPrice, 0, ',', '.') }}
                    </td>
                    <td class="p-5 text-right font-black {{ $netProfit >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                      {{ $netProfit >= 0 ? '+' : '' }}Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="p-12 text-center">
                      <div class="flex flex-col items-center justify-center gap-3">
                        <div class="w-14 h-14 bg-gray-50 text-gray-300 rounded-2xl flex items-center justify-center">
                          <i data-lucide="receipt" class="w-7 h-7"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No accepted sales yet.</p>
                        <p class="text-xs text-gray-400 max-w-sm">
                          When you accept an offer from the dashboard, it will appear here automatically.
                        </p>
                        <a
                          href="{{ route('dashboard') }}"
                          class="mt-2 text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
                        >
                          Go to Dashboard <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        if (typeof lucide !== "undefined") {
          lucide.createIcons();
        }
      });
    </script>
    @include('partials.flash')
  </body>
</html>
