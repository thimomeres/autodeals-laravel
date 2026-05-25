document.addEventListener("DOMContentLoaded", () => {
  // ========================================================
  // 1. LARAVEL ACTIVE LINK SELECTOR & LUCIDE ICONS (SOP LARAVEL)
  // ========================================================
  const currentPath = window.location.pathname.toLowerCase(); 
  let activePage = "dashboard"; 

  if (
    currentPath.includes("infentory") || 
    currentPath.includes("inventory") || 
    currentPath.includes("addnew") || 
    currentPath.includes("vehicle")
  ) {
    activePage = "infentory"; 
  } else if (currentPath.includes("sales")) {
    activePage = "sales";
  } else if (currentPath.includes("/admin/users")) {
    activePage = "users";
  } else if (currentPath.includes("/admin/activity")) {
    activePage = "activity";
  } else if (currentPath.includes("/profile")) {
    activePage = "profile";
  } else if (currentPath.includes("dashboard")) {
    activePage = "dashboard";
  }

  const targetLink = document.querySelector(`.nav-link[data-page="${activePage}"]`);
  
  document.querySelectorAll('.nav-link').forEach(link => {
    link.classList.remove("bg-blue-50", "text-blue-600", "font-bold");
    link.classList.add("hover:bg-gray-100", "text-gray-600");
  });

  if (targetLink) {
    targetLink.classList.remove("hover:bg-gray-100", "text-gray-600");
    targetLink.classList.add("bg-blue-50", "text-blue-600", "font-bold");
  }

  if (typeof lucide !== "undefined") {
    lucide.createIcons();
  }

  // ========================================================
  // 2. LOADER HANDLER (VERSI AMAN & ANTI-MACET)
  // ========================================================
  const loader = document.getElementById("pageLoader");
  if (loader) {
    setTimeout(() => {
      loader.style.transition = "opacity 0.3s ease, visibility 0.3s ease";
      loader.style.opacity = "0";
      loader.style.pointerEvents = "none";
      setTimeout(() => { loader.remove(); }, 300);
    }, 200);
  }

  // ========================================================
  // 3. VEHICLE IMAGE UPLOAD TRIGGER
  // ========================================================
  const uploadBox = document.querySelector(".upload-box");
  const uploadInput = document.getElementById("carImageInput");

  if (uploadBox && uploadInput) {
    uploadBox.addEventListener("click", (e) => {
      if (e.target.closest('#previewContainer')) return;
      uploadInput.click();
    });
  }

  // ========================================================
  // 4. MULTIPLE IMAGES PREVIEW (SISTEM AKUMULASI MAKSIMAL 10 FOTO)
  // ========================================================
  const previewContainer = document.getElementById("previewContainer");
  let selectedFiles = [];

  if (uploadInput && previewContainer) {
    uploadInput.addEventListener("change", function () {
      const newFiles = this.files;

      Array.from(newFiles).forEach((file) => {
        if (!file.type.startsWith('image/')) return; 

        if (selectedFiles.length < 10) {
          selectedFiles.push(file);
        } else {
          alert("Maksimal foto yang dapat diunggah adalah 10 foto!");
        }
      });

      this.value = ""; 
      renderPreviews();
    });
  }

  function renderPreviews() {
    previewContainer.innerHTML = ""; 

    selectedFiles.forEach((file, index) => {
      const reader = new FileReader();

      reader.onload = function (e) {
        const imageBox = document.createElement("div");
        imageBox.className =
          "w-32 h-24 rounded-2xl overflow-hidden border border-gray-200 shrink-0 shadow-sm relative group";

        imageBox.innerHTML = `
          <img src="${e.target.result}" class="w-full h-full object-cover" alt="Preview" />
          <button type="button" class="absolute top-1 right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow" data-index="${index}">
            ×
          </button>
        `;

        imageBox.querySelector('button').addEventListener('click', (event) => {
          event.stopPropagation(); 
          const idx = parseInt(event.target.getAttribute('data-index'));
          selectedFiles.splice(idx, 1); 
          renderPreviews(); 
        });

        previewContainer.appendChild(imageBox);
      };

      reader.readAsDataURL(file);
    });
  }

  // ========================================================
  // 5. FORM SUBMIT HANDLER (DIPERBAIKI: DETEKSI METHOD EDIT/UPDATE)
  // ========================================================
  const vehicleForm = document.getElementById("vehicleForm");

  if (vehicleForm) {
    vehicleForm.addEventListener("submit", function (e) {
      // 1. Hentikan kiriman form standar sejenak untuk memproses manipulasi data gambar
      e.preventDefault(); 

      // Cek apakah di dalam form terdapat input "_method" bernilai "PUT" (Penanda Halaman Edit)
      const isEditMode = vehicleForm.querySelector('input[name="_method"]')?.value === 'PUT';

      // 2. Logika Pengecekan Validasi Upload Foto yang Cerdas
      if (!isEditMode && selectedFiles.length === 0) {
        // Jika BARU TAMBAH DATA dan tidak ada foto, tampilkan peringatan eror
        alert("Silakan unggah minimal 1 foto kendaraan!");
        return;
      }

      // 3. Jika admin memilih/mengganti berkas foto baru (baik di mode Tambah maupun Edit)
      if (selectedFiles.length > 0) {
        const dataTransfer = new DataTransfer();
        
        selectedFiles.forEach((file) => {
          dataTransfer.items.add(file);
        });

        const fileInput = document.getElementById("carImageInput");
        if (fileInput) {
          fileInput.files = dataTransfer.files;
        }
      }

      // 4. Setelah urusan sinkronisasi file selesai, teruskan submit secara resmi tanpa interupsi JavaScript
      this.submit();
    });
  }
});