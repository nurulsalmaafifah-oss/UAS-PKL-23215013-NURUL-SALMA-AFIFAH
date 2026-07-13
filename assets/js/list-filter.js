/**
 * PENDEKAR - Client-side search, filter & pagination for data tables
 */
(function ($) {
  'use strict';

  function debounce(fn, delay) {
    var timer;
    return function () {
      var ctx = this, args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
  }

  function PendekarListFilter(options) {
    this.opts = $.extend({
      table: null,
      searchInput: null,
      resetBtn: null,
      countEl: null,
      paginationEl: null,
      perPage: 10,
      emptyColspan: 6,
      emptyMessage: 'Data tidak ditemukan',
      emptyIcon: 'ti ti-search-off',
      filters: []
    }, options);

    this.$table = $(this.opts.table);
    this.$tbody = this.$table.find('tbody');
    this.$allRows = this.$tbody.find('tr.list-data-row');
    this.currentPage = 1;
    this.init();
  }

  PendekarListFilter.prototype.init = function () {
    var self = this;

    $(this.opts.searchInput).on('input', debounce(function () {
      self.currentPage = 1;
      self.apply();
    }, 300));

    this.opts.filters.forEach(function (f) {
      $(f.el).on('change', function () {
        self.currentPage = 1;
        self.apply();
      });
    });

    if (this.opts.resetBtn) {
      $(this.opts.resetBtn).on('click', function () {
        $(self.opts.searchInput).val('');
        self.opts.filters.forEach(function (f) {
          $(f.el).val('');
        });
        self.currentPage = 1;
        self.apply();
      });
    }

    this.apply();
  };

  PendekarListFilter.prototype.getSearchQuery = function () {
    return ($(this.opts.searchInput).val() || '').toLowerCase().trim();
  };

  PendekarListFilter.prototype.rowMatches = function ($row) {
    var q = this.getSearchQuery();
    var searchText = ($row.attr('data-search') || '').toLowerCase();

    if (q && searchText.indexOf(q) === -1) {
      return false;
    }

    for (var i = 0; i < this.opts.filters.length; i++) {
      var f = this.opts.filters[i];
      var val = ($(f.el).val() || '').trim();
      if (!val) continue;

      var rowVal = $row.attr('data-' + f.key) || '';
      if (f.match === 'contains') {
        if (rowVal.toLowerCase().indexOf(val.toLowerCase()) === -1) return false;
      } else if (f.match === 'date') {
        if (rowVal !== val) return false;
      } else {
        if (rowVal !== val) return false;
      }
    }

    return true;
  };

  PendekarListFilter.prototype.apply = function () {
    var self = this;
    var matched = [];

    this.$allRows.each(function () {
      var $row = $(this);
      if (self.rowMatches($row)) {
        matched.push($row);
      }
    });

    var total = this.$allRows.length;
    var filtered = matched.length;
    var perPage = this.opts.perPage;
    var totalPages = Math.max(1, Math.ceil(filtered / perPage));

    if (this.currentPage > totalPages) {
      this.currentPage = totalPages;
    }

    this.$allRows.hide();
    this.$tbody.find('tr.list-empty-row').remove();

    if (filtered === 0) {
      this.$tbody.append(
        '<tr class="list-empty-row list-empty-state">' +
        '<td colspan="' + this.opts.emptyColspan + '">' +
        '<i class="' + this.opts.emptyIcon + '"></i>' +
        '<div>' + this.opts.emptyMessage + '</div>' +
        '</td></tr>'
      );
    } else {
      var start = (this.currentPage - 1) * perPage;
      var end = start + perPage;
      matched.slice(start, end).forEach(function ($row, idx) {
        $row.show();
        $row.find('td:first').text(start + idx + 1);
      });
    }

    this.updateCount(total, filtered);
    this.renderPagination(filtered, totalPages);
  };

  PendekarListFilter.prototype.updateCount = function (total, filtered) {
    if (!this.opts.countEl) return;
    var $el = $(this.opts.countEl);
    var q = this.getSearchQuery();
    var hasFilter = q || this.opts.filters.some(function (f) {
      return ($(f.el).val() || '').trim() !== '';
    });

    if (!hasFilter) {
      $el.html('Menampilkan <strong>' + filtered + '</strong> data');
    } else {
      $el.html('Menampilkan <strong>' + filtered + '</strong> dari <strong>' + total + '</strong> data');
    }
  };

  PendekarListFilter.prototype.renderPagination = function (filtered, totalPages) {
    var $wrap = $(this.opts.paginationEl);
    if (!$wrap.length) return;

    if (filtered <= this.opts.perPage) {
      $wrap.html(
        '<div class="list-pagination-info">Halaman 1 dari 1</div>'
      );
      return;
    }

    var self = this;
    var html = '<div class="list-pagination-info">Halaman ' + this.currentPage + ' dari ' + totalPages + '</div>';
    html += '<nav><ul class="pagination pagination-sm mb-0">';

    html += '<li class="page-item' + (this.currentPage <= 1 ? ' disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="prev">&laquo;</a></li>';

    var startPage = Math.max(1, this.currentPage - 2);
    var endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }

    for (var p = startPage; p <= endPage; p++) {
      html += '<li class="page-item' + (p === this.currentPage ? ' active' : '') + '">' +
        '<a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
    }

    html += '<li class="page-item' + (this.currentPage >= totalPages ? ' disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="next">&raquo;</a></li>';
    html += '</ul></nav>';

    $wrap.html(html);

    $wrap.find('a.page-link').on('click', function (e) {
      e.preventDefault();
      if ($(this).parent().hasClass('disabled')) return;
      var page = $(this).data('page');
      if (page === 'prev') {
        self.currentPage = Math.max(1, self.currentPage - 1);
      } else if (page === 'next') {
        self.currentPage = Math.min(totalPages, self.currentPage + 1);
      } else {
        self.currentPage = parseInt(page, 10);
      }
      self.apply();
      $('html, body').animate({ scrollTop: self.$table.offset().top - 100 }, 200);
    });
  };

  window.PendekarListFilter = PendekarListFilter;
})(jQuery);
