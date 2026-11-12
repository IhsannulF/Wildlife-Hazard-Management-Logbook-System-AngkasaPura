@php
  $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
  $x0 = 41.0;
  $y0 = 121.0;
  $cw = 941.0 / 29.0; // 32.448275862068965 px per column
  $rh = 395.0 / 12.0; // 32.916666666666664 px per row

  // Pre-index grid data with both dashed (G-19) and compact (G19) keys
  $normGrid = [];
  $gridAggData = $gridAgg ?? [];
  foreach ($gridAggData as $k => $v) {
      $normGrid[$k] = $v;
      $clean = strtoupper(str_replace(['-', ' ', '='], '', $k));
      $normGrid[$clean] = $v;
      if (preg_match('/^([A-L])(\d+)$/', $clean, $m)) {
          $normGrid[$m[1] . '-' . (int)$m[2]] = $v;
          $normGrid[$m[1] . (int)$m[2]] = $v;
      }
  }

  $initialGrid = old('grid_lokasi', 'K-10');
  $initialLat  = old('latitude', '-7.383750');
  $initialLng  = old('longitude', '112.784828');
@endphp

<div class="w-full flex flex-col gap-3 select-none">

  {{-- 1. SELECTED LOCATION SUMMARY STRIP --}}
  <div class="w-full bg-slate-50/90 rounded-xl border border-slate-200/90 p-3.5 sm:p-4 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
    
    {{-- Coordinate Preview --}}
    <div class="flex items-center gap-3 min-w-0 flex-1">
      <div id="mainPreviewBadge"
           class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-[#00A9C1] to-[#007fa3] text-white font-mono font-black text-base sm:text-lg flex items-center justify-center shadow-md shadow-cyan-500/20 shrink-0 border border-teal-300/30">
        {{ $initialGrid }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
          <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-teal-100 text-teal-800 uppercase tracking-wider">Koordinat Terpilih</span>
          <span id="mainPreviewRisk" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Flocking Sedang</span>
        </div>
        <h4 id="mainPreviewZoneName" class="text-xs sm:text-sm font-extrabold text-slate-900 truncate">
          Kanal Drainase Perimeter Selatan Terminal 2
        </h4>
        <div id="mainPreviewGps" class="text-[11px] text-slate-500 font-mono mt-0.5 truncate flex items-center gap-1">
          <span class="material-symbols-outlined text-teal-600 text-xs">my_location</span>
          <span>Lintang: {{ $initialLat }} • Bujur: {{ $initialLng }}</span>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 shrink-0">
      <button type="button" onclick="applyGridToSatwa()"
              class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 hover:border-slate-300 text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs active:scale-[0.98]"
              title="Salin koordinat ke seluruh baris jenis satwa terpilih">
        <span class="material-symbols-outlined text-base text-teal-600">copy_all</span>
        <span>Salin ke Satwa</span>
      </button>

      <button type="button" onclick="toggleManualGridInput()"
              class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all cursor-pointer"
              title="Input kode grid manual">
        <span class="material-symbols-outlined text-base">edit_note</span>
      </button>
    </div>

  </div>

  {{-- Hidden inputs --}}
  <input type="hidden" name="grid_lokasi" id="inputGridLokasi" value="{{ $initialGrid }}" required>
  <input type="hidden" name="latitude"    id="inputLatitude"   value="{{ $initialLat }}">
  <input type="hidden" name="longitude"   id="inputLongitude"  value="{{ $initialLng }}">

  {{-- Manual Input Drawer (Hidden by default) --}}
  <div id="manualInputContainer" class="hidden p-3 bg-white rounded-xl border border-slate-200 flex items-center gap-3">
    <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Input Manual:</label>
    <input type="text" id="manualGridInput" value="{{ $initialGrid }}" placeholder="Contoh: K-10"
           class="w-32 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold uppercase text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500"
           oninput="onManualGridInput(this.value)">
    <span class="text-[11px] text-slate-400">Ketik kode sel grid (A1 – L29) lalu tekan enter/tab</span>
  </div>

  {{-- 2. MAP ACTION & CONTROL TOOLBAR (OUTSIDE MAP CANVAS - 100% BERSIH DARI APAPUN) --}}
  <div class="flex items-center justify-between gap-2 px-1">
    
    {{-- Left: Status & Hint --}}
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-600 min-w-0">
      <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse shrink-0"></span>
      <span class="truncate hidden xs:inline sm:inline">Sentuh sel grid pada peta untuk memilih lokasi</span>
      <span class="truncate inline xs:hidden sm:hidden">Sentuh sel grid pada peta</span>
    </div>

    {{-- Right: Tight Button Group for Zoom Controls --}}
    <div class="inline-flex items-center gap-1 shrink-0 bg-slate-100/90 p-1 rounded-xl border border-slate-200 shadow-2xs">
      
      {{-- Zoom In Button --}}
      <button type="button" onclick="zoomMap(0.15)"
              class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 hover:border-slate-300 flex items-center justify-center transition-all shadow-2xs active:scale-95 cursor-pointer"
              title="Perbesar Peta (Zoom In)">
        <span class="material-symbols-outlined text-base">add</span>
      </button>

      {{-- Zoom Out Button --}}
      <button type="button" onclick="zoomMap(-0.15)"
              class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 hover:border-slate-300 flex items-center justify-center transition-all shadow-2xs active:scale-95 cursor-pointer"
              title="Perkecil Peta (Zoom Out)">
        <span class="material-symbols-outlined text-base">remove</span>
      </button>

      {{-- Reset Scale Button --}}
      <button type="button" onclick="resetMap()"
              class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 hover:border-slate-300 flex items-center justify-center transition-all shadow-2xs active:scale-95 cursor-pointer"
              title="Reset Skala & Posisi (1:1)">
        <span class="material-symbols-outlined text-base">restart_alt</span>
      </button>

    </div>

  </div>

  {{-- 3. AIRSIDE INTERACTIVE SPATIAL CANVAS (100% BERSIH DARI APAPUN) --}}
  <div class="relative w-full overflow-hidden bg-slate-950 rounded-2xl border border-slate-700 select-none shadow-md">
    
    {{-- CANVAS CONTAINER --}}
    <div id="gisMapLayerWrapper" class="w-full overflow-hidden transition-transform duration-200 origin-top-left cursor-grab active:cursor-grabbing">
      <div id="gisCanvasInner" class="relative w-full overflow-hidden" style="aspect-ratio: 1024/723; width: 100%;">
        
        {{-- 1. BASE MAP ASLI (Gambar Peta Juanda Resmi 1024 x 723) --}}
        <img id="baseMapImg" src="{{ asset('images/gridmap_juanda.jpg') }}" alt="Gridmap Bandar Udara Internasional Juanda" class="w-full h-full object-fill block select-none pointer-events-none" onerror="this.src='{{ asset('images/gridmap_injourney.jpeg') }}'">
        
        {{-- 2. GIS OVERLAY MAPPING LAYER (SVG 1024 x 723 ViewBox) --}}
        <svg id="gisOverlaySvg" viewBox="0 0 1024 723" preserveAspectRatio="none" class="absolute inset-0 w-full h-full pointer-events-auto select-none">
          <defs>
            <filter id="glowRed" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="3" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <filter id="glowTeal" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="2.5" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <pattern id="isolatedParkingPattern" width="8" height="8" patternTransform="rotate(45 0 0)" patternUnits="userSpaceOnUse">
              <line x1="0" y1="0" x2="0" y2="8" stroke="rgba(239, 68, 68, 0.45)" stroke-width="1.5" />
            </pattern>
          </defs>

          {{-- OVERLAY GROUP (OPACITY CONTROLLED) --}}
          <g id="mainOverlayGroup" style="opacity: 0.75; transition: opacity 0.2s ease;">

            {{-- SUB-LAYER A: FASILITAS SISI UDARA --}}
            <g id="layerFacilities">
              {{-- Runway 10/28 Strip Buffer --}}
              <rect x="170.79" y="318.50" width="681.41" height="65.83" fill="rgba(245, 158, 11, 0.16)" stroke="rgba(245, 158, 11, 0.75)" stroke-width="1.3" stroke-dasharray="4,3" rx="2" />
              
              {{-- Runway Asphalt Surface --}}
              <rect x="170.79" y="344.00" width="681.41" height="14.80" fill="rgba(15, 23, 42, 0.35)" stroke="rgba(255, 255, 255, 0.7)" stroke-width="1" rx="1" />
              <line x1="185" y1="351.42" x2="838" y2="351.42" stroke="#FFFFFF" stroke-width="1.5" stroke-dasharray="8,6" />
              
              <text x="175" y="354" font-size="7" font-weight="900" fill="#FFFFFF" font-family="'DM Sans', monospace">◄ RWY 10</text>
              <text x="822" y="354" font-size="7" font-weight="900" fill="#FFFFFF" font-family="'DM Sans', monospace">RWY 28 ►</text>
              <text x="511.5" y="339" font-size="7.5" font-weight="800" fill="#F8FAFC" text-anchor="middle" font-family="'DM Sans', monospace" letter-spacing="0.8">RUNWAY ( 3000 M X 45 M )</text>

              {{-- Apron B --}}
              <rect x="560.17" y="252.67" width="194.69" height="32.92" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
              <text x="657.5" y="271.5" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON B</text>

              {{-- Apron C --}}
              <rect x="397.93" y="252.67" width="97.34" height="65.83" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
              <text x="446.6" y="288" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON C</text>

              {{-- Apron A --}}
              <rect x="268.14" y="417.25" width="194.69" height="32.92" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
              <text x="365.5" y="436" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON A</text>

              {{-- Terminal 1 --}}
              <rect x="592.62" y="219.75" width="162.24" height="32.92" fill="rgba(99, 102, 241, 0.18)" stroke="#6366F1" stroke-width="1" rx="2" />
              <text x="673.7" y="238.5" font-size="8" font-weight="800" fill="#4338CA" text-anchor="middle" font-family="'DM Sans', sans-serif">TERMINAL 1</text>

              {{-- Terminal 2 --}}
              <rect x="268.14" y="417.25" width="129.79" height="32.92" fill="rgba(99, 102, 241, 0.18)" stroke="#6366F1" stroke-width="1" rx="2" />
              <text x="333.0" y="443" font-size="7.5" font-weight="800" fill="#4338CA" text-anchor="middle" font-family="'DM Sans', sans-serif">TERMINAL 2</text>

              {{-- Regulating Pond 1 --}}
              <rect x="300.59" y="285.58" width="97.34" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
              <text x="349.2" y="304" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 1</text>

              {{-- Regulating Pond 2 --}}
              <rect x="527.72" y="186.83" width="97.34" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
              <text x="576.4" y="205" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 2</text>

              {{-- Regulating Pond 3 --}}
              <rect x="754.86" y="285.58" width="64.90" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
              <text x="787.3" y="304" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 3</text>

              {{-- Fasilitas PKP 1 --}}
              <g transform="translate(504, 262)">
                <rect width="15" height="15" rx="3" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
                <text x="7.5" y="10.5" font-size="6" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">PKP</text>
              </g>

              {{-- Fasilitas PKP 2 --}}
              <g transform="translate(471.5, 426)">
                <rect width="15" height="15" rx="3" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
                <text x="7.5" y="10.5" font-size="6" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">PKP</text>
              </g>

              {{-- EOC --}}
              <g transform="translate(479.1, 236.2)">
                <polygon points="0,-8 8,0 0,8 -8,0" fill="#EAB308" stroke="#FFFFFF" stroke-width="1" />
                <text x="0" y="2.5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">EOC</text>
              </g>

              {{-- Rendezvous Point 1 --}}
              <g transform="translate(381.7, 236.2)">
                <polygon points="0,-7 7,7 -7,7" fill="#FACC15" stroke="#FFFFFF" stroke-width="1" />
                <text x="0" y="5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">RP1</text>
              </g>

              {{-- Rendezvous Point 2 --}}
              <g transform="translate(738.7, 203.3)">
                <polygon points="0,-7 7,7 -7,7" fill="#FACC15" stroke="#FFFFFF" stroke-width="1" />
                <text x="0" y="5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">RP2</text>
              </g>

              {{-- Isolated Parking Area --}}
              <rect x="144" y="291" width="21" height="21" rx="2" fill="url(#isolatedParkingPattern)" stroke="#EF4444" stroke-width="1" />
              <text x="154.5" y="318" font-size="5.5" font-weight="800" fill="#EF4444" text-anchor="middle" font-family="'DM Sans', monospace">ISOLATED</text>
            </g>

            {{-- SUB-LAYER B: RUTE DISPERSAL AMC --}}
            <g id="layerDispersal">
              <line x1="170.79" y1="324" x2="852.2" y2="324" stroke="#10B981" stroke-width="1.2" stroke-dasharray="4,4" opacity="0.8" />
              <line x1="170.79" y1="378" x2="852.2" y2="378" stroke="#10B981" stroke-width="1.2" stroke-dasharray="4,4" opacity="0.8" />
              <circle cx="235.7" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
              <circle cx="511.5" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
              <circle cx="754.9" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
            </g>

            {{-- SUB-LAYER C: HOTSPOT BAHAYA SATWA LIAR --}}
            <g id="layerHotspots">
              @foreach($rows as $rIdx => $rChar)
                @for($cNum = 1; $cNum <= 29; $cNum++)
                  @php
                    $codeKey1 = $rChar . '-' . $cNum;
                    $codeKey2 = $rChar . $cNum;
                    $itemData = $normGrid[$codeKey1] ?? ($normGrid[$codeKey2] ?? null);
                    $cellLeft = round($x0 + ($cNum - 1) * $cw, 2);
                    $cellTop  = round($y0 + $rIdx * $rh, 2);
                    $cellW    = round($cw, 2);
                    $cellH    = round($rh, 2);
                  @endphp
                  @if($itemData)
                    @php
                      $cCount = $itemData['count'];
                      $isKritis = $cCount >= 30;
                      $isSedang = $cCount >= 15 && $cCount < 30;
                      $fillColor = $isKritis ? 'rgba(239, 68, 68, 0.55)' : ($isSedang ? 'rgba(245, 158, 11, 0.50)' : 'rgba(0, 163, 180, 0.45)');
                      $strokeColor = $isKritis ? '#EF4444' : ($isSedang ? '#F59E0B' : '#00A3B4');
                    @endphp
                    <rect x="{{ $cellLeft }}" y="{{ $cellTop }}" width="{{ $cellW }}" height="{{ $cellH }}" fill="{{ $fillColor }}" stroke="{{ $strokeColor }}" stroke-width="1.8" rx="1.5" />
                    <rect x="{{ round($cellLeft + ($cellW - 17)/2, 2) }}" y="{{ round($cellTop + ($cellH - 12)/2, 2) }}" width="17" height="12" rx="3" fill="#0F172A" fill-opacity="0.88" stroke="#FFFFFF" stroke-width="0.8" />
                    <text x="{{ round($cellLeft + $cellW/2, 2) }}" y="{{ round($cellTop + $cellH/2 + 3.2, 2) }}" font-size="7" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">{{ $cCount }}</text>
                  @endif
                @endfor
              @endforeach
            </g>

            {{-- SUB-LAYER D: LABEL KODING GRID KOORDINAT A1 - L29 --}}
            <g id="layerGridLabels">
              @foreach($rows as $rIdx => $rChar)
                @for($cNum = 1; $cNum <= 29; $cNum++)
                  @php
                    $cellLeft = round($x0 + ($cNum - 1) * $cw, 2);
                    $cellTop  = round($y0 + $rIdx * $rh, 2);
                  @endphp
                  <text x="{{ $cellLeft + 2.5 }}" y="{{ $cellTop + 7.5 }}" font-size="5.5" font-weight="700" fill="#E2E8F0" fill-opacity="0.75" font-family="'DM Sans', monospace" pointer-events="none">{{ $rChar . $cNum }}</text>
                @endfor
              @endforeach
            </g>

          </g>

          {{-- 4. INTERACTIVE HIT BOX LAYER (SEL A1 s/d L29 - HOVER & CLICK) --}}
          <g id="layerInteractiveHitBoxes">
            @foreach($rows as $rIdx => $rChar)
              @for($cNum = 1; $cNum <= 29; $cNum++)
                @php
                  $codeKey1 = $rChar . '-' . $cNum;
                  $codeKey2 = $rChar . $cNum;
                  $cellLeft = round($x0 + ($cNum - 1) * $cw, 2);
                  $cellTop  = round($y0 + $rIdx * $rh, 2);
                  $cellW    = round($cw, 2);
                  $cellH    = round($rh, 2);
                  $hasData  = isset($normGrid[$codeKey1]) || isset($normGrid[$codeKey2]);
                @endphp
                <rect id="hitCell_{{ $rChar }}_{{ $cNum }}"
                      x="{{ $cellLeft }}" y="{{ $cellTop }}" width="{{ $cellW }}" height="{{ $cellH }}"
                      fill="transparent" class="grid-hit-cell cursor-pointer transition-colors"
                      data-grid="{{ $codeKey1 }}" data-grid-compact="{{ $codeKey2 }}" data-row="{{ $rChar }}" data-col="{{ $cNum }}"
                      data-has-data="{{ $hasData ? '1' : '0' }}"
                      onmouseenter="onHoverGrid('{{ $codeKey1 }}', '{{ $rChar }}', {{ $cNum }})"
                      onmouseleave="onLeaveGrid()"
                      onclick="selectGrid('{{ $codeKey1 }}')" />
              @endfor
            @endforeach
          </g>

          {{-- 5. ACTIVE SELECTION RETICLE --}}
          <g id="activeSelectionGroup">
            <rect id="activeSelectionBox" x="0" y="0" width="{{ round($cw, 2) }}" height="{{ round($rh, 2) }}" rx="2"
                  fill="rgba(56, 189, 248, 0.25)" stroke="#38BDF8" stroke-width="2.5"
                  class="hidden transition-all duration-300 pointer-events-none" filter="url(#glowTeal)" />
          </g>

        </svg>
      </div>
    </div>

  </div>

</div>

{{-- RAW DATA STORE --}}
<script id="rawGridDataJson" type="application/json">@json($gridAggData)</script>

<script>
// =============================================================================
// CLIENT-SIDE SCRIPT FOR INTERACTIVE MAP & DETAILS
// =============================================================================
const JUANDA_BOUNDS = { minLat: -7.385000, maxLat: -7.375000, minLng: 112.775000, maxLng: 112.805000 };

let rawGridData = {};
try { rawGridData = JSON.parse(document.getElementById('rawGridDataJson').textContent) || {}; } catch(e) {}

const gridData = {};
const rowLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
const X0 = 41.0;
const Y0 = 121.0;
const CW = 941.0 / 29.0;
const RH = 395.0 / 12.0;

function rebuildGridData(newRaw) {
  rawGridData = newRaw;
  Object.keys(gridData).forEach(k => delete gridData[k]);
  Object.keys(rawGridData).forEach(k => {
    gridData[k] = rawGridData[k];
    const clean = k.replace(/[-_ =]/g, '').toUpperCase();
    gridData[clean] = rawGridData[k];
    const m = clean.match(/^([A-L])(\d+)$/);
    if (m) {
      gridData[`${m[1]}-${parseInt(m[2], 10)}`] = rawGridData[k];
      gridData[`${m[1]}${parseInt(m[2], 10)}`]  = rawGridData[k];
    }
  });
}
rebuildGridData(rawGridData);

function getGridCoordinates(rowChar, colNum) {
  const rIdx = rowLetters.indexOf(rowChar.toUpperCase());
  const cIdx = parseInt(colNum, 10) - 1;
  if (rIdx < 0 || cIdx < 0 || cIdx >= 29) return { lat: '-7.383750', lng: '112.784828' };
  const lat = (JUANDA_BOUNDS.maxLat - (rIdx + 0.5) / 12 * (JUANDA_BOUNDS.maxLat - JUANDA_BOUNDS.minLat)).toFixed(6);
  const lng = (JUANDA_BOUNDS.minLng + (cIdx + 0.5) / 29 * (JUANDA_BOUNDS.maxLng - JUANDA_BOUNDS.minLng)).toFixed(6);
  return { lat, lng };
}

// Zoom and pan state
let currentZoom = 1.0;
let panX = 0;
let panY = 0;
let isPanning = false;
let startX = 0;
let startY = 0;
let activeSelectedCode = '{{ $initialGrid }}';
let activeTargetSatwaInput = null;


function applyTransform() {
  const canvas = document.getElementById('gisCanvasInner');
  if (canvas) {
    canvas.style.transform = `scale(${currentZoom}) translate(${panX}px, ${panY}px)`;
  }
}

function zoomMap(delta) {
  currentZoom = Math.min(Math.max(0.75, currentZoom + delta), 2.2);
  applyTransform();
}

function resetMap() {
  currentZoom = 1.0;
  panX = 0;
  panY = 0;
  applyTransform();
}

// Mouse & Touch Pan Interaction
const mapWrapper = document.getElementById('gisMapLayerWrapper');
if (mapWrapper) {
  mapWrapper.addEventListener('mousedown', (e) => {
    if (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'button') return;
    isPanning = true;
    startX = e.clientX - panX;
    startY = e.clientY - panY;
  });

  window.addEventListener('mousemove', (e) => {
    if (!isPanning || currentZoom <= 1.0) return;
    panX = e.clientX - startX;
    panY = e.clientY - startY;
    applyTransform();
  });

  window.addEventListener('mouseup', () => {
    isPanning = false;
  });

  // Touch Support
  mapWrapper.addEventListener('touchstart', (e) => {
    if (e.touches.length === 1 && currentZoom > 1.0) {
      isPanning = true;
      startX = e.touches[0].clientX - panX;
      startY = e.touches[0].clientY - panY;
    }
  }, { passive: true });

  window.addEventListener('touchmove', (e) => {
    if (!isPanning || currentZoom <= 1.0 || e.touches.length !== 1) return;
    panX = e.touches[0].clientX - startX;
    panY = e.touches[0].clientY - startY;
    applyTransform();
  }, { passive: true });

  window.addEventListener('touchend', () => {
    isPanning = false;
  });
}

// Real-Time HUD Hover Handler
function onHoverGrid(code, row, col) {
  const clean = code.replace(/[-_ =]/g, '').toUpperCase();
  const compact = row + col;
  const data = gridData[code] || gridData[compact];

  const hudCoord = document.getElementById('gisHudCoord');
  const hudInfo  = document.getElementById('gisHudInfo');

  if (hudCoord) hudCoord.innerText = `KOORDINAT: ${compact} (${row}-${col})`;
  if (hudInfo) {
    if (data && data.count > 0) {
      hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;
    } else {
      hudInfo.innerText = `Area Steril Sisi Udara • Sumbu Horisontal ${col}, Sumbu Vertikal ${row}`;
    }
  }

  // Highlight hit cell
  const hitEl = document.getElementById(`hitCell_${row}_${col}`);
  if (hitEl) {
    hitEl.setAttribute('stroke', '#00A3B4');
    hitEl.setAttribute('stroke-width', '1.5');
    hitEl.setAttribute('fill', 'rgba(0, 163, 180, 0.15)');
  }
}

function onLeaveGrid() {
  document.querySelectorAll('.grid-hit-cell').forEach(el => {
    el.setAttribute('stroke', 'transparent');
    el.setAttribute('fill', 'transparent');
  });

  selectGrid(activeSelectedCode, false);
}

// Dynamic Grid Selection on Click
function selectGrid(code, updateActive = true) {
  if (updateActive) {
    activeSelectedCode = code;
  }

  const clean = code.replace(/[-_ =]/g, '').toUpperCase();
  const m = clean.match(/^([A-L])(\d+)$/);
  let rowChar = 'K';
  let colNum  = 10;
  let compactCode = code;
  let standardCode = code;

  if (m) {
    rowChar = m[1];
    colNum  = parseInt(m[2], 10);
    compactCode = rowChar + colNum;
    standardCode = rowChar + '-' + colNum;
  }

  const data = gridData[standardCode] || gridData[compactCode] || {
    grid: standardCode,
    zone_name: 'Zona ' + compactCode + ' (Area Sisi Udara)',
    count: 0,
    dominant_species: 'Tidak Ada'
  };
  const coords = getGridCoordinates(rowChar, colNum);

  // Position Active Selection Box Reticle in SVG
  const rIdx = rowLetters.indexOf(rowChar);
  const cIdx = colNum - 1;
  if (rIdx >= 0 && cIdx >= 0 && cIdx < 29) {
    const boxX = (X0 + cIdx * CW).toFixed(2);
    const boxY = (Y0 + rIdx * RH).toFixed(2);
    const boxW = CW.toFixed(2);
    const boxH = RH.toFixed(2);

    const selBox = document.getElementById('activeSelectionBox');
    if (selBox) {
      selBox.setAttribute('x', boxX);
      selBox.setAttribute('y', boxY);
      selBox.setAttribute('width', boxW);
      selBox.setAttribute('height', boxH);
      selBox.classList.remove('hidden');
    }
  }

  // Update HUD
  const hudCoord = document.getElementById('gisHudCoord');
  const hudInfo  = document.getElementById('gisHudInfo');
  if (hudCoord) hudCoord.innerText = `TERPILIH: ${compactCode} (${standardCode})`;
  if (hudInfo) hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;

  // Update Form inputs & Main Preview Bar
  if (updateActive) {
    const ig  = document.getElementById('inputGridLokasi');
    const il  = document.getElementById('inputLatitude');
    const iln = document.getElementById('inputLongitude');
    const mi  = document.getElementById('manualGridInput');
    if (ig)  ig.value  = standardCode;
    if (il)  il.value  = coords.lat;
    if (iln) iln.value = coords.lng;
    if (mi)  mi.value  = standardCode;

    const mb = document.getElementById('mainPreviewBadge');
    const mz = document.getElementById('mainPreviewZoneName');
    const mg = document.getElementById('mainPreviewGps');
    const mr = document.getElementById('mainPreviewRisk');
    if (mb) mb.innerText = standardCode;
    if (mz) mz.innerText = data.zone_name || ('Zona ' + standardCode);
    if (mg) mg.innerHTML = `<span class="material-symbols-outlined text-teal-600 text-xs">my_location</span><span>Lintang: ${coords.lat} • Bujur: ${coords.lng}</span>`;
    if (mr) {
      const c = data.count || 0;
      if      (c >= 30) { mr.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800';   mr.innerText = 'Kritis (>30)'; }
      else if (c >= 15) { mr.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800'; mr.innerText = 'Sedang (15–30)'; }
      else if (c >  0)  { mr.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-sky-100 text-sky-800';     mr.innerText = 'Rendah (<15)'; }
      else              { mr.className = 'text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700'; mr.innerText = 'Steril'; }
    }

    if (activeTargetSatwaInput) {
      activeTargetSatwaInput.value = standardCode;
      activeTargetSatwaInput.dispatchEvent(new Event('input', { bubbles: true }));
      activeTargetSatwaInput = null;
    }

    if (typeof onMapGridSelected === 'function') {
      onMapGridSelected(standardCode, data.zone_name);
    }
  }
}

// Utilities
function toggleManualGridInput() {
  const c = document.getElementById('manualInputContainer');
  if (c) {
    c.classList.toggle('hidden');
    if (!c.classList.contains('hidden')) document.getElementById('manualGridInput').focus();
  }
}

function onManualGridInput(val) {
  if (!val) return;
  const clean = val.replace(/[-_ =]/g, '').toUpperCase();
  const m = clean.match(/^([A-L])(\d{1,2})$/);
  if (m) {
    const row = m[1], col = parseInt(m[2], 10);
    if (col >= 1 && col <= 29) {
      const code = `${row}-${col}`;
      selectGrid(code, true);
    }
  }
}

function applyGridToSatwa() {
  const code = activeSelectedCode || document.getElementById('inputGridLokasi').value || 'K-10';
  let n = 0;
  const selBurung = document.getElementById('selectGridBurung');
  if (selBurung) {
    selBurung.value = code;
    selBurung.dispatchEvent(new Event('change', { bubbles: true }));
    n++;
  }
  document.querySelectorAll('#mainForm input[name*="[grid]"]').forEach(inp => {
    inp.value = code;
    inp.dispatchEvent(new Event('input', { bubbles: true }));
    n++;
  });
  if (n > 0) alert(`✓ Koordinat ${code} berhasil diterapkan ke Bagian 2.`);
  else       alert(`Koordinat ${code} aktif.`);
}

document.addEventListener('focusin', (e) => {
  if (e.target && e.target.name && e.target.name.includes('[grid]') && e.target.id !== 'inputGridLokasi' && e.target.id !== 'manualGridInput') {
    activeTargetSatwaInput = e.target;
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const g = '{{ $initialGrid }}';
  selectGrid(g, true);
});
</script>
