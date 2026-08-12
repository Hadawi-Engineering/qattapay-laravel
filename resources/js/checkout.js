/**
 * Optional standalone init for QattaPay Blade buttons.
 * The Blade component already inlines equivalent logic; publish this file
 * only if you prefer an external asset.
 *
 * Usage: load after @hadawi/sdk browser.iife.js, then call QattaPayLaravel.mountAll().
 */
(function (global) {
  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function mountOne(el) {
    if (!global.QattaPay || !global.QattaPay.QattaPayCheckout) {
      throw new Error('[QattaPay] Browser SDK not loaded');
    }
    if (el.dataset.qattapayMounted === '1') {
      return;
    }

    var config = el.dataset.baseUrl
      ? { baseUrl: el.dataset.baseUrl }
      : { mode: el.dataset.mode || 'live' };

    var checkout = new global.QattaPay.QattaPayCheckout(config);
    var open = { mode: el.dataset.openMode || 'popup' };

    if (el.dataset.returnUrl) {
      open.returnUrl = el.dataset.returnUrl;
    }
    if (el.dataset.successUrl) {
      open.onSuccess = function () {
        global.location.href = el.dataset.successUrl;
      };
    }

    checkout.mountButton({
      container: el,
      variant: el.dataset.variant || 'primary',
      label: el.dataset.label || 'split',
      size: el.dataset.size || 'md',
      locale: el.dataset.locale || 'en',
      showBadge: el.dataset.showBadge !== 'false',
      showIcon: el.dataset.showIcon !== 'false',
      getIntentId: function () {
        return fetch(el.dataset.intentUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: JSON.stringify({}),
        })
          .then(function (res) {
            if (!res.ok) {
              throw new Error('Failed to create QattaPay intent (' + res.status + ')');
            }
            return res.json();
          })
          .then(function (data) {
            var id = data.intentId || (data.intent && data.intent.id);
            if (!id) {
              throw new Error('Intent response missing intentId');
            }
            return id;
          });
      },
      open: open,
    });

    el.dataset.qattapayMounted = '1';
  }

  function mountAll() {
    var nodes = document.querySelectorAll('[data-qattapay-button]');
    for (var i = 0; i < nodes.length; i++) {
      mountOne(nodes[i]);
    }
  }

  global.QattaPayLaravel = { mountAll: mountAll, mountOne: mountOne };
})(typeof window !== 'undefined' ? window : globalThis);
