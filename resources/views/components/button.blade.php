{{-- Official QattaPay checkout button (loads @hadawi/sdk browser IIFE). --}}
<div
    id="{{ $containerId }}"
    class="qattapay-button-root"
    data-qattapay-button
    data-intent-url="{{ $intentUrl }}"
    data-mode="{{ $mode }}"
    data-variant="{{ $variant }}"
    data-label="{{ $label }}"
    data-size="{{ $size }}"
    data-locale="{{ $locale }}"
    data-open-mode="{{ $openMode }}"
    @if ($successUrl) data-success-url="{{ $successUrl }}" @endif
    @if ($returnUrl) data-return-url="{{ $returnUrl }}" @endif
    data-show-badge="{{ $showBadge ? 'true' : 'false' }}"
    data-show-icon="{{ $showIcon ? 'true' : 'false' }}"
    @if ($baseUrl) data-base-url="{{ $baseUrl }}" @endif
></div>

@once
    <script src="{{ $sdkUrl }}" defer></script>
    <script>
        (function () {
            function csrfToken() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }

            function mountOne(el) {
                if (!window.QattaPay || !window.QattaPay.QattaPayCheckout) {
                    return false;
                }
                if (el.dataset.qattapayMounted === '1') {
                    return true;
                }

                var config = el.dataset.baseUrl
                    ? { baseUrl: el.dataset.baseUrl }
                    : { mode: el.dataset.mode || 'live' };

                var checkout = new window.QattaPay.QattaPayCheckout(config);
                var open = {
                    mode: el.dataset.openMode || 'popup',
                };

                if (el.dataset.returnUrl) {
                    open.returnUrl = el.dataset.returnUrl;
                }

                if (el.dataset.successUrl) {
                    open.onSuccess = function () {
                        window.location.href = el.dataset.successUrl;
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
                        }).then(function (res) {
                            if (!res.ok) {
                                throw new Error('Failed to create QattaPay intent (' + res.status + ')');
                            }
                            return res.json();
                        }).then(function (data) {
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
                return true;
            }

            function mountAll() {
                var nodes = document.querySelectorAll('[data-qattapay-button]');
                for (var i = 0; i < nodes.length; i++) {
                    mountOne(nodes[i]);
                }
            }

            function whenReady() {
                if (window.QattaPay && window.QattaPay.QattaPayCheckout) {
                    mountAll();
                    return;
                }
                var tries = 0;
                var timer = setInterval(function () {
                    tries += 1;
                    if (mountAll() || tries > 50) {
                        // mountAll always runs; stop once SDK is present or timed out
                    }
                    if (window.QattaPay && window.QattaPay.QattaPayCheckout) {
                        mountAll();
                        clearInterval(timer);
                    } else if (tries > 50) {
                        clearInterval(timer);
                        console.error('[QattaPay] Browser SDK failed to load');
                    }
                }, 100);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', whenReady);
            } else {
                whenReady();
            }
        })();
    </script>
@endonce
