/**
 * Domain Registration page: filter the price table, and let "Register"
 * fill the search box with the chosen extension.
 */
(function () {
    var filter = document.getElementById('dr-filter-input');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.dr-row'));
    var noMatch = document.getElementById('dr-no-match');
    var search = document.getElementById('dr-search-input');

    if (filter) {
        filter.addEventListener('input', function () {
            var term = filter.value.trim().toLowerCase().replace(/^\.?/, '.');
            var shown = 0;

            rows.forEach(function (row) {
                var match = term === '.' || row.getAttribute('data-tld').indexOf(term) !== -1;
                row.hidden = !match;
                if (match) shown++;
            });

            if (noMatch) noMatch.hidden = shown !== 0;
        });
    }

    document.querySelectorAll('.dr-btn-buy').forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (!search) return;
            event.preventDefault();

            // "mybrand" or "mybrand.net" → "mybrand.com.bd"
            var name = search.value.trim().toLowerCase().split('.')[0];
            search.value = (name || '') + button.getAttribute('data-tld');
            search.scrollIntoView({ behavior: 'smooth', block: 'center' });
            search.focus({ preventScroll: true });

            if (name) {
                search.form.requestSubmit ? search.form.requestSubmit() : search.form.submit();
            } else {
                // No name yet: put the cursor before the extension so it can be typed.
                search.setSelectionRange(0, 0);
            }
        });
    });
})();
