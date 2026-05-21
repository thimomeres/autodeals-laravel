<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Vehicle Detail</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>

  <body class="bg-[#F5F7FB]">
    <div id="pageLoader" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
      <div class="w-14 h-14 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="flex min-h-screen">
      
      @include('sidebar')

      <main class="main-content">
        <header class="navbar flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button
                onclick="window.location.href='{{ route('inventory') }}'"
                class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 transition cursor-pointer"
                >
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </button>
                <div>
                <h2 class="page-title">Vehicle Details</h2>
                <p class="page-subtitle">Review specification details and negotiation history</p>
                </div>
            </div>

            <div class="flex gap-3 items-center">
                <button
                onclick="window.location.href='{{ route('Car.edit', $car->id) }}'"
                class="px-5 h-11 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold flex items-center gap-2 transition text-sm cursor-pointer"
                >
                <i data-lucide="edit" class="w-4 h-4"></i> Edit Unit
                </button>

                <form 
                action="{{ route('Car.destroy', $car->id) }}" 
                method="POST" 
                onsubmit="return confirm('Are you sure you want to delete this vehicle from the system? This action cannot be undone.');"
                class="inline-block"
                >
                  @csrf
                  @method('DELETE')
                  
                  <button
                      type="submit"
                      class="px-5 h-11 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold flex items-center gap-2 transition text-sm cursor-pointer shadow-sm"
                      title="Delete Vehicle"
                  >
                      <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                  </button>
                </form>
            </div>
         </header>
       

        <section class="content-wrapper">
          <div class="card-box !overflow-hidden">
            
            <div class="w-full bg-gray-900 p-6">
              <div class="relative h-[450px] w-full rounded-2xl overflow-hidden shadow-md bg-gray-800 group">
                
                <div id="sliderWrapper" class="flex transition-transform duration-500 ease-out h-full w-full">
                  @if($car->images->isNotEmpty())
                    @foreach($car->images as $img)
                      <div class="w-full h-full flex-shrink-0">
                        <img
                          src="{{ asset('storage/' . $img->image_path) }}"
                          class="w-full h-full object-cover select-none"
                          alt="Car Image"
                        />
                      </div>
                    @endforeach
                  @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-800 gap-2">
                      <i data-lucide="image-off" class="w-12 h-12"></i>
                      <p class="text-sm italic">No Preview Available</p>
                    </div>
                  @endif
                </div>

                <div class="absolute bottom-6 left-8 flex gap-3 z-10">
                  <span class="px-4 py-1.5 rounded-xl bg-green-500 text-white text-xs font-bold tracking-wide shadow-lg uppercase">
                    {{ $car->status }}
                  </span>
                  <span class="px-4 py-1.5 rounded-xl bg-blue-600 text-white text-xs font-bold tracking-wide shadow-lg">
                    {{ $car->condition }}
                  </span>
                </div>

                @if($car->images->count() > 1)
                  <button
                    type="button"
                    onclick="moveSlider(-1)"
                    class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 w-11 h-11 rounded-full shadow-lg transition opacity-0 group-hover:opacity-100 flex items-center justify-center z-10 cursor-pointer"
                  >
                    <i data-lucide="chevron-left" class="w-6 h-6"></i>
                  </button>
                  
                  <button
                    type="button"
                    onclick="moveSlider(1)"
                    class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 w-11 h-11 rounded-full shadow-lg transition opacity-0 group-hover:opacity-100 flex items-center justify-center z-10 cursor-pointer"
                  >
                    <i data-lucide="chevron-right" class="w-6 h-6"></i>
                  </button>

                  <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    @foreach($car->images as $index => $img)
                      <span
                        class="slider-dot w-2.5 h-2.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-blue-500 w-6' : 'bg-white/50' }}"
                      ></span>
                    @endforeach
                  </div>
                @endif

              </div>
            </div>

            <div class="p-10">
              <div class="border-b border-gray-100 pb-6 mb-6">
                <span class="text-xs font-mono bg-gray-100 text-gray-500 px-3 py-1 rounded-md">
                  {{ $car->stock_code }}
                </span>
                <h3 class="text-3xl font-bold text-gray-900 mt-3">
                  {{ $car->brand }} {{ $car->model }}
                </h3>
                <p class="text-3xl font-black text-blue-600 mt-2">
                  Rp {{ number_format($car->price, 0, ',', '.') }}
                </p>
              </div>

              <h4 class="text-xs uppercase text-gray-400 font-bold mb-4 tracking-wider">
                Technical Specifications
              </h4>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 text-sm border-b border-gray-100 pb-8">
                <div>
                  <p class="text-gray-400 mb-1">Brand</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->brand }}</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Model</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->model }}</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Year</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->year }}</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Mileage</p>
                  <p class="font-semibold text-gray-900 text-base">{{ number_format($car->mileage, 0, ',', '.') }} KM</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Color</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->color }}</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Transmission</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->transmission }}</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Fuel Type</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->fuel_type }}</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Engine CC</p>
                  <p class="font-semibold text-gray-900 text-base">{{ number_format($car->engine_capacity_cc, 0, ',', '.') }} CC</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Plate Number</p>
                  <p class="font-semibold text-gray-900 text-base font-mono uppercase">{{ $car->plate_number ?? '-' }}</p>
                </div>
                
                <div>
                  <p class="text-gray-400 mb-1">Fuel Tank Capacity</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->fuel_tank_capacity }} Liter</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Seating Capacity</p>
                  <p class="font-semibold text-gray-900 text-base">{{ $car->seating_capacity }} Seats</p>
                </div>
                <div>
                  <p class="text-gray-400 mb-1">Uploader Admin</p>
                  <p class="font-semibold text-blue-600 text-base">
                    <i data-lucide="user" class="w-4 h-4 inline mr-1"></i>{{ $car->user->name ?? 'System' }}
                  </p>
                </div>

                <div class="md:col-span-3">
                  <p class="text-gray-400 mb-1">VIN Number</p>
                  <p class="font-semibold text-gray-900 text-base font-mono tracking-wide uppercase">
                    {{ $car->vin_number ?? '-' }}
                  </p>
                </div>
              </div>

              <div class="mt-6">
                <h4 class="text-xs uppercase text-gray-400 font-bold mb-2 tracking-wider">
                  Description
                </h4>
                <p class="text-gray-600 leading-relaxed text-sm">
                  {{ $car->description ?? 'No description provided for this vehicle.' }}
                </p>
              </div>
            </div>
          </div>

          <div class="card-box p-10">
            <div class="mb-6">
              <h3 class="text-xl font-bold text-gray-900">Offers & Negotiation History</h3>
              <p class="text-gray-500 text-sm mt-1">Logs of prospective customers who gave offers to this car unit.</p>
            </div>

            <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Buyer Name</th>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Price Offered</th>
                    <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  @forelse($car->inquiries as $inquiry)
                    <tr class="hover:bg-gray-50/60 transition">
                      <td class="p-4 font-bold text-gray-950">{{ $inquiry->buyer_name }}</td>
                      <td class="p-4 text-gray-500">{{ $inquiry->created_at->format('M d, Y') }}</td>
                      <td class="p-4 font-bold text-gray-900">Rp {{ number_format($inquiry->price_offered, 0, ',', '.') }}</td>
                      <td class="p-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $inquiry->status == 'Approved' ? 'bg-green-50 text-green-600' : 'bg-rose-50 text-rose-600' }}">
                          {{ $inquiry->status }}
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="p-6 text-center text-gray-400 italic">No offers received yet for this vehicle.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </main>
    </div>

    <script>
      let currentSlide = 0;
      const totalSlides = {{ $car->images->count() }};
      const sliderWrapper = document.getElementById('sliderWrapper');
      const dots = document.querySelectorAll('.slider-dot');

      function moveSlider(direction) {
        if (totalSlides <= 1 || !sliderWrapper) return;

        currentSlide += direction;

        if (currentSlide >= totalSlides) {
          currentSlide = 0;
        } else if (currentSlide < 0) {
          currentSlide = totalSlides - 1;
        }

        sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;

        dots.forEach((dot, index) => {
          if (index === currentSlide) {
            dot.classList.add('bg-blue-500', 'w-6');
            dot.classList.remove('bg-white/50');
          } else {
            dot.classList.remove('bg-blue-500', 'w-6');
            dot.classList.add('bg-white/50');
          }
        });
      }
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
  </body>
</html>