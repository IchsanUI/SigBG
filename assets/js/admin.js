/**
 * Admin utilities
 * shared across dashboard views
 */

window.csrf_token = '';
window.base_url = '';

document.addEventListener('DOMContentLoaded', function () {
  // Fill csrf tokens from meta tags
  var csrfEl = document.querySelector('meta[name="csrf-token"]');
  if (csrfEl) {
    window.csrf_token = csrfEl.getAttribute('content');
  }
});
