@if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), confirmButtonColor: '#165dff' });
    }
  });
</script>
@endif
@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonColor: '#165dff' });
    }
  });
</script>
@endif
