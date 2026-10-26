@php
  $hasDate = !empty($startDate) || !empty($endDate);
  $initialLabel = 'Pilih tanggal atau rentang...';
  if (!empty($startDate) && !empty($endDate)) {
      if ($startDate === $endDate) {
          $initialLabel = \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y');
      } else {
          $initialLabel = \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y');
      }
  } elseif (!empty($startDate)) {
      $initialLabel = \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y');
  } elseif (!empty($endDate)) {
      $initialLabel = 's/d ' . \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y');
  }
@endphp

<!-- UNIFIED SINGLE DATE & RANGE CALENDAR PICKER -->
<div class="relative inline-block text-left" id="calendarPickerComponent">
  <div class="flex items-center gap-1.5">
    <label class="font-label-md text-label-md text-secondary">Tanggal:</label>
    
    <!-- Single Trigger Field -->
    <div class="relative">
      <button 
        type="button" 
        id="datePickerTrigger" 
        onclick="toggleCalendarDropdown(event)" 
        class="h-9 px-3 py-1.5 text-xs font-label-md rounded-lg border border-border-subtle bg-white text-on-surface hover:border-injourney-teal focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 transition-all flex items-center gap-2 cursor-pointer shadow-2xs group"
        aria-haspopup="true"
        aria-expanded="false"
        title="Klik untuk memilih tanggal (sehari atau rentang hari)"
      >
        <span class="material-symbols-outlined text-[17px] text-injourney-teal group-hover:scale-110 transition-transform">calendar_month</span>
        <span id="datePickerDisplay" class="font-semibold text-slate-700 min-w-[130px] text-left">{{ $initialLabel }}</span>
        
        <span 
          id="datePickerClear" 
          onclick="event.stopPropagation(); clearCalendarFilter();" 
          class="{{ $hasDate ? '' : 'hidden ' }}material-symbols-outlined text-[15px] text-slate-400 hover:text-rose-500 hover:scale-110 transition-all ml-1 p-0.5 rounded-full hover:bg-rose-50" 
          title="Hapus filter tanggal"
        >close</span>
        
        <span class="material-symbols-outlined text-[16px] text-slate-400">arrow_drop_down</span>
      </button>

      <!-- Hidden inputs for server form submission / URL parameters -->
      <input type="hidden" id="filterStartDate" name="start_date" value="{{ $startDate ?? '' }}">
      <input type="hidden" id="filterEndDate" name="end_date" value="{{ $endDate ?? '' }}">

      <!-- Floating Calendar Dropdown Popover -->
      <div 
        id="calendarDropdown" 
        class="hidden absolute left-0 sm:left-auto sm:right-0 top-full mt-1.5 z-50 bg-white rounded-2xl border border-slate-200/90 shadow-2xl p-4 w-[330px] select-none text-on-surface animate-in fade-in duration-150"
        onclick="event.stopPropagation()"
      >
        <!-- Header: Month & Year Navigator -->
        <div class="flex items-center justify-between pb-3 mb-2.5 border-b border-slate-100">
          <button 
            type="button" 
            onclick="calendarNavMonth(-1)" 
            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 transition-colors cursor-pointer" 
            title="Bulan sebelumnya"
          >
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
          </button>
          
          <div class="flex items-center gap-1.5">
            <span id="calendarMonthText" class="font-bold text-sm text-slate-800">Oktober</span>
            <span id="calendarYearText" class="font-bold text-sm text-slate-500">2026</span>
          </div>

          <button 
            type="button" 
            onclick="calendarNavMonth(1)" 
            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 transition-colors cursor-pointer" 
            title="Bulan berikutnya"
          >
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
          </button>
        </div>

        <!-- Days of Week Header (Min - Sab) -->
        <div class="grid grid-cols-7 gap-1 text-center font-bold text-[10px] text-slate-400 mb-1.5 tracking-wider uppercase">
          <span class="text-rose-400">Min</span>
          <span>Sen</span>
          <span>Sel</span>
          <span>Rab</span>
          <span>Kam</span>
          <span>Jum</span>
          <span>Sab</span>
        </div>

        <!-- Calendar Days Matrix -->
        <div id="calendarDaysGrid" onmouseleave="CalendarPicker.hoverDay(null)" class="grid grid-cols-7 gap-1 text-center text-xs min-h-[210px]">
          <!-- Dynamically generated via JavaScript -->
        </div>

        <!-- Selection Status & Quick Presets -->
        <div class="mt-3 pt-3 border-t border-slate-100 space-y-2.5">
          <!-- Selection Status Info -->
          <div class="flex items-center justify-between text-[11px] bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
            <span class="text-slate-500 font-medium">Pilihan:</span>
            <span id="calendarSelectionBadge" class="font-bold text-injourney-teal truncate max-w-[210px]">
              Pilih 1 hari atau rentang tanggal
            </span>
          </div>

          <!-- Quick Filter Chips -->
          <div class="flex items-center gap-1 text-[10px] flex-wrap">
            <button type="button" onclick="calendarApplyPreset('today')" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-injourney-teal text-slate-700 transition-colors cursor-pointer font-medium">Hari Ini</button>
            <button type="button" onclick="calendarApplyPreset('last7')" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-injourney-teal text-slate-700 transition-colors cursor-pointer font-medium">7 Hari</button>
            <button type="button" onclick="calendarApplyPreset('thisMonth')" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-injourney-teal text-slate-700 transition-colors cursor-pointer font-medium">Bulan Ini</button>
            <button type="button" onclick="calendarApplyPreset('lastMonth')" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-teal-50 hover:text-injourney-teal text-slate-700 transition-colors cursor-pointer font-medium">Bulan Lalu</button>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between gap-2 pt-1">
            <button 
              type="button" 
              onclick="clearCalendarFilter()" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
            >
              Reset
            </button>
            <button 
              type="button" 
              onclick="confirmCalendarSelection()" 
              class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-teal-700 text-white shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
            >
              <span class="material-symbols-outlined text-[15px]">check_circle</span>
              <span>Terapkan Filter</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('partials.scripts-calendar')
