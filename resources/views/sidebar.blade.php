<aside
  class="w-[280px] bg-white border-r border-gray-200 fixed inset-y-0 left-0 flex flex-col"
>
  <div class="h-[90px] border-b border-gray-200 flex items-center gap-4 px-6">
    <div
      class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center"
    >
      <i data-lucide="car-front"></i>
    </div>
    <h1 class="text-2xl font-bold">AutoDeals</h1>
  </div>

  <div class="flex-1 p-6 overflow-y-auto">
    <p class="text-xs uppercase text-gray-400 font-bold mb-5">Main Menu</p>
    <div class="space-y-2" id="sidebar-menu-links">
      <a
        href="{{ route('dashboard') }}"
        data-page="dashboard"
        class="nav-link flex items-center gap-3 px-4 h-14 rounded-2xl hover:bg-gray-100 text-gray-600"
      >
        <i data-lucide="layout-dashboard"></i>
        <span>Dashboard</span>
      </a>

      <a
        href="{{ route('inventory') }}"
        data-page="infentory"
        class="nav-link flex items-center gap-3 px-4 h-14 rounded-2xl hover:bg-gray-100 text-gray-600"
      >
        <i data-lucide="package"></i>
        <span>Inventory</span>
      </a>

      <a
        href="{{ route('sales') }}"
        data-page="sales"
        class="nav-link flex items-center gap-3 px-4 h-14 rounded-2xl hover:bg-gray-100 text-gray-600"
      >
        <i data-lucide="trending-up"></i>
        <span>Sales</span>
      </a>
    </div>

    @if(auth()->user()?->isOwner())
      <p class="text-xs uppercase text-gray-400 font-bold mb-5 mt-8">Owner</p>
      <div class="space-y-2">
        <a
          href="{{ route('users.index') }}"
          data-page="users"
          class="nav-link flex items-center gap-3 px-4 h-12 rounded-2xl hover:bg-gray-100 text-gray-600 text-sm"
        >
          <i data-lucide="users"></i>
          <span>Kelola Admin</span>
        </a>
        <a
          href="{{ route('activity.index') }}"
          data-page="activity"
          class="nav-link flex items-center gap-3 px-4 h-12 rounded-2xl hover:bg-gray-100 text-gray-600 text-sm"
        >
          <i data-lucide="scroll-text"></i>
          <span>Audit Trail</span>
        </a>
      </div>
    @endif

    <p class="text-xs uppercase text-gray-400 font-bold mb-5 mt-8">Akun</p>
    <a
      href="{{ route('profile.edit') }}"
      data-page="profile"
      class="nav-link flex items-center gap-3 px-4 h-12 rounded-2xl hover:bg-gray-100 text-gray-600 text-sm"
    >
      <i data-lucide="user-cog"></i>
      <span>Profil & Password</span>
    </a>
  </div>

  <div class="border-t border-gray-200 p-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img src="{{ asset('images/Loginlogo.jpg') }}" class="w-11 h-11 rounded-full object-cover" />
      <div>
        <h4 class="font-bold text-sm">{{ Auth::user()->name }}</h4>
        <p class="text-xs text-gray-500">{{ Auth::user()->roleLabel() }}</p>
      </div>
    </div>

    <form action="{{ route('logout') }}" method="POST" class="inline">
      @csrf
      <button
        type="submit"
        class="w-10 h-10 rounded-xl hover:bg-red-100 hover:text-red-500 transition flex items-center justify-center"
      >
        <i data-lucide="log-out"></i>
      </button>
    </form>
  </div>
</aside>
