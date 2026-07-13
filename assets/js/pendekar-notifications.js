/**
 * PENDEKAR - Auto-dismiss notifikasi flash
 */
(function () {
  'use strict';

  function initAutoDismiss() {
    document.querySelectorAll('.pendekar-alert[data-auto-dismiss]').forEach(function (el) {
      var ms = parseInt(el.getAttribute('data-auto-dismiss'), 10) || 5000;
      setTimeout(function () {
        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
          bootstrap.Alert.getOrCreateInstance(el).close();
        } else {
          el.remove();
        }
      }, ms);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutoDismiss);
  } else {
    initAutoDismiss();
  }
})();
