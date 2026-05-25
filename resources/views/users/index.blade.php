<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Admin Users</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>
  <body class="bg-[#F5F7FB] min-h-screen flex">
    @include('sidebar')

    <main class="ml-[280px] flex-1">
      <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Kelola Admin</h2>
          <p class="text-gray-500 mt-1 text-sm">Tambah atau hapus akun Owner / Staff.</p>
        </div>
      </header>

      <section class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white border border-gray-200 rounded-3xl p-8 shadow-sm">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Tambah Pengguna Baru</h3>
          <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Nama</label>
              <input name="name" required class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" value="{{ old('name') }}" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Email</label>
              <input type="email" name="email" required class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" value="{{ old('email') }}" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Password</label>
              <input type="password" name="password" required minlength="8" class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Konfirmasi Password</label>
              <input type="password" name="password_confirmation" required class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200" />
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 uppercase">Role</label>
              <select name="role" class="w-full mt-1 h-12 px-4 rounded-xl border border-gray-200">
                <option value="staff">Staff</option>
                <option value="owner">Owner</option>
              </select>
            </div>
            <button type="submit" class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl">Simpan Pengguna</button>
          </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Nama</th>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Email</th>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Role</th>
                <th class="text-right p-4 text-xs font-bold text-gray-400 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($users as $user)
                <tr>
                  <td class="p-4 font-bold">{{ $user->name }}</td>
                  <td class="p-4 text-gray-600">{{ $user->email }}</td>
                  <td class="p-4">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $user->isOwner() ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                      {{ $user->roleLabel() }}
                    </span>
                  </td>
                  <td class="p-4 text-right">
                    @if($user->id !== auth()->id())
                      <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 text-xs font-bold hover:underline">Hapus</button>
                      </form>
                    @else
                      <span class="text-xs text-gray-400">Akun aktif</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.flash')
  </body>
</html>
