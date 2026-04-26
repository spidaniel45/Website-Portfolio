/* ============================================================
   script.js  –  Portfolio JavaScript
   Bootstrap 5 handles modals natively.
   This file handles: about-section toggle, section visibility,
   and any progressive enhancements not covered by Bootstrap.
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    console.log('%c✅ Portfolio loaded', 'color:#ff6b6b;font-weight:bold;');

    initAboutSection();
    initSectionNav();
});


/* ── About Section ──────────────────────────────────────────── */

function initAboutSection() {
    const box = document.querySelector('.About_Info');
    if (!box) return;

    // Keyboard accessibility: allow Enter/Space to toggle <details>
    box.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'SUMMARY' && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            const details = e.target.closest('details');
            if (details) details.open = !details.open;
        }
    });
}


/* ── Section Navigation ─────────────────────────────────────── */
/*
 * CSS :target handles show/hide of sections.
 * JS only adds smooth scroll and updates active nav link.
 */

function initSectionNav() {
    // Highlight the active nav item based on current hash
    const updateActiveNav = () => {
        const hash = window.location.hash;
        document.querySelectorAll('.top-nav li a').forEach(a => {
            a.classList.toggle('active-nav', a.getAttribute('href') === hash);
        });
    };

    window.addEventListener('hashchange', updateActiveNav);
    updateActiveNav();

    // Smooth scroll polyfill for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const targetId = anchor.getAttribute('href');
            if (!targetId || targetId === '#') return;

            const target = document.querySelector(targetId);
            if (target) {
                // Small delay allows :target CSS to apply first
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 50);
            }
        });
    });
}