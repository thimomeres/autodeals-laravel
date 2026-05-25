<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Profil Saya</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>
  <body class="bg-[#F5F7FB] min-h-screen flex">
    @include('sidebar')

    <main class="ml-[280px] flex-1">
      <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Profil Saya</h2>
          <p class="text-gray-500 mt-1 text-sm">Perbarui password akun Anda.</p>
        </div>
      </header>

      <section class="p-8 max-w-lg">
        <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm mb-6">
          <p class="text-sm text-gray-500">Nama</p>
          <p class="font-bold text-lg">{{ $user->name }}</p>
          <p class="text-sm text-gray-500 mt-4">Email</p>
          <p class="font-bold">{{ $user->email }}</p>
          <p class="text-sm text-gray-500 mt-4">Role</p>
          <p class="font-bold">{{ $user->roleLabel() }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
          <h3 class="font-bold text-gray-900 mb-4">Ganti Password</h3>
          <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Password Saat Ini</label>
              <input type="password" name="current_password" required class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Password Baru</label>
              <input type="password" name="password" required minlength="8" class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Konfirmasi Password Baru</label>
              <input type="password" name="password_confirmation" required class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" />
            </div>
            <button type="submit" class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl">Perbarui Password</button>
          </form>
        </div>
      </section>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.flash')
  </body>
</html>
