<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Rekapitulasi Satwa Liar {{ $tahun }}</title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 6px 10px; font-family: Arial, sans-serif; font-size: 12px; }
    th { background-color: #00A9C1; color: #ffffff; font-weight: bold; }
    .header-row { background-color: #eaf6f8; font-weight: bold; }
  </style>
</head>
<body>
  <h2>REKAPITULASI LOGBOOK PENGAMATAN SATWA LIAR</h2>
  <h3>Tahun: {{ $tahun }} {{ $zona ? "(Zona Grid $zona)" : "" }}</h3>
  <p>Tanggal Export: {{ date('d F Y H:i') }}</p>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID Laporan</th>
        <th>Tanggal</th>
        <th>Nama Petugas</th>
        <th>Unit Kerja</th>
        <th>Area Inspeksi</th>
        <th>Grid Lokasi</th>
        <th>Kondisi Cuaca</th>
        <th>Kondisi Satwa</th>
        <th>Aktivitas</th>
        <th>Tindak Lanjut</th>
        <th>Detail Pengusiran</th>
        <th>Status</th>
        <th>Rincian Temuan Satwa</th>
      </tr>
    </thead>
    <tbody>
      @foreach($laporans as $i => $l)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>#{{ $l->id }}</td>
          <td>{{ $l->tanggal ? $l->tanggal->format('d/m/Y') : '-' }}</td>
          <td>{{ $l->nama_petugas }}</td>
          <td>{{ $l->unit_kerja }}</td>
          <td>{{ $l->area_inspeksi }}</td>
          <td>{{ $l->grid_lokasi }}</td>
          <td>{{ $l->kondisi_cuaca }}</td>
          <td>{{ $l->kondisi_apron }}</td>
          <td>{{ $l->aktivitas_satwa }}</td>
          <td>{{ $l->tindak_lanjut }}</td>
          <td>{{ $l->detail_pengusiran }}</td>
          <td>{{ $l->status === 'sudah' ? 'Telah Ditangani' : 'Belum Ditangani' }}</td>
          <td>
            @foreach($l->detailSatwa as $ds)
              {{ $ds->nama_satwa }} ({{ $ds->jumlah }} ekor - Grid: {{ $ds->grid ?: '-' }}); 
            @endforeach
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
