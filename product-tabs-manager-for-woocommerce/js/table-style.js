(function () {
    'use strict';

    function prepareTables() {
        document.querySelectorAll('.br-tab-table-style table').forEach(function (table) {
            // Keep nested tables inside their original cells; do not wrap twice.
            if (table.parentElement.closest('table, .br-tab-table-scroll')) {
                return;
            }
            var content = table.closest('.br-tab-table-style');
            var caption = table.querySelector('caption');
            var scroll = document.createElement('div');
            scroll.className = 'br-tab-table-scroll';
            scroll.tabIndex = 0;
            scroll.setAttribute('role', 'region');
            scroll.setAttribute('aria-label', caption && caption.textContent.trim()
                ? caption.textContent.trim()
                : content.getAttribute('data-scroll-label'));
            table.parentNode.insertBefore(scroll, table);
            scroll.appendChild(table);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prepareTables);
    } else {
        prepareTables();
    }
}());
