<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - {{ isset($car) ? 'Edit Vehicle' : 'Add New Vehicle' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>

  <body class="bg-[#F5F7FB]">
    @include('sidebar')

    <div id="pageLoader" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
      <div class="w-14 h-14 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="flex min-h-screen">
      <main class="main-content">
        <header class="navbar">
          <div>
            <h2 class="page-title">{{ isset($car) ? 'Edit Vehicle' : 'Add New Vehicle' }}</h2>
            <p class="page-subtitle">{{ isset($car) ? 'Modify vehicle specifications and metadata' : 'Add a new car into dealership inventory' }}</p>
          </div>
        </header>

        <section class="content-wrapper">
        <form id="{{ isset($car) ? 'vehicleFormEdit' : 'vehicleForm' }}" 
        action="{{ isset($car) ? route('Car.update', $car->id) : route('Car.store') }}" 
        method="POST" 
        enctype="multipart/form-data" 
        class="space-y-6">
        @csrf
        
        @if(isset($car))
            @method('PUT')
        @endif

            <div class="card-box">
              <div class="card-header">
                <h3>Basic Information</h3>
                <p>Fill primary vehicle metadata and branding identity.</p>
              </div>

              <div class="form-grid">
                <div>
                  <label class="input-label"> Stock Code </label>
                  <input
                    type="text"
                    name="stock_code"
                    value="{{ isset($car) ? $car->stock_code : 'AUTO-'.rand(100000, 999999) }}" 
                    readonly
                    class="input-control readonly"
                  />
                </div>

                <div>
                  <label class="input-label"> Condition </label>
                  <select name="condition" class="input-control">
                    <option value="Grade A (Like New)" {{ (isset($car) && $car->condition == 'Grade A (Like New)') ? 'selected' : '' }}>Grade A (Like New)</option>
                    <option value="Grade B (Good Condition)" {{ (isset($car) && $car->condition == 'Grade B (Good Condition)') ? 'selected' : '' }}>Grade B (Good Condition)</option>
                    <option value="Grade C (Fair Condition)" {{ (isset($car) && $car->condition == 'Grade C (Fair Condition)') ? 'selected' : '' }}>Grade C (Fair Condition)</option>
                  </select>
                </div>

                <div>
                  <label class="input-label"> Brand </label>
                  <select name="brand" class="input-control">
                    <option value="Toyota" {{ (isset($car) && $car->brand == 'Toyota') ? 'selected' : '' }}>Toyota</option>
                    <option value="Honda" {{ (isset($car) && $car->brand == 'Honda') ? 'selected' : '' }}>Honda</option>
                    <option value="BMW" {{ (isset($car) && $car->brand == 'BMW') ? 'selected' : '' }}>BMW</option>
                    <option value="Hyundai" {{ (isset($car) && $car->brand == 'Hyundai') ? 'selected' : '' }}>Hyundai</option>
                    <option value="Mitsubishi" {{ (isset($car) && $car->brand == 'Mitsubishi') ? 'selected' : '' }}>Mitsubishi</option>
                  </select>
                </div>

                <div>
                  <label class="input-label"> Model </label>
                  <input
                    type="text"
                    name="model"
                    value="{{ isset($car) ? $car->model : '' }}"
                    required
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> Year </label>
                  <input
                    type="number"
                    name="year"
                    value="{{ isset($car) ? $car->year : '' }}"
                    required
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> Price (Rupiah) </label>
                  <input
                    type="number"
                    name="price"
                    value="{{ isset($car) ? $car->price : '' }}"
                    required
                    class="input-control"
                  />
                </div>
              </div>
            </div>

            <div class="card-box">
              <div class="card-header">
                <h3>Specifications</h3>
                <p>Detailed technical information about the physical asset.</p>
              </div>

              <div class="form-grid">
                <div>
                  <label class="input-label"> Mileage KM </label>
                  <input
                    type="number"
                    name="mileage"
                    value="{{ isset($car) ? $car->mileage : '' }}"
                    required
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> Color </label>
                  <select name="color" class="input-control">
                    <option value="Black" {{ (isset($car) && $car->color == 'Black') ? 'selected' : '' }}>Black</option>
                    <option value="White" {{ (isset($car) && $car->color == 'White') ? 'selected' : '' }}>White</option>
                    <option value="Silver" {{ (isset($car) && $car->color == 'Silver') ? 'selected' : '' }}>Silver</option>
                    <option value="Gray" {{ (isset($car) && $car->color == 'Gray') ? 'selected' : '' }}>Gray</option>
                    <option value="Yellow" {{ (isset($car) && $car->color == 'Yellow') ? 'selected' : '' }}>Yellow</option>
                    <option value="Red" {{ (isset($car) && $car->color == 'Red') ? 'selected' : '' }}>Red</option>
                    <option value="Navy" {{ (isset($car) && $car->color == 'Navy') ? 'selected' : '' }}>Navy</option>
                  </select>
                </div>

                <div>
                  <label class="input-label"> Transmission </label>
                  <select name="transmission" class="input-control">
                    <option value="Automatic" {{ (isset($car) && $car->transmission == 'Automatic') ? 'selected' : '' }}>Automatic</option>
                    <option value="Manual" {{ (isset($car) && $car->transmission == 'Manual') ? 'selected' : '' }}>Manual</option>
                  </select>
                </div>

                <div>
                  <label class="input-label"> Fuel Type </label>
                  <select name="fuel_type" class="input-control">
                    <option value="Gasoline" {{ (isset($car) && $car->fuel_type == 'Gasoline') ? 'selected' : '' }}>Gasoline</option>
                    <option value="Diesel" {{ (isset($car) && $car->fuel_type == 'Diesel') ? 'selected' : '' }}>Diesel</option>
                    <option value="Hybrid" {{ (isset($car) && $car->fuel_type == 'Hybrid') ? 'selected' : '' }}>Hybrid</option>
                  </select>
                </div>

                <div>
                  <label class="input-label"> Engine CC </label>
                  <input
                    type="number"
                    name="engine_capacity_cc"
                    value="{{ isset($car) ? $car->engine_capacity_cc : '' }}"
                    required
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> Plate Number </label>
                  <input
                    type="text"
                    name="plate_number"
                    value="{{ isset($car) ? $car->plate_number : '' }}"
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> Fuel Tank Capacity (Liters) </label>
                  <input
                    type="number"
                    name="fuel_tank_capacity"
                    value="{{ isset($car) ? $car->fuel_tank_capacity : '' }}"
                    required
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> Seating Capacity </label>
                  <input
                    type="number"
                    name="seating_capacity"
                    value="{{ isset($car) ? $car->seating_capacity : '' }}"
                    required
                    class="input-control"
                  />
                </div>

                <div>
                  <label class="input-label"> VIN Number </label>
                  <input
                    type="text"
                    name="vin_number"
                    value="{{ isset($car) ? $car->vin_number : '' }}"
                    class="input-control"
                  />
                </div>
              </div>

              <div class="mt-6">
                <label class="input-label"> Description </label>
                <textarea
                  name="description"
                  rows="5"
                  class="textarea-control"
                  placeholder="Vehicle description..."
                >{{ isset($car) ? $car->description : '' }}</textarea>
              </div>

              @if(isset($car))
              <div class="mt-6">
                <label class="input-label"> Current Photos </label>
                <div class="flex gap-4 mt-2 flex-wrap">
                  @forelse($car->images as $img)
                    <div class="relative group w-24 h-20 rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                      <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 italic">No image registered.</p>
                  @endforelse
                </div>
              </div>
              @endif

              <div class="mt-6">
                <label class="input-label"> 
                  {{ isset($car) ? 'Upload New Photos (Optional, replaces old ones)' : 'Upload Vehicle Photos' }} 
                </label>
                <div class="upload-box p-6 border-2 border-dashed border-gray-200 rounded-2xl text-center cursor-pointer hover:bg-gray-50 transition mt-2">
                  <input type="file" id="carImageInput" name="images[]" multiple hidden />
                  <div class="upload-icon text-gray-400 flex justify-center mb-2">
                      <i data-lucide="image-plus" class="w-8 h-8"></i>
                  </div>
                  <h4 class="text-sm font-semibold text-gray-700">Upload Photos</h4>
                  <div id="previewContainer" class="flex gap-4 flex-wrap mt-4"></div>
                </div> 
              </div>
              <div class="button-wrapper mt-8 border-t border-gray-100 pt-6 flex justify-end gap-3">
                <a
                    href="{{ route('inventory') }}"
                    class="cancel-btn px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition inline-flex items-center"
                >
                    Cancel
                </a>
                
                <button type="submit" class="submit-btn px-6 py-2.5 rounded-xl {{ isset($car) ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-semibold text-sm transition shadow-sm">
                    {{ isset($car) ? 'Update Vehicle' : 'Save Vehicle' }}
                </button>
             </div>
            </div>
          </form>
        </section>
      </main>
    </div>

    <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
    <script>
  // Memastikan jika form dalam mode edit, submit berjalan murni lewat HTML Laravel (PUT)
  const editForm = document.getElementById('vehicleFormEdit');
  if (editForm) {
      editForm.addEventListener('submit', function() {
          // Matikan loader jika mengganggu proses submit
          const loader = document.getElementById("pageLoader");
          if (loader) loader.style.display = 'none';
      });
  }
</script>
  </body>
</html>