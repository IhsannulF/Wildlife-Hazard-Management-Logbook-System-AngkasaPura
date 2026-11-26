{{-- Penanda wajib/opsional di label isian form inspeksi (mengikuti pengaturan admin Form Inspeksi). --}}
@if($wajib ?? true)
  <span class="text-rose-500">*</span>
@else
  <span class="text-slate-400 font-normal">(opsional)</span>
@endif
