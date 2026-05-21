<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Inventory</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style type="text/tailwindcss">
      :root {
        --primary: #165dff;
        --primary-hover: #0e4bd9;
        --foreground: #080c1a;
        --secondary: #6a7686;
        --border: #e5e7eb;
      }

      body {
        font-family: "Lexend Deca", sans-serif;
      }
    </style>
  </head>

  <body class="bg-[#F5F7FB] min-h-screen flex">
    @include('sidebar')
    
    <main class="ml-[280px] flex-1">
      <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Vehicle Inventory</h2>
          <p class="text-gray-500 mt-1 text-sm">
            Manage dealership stock and availability.
          </p>
        </div>

        <button
          onclick="window.location.href='{{ route('AddNew') }}'"
          class="px-6 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold flex items-center gap-3 transition cursor-pointer"
        >
          <i data-lucide="plus"></i>
          Add Vehicle
        </button>
      </header>

      <section class="p-8">
        <div class="grid grid-cols-5 gap-6 mb-8">
          <div class="bg-white border border-gray-200 rounded-3xl p-6">
            <p class="text-gray-500 text-sm mb-3">Total Cars</p>
            <h3 class="text-4xl font-bold">{{ $cars->count() }}</h3>
          </div>

          <div class="bg-white border border-gray-200 rounded-3xl p-6">
            <p class="text-gray-500 text-sm mb-3">Available</p>
            <h3 class="text-4xl font-bold text-green-600">{{ $cars->where('status', 'available')->count() }}</h3>
          </div>

          <div class="bg-white border border-gray-200 rounded-3xl p-6">
            <p class="text-gray-500 text-sm mb-3">Pending</p>
            <h3 class="text-4xl font-bold text-yellow-500">{{ $cars->where('status', 'pending')->count() }}</h3>
          </div>

          <div class="bg-white border border-gray-200 rounded-3xl p-6">
            <p class="text-gray-500 text-sm mb-3">Sold Units</p>
            <h3 class="text-4xl font-bold text-blue-600">{{ $cars->where('status', 'sold')->count() }}</h3>
          </div>

          <div class="bg-white border border-gray-200 rounded-3xl p-6">
            <p class="text-gray-500 text-sm mb-3">Total Stock Value</p>
            <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($cars->sum('price'), 0, ',', '.') }}</h3>
          </div>
        </div>

        <div class="flex gap-4 mb-8">
          <input
            type="text"
            id="searchVehicle"
            placeholder="Search vehicle by brand or model..."
            class="flex-1 h-14 rounded-2xl border border-gray-200 px-5 bg-white outline-none"
          />

          <select
            id="filterBrand"
            class="h-14 px-5 rounded-2xl border border-gray-200 bg-white outline-none cursor-pointer"
          >
            <option value="all">All Brands</option>
            <option value="toyota">Toyota</option>
            <option value="honda">Honda</option>
            <option value="bmw">BMW</option>
            <option value="mercedes">Mercedes</option>
            <option value="hyundai">Hyundai</option>
            <option value="mitsubishi">Mitsubishi</option>
          </select>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left p-6 text-sm text-gray-500">Car</th>
                <th class="text-left p-6 text-sm text-gray-500">Year</th>
                <th class="text-left p-6 text-sm text-gray-500">Transmission</th>
                <th class="text-left p-6 text-sm text-gray-500">Mileage</th>
                <th class="text-left p-6 text-sm text-gray-500">Price</th>
                <th class="text-left p-6 text-sm text-gray-500">Status</th>
                <th class="text-right p-6 text-sm text-gray-500">Action</th>
              </tr>
            </thead>

            <tbody id="vehicleTableBody">
              @forelse($cars as $car)
              <tr class="vehicle-item border-b border-gray-100 hover:bg-gray-50 transition" data-brand="{{ strtolower($car->brand) }}">
                <td class="p-6">
                  <div class="flex items-center gap-3">
                    @if($car->images->isNotEmpty())
                      <img
                        src="{{ asset('storage/' . $car->images->first()->image_path) }}"
                        class="w-20 h-14 object-cover rounded-xl shrink-0 border border-gray-100"
                        alt="{{ $car->model }}"
                      />
                    @else
                      <img
                        src="{{ asset('images/default-car.jpg') }}"
                        class="w-20 h-14 object-cover rounded-xl shrink-0 bg-gray-100"
                        alt="No Image"
                      />
                    @endif

                    <div class="flex flex-col gap-1 min-w-0">
                      <h4 class="font-bold text-gray-950 truncate vehicle-name">
                        {{ $car->brand }} {{ $car->model }}
                      </h4>
                      <p class="text-xs font-mono text-gray-400 tracking-wider">
                        {{ $car->stock_code }}
                      </p>
                    </div>
                  </div>
                </td>

                <td class="p-6 text-gray-700">{{ $car->year }}</td>
                <td class="p-6 text-gray-700">{{ $car->transmission }}</td>
                <td class="p-6 text-gray-700">{{ number_format($car->mileage, 0, ',', '.') }} km</td>
                <td class="p-6 font-bold text-gray-950">Rp {{ number_format($car->price, 0, ',', '.') }}</td>

                <td class="p-6">
                    <form action="{{ route('Car.updateStatus', $car->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <select 
                            name="status" 
                            onchange="this.form.submit()"
                            class="text-xs font-bold uppercase tracking-wide px-3 py-1.5 rounded-full border cursor-pointer outline-none transition-all duration-200
                            {{ strtolower($car->status) == 'available' ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : '' }}
                            {{ strtolower($car->status) == 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : '' }}
                            {{ strtolower($car->status) == 'sold' ? 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200' : '' }}"
                        >
                            <option value="available" {{ strtolower($car->status) == 'available' ? 'selected' : '' }}>🟢 Available</option>
                            <option value="pending" {{ strtolower($car->status) == 'pending' ? 'selected' : '' }}>🟡 Pending</option>
                            <option value="sold" {{ strtolower($car->status) == 'sold' ? 'selected' : '' }}>🔴 Sold</option>
                        </select>
                    </form>
                </td>

                <td class="p-6">
                  <div class="flex justify-end gap-2">
                    <button
                      onclick="window.location.href='{{ route('Car.detail', $car->id) }}'"
                      class="w-10 h-10 rounded-xl hover:bg-blue-100 hover:text-blue-600 flex items-center justify-center transition cursor-pointer"
                      title="View Details & Offers"
                    >
                      <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                    
                    <button
                      onclick="window.location.href='{{ route('Car.edit', $car->id) }}'"
                      class="w-10 h-10 rounded-xl hover:bg-yellow-100 hover:text-yellow-600 flex items-center justify-center transition cursor-pointer"
                      title="Edit Asset"
                    >
                      <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    
                    <form action="{{ route('Car.destroy', $car->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle from the system? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button
                          type="submit"
                          class="w-10 h-10 rounded-xl hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition cursor-pointer text-gray-500"
                          title="Delete Asset"
                        >
                          <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="p-12 text-center text-gray-400 italic bg-gray-50/50">
                  <div class="flex flex-col items-center gap-2">
                    <i data-lucide="package-open" class="w-8 h-8 text-gray-300"></i>
                    <span>No vehicles registered in the showroom system yet.</span>
                  </div>
                </td>
              </tr>
              @endforelse

              <tr id="noResultsRow" class="hidden">
                <td colspan="7" class="p-12 text-center text-gray-400 italic bg-gray-50/50">
                  <div class="flex flex-col items-center gap-2">
                    <i data-lucide="search-code" class="w-8 h-8 text-gray-300"></i>
                    <span>No vehicles match your search criteria.</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("searchVehicle");
        const brandSelect = document.getElementById("filterBrand");
        const vehicleItems = document.querySelectorAll(".vehicle-item");
        const noResultsRow = document.getElementById("noResultsRow");

        function filterVehicles() {
          const searchQuery = searchInput.value.toLowerCase().trim();
          const selectedBrand = brandSelect.value.toLowerCase();
          let visibleCount = 0;

          vehicleItems.forEach((item) => {
            const vehicleNameEle = item.querySelector(".vehicle-name");
            const vehicleName = vehicleNameEle ? vehicleNameEle.textContent.toLowerCase() : "";
            const vehicleBrand = item.getAttribute("data-brand") || "";

            const matchesSearch = vehicleName.includes(searchQuery);
            const matchesBrand = selectedBrand === "all" || vehicleBrand === selectedBrand;

            if (matchesSearch && matchesBrand) {
              item.style.display = ""; 
              visibleCount++;
            } else {
              item.style.display = "none"; 
            }
          });

          if (visibleCount === 0) {
            noResultsRow.classList.remove("hidden");
          } else {
            noResultsRow.classList.add("hidden");
          }
        }

        if (searchInput) searchInput.addEventListener("input", filterVehicles);
        if (brandSelect) brandSelect.addEventListener("change", filterVehicles);
      });
    </script>

    @if(session('success'))
    <script>
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Great!'
        });
    </script>
    @endif
  </body>
</html>