<?php use App\Core\View; use App\Helpers\Flash; use App\Helpers\Auth; $title = $title ?? APP_NAME;   // Persisted UI state: sidebar hidden (cookie fallback to avoid flicker on navigation)
  $sidebarHidden = (isset($_COOKIE['psSidebarHidden']) && $_COOKIE['psSidebarHidden'] === '1');
?>
<?php
  // Detect auth and route to adapt layout (e.g., login page should be clean)
  $isAuth = Auth::check();
  $reqUri = $_SERVER['REQUEST_URI'] ?? '';
  $isLogin = (strpos($reqUri, '/auth/login') !== false);
  // Active nav helpers
  $isDash = (strpos($reqUri, '/dashboard') !== false);
  $isProductsPg = (strpos($reqUri, '/products') !== false);
  $isSalesPg = (strpos($reqUri, '/sales') !== false);
  $isUsersPg = (strpos($reqUri, '/users') !== false);
  $isSuppliersPg = (strpos($reqUri, '/suppliers') !== false);
  $isCategoriesPg = (strpos($reqUri, '/categories') !== false);
  $isMovementsPg = (strpos($reqUri, '/movements') !== false);
  $isProfilePg = (strpos($reqUri, '/profile') !== false);
  // Current Colombia time from server
  try {
    $coNow = new DateTime('now', new DateTimeZone('America/Bogota'));
  } catch (Exception $e) {
    $coNow = new DateTime('now');
  }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title) ?> - <?= View::e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css">
  <!-- Choices.js for enhanced select dropdowns (scrollable, searchable) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
  <style>
    /* Loading Overlay - Deshabilitado */
    .app-loading-overlay { display: none !important; }
    .app-loading-box { display: none !important; }
    .app-spinner { display: none !important; }
    @keyframes spin { to { transform: rotate(360deg); } }
    /* Loading overlay fade */
    .app-loading-overlay.fade-enter { opacity: 0; }
    .app-loading-overlay.fade-enter-active { opacity: 1; transition: opacity .18s ease-out; }
    .app-loading-overlay.fade-exit { opacity: 1; }
    .app-loading-overlay.fade-exit-active { opacity: 0; transition: opacity .18s ease-in; }
    /* Toasts */
    /* Force dark toast background and light text with high specificity */
    /* Toast container: force top-right placement only for our toasts */
    .swal2-container.ps-toast-container { justify-content: flex-end !important; align-items: flex-start !important; padding: 12px 16px !important; }
    .swal2-container .swal2-popup.ps-toast { background: #0b1220 !important; color: #e5e7eb !important; border-radius: 14px; padding: 12px 14px; box-shadow: none !important; width: 300px; border: 1px solid rgba(255,255,255,.06); }
    .swal2-container .swal2-popup.ps-toast .swal2-html-container { color: inherit !important; margin: 0 !important; }
    .swal2-popup.ps-toast .swal2-close { color: #e5e7eb; opacity: .7; }
    .swal2-popup.ps-toast .swal2-timer-progress-bar { height: 4px; background: #22c55e; border-radius: 0 0 12px 12px; }
    .ps-toast-body { display: grid; grid-template-columns: 46px 1fr; gap: 14px; align-items: flex-start; }
    .ps-toast-icon { width: 46px; height: 46px; border-radius: 11px; display: grid; place-items: center; color: #ffffff; font-size: 18px; box-shadow: none !important; }
    .ps-toast-title { font-weight: 900; color: #f9fafb; margin-top: 0; font-size: 15px; }
    .ps-toast-text { color: #f3f4f6; opacity: .98; margin-top: 4px; font-size: 14px; font-weight: 700; }
    .ps-toast.ps-success .ps-toast-icon { background: #0b1220; color: #22c55e; border: 2px solid #22c55e; }
    .ps-toast.ps-warning .ps-toast-icon { background: #fcd34d; }
    .ps-toast.ps-error .ps-toast-icon { background: #fca5a5; }
    .ps-toast.ps-info .ps-toast-icon { background: #93c5fd; }
    .ps-toast.ps-success .swal2-timer-progress-bar { background: #22c55e; }
    .ps-toast.ps-warning .swal2-timer-progress-bar { background: #f59e0b; }
    .ps-toast.ps-error .swal2-timer-progress-bar { background: #ef4444; }
    .ps-toast.ps-info .swal2-timer-progress-bar { background: #3b82f6; }
    /* Fallback toast container and item (when SweetAlert2 not available) */
    .ps-fallback-toasts { position: fixed; right: 16px; top: 16px; z-index: 4005; display: grid; gap: 10px; }
    .ps-fallback-toast { background: #0b1220; color: #e5e7eb; border-radius: 14px; padding: 14px 16px; box-shadow: none !important; min-width: 320px; max-width: 480px; display: grid; grid-template-columns: 46px 1fr 18px; gap: 14px; align-items: start; border: 1px solid rgba(255,255,255,.06); }
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
    /* Slower sidebar transitions (AdminLTE override) */
    .sidebar-mini .main-sidebar,
    .sidebar-mini .content-wrapper,
    .sidebar-mini .main-header,
    .sidebar-mini .main-footer {
      transition: margin-left .45s ease, width .45s ease, left .45s ease !important;
    }
    .sidebar-mini.sidebar-collapse .main-sidebar,
    .sidebar-mini.sidebar-collapse .content-wrapper,
    .sidebar-mini.sidebar-collapse .main-header,
    .sidebar-mini.sidebar-collapse .main-footer {
      transition: margin-left .45s ease, width .45s ease, left .45s ease !important;
    }
    /* Disable sidebar transitions during first paint to avoid flicker */
    .ps-no-anim .main-sidebar,
    .ps-no-anim .content-wrapper,
    .ps-no-anim .main-header,
    .ps-no-anim .main-footer { transition: none !important; }
    /* Colombia time chip */
    .ps-co-time { position: fixed; left: 16px; bottom: 16px; z-index: 5001; background: rgba(255,255,255,.95); color: #1f2937; border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px 10px; box-shadow: 0 8px 22px rgba(0,0,0,.12); font-size: 13px; display: inline-flex; align-items: center; gap: 8px; }
    .ps-co-time .fa-clock { color: #3c8dbc; }
    /* Compact mini loader */
    .ps-mini-loader { position: fixed; right: 16px; bottom: 16px; z-index: 4006; background: #0b1220; color: #e5e7eb; padding: 8px 12px; border-radius: 10px; display: none; align-items: center; gap: 8px; box-shadow: 0 16px 40px rgba(0,0,0,.35); border: 1px solid rgba(255,255,255,.06); }
    /* UX: Hide bottom-right mini loader completely */
    #psMiniLoader { display: none !important; }
    /* Choices.js dropdown UX improvements (contained, scrollable) */
    .choices { position: relative; }
    .choices__inner { min-height: 40px; }
    .choices.is-open .choices__list--dropdown { z-index: 5005; }
    /* Cap height and force vertical scroll on any dropdown list */
    .choices__list--dropdown,
    .choices__list[role="listbox"],
    .choices__list--dropdown .choices__list {
      max-height: 260px !important;
      overflow-y: auto !important;
      -webkit-overflow-scrolling: touch;
    }
    /* Allow long option labels to wrap to next line */
    .choices__list--dropdown .choices__item {
      white-space: normal !important;
      overflow: visible;
      text-overflow: clip;
      word-break: break-word;
      line-height: 1.25;
    }
    /* Do not show disabled (placeholder) items inside dropdown */
    .choices__list--dropdown .choices__item--disabled { display: none !important; }
    .ps-mini-spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.15); border-top-color: #3b82f6; border-radius: 50%; animation: spin .9s linear infinite; }
    /* Reduced motion respect */
    @media (prefers-reduced-motion: reduce) {
      .app-spinner { animation: none; }
      .app-loading-overlay.fade-enter-active,
      .app-loading-overlay.fade-exit-active { transition: none; }
    }
    /* Floating cart button */
    .cart-fab { position: fixed; right: 20px; bottom: 20px; z-index: 3045; border: none; border-radius: 50%; width: 64px; height: 64px; background: #3c8dbc; color: #fff; box-shadow: 0 8px 22px rgba(0,0,0,.28); display: inline-flex; align-items: center; justify-content: center; animation: ps-fab-float 4s ease-in-out infinite, ps-fab-pulse 1.2s ease-in-out infinite, ps-fab-glow 6s ease-in-out infinite; }
    .cart-fab:hover { background: #357ea8; }
    .cart-fab .fa-shopping-cart { font-size: 1.4rem; }
    .cart-fab-badge { position: absolute; top: -6px; right: -6px; border-radius: 10px; font-weight: 700; }
    /* Floating notifications button */
    .notify-fab { position: fixed; right: 20px; bottom: 94px; z-index: 3046; border: none; border-radius: 50%; width: 64px; height: 64px; background: #f59e0b; color: #111827; box-shadow: 0 8px 22px rgba(0,0,0,.28); display: inline-flex; align-items: center; justify-content: center; animation: ps-fab-float 4s ease-in-out infinite, ps-fab-pulse 1.2s ease-in-out infinite, ps-notify-rgb 4s ease-in-out infinite; }
    .notify-fab:hover { background: #d97706; color: #111827; }
    .notify-fab .fa-bell { font-size: 1.4rem; }
    .notify-fab-badge { position: absolute; top: -6px; right: -6px; border-radius: 10px; font-weight: 700; }
    /* Notifications modal content design */
    .notify-section { border-radius: 12px; overflow: hidden; box-shadow: 0 6px 18px rgba(0,0,0,.08); margin: 12px; border: 1px solid #eee; }
    .notify-section .ns-header { padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; color: #111; }
    .notify-section .ns-header .title { display: flex; align-items: center; font-weight: 700; }
    .notify-section .ns-header .title i { margin-right: 8px; }
    .notify-section .ns-header .count { font-weight: 700; opacity: .8; }
    .notify-section.ns-low .ns-header { background: linear-gradient(90deg,#fff7ed,#fde68a); }
    .notify-section.ns-expired .ns-header { background: linear-gradient(90deg,#fef2f2,#fecaca); }
    .notify-section.ns-soon .ns-header { background: linear-gradient(90deg,#fff7ed,#fde68a); }
    .notify-section .ns-list .notify-item { display:flex; align-items:center; justify-content:space-between; padding: 10px 14px; border-top: 1px solid #f1f1f1; }
    .notify-section .ns-list .notify-item:hover { background: #fafafa; }
    .notify-section .ns-list .left { display:flex; align-items:center; }
    .notify-section .ns-list .left .name { font-weight:600; }
    .notify-section .ns-list .left .sku { margin-left:8px; color:#6b7280; font-size:.85rem; }
    .notify-section .ns-list .right { display:flex; gap:6px; align-items:center; }
    .chip { display:inline-flex; align-items:center; padding: 2px 8px; border-radius: 999px; font-size: .75rem; font-weight: 600; border: 1px solid transparent; }
    .chip-info { background:#ebf5ff; color:#1d4ed8; border-color:#bfdbfe; }
    .chip-warn { background:#fff7ed; color:#b45309; border-color:#fde68a; }
    .chip-danger { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .chip-emph { font-size: .78rem; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    .chip-emph.chip-warn { background:#fffbeb; color:#92400e; border-color:#f59e0b; }
    .chip-emph.chip-danger { background:#fff1f2; color:#7f1d1d; border-color:#ef4444; }
    .text-amber-strong { color: #f59e0b !important; }

    /* Subtle floating + glow animations for FABs */
    @keyframes ps-fab-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
    @keyframes ps-fab-float-slow { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-2px); } }
    @keyframes ps-fab-pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.14); } }
    @keyframes ps-fab-glow {
      0%   { box-shadow: 0 6px 16px rgba(0,0,0,.25), 0 0 10px rgba(255,0,128,.12), 0 0 20px rgba(0,200,255,.10); }
      50%  { box-shadow: 0 6px 16px rgba(0,0,0,.25), 0 0 12px rgba(255,200,0,.14), 0 0 24px rgba(64,224,208,.12); }
      100% { box-shadow: 0 6px 16px rgba(0,0,0,.25), 0 0 10px rgba(128,0,255,.12), 0 0 20px rgba(0,255,200,.10); }
    }
    /* RGB side glow for notification button (stronger) */
    @keyframes ps-notify-rgb {
      0% {
        box-shadow:
          0 8px 22px rgba(0,0,0,.28),
          -6px 0 18px rgba(255, 0, 128, .55),
          6px 0 18px rgba(0, 200, 255, .55);
      }
      50% {
        box-shadow:
          0 8px 22px rgba(0,0,0,.28),
          -6px 0 22px rgba(255, 200, 0, .65),
          6px 0 22px rgba(64, 224, 208, .65);
      }
      100% {
        box-shadow:
          0 8px 22px rgba(0,0,0,.28),
          -6px 0 18px rgba(128, 0, 255, .55),
          6px 0 18px rgba(0, 255, 200, .55);
      }
    }
    /* Gentle floating for user chip and dropdown panel */
    @keyframes ps-chip-float { 0%,100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-2px) scale(1.035); } }
    @keyframes ps-dd-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-2px); } }
    /* RGB border animation for dropdown */
    @keyframes ps-dd-rgb { 0% { filter: hue-rotate(0deg); } 100% { filter: hue-rotate(360deg); } }
    /* Subtle RGB halo for user chip */
    @keyframes ps-chip-rgb {
      0% { box-shadow: 0 2px 10px rgba(239,68,68,.16), 0 0 6px rgba(255, 59, 48, .28), 0 0 0px rgba(52, 199, 89, 0); }
      33% { box-shadow: 0 2px 10px rgba(239,68,68,.16), 0 0 10px rgba(52, 199, 89, .32), 0 0 16px rgba(88, 86, 214, .22); }
      66% { box-shadow: 0 2px 10px rgba(239,68,68,.16), 0 0 12px rgba(10, 132, 255, .34), 0 0 18px rgba(255, 149, 0, .24); }
      100% { box-shadow: 0 2px 10px rgba(239,68,68,.16), 0 0 10px rgba(255, 59, 48, .30), 0 0 14px rgba(0, 255, 200, .22); }
    }
    .chip-muted { background:#f3f4f6; color:#374151; border-color:#e5e7eb; }
    /* Lightweight modal (global) */
    .ps-modal { position: fixed; inset: 0; z-index: 3050; display: none; opacity: 0; transition: opacity .65s ease; }
    .ps-modal.ps-show { opacity: 1; }
    .ps-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.45); }
    .ps-modal-dialog { position: relative; background: #fff; z-index: 1; max-width: 860px; width: 92%; margin: 40px auto; border-radius: 8px; box-shadow: 0 16px 40px rgba(0,0,0,.3); display: flex; flex-direction: column; max-height: calc(100vh - 80px); transform: translateY(12px) scale(.985); transition: transform .65s cubic-bezier(.16,1,.3,1); }
    .ps-modal.ps-show .ps-modal-dialog { transform: translateY(0) scale(1); }
    .ps-modal-header { padding: 12px 16px; border-bottom: 1px solid #eee; display: flex; align-items: center; position: relative; }
    .ps-modal-body { padding: 0; max-height: calc(100vh - 190px); overflow: auto; }
    .ps-modal-footer { padding: 10px 16px; border-top: 1px solid #eee; background: #fafafa; }
    .ps-modal .close { background: transparent; border: 0; font-size: 1.6rem; line-height: 1; color: #333; position: absolute; right: 10px; top: 8px; padding: 4px 8px; opacity: .8; }
    .ps-modal .close:hover { opacity: 1; }
    @media (max-width: 576px) {
      .ps-modal-dialog { width: 96%; margin: 10px auto; max-height: calc(100vh - 20px); }
      .ps-modal-body { max-height: calc(100vh - 170px); }
    }
    /* Disable pointer events app-wide while busy; keep overlay and confirm dialogs interactive */
    body.app-busy { cursor: wait; }
    body.app-busy * { pointer-events: none !important; }
    /* Exceptions: Loading overlay and confirmation modals must remain clickable */
    body.app-busy .app-loading-overlay,
    body.app-busy .app-loading-overlay *,
    body.app-busy .swal2-container,
    body.app-busy .swal2-container *,
    body.app-busy #psConfirmFallback,
    body.app-busy #psConfirmFallback * { pointer-events: auto !important; }
    body.app-busy .app-loading-overlay { display: flex !important; }
    /* Ensure confirmation dialogs render above any app overlays */
    .swal2-container { z-index: 5006 !important; }
    #psConfirmFallback { z-index: 5006 !important; }
    /* Keep header fixed and avoid overlap with content when fixed layout active
       Use real AdminLTE navbar height (~56px) to avoid big white gap */
    :root { --ps-header-h: 56px; --ps-header-gap: 16px; }
    .layout-navbar-fixed .content-wrapper { padding-top: calc(var(--ps-header-h, 56px) - var(--ps-header-gap, 16px)); }
    @media (max-width: 576px) { .layout-navbar-fixed .content-wrapper { padding-top: calc(var(--ps-header-h, 56px) - var(--ps-header-gap, 16px)); } }
    .layout-navbar-fixed .content-wrapper > .content { padding-top: 0; }
    .layout-navbar-fixed .content-wrapper .container-fluid { padding-top: 0; }
    /* Quitar cualquier margen/padding superior del primer bloque para que quede pegado */
    .layout-navbar-fixed .content-wrapper .container-fluid > *:first-child { margin-top: 0 !important; padding-top: 0 !important; }
    /* Pegar visualmente la primera card al borde de la navbar (tuck under shadow) */
    .layout-navbar-fixed .content-wrapper .container-fluid > .card:first-child,
    .layout-navbar-fixed .content-wrapper .container-fluid > .products-card:first-child { margin-top: -16px !important; }
    /* Sticky modal header when scrolling modal body */
    .ps-modal-header { position: sticky; top: 0; z-index: 2; background: #fff; }
    /* Global Empty State */
    .ps-empty-state { display: grid; place-items: center; padding: 48px 16px; color: #6b7280; }
    .ps-empty-state .box { text-align: center; max-width: 680px; }
    .ps-empty-state .title { font-weight: 900; font-size: 1.25rem; color: #111827; }
    .ps-empty-state .desc { margin-top: 6px; font-weight: 700; }
    /* Pretty top navbar */
    .ps-navbar.navbar { box-shadow: 0 1px 0 rgba(0,0,0,.04); }
    .ps-navbar .navbar-nav { align-items: center; gap: 8px; flex-wrap: nowrap; }
    .ps-navbar .navbar-nav > .nav-item { margin-right: 0; }
    .ps-navbar .nav-link { font-weight: 700; color: #374151; padding: 6px 10px; border-radius: 10px; display: inline-flex; align-items: center; line-height: 1; font-size: 14px; }
    .ps-navbar .nav-link i { opacity: .9; font-size: 15px; margin-right: 6px; }
    .ps-navbar .nav-link:hover { color: #111827; background: rgba(59,130,246,.10); }
    .ps-navbar .nav-link.active { color: #0f172a; background: rgba(59,130,246,.18); box-shadow: inset 0 0 0 1px rgba(59,130,246,.35); }
    /* Profile chip (pastel red) */
    .user-menu > .nav-link { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 5px 9px; border-radius: 999px; background: linear-gradient(135deg, #ffe4e6, #fecaca); border: 1px solid #fecaca; box-shadow: 0 2px 10px rgba(239,68,68,.14); transition: background .2s ease, box-shadow .2s ease, transform .1s ease; animation: ps-chip-float 5.5s ease-in-out infinite, ps-chip-rgb 4.5s ease-in-out infinite; }
    .user-menu > .nav-link:hover { background: linear-gradient(135deg, #fecaca, #fbcfe8); box-shadow: 0 6px 16px rgba(244,63,94,.25), 0 0 0 2px rgba(255,255,255,.35) inset; }
    .user-menu > .nav-link:active { transform: translateY(1px) scale(.995); }
    .user-menu > .nav-link .user-image { width: 28px; height: 28px; border: 2px solid #fecaca; box-shadow: 0 2px 6px rgba(239,68,68,.18); transform: translateY(2px); }
    .user-menu > .nav-link span { font-weight: 700; color: #0f172a; }
    /* Dropdown redesign */
    .ps-user-dd { width: 340px; overflow: hidden; border-radius: 14px; box-shadow: 0 22px 60px rgba(0,0,0,.18); position: relative; }
    .ps-user-dd::before { content: ""; position: absolute; inset: -2px; border-radius: 16px; padding: 2px; background: linear-gradient(90deg,#38bdf8,#a78bfa,#fb7185,#34d399,#38bdf8); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; animation: ps-dd-rgb 8s linear infinite; pointer-events: none; }
    .ps-user-dd-header { background: radial-gradient(120% 120% at 0% 0%, #1d4ed8, #3b82f6); text-align:center; padding: 18px 16px 14px; position: relative; }
    .ps-user-dd-header::after { content: ""; position: absolute; inset: auto 0 0 0; height: 5px; background: linear-gradient(90deg,#38bdf8,#a78bfa,#fb7185,#34d399); filter: saturate(1.1); opacity: .9; }
    .ps-user-dd-header .avatar { width: 84px; height: 84px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 6px 18px rgba(0,0,0,.25); object-fit: cover; }
    .ps-user-dd-header .name { color:#fff; font-weight: 900; font-size: 17px; margin-top: 10px; }
    .ps-user-dd-header .email { color: rgba(255,255,255,.95); font-weight: 700; font-size: 13px; }
    .ps-user-dd-header .role { display:inline-flex; align-items:center; gap:6px; margin-top: 6px; font-weight:900; font-size: 12px; padding:6px 10px; border-radius:999px; border:1px solid transparent; }
    .ps-role-admin  { background: rgba(239,68,68,.14); color: #fee2e2; border-color: rgba(239,68,68,.45); }
    .ps-role-tech   { background: rgba(59,130,246,.14); color: #dbeafe; border-color: rgba(59,130,246,.45); }
    .ps-role-worker { background: rgba(245,158,11,.14); color: #fef08a; border-color: rgba(245,158,11,.45); }
    .ps-role-default{ background: rgba(148,163,184,.16); color: #f3f4f6; border-color: rgba(148,163,184,.35); }
    .ps-user-dd .dd-actions { display:grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 12px; background:#fff; }
    .ps-user-dd .btn { font-weight: 900; font-size: 12.5px; padding: 7px 10px; border-radius: 9px; transition: transform .12s ease, box-shadow .2s ease, background .2s ease; pointer-events: auto; }
    .ps-user-dd .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(0,0,0,.12); }
    .ps-user-dd .btn:active { transform: translateY(0); }
    .ps-user-dd .btn-profile { background: linear-gradient(135deg,#e0e7ff,#bfdbfe); color:#1e40af; border:1px solid #bfdbfe; }
    .ps-user-dd .btn-profile:hover { background: linear-gradient(135deg,#dbeafe,#a5b4fc); color:#1e3a8a; }
    .ps-user-dd .btn-logout { background: linear-gradient(135deg,#fee2e2,#fecaca); color:#7f1d1d; border:1px solid #fecaca; }
    .ps-user-dd .btn-logout:hover { background: linear-gradient(135deg,#fecaca,#fbcfe8); color:#7f1d1d; }
    /* Sidebar hide/show behavior */
    body.sidebar-hidden .main-sidebar { display: none !important; }
    .sidebar-hidden .content-wrapper { margin-left: 0 !important; }
    /* Brand that appears only when sidebar is hidden */
    .ps-topbrand { display: none; align-items: center; gap: 8px; font-weight: 900; color: #0f172a; margin-left: 6px; margin-right: 10px; font-size: 16px; }
    .ps-topbrand .icon { width: 26px; height: 26px; display: grid; place-items: center; border-radius: 8px; background: #e0e7ff; color: #1d4ed8; box-shadow: 0 0 0 rgba(29,78,216,0); }
    /* RGB glow when sidebar hidden */
    .sidebar-hidden .ps-topbrand .name {
      background: linear-gradient(90deg, #60a5fa, #a78bfa, #f472b6, #fb7185, #f59e0b, #34d399, #60a5fa);
      background-size: 400% 100%;
      -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
      animation: psHue 8s linear infinite, psGlow 2.2s ease-in-out infinite alternate, psFloat 6s ease-in-out infinite;
      text-shadow: 0 0 0 rgba(99,102,241,0);
    }
    .sidebar-hidden .ps-topbrand .icon { animation: psIconGlow 2.2s ease-in-out infinite alternate; }
    @keyframes psHue { 0% { background-position: 0% 50%; } 100% { background-position: 100% 50%; } }
    @keyframes psGlow { 0% { text-shadow: 0 0 0px rgba(99,102,241,.0), 0 0 0px rgba(147,197,253,.0); }
                         100% { text-shadow: 0 0 10px rgba(99,102,241,.35), 0 0 18px rgba(147,197,253,.25); } }
    @keyframes psIconGlow { 0% { box-shadow: 0 0 0 rgba(59,130,246,0); }
                            100% { box-shadow: 0 0 16px rgba(59,130,246,.45); } }
    @keyframes psFloat { 0% { transform: translateY(0); } 50% { transform: translateY(-1px); } 100% { transform: translateY(0); } }
    @media (prefers-reduced-motion: reduce) {
      .sidebar-hidden .ps-topbrand .name, .sidebar-hidden .ps-topbrand .icon { animation: none; }
    }
    .sidebar-hidden .ps-topbrand { display: inline-flex; }
    /* Dark mode toggles and safe overrides */
    .ps-theme-btn { border: 0; background: transparent; padding: 6px 10px; border-radius: 10px; color: #374151; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
    .ps-theme-btn i { font-size: 14px; }
    .ps-theme-btn:hover { background: rgba(59,130,246,.10); color: #111827; }
    body.dark-mode .ps-navbar.navbar { background: #0b1220 !important; color: #e5e7eb; box-shadow: 0 1px 0 rgba(255,255,255,.06); }
    body.dark-mode .ps-navbar .nav-link { color: #e5e7eb; }
    body.dark-mode .ps-navbar .nav-link:hover { color: #fff; background: rgba(59,130,246,.22); }
    body.dark-mode .ps-navbar .nav-link.active { color: #fff; background: rgba(59,130,246,.28); box-shadow: inset 0 0 0 1px rgba(59,130,246,.45); }
    body.dark-mode .ps-user-dd { background: #0f172a; color: #e5e7eb; }
    body.dark-mode .ps-user-dd .dd-actions { background: #0f172a; }
    body.dark-mode .ps-user-dd-header { background: radial-gradient(120% 120% at 0% 0%, #7f1d1d, #b91c1c); }
    body.dark-mode .ps-user-dd-header::after { opacity: .75; }
    body.dark-mode .ps-user-dd .btn-profile { background: linear-gradient(135deg,#1f2937,#111827); color:#bfdbfe; border-color: rgba(59,130,246,.35); }
    body.dark-mode .ps-user-dd .btn-profile:hover { background: linear-gradient(135deg,#0b1220,#111827); }
    body.dark-mode .ps-user-dd .btn-logout { background: linear-gradient(135deg,#111827,#0b1220); color:#fecaca; border-color: rgba(239,68,68,.35); }
    body.dark-mode .ps-user-dd .btn-logout:hover { background: linear-gradient(135deg,#111827,#111827); }
    body.dark-mode .dropdown-menu { background: #0f172a; color: #e5e7eb; border-color: rgba(255,255,255,.08); }
    body.dark-mode .dropdown-item { color: #e5e7eb; }
    body.dark-mode .dropdown-item:hover { background: rgba(59,130,246,.20); color: #fff; }
    body.dark-mode .ps-modal-dialog { background: #111827; color: #e5e7eb; }
    body.dark-mode .ps-modal-header { border-color: rgba(255,255,255,.08); background: #0b1220; }
    body.dark-mode .ps-modal-footer { border-color: rgba(255,255,255,.08); background: #0b1220; }
    body.dark-mode .ps-co-time { background: rgba(17,24,39,.95); color: #e5e7eb; border-color: rgba(255,255,255,.08); }
    body.dark-mode .cart-fab { background: #2563eb; }
    body.dark-mode .notify-fab { background: #f59e0b; color: #111827; }
  </style>
</head>
<body class="hold-transition sidebar-mini ps-no-anim<?= $isLogin ? ' login-body' : ' layout-navbar-fixed layout-fixed' ?><?= $sidebarHidden ? ' sidebar-hidden sidebar-collapse' : '' ?>">
<div class="wrapper">
  <?php if (!$isLogin && $isAuth): ?>
  <nav class="main-header navbar navbar-expand navbar-white navbar-light ps-navbar">
    <?php $isTech = Auth::isTechnician(); $isAdmin = Auth::isAdmin(); ?>
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" id="psToggleSidebar" href="#" aria-label="Alternar menú" role="button" style="cursor:pointer" onclick="return (function(e){try{if(e)e.preventDefault();var b=document.body;var h=!b.classList.contains('sidebar-hidden');b.classList.toggle('sidebar-hidden',h);b.classList.toggle('sidebar-collapse',h);b.classList.remove('sidebar-open');var s=document.querySelector('.main-sidebar');if(s) s.style.display=h?'none':'';try{localStorage.setItem('psSidebarHidden',h?'1':'0');}catch(_){ }document.cookie='psSidebarHidden='+(h?'1':'0')+'; max-age=31536000; path=/';}catch(_){ } return false;})(event)"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item align-self-center"><span class="ps-topbrand"><span class="icon"><i class="fas fa-capsules"></i></span><span class="name"><?= View::e(APP_NAME) ?></span></span></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/dashboard" class="nav-link<?= $isDash ? ' active' : '' ?>"><i class="fas fa-tachometer-alt mr-1" aria-hidden="true"></i> Dashboard</a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/products" class="nav-link<?= $isProductsPg ? ' active' : '' ?>"><i class="fas fa-pills mr-1" aria-hidden="true"></i> Productos</a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/sales" class="nav-link<?= $isSalesPg ? ' active' : '' ?>"><i class="fas fa-cash-register mr-1" aria-hidden="true"></i> Ventas</a></li>
      <?php if ($isAdmin): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/users" class="nav-link<?= $isUsersPg ? ' active' : '' ?>"><i class="fas fa-users mr-1" aria-hidden="true"></i> Usuarios</a></li>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/suppliers" class="nav-link<?= $isSuppliersPg ? ' active' : '' ?>"><i class="fas fa-truck mr-1" aria-hidden="true"></i> Proveedores</a></li>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= BASE_URL ?>/categories" class="nav-link<?= $isCategoriesPg ? ' active' : '' ?>"><i class="fas fa-tags mr-1" aria-hidden="true"></i> Categorías</a></li>
      <?php endif; ?>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
      <?php
        $avatar = $_SESSION['user']['avatar'] ?? 'https://via.placeholder.com/40?text=U';
        $name = $_SESSION['user']['name'] ?? 'Usuario';
        if (strpos($avatar, 'http://') !== 0 && strpos($avatar, 'https://') !== 0) { $avatar = BASE_URL . '/' . ltrim($avatar, '/'); }
        $avver = isset($_SESSION['user']['avatar_ver']) ? (int)$_SESSION['user']['avatar_ver'] : 0;
        $avatar_q = $avatar . ($avver ? (strpos($avatar, '?') === false ? ('?v=' . $avver) : ('&v=' . $avver)) : '');
        // Role mapping for pretty badge
        $roleRaw = strtolower((string)($_SESSION['user']['role'] ?? ''));
        $roleName = $roleRaw ?: 'Usuario';
        $roleClass = 'ps-role-default';
        if (in_array($roleRaw, ['admin','administrator'], true)) { $roleName = 'Administrador'; $roleClass = 'ps-role-admin'; }
        elseif (in_array($roleRaw, ['technician','tecnico','técnico'], true)) { $roleName = 'Técnico'; $roleClass = 'ps-role-tech'; }
        elseif (in_array($roleRaw, ['worker','trabajador'], true)) { $roleName = 'Trabajador'; $roleClass = 'ps-role-worker'; }
      ?>
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
          <img src="<?= View::e($avatar_q) ?>" class="user-image img-circle elevation-2" alt="Avatar" style="object-fit:cover; width:32px; height:32px;">
          <span class="d-none d-md-inline"><?= View::e($name) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right ps-user-dd">
          <li class="ps-user-dd-header">
            <img src="<?= View::e($avatar_q) ?>" class="avatar" alt="Avatar">
            <div class="name"><?= View::e($name) ?></div>
            <div class="email"><?= View::e($_SESSION['user']['email'] ?? '') ?></div>
            <div class="role <?= View::e($roleClass) ?>" title="Rol del usuario"><i class="fas fa-user-shield mr-1" aria-hidden="true"></i> <?= View::e($roleName) ?></div>
          </li>
          <li class="p-2">
            <div class="dd-actions">
              <a href="<?= BASE_URL ?>/profile" class="btn btn-profile"><i class="fas fa-user mr-1"></i> Ir al perfil</a>
              <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-logout js-confirmable" data-confirm-title="Cerrar sesión" data-confirm-text="¿Seguro que deseas cerrar sesión?" data-confirm-ok="Cerrar" data-confirm-cancel="Cancelar"><i class="fas fa-sign-out-alt mr-1"></i> Cerrar sesión</a>
            </div>
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
            <li class="nav-item"><a href="<?= BASE_URL ?>/suppliers" class="nav-link"><i class="nav-icon fas fa-truck"></i><p>Proveedores</p></a></li>
            <li class="nav-item"><a href="<?= BASE_URL ?>/categories" class="nav-link"><i class="nav-icon fas fa-tags"></i><p>Categorías</p></a></li>
            <li class="nav-item"><a href="<?= BASE_URL ?>/movements" class="nav-link"><i class="nav-icon fas fa-history"></i><p>Movimientos</p></a></li>
            <li class="nav-item"><a href="<?= BASE_URL ?>/auth/login-logs" class="nav-link"><i class="nav-icon fas fa-sign-in-alt"></i><p>Registro de Accesos</p></a></li>
          <?php endif; ?>
          <li class="nav-item"><a href="<?= BASE_URL ?>/profile" class="nav-link"><i class="nav-icon fas fa-user"></i><p>Perfil</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>
  <?php endif; ?>
  <div class="content-wrapper"<?= $isLogin ? ' style="margin-left:0;"' : '' ?> >
    <section class="content pt-0">
      <div class="container-fluid">
        <?php include $viewFile; ?>
      </div>
    </section>
  </div>
  <?php if (!$isLogin && $isAuth): ?>
  <footer class="main-footer small"><strong>&copy; <?= date('Y') ?> PharmaSoft</strong></footer>
  <?php endif; ?>
</div>
<?php if (!$isLogin && $isAuth): ?>
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
<!-- Global Floating Notifications Button and Modal -->
<button id="psNotifyFab" class="notify-fab" title="Notificaciones" aria-label="Notificaciones">
  <i class="fas fa-bell" aria-hidden="true"></i>
  <span class="badge badge-danger notify-fab-badge" id="psNotifyBadge" style="display:none;">0</span>
  <span class="sr-only">Notificaciones</span>
</button>

<div id="psNotifyModal" class="ps-modal" style="display:none;">
  <div class="ps-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="psNotifyTitle">
    <div class="ps-modal-header">
      <h5 id="psNotifyTitle" class="mb-0 d-flex align-items-center">
        <i class="fas fa-bell mr-2 text-amber-strong" aria-hidden="true"></i>
        Notificaciones
      </h5>
      <button type="button" class="close" id="psNotifyClose" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="ps-modal-body" id="psNotifyBody">
      <div class="p-3 text-muted">Cargando notificaciones...</div>
    </div>
    <div class="ps-modal-footer d-flex justify-content-between align-items-center">
      <small class="text-muted">Se actualiza al abrir.</small>
      <div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="psNotifyRefresh"><i class="fas fa-sync-alt mr-1"></i> Actualizar</button>
        <button type="button" class="btn btn-primary btn-sm" id="psNotifyOk">Cerrar</button>
      </div>
    </div>
  </div>
  <div class="ps-modal-backdrop" id="psNotifyBackdrop"></div>
</div>
<?php endif; ?>
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
<!-- Colombia time chip (visible en todo el sistema) -->
<div id="psCoTime" class="ps-co-time" aria-live="polite">
  <i class="far fa-clock" aria-hidden="true"></i>
  <span class="d-none d-md-inline">Colombia:</span>
  <span id="psCoDateText"><?php echo htmlspecialchars($coNow->format('d/m/Y')); ?></span>
  <span id="psCoTimeText"><?php echo htmlspecialchars($coNow->format('h:i:s A')); ?></span>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/dist/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.2/dist/themes/base/jquery-ui.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
<script src="<?= BASE_URL ?>/js/confirm-modal.js?v=20250826-0138"></script>
<script>
  // Sidebar hide/show toggle with persistence and brand swap
  (function(){
    const key = 'psSidebarHidden';
    const body = document.body;
    const apply = (hidden) => {
      const sidebar = document.querySelector('.main-sidebar');
      if (hidden) {
        body.classList.add('sidebar-hidden');
        body.classList.add('sidebar-collapse');
        body.classList.remove('sidebar-open');
        if (sidebar) sidebar.style.display = 'none';
      } else {
        // Sidebar visible: default to collapsed (mini) unless user preference overrides later
        body.classList.remove('sidebar-hidden');
        body.classList.add('sidebar-collapse');
        body.classList.remove('sidebar-open');
        if (sidebar) sidebar.style.display = '';
      }
    };
    const readCookie = (name) => {
      try {
        const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}\\(\\)\\[\\]\\/\\+^])/g,'\\$1') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
      } catch(_) { return null; }
    };
    const writeCookie = (name, value) => {
      try { document.cookie = name + '=' + encodeURIComponent(value) + '; max-age=31536000; path=/'; } catch(_) {}
    };
    try {
      const c = readCookie('psSidebarHidden');
      const saved = (c !== null) ? c : localStorage.getItem(key);
      if (saved === '1') apply(true); else if (saved === '0') apply(false);
    } catch (e) {}
    // If no explicit hidden state saved, and sidebar is visible, ensure it starts collapsed by default
    try {
      const hasHidden = (function(){ const c = readCookie('psSidebarHidden'); const s = localStorage.getItem(key); return (c!==null) || (s!==null); })();
      if (!hasHidden && !body.classList.contains('sidebar-hidden')) {
        body.classList.add('sidebar-collapse');
      }
    } catch(_){}
    const onToggle = function(ev){
      if (ev) ev.preventDefault();
      const hidden = !body.classList.contains('sidebar-hidden');
      apply(hidden);
      try { localStorage.setItem(key, hidden ? '1' : '0'); } catch (e) {}
      writeCookie('psSidebarHidden', hidden ? '1' : '0');
    };
    // Expose globally for inline fallback
    window.psToggleSidebar = onToggle;
    const btn = document.getElementById('psToggleSidebar');
    if (btn) btn.addEventListener('click', onToggle);
    // Delegated fallback (por si el botón cambia entre vistas)
    document.addEventListener('click', function(e){
      const t = e.target.closest && e.target.closest('#psToggleSidebar');
      if (t) onToggle(e);
    });
    // Expand on hover (remove collapse) and collapse when leaving the sidebar subtree
    (function(){
      // Use server date as authoritative "today" to avoid client timezone/clock drift
      var SERVER_TODAY = '<?= date('Y-m-d') ?>';
      const sb = document.querySelector('.main-sidebar');
      if (!sb) return;
      function isVisible(){ return !body.classList.contains('sidebar-hidden'); }
      // Expand as soon as cursor enters any child of the sidebar
      sb.addEventListener('mouseover', function(){
        if (isVisible()) {
          body.classList.remove('sidebar-collapse');
          body.classList.add('sidebar-open');
        }
      });
      // Collapse only when cursor leaves the entire sidebar subtree
      sb.addEventListener('mouseout', function(e){
        if (!isVisible()) return;
        const toEl = e.relatedTarget;
        const stillInside = toEl && sb.contains(toEl);
        if (!stillInside) {
          body.classList.add('sidebar-collapse');
          body.classList.remove('sidebar-open');
        }
      });
    })();
  })();
  // Sync content offset with real navbar height to avoid any white gap
  (function(){
    function setHeaderH(){
      try {
        var nav = document.querySelector('.main-header.navbar');
        var h = nav ? Math.ceil(nav.getBoundingClientRect().height) : 56;
        document.documentElement.style.setProperty('--ps-header-h', h + 'px');
      } catch(_){}
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setHeaderH);
    } else { setHeaderH(); }
    window.addEventListener('resize', function(){ setTimeout(setHeaderH, 50); });
    // Recompute after AdminLTE layout events
    document.addEventListener('shown.lte.pushmenu', setHeaderH);
    document.addEventListener('collapsed.lte.pushmenu', setHeaderH);
  })();
  // Theme: dark/light toggle with persistence and system preference fallback
  (function themeInit(){
    const KEY = 'psTheme';
    const body = document.body;
    function updateBtn(mode){
      const btn = document.getElementById('psThemeToggle'); if (!btn) return;
      const icon = btn.querySelector('i'); const label = btn.querySelector('span');
      if (icon) icon.className = 'fas ' + (mode === 'dark' ? 'fa-sun' : 'fa-moon');
      if (label) label.textContent = (mode === 'dark' ? 'Modo claro' : 'Modo oscuro');
      btn.setAttribute('aria-pressed', mode === 'dark' ? 'true' : 'false');
    }
    function apply(mode){ body.classList.toggle('dark-mode', mode === 'dark'); updateBtn(mode); }
    function read(){ try { return localStorage.getItem(KEY); } catch(_) { return null; } }
    function write(v){ try { localStorage.setItem(KEY, v); } catch(_){} }
    function sysPref(){ try { return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light'; } catch(_) { return 'light'; } }
    let saved = read(); let mode = saved || sysPref(); apply(mode);
    function toggle(){ const current = body.classList.contains('dark-mode') ? 'dark' : 'light'; const next = current === 'dark' ? 'light' : 'dark'; apply(next); write(next); }
    window.psThemeToggleClick = function(e){ if (e) e.preventDefault(); toggle(); return false; };
    const btn = document.getElementById('psThemeToggle');
    if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); toggle(); });
    // Delegated fallback in case the button is re-rendered
    document.addEventListener('click', function(e){ var t = e.target && (e.target.closest ? e.target.closest('#psThemeToggle') : null); if (t) { e.preventDefault(); toggle(); } });
    if (window.matchMedia) {
      try {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', function(ev){ if (!read()) apply(ev.matches ? 'dark' : 'light'); });
      } catch(_){}
    }
  })();
</script>
<script>
  // SweetAlert2 Toast (enhanced) if available
  const Toast = (window.Swal ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
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
    // Always top-right for fallback toasts
    container.style.top = '16px';
    container.style.bottom = 'auto';
    container.style.right = '16px';
    container.style.left = 'auto';
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
      // Force default 4s timer unless sticky or explicitly set
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
      // Force all app toasts to render top-right consistently
      const pos = 'top-end';
      // Remove built-in title/text/icon to avoid duplicates and low-contrast defaults
      const clean = Object.assign({}, options);
      delete clean.title; delete clean.text; delete clean.html; delete clean.icon;
      // Ignore any incoming position to guarantee top-right
      delete clean.position;
      // Ensure a default 4s timer unless sticky or explicitly set
      if (!clean.sticky && (typeof clean.timer === 'undefined' || clean.timer === null)) { clean.timer = 4000; }
      // Enforce high-contrast colors
      clean.background = '#0b1220';
      clean.color = '#e5e7eb';
      if (options.sticky) { clean.timer = undefined; clean.showCloseButton = true; }
      // Merge with clean last but keep position enforced at the end
      Toast.fire(Object.assign({}, defaults, clean, { position: pos }));
    } else {
      domToast({ icon: type, title, text, timer: options.timer, sticky: !!options.sticky });
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
  

  // Persist sidebar collapsed state (hamburger) across reloads
  (function sidebarPersistence(){
    try {
      var KEY = 'ps.sidebar.collapsed';
      // Apply saved state early after scripts load
      var saved = null;
      try { saved = localStorage.getItem(KEY); } catch(_) { saved = null; }
      // Default collapsed (already set on <body>), expand only if saved === '0'
      if (saved === '0') {
        try { document.body.classList.remove('sidebar-collapse'); } catch(_){ }
      }
      // Update storage on toggle
      function saveState(){
        var isCollapsed = document.body.classList.contains('sidebar-collapse');
        try { localStorage.setItem(KEY, isCollapsed ? '1' : '0'); } catch(_){ }
      }
      // Listen to AdminLTE pushmenu events if available
      if (window.$ && $.fn && typeof $(document).on === 'function') {
        $(document).on('collapsed.lte.pushmenu shown.lte.pushmenu', function(){ saveState(); });
      }
      // Fallback: also hook the hamburger click
      var btn = document.querySelector('[data-widget="pushmenu"]');
      if (btn) {
        btn.addEventListener('click', function(){ setTimeout(saveState, 50); });
      }
      // Re-enable transitions after first paint to avoid flicker
      function enableTransitions(){ try { document.body.classList.remove('ps-no-anim'); } catch(_){} }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(enableTransitions, 50); });
      } else { setTimeout(enableTransitions, 50); }
    } catch(_){ }
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
      // Skip for explicit no-loader links (e.g., file exports/downloads)
      try { if (this.hasAttribute('data-no-loader')) return; } catch(_){ }
      try { if (window.loadingBar) window.loadingBar.start('Cargando...'); } catch(_){ }
      // allow navigation to proceed normally
    });
    window.addEventListener('beforeunload', function(){
      try {
        if (window.__psSkipNextBeforeUnload) { window.__psSkipNextBeforeUnload = false; return; }
        if (window.loadingBar) window.loadingBar.start('Cargando...');
      } catch(_){ }
    });
  })();

  // Global Cart (floating) only when authenticated and not on login page
  <?php if (!$isLogin && $isAuth): ?>
  (function globalCart(){
    var uid = <?= (int)(\App\Helpers\Auth::id() ?? 0) ?>;
    var KEY = 'pharmasoft_sales_draft_' + uid;
    var LEGACY = 'pharmasoft_pending_cart';
    var SHARED = 'pharmasoft_sales_draft';
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
    function migrate(){
      try {
        // Prefer migrating from shared draft key if exists
        var shared = localStorage.getItem(SHARED);
        if (shared && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, shared); }
        // Also migrate from older legacy key
        var old = localStorage.getItem(LEGACY);
        if (old && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, old); localStorage.removeItem(LEGACY); }
      } catch(_){ }
    }
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
        var emptyHtml = '<div class="p-4 text-center text-muted">'
          + '<div style="font-size:28px; margin-bottom:8px;"><i class="fas fa-shopping-cart"></i></div>'
          + '<div style="font-weight:600;">No hay productos agregados al carrito</div>'
          + '<div class="mt-1">Tu carrito está vacío.</div>';
        if (!isOnProducts()) {
          emptyHtml += '<div class="mt-3"><a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-primary"><i class="fas fa-pills mr-1"></i> Ir a productos</a></div>';
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
        // Full-screen loader with minimum 4.5s for removing single item
        try { window.bannerLoadingMinDuration = 4500; } catch(_){ }
        try { if (window.bannerLoading) bannerLoading(true, 'Quitando artículo...'); } catch(_){ }
        var tr=b.closest('tr'); var idx = tr ? parseInt(tr.getAttribute('data-i')||'-1',10) : -1; var arr = read();
        if (idx>=0 && idx < arr.length){ arr.splice(idx,1); write(arr); render(); }
        try { if (window.bannerLoading) bannerLoading(false); } catch(_){ }
        try { notify({ icon:'success', title:'Producto quitado del carrito' }); } catch(_){ }
      }); }); }
    }
    function open(){
      if (!modal) return;
      render();
      modal.style.display='block';
      // smooth fade+slide
      requestAnimationFrame(function(){ try { modal.classList.add('ps-show'); } catch(_){ } });
      document.body.style.overflow='hidden';
    }
    function close(){
      if (!modal) return;
      try { modal.classList.remove('ps-show'); } catch(_){ }
      var onEnd = function(){ try { modal.removeEventListener('transitionend', onEnd); } catch(_){ } modal.style.display='none'; };
      try { modal.addEventListener('transitionend', onEnd); } catch(_){ setTimeout(onEnd, 650); }
      document.body.style.overflow='';
    }
    if (fab) fab.addEventListener('click', function(e){
      e.preventDefault();
      // Open immediately and show a short toast only
      open();
      try { notify({ icon:'info', title:'Carrito abierto', timer: 2000, position:'top-end', toast:true }); } catch(_){ }
    });
    function closeWithFeedback(){
      // Close immediately and show a short toast only
      close();
      try { notify({ icon:'info', title:'Carrito cerrado', timer: 2000, position:'top-end', toast:true }); } catch(_){ }
    }
    if (mClose) mClose.addEventListener('click', closeWithFeedback);
    if (mBackdrop) mBackdrop.addEventListener('click', function(e){ if (e.target===mBackdrop) closeWithFeedback(); });
    if (mClear) mClear.addEventListener('click', function(){
      // Close modal first, then confirm, then show loader and clear, and reopen
      close();
      var doConfirm = function(){
        try {
          if (window.Swal && Swal.fire) {
            return Swal.fire({ title:'Vaciar carrito', text:'¿Desea vaciar el borrador del carrito?', icon:'warning', showCancelButton:true, confirmButtonText:'Sí, vaciar', cancelButtonText:'No' })
              .then(function(res){ return !!(res && res.isConfirmed); });
          }
        } catch(_){ }
        return Promise.resolve(!!confirm('¿Desea vaciar el borrador del carrito?'));
      };
      doConfirm().then(function(ok){
        if (!ok) { open(); return; }
        try { window.bannerLoadingMinDuration = 4500; } catch(_){ }
        try { if (window.bannerLoading) bannerLoading(true, 'Vaciando carrito...'); } catch(_){ }
        try { write([]); } catch(_){ }
        try { render(); } catch(_){ }
        try { if (window.bannerLoading) bannerLoading(false); } catch(_){ }
        // Reopen modal after operation
        open();
        try { notify({ icon:'success', title:'Carrito vaciado' }); } catch(_){ }
      });
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
  <?php endif; ?>

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
  // Global centered loading banner with interaction lock
  (function(){
    const overlay = document.getElementById('appLoadingOverlay');
    const txt = document.getElementById('appLoadingText');
    let busyCount = 0; // reference count to support nested calls
    function show(text){
      try { if (txt && text) txt.textContent = String(text); } catch(_){ }
      busyCount++;
      if (busyCount < 1) busyCount = 1;
      if (overlay) {
        overlay.classList.remove('fade-exit','fade-exit-active');
        overlay.classList.add('fade-enter');
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('fade-enter-active'));
        overlay.setAttribute('aria-hidden', 'false');
      }
      document.body.classList.add('app-busy');
      document.documentElement.style.overflow = 'hidden';
    }
    function hide(){
      busyCount = Math.max(0, busyCount - 1);
      if (busyCount > 0) return; // still busy by another process
      if (overlay) {
        overlay.classList.remove('fade-enter','fade-enter-active');
        overlay.classList.add('fade-exit');
        requestAnimationFrame(() => overlay.classList.add('fade-exit-active'));
        setTimeout(() => { if (overlay) overlay.style.display = 'none'; }, 200);
        overlay.setAttribute('aria-hidden', 'true');
      }
      document.body.classList.remove('app-busy');
      document.documentElement.style.overflow = '';
    }
    window.bannerLoading = function(on, text){ if (on) show(text || 'Procesando...'); else hide(); };
    // Expose manual controls
    window.__appBusy = { inc: () => show(), dec: () => hide(), active: () => busyCount > 0 };
  })();
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
      try { bannerLoading(true, text || 'Cargando...'); } catch(_){ }
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
      try { bannerLoading(false); } catch(_){ }
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
    try {
      // Skip loader for forms that open in new tab or opt-out
      if (this && (this.getAttribute('target') === '_blank' || this.hasAttribute('data-no-loader'))) {
        return; // no loader
      }
      const btn = this.querySelector('button[type="submit"][data-loading-text], input[type="submit"][data-loading-text]');
      const text = (btn && btn.getAttribute('data-loading-text')) || this.getAttribute('data-loading-text') || 'Enviando datos...';
      bannerLoading(true, text);
    } catch(_) {}
  });

  // Colombia live clock (server-based)
  (function(){
    try {
      var base = <?php echo (int)($coNow->getTimestamp() * 1000); ?>; // ms desde servidor
      var elT = document.getElementById('psCoTimeText');
      var elD = document.getElementById('psCoDateText');
      if (!elT || !elD) return;
      var fmtT, fmtD;
      try {
        var fTime = new Intl.DateTimeFormat('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'America/Bogota' });
        var fDate = new Intl.DateTimeFormat('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'America/Bogota' });
        fmtT = function(ms){ return fTime.format(new Date(ms)); };
        fmtD = function(ms){ return fDate.format(new Date(ms)); };
      } catch(_){
        fmtT = function(ms){ return new Date(ms).toLocaleTimeString('es-CO'); };
        fmtD = function(ms){ return new Date(ms).toLocaleDateString('es-CO'); };
      }
      var t = base;
      function tick(){ try { elT.textContent = fmtT(t); elD.textContent = fmtD(t); } catch(_){} t += 1000; }
      tick();
      setInterval(tick, 1000);
    } catch(_){ }
  })();
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
      try {
        var noLoader = false;
        try {
          var headers = (init && init.headers) || (typeof input === 'object' && input && input.headers) || {};
          // Normalize to Map-like lookup
          if (headers && typeof headers.get === 'function') {
            noLoader = headers.get('X-No-Loader') === '1';
          } else {
            var key;
            for (key in headers) { if (key && key.toLowerCase() === 'x-no-loader') { noLoader = String(headers[key]) === '1'; break; } }
          }
        } catch(_e){}
        if (!noLoader) { loadingBar.start('Cargando...'); }
        return _fetch(input, init).finally(() => { try { if (!noLoader) loadingBar.stop(); } catch(e){} });
      } catch(e){
        return _fetch(input, init);
      }
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
      if (ok) {
        form.submit();
      } else {
        if (window.loadingBar) window.loadingBar.stop();
      }
    } else if (el.tagName === 'A') {
      e.preventDefault();
      const ok = await confirmAction({ text: msg || 'Continuar con la acción' });
      if (ok) {
        window.location.href = el.getAttribute('href');
      } else {
        if (window.loadingBar) window.loadingBar.stop();
      }
    }
  });
  
  // END main app script block
  </script>
  
  <!-- Notifications initializer -->
  <script>
    (function(){
      var fab = document.getElementById('psNotifyFab');
      var modal = document.getElementById('psNotifyModal');
      var closeBtn = document.getElementById('psNotifyClose');
      var okBtn = document.getElementById('psNotifyOk');
      var refreshBtn = document.getElementById('psNotifyRefresh');
      var backdrop = document.getElementById('psNotifyBackdrop');
      var bodyEl = document.getElementById('psNotifyBody');
      var badge = document.getElementById('psNotifyBadge');
      var __psNotifyDebounce = null;
      var __psNotifyLoading = false;
      function setRefreshLoading(on){
        __psNotifyLoading = !!on;
        try {
          if (!refreshBtn) return;
          var icon = refreshBtn.querySelector('i');
          refreshBtn.disabled = __psNotifyLoading;
          if (icon) {
            if (__psNotifyLoading) icon.classList.add('fa-spin'); else icon.classList.remove('fa-spin');
          }
        } catch(_){}
      }
      function openModal(){
        if (!modal) return;
        modal.style.display = 'block';
        requestAnimationFrame(function(){ try { modal.classList.add('ps-show'); } catch(_){ } });
      }
      function closeModal(){
        if (!modal) return;
        try { modal.classList.remove('ps-show'); } catch(_){ }
        var onEnd = function(){ try { modal.removeEventListener('transitionend', onEnd); } catch(_){ } modal.style.display = 'none'; };
        try { modal.addEventListener('transitionend', onEnd); } catch(_){ setTimeout(onEnd, 650); }
      }
      function setBadge(n){ try { if (!badge) return; if (n > 0) { badge.style.display = ''; badge.textContent = n; } else { badge.style.display = 'none'; } } catch(_){} }
      function parseYMD(s){
        if (!s) return null;
        // Accept formats like YYYY-MM-DD or with time
        var d = new Date(s);
        if (!isNaN(d)) return d;
        // Fallback: try split
        var m = String(s).match(/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})/);
        if (m) { return new Date(parseInt(m[1],10), parseInt(m[2],10)-1, parseInt(m[3],10)); }
        return null;
      }
      // Ensure server date is available globally for date-only comparisons
      try { if (typeof window !== 'undefined' && !window.SERVER_TODAY) { window.SERVER_TODAY = '<?= date('Y-m-d') ?>'; } } catch(_){}
      function getTodayStr(){
        try { return (typeof window !== 'undefined' && window.SERVER_TODAY) ? String(window.SERVER_TODAY) : '<?= date('Y-m-d') ?>'; } catch(_){ return '<?= date('Y-m-d') ?>'; }
      }
      function daysFromToday(dateStr){
        try {
          var d;
          if (dateStr instanceof Date) {
            d = new Date(dateStr.getTime());
          } else if (typeof dateStr === 'string') {
            // Parse YYYY-MM-DD as local date
            var m = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(dateStr.trim());
            if (m) {
              d = new Date(parseInt(m[1],10), parseInt(m[2],10)-1, parseInt(m[3],10));
            } else {
              d = new Date(dateStr);
            }
          } else {
            d = new Date(dateStr);
          }
          if (!d || isNaN(d.getTime())) return null;
          // Today based on server local date; read from window.SERVER_TODAY with fallback
          var todayStr = (typeof window !== 'undefined' && window.SERVER_TODAY) ? String(window.SERVER_TODAY) : '<?= date('Y-m-d') ?>';
          var tm = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(todayStr);
          var ty = parseInt(tm[1],10), tmo = parseInt(tm[2],10)-1, td = parseInt(tm[3],10);
          // Compute using UTC-normalized midnight to avoid DST/timezone drift
          var targetUTC = Date.UTC(d.getFullYear(), d.getMonth(), d.getDate());
          var todayUTC  = Date.UTC(ty, tmo, td);
          var diff = Math.round((targetUTC - todayUTC) / 86400000);
          return diff;
        } catch(_){ return null; }
      }
      async function fetchAlerts(){
        try {
          setRefreshLoading(true);
          const url = '<?= BASE_URL ?>/notifications/alerts' + '?_=' + Date.now();
          const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-No-Loader': '1' } });
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const data = await res.json();
          renderAlerts(data && data.ok ? data : { ok:false, low_stock:[], expired:[], expiring:[], threshold: 0 });
        } catch(e) { renderError(e && e.message ? e.message : 'Error de red'); }
        finally { setRefreshLoading(false); }
      }
      function chip(cls, text){ return '<span class="chip ' + cls + '">' + text + '</span>'; }
      function resolveImg(src){
        if (!src) return '';
        try {
          if (/^https?:\/\//i.test(src)) return src;
          if (src[0] === '/') return src;
          return '<?= BASE_URL ?>/uploads/' + src;
        } catch(_) { return src; }
      }
      function rowItem(id, name, sku, chips, img){
        var imgHtml = img ? ('<img src="' + resolveImg(img) + '" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid #eee;margin-right:8px;vertical-align:middle;">') : '';
        var left = '<div class="left">' + imgHtml + '<span class="name">' + name + '</span>' + (sku ? '<span class="sku">['+ sku +']</span>' : '') + '</div>';
        var right = '<div class="right">' + chips.join('') + '</div>';
        // If we have a valid id, render as an accessible anchor to product edit; otherwise a non-clickable div
        var inner = left + right;
        if (id != null && id !== '' && !isNaN(parseInt(id,10))) {
          return '<a href="<?= BASE_URL ?>/products/edit/' + parseInt(id,10) + '" class="notify-item" role="link" tabindex="0" aria-label="Editar producto ' + (name||'') + '">' + inner + '</a>';
        }
        return '<div class="notify-item" role="group" aria-label="Producto">' + inner + '</div>';
      }
      function section(variant, icon, title, count, itemsHtml){
        return (
          '<div class="notify-section ' + variant + '">' +
            '<div class="ns-header">' +
              '<div class="title"><i class="' + icon + '"></i>' + title + '</div>' +
              '<div class="count">' + count + '</div>' +
            '</div>' +
            '<div class="ns-list">' + (itemsHtml || '<div class="p-3 text-muted">Sin registros</div>') + '</div>' +
          '</div>'
        );
      }
      function renderAlerts(data){
        try {
          var low = data.low_stock || [];
          // Trust backend classification; only compute dd to show friendly chips
          var exp = (data.expired || []).slice();
          var soon = (data.expiring || []).slice();
          // One-time banner: if there are expired items, notify immediately
          try {
            var hasExpiredNow = false;
            for (var j = 0; j < exp.length; j++) {
              var dxx = daysFromToday(exp[j] && exp[j].expires_at);
              if (dxx != null && dxx <= 0) { hasExpiredNow = true; break; }
            }
            if (hasExpiredNow) {
              var key = 'ps_expired_notified';
              var todayStr = (new Date()).toDateString();
              var last = null; try { last = sessionStorage.getItem(key); } catch(_e){}
              if (last !== todayStr) {
                try { sessionStorage.setItem(key, todayStr); } catch(_e){}
                notify({ icon:'error', title:'Producto vencido', text:'Hay productos vencidos en el inventario.' });
              }
            }
          } catch(_e){}
          // UI policy: rojo < 20, amarillo 20..60, >60 no mostrar
          var lowFiltered = low.filter(function(p){
            var st = (p && p.stock != null) ? parseInt(p.stock,10) : null;
            return (st != null && st < 61);
          });
          var lowHtml = lowFiltered.map(function(p){
            var chips = [];
            var st = (p && p.stock != null) ? parseInt(p.stock,10) : null;
            if (st != null) {
              if (st < 20) {
                chips.push(chip('chip-danger chip-emph','Advertencia: este producto está próximo a acabar'));
              } else {
                chips.push(chip('chip-warn chip-emph','Advertencia: producto próximo a acabarse'));
              }
              chips.push(chip((st < 20 ? 'chip-danger' : 'chip-warn') + ' chip-emph','Quedan ' + st + ' unidades'));
            }
            return rowItem(p && p.id, (p.name||''), (p.sku||''), chips, p && p.image);
          }).join('');
          var expHtml = exp.map(function(p){
            var chips = [ chip('chip-danger','Vencido') ];
            var dd = daysFromToday(p.expires_at);
            var todayStr = getTodayStr();
            var isToday = (p && p.expires_at && String(p.expires_at).slice(0,10) === todayStr);
            if (dd != null) {
              if (dd === 0) {
                chips.push(chip('chip-danger', 'Venció hoy'));
              } else if (dd < 0) {
                var absd = Math.abs(dd);
                var dl = (absd === 1 ? 'día' : 'días');
                chips.push(chip('chip-danger', 'Venció hace ' + absd + ' ' + dl));
              }
            }
            if (dd == null && isToday) { chips.push(chip('chip-danger', 'Venció hoy')); }
            if (p.expires_at) chips.push(chip('chip-muted', p.expires_at));
            return rowItem(p && p.id, (p.name||''), (p.sku||''), chips, p && p.image);
          }).join('');
          var soonHtml = soon.map(function(p){
            var dd = daysFromToday(p.expires_at);
            var chips = [ chip('chip-warn','Por vencer') ];
            if (dd != null && dd > 0) {
              var dl2 = (dd === 1 ? 'día' : 'días');
              chips.push(chip('chip-warn','Faltan ' + dd + ' ' + dl2));
            }
            // Defensive: if dd is null but date is within 1..31 days by string compare vs today, try to compute quickly
            if (dd == null && p && p.expires_at) {
              try {
                var todayParts = getTodayStr().split('-');
                var exParts = String(p.expires_at).slice(0,10).split('-');
                var tD = Date.UTC(parseInt(todayParts[0],10), parseInt(todayParts[1],10)-1, parseInt(todayParts[2],10));
                var eD = Date.UTC(parseInt(exParts[0],10), parseInt(exParts[1],10)-1, parseInt(exParts[2],10));
                var rough = Math.round((eD - tD)/86400000);
                if (rough > 0 && rough <= 31) {
                  var dl3 = (rough === 1 ? 'día' : 'días');
                  chips.push(chip('chip-warn','Faltan ' + rough + ' ' + dl3));
                }
              } catch(_){ }
            }
            if (p.expires_at) chips.push(chip('chip-muted', p.expires_at));
            return rowItem(p && p.id, (p.name||''), (p.sku||''), chips, p && p.image);
          }).join('');
          var total = lowFiltered.length + exp.length + soon.length;
          setBadge(total);
          if (bodyEl) {
            if (total === 0) {
              bodyEl.innerHTML = '<div class="p-4 text-center text-muted">'
                + '<div style="font-size:28px; margin-bottom:8px;"><i class="far fa-bell-slash"></i></div>'
                + '<div style="font-weight:600;">Tu bandeja de notificaciones está vacía</div>'
                + '<div class="mt-1">No tienes notificaciones por ahora.</div>'
                + '</div>';
            } else {
              bodyEl.innerHTML = ''
                + section('ns-low','fas fa-box-open text-warning','Próximo a agotar', lowFiltered.length, lowHtml)
                + section('ns-expired','fas fa-times-circle text-danger','Vencidos', exp.length, expHtml)
                + section('ns-soon','fas fa-hourglass-half text-warning','Próximo a vencer', soon.length, soonHtml);
            }
          }
        } catch(e) { renderError('Error al procesar datos'); }
      }
      function renderError(msg){ if (bodyEl) bodyEl.innerHTML = '<div class="p-3 text-danger">' + (msg||'Error desconocido') + '</div>'; try { setBadge(0); } catch(_){} }
      function debouncedFetch(){ try { if (__psNotifyDebounce) clearTimeout(__psNotifyDebounce); __psNotifyDebounce = setTimeout(fetchAlerts, 500); } catch(_){} }
      function init(){
        if (fab) fab.addEventListener('click', function(){ openModal(); fetchAlerts(); });
        if (refreshBtn) refreshBtn.addEventListener('click', function(){ fetchAlerts(); });
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (okBtn) okBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
        // Fetch once on load for initial badge
        fetchAlerts().catch(function(){});
        // Refresh after any AJAX completes (e.g., ventas, productos)
        if (window.jQuery && jQuery(document) && jQuery(document).ajaxStop) {
          jQuery(document).ajaxStop(function(){ debouncedFetch(); });
        }
        // Confirm before navigating to edit product from notifications
        if (bodyEl) {
          bodyEl.addEventListener('click', function(e){
            try {
              var a = e.target && e.target.closest ? e.target.closest('a.notify-item') : null;
              if (!a) return;
              e.preventDefault();
              var nameEl = a.querySelector('.name');
              var pname = nameEl ? nameEl.textContent.trim() : 'producto';
              var prompt = '¿Quieres editar el producto "' + pname + '"?';
              var go = function(){ window.location.href = a.getAttribute('href'); };
              var onCancel = function(){
                try { if (typeof loadingBar !== 'undefined' && loadingBar && typeof loadingBar.stop === 'function') loadingBar.stop(); } catch(_){}
                try { if (typeof bannerLoading === 'function') bannerLoading(false); } catch(_){}
              };
              if (typeof confirmAction === 'function') {
                Promise.resolve(confirmAction({ text: prompt, icon: 'question', confirmText: 'Sí, editar' }))
                  .then(function(ok){ if (ok) go(); else onCancel(); })
                  .catch(function(){ onCancel(); });
              } else {
                if (window.confirm(prompt)) go(); else onCancel();
              }
            } catch(_){ /* no-op */ }
          });
        }
        // Close on ESC
        document.addEventListener('keydown', function(e){ if ((e.key === 'Escape' || e.key === 'Esc') && modal && modal.style.display === 'block') { closeModal(); } });
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
  </script>
  
  <?php $___fl = Flash::popAll(); if (!empty($___fl)): ?>
  <script>
    (function(){
      const msgs = <?php echo json_encode($___fl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      msgs.forEach(m => notify({ icon: m.type || 'info', title: m.title || '', text: m.message || '', timer: (m.timer && m.timer > 0) ? m.timer : undefined, position: m.position || undefined }));
    })();
  </script>
  <?php endif; ?>
</body>
</html>
