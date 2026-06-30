/* SITARA – sidebar controller
 * Desktop (>=992px): toggle collapses/expands the sidebar, state persisted.
 * Mobile  (<992px) : toggle opens/closes an overlay drawer.
 */
(function () {
    'use strict';

    var body = document.body;
    var mq = window.matchMedia('(max-width: 991.98px)');
    var toggle = document.getElementById('sidebarToggle');
    var backdrop = document.querySelector('.sidebar-backdrop');

    function isMobile() { return mq.matches; }

    function persist() {
        localStorage.setItem('sitara_sidebar',
            body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open');
    }

    function onToggle() {
        if (isMobile()) {
            body.classList.toggle('sidebar-open');
        } else {
            body.classList.toggle('sidebar-collapsed');
            persist();
        }
    }

    if (toggle) toggle.addEventListener('click', onToggle);
    if (backdrop) backdrop.addEventListener('click', function () {
        body.classList.remove('sidebar-open');
    });

    // Close the mobile drawer after tapping a menu link
    document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile()) body.classList.remove('sidebar-open');
        });
    });

    // Reset transient state when crossing the breakpoint
    var onChange = function () {
        body.classList.remove('sidebar-open');
        if (isMobile()) {
            body.classList.remove('sidebar-collapsed');
        } else if (localStorage.getItem('sitara_sidebar') === 'collapsed') {
            body.classList.add('sidebar-collapsed');
        }
    };
    if (mq.addEventListener) mq.addEventListener('change', onChange);
    else if (mq.addListener) mq.addListener(onChange); // older browsers

    // Esc closes the mobile drawer
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && body.classList.contains('sidebar-open')) {
            body.classList.remove('sidebar-open');
        }
    });
})();
