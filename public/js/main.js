// E-BloodBank — Main JS

document.addEventListener('DOMContentLoaded', () => {

  // ── Auto-hide alerts ──────────────────────
  setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
      el.style.transition = 'opacity .5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 4000);

  // ── Sidebar toggle (mobile) ───────────────
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar   = document.querySelector('.sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  // ── Confirm delete dialogs ────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Yakin ingin melanjutkan?')) {
        e.preventDefault();
      }
    });
  });

  // ── Animate bar charts ────────────────────
  document.querySelectorAll('.bar-fill').forEach(bar => {
    const target = bar.dataset.width || '0';
    bar.style.width = '0';
    requestAnimationFrame(() => {
      setTimeout(() => { bar.style.width = target + '%'; }, 100);
    });
  });

  // ── Live stock level color ────────────────
  document.querySelectorAll('.blood-qty[data-qty][data-min]').forEach(el => {
    const qty = parseInt(el.dataset.qty);
    const min = parseInt(el.dataset.min);
    const card = el.closest('.blood-card');
    if (!card) return;
    if (qty <= 0)     card.classList.add('critical');
    else if (qty <= min) card.classList.add('critical');
    else if (qty <= min * 2) card.classList.add('low');
    else card.classList.add('safe');
  });

  // ── Role tabs on register ─────────────────
  const roleTabs = document.querySelectorAll('.auth-tab[data-role]');
  const roleInput = document.getElementById('role-input');
  const hospitalField = document.getElementById('hospital-field');
  const donorFields = document.getElementById('donor-fields');
  if (roleTabs.length) {
    roleTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        roleTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        if (roleInput) roleInput.value = tab.dataset.role;
        if (hospitalField) hospitalField.style.display = tab.dataset.role === 'rs' ? 'block' : 'none';
        if (donorFields)   donorFields.style.display   = tab.dataset.role === 'donor' ? 'block' : 'none';
      });
    });
  }

  // ── Emergency button pulse confirm ───────
  const emergencyBtn = document.querySelector('.emergency-btn');
  if (emergencyBtn) {
    emergencyBtn.addEventListener('click', e => {
      if (!confirm('⚠️ Kirim permintaan DARURAT? Pastikan ini benar-benar situasi emergency!')) {
        e.preventDefault();
      }
    });
  }

  // ── Print QR ticket ──────────────────────
  const printBtn = document.getElementById('print-ticket');
  if (printBtn) {
    printBtn.addEventListener('click', () => window.print());
  }

  // ── Search/filter tables ──────────────────
  const tableSearch = document.getElementById('table-search');
  const dataTable   = document.getElementById('data-table');
  if (tableSearch && dataTable) {
    tableSearch.addEventListener('input', () => {
      const q = tableSearch.value.toLowerCase();
      dataTable.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ── Countdown timer (eligible) ───────────
  const countEl = document.getElementById('eligibility-countdown');
  if (countEl) {
    const days = parseInt(countEl.dataset.days);
    countEl.textContent = days + ' hari lagi';
  }
});
