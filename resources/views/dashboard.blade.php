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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @vite(['resources/js/dashboard.js'])

    <style>
      @keyframes offerSlideIn {
        from {
          opacity: 0;
          transform: translateY(-8px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      .animate-in {
        animation: offerSlideIn 0.35s ease-out;
      }
    </style>
  </head>

  <body class="bg-[#F5F7FB]">
    @include('sidebar')

    <div class="flex min-h-screen">
      <main class="ml-[280px] flex-1">
        
        <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center justify-between">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">Dashboard Overview</h2>
            <p class="text-gray-500 mt-1 text-sm">
              Welcome back, Admin. Here is your showroom performance today.
            </p>
          </div>

          <div class="flex items-center gap-4 relative" x-data="{ open: false }">
            <button 
              type="button" 
              @click="open = !open"
              @click.outside="open = false"
              class="w-12 h-12 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-2xl flex items-center justify-center border border-gray-200 relative transition cursor-pointer"
              title="Notifications"
            >
              <i data-lucide="bell" class="w-5 h-5"></i>
              <span
                id="pending-offers-badge"
                class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center ring-2 ring-white animate-bounce {{ $unreadNotificationsCount > 0 ? '' : 'hidden' }}"
              >
                {{ $unreadNotificationsCount }}
              </span>
            </button>

            <div 
              x-show="open"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-95 translate-y-2"
              x-transition:enter-end="opacity-100 scale-100 translate-y-0"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 scale-100 translate-y-0"
              x-transition:leave-end="opacity-0 scale-95 translate-y-2"
              class="absolute right-0 top-14 w-96 bg-white border border-gray-200/80 rounded-3xl shadow-xl z-50 overflow-hidden"
              style="display: none;"
            >
              <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-gray-900">Active Notifications</span>
                  <span id="pending-offers-dropdown-count" class="bg-blue-50 text-blue-600 text-xs px-2.5 py-0.5 rounded-full font-bold">
                    {{ $unreadNotificationsCount }} New
                  </span>
                </div>
                <span class="text-xs text-gray-400 font-medium">Offers Action</span>
              </div>

              <div id="notifications-list" class="max-h-[320px] overflow-y-auto divide-y divide-gray-50">
                @forelse($recentOffers as $offer)
                  <div
                    data-offer-id="{{ $offer->id }}"
                    @click="openReviewModal('{{ $offer->id }}', '{{ $offer->buyer_name }}', '{{ $offer->car->brand ?? 'Car' }} {{ $offer->car->model ?? '' }}', '{{ number_format($offer->price_offered, 0, ',', '.') }}'); open = false;"
                    class="p-4 hover:bg-gray-50/80 transition flex gap-3.5 items-start cursor-pointer offer-notification-item"
                  >
                    <div class="w-9 h-9 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                      <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-xs text-gray-500 font-medium">New Offer Submitted</p>
                      <p class="text-sm font-bold text-gray-900 truncate mt-0.5">
                        {{ $offer->buyer_name }}
                      </p>
                      <p class="text-xs text-gray-600 mt-1 bg-gray-100 px-2 py-1 rounded-lg inline-block font-medium">
                        Target: {{ $offer->car->brand ?? 'Car' }} {{ $offer->car->model ?? '' }}
                      </p>
                      <div class="flex items-center justify-between mt-2.5">
                        <span class="text-xs font-black text-blue-600">
                          Rp {{ number_format($offer->price_offered, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">
                          Review
                        </span>
                      </div>
                    </div>
                  </div>
                @empty
                  <div id="notifications-empty" class="p-8 text-center flex flex-col items-center justify-center gap-2">
                    <div class="w-12 h-12 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center">
                      <i data-lucide="bell-off" class="w-5 h-5"></i>
                    </div>
                    <p class="text-xs text-gray-400 font-medium">All caught up! No new offers.</p>
                  </div>
                @endforelse
              </div>

              <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                <a href="#recent-offers-section" @click="open = false" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition inline-flex items-center gap-1">
                  View All Actions <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
              </div>
            </div>
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
                <span
                  id="pending-offers-widget-pulse"
                  class="absolute top-3 right-3 w-2.5 h-2.5 bg-rose-500 rounded-full animate-pulse {{ $unreadNotificationsCount > 0 ? '' : 'hidden' }}"
                ></span>
              </div>
              <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  Active Pending Offers
                </p>
                <h3 id="pending-offers-widget-count" class="text-2xl font-black text-gray-900 mt-1">
                  {{ $unreadNotificationsCount }} Offers
                </h3>
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

          <div id="recent-offers-section" class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
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
                <tbody id="pending-offers-table-body" class="divide-y divide-gray-100">
                  @forelse($recentOffers as $offer)
                    <tr data-offer-id="{{ $offer->id }}" class="hover:bg-gray-50/60 transition offer-table-row">
                      <td class="p-4 font-bold text-gray-950">{{ $offer->buyer_name }}</td>
                      <td class="p-4">
                        @if($offer->car)
                          <span class="font-medium text-gray-900 block">
                            {{ $offer->car->brand }} {{ $offer->car->model }}
                          </span>
                          <span class="text-xs text-gray-400 font-mono">
                            {{ $offer->car->stock_code ?? 'N/A' }}
                          </span>
                        @else
                          <span class="text-gray-400 italic">Vehicle data missing</span>
                        @endif
                      </td>
                      <td class="p-4 font-bold text-blue-600">
                        Rp {{ number_format($offer->price_offered, 0, ',', '.') }}
                      </td>
                      <td class="p-4">
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-xs font-semibold capitalize">
                          {{ str_replace('_', ' ', $offer->status) }}
                        </span>
                      </td>
                      <td class="p-4 text-center">
                        <button 
                          type="button"
                          onclick="openReviewModal('{{ $offer->id }}', '{{ $offer->buyer_name }}', '{{ $offer->car->brand ?? 'Car' }} {{ $offer->car->model ?? '' }}', '{{ number_format($offer->price_offered, 0, ',', '.') }}')"
                          class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-blue-600 hover:text-white text-gray-700 font-bold rounded-lg transition cursor-pointer"
                        >
                          Review Offer
                        </button>

                        <form id="accept-form-{{ $offer->id }}" action="{{ route('offers.accept', $offer->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('PATCH')
                        </form>
                        <form id="reject-form-{{ $offer->id }}" action="{{ route('offers.reject', $offer->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('PATCH')
                        </form>
                      </td>
                    </tr>
                  @empty
                    <tr id="pending-offers-empty-row">
                      <td colspan="5" class="p-8 text-center text-gray-400 font-medium">
                        No pending offers available at the moment.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
      window.AutodealsConfig = {
        csrfToken: @json(csrf_token()),
        offerAcceptUrl: (id) => `/offers/${id}/accept`,
        offerRejectUrl: (id) => `/offers/${id}/reject`,
      };

      document.addEventListener("DOMContentLoaded", function() {
        lucide.createIcons();
      });

      // =========================================================================
      // ⚡ MODAL INTERAKTIF PENINJAUAN TAWARAN (SWEETALERT2)
      // =========================================================================
      window.openReviewModal = function openReviewModal(offerId, buyerName, carInfo, priceOffered) {
          Swal.fire({
              title: 'Review Vehicle Offer',
              html: `
                  <div class="text-left bg-gray-50 p-4 rounded-2xl border border-gray-200/60 text-sm space-y-2">
                      <div><span class="text-gray-400 font-medium">Buyer Name:</span> <strong class="text-gray-900 block text-base">${buyerName}</strong></div>
                      <div><span class="text-gray-400 font-medium">Target Vehicle:</span> <strong class="text-gray-800 block">${carInfo}</strong></div>
                      <div class="pt-1"><span class="text-gray-400 font-medium">Price Offered:</span> <strong class="text-blue-600 block text-lg font-black">Rp ${priceOffered}</strong></div>
                  </div>
                  <p class="text-[11px] text-gray-400 mt-4 text-center leading-relaxed">
                      Accepting this will mark the car as <strong>SOLD</strong> and automatically <strong>REJECT</strong> all other pending offers for this asset.
                  </p>
              `,
              icon: 'info',
              showCancelButton: true,
              showDenyButton: true,
              confirmButtonColor: '#10B981', // Emerald-500
              denyButtonColor: '#EF4444',    // Rose-500
              cancelButtonColor: '#9CA3AF',  // Gray-400
              confirmButtonText: '✅ Accept Offer',
              denyButtonText: '❌ Reject Offer',
              cancelButtonText: 'Decide Later',
              customClass: {
                  popup: 'rounded-3xl p-6',
                  confirmButton: 'px-4 py-2.5 rounded-xl font-bold text-xs cursor-pointer',
                  denyButton: 'px-4 py-2.5 rounded-xl font-bold text-xs cursor-pointer',
                  cancelButton: 'px-4 py-2.5 rounded-xl font-medium text-xs cursor-pointer'
              }
          }).then((result) => {
              if (result.isConfirmed) {
                  // Kirim form accept
                  document.getElementById(`accept-form-${offerId}`).submit();
              } else if (result.isDenied) {
                  // Kirim form reject
                  document.getElementById(`reject-form-${offerId}`).submit();
              }
          });
      }
    </script>

    @include('partials.flash')
  </body>
</html>