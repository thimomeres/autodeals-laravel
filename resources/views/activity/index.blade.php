<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoDeals - Activity Log</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
  </head>
  <body class="bg-[#F5F7FB] min-h-screen flex">
    @include('sidebar')

    <main class="ml-[280px] flex-1">
      <header class="h-[90px] bg-white border-b border-gray-200 px-8 flex items-center">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Audit Trail</h2>
          <p class="text-gray-500 mt-1 text-sm">Riwayat aktivitas admin di sistem.</p>
        </div>
      </header>

      <section class="p-8">
        <div class="bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Waktu</th>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Admin</th>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Aksi</th>
                <th class="text-left p-4 text-xs font-bold text-gray-400 uppercase">Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($logs as $log)
                <tr class="hover:bg-gray-50/50">
                  <td class="p-4 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                  <td class="p-4 font-medium">{{ $log->user->name ?? 'Sistem / API' }}</td>
                  <td class="p-4">
                    <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">{{ $log->action }}</span>
                  </td>
                  <td class="p-4 text-gray-800">{{ $log->description }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="p-8 text-center text-gray-400">Belum ada aktivitas tercatat.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
        </div>
      </section>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>lucide.createIcons();</script>
  </body>
</html>
