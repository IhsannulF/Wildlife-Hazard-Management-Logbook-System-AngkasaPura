@php
  $headerTitle = $title ?? 'Logbook Sisi Udara';
  $headerSubtitle = $subtitle ?? '';
  $headerIcon = $icon ?? 'space_dashboard';
  $headerMaxWidth = $maxWidth ?? 'max-w-[1680px]';
@endphp

<!-- ================= UNIFIED WHMS PAGE TOP HEADER BAR ================= -->
<header class="whms-page-header static lg:sticky lg:top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-2xs select-none">
  <div class="{{ $headerMaxWidth }} mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col md:flex-row md:items-center justify-between gap-3 min-h-[62px]">
    
    <!-- Left: Hamburger Menu Trigger (Desktop) + Title & Description -->
    <div class="flex items-center gap-3 sm:gap-3.5 min-w-0">
      
      <!-- Desktop Hamburger Menu Trigger Button (Auto-Hide Mode) -->
      <button type="button" 
              id="sidebarTriggerTab" 
              onclick="toggleSidebarClick()" 
              class="hidden lg:flex w-10 h-10 rounded-xl bg-slate-50 hover:bg-cyan-50/80 text-slate-700 hover:text-[#00A9C1] border border-slate-200/90 hover:border-cyan-200 shadow-2xs hover:shadow-xs items-center justify-center cursor-pointer select-none group transition-all duration-200 shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]" 
              title="Buka Menu Navigasi"
              aria-label="Buka Menu Navigasi">
        <span class="material-symbols-outlined text-2xl text-slate-600 group-hover:text-[#00A9C1] transition-transform group-hover:scale-105 leading-none">menu</span>
      </button>

      <!-- Title & Subtitle -->
      <div class="min-w-0">
        <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-snug">
          {{ $headerTitle }}
        </h1>
        @if(!empty($headerSubtitle))
          <p class="text-[11px] sm:text-xs text-slate-500 font-normal mt-0.5 line-clamp-2 md:line-clamp-none">
            {{ $headerSubtitle }}
          </p>
        @endif
      </div>
    </div>

    <!-- Right: Page Specific Action Controls / Tabs / Buttons -->
    @if(isset($actions) && !empty($actions))
      <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start md:self-center overflow-x-auto max-w-full">
        {!! $actions !!}
      </div>
    @elseif(!empty(trim($slot ?? '')))
      <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start md:self-center overflow-x-auto max-w-full">
        {!! $slot !!}
      </div>
    @elseif(View::hasSection('header_actions'))
      <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start md:self-center overflow-x-auto max-w-full">
        @yield('header_actions')
      </div>
    @endif

  </div>
</header>
