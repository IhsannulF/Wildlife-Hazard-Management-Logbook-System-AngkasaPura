<script>
  // UNIFIED SINGLE DATE & DATE RANGE CALENDAR COMPONENT CONTROLLER
  const CalendarPicker = {
    isOpen: false,
    viewYear: 2026,
    viewMonth: 9, // 0-indexed (9 = Oktober)
    selectedStart: null, // 'YYYY-MM-DD'
    selectedEnd: null,   // 'YYYY-MM-DD'
    isSelectingRange: false,
    hoverDate: null,
    todayStr: '',

    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    monthShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],

    init() {
      const now = new Date();
      this.todayStr = this.formatYmd(now.getFullYear(), now.getMonth(), now.getDate());

      const sInput = document.getElementById('filterStartDate');
      const eInput = document.getElementById('filterEndDate');
      
      if (sInput && sInput.value) {
        this.selectedStart = sInput.value.trim();
      }
      if (eInput && eInput.value) {
        this.selectedEnd = eInput.value.trim();
      }

      // If one date is set, ensure selectedEnd is also set to the same date for consistency
      if (this.selectedStart && !this.selectedEnd) {
        this.selectedEnd = this.selectedStart;
      } else if (!this.selectedStart && this.selectedEnd) {
        this.selectedStart = this.selectedEnd;
      }

      this.isSelectingRange = false;

      // Initialize view month & year based on selection, existing table data, or today
      if (this.selectedStart) {
        const parts = this.selectedStart.split('-');
        if (parts.length === 3) {
          this.viewYear = parseInt(parts[0], 10);
          this.viewMonth = parseInt(parts[1], 10) - 1;
        }
      } else {
        const firstRow = document.querySelector('#logbookTable tbody tr[data-date]');
        if (firstRow && firstRow.getAttribute('data-date')) {
          const dStr = firstRow.getAttribute('data-date');
          const p = dStr.split('-');
          if (p.length === 3) {
            this.viewYear = parseInt(p[0], 10);
            this.viewMonth = parseInt(p[1], 10) - 1;
          }
        } else {
          this.viewYear = now.getFullYear();
          this.viewMonth = now.getMonth();
        }
      }

      this.updateDisplayLabel();
      this.render();

      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        const component = document.getElementById('calendarPickerComponent');
        if (component && !component.contains(e.target) && this.isOpen) {
          this.close();
        }
      });
    },

    toggle() {
      if (this.isOpen) {
        this.close();
      } else {
        this.open();
      }
    },

    open() {
      const dropdown = document.getElementById('calendarDropdown');
      const trigger = document.getElementById('datePickerTrigger');
      if (dropdown) {
        dropdown.classList.remove('hidden');
        this.isOpen = true;
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
        this.render();
      }
    },

    close() {
      const dropdown = document.getElementById('calendarDropdown');
      const trigger = document.getElementById('datePickerTrigger');
      if (dropdown) {
        dropdown.classList.add('hidden');
        this.isOpen = false;
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        this.hoverDate = null;
        this.isSelectingRange = false;
        this.updateRangeHighlight(null);
      }
    },

    navMonth(direction) {
      this.viewMonth += direction;
      if (this.viewMonth < 0) {
        this.viewMonth = 11;
        this.viewYear--;
      } else if (this.viewMonth > 11) {
        this.viewMonth = 0;
        this.viewYear++;
      }
      this.render();
    },

    formatYmd(year, month, day) {
      return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    },

    formatIndo(dateStr) {
      if (!dateStr) return '';
      const parts = dateStr.split('-');
      if (parts.length !== 3) return dateStr;
      const day = parseInt(parts[2], 10);
      const mIdx = parseInt(parts[1], 10) - 1;
      const year = parts[0];
      return `${day} ${this.monthShort[mIdx] || ''} ${year}`;
    },

    updateDisplayLabel() {
      const labelEl = document.getElementById('datePickerDisplay');
      const clearBtn = document.getElementById('datePickerClear');
      if (!labelEl) return;

      if (this.selectedStart && this.selectedEnd) {
        if (this.selectedStart === this.selectedEnd) {
          labelEl.innerText = this.formatIndo(this.selectedStart);
        } else {
          labelEl.innerText = `${this.formatIndo(this.selectedStart)} - ${this.formatIndo(this.selectedEnd)}`;
        }
        if (clearBtn) clearBtn.classList.remove('hidden');
      } else if (this.selectedStart) {
        labelEl.innerText = this.formatIndo(this.selectedStart);
        if (clearBtn) clearBtn.classList.remove('hidden');
      } else {
        labelEl.innerText = 'Pilih tanggal atau rentang...';
        if (clearBtn) clearBtn.classList.add('hidden');
      }
    },

    selectDay(dateStr) {
      if (!this.isSelectingRange) {
        // First click: select this day immediately as single day
        this.selectedStart = dateStr;
        this.selectedEnd = dateStr;
        this.isSelectingRange = true;
      } else {
        // Second click: create range or confirm 1 day
        if (dateStr < this.selectedStart) {
          this.selectedEnd = this.selectedStart;
          this.selectedStart = dateStr;
        } else {
          this.selectedEnd = dateStr;
        }
        this.isSelectingRange = false;
      }

      this.hoverDate = null;
      this.render();
      this.updateDisplayLabel();
    },

    hoverDay(dateStr) {
      if (this.isSelectingRange && this.selectedStart) {
        this.hoverDate = dateStr;
        this.updateRangeHighlight(dateStr);
      }
    },

    updateRangeHighlight(hoverDate) {
      const buttons = document.querySelectorAll('#calendarDaysGrid button[data-date]');
      const start = this.selectedStart;
      const end = (this.isSelectingRange && hoverDate) ? hoverDate : this.selectedEnd;

      if (!start) return;

      const min = (end && end < start) ? end : start;
      const max = (end && end > start) ? end : (end || start);

      buttons.forEach(btn => {
        const d = btn.getAttribute('data-date');
        const isToday = (d === this.todayStr);

        let cellClasses = 'h-8 text-xs flex items-center justify-center cursor-pointer transition-all relative font-medium ';

        if (min === max && d === min) {
          cellClasses += 'bg-injourney-teal text-white font-bold rounded-lg shadow-sm scale-105 z-10';
        } else if (d === min) {
          cellClasses += 'bg-injourney-teal text-white font-bold rounded-l-lg shadow-sm z-10';
        } else if (d === max) {
          cellClasses += 'bg-injourney-teal text-white font-bold rounded-r-lg shadow-sm z-10';
        } else if (d > min && d < max) {
          cellClasses += 'bg-teal-50 text-injourney-teal font-semibold rounded-none';
        } else if (isToday) {
          cellClasses += 'border border-injourney-teal text-injourney-teal font-bold rounded-lg hover:bg-slate-100';
        } else {
          cellClasses += 'text-slate-700 hover:bg-slate-100 rounded-lg';
        }

        btn.className = cellClasses;
      });

      // Update Selection Badge Text
      const badge = document.getElementById('calendarSelectionBadge');
      if (badge) {
        if (min === max) {
          badge.innerText = `${this.formatIndo(min)} (1 hari)`;
        } else {
          const d1 = new Date(min);
          const d2 = new Date(max);
          const daysCount = Math.round(Math.abs(d2 - d1) / 86400000) + 1;
          badge.innerText = `${this.formatIndo(min)} - ${this.formatIndo(max)} (${daysCount} hari)`;
        }
      }
    },

    applyPreset(type) {
      const now = new Date();
      let start, end;

      if (type === 'today') {
        start = this.formatYmd(now.getFullYear(), now.getMonth(), now.getDate());
        end = start;
        this.viewYear = now.getFullYear();
        this.viewMonth = now.getMonth();
      } else if (type === 'last7') {
        const past = new Date(now.getTime() - 6 * 86400000);
        start = this.formatYmd(past.getFullYear(), past.getMonth(), past.getDate());
        end = this.formatYmd(now.getFullYear(), now.getMonth(), now.getDate());
        this.viewYear = now.getFullYear();
        this.viewMonth = now.getMonth();
      } else if (type === 'thisMonth') {
        const first = new Date(this.viewYear, this.viewMonth, 1);
        const last = new Date(this.viewYear, this.viewMonth + 1, 0);
        start = this.formatYmd(first.getFullYear(), first.getMonth(), first.getDate());
        end = this.formatYmd(last.getFullYear(), last.getMonth(), last.getDate());
      } else if (type === 'lastMonth') {
        let m = this.viewMonth - 1;
        let y = this.viewYear;
        if (m < 0) { m = 11; y--; }
        const first = new Date(y, m, 1);
        const last = new Date(y, m + 1, 0);
        start = this.formatYmd(first.getFullYear(), first.getMonth(), first.getDate());
        end = this.formatYmd(last.getFullYear(), last.getMonth(), last.getDate());
        this.viewYear = y;
        this.viewMonth = m;
      }

      this.selectedStart = start;
      this.selectedEnd = end;
      this.isSelectingRange = false;
      this.render();
      this.updateDisplayLabel();
    },

    confirm() {
      if (this.selectedStart && !this.selectedEnd) {
        this.selectedEnd = this.selectedStart;
      }

      this.isSelectingRange = false;

      const sInput = document.getElementById('filterStartDate');
      const eInput = document.getElementById('filterEndDate');
      if (sInput) sInput.value = this.selectedStart || '';
      if (eInput) eInput.value = this.selectedEnd || '';

      this.updateDisplayLabel();
      this.close();

      if (typeof searchTable === 'function') {
        searchTable();
      }

      if (typeof applyDateFilterServer === 'function') {
        applyDateFilterServer();
      }
    },

    clear() {
      this.selectedStart = null;
      this.selectedEnd = null;
      this.hoverDate = null;
      this.isSelectingRange = false;

      const sInput = document.getElementById('filterStartDate');
      const eInput = document.getElementById('filterEndDate');
      if (sInput) sInput.value = '';
      if (eInput) eInput.value = '';

      this.updateDisplayLabel();
      this.render();
      this.close();

      const url = new URL(window.location.href);
      if (url.searchParams.has('start_date') || url.searchParams.has('end_date') || url.searchParams.has('tanggal')) {
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');
        url.searchParams.delete('tanggal');
        url.searchParams.delete('page');
        window.location.href = url.toString();
      } else if (typeof searchTable === 'function') {
        searchTable();
      }
    },

    render() {
      const monthText = document.getElementById('calendarMonthText');
      const yearText = document.getElementById('calendarYearText');
      const grid = document.getElementById('calendarDaysGrid');
      const badge = document.getElementById('calendarSelectionBadge');

      if (!grid) return;

      if (monthText) monthText.innerText = this.monthNames[this.viewMonth];
      if (yearText) yearText.innerText = this.viewYear;

      const firstDayIndex = new Date(this.viewYear, this.viewMonth, 1).getDay();
      const totalDays = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
      const prevMonthTotalDays = new Date(this.viewYear, this.viewMonth, 0).getDate();

      const min = (this.selectedEnd && this.selectedEnd < this.selectedStart) ? this.selectedEnd : this.selectedStart;
      const max = (this.selectedEnd && this.selectedEnd > this.selectedStart) ? this.selectedEnd : (this.selectedEnd || this.selectedStart);

      let html = '';

      // Greyed previous month padding days
      for (let p = firstDayIndex - 1; p >= 0; p--) {
        const prevDay = prevMonthTotalDays - p;
        html += `<div class="h-8 flex items-center justify-center text-[11px] text-slate-300 font-normal cursor-default select-none">${prevDay}</div>`;
      }

      // Current month days
      for (let d = 1; d <= totalDays; d++) {
        const dateStr = this.formatYmd(this.viewYear, this.viewMonth, d);
        const isToday = (dateStr === this.todayStr);

        let cellClasses = 'h-8 text-xs flex items-center justify-center cursor-pointer transition-all relative font-medium ';

        if (min && max && min === max && dateStr === min) {
          cellClasses += 'bg-injourney-teal text-white font-bold rounded-lg shadow-sm scale-105 z-10';
        } else if (dateStr === min) {
          cellClasses += 'bg-injourney-teal text-white font-bold rounded-l-lg shadow-sm z-10';
        } else if (dateStr === max) {
          cellClasses += 'bg-injourney-teal text-white font-bold rounded-r-lg shadow-sm z-10';
        } else if (min && max && dateStr > min && dateStr < max) {
          cellClasses += 'bg-teal-50 text-injourney-teal font-semibold rounded-none';
        } else if (isToday) {
          cellClasses += 'border border-injourney-teal text-injourney-teal font-bold rounded-lg hover:bg-slate-100';
        } else {
          cellClasses += 'text-slate-700 hover:bg-slate-100 rounded-lg';
        }

        html += `
          <button 
            type="button" 
            data-date="${dateStr}"
            onclick="CalendarPicker.selectDay('${dateStr}')" 
            ondblclick="CalendarPicker.confirm()"
            onmouseenter="CalendarPicker.hoverDay('${dateStr}')"
            class="${cellClasses}"
            title="${dateStr}"
          >
            ${d}
          </button>
        `;
      }

      // Next month padding days
      const totalRendered = firstDayIndex + totalDays;
      const nextDays = (7 - (totalRendered % 7)) % 7;
      for (let n = 1; n <= nextDays; n++) {
        html += `<div class="h-8 flex items-center justify-center text-[11px] text-slate-300 font-normal cursor-default select-none">${n}</div>`;
      }

      grid.innerHTML = html;

      // Update Selection Badge Text
      if (badge) {
        if (min && max) {
          if (min === max) {
            badge.innerText = `${this.formatIndo(min)} (1 hari)`;
          } else {
            const d1 = new Date(min);
            const d2 = new Date(max);
            const daysCount = Math.round(Math.abs(d2 - d1) / 86400000) + 1;
            badge.innerText = `${this.formatIndo(min)} - ${this.formatIndo(max)} (${daysCount} hari)`;
          }
        } else {
          badge.innerText = 'Pilih 1 hari atau rentang tanggal';
        }
      }
    }
  };

  // Global helper bridge functions for inline onclick handlers
  function toggleCalendarDropdown(e) {
    if (e) e.stopPropagation();
    CalendarPicker.toggle();
  }

  function calendarNavMonth(direction) {
    CalendarPicker.navMonth(direction);
  }

  function calendarApplyPreset(type) {
    CalendarPicker.applyPreset(type);
  }

  function confirmCalendarSelection() {
    CalendarPicker.confirm();
  }

  function clearCalendarFilter() {
    CalendarPicker.clear();
  }

  // Initialize CalendarPicker on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', () => {
    CalendarPicker.init();
  });
</script>
