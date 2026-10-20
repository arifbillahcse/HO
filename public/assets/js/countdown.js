/**
 * Countdown Timer for Offer Page
 * Each visitor gets 7 days from their first visit; when it runs out a new
 * 7 days starts. The end time is remembered in localStorage.
 */
(function() {
    'use strict';

    const STORAGE_KEY = 'countdownEnd';
    const ONE_WEEK = 7 * 24 * 60 * 60 * 1000; // 7 days in milliseconds

    function readEnd() {
        try {
            return parseInt(localStorage.getItem(STORAGE_KEY), 10);
        } catch (_) {
            return NaN;
        }
    }

    function saveEnd(end) {
        try { localStorage.setItem(STORAGE_KEY, String(end)); } catch (_) {}
    }

    // Helper function to pad single digits with zero
    function padZero(num) {
        return num < 10 ? '0' + num : String(num);
    }

    function startCountdown() {
        const daysEl = document.getElementById('days');
        if (!daysEl) return; // no countdown on this page

        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        const secondsEl = document.getElementById('seconds');
        const daysRemainingEl = document.getElementById('daysRemaining');

        let countdownEnd = readEnd();

        function tick() {
            const now = Date.now();

            // First visit, or the week is over: start a new week
            if (isNaN(countdownEnd) || countdownEnd <= now) {
                countdownEnd = now + ONE_WEEK;
                saveEnd(countdownEnd);
            }

            const distance = countdownEnd - now;
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            daysEl.textContent = padZero(days);
            if (hoursEl) hoursEl.textContent = padZero(hours);
            if (minutesEl) minutesEl.textContent = padZero(minutes);
            if (secondsEl) secondsEl.textContent = padZero(seconds);

            // Header text, rounded up: "7 Days" ... "1 Day", then hours on the last day
            if (daysRemainingEl) {
                const daysLeft = Math.ceil(distance / (1000 * 60 * 60 * 24));
                const hoursLeft = Math.ceil(distance / (1000 * 60 * 60));
                daysRemainingEl.textContent = daysLeft > 1
                    ? daysLeft + ' Days'
                    : hoursLeft + ' ' + (hoursLeft === 1 ? 'Hour' : 'Hours');
            }
        }

        tick(); // show the time straight away, not after the first second
        setInterval(tick, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startCountdown);
    } else {
        startCountdown();
    }
})();
