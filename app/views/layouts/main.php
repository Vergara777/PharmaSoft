<?php use App\Core\View; use App\Helpers\Flash; use App\Helpers\Auth; $title = $title ?? APP_NAME; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title) ?> - <?= View::e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css">
  <style>
    /* Loading Overlay */
    .app-loading-overlay { position: fixed; inset: 0; background: rgba(17,24,39,.45); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); z-index: 4000; display: none; align-items: center; justify-content: center; }
    .app-loading-box { background: #0b1220; color: #e5e7eb; padding: 18px 22px; border-radius: 12px; box-shadow: 0 24px 60px rgba(0,0,0,.45); display: flex; gap: 14px; align-items: center; min-width: 280px; }
    .app-spinner { width: 30px; height: 30px; border: 3px solid rgba(255,255,255,.15); border-top-color: #3b82f6; border-radius: 50%; animation: spin .9s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    /* Loading overlay fade */
    .app-loading-overlay.fade-enter { opacity: 0; }
    .app-loading-overlay.fade-enter-active { opacity: 1; transition: opacity .18s ease-out; }
    .app-loading-overlay.fade-exit { opacity: 1; }
    .app-loading-overlay.fade-exit-active { opacity: 0; transition: opacity .18s ease-in; }
    /* Toasts */
    /* Force dark toast background and light text with high specificity */
    .swal2-container .swal2-popup.ps-toast { background: #0b1220 !important; color: #e5e7eb !important; border-radius: 14px; padding: 14px 16px; box-shadow: 0 28px 64px rgba(0,0,0,.5); width: 460px; border: 1px solid rgba(255,255,255,.06); }
    .swal2-container .swal2-popup.ps-toast .swal2-html-container { color: inherit !important; margin: 0 !important; }
    .swal2-popup.ps-toast .swal2-close { color: #e5e7eb; opacity: .7; }
    .swal2-popup.ps-toast .swal2-timer-progress-bar { height: 4px; background: #22c55e; border-radius: 0 0 12px 12px; }
    .ps-toast-body { display: grid; grid-template-columns: 46px 1fr; gap: 14px; align-items: flex-start; }
    .ps-toast-icon { width: 46px; height: 46px; border-radius: 11px; display: grid; place-items: center; color: #0b1220; font-size: 18px; box-shadow: inset 0 -2px 0 rgba(0,0,0,.12); }
    .ps-toast-title { font-weight: 900; color: #f9fafb; margin-top: 0; font-size: 15px; }
    .ps-toast-text { color: #f3f4f6; opacity: .98; margin-top: 4px; font-size: 14px; font-weight: 700; }
    .ps-toast.ps-success .ps-toast-icon { background: #86efac; }
    .ps-toast.ps-warning .ps-toast-icon { background: #fcd34d; }
    .ps-toast.ps-error .ps-toast-icon { background: #fca5a5; }
    .ps-toast.ps-info .ps-toast-icon { background: #93c5fd; }
    .ps-toast.ps-success .swal2-timer-progress-bar { background: #22c55e; }
    .ps-toast.ps-warning .swal2-timer-progress-bar { background: #f59e0b; }
    .ps-toast.ps-error .swal2-timer-progress-bar { background: #ef4444; }
    .ps-toast.ps-info .swal2-timer-progress-bar { background: #3b82f6; }
    /* Fallback toast container and item (when SweetAlert2 not available) */
    .ps-fallback-toasts { position: fixed; right: 16px; top: 16px; z-index: 4005; display: grid; gap: 10px; }
    .ps-fallback-toast { background: #0b1220; color: #e5e7eb; border-radius: 14px; padding: 14px 16px; box-shadow: 0 28px 64px rgba(0,0,0,.5); min-width: 320px; max-width: 480px; display: grid; grid-template-columns: 46px 1fr 18px; gap: 14px; align-items: start; border: 1px solid rgba(255,255,255,.06); }
    .ps-fallback-icon { width: 44px; height: 44px; border-radius: 10px; display: grid; place-items: center; color: #111827; font-size: 18px; box-shadow: inset 0 -2px 0 rgba(0,0,0,.12); }
    .ps-fallback-title { font-weight: 900; color: #f9fafb; margin-top: 2px; }
    .ps-fallback-text { color: #f3f4f6; opacity: .98; margin-top: 4px; font-weight: 700; }
    .ps-fallback-close { color: #9ca3af; cursor: pointer; }
    .ps-fallback-bar { height: 4px; border-radius: 0 0 12px 12px; background: #3b82f6; margin-top: 8px; transform-origin: left; }
    .ps-fb-success .ps-fallback-icon { background: #86efac; }
    .ps-fb-warning .ps-fallback-icon { background: #fcd34d; }
    .ps-fb-error   .ps-fallback-icon { background: #fca5a5; }
    .ps-fb-info    .ps-fallback-icon { background: #93c5fd; }
    .ps-fb-success .ps-fallback-bar { background: #22c55e; }
    .ps-fb-warning .ps-fallback-bar { background: #f59e0b; }
    .ps-fb-error   .ps-fallback-bar { background: #ef4444; }
    .ps-fb-info    .ps-fallback-bar { background: #3b82f6; }
    /* Reduced motion respect */
    @media (prefers-reduced-motion: reduce) {
      .app-spinner { animation: none; }
      .app-loading-overlay.fade-enter-active,
      .app-loading-overlay.fade-exit-active { transition: none; }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <?php $isTech = Auth::isTechnician(); $isAdmin = Auth::isAdmin(); ?>
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/dashboard" class="nav-link">Dashboard</a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/products" class="nav-link">Productos</a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/sales" class="nav-link">Ventas</a></li>
      <?php if ($isAdmin): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/users" class="nav-link">Usuarios</a></li>
      <?php endif; ?>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
      <?php
        $avatar = $_SESSION['user']['avatar'] ?? 'https://via.placeholder.com/40?text=U';
        $name = $_SESSION['user']['name'] ?? 'Usuario';
        if (strpos($avatar, 'http://') !== 0 && strpos($avatar, 'https://') !== 0) { $avatar = BASE_URL . '/' . ltrim($avatar, '/'); }
        $avver = isset($_SESSION['user']['avatar_ver']) ? (int)$_SESSION['user']['avatar_ver'] : 0;
        $avatar_q = $avatar . ($avver ? (strpos($avatar, '?') === false ? ('?v=' . $avver) : ('&v=' . $avver)) : '');
      ?>
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
          <img src="<?= View::e($avatar_q) ?>" class="user-image img-circle elevation-2" alt="Avatar" style="object-fit:cover; width:32px; height:32px;">
          <span class="d-none d-md-inline"><?= View::e($name) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <li class="user-header bg-primary">
            <img src="<?= View::e($avatar_q) ?>" class="img-circle elevation-2" alt="User Image" style="object-fit:cover; width:80px; height:80px;">
            <p>
              <?= View::e($name) ?><br>
              <small><?= View::e($_SESSION['user']['email'] ?? '') ?></small>
            </p>
          </li>
          <li class="user-footer d-flex justify-content-between">
            <a href="<?= BASE_URL ?>/profile" class="btn btn-default btn-flat">Perfil</a>
            <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-default btn-flat">Salir</a>
          </li>
        </ul>
      </li>
    </ul>
  </nav>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <i class="fas fa-capsules text-primary mr-2" aria-hidden="true"></i>
      <span class="brand-text font-weight-light">PharmaSoft</span>
    </a>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?= View::e($avatar_q) ?>" class="img-circle elevation-2" alt="User Image" style="object-fit:cover; width:34px; height:34px;">
        </div>
        <div class="info">
          <a href="<?= BASE_URL ?>/profile" class="d-block"><?= View::e($name) ?></a>
        </div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
          <li class="nav-item"><a href="<?= BASE_URL ?>/dashboard" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="<?= BASE_URL ?>/products" class="nav-link"><i class="nav-icon fas fa-pills"></i><p>Productos</p></a></li>
          <li class="nav-item"><a href="<?= BASE_URL ?>/sales" class="nav-link"><i class="nav-icon fas fa-cash-register"></i><p>Ventas</p></a></li>
          <?php if ($isAdmin): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/users" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Usuarios</p></a></li>
          <?php endif; ?>
          <li class="nav-item"><a href="<?= BASE_URL ?>/profile" class="nav-link"><i class="nav-icon fas fa-user"></i><p>Perfil</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>
  <div class="content-wrapper">
    <section class="content pt-3">
      <div class="container-fluid">
        <?php include $viewFile; ?>
      </div>
    </section>
  </div>
  <footer class="main-footer small"><strong>&copy; <?= date('Y') ?> PharmaSoft</strong></footer>
</div>
<!-- Loading Overlay -->
<div class="app-loading-overlay" id="appLoadingOverlay" aria-hidden="true">
  <div class="app-loading-box">
    <div class="app-spinner" aria-label="Cargando"></div>
    <div>
      <div class="font-weight-bold mb-0" id="appLoadingText">Procesando...</div>
      <small class="text-muted">Por favor, espera</small>
    </div>
  </div>
 </div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
<script src="<?= BASE_URL ?>/js/confirm-modal.js?v=20250824"></script>
<script>
  // SweetAlert2 Toast (enhanced) if available
  const Toast = (window.Swal ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4200,
    timerProgressBar: true,
    showCloseButton: true,
    customClass: { container: 'ps-toast-container' },
    didOpen: (el) => {
      el.setAttribute('aria-live', 'assertive');
      el.addEventListener('mouseenter', Swal.stopTimer)
      el.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }) : null);
  // notify can be called as notify(type, message) or notify(options)
  // DOM-based fallback toast
  function domToast(options) {
    const type = options.icon || 'info';
    const title = options.title || '';
    const text = options.text || options.html || '';
    const map = { success: 'ps-fb-success', error: 'ps-fb-error', warning: 'ps-fb-warning', info: 'ps-fb-info', question: 'ps-fb-info' };
    const iconMap = { success: 'fa-check', error: 'fa-times', warning: 'fa-exclamation', info: 'fa-info', question: 'fa-question' };
    const cls = map[type] || 'ps-fb-info';
    let container = document.querySelector('.ps-fallback-toasts');
    if (!container) { container = document.createElement('div'); container.className = 'ps-fallback-toasts'; document.body.appendChild(container); }
    if (options.position && options.position.includes('bottom')) {
      container.style.top = 'auto'; container.style.bottom = '16px';
    } else { container.style.top = '16px'; container.style.bottom = 'auto'; }
    if (options.position && options.position.includes('left')) {
      container.style.right = 'auto'; container.style.left = '16px';
    } else { container.style.right = '16px'; container.style.left = 'auto'; }
    const el = document.createElement('div'); el.className = 'ps-fallback-toast ' + cls;
    el.innerHTML = `
      <div class="ps-fallback-icon"><i class="fas ${iconMap[type]||'fa-info'}"></i></div>
      <div>
        ${title ? `<div class="ps-fallback-title">${title}</div>` : ''}
        ${text ? `<div class="ps-fallback-text">${text}</div>` : ''}
        <div class="ps-fallback-bar"></div>
      </div>
      <div class="ps-fallback-close">&times;</div>`;
    container.appendChild(el);
    const bar = el.querySelector('.ps-fallback-bar');
    const close = el.querySelector('.ps-fallback-close');
    close.addEventListener('click', () => { container.removeChild(el); });
    // animate progress bar
    const ttl = Math.max(2000, options.timer || 4000);
    bar.style.transform = 'scaleX(1)';
    bar.style.transition = `transform ${ttl}ms linear`;
    requestAnimationFrame(() => { bar.style.transform = 'scaleX(0)'; });
    // auto hide
    setTimeout(() => { if (el.parentNode) el.parentNode.removeChild(el); }, ttl + 100);
  }

  window.notify = function(arg1, arg2) {
    const types = ['success','error','warning','info','question'];
    let options = {};
    if (typeof arg1 === 'object') { options = arg1 || {}; }
    else { options = { icon: types.includes(arg1) ? arg1 : 'info', title: arg2 || String(arg1) }; }
    const typesMap = {
      success: { cls: 'ps-success', icon: 'fa-check' },
      error:   { cls: 'ps-error',   icon: 'fa-times' },
      warning: { cls: 'ps-warning', icon: 'fa-exclamation' },
      info:    { cls: 'ps-info',    icon: 'fa-info' },
      question:{ cls: 'ps-info',    icon: 'fa-question' }
    };
    const type = (options.icon && ['success','error','warning','info','question'].includes(options.icon)) ? options.icon : 'info';
    const map = typesMap[type];
    const title = options.title || '';
    const text = options.text || options.html || '';
    const html = `
      <div class="ps-toast-body">
        <div class="ps-toast-icon"><i class="fas ${map.icon}"></i></div>
        <div>
          ${title ? `<div class=\"ps-toast-title\">${title}</div>` : ''}
          ${text ? `<div class=\"ps-toast-text\">${text}</div>` : ''}
        </div>
      </div>`;
    if (window.Swal && Toast) {
      const defaults = { icon: undefined, html, customClass: { popup: `ps-toast ${map.cls}` } };
      const pos = options.position || 'top-end';
      // Remove built-in title/text/icon to avoid duplicates and low-contrast defaults
      const clean = Object.assign({}, options);
      delete clean.title; delete clean.text; delete clean.html; delete clean.icon;
      // Enforce high-contrast colors
      clean.background = '#0b1220';
      clean.color = '#e5e7eb';
      Toast.fire(Object.assign({}, defaults, { position: pos }, clean));
    } else {
      domToast({ icon: type, title, text, timer: options.timer, position: options.position });
    }
  }
  window.notifySuccess = (msg) => notify('success', msg);
  window.notifyError = (msg) => notify('error', msg);

  // Signal readiness and allow debug via ?debug_toast=1
  window.__psNotifyReady = true;
  try { console.debug('[PharmaSoft] notify() ready:', typeof window.notify); } catch(e){}
  (function(){
    const p = new URLSearchParams(window.location.search);
    if (p.has('debug_toast')) {
      notify({ icon:'warning', title:'Debug Toast', text:'notify() está funcionando.' });
    }
  })();

  // Generic confirmation helper
  window.confirmAction = function(opts = {}) {
    const {
      title = '¿Estás seguro?',
      text = 'Esta acción no se puede deshacer.',
      icon = 'warning',
      confirmText = 'Sí, continuar',
      cancelText = 'Cancelar',
      confirmButtonColor = '#3c8dbc',
      cancelButtonColor = '#6c757d'
    } = opts;
    return Swal.fire({ title, text, icon, showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: cancelText, confirmButtonColor, cancelButtonColor }).then(r => r.isConfirmed);
  }

  // Loading Overlay API
  const overlay = document.getElementById('appLoadingOverlay');
  const overlayText = document.getElementById('appLoadingText');
  window.bannerLoading = function(show, text) {
    if (typeof text === 'string' && text) overlayText.textContent = text; else overlayText.textContent = 'Procesando...';
    overlay.style.display = show ? 'flex' : 'none';
    overlay.setAttribute('aria-hidden', show ? 'false' : 'true');
  }

  // Auto-show on form submit (supports data-loading-text on form or submit button)
  $(document).on('submit', 'form', function(e){
    const btn = this.querySelector('button[type="submit"][data-loading-text], input[type="submit"][data-loading-text]');
    const text = (btn && btn.getAttribute('data-loading-text')) || this.getAttribute('data-loading-text') || 'Enviando datos...';
    bannerLoading(true, text);
  });
  // Auto-show during jQuery AJAX
  $(document).ajaxStart(function(){ bannerLoading(true, 'Cargando...'); });
  $(document).ajaxStop(function(){ bannerLoading(false); });
  // Notify on AJAX errors
  $(document).ajaxError(function(_evt, jqxhr, settings, err){
    try {
      const status = jqxhr && jqxhr.status ? ` [${jqxhr.status}]` : '';
      const url = (settings && settings.url) ? settings.url : '';
      const msg = (jqxhr && jqxhr.responseText) ? String(jqxhr.responseText).slice(0, 200) : (err || 'Error de red');
      notify({ icon: 'error', title: 'Error AJAX' + status, text: (url ? url + ': ' : '') + msg });
    } catch(e){}
  });

  // Auto-show during native fetch as well
  (function(){
    if (!window.fetch) return;
    const _fetch = window.fetch.bind(window);
    window.fetch = function(input, init) {
      try { bannerLoading(true, 'Cargando...'); } catch(e){}
      return _fetch(input, init).finally(() => { try { bannerLoading(false); } catch(e){} });
    }
  })();

  // Global JS error handlers
  window.addEventListener('error', function (e) {
    try { notify({ icon: 'error', title: 'Error de JavaScript', text: (e.message || 'Error desconocido') }); } catch(_){}
  });
  window.addEventListener('unhandledrejection', function (e) {
    try {
      const reason = e && e.reason ? (e.reason.message || e.reason) : 'Promesa rechazada sin manejar';
      notify({ icon: 'error', title: 'Error en promesa', text: String(reason) });
    } catch(_){}
  });

  // Read ?success= / ?error=
  (function(){
    const p = new URLSearchParams(window.location.search);
    if (p.has('success')) { notify('success', p.get('success')); }
    if (p.has('error')) { notify('error', p.get('error')); }
    if (p.has('success') || p.has('error')) {
      const url = new URL(window.location.href);
      url.searchParams.delete('success');
      url.searchParams.delete('error');
      window.history.replaceState({}, document.title, url.toString());
    }
  })();

  // Global delete confirmation
  function confirmDelete(message) {
    return confirmAction({ text: message || 'Esta acción no se puede deshacer.', icon: 'warning', confirmText: 'Sí, eliminar', confirmButtonColor: '#d33' });
  }

  // Intercept only forms explicitly marked with data-confirm (shared script handles js-confirmable)
  $(document).on('submit', 'form', async function(e){
    const form = this;
    if (form.dataset.skipConfirm === '1') return; // allow opt-out
    if (form.classList && form.classList.contains('js-confirmable')) return; // handled by shared script
    const needsConfirm = form.hasAttribute('data-confirm');
    if (!needsConfirm) return;
    e.preventDefault();
    const msg = form.getAttribute('data-confirm') || 'Eliminar el registro seleccionado';
    const ok = await confirmDelete(msg);
    if (ok) {
      // Guard to avoid re-interception
      form.dataset.skipConfirm = '1';
      try {
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
      } finally {
        // Remove guard shortly after to not affect subsequent submits of other forms
        setTimeout(() => { try { delete form.dataset.skipConfirm; } catch(_){} }, 300);
      }
    } else {
      bannerLoading(false);
    }
  });

  // Buttons/links with data-confirm inside forms
  $(document).on('click', '[data-confirm]', async function(e){
    const el = this;
    const form = el.closest('form');
    const msg = el.getAttribute('data-confirm') || undefined;
    if (form) {
      e.preventDefault();
      const ok = await confirmDelete(msg);
      if (ok) form.submit();
    } else if (el.tagName === 'A') {
      e.preventDefault();
      const ok = await confirmAction({ text: msg || 'Continuar con la acción' });
      if (ok) window.location.href = el.getAttribute('href');
    }
  });
</script>
<?php $___fl = Flash::popAll(); if (!empty($___fl)): ?>
<script>
  (function(){
    const msgs = <?php echo json_encode($___fl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    msgs.forEach(m => notify({ icon: m.type || 'info', title: m.title || '', text: m.message || '', timer: (m.timer && m.timer > 0) ? m.timer : undefined }));
  })();
</script>
<?php endif; ?>
</body>
</html>
