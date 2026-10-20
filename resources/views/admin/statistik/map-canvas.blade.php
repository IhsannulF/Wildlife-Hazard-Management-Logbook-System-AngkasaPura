<!-- 2. PANORAMIC AIRSIDE RUNWAY GRID MAP (FULL WIDTH - EDGE TO EDGE) -->
<div class="w-full bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col relative">
  
  <!-- Airside Map Top Controls & Heading Strip -->
  <div class="flex flex-wrap items-center justify-between gap-2.5 pb-2.5 border-b border-border-subtle mb-3.5">
    <div class="flex items-center gap-2.5">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-injourney-teal text-lg">grid_4x4</span>
        <span class="text-sm sm:text-base font-bold text-slate-navy">Denah Sektoral Grid Sisi Udara — Bandara Juanda</span>
      </div>
      <div class="hidden sm:flex items-center gap-1 text-[10px] font-code-coordinate bg-surface-container-low px-2 py-0.5 rounded border border-border-subtle text-secondary font-semibold">
        <span>RUNWAY 10/28 (3.000m × 45m) • 29 KOLOM × 12 BARIS</span>
      </div>
    </div>
    
    @php
      $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
      $x0 = 41.0;
      $y0 = 121.0;
      $cw = 941.0 / 29.0; // 32.448275862068965 px per column
      $rh = 395.0 / 12.0; // 32.916666666666664 px per row

      // Pre-index grid data with both dashed (G-19) and compact (G19) keys
      $normGrid = [];
      foreach ($gridAgg as $k => $v) {
          $normGrid[$k] = $v;
          $clean = strtoupper(str_replace(['-', ' ', '='], '', $k));
          $normGrid[$clean] = $v;
          if (preg_match('/^([A-L])(\d+)$/', $clean, $m)) {
              $normGrid[$m[1] . '-' . (int)$m[2]] = $v;
              $normGrid[$m[1] . (int)$m[2]] = $v;
          }
      }
    @endphp

    <!-- Operational GIS View Mode & Layer Controls -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 bg-surface-container-low p-1.5 rounded-lg border border-border-subtle w-full">
      <!-- View Mode Switcher -->
      <div class="flex items-center gap-1">
        <button type="button" id="btnModeGis" onclick="setMapMode('gis')" class="text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer" title="Base map Juanda dengan overlay presisi sistem grid A-L & 1-29">
          <span class="material-symbols-outlined text-xs">layers</span>
          <span>Overlay GIS Juanda</span>
        </button>
        <button type="button" id="btnModeTactical" onclick="setMapMode('tactical')" class="text-[11px] px-2.5 py-1 rounded-md bg-surface-container-low text-secondary hover:text-slate-navy hover:bg-surface-container font-medium flex items-center gap-1 transition-all cursor-pointer" title="Tabel matriks data 29 kolom">
          <span class="material-symbols-outlined text-xs">grid_on</span>
          <span>Matriks 29 Kolom</span>
        </button>
      </div>

      <!-- Granular Layer Toggles (Overlay GIS) with Google Material Symbols -->
      <div id="gisLayerToggles" class="flex flex-wrap items-center gap-2 text-[11px]">
        <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
          <input type="checkbox" id="toggleLayerHotspot" checked onchange="toggleGisLayer('layerHotspots', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
          <span class="material-symbols-outlined text-[15px] text-warning-rose leading-none">pets</span>
          <span>Hotspot Satwa</span>
        </label>
        <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
          <input type="checkbox" id="toggleLayerFacilities" checked onchange="toggleGisLayer('layerFacilities', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
          <span class="material-symbols-outlined text-[15px] text-sky-600 leading-none">flight</span>
          <span>Fasilitas Bandara</span>
        </label>
        <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
          <input type="checkbox" id="toggleLayerGridLabels" checked onchange="toggleGisLayer('layerGridLabels', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
          <span class="material-symbols-outlined text-[15px] text-slate-600 leading-none">grid_4x4</span>
          <span>Label Grid (A1–L29)</span>
        </label>
        <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
          <input type="checkbox" id="toggleLayerDispersal" checked onchange="toggleGisLayer('layerDispersal', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
          <span class="material-symbols-outlined text-[15px] text-amber-500 leading-none">alt_route</span>
          <span>Rute Dispersal</span>
        </label>

        <!-- Opacity Slider -->
        <div class="inline-flex items-center gap-1.5 pl-2 border-l border-border-subtle">
          <span class="material-symbols-outlined text-[15px] text-secondary leading-none">opacity</span>
          <span class="text-secondary text-[10px] font-bold">Transparansi:</span>
          <input type="range" id="overlayOpacityRange" min="20" max="100" value="75" oninput="setOverlayOpacity(this.value)" class="w-16 h-1.5 bg-slate-300 rounded-lg appearance-none cursor-pointer accent-injourney-teal" title="Atur transparansi layer overlay">
          <span id="opacityValText" class="font-code-coordinate text-[10px] font-bold text-slate-700 w-6">75%</span>
        </div>
      </div>
    </div>
  </div>

  <!-- AIRSIDE INTERACTIVE SPATIAL CANVAS (FULL WIDTH, ZERO DEAD SPACE ON EDGES) -->
  <div class="relative w-full overflow-hidden bg-slate-950 rounded-xl border border-slate-700 select-none shadow-md">
    
    <!-- Floating Map Tools (Overlay Controls) -->
    <div class="absolute top-3 right-3 z-30 flex flex-col gap-1.5 bg-slate-navy/90 backdrop-blur-md p-1.5 rounded-lg border border-runway-grid shadow-lg">
      <button type="button" onclick="zoomMap(0.1)" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer" title="Perbesar (Zoom In)">
        <span class="material-symbols-outlined text-base">add</span>
      </button>
      <button type="button" onclick="zoomMap(-0.1)" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer" title="Perkecil (Zoom Out)">
        <span class="material-symbols-outlined text-base">remove</span>
      </button>
      <button type="button" onclick="resetMap()" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer" title="Reset Skala & Posisi (1:1)">
        <span class="material-symbols-outlined text-base">restart_alt</span>
      </button>
      <div class="w-full h-px bg-runway-grid my-0.5"></div>
      <!-- Wind Compass Heading Indicator -->
      <div class="w-8 h-8 rounded flex items-center justify-center text-injourney-teal" title="Orientasi Runway 10/28 WNW 276°">
        <span class="material-symbols-outlined text-base transform rotate-[86deg]">navigation</span>
      </div>
    </div>

    <!-- Real-Time Cursor HUD Strip (Top-Left) -->
    <div id="gisCursorHud" class="absolute top-3 left-3 z-30 flex items-center gap-2 bg-slate-navy/90 backdrop-blur-md px-3 py-1.5 rounded-lg border border-runway-grid text-xs text-slate-200 pointer-events-none">
      <span class="w-2 h-2 rounded-full bg-injourney-teal animate-pulse"></span>
      <span id="gisHudCoord" class="font-code-coordinate font-bold text-cyan-300">KOORDINAT: K-10 (K10)</span>
      <span class="text-slate-500">•</span>
      <span id="gisHudInfo" class="text-slate-300 truncate max-w-xs sm:max-w-md">Bahu Runway 25R &amp; Kanal Selatan (38 Kejadian)</span>
    </div>

    <!-- ================= LAYER 1: GIS OPERATIONAL MAP CONTAINER (BASE MAP + EXACT SVG OVERLAY) ================= -->
    <div id="gisMapLayerWrapper" class="w-full overflow-hidden transition-transform duration-200 origin-top-left cursor-grab active:cursor-grabbing">
      <div id="gisCanvasInner" class="relative w-full overflow-hidden" style="aspect-ratio: 1024/723; width: 100%;">
        
        <!-- 1. BASE MAP ASLI (Gambar Peta Juanda Resmi Tanpa Crop / Stretch / Rotasi - Flush Full Edge) -->
        <img id="baseMapImg" src="{{ asset('images/gridmap_juanda.jpg') }}" alt="Gridmap Bandar Udara Internasional Juanda" class="w-full h-full object-fill block select-none pointer-events-none" onerror="this.src='{{ asset('images/gridmap_injourney.jpeg') }}'">
        
        <!-- 2. GIS OVERLAY MAPPING LAYER (SVG 1024 x 723 ViewBox - Presisi 1:1 Terhadap Pixel Grid Asli) -->
        <svg id="gisOverlaySvg" viewBox="0 0 1024 723" preserveAspectRatio="none" class="absolute inset-0 w-full h-full pointer-events-auto select-none">
            <defs>
              <!-- Glow filter for Critical Hotspots -->
              <filter id="glowRed" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="3" result="blur" />
                <feComposite in="SourceGraphic" in2="blur" operator="over" />
              </filter>
              <filter id="glowTeal" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="2.5" result="blur" />
                <feComposite in="SourceGraphic" in2="blur" operator="over" />
              </filter>
              <!-- Pattern for Isolated Parking -->
              <pattern id="isolatedParkingPattern" width="8" height="8" patternTransform="rotate(45 0 0)" patternUnits="userSpaceOnUse">
                <line x1="0" y1="0" x2="0" y2="8" stroke="rgba(239, 68, 68, 0.45)" stroke-width="1.5" />
              </pattern>
            </defs>

            <!-- ================= OVERLAY MAPPING GROUP (CONTROLLED BY OPACITY SLIDER) ================= -->
            <g id="mainOverlayGroup" style="opacity: 0.75; transition: opacity 0.2s ease;">

              <!-- SUB-LAYER A: FASILITAS SISI UDARA (RUNWAY, APRON, REGULATING POND, TERMINAL) -->
              <g id="layerFacilities">
                <!-- Runway 10/28 Strip Buffer (G5-G25 & H5-H25: x: 170.8 s/d 852.2, y: 318.5 s/d 384.3) -->
                <rect x="170.79" y="318.50" width="681.41" height="65.83" fill="rgba(245, 158, 11, 0.16)" stroke="rgba(245, 158, 11, 0.75)" stroke-width="1.3" stroke-dasharray="4,3" rx="2" />
                
                <!-- Runway 10/28 Asphalt Surface (3000m x 45m: x: 170.8 s/d 852.2, y: 344 s/d 359) -->
                <rect x="170.79" y="344.00" width="681.41" height="14.80" fill="rgba(15, 23, 42, 0.35)" stroke="rgba(255, 255, 255, 0.7)" stroke-width="1" rx="1" />
                <line x1="185" y1="351.42" x2="838" y2="351.42" stroke="#FFFFFF" stroke-width="1.5" stroke-dasharray="8,6" />
                
                <!-- Runway Threshold Headings & Label -->
                <text x="175" y="354" font-size="7" font-weight="900" fill="#FFFFFF" font-family="'DM Sans', monospace">◄ RWY 10</text>
                <text x="822" y="354" font-size="7" font-weight="900" fill="#FFFFFF" font-family="'DM Sans', monospace">RWY 28 ►</text>
                <text x="511.5" y="339" font-size="7.5" font-weight="800" fill="#F8FAFC" text-anchor="middle" font-family="'DM Sans', monospace" letter-spacing="0.8">RUNWAY ( 3000 M X 45 M )</text>

                <!-- Apron B (Row E, Col 17..22: x: 560.2 s/d 754.9, y: 252.7 s/d 285.6) -->
                <rect x="560.17" y="252.67" width="194.69" height="32.92" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
                <text x="657.5" y="271.5" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON B</text>

                <!-- Apron C (Row E..F, Col 12..14: x: 397.9 s/d 495.3, y: 252.7 s/d 318.5) -->
                <rect x="397.93" y="252.67" width="97.34" height="65.83" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
                <text x="446.6" y="288" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON C</text>

                <!-- Apron A / Terminal 2 (Row J, Col 8..13: x: 268.1 s/d 462.8, y: 417.2 s/d 450.2) -->
                <rect x="268.14" y="417.25" width="194.69" height="32.92" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
                <text x="365.5" y="436" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON A</text>

                <!-- Terminal 1 (Row D, Col 18..22: x: 592.6 s/d 754.9, y: 219.8 s/d 252.7) -->
                <rect x="592.62" y="219.75" width="162.24" height="32.92" fill="rgba(99, 102, 241, 0.18)" stroke="#6366F1" stroke-width="1" rx="2" />
                <text x="673.7" y="238.5" font-size="8" font-weight="800" fill="#4338CA" text-anchor="middle" font-family="'DM Sans', sans-serif">TERMINAL 1</text>

                <!-- Terminal 2 (Row J, Col 8..11: x: 268.1 s/d 397.9, y: 417.2 s/d 450.2) -->
                <rect x="268.14" y="417.25" width="129.79" height="32.92" fill="rgba(99, 102, 241, 0.18)" stroke="#6366F1" stroke-width="1" rx="2" />
                <text x="333.0" y="443" font-size="7.5" font-weight="800" fill="#4338CA" text-anchor="middle" font-family="'DM Sans', sans-serif">TERMINAL 2</text>

                <!-- Regulating Pond 1 (Water Body, Habitat Burung Air: Row F, Col 9..11: x: 300.6 s/d 397.9, y: 285.6 s/d 318.5) -->
                <rect x="300.59" y="285.58" width="97.34" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
                <text x="349.2" y="304" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 1</text>

                <!-- Regulating Pond 2 (Row C, Col 16..18: x: 527.7 s/d 625.1, y: 186.8 s/d 219.8) -->
                <rect x="527.72" y="186.83" width="97.34" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
                <text x="576.4" y="205" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 2</text>

                <!-- Regulating Pond 3 (Row F, Col 23..24: x: 754.9 s/d 819.8, y: 285.6 s/d 318.5) -->
                <rect x="754.86" y="285.58" width="64.90" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
                <text x="787.3" y="304" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 3</text>

                <!-- Fasilitas PKP-PK / ARFF 1 (Row E, Col 15: x: 511.5, y: 269.1) -->
                <g transform="translate(504, 262)">
                  <rect width="15" height="15" rx="3" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
                  <text x="7.5" y="10.5" font-size="6" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">PKP</text>
                </g>

                <!-- Fasilitas PKP-PK / ARFF 2 (Row J, Col 14: x: 479.1, y: 433.7) -->
                <g transform="translate(471.5, 426)">
                  <rect width="15" height="15" rx="3" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
                  <text x="7.5" y="10.5" font-size="6" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">PKP</text>
                </g>

                <!-- Emergency Operation Center (EOC, Row D, Col 15: x: 479.1, y: 236.2) -->
                <g transform="translate(479.1, 236.2)">
                  <polygon points="0,-8 8,0 0,8 -8,0" fill="#EAB308" stroke="#FFFFFF" stroke-width="1" />
                  <text x="0" y="2.5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">EOC</text>
                </g>

                <!-- Rendezvous Point 1 (Row D, Col 11: x: 381.7, y: 236.2) -->
                <g transform="translate(381.7, 236.2)">
                  <polygon points="0,-7 7,7 -7,7" fill="#FACC15" stroke="#FFFFFF" stroke-width="1" />
                  <text x="0" y="5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">RP1</text>
                </g>

                <!-- Rendezvous Point 2 (Row C, Col 22: x: 738.7, y: 203.3) -->
                <g transform="translate(738.7, 203.3)">
                  <polygon points="0,-7 7,7 -7,7" fill="#FACC15" stroke="#FFFFFF" stroke-width="1" />
                  <text x="0" y="5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">RP2</text>
                </g>

                <!-- Isolated Parking Area (Row F, Col 4: x: 154.6, y: 302.1) -->
                <rect x="144" y="291" width="21" height="21" rx="2" fill="url(#isolatedParkingPattern)" stroke="#EF4444" stroke-width="1" />
                <text x="154.5" y="318" font-size="5.5" font-weight="800" fill="#EF4444" text-anchor="middle" font-family="'DM Sans', monospace">ISOLATED</text>
              </g>

              <!-- SUB-LAYER B: RUTE PATROLI & MITIGASI DISPERSAL AMC -->
              <g id="layerDispersal">
                <line x1="170.79" y1="324" x2="852.2" y2="324" stroke="#10B981" stroke-width="1.2" stroke-dasharray="4,4" opacity="0.8" />
                <line x1="170.79" y1="378" x2="852.2" y2="378" stroke="#10B981" stroke-width="1.2" stroke-dasharray="4,4" opacity="0.8" />
                <circle cx="235.7" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
                <circle cx="511.5" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
                <circle cx="754.9" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
              </g>

              <!-- SUB-LAYER C: HOTSPOT BAHAYA SATWA LIAR (MAPPING PRESI PADA SETIAP SEL GRID) -->
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
                      <!-- Hotspot Cell {{ $codeKey1 }} -->
                      <rect x="{{ $cellLeft }}" y="{{ $cellTop }}" width="{{ $cellW }}" height="{{ $cellH }}" fill="{{ $fillColor }}" stroke="{{ $strokeColor }}" stroke-width="1.8" rx="1.5" />
                      <!-- Incident Count Badge in Cell Center -->
                      <rect x="{{ round($cellLeft + ($cellW - 17)/2, 2) }}" y="{{ round($cellTop + ($cellH - 12)/2, 2) }}" width="17" height="12" rx="3" fill="#0F172A" fill-opacity="0.88" stroke="#FFFFFF" stroke-width="0.8" />
                      <text x="{{ round($cellLeft + $cellW/2, 2) }}" y="{{ round($cellTop + $cellH/2 + 3.2, 2) }}" font-size="7" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">{{ $cCount }}</text>
                    @endif
                  @endfor
                @endforeach
              </g>

              <!-- SUB-LAYER D: LABEL KODING GRID KOORDINAT A1 - L29 -->
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

            <!-- ================= INTERACTIVE HIT BOX LAYER (SEL A1 s/d L29 - HOVER & CLICK) ================= -->
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
                  <rect id="hitCell_{{ $rChar }}_{{ $cNum }}" x="{{ $cellLeft }}" y="{{ $cellTop }}" width="{{ $cellW }}" height="{{ $cellH }}" fill="transparent" class="grid-hit-cell cursor-pointer transition-colors" data-grid="{{ $codeKey1 }}" data-grid-compact="{{ $codeKey2 }}" data-row="{{ $rChar }}" data-col="{{ $cNum }}" data-has-data="{{ $hasData ? '1' : '0' }}" onmouseenter="onHoverGrid('{{ $codeKey1 }}', '{{ $rChar }}', {{ $cNum }})" onmouseleave="onLeaveGrid()" onclick="selectGrid('{{ $codeKey1 }}')" />
                @endfor
              @endforeach
            </g>

            <!-- ================= ACTIVE SELECTION RETICLE (ANIMATED HIGHLIGHT) ================= -->
            <g id="activeSelectionGroup">
              <rect id="activeSelectionBox" x="0" y="0" width="32.45" height="32.92" rx="2" fill="none" stroke="#38BDF8" stroke-width="2.5" class="hidden transition-all duration-300 pointer-events-none" filter="url(#glowTeal)" />
            </g>

          </svg>
        </div>
      </div>

      <!-- ================= LAYER 2: TACTICAL 29-COLUMN MATRIX LAYER (FALLBACK ALTERNATIF) ================= -->
      <div id="mapTacticalLayer" class="hidden min-w-[960px] w-full p-4 overflow-x-auto transition-transform duration-200 origin-top-left">
        
        <!-- Column Numbers Axis (01 - 29) -->
        <div class="juanda-grid-row mb-1 text-center font-code-coordinate text-[11px] text-slate-400 font-bold">
          <div class="py-1 text-slate-500 text-[10px]">GRID</div>
          @for($c = 1; $c <= 29; $c++)
            <div class="py-1">{{ str_pad($c, 2, '0', STR_PAD_LEFT) }}</div>
          @endfor
        </div>

        @foreach($rows as $r)
          @if($r === 'G')
            <!-- ROW G & H (PRIMARY RUNWAY 10 / 28) -->
            <div class="relative py-1.5 my-1 bg-slate-950 rounded-lg border-2 border-slate-600 shadow-2xl">
              <div class="juanda-grid-row mb-1">
                <div class="text-center font-code-coordinate font-bold text-amber-400 text-xs">G</div>
                @for($col = 1; $col <= 29; $col++)
                  @php
                    $code = 'G-' . $col;
                    $hasData = isset($gridAgg[$code]) || isset($gridAgg['G' . $col]);
                    $item = $hasData ? ($gridAgg[$code] ?? $gridAgg['G' . $col]) : null;
                    $isRunway = ($col >= 5 && $col <= 26);
                  @endphp
                  @if($hasData)
                    <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex flex-col items-center justify-center p-0.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                      <span class="text-[10px] font-code-coordinate {{ $item['risk_text'] }} font-bold leading-none">{{ $code }}</span>
                      <span class="text-[9px] font-bold bg-white text-slate-navy px-1 rounded mt-0.5 leading-none">{{ $item['count'] }}</span>
                    </div>
                  @elseif($isRunway)
                    <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-700/80 hover:border-injourney-teal bg-slate-900/90 flex flex-col items-center justify-center cursor-pointer text-slate-400 hover:text-white text-[10px] font-code-coordinate transition-colors" title="Grid {{ $code }} (Runway 10/28 Steril)">
                      <span class="text-[9px] opacity-70">{{ $col }}</span>
                      <div class="w-3 h-0.5 bg-white/30 mt-0.5"></div>
                    </div>
                  @else
                    <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-800 bg-slate-950/60 flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                      {{ $col }}
                    </div>
                  @endif
                @endfor
              </div>

              <div class="h-1 my-0.5 flex items-center justify-between px-8 pointer-events-none opacity-60">
                <span class="text-[9px] font-code-coordinate font-bold text-white tracking-widest pl-4">◄ RWY 10</span>
                <div class="flex-1 border-t-2 border-dashed border-white mx-4"></div>
                <span class="text-[9px] font-code-coordinate font-bold text-white tracking-widest pr-4">RWY 28 ►</span>
              </div>

              <div class="juanda-grid-row mt-1">
                <div class="text-center font-code-coordinate font-bold text-amber-400 text-xs">H</div>
                @for($col = 1; $col <= 29; $col++)
                  @php
                    $code = 'H-' . $col;
                    $hasData = isset($gridAgg[$code]) || isset($gridAgg['H' . $col]);
                    $item = $hasData ? ($gridAgg[$code] ?? $gridAgg['H' . $col]) : null;
                    $isRunway = ($col >= 5 && $col <= 26);
                  @endphp
                  @if($hasData)
                    <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex flex-col items-center justify-center p-0.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                      <span class="text-[10px] font-code-coordinate {{ $item['risk_text'] }} font-bold leading-none">{{ $code }}</span>
                      <span class="text-[9px] font-bold bg-white text-slate-navy px-1 rounded mt-0.5 leading-none">{{ $item['count'] }}</span>
                    </div>
                  @elseif($isRunway)
                    <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-700/80 hover:border-injourney-teal bg-slate-900/90 flex flex-col items-center justify-center cursor-pointer text-slate-400 hover:text-white text-[10px] font-code-coordinate transition-colors" title="Grid {{ $code }} (Runway 10/28 Steril)">
                      <span class="text-[9px] opacity-70">{{ $col }}</span>
                      <div class="w-3 h-0.5 bg-white/30 mt-0.5"></div>
                    </div>
                  @else
                    <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-800 bg-slate-950/60 flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                      {{ $col }}
                    </div>
                  @endif
                @endfor
              </div>
            </div>
          @elseif($r === 'H')
            {{-- Row H is rendered inside Runway above --}}
          @else
            <div class="juanda-grid-row mb-1">
              <div class="text-center font-code-coordinate font-bold {{ in_array($r, ['E', 'D']) ? 'text-emerald-400' : (in_array($r, ['J', 'K']) ? 'text-sky-400' : 'text-slate-400') }} text-xs">{{ $r }}</div>
              @for($col = 1; $col <= 29; $col++)
                @php
                  $code = $r . '-' . $col;
                  $hasData = isset($gridAgg[$code]) || isset($gridAgg[$r . $col]);
                  $item = $hasData ? ($gridAgg[$code] ?? $gridAgg[$r . $col]) : null;
                @endphp
                @if($hasData)
                  <div onclick="selectGrid('{{ $code }}')" class="h-9 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex flex-col items-center justify-center p-0.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                    <span class="text-[10px] font-code-coordinate {{ $item['risk_text'] }} font-semibold leading-none">{{ $code }}</span>
                    <span class="text-[9px] font-bold bg-white/20 text-white px-1 rounded mt-0.5 leading-none">{{ $item['count'] }}</span>
                  </div>
                @else
                  <div onclick="selectGrid('{{ $code }}')" class="h-9 rounded border border-slate-700/60 bg-slate-800/40 hover:border-injourney-teal transition-all flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                    {{ $col }}
                  </div>
                @endif
              @endfor
            </div>
          @endif
        @endforeach

      </div>

    </div>

    <!-- BOTTOM MAP FOOTER: RISK LEVEL LEGEND -->
    <div class="mt-3.5 pt-3 border-t border-border-subtle flex flex-wrap items-center justify-between gap-3 text-xs">
      <div id="mapRiskLegend" class="flex items-center gap-4 flex-wrap">
        <span class="text-secondary font-bold uppercase tracking-wider text-[11px]">Legenda Tingkat Risiko ICAO:</span>
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded bg-warning-rose"></span>
          <span class="text-slate-navy font-semibold text-[11px]">Kritis / Kategori 4 (&gt;30 Temuan)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded bg-amber-500"></span>
          <span class="text-slate-navy font-semibold text-[11px]">Sedang / Flocking (15-30)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded bg-sky-500"></span>
          <span class="text-slate-navy font-semibold text-[11px]">Rendah / Terpantau (&lt;15)</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded border border-slate-300 bg-surface-container-low"></span>
          <span class="text-secondary text-[11px]">Steril / Tanpa Laporan</span>
        </div>
      </div>
      <div class="text-secondary font-code-coordinate text-xs">
        Bandara Internasional Juanda (SUB/WARR) • Sisi Udara 29 Kolom × 12 Baris (Sumbu A–L)
      </div>
    </div>

  </div>
</div>
