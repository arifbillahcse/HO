/**
 * Billing Toggle Module
 * Handles the Monthly / Yearly pricing toggle. Monthly shows each plan's own
 * monthly price (data-monthly-price); plans without one stay yearly.
 * Integrates with window.i18n (I18nManager) for currency + language sync.
 */
(function () {
    'use strict';

    const YEARLY_DISCOUNT = 0.30;   // 30 % off when billed yearly
    const STORAGE_KEY     = 'billingPeriod';

    let isYearly = true; // default: yearly

    /* ──────────────────────────────────────────
       Persistence helpers
    ────────────────────────────────────────── */
    function loadPref() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved !== null) {
                // User has an explicit saved preference — honour it
                isYearly = (saved !== 'monthly');
            } else {
                // No saved pref — check for page-specific default via data attribute
                const toggleWrapper = document.querySelector('.billing-toggle');
                const pageDefault   = toggleWrapper && toggleWrapper.getAttribute('data-billing-default');
                isYearly = (pageDefault !== 'monthly');
            }
        } catch (_) {
            isYearly = true;
        }
    }

    function savePref() {
        try { localStorage.setItem(STORAGE_KEY, isYearly ? 'yearly' : 'monthly'); } catch (_) {}
    }

    /* ──────────────────────────────────────────
       Currency / format helpers
    ────────────────────────────────────────── */
    function symbol() {
        return (window.i18n && window.i18n.currentCurrency === 'USD') ? '$' : '৳';
    }

    function fmtAmt(usd) {
        if (!window.i18n) return parseFloat(usd).toFixed(2);
        return window.i18n.formatPrice(usd).replace(/[^\d০-৯.,]/g, '');
    }

    function t(key) {
        if (window.i18n) {
            const v = window.i18n.translate(key);
            return (v && v !== key) ? v : null;
        }
        return null;
    }

    /* ──────────────────────────────────────────
       DOM: toggle labels
    ────────────────────────────────────────── */
    function updateLabels() {
        document.querySelectorAll('.billing-toggle').forEach(function (wrapper) {
            const labels = wrapper.querySelectorAll('.toggle-label');
            if (labels[0]) labels[0].classList.toggle('active', !isYearly);
            if (labels[1]) labels[1].classList.toggle('active',  isYearly);
        });
    }

    /* ──────────────────────────────────────────
       DOM: price cards
    ────────────────────────────────────────── */
    // data-period-year / data-period-month hold "translationKey|fallback".
    function setPeriod(el, spec) {
        if (!el || !spec) return;
        const parts = spec.split('|');
        el.setAttribute('data-i18n', parts[0]); // so a language switch keeps the right label
        el.textContent = t(parts[0]) || parts[1] || '';
    }

    function updatePrices() {
        const sym     = symbol();
        const bdtRate = (window.i18n && window.i18n.exchangeRate) ? window.i18n.exchangeRate : 125;

        document.querySelectorAll('.cloud-pricing[data-yearly-price]').forEach(function (wrapper) {
            // Prices are stored in BDT; formatting expects USD.
            const yearlyUsd = parseFloat(wrapper.getAttribute('data-yearly-price')) / bdtRate;
            if (isNaN(yearlyUsd)) return;
            const monthlyBdt = parseFloat(wrapper.getAttribute('data-monthly-price'));

            // Plans without a monthly price (sold yearly only) always show yearly.
            const showMonthly = !isYearly && !isNaN(monthlyBdt);

            const amountEl  = wrapper.querySelector('.cloud-amount');
            const periodEl  = wrapper.querySelector('.cloud-period');
            const origBlock = wrapper.querySelector('.cloud-price-original');
            const origCurEl = origBlock && origBlock.querySelector('[data-orig-currency]');
            const origAmtEl = origBlock && origBlock.querySelector('.orig-amount');
            const currEl    = wrapper.querySelector('[data-currency-symbol]');
            const orderBtn  = wrapper.parentElement && wrapper.parentElement.querySelector('.cloud-order-btn[data-monthly-url]');

            if (amountEl) amountEl.textContent = fmtAmt(showMonthly ? monthlyBdt / bdtRate : yearlyUsd);
            if (periodEl) setPeriod(periodEl, periodEl.getAttribute(showMonthly ? 'data-period-month' : 'data-period-year'));

            // Strikethrough = yearly price without the 30% discount; the monthly price has none.
            if (origBlock) {
                if (showMonthly) {
                    origBlock.style.display = 'none';
                } else {
                    if (origCurEl) origCurEl.textContent = sym;
                    if (origAmtEl) origAmtEl.textContent = fmtAmt(yearlyUsd / (1 - YEARLY_DISCOUNT));
                    origBlock.style.display = '';
                }
            }

            // Order the billing cycle that is on screen.
            if (orderBtn) orderBtn.href = orderBtn.getAttribute(showMonthly ? 'data-monthly-url' : 'data-yearly-url');

            if (currEl) currEl.textContent = sym;
        });
    }

    /* ──────────────────────────────────────────
       Patch window.i18n so we stay in sync
    ────────────────────────────────────────── */
    function patchI18n() {
        if (!window.i18n) return;

        const origCurrency = window.i18n.setCurrency.bind(window.i18n);
        window.i18n.setCurrency = function (currency) {
            origCurrency(currency);
            updatePrices();
        };

        const origLanguage = window.i18n.setLanguage.bind(window.i18n);
        window.i18n.setLanguage = function (lang) {
            origLanguage(lang);
            updateLabels();
            updatePrices();
        };
    }

    /* ──────────────────────────────────────────
       Init
    ────────────────────────────────────────── */
    function init() {
        const toggle = document.getElementById('billingToggle');

        // Pages without the switch sell yearly only: still fill in the
        // yearly and crossed-out prices, and keep them in sync with currency.
        if (!toggle) {
            if (!document.querySelector('.cloud-pricing[data-yearly-price]')) return; // not a pricing page
            isYearly = true;
            patchI18n();
            updatePrices();
            return;
        }

        loadPref();
        patchI18n();

        // Set toggle visual state
        toggle.checked = isYearly;

        updateLabels();
        updatePrices();

        // Wire up toggle events
        toggle.addEventListener('change', function () {
            isYearly = toggle.checked;
            savePref();
            updateLabels();
            updatePrices();
        });
    }

    // i18n.js defers its first render with setTimeout(0), so we wait a tick
    // longer to ensure translations are already applied before billing runs.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 50); });
    } else {
        setTimeout(init, 50);
    }

    // Expose for external calls (e.g. currency/language buttons)
    window.billing = { refresh: function () { updateLabels(); updatePrices(); } };
})();
