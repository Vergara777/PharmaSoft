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
    /* Top loading bar */
    .ps-topbar { position: fixed; top: 0; left: 0; height: 3px; background: #3c8dbc; width: 0; opacity: 0; z-index: 5000; transition: width .3s ease, opacity .2s ease; }
    /* Compact mini loader */
    .ps-mini-loader { position: fixed; right: 16px; bottom: 16px; z-index: 4006; background: #0b1220; color: #e5e7eb; padding: 8px 12px; border-radius: 10px; display: none; align-items: center; gap: 8px; box-shadow: 0 16px 40px rgba(0,0,0,.35); border: 1px solid rgba(255,255,255,.06); }
    .ps-mini-spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.15); border-top-color: #3b82f6; border-radius: 50%; animation: spin .9s linear infinite; }
    /* Reduced motion respect */
    @media (prefers-reduced-motion: reduce) {
      .app-spinner { animation: none; }
      .app-loading-overlay.fade-enter-active,
      .app-loading-overlay.fade-exit-active { transition: none; }
    }
    /* Floating cart button */
    .cart-fab { position: fixed; right: 20px; bottom: 20px; z-index: 3045; border: none; border-radius: 50%; width: 56px; height: 56px; background: #3c8dbc; color: #fff; box-shadow: 0 6px 16px rgba(0,0,0,.25); display: inline-flex; align-items: center; justify-content: center; }
    .cart-fab:hover { background: #357ea8; }
    .cart-fab .fa-shopping-cart { font-size: 1.25rem; }
    .cart-fab-badge { position: absolute; top: -6px; right: -6px; border-radius: 10px; font-weight: 700; }
    /* Lightweight modal (global) */
    .ps-modal { position: fixed; inset: 0; z-index: 3050; display: none; }
    .ps-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.45); }
    .ps-modal-dialog { position: relative; background: #fff; z-index: 1; max-width: 860px; width: 92%; margin: 40px auto; border-radius: 8px; box-shadow: 0 16px 40px rgba(0,0,0,.3); display: flex; flex-direction: column; max-height: calc(100vh - 80px); }
    .ps-modal-header { padding: 12px 16px; border-bottom: 1px solid #eee; display: flex; align-items: center; position: relative; }
    .ps-modal-body { padding: 0; max-height: calc(100vh - 190px); overflow: auto; }
    .ps-modal-footer { padding: 10px 16px; border-top: 1px solid #eee; background: #fafafa; }
    .ps-modal .close { background: transparent; border: 0; font-size: 1.6rem; line-height: 1; color: #333; position: absolute; right: 10px; top: 8px; padding: 4px 8px; opacity: .8; }
    .ps-modal .close:hover { opacity: 1; }
    @media (max-width: 576px) {
      .ps-modal-dialog { width: 96%; margin: 10px auto; max-height: calc(100vh - 20px); }
      .ps-modal-body { max-height: calc(100vh - 170px); }
    }
    /* Disable pointer events on app while busy (overlay active) */
    body.app-busy #content, body.app-busy main, body.app-busy .wrapper { pointer-events: none; }
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
<!-- Global Floating Cart Button and Modal -->
<button id="globalCartFab" class="cart-fab" title="Ver carrito" aria-label="Ver carrito">
  <i class="fas fa-shopping-cart" aria-hidden="true"></i>
  <span class="badge badge-danger cart-fab-badge" id="globalCartCount" style="display:none;">0</span>
  <span class="sr-only">Carrito</span>
</button>

<div id="globalCartModal" class="ps-modal" style="display:none;">
  <div class="ps-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="globalCartModalTitle">
    <div class="ps-modal-header">
      <h5 id="globalCartModalTitle" class="mb-0 d-flex align-items-center">
        <i class="fas fa-shopping-cart mr-2 text-primary" aria-hidden="true"></i>
        Carrito
        <span class="badge badge-secondary ml-2" id="globalCartModalCount">0</span>
      </h5>
      <button type="button" class="close" id="globalCartModalClose" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="ps-modal-body" id="globalCartModalBody">
      <div class="text-muted p-3">No hay borrador de carrito en este navegador.</div>
    </div>
    <div class="ps-modal-footer d-flex justify-content-between align-items-center" id="globalCartModalFooter" style="display:none;">
      <div class="text-muted small">Borrador guardado localmente.</div>
      <div class="d-flex align-items-center" style="gap:.5rem;">
        <div class="mr-3"><strong>Total:</strong> <span id="globalCartModalTotal">$0</span></div>
        <a id="globalCartGoProducts" href="<?= BASE_URL ?>/products" class="btn btn-outline-secondary btn-sm"><i class="fas fa-pills mr-1"></i> Ir a productos</a>
        <a id="globalCartGoCreateSale" href="<?= BASE_URL ?>/sales/create" class="btn btn-outline-primary btn-sm"><i class="fas fa-cash-register mr-1"></i> Ir a realizar compra</a>
        <button type="button" class="btn btn-outline-danger btn-sm" id="globalCartModalClear"><i class="fas fa-trash mr-1"></i> Vaciar</button>
      </div>
    </div>
  </div>
  <div class="ps-modal-backdrop" id="globalCartModalBackdrop"></div>
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
<!-- Top Loading Bar + Mini Loader -->
<div id="psTopBar" class="ps-topbar" aria-hidden="true"></div>
<div id="psMiniLoader" class="ps-mini-loader" role="status" aria-live="polite">
  <div class="ps-mini-spinner" aria-hidden="true"></div>
  <span id="psMiniLoaderText">Cargando...</span>
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
        ${options.sticky ? '' : '<div class="ps-fallback-bar"></div>'}
      </div>
      <div class="ps-fallback-close">&times;</div>`;
    container.appendChild(el);
    const bar = el.querySelector('.ps-fallback-bar');
    const close = el.querySelector('.ps-fallback-close');
    close.addEventListener('click', () => { container.removeChild(el); });
    if (!options.sticky) {
      // animate progress bar
      const ttl = Math.max(2000, options.timer || 4000);
      bar.style.transform = 'scaleX(1)';
      bar.style.transition = `transform ${ttl}ms linear`;
      requestAnimationFrame(() => { bar.style.transform = 'scaleX(0)'; });
      // auto hide
      setTimeout(() => { if (el.parentNode) el.parentNode.removeChild(el); }, ttl + 120);
    }
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
      if (options.sticky) { clean.timer = undefined; clean.showCloseButton = true; }
      Toast.fire(Object.assign({}, defaults, { position: pos }, clean));
    } else {
      domToast({ icon: type, title, text, timer: options.timer, position: options.position, sticky: !!options.sticky });
    }
  }
  window.notifySuccess = (msg) => notify('success', msg);
  window.notifyError = (msg) => notify('error', msg);
  // Sticky helpers
  window.notifySticky = function(icon, title, text){
    notify({ icon: icon || 'info', title: title || '', text: text || '', sticky: true });
  }
  window.notifyExpiry = function(text){
    notify({ icon: 'error', title: 'Alerta de vencimiento', text: text || '', sticky: true });
  }

  // Signal readiness and allow debug via ?debug_toast=1
  window.__psNotifyReady = true;
  try { console.debug('[PharmaSoft] notify() ready:', typeof window.notify); } catch(e){}
  (function(){
    const p = new URLSearchParams(window.location.search);
    if (p.has('debug_toast')) {
      notify({ icon:'warning', title:'Debug Toast', text:'notify() está funcionando.' });
    }
  })();

  // Global navigation loading (all internal links)
  (function(){
    function sameOrigin(href){
      try { var u=new URL(href, window.location.origin); return u.origin === window.location.origin; } catch(e){ return false; }
    }
    $(document).on('click', 'a[href]', function(e){
      var href = this.getAttribute('href'); if (!href) return;
      if (href.startsWith('#') || href.startsWith('javascript:')) return;
      if (this.target && this.target === '_blank') return;
      if (!sameOrigin(href)) return;
      try { if (window.loadingBar) window.loadingBar.start('Cargando...'); } catch(_){ }
      // allow navigation to proceed normally
    });
    window.addEventListener('beforeunload', function(){ try { if (window.loadingBar) window.loadingBar.start('Cargando...'); } catch(_){ } });
  })();

  // Global Cart (floating) available on all pages
  (function globalCart(){
    var KEY = 'pharmasoft_sales_draft';
    var LEGACY = 'pharmasoft_pending_cart';
    var fab = document.getElementById('globalCartFab');
    var fabCount = document.getElementById('globalCartCount');
    var modal = document.getElementById('globalCartModal');
    var mBody = document.getElementById('globalCartModalBody');
    var mFooter = document.getElementById('globalCartModalFooter');
    var mCount = document.getElementById('globalCartModalCount');
    var mTotal = document.getElementById('globalCartModalTotal');
    var mClose = document.getElementById('globalCartModalClose');
    var mBackdrop = document.getElementById('globalCartModalBackdrop');
    var mClear = document.getElementById('globalCartModalClear');
    var btnGoProducts = document.getElementById('globalCartGoProducts');
    var btnGoCreateSale = document.getElementById('globalCartGoCreateSale');
    function isOnProducts(){
      try { var p=(window.location.pathname||'').toLowerCase(); return p.indexOf('/products') !== -1; } catch(e){ return false; }
    }
    function isOnCreateSale(){
      try { var p=(window.location.pathname||'').toLowerCase(); return p.indexOf('/sales/create') !== -1; } catch(e){ return false; }
    }

    function fmt(n){ try { return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0}).format(n||0); } catch(e){ var v=Math.round(n||0); return '$'+String(v).replace(/\B(?=(\d{3})+(?!\d))/g,'.'); } }
    function migrate(){ try { var old = localStorage.getItem(LEGACY); if (old && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, old); localStorage.removeItem(LEGACY); } } catch(_){} }
    function read(){ migrate(); try { var raw = localStorage.getItem(KEY); var arr = raw ? JSON.parse(raw||'[]')||[] : []; return Array.isArray(arr)?arr:[]; } catch(_){ return []; } }
    function write(arr){ try { if (arr && arr.length) localStorage.setItem(KEY, JSON.stringify(arr)); else localStorage.removeItem(KEY); } catch(_){} }
    function total(arr){ var t=0; (arr||[]).forEach(function(it){ var q=parseInt(it.qty||0,10)||0; var p=Math.round(parseFloat(it.unit_price||0)||0); t+=q*p; }); return t; }
    function render(){
      var items = read();
      var cnt = items.length;
      if (fabCount){ fabCount.style.display = cnt>0?'inline-block':'none'; fabCount.textContent = String(cnt); }
      if (mCount){ mCount.textContent = String(cnt); }
      // Toggle footer CTAs depending on location
      try {
        if (btnGoProducts) btnGoProducts.style.display = isOnProducts() ? 'none' : '';
        if (btnGoCreateSale) btnGoCreateSale.style.display = isOnCreateSale() ? 'none' : '';
      } catch(_){ }
      if (!cnt){
        var emptyHtml = '<div class="p-4 text-center">\
          <div class="text-muted mb-3">No hay borrador de carrito.</div>';
        if (!isOnProducts()) {
          emptyHtml += ' <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-primary"><i class="fas fa-pills mr-1"></i> Ir a productos</a>';
        }
        emptyHtml += '</div>';
        if (mBody) mBody.innerHTML = emptyHtml;
        if (mFooter) mFooter.style.display = 'none';
        if (mTotal) mTotal.textContent = fmt(0);
        return;
      }
      var html = '\n<div class="table-responsive">\n<table class="table table-sm table-hover mb-0">\n<thead><tr><th>#</th><th>SKU</th><th>Producto</th><th class="text-right">Cant.</th><th class="text-right">P. Unit</th><th class="text-right">Importe</th><th></th></tr></thead><tbody>';
      for (var i=0;i<items.length;i++){
        var it = items[i]||{}; var idx=i+1;
        var img = it.image ? '<?= BASE_URL ?>/uploads/'+it.image : '';
        var name = (it.name||'').replace(/</g,'&lt;');
        var sku = (it.sku||'').replace(/</g,'&lt;');
        var q = Math.max(1, parseInt(it.qty||1,10)||1);
        var p = Math.max(0, Math.round(parseFloat(it.unit_price||0)||0));
        var imp = q*p;
        html += '<tr data-i="'+i+'">'
          + '<td>'+idx+'</td>'
          + '<td>'+sku+'</td>'
          + '<td>' + (img?('<img src="'+img+'" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:4px;border:1px solid #eee;margin-right:8px;vertical-align:middle;">'):'') + name + '</td>'
          + '<td class="text-right">'+q+'</td>'
          + '<td class="text-right">'+fmt(p)+'</td>'
          + '<td class="text-right">'+fmt(imp)+'</td>'
          + '<td class="text-right"><button type="button" class="btn btn-sm btn-outline-danger btnRemoveItem" title="Quitar"><i class="fas fa-trash"></i></button></td>'
          + '</tr>';
      }
      html += '</tbody></table></div>';
      if (mBody) mBody.innerHTML = html;
      if (mFooter) mFooter.style.display = '';
      if (mTotal) mTotal.textContent = fmt(total(items));
      var btns = mBody ? mBody.querySelectorAll('.btnRemoveItem') : [];
      if (btns && btns.forEach){ btns.forEach(function(b){ b.addEventListener('click', function(){
        try { if (window.loadingBar) window.loadingBar.start('Quitando...'); } catch(_){ }
        var tr=b.closest('tr'); var idx = tr ? parseInt(tr.getAttribute('data-i')||'-1',10) : -1; var arr = read();
        if (idx>=0 && idx < arr.length){ arr.splice(idx,1); write(arr); render(); }
        try { if (window.loadingBar) window.loadingBar.stop(); } catch(_){ }
      }); }); }
    }
    function open(){ if (!modal) return; render(); modal.style.display='block'; document.body.style.overflow='hidden'; }
    function close(){ if (!modal) return; modal.style.display='none'; document.body.style.overflow=''; }
    if (fab) fab.addEventListener('click', function(e){ e.preventDefault(); open(); });
    if (mClose) mClose.addEventListener('click', function(){ close(); });
    if (mBackdrop) mBackdrop.addEventListener('click', function(e){ if (e.target===mBackdrop) close(); });
    if (mClear) mClear.addEventListener('click', function(){
      try {
        if (window.Swal && Swal.fire) {
          return Swal.fire({ title:'Vaciar carrito', text:'¿Desea vaciar el borrador del carrito?', icon:'warning', showCancelButton:true, confirmButtonText:'Sí, vaciar', cancelButtonText:'No' }).then(function(res){ if (res && res.isConfirmed) { write([]); render(); } });
        }
      } catch(_){}
      if (confirm('¿Desea vaciar el borrador del carrito?')) { write([]); render(); }
    });
    // expose for live control from any page
    window.psCart = window.psCart || {};
    window.psCart.refresh = render;
    window.psCart.clear = function(){ try { write([]); render(); } catch(_){ render(); } };
    // initial badge update
    render();
    // Ensure initial footer CTA visibility per location
    try {
      if (btnGoProducts) btnGoProducts.style.display = isOnProducts() ? 'none' : '';
      if (btnGoCreateSale) btnGoCreateSale.style.display = isOnCreateSale() ? 'none' : '';
    } catch(_){ }
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

  // Loading Overlay API (with minimum visible duration)
  const overlay = document.getElementById('appLoadingOverlay');
  const overlayText = document.getElementById('appLoadingText');
  let __olMin = 3500; // ms (preferencia UX 3–4s)
  let __olStartedAt = 0;
  let __olHideTimer = null;
  let __olActive = false;
  function __olDoHide(){
    overlay.style.display = 'none';
    overlay.setAttribute('aria-hidden', 'true');
    try { document.body.style.overflow = ''; } catch(_){}
    try { document.body.classList.remove('app-busy'); } catch(_){}
    try { document.body.setAttribute('aria-busy','false'); } catch(_){}
    __olActive = false; __olStartedAt = 0; __olHideTimer = null;
  }
  window.bannerLoading = function(show, text) {
    if (typeof text === 'string' && text) overlayText.textContent = text; else overlayText.textContent = 'Procesando...';
    if (show) {
      if (__olHideTimer) { try { clearTimeout(__olHideTimer); } catch(_){} __olHideTimer = null; }
      // Ensure overlay fully covers and blocks interaction
      try { overlay.style.position = 'fixed'; overlay.style.inset = '0'; overlay.style.zIndex = '2050'; } catch(_){ }
      overlay.style.display = 'flex';
      overlay.setAttribute('aria-hidden', 'false');
      try { document.body.style.overflow = 'hidden'; } catch(_){}
      try { document.body.classList.add('app-busy'); } catch(_){}
      try { document.body.setAttribute('aria-busy','true'); } catch(_){}
      if (!__olActive) { __olActive = true; __olStartedAt = Date.now(); }
    } else {
      if (!__olActive) { __olDoHide(); return; }
      const elapsed = Math.max(0, Date.now() - __olStartedAt);
      const remaining = Math.max(0, __olMin - elapsed);
      if (remaining > 0) {
        if (__olHideTimer) { try { clearTimeout(__olHideTimer); } catch(_){} }
        __olHideTimer = setTimeout(__olDoHide, remaining);
      } else {
        __olDoHide();
      }
    }
  }
  // Allow runtime adjustment: window.bannerLoadingMinDuration = 4000
  try {
    Object.defineProperty(window, 'bannerLoadingMinDuration', {
      get: function(){ return __olMin; },
      set: function(v){ __olMin = Math.max(0, parseInt(v, 10) || 0); }
    });
  } catch(_){ }

  // Top bar + mini loader API (with minimum visible duration)
  const topBar = document.getElementById('psTopBar');
  const miniLoader = document.getElementById('psMiniLoader');
  const miniLoaderText = document.getElementById('psMiniLoaderText');
  let __lbMin = 6000; // ms, keep loader visible at least 6s
  let __lbStartedAt = 0;
  let __lbHideTimer = null;
  let __lbActive = false;
  function __lbDoHide(){
    try { if (miniLoader) miniLoader.style.display = 'none'; } catch(_){ }
    if (!topBar) { __lbActive = false; __lbStartedAt = 0; __lbHideTimer = null; return; }
    topBar.style.width = '100%';
    setTimeout(function(){
      topBar.style.opacity = '0';
      topBar.style.width = '0';
      __lbActive = false;
      __lbStartedAt = 0;
      __lbHideTimer = null;
    }, 250);
  }
  window.loadingBar = {
    start: function(text){
      // Clear any pending hide to extend duration when new operation starts
      if (__lbHideTimer) { try { clearTimeout(__lbHideTimer); } catch(_){} __lbHideTimer = null; }
      if (miniLoaderText && text) miniLoaderText.textContent = text;
      if (miniLoader) miniLoader.style.display = 'flex';
      if (!__lbActive) {
        __lbActive = true;
        __lbStartedAt = Date.now();
        if (topBar) {
          topBar.style.opacity = '1';
          topBar.style.width = '15%';
          requestAnimationFrame(function(){ topBar.style.width = '70%'; });
        }
      }
    },
    stop: function(){
      if (!__lbActive) return; // nothing to hide
      const elapsed = Math.max(0, Date.now() - __lbStartedAt);
      const remaining = Math.max(0, __lbMin - elapsed);
      if (remaining > 0) {
        if (__lbHideTimer) { try { clearTimeout(__lbHideTimer); } catch(_){} }
        __lbHideTimer = setTimeout(__lbDoHide, remaining);
      } else {
        __lbDoHide();
      }
    }
  };
  // Allow runtime adjustment of minimum duration: window.loadingBar.minDuration = 4000
  try {
    Object.defineProperty(window.loadingBar, 'minDuration', {
      get: function(){ return __lbMin; },
      set: function(v){ __lbMin = Math.max(0, parseInt(v, 10) || 0); }
    });
  } catch(_){ }

  // Auto-show on form submit (supports data-loading-text on form or submit button)
  $(document).on('submit', 'form', function(e){
    const btn = this.querySelector('button[type="submit"][data-loading-text], input[type="submit"][data-loading-text]');
    const text = (btn && btn.getAttribute('data-loading-text')) || this.getAttribute('data-loading-text') || 'Enviando datos...';
    bannerLoading(true, text);
  });
  // Auto-show during jQuery AJAX (top bar + mini loader)
  $(document).ajaxStart(function(){ loadingBar.start('Cargando...'); });
  $(document).ajaxStop(function(){ loadingBar.stop(); });
  // Notify on AJAX errors
  $(document).ajaxError(function(_evt, jqxhr, settings, err){
    try {
      const status = jqxhr && jqxhr.status ? ` [${jqxhr.status}]` : '';
      const url = (settings && settings.url) ? settings.url : '';
      const msg = (jqxhr && jqxhr.responseText) ? String(jqxhr.responseText).slice(0, 200) : (err || 'Error de red');
      notify({ icon: 'error', title: 'Error AJAX' + status, text: (url ? url + ': ' : '') + msg });
    } catch(e){}
  });

  // Auto-show during native fetch as well (top bar + mini)
  (function(){
    if (!window.fetch) return;
    const _fetch = window.fetch.bind(window);
    window.fetch = function(input, init) {
      try { loadingBar.start('Cargando...'); } catch(e){}
      return _fetch(input, init).finally(() => { try { loadingBar.stop(); } catch(e){} });
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
