<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AutoDeals - Admin Login</title>

  <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <script src="https://unpkg.com/lucide@latest"></script>

  <style type="text/tailwindcss">
    :root{
      --primary:#165DFF;
      --primary-hover:#0E4BD9;
      --foreground:#080C1A;
      --secondary:#6A7686;
      --muted:#EFF2F7;
      --border:#E5E7EB;
    }

    body{
      font-family:'Lexend Deca',sans-serif;
    }
  </style>
</head>

<body class="bg-[#F5F7FB] min-h-screen overflow-hidden">

<div class="grid lg:grid-cols-2 min-h-screen">

  <!-- LEFT SIDE -->
  <div class="flex items-center justify-center px-6 py-10 bg-white">

    <div class="w-full max-w-md">

      <!-- LOGO -->
      <div class="flex items-center gap-4 mb-10">

        <div class="w-14 h-14 rounded-2xl bg-[var(--primary)] flex items-center justify-center shadow-lg shadow-blue-200">
          <i data-lucide="car-front" class="w-7 h-7 text-white"></i>
        </div>

        <div>
          <h1 class="text-3xl font-bold text-[var(--foreground)]">
            AutoDeals
          </h1>

          <p class="text-sm text-[var(--secondary)] mt-1">
            Car Dealership Management
          </p>
        </div>

      </div>

      <!-- HEADING -->
      <div class="mb-8">
        <h2 class="text-4xl font-bold text-[var(--foreground)] leading-tight mb-3">
          Welcome 
        </h2>

        <p class="text-[var(--secondary)] leading-relaxed">
          Login to manage vehicle inventory, sales, and dealership operations.
        </p>
      </div>

      <!-- FORM -->
     <form class="space-y-5" action="{{ route('login.post') }}" method="POST">
  
  @csrf

  <div>
    <label class="block text-sm font-semibold text-[var(--foreground)] mb-2">
      Email Address
    </label>
    <div class="relative">
      <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
      
      <input
        type="email"
        name="email"
        placeholder="admin@autodeals.com"
        value="{{ old('email') }}"
        class="w-full h-14 pl-12 pr-4 rounded-2xl border border-[var(--border)] bg-[#F9FAFB] focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium"
        required
      />
    </div>
    @error('email')
      <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <div class="flex items-center justify-between mb-2">
      <label class="text-sm font-semibold text-[var(--foreground)]">
        Password
      </label>
      <a href="#" class="text-sm font-medium text-[var(--primary)] hover:underline">
        Forgot Password?
      </a>
    </div>
    <div class="relative">
      <i data-lucide="lock-keyhole" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
      
      <input
        id="password"
        type="password"
        name="password"
        placeholder="Enter your password"
        class="w-full h-14 pl-12 pr-14 rounded-2xl border border-[var(--border)] bg-[#F9FAFB] focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium"
        required
      />
      <button
        type="button"
        onclick="togglePassword()"
        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition"
      >
        <i id="eyeIcon" data-lucide="eye" class="w-5 h-5"></i>
      </button>
    </div>
  </div>

  <div class="flex items-center justify-between">
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
      <span class="text-sm text-[var(--secondary)]">Remember me</span>
    </label>
  </div>

  <button
    type="submit"
    class="w-full h-14 rounded-2xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white font-bold text-sm transition-all duration-300 shadow-lg shadow-blue-200"
  >
    Sign In
  </button>
</form>
 
      <!-- FOOTER -->
      <div class="mt-10 pt-6 border-t border-gray-100">

        <p class="text-sm text-center text-[var(--secondary)]">
          © 2026 AutoDeals. All rights reserved.
        </p>

      </div>

    </div>

  </div>

  <!-- RIGHT SIDE -->
  <div class="hidden lg:block relative overflow-hidden">

    <!-- IMAGE -->
    <img
      src="{{ asset('images/BacgrounLogin.jpg') }}"
      class="w-full h-full object-cover"
      alt="Showroom"
    />

    <!-- DARK OVERLAY -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/10 via-black/20 to-black/50"></div>

    <!-- FLOATING CARD -->
    <div class="absolute bottom-10 left-10 bg-white/90 backdrop-blur-xl border border-white/30 rounded-3xl p-6 shadow-2xl w-[320px]">

      <div class="flex items-center gap-4">

        <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
          <i data-lucide="car" class="w-7 h-7 text-blue-600"></i>
        </div>

        <div>

          <h3 class="text-3xl font-bold text-[var(--foreground)]">
            200+
          </h3>

          <p class="text-sm text-[var(--secondary)] font-medium">
            Cars in Stock
          </p>

        </div>

      </div>

      <div class="mt-5 pt-5 border-t border-gray-200">

        <div class="flex items-center gap-1 mb-2">

          <i data-lucide="star" class="w-4 h-4 fill-yellow-400 text-yellow-400"></i>
          <i data-lucide="star" class="w-4 h-4 fill-yellow-400 text-yellow-400"></i>
          <i data-lucide="star" class="w-4 h-4 fill-yellow-400 text-yellow-400"></i>
          <i data-lucide="star" class="w-4 h-4 fill-yellow-400 text-yellow-400"></i>
          <i data-lucide="star" class="w-4 h-4 fill-yellow-400 text-yellow-400"></i>

        </div>

        <p class="text-sm text-gray-600 leading-relaxed">
          Trusted dealership management platform for modern automotive businesses.
        </p>

      </div>

    </div>

  </div>

</div>

<script>

  lucide.createIcons();

  function togglePassword(){

    const password = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');

    if(password.type === 'password'){
      password.type = 'text';

      icon.setAttribute('data-lucide','eye-off');

    }else{
      password.type = 'password';

      icon.setAttribute('data-lucide','eye');
    }

    lucide.createIcons();
  }

</script>

</body>
</html>