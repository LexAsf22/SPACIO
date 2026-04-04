/* ═══════════════════════════════════════════════════════════
   SPACIO — Shared App JavaScript
   ═══════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ── Live clock in topbar ── */
  function updateClock() {
    const el = document.getElementById('topbar-clock');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', {
      hour: '2-digit', minute: '2-digit', hour12: true
    });
  }

  updateClock();
  setInterval(updateClock, 1000);

  /* ── Flash message auto-dismiss & close button ── */
  document.querySelectorAll('.flash').forEach(function (flash) {
    // Close button
    const closeBtn = flash.querySelector('.flash-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        dismissFlash(flash);
      });
    }
    // Auto-dismiss after 5 seconds
    setTimeout(function () {
      dismissFlash(flash);
    }, 5000);
  });

  function dismissFlash(el) {
    el.style.transition = 'opacity .4s ease, transform .4s ease, max-height .4s ease, margin .4s ease, padding .4s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-8px)';
    el.style.maxHeight = el.offsetHeight + 'px';
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        el.style.maxHeight = '0';
        el.style.margin = '0';
        el.style.padding = '0';
        el.style.overflow = 'hidden';
      });
    });
    setTimeout(function () { el.remove(); }, 500);
  }

  /* ── Mobile sidebar toggle ── */
  const menuBtn  = document.getElementById('mobile-menu-btn');
  const sidebar  = document.querySelector('.sidebar');
  const overlay  = document.getElementById('sidebar-overlay');

  if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('active');
    });
  }

  if (overlay) {
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }

  /* ── Active nav item detection ── */
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-item').forEach(function (link) {
    const href = link.getAttribute('href') || '';
    if (href && currentPath.endsWith(href.split('/').pop())) {
      link.classList.add('active');
    }
  });

  /* ── Stat card count-up animation ── */
  function animateCountUp(el) {
    const target = parseFloat(el.dataset.value || el.textContent);
    if (isNaN(target)) return;
    const duration = 900;
    const start    = performance.now();
    const isFloat  = String(target).includes('.');
    const decimals = isFloat ? (String(target).split('.')[1] || '').length : 0;

    function tick(now) {
      const elapsed  = now - start;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out expo
      const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
      const current = target * eased;
      el.textContent = isFloat ? current.toFixed(decimals) : Math.round(current).toLocaleString();
      if (progress < 1) requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
  }

  /* ── Intersection observer for stat cards ── */
  const statObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        const valueEl = entry.target.querySelector('[data-count]');
        if (valueEl) animateCountUp(valueEl);
        statObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  document.querySelectorAll('.stat-card').forEach(function (card) {
    statObserver.observe(card);
  });

  /* ── Scroll reveal (reusable across all pages) ── */
  const revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.08 });

  document.querySelectorAll('.reveal').forEach(function (el) {
    revealObserver.observe(el);
  });

})();