{{--
  Halaman ini memuat Tailwind dua kali: CDN v3 dan build Vite v4. Tailwind v4 mendaftarkan
  --tw-gradient-from/via/to sebagai tipe <color>, sedangkan kelas v3 mengisinya dengan
  "warna + posisi" (mis. "#00A9C1 var(--tw-gradient-from-position)"). Nilai itu ditolak browser
  sehingga gradien menjadi transparan dan tombol bertulisan putih (mis. "Kirim Laporan") tidak terlihat.
  Pendaftaran ulang dengan syntax "*" (aturan @property terakhir yang berlaku) menerima kedua format.
  Wajib diletakkan SETELAH @vite.
--}}
<style>
  @property --tw-gradient-from { syntax: "*"; inherits: false; }
  @property --tw-gradient-via { syntax: "*"; inherits: false; }
  @property --tw-gradient-to { syntax: "*"; inherits: false; }
</style>
