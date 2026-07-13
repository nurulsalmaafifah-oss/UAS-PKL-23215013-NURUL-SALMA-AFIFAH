/**
 * Profile dropdown & ganti password
 */
(function () {
  'use strict';

  function initProfileMenu() {
    var dropdownEl = document.getElementById('profileDropdownMenu');
    var triggerEl = document.getElementById('profileDropdownTrigger');
    if (!dropdownEl || !triggerEl || typeof bootstrap === 'undefined') return;

    var dropdown = bootstrap.Dropdown.getOrCreateInstance(triggerEl);

    document.addEventListener('click', function (e) {
      if (!triggerEl.contains(e.target) && !dropdownEl.contains(e.target)) {
        dropdown.hide();
      }
    });

    var btnGanti = document.getElementById('btnGantiPassword');
    if (btnGanti) {
      btnGanti.addEventListener('click', function (e) {
        e.preventDefault();
        dropdown.hide();
        var modalEl = document.getElementById('modalGantiPassword');
        if (modalEl) {
          resetPasswordForm();
          bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
      });
    }

    var form = document.getElementById('formGantiPassword');
    if (form) {
      form.addEventListener('submit', handlePasswordSubmit);
    }

    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;
        var isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        var icon = btn.querySelector('i');
        if (icon) {
          icon.className = isPass ? 'ti ti-eye-off' : 'ti ti-eye';
        }
      });
    });
  }

  function resetPasswordForm() {
    var form = document.getElementById('formGantiPassword');
    var alertEl = document.getElementById('profilePasswordAlert');
    if (form) form.reset();
    if (alertEl) {
      alertEl.className = 'alert d-none';
      alertEl.textContent = '';
    }
    document.querySelectorAll('#modalGantiPassword .password-toggle-btn').forEach(function (btn) {
      var icon = btn.querySelector('i');
      if (icon) icon.className = 'ti ti-eye';
    });
    ['pwdLama', 'pwdBaru', 'pwdKonfirmasi'].forEach(function (id) {
      var inp = document.getElementById(id);
      if (inp) inp.type = 'password';
    });
  }

  function showPasswordAlert(type, message) {
    var alertEl = document.getElementById('profilePasswordAlert');
    if (!alertEl) return;
    alertEl.className = 'alert alert-' + type;
    alertEl.textContent = message;
    alertEl.classList.remove('d-none');
  }

  function handlePasswordSubmit(e) {
    e.preventDefault();
    var form = e.target;
    var btn = document.getElementById('btnSimpanPassword');
    var alertEl = document.getElementById('profilePasswordAlert');

    if (alertEl) alertEl.classList.add('d-none');

    var pwdBaru = form.password_baru.value;
    var pwdKonfirm = form.konfirmasi_password.value;

    if (window.PendekarPassword) {
      var pwdError = PendekarPassword.validate(pwdBaru);
      if (pwdError) {
        showPasswordAlert('warning', pwdError);
        return;
      }
    }
    if (pwdBaru !== pwdKonfirm) {
      showPasswordAlert('warning', 'Konfirmasi password tidak cocok.');
      return;
    }

    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    }

    var body = new FormData(form);

    fetch('api/ganti_password.php', {
      method: 'POST',
      body: body,
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.success) {
          showPasswordAlert('success', data.message);
          form.reset();
          setTimeout(function () {
            var modalEl = document.getElementById('modalGantiPassword');
            if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
          }, 1800);
        } else {
          showPasswordAlert('danger', data.message || 'Gagal memperbarui password.');
        }
      })
      .catch(function () {
        showPasswordAlert('danger', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
      })
      .finally(function () {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Simpan Password';
        }
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfileMenu);
  } else {
    initProfileMenu();
  }
})();
