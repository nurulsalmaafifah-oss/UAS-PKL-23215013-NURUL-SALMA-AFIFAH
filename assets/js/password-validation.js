/**
 * PENDEKAR - Validasi password (frontend)
 * Karakter khusus: ! @ # $ % ^ & * ( ) _ + - = ? . ,
 */
(function (global) {
  'use strict';

  var MESSAGE = 'Password harus terdiri dari minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta karakter khusus.';
  var SPECIAL_REGEX = /[!@#$%^&*()_+\-=?.,]/;

  function isValid(password) {
    if (!password || password.length < 8) return false;
    if (!/[A-Z]/.test(password)) return false;
    if (!/[a-z]/.test(password)) return false;
    if (!/[0-9]/.test(password)) return false;
    if (!SPECIAL_REGEX.test(password)) return false;
    return true;
  }

  function validate(password) {
    return isValid(password) ? null : MESSAGE;
  }

  function initPasswordForms() {
    document.querySelectorAll('.form-pendekar-password').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var pwdInput = form.querySelector('input[name="password"]');
        if (!pwdInput) return;
        var val = pwdInput.value;
        if (!val) return;
        var err = validate(val);
        if (err) {
          e.preventDefault();
          alert(err);
        }
      });
    });
  }

  global.PendekarPassword = {
    MESSAGE: MESSAGE,
    SPECIAL_REGEX: SPECIAL_REGEX,
    isValid: isValid,
    validate: validate
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPasswordForms);
  } else {
    initPasswordForms();
  }
})(window);
