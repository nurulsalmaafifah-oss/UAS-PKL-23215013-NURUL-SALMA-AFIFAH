/**
 * PENDEKAR - Pencarian & pengurutan kategori (realtime via API)
 */
(function () {
  'use strict';

  function debounce(fn, delay) {
    var timer;
    return function () {
      var ctx = this, args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
  }

  function escHtml(str) {
    if (str == null) return '';
    var d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
  }

  function initKategoriList() {
    var searchEl = document.getElementById('kategoriSearch');
    var sortEl = document.getElementById('kategoriSort');
    var tbody = document.getElementById('kategoriTableBody');
    var countEl = document.getElementById('kategoriCount');
    var resetBtn = document.getElementById('kategoriResetFilter');

    if (!searchEl || !sortEl || !tbody) return;

    var canManage = tbody.getAttribute('data-can-manage') === '1';
    var colspan = parseInt(tbody.getAttribute('data-colspan') || '4', 10);
    var loading = false;

    function renderRows(items, hasQuery) {
      if (!items.length) {
        var msg = hasQuery ? 'Data tidak ditemukan' : 'Belum ada kategori.';
        tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center text-muted py-4 list-empty-state">' +
          '<i class="ti ti-search-off d-block mb-2" style="font-size:2rem;opacity:.5;"></i>' + msg + '</td></tr>';
        return;
      }

      var html = '';
      items.forEach(function (row, idx) {
        var desk = row.deskripsi ? row.deskripsi : '-';
        html += '<tr>';
        html += '<td>' + (idx + 1) + '</td>';
        html += '<td><h6 class="fw-semibold mb-0">' + escHtml(row.nama) + '</h6>';
        html += '<div class="d-md-none mt-2 text-muted" style="font-size:12px;">' + escHtml(desk);
        html += ' <span class="badge bg-light text-dark border ms-1">' + parseInt(row.jml_dokumen, 10) + ' dokumen</span></div></td>';
        html += '<td class="d-none d-md-table-cell">' + escHtml(desk) + '</td>';
        html += '<td class="d-none d-md-table-cell text-center"><span class="badge bg-primary rounded-3">' +
          parseInt(row.jml_dokumen, 10) + '</span></td>';

        if (canManage) {
          html += '<td><div class="d-flex flex-wrap gap-2">';
          html += '<a href="index.php?page=kategori&action=edit&id=' + row.id + '" class="btn btn-sm btn-warning text-nowrap">';
          html += '<i class="ti ti-edit"></i> Edit</a>';
          html += '<a href="index.php?page=kategori&action=hapus&id=' + row.id + '" class="btn btn-sm btn-danger text-nowrap" ';
          html += 'onclick="return confirm(\'Yakin ingin menghapus kategori ini?\')">';
          html += '<i class="ti ti-trash"></i> Hapus</a></div></td>';
        }

        html += '</tr>';
      });

      tbody.innerHTML = html;
    }

    function updateCount(total, hasQuery) {
      if (!countEl) return;
      if (hasQuery) {
        countEl.innerHTML = 'Menampilkan <strong>' + total + '</strong> kategori';
      } else {
        countEl.innerHTML = 'Menampilkan <strong>' + total + '</strong> kategori';
      }
    }

    function loadData() {
      if (loading) return;
      loading = true;
      tbody.classList.add('opacity-50');

      var q = searchEl.value.trim();
      var sort = sortEl.value || 'terbanyak';
      var params = new URLSearchParams({ q: q, sort: sort });

      fetch('api/kategori_list.php?' + params.toString(), { credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (res) {
          if (!res.success) {
            throw new Error(res.message || 'Gagal memuat data');
          }
          renderRows(res.data || [], q !== '');
          updateCount(res.total || 0, q !== '');
        })
        .catch(function () {
          tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center text-danger py-4">' +
            'Gagal memuat data kategori.</td></tr>';
        })
        .finally(function () {
          loading = false;
          tbody.classList.remove('opacity-50');
        });
    }

    searchEl.addEventListener('input', debounce(loadData, 300));
    sortEl.addEventListener('change', loadData);

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        searchEl.value = '';
        sortEl.value = 'terbanyak';
        loadData();
      });
    }

    loadData();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKategoriList);
  } else {
    initKategoriList();
  }
})();
