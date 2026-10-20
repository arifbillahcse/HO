/**
 * Billing Toggle Module
 * Handles Monthly / Yearly pricing toggle with 30% yearly discount.
 * Integrates with window.i18n (I18nManager) for currency + language sync.
 */
(function () {
    'use strict';

    const YEARLY_DISCOUNT = 0.30;   // 30 % off when billed yearly
    const STORAGE_KEY     = 'billingPeriod';

    let isYearly = true; // default: yearly
    let hasToggle = false; // pages without the switch keep their own period label

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

    function isBangla() {
        return !!(window.i18n && window.i18n.currentLanguage === 'bn');
    }

    function billedNote(wrapper) {
        let note = wrapper.querySelector('.cloud-billed-note');
        if (!note) {
            note = document.createElement('div');
            note.className = 'cloud-billed-note';
            wrapper.appendChild(note);
        }
        return note;
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
            const monthly = wrapper.querySelector('[data-i18n="pricing.billedMonthly"]');
            const yearly  = wrapper.querySelector('[data-i18n="pricing.billedAnnually"]');
            if (monthly) monthly.classList.toggle('active', !isYearly);
            if (yearly)  yearly.classList.toggle('active',  isYearly);
        });
    }

    /* ──────────────────────────────────────────
       DOM: price cards
    ────────────────────────────────────────── */
    function updatePrices() {
        const sym = symbol();

        document.querySelectorAll('.cloud-pricing[data-yearly-price]').forEach(function (wrapper) {
            // data-yearly-price stores the BDT amount; convert to USD for calculations
            const yearlyBdt  = parseFloat(wrapper.getAttribute('data-yearly-price'));
            if (isNaN(yearlyBdt)) return;
            const bdtRate    = (window.i18n && window.i18n.exchangeRate) ? window.i18n.exchangeRate : 125;
            const yearlyUsd  = yearlyBdt / bdtRate;

            // Undiscounted monthly price = yearly / (12 × (1 − discount))  →  yearly / 8.4
            const fullMonthlyUsd = yearlyUsd / (12 * (1 - YEARLY_DISCOUNT));

            const amountEl   = wrapper.querySelector('.cloud-amount');
            const periodEl   = wrapper.querySelector('.cloud-period');
            const origBlock  = wrapper.querySelector('.cloud-price-original');
            const origCurEl  = origBlock  && origBlock.querySelector('[data-orig-currency]');
            const origAmtEl  = origBlock  && origBlock.querySelector('.orig-amount');
            const currEl     = wrapper.querySelector('[data-currency-symbol]');
            const noteEl     = billedNote(wrapper);

            // Both views show the same plan price: the yearly price, or that
            // price spread over 12 months. The strikethrough is the matching
            // price without the 30% discount.
            if (amountEl) amountEl.textContent = fmtAmt(isYearly ? yearlyUsd : yearlyUsd / 12);

            if (periodEl && hasToggle) {
                periodEl.textContent = isYearly
                    ? (t('pricing.perYear') || '/yr')
                    : (t('pricing.perMonth') || '/mo');
            }

            if (origBlock) {
                if (origCurEl) origCurEl.textContent = sym;
                if (origAmtEl) origAmtEl.textContent = fmtAmt(isYearly ? fullMonthlyUsd * 12 : fullMonthlyUsd);
                origBlock.style.display = '';
            }

            // Monthly view: say what is actually charged at checkout.
            if (isYearly) {
                noteEl.style.display = 'none';
            } else {
                const amount = sym + fmtAmt(yearlyUsd);
                noteEl.textContent = isBangla() ? 'বার্ষিক ' + amount + ' বিল করা হয়' : 'Billed ' + amount + ' yearly';
                noteEl.style.display = '';
            }

            // Always refresh currency symbol
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

        hasToggle = true;
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
