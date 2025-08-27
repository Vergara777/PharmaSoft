<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title d-flex align-items-center">
      <i class="fas fa-cart-plus mr-2 text-primary" aria-hidden="true"></i>
      Registrar venta
    </h3>
  <!-- Choices.js script include placed before page scripts to ensure availability -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</div>
  <div class="card-body">
    <style>
      /* Mostrar opciones vencidas en rojo en el desplegable */
      select[name="product_pick"] option[data-expired="1"] { color: #dc3545 !important; font-weight: 600; }
      /* Próximo a vencer (≤30 días) en amarillo */
      select[name="product_pick"] option[data-status="warn"] { color: #d39e00 !important; font-weight: 600; }
      /* Ok en verde */
      select[name="product_pick"] option[data-status="ok"] { color: #28a745 !important; }
      .cart-table td, .cart-table th { vertical-align: middle; }
      .icon svg { display:block; }
      .btn .icon { display:inline-flex; vertical-align:middle; margin-right:.35rem; }
      .btn .icon svg { width:1rem; height:1rem; }
      .cart-table thead th { white-space:nowrap; }
      .cart-table tfoot th, .cart-table tfoot td { border-top: 2px solid #dee2e6; }
      #cartTotal { font-weight:700; font-size:1.05rem; }
      .btnRemove svg { width:.95rem; height:.95rem; }
      .cart-table tbody tr:hover { background:#f8f9fa; }
      .cart-actions { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
      .cart-table tfoot tr { position: sticky; bottom: 0; background: #fff; }
      /* Preview panel for selected product */
      #pickPreview { display:flex; gap:12px; align-items:center; margin-top:6px; }
      #pickPreview img { width:72px; height:72px; object-fit:cover; border-radius:6px; border:1px solid #e5e5e5; display:none; }
      #pickPreview .meta { font-size:.9rem; color:#555; }
      /* Always show preview on all sizes to give immediate visual feedback */
      /* (si deseas ocultarlo en desktop, podemos volver a activar este bloque) */
      /* @media (min-width: 768px) { #pickPreview { display:none; } } */
      /* Choices.js tweaks */
      .choices__list--dropdown .choice__inner { display:flex; align-items:center; gap:8px; }
      .choice__img { width:28px; height:28px; border-radius:4px; object-fit:cover; flex:0 0 28px; border:1px solid #e5e5e5; }
      .choice__label { display:flex; align-items:center; gap:6px; }
      .choice__text { display:inline-block; }
      .choice__sku { color:#6c757d; font-size:.86rem; margin-right:6px; }
      /* Make Choices control same height as Bootstrap form-control (38px) */
      .choices { margin-bottom:0; }
      .choices__inner { min-height:38px; padding-top:.375rem; padding-bottom:.375rem; line-height:1.5; }
      .choices__input { padding-top:.25rem; padding-bottom:.25rem; }
      .choices__item--selectable .choice__inner { display:flex; align-items:center; gap:8px; }
      .choices__item.is-selected .choice__inner { font-weight:600; }
      /* Status-based colors inside Choices (lighter weights for smoother layout) */
      .stock-ok .choice__text, .choice__text.stock-ok { color:#28a745; }
      .stock-warn .choice__text, .choice__text.stock-warn { color:#d39e00; font-weight:500; }
      .stock-expired .choice__text, .choice__text.stock-expired { color:#dc3545; font-weight:500; }
      .stock-low .choice__text, .choice__text.stock-low { color:#dc3545; font-weight:500; }
      /* Reduce label spacing to bring inputs up */
      .form-row .form-group > label { margin-bottom:.2rem !important; }
      /* Normalize control heights in the row */
      @media (min-width: 768px) {
        .form-row .form-control { height:38px; padding-top:.375rem; padding-bottom:.375rem; line-height:1.5; }
        .form-row .input-group > .form-control { height:38px; }
        .form-row .input-group > .input-group-prepend > .input-group-text,
        .form-row .input-group > .input-group-append > .input-group-text { height:38px; padding-top:.375rem; padding-bottom:.375rem; line-height:1.5; margin-top:0; }
        #btnAddItem { height:38px; padding:.375rem .75rem; line-height:1.5; margin-top:0; }
        /* Fine alignment: raise qty, price and button a bit more */
        #pickQty, #pickPrice, #btnAddItem, .input-group-prepend .input-group-text { transform: translateY(-5px); }
        /* Keep category + product selectors on one line and aligned neatly */
        .form-row.pickers { display:flex; flex-wrap: nowrap; align-items: flex-start; gap: 12px; overflow: visible; }
        .form-row.pickers .pick-col { display:flex; flex-direction: column; }
        .form-row.pickers .pick-col select.form-control { height: 38px; }
        .form-row.pickers .pick-col .choices__inner { min-height: 38px; }
        .form-row.pickers .pick-col label { margin-bottom: .25rem !important; }
        /* Raise the category column using margin (no stacking context) */
        .form-row.pickers .pick-col-cat { margin-top: -56px; }
      }
      /* Category filter styles */
      #catFilter + .choices { margin-top: 0; }
      /* Choices height + alignment to match Bootstrap controls */
      .choices { margin-bottom: 0; }
      .choices__inner { min-height: 38px; padding: 0.25rem 0.5rem; display: flex; align-items: center; }
      .choices__list--single .choices__item { line-height: 1.5; }
      /* SHOW the search input for select-one to help users find products */
      .choices[data-type*=select-one] .choices__input { display: block; width: 100%; padding: .375rem .5rem; margin: 0; min-height: auto; }
      .choices__input { font-size: .95rem; }
      /* Make dropdown scroll internally, not the page */
      .choices__list--dropdown {
        max-height: 360px;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        /* Performance: isolate and hint GPU for smoother scroll */
        will-change: transform;
        transform: translateZ(0);
        backface-visibility: hidden;
        contain: content;
        /* Keep scrollbar space to avoid layout shifts */
        scrollbar-gutter: stable both-edges;
      }
      .choices.is-open .choices__list--dropdown { max-height: 360px; overflow-y: auto; }
      /* Performance: avoid rendering offscreen items when possible */
      .choices__list--dropdown .choices__item { content-visibility: auto; contain-intrinsic-size: 44px; }
      /* Only the outer dropdown should scroll to avoid double scrollbars */
      .choices__list--dropdown .choices__list { position: static; max-height: none; overflow: visible; }
      /* Ensure dropdown overlays neighbors and is not hidden */
      .choices { position: relative; }
      .choices.is-open { z-index: 1200; }
      .choices__list--dropdown { z-index: 1210; }
      /* Zero-stock choice look */
      .choices__list--dropdown .choices__item.no-stock { opacity: .55; }
      .choices__list--dropdown .choices__item.no-stock .choice__text { color: #6c757d !important; }
      .choices__list--dropdown .choices__item.no-stock .choice__img { filter: grayscale(100%); opacity: .8; }
      /* Align labels spacing */
      .form-group > label.mb-1 { margin-bottom: .25rem !important; }
      /* Centered loading overlay */
      #centerLoading { position: fixed; inset: 0; display: none; z-index: 1100; background: rgba(0,0,0,.35); align-items: center; justify-content: center; }
      #centerLoading .box { background: #fff; color: #1f2d3d; padding: 14px 18px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.25); font-weight: 700; display: inline-flex; align-items: center; gap: .6rem; }
      #centerLoading .box .spinner { width: 1rem; height: 1rem; border: 2px solid #3c8dbc; border-top-color: transparent; border-radius: 50%; animation: sp 1s linear infinite; }
      @keyframes sp { to { transform: rotate(360deg); } }
      /* Smooth open/close for item detail modal (slower, softer) */
      #itemDetailModal { opacity: 0; transition: opacity .65s ease; }
      #itemDetailModal.show { opacity: 1; }
      #itemDetailModal .modal-card { transform: translateY(10px) scale(.985); transition: transform .65s cubic-bezier(.16,1,.3,1); }
      #itemDetailModal.show .modal-card { transform: translateY(0) scale(1); }
    </style>
    <!-- Centered full-screen loader -->
    <div id="centerLoading" aria-hidden="true">
      <div class="box" role="status" aria-live="polite">
        <span class="spinner" aria-hidden="true"></span>
        <span id="centerLoadingText">Procesando...</span>
      </div>
    </div>
    <!-- Choices.js (vanilla) for rich select rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <!-- Banner: borrador detectado/restaurado -->
    <div id="draftBanner" class="alert alert-warning d-none" role="alert" style="display:none;">
      <style>
        #draftBanner .banner-actions .btn { font-weight: 700; border-width: 2px; border-radius: 999px; }
        #draftBanner .btn-continue { background: #3c8dbc; color: #fff; border-color: #2f6f92; }
        #draftBanner .btn-continue:hover { background: #357ea8; color: #fff; }
        #draftBanner .btn-products { background: #fff; color: #3c8dbc; border-color: #3c8dbc; }
        #draftBanner .btn-products:hover { background: #e8f3fb; color: #2b6d8c; }
        #draftBanner .btn-discard { background: #fff; color: #dc3545; border-color: #dc3545; }
        #draftBanner .btn-discard:hover { background: #fbe9eb; color: #b02a37; }
      </style>
      <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle mr-2 text-warning" aria-hidden="true"></i>
        <div class="flex-grow-1">
          <strong>Continuas con un borrador de venta</strong>
          <span class="badge badge-pill badge-dark ml-2" id="draftCount" title="Cantidad de ítems en el borrador">0 ítems</span>
          <div class="text-muted small mt-1">Puedes seguir editando, ir a Productos o descartar el borrador.</div>
        </div>
        <div class="ml-3 d-flex banner-actions" style="gap:.5rem;">
          <button type="button" id="btnDraftContinue" class="btn btn-sm btn-continue">
            <i class="fas fa-edit mr-1" aria-hidden="true"></i> Continuar editando
          </button>
          <a href="<?= BASE_URL ?>/products" class="btn btn-sm btn-products">
            <i class="fas fa-store mr-1" aria-hidden="true"></i> Ir a Productos
          </a>
          <button type="button" id="btnDraftDiscard" class="btn btn-sm btn-discard">
            <i class="fas fa-trash-alt mr-1" aria-hidden="true"></i> Descartar borrador
          </button>
        </div>
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= View::e($error) ?></div>
      <script>
        (function(){
          var msg = <?= json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
          try {
            if (window.Swal && Swal.fire) {
              Swal.fire({
                icon: 'error',
                title: 'Error al registrar venta',
                text: String(msg || ''),
                position: 'top-end',
                toast: true,
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false
              });
            } else if (typeof window.notify === 'function') {
              // Fallback to notify() API if available
              try { notify({ icon: 'error', title: 'Error al registrar venta', text: String(msg||''), position: 'top-end', timer: 4000 }); } catch(_){ notify('error', String(msg||'')); }
            }
          } catch(_){ /* ignore */ }
          // Highlight product selector for stock/expiry errors
          try {
            var sel = document.querySelector('select[name="product_pick"]');
            if (!sel) return;
            if (/vencid/i.test(msg) || /stock insuficiente/i.test(msg)) {
              sel.classList.add('is-invalid');
              sel.focus();
            }
          } catch(_){ }
        })();
      </script>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>/sales/store" data-loading-text="Guardando venta..." id="cartForm">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-row align-items-center pickers">
        <div class="form-group col-12 col-md-4 col-lg-4 mb-0 pick-col pick-col-cat">
          <label class="mb-1">Categoría</label>
          <small class="form-text text-muted mb-1">Aquí podrá elegir su categoría</small>
          <select id="catFilter" class="form-control">
            <option value="" disabled hidden data-placeholder="true">Filtrar categoría</option>
            <option value="all">Todas</option>
            <?php if (!empty($categories ?? [])): foreach (($categories ?? []) as $c): ?>
              <option value="<?= (int)($c['id'] ?? 0) ?>"><?= View::e($c['name'] ?? '') ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>
        <div class="form-group col-12 col-md-8 col-lg-8 mb-0 pick-col pick-col-prod">
          <label class="mb-1">Producto</label>
          <small class="form-text text-muted mb-1">Busca por nombre o SKU. Escribe aquí el nombre del producto…</small>
          <select name="product_pick" class="form-control">
            <option value="">Seleccione un producto</option>
            <?php
              $today = (new DateTimeImmutable('today'))->format('Y-m-d');
              $catMap = [];
              if (!empty($categories ?? [])) { foreach (($categories ?? []) as $c) { $catMap[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? ''); } }
              $supMap = [];
              if (!empty($suppliers ?? [])) { foreach (($suppliers ?? []) as $s) { $supMap[(int)($s['id'] ?? 0)] = (string)($s['name'] ?? ''); } }
              foreach ($products as $pr):
                $expires = $pr['expires_at'] ?? null;
                $isExpired = !empty($expires) && $expires < $today;
                $label = $pr['sku'] . ' - ' . $pr['name'];
                // Status: expired takes precedence; otherwise by stock thresholds
                $status = 'ok';
                if ($isExpired) { $label .= ' (Vencido)'; $status = 'expired'; }
                else {
                  $stk = (int)($pr['stock'] ?? 0);
                  if ($stk <= 20) { $status = 'low'; }
                  elseif ($stk <= 50) { $status = 'warn'; }
                  else { $status = 'ok'; }
                }
                $catId = (int)($pr['category_id'] ?? 0);
                $supId = (int)($pr['supplier_id'] ?? 0);
                $catName = isset($catMap[$catId]) && $catMap[$catId] !== '' ? $catMap[$catId] : '';
                $supName = isset($supMap[$supId]) && $supMap[$supId] !== '' ? $supMap[$supId] : '';
            ?>
              <option value="<?= View::e($pr['id']) ?>" data-sku="<?= View::e($pr['sku']) ?>" data-name="<?= View::e($pr['name']) ?>" data-description="<?= View::e($pr['description'] ?? '') ?>" data-price="<?= View::e((int)($pr['price'] ?? 0)) ?>" data-image="<?= View::e($pr['image'] ?? '') ?>" data-expired="<?= $isExpired ? '1' : '0' ?>" data-status="<?= View::e($status) ?>" data-stock="<?= View::e($pr['stock'] ?? 0) ?>" data-category-id="<?= $catId ?>" data-category-name="<?= View::e($catName) ?>" data-supplier-id="<?= $supId ?>" data-supplier-name="<?= View::e($supName) ?>" title="<?= $isExpired ? 'Producto vencido' : ('Stock: ' . (int)($pr['stock'] ?? 0)) ?>">
                <?= View::e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted d-block d-md-none">Los productos vencidos se muestran en rojo. Al seleccionarlos, verás un aviso y no podrás agregarlos.</small>
          <div id="pickPreview">
            <img id="pickImg" alt="Imagen producto">
            <div class="meta">
              <div><strong id="pickName">—</strong> <span id="pickBadges"></span></div>
            </div>
          </div>
        </div>
      </div>
      <!-- Second row: qty, price and add button -->
      <div class="form-row align-items-center mt-2">
        <div class="form-group col-6 col-md-2 col-lg-2 mb-0">
          <label class="mb-1">Cantidad</label>
          <input type="number" min="1" step="1" id="pickQty" value="1" class="form-control">
        </div>
        <div class="form-group col-6 col-md-2 col-lg-2 mb-0">
          <label class="mb-1">Precio unitario</label>
          <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
            <input type="number" min="0" step="1" id="pickPrice" value="0" class="form-control">
          </div>
        </div>
        <div class="form-group col-12 col-md-2 col-lg-2 mb-0">
          <label class="d-none d-md-block mb-1">&nbsp;</label>
          <button type="button" class="btn btn-success btn-block w-100" id="btnAddItem">
            <i class="fas fa-plus mr-1" aria-hidden="true"></i>
            Agregar
          </button>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Nombre del cliente</label>
          <input type="text" name="customer_name" class="form-control" placeholder="Luis Pérez">
        </div>
        <div class="form-group col-md-4">
          <label>Teléfono</label>
          <input type="text" name="customer_phone" class="form-control" placeholder="300 000 0000">
        </div>
        <div class="form-group col-md-4">
          <label>Correo electrónico</label>
          <input type="email" name="customer_email" class="form-control" placeholder="luis@example.com">
        </div>
      </div>

      <!-- Importar items (CSV) movido a Ventas del día -->

      <div class="table-responsive">
        <table class="table table-striped cart-table">
          <thead>
            <tr>
              <th>#</th>
              <th>SKU</th>
              <th>Producto</th>
              <th class="text-right">Cant.</th>
              <th class="text-right">P. Unit</th>
              <th class="text-right">Importe</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="cartBody"></tbody>
          <tfoot>
            <tr>
              <th colspan="5" class="text-right">Total</th>
              <th class="text-right" id="cartTotal">$0</th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="cart-actions mt-2">
      <button class="btn btn-primary">
        <i class="fas fa-save mr-1" aria-hidden="true"></i>
        Guardar venta
      </button>
      <button type="button" class="btn btn-outline-primary" id="btnSaveDraft">
        <i class="fas fa-bookmark mr-1" aria-hidden="true"></i>
        Guardar borrador
      </button>
      <a class="btn btn-outline-secondary" id="btnBackToProducts" href="<?= BASE_URL ?>/products">
        <i class="fas fa-store mr-1" aria-hidden="true"></i>
        Seguir viendo productos
      </a>
      <a class="btn btn-secondary" href="<?= BASE_URL ?>/sales">
        <i class="fas fa-times mr-1" aria-hidden="true"></i>
        Cancelar
      </a>
      <button type="button" id="btnClearCart" class="btn btn-outline-danger">
        <i class="fas fa-trash mr-1" aria-hidden="true"></i>
        Vaciar carrito
      </button>
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    // Centered loader helper with minimum visible duration (default 4000ms)
    var __clWrap = document.getElementById('centerLoading');
    var __clText = document.getElementById('centerLoadingText');
    var __clMin = 4000; var __clStarted = 0; var __clTimer = null; var __clActive = false;
    function clShow(text, minMs){
      try { if (text) __clText.textContent = String(text); else __clText.textContent = 'Procesando...'; } catch(_){ }
      if (typeof minMs === 'number' && minMs >= 0) { __clMin = Math.max(0, Math.floor(minMs)); }
      if (__clTimer) { try { clearTimeout(__clTimer); } catch(_){ } __clTimer = null; }
      if (__clWrap) { __clWrap.style.display = 'flex'; __clWrap.setAttribute('aria-hidden','false'); }
      __clActive = true; __clStarted = Date.now();
    }
    function clHide(){
      if (!__clActive) { if (__clWrap) { __clWrap.style.display = 'none'; __clWrap.setAttribute('aria-hidden','true'); } return; }
      var elapsed = Math.max(0, Date.now() - __clStarted);
      var remaining = Math.max(0, __clMin - elapsed);
      if (remaining > 0){ __clTimer = setTimeout(function(){ __clActive=false; if (__clWrap){ __clWrap.style.display='none'; __clWrap.setAttribute('aria-hidden','true'); } }, remaining); }
      else { __clActive=false; if (__clWrap){ __clWrap.style.display='none'; __clWrap.setAttribute('aria-hidden','true'); } }
    }
    window.centerLoading = { show: clShow, hide: clHide };
    // CSS.escape polyfill (MDN) to ensure compatibility across browsers
    if (!window.CSS) window.CSS = {};
    if (typeof window.CSS.escape !== 'function') {
      window.CSS.escape = function(value) {
        if (arguments.length === 0) throw new TypeError('`CSS.escape` requires an argument.');
        var string = String(value);
        var length = string.length;
        var index = -1;
        var codeUnit;
        var result = '';
        var firstCodeUnit = string.charCodeAt(0);
        while (++index < length) {
          codeUnit = string.charCodeAt(index);
          // Note: there’s no need to special-case astral symbols, surrogate
          // pairs, or lone surrogates.
          if (codeUnit == 0x0000) {
            result += '\\FFFD';
            continue;
          }
          if (
            (codeUnit >= 0x0001 && codeUnit <= 0x001F) ||
            codeUnit == 0x007F ||
            (index == 0 && codeUnit >= 0x0030 && codeUnit <= 0x0039) ||
            (index == 1 && codeUnit >= 0x0030 && codeUnit <= 0x0039 && firstCodeUnit == 0x002D)
          ) {
            result += '\\' + codeUnit.toString(16) + ' ';
            continue;
          }
          if (
            index == 0 && codeUnit == 0x002D && length == 1 ||
            codeUnit >= 0x0080 ||
            codeUnit == 0x002D ||
            codeUnit == 0x005F ||
            (codeUnit >= 0x0030 && codeUnit <= 0x0039) ||
            (codeUnit >= 0x0041 && codeUnit <= 0x005A) ||
            (codeUnit >= 0x0061 && codeUnit <= 0x007A)
          ) {
            result += string.charAt(index);
            continue;
          }
          result += '\\' + string.charAt(index);
        }
        return result;
      };
    }
    const sel = document.querySelector('select[name="product_pick"]');
    const qty = document.getElementById('pickQty');
    const price = document.getElementById('pickPrice');
    var pv = {
      wrap: document.getElementById('pickPreview'),
      img: document.getElementById('pickImg'),
      name: document.getElementById('pickName'),
      badges: document.getElementById('pickBadges')
    };
    var catSel = document.getElementById('catFilter');
    const addBtn = document.getElementById('btnAddItem');
    const body = document.getElementById('cartBody');
    const totalEl = document.getElementById('cartTotal');
    const form = document.getElementById('cartForm');
    const submitBtn = form ? form.querySelector('button[type="submit"], button:not([type])') : null;
    // Import CSV controls fueron movidos a Ventas del día

    function centerNotify(optsOrType, maybeTitle, maybeText){
      try {
        if (typeof window.notify === 'function') {
          if (typeof optsOrType === 'string') {
            return notify({ icon: optsOrType, title: maybeTitle || '', text: maybeText || '', position: 'top-end', timer: 4000 });
          }
          var o = optsOrType || {}; o.position = 'top-end'; if (typeof o.timer === 'undefined') o.timer = 4000;
          return notify(o);
        }
      } catch(_){}}
      var o2 = (typeof optsOrType === 'string') ? { icon: optsOrType, title: maybeTitle||'', text: maybeText||'' } : (optsOrType||{});
      if (window.Swal && Swal.fire) {
        Swal.fire({ icon: o2.icon || 'info', title: o2.title || '', text: o2.text || '', position: 'top-end', toast: true, timer: (typeof o2.timer==='number'?o2.timer:4000), timerProgressBar: true, showConfirmButton: false, showClass: { popup: 'swal2-show' }, hideClass: { popup: 'swal2-hide' } });
      }
    }

    // Pretty toast with fixed 4s duration (error/warn/info) for critical messages
    function centerToast4(type, title, text){
      try {
        if (window.Swal && Swal.fire) {
          return Swal.fire({ icon: type || 'info', title: title || '', text: text || '', position: 'top-end', toast: true, timer: 6000, timerProgressBar: true, showConfirmButton: false, showClass: { popup: 'swal2-show' }, hideClass: { popup: 'swal2-hide' } });
        }
        if (typeof window.notify === 'function') {
          return notify({ icon: type || 'info', title: title || '', text: text || '', position: 'top-end', timer: 6000 });
        }
      } catch(_){ }
    }

    // Pretty confirm helper (Swal -> psConfirm -> native)
    function confirmPretty(opts){
      opts = opts || {};
      var title = opts.title || 'Confirmación';
      var text = opts.text || '¿Deseas continuar?';
      var ok = opts.ok || 'Sí';
      var cancel = (typeof opts.cancel === 'undefined') ? 'No' : opts.cancel; // allow false to hide cancel
      try {
        if (window.Swal && Swal.fire) {
          var swalOpts = { title: title, text: text, icon: opts.icon || 'question', showCancelButton: cancel !== false, confirmButtonText: ok };
          if (cancel !== false) swalOpts.cancelButtonText = cancel;
          return Swal.fire(swalOpts).then(function(r){ return !!(r && r.isConfirmed); });
        }
        if (window.psConfirm) { return window.psConfirm({ title: title, text: text, ok: ok, cancel: cancel }); }
      } catch(_){ }
      return Promise.resolve(confirm(text));
    }

    function fmtCOP(n){
      try { return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0, maximumFractionDigits:0 }).format(n||0); }
      catch(e){ var v = Math.round(n||0); return '$' + String(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    }
    // Draft autosave
    var DRAFT_KEY = 'pharmasoft_sales_draft';
    function serializeCart(){
      var items = [];
      body.querySelectorAll('tr').forEach(function(tr){
        try {
          var pid = parseInt((tr.querySelector('input[name="product_id[]"]')||{}).value||'0',10)||0;
          if (!pid) return;
          var qtyEl = tr.querySelector('input[name="qty[]"]');
          var prEl = tr.querySelector('input[name="unit_price[]"]');
          var nameText = (tr.querySelector('td:nth-child(3) span')||{}).textContent || '';
          var skuText = (tr.querySelector('td:nth-child(2)')||{}).textContent || '';
          var imgEl = tr.querySelector('td:nth-child(3) img');
          var img = imgEl ? (imgEl.getAttribute('src')||'').split('/').pop() : '';
          // Extract category/supplier robustly regardless of description presence
          var smEls = tr.querySelectorAll('td:nth-child(3) small');
          var catTxt = '';
          var supTxt = '';
          smEls.forEach(function(sm){
            var t = (sm.textContent || '').trim();
            if (!t) return;
            var low = t.toLowerCase();
            if (low.indexOf('categoría:') === 0) catTxt = t.replace(/^Categoría:\s*/i,'').trim();
            if (low.indexOf('proveedor:') === 0) supTxt = t.replace(/^Proveedor:\s*/i,'').trim();
          });
          items.push({
            product_id: pid,
            sku: skuText.trim(),
            name: nameText.trim(),
            qty: Math.max(1, parseInt((qtyEl&&qtyEl.value)||'1',10)||1),
            unit_price: Math.max(0, Math.round(parseFloat((prEl&&prEl.value)||'0')||0)),
            image: img,
            category_name: catTxt,
            supplier_name: supTxt
          });
        } catch(_){ }
      });
      return items;
    }
    function saveDraft(){
      try {
        var items = serializeCart();
        if (items.length > 0) localStorage.setItem(DRAFT_KEY, JSON.stringify(items));
        else localStorage.removeItem(DRAFT_KEY);
      } catch(_){ }
    }
    function loadDraft(){
      try {
        var raw = localStorage.getItem(DRAFT_KEY);
        if (!raw) return 0;
        var arr = JSON.parse(raw||'[]')||[];
        if (Array.isArray(arr) && arr.length){ arr.forEach(function(it){ addItemFromData(it||{}); }); return arr.length; }
        return 0;
      } catch(_){ }
      return 0;
    }
    function clearDraft(){ try { localStorage.removeItem(DRAFT_KEY); } catch(_){ } }

    function recalc(){
      let t = 0;
      body.querySelectorAll('tr').forEach(tr => {
        const q = parseFloat(tr.querySelector('input[name="qty[]"]').value || '0');
        const p = Math.round(parseFloat(tr.querySelector('input[name="unit_price[]"]').value || '0'));
        const imp = q * p;
        tr.querySelector('.line-import').textContent = fmtCOP(imp);
        t += imp;
      });
      totalEl.textContent = fmtCOP(t);
      toggleSubmit();
      saveDraft();
      try { if (typeof setDraftBannerCount === 'function') setDraftBannerCount(); } catch(_){ }
      // Refresh global cart badge/modal instantly if available
      try { if (window.psCart && typeof window.psCart.refresh === 'function') window.psCart.refresh(); } catch(_){ }
    }
    function toggleSubmit(){
      if (!submitBtn) return;
      const hasItems = !!body.querySelector('tr');
      submitBtn.disabled = !hasItems;
      submitBtn.classList.toggle('disabled', !hasItems);
      submitBtn.title = hasItems ? '' : 'Agrega al menos un producto';
      var saveBtn = document.getElementById('btnSaveDraft');
      if (saveBtn) { saveBtn.disabled = !hasItems; saveBtn.classList.toggle('disabled', !hasItems); }
    }
    function updatePickerFromSelection(){
      // Resolve selected option robustly (works with Choices.js)
      let opt = null, cp = null;
      if (sel) {
        const val = sel.value;
        if (val) { opt = sel.querySelector('option[value="' + CSS.escape(val) + '"]'); }
        if (!opt) { opt = sel.options[sel.selectedIndex]; }
        try {
          if (typeof __choicesInst !== 'undefined' && __choicesInst) {
            var arr = __choicesInst.getValue(); // array of selected items
            var selChoice = Array.isArray(arr) ? (arr[0] || null) : (arr || null);
            if (selChoice && selChoice.customProperties) cp = selChoice.customProperties;
          }
        } catch(_){ }
      }
      if ((!opt || !opt.value) && !cp) {
        if (pv.img) { pv.img.style.display = 'none'; }
        if (pv.name) pv.name.textContent = '—';
        return;
      }
      const dpAttr = opt ? opt.getAttribute('data-price') : null;
      const dp = (dpAttr !== null) ? dpAttr : (cp ? cp.price : null);
      const dsAttr = opt ? (opt.getAttribute('data-stock') || '') : '';
      const ds = (dsAttr !== '' ? parseInt(dsAttr, 10) : (cp && cp.stock !== '' ? parseInt(cp.stock, 10) : 0)) || 0;
      const status = (opt && opt.getAttribute('data-status')) || (cp ? cp.status : 'ok') || 'ok';
      const pidNow = parseInt((opt && opt.value) ? opt.value : (cp && cp.id ? cp.id : '0'), 10) || 0;
      const nameTxt = (opt && (opt.getAttribute('data-name') || opt.textContent)) || (cp ? cp.name : '') || '';
      if (dp !== null) {
        const v = Math.round(parseFloat(String(dp) || '0') || 0);
        price.value = String(v);
      }
      if (pv.name) pv.name.textContent = String(nameTxt).trim();
      if (pv.badges) pv.badges.innerHTML = badgeFor(status) + ' ' + stockPreviewBadge(status, ds);
      const imgName = (opt && opt.getAttribute('data-image')) || (cp ? cp.image : '') || '';
      if (pv.img) {
        if (imgName) { pv.img.src = '<?= BASE_URL ?>/uploads/' + imgName; pv.img.style.display = ''; }
        else { pv.img.removeAttribute('src'); pv.img.style.display = 'none'; }
      }
      // Clamp qty to stock immediately
      let q = Math.max(1, parseInt(qty.value || '1', 10));
      let origQ = q;
      if (ds > 0 && q > ds) {
        q = ds; qty.value = String(q);
        centerToast4('error','Stock insuficiente','Disponible: ' + ds + ', solicitado: ' + origQ + '. Se ajustó a ' + ds + '.');
      }
      // Prompt on selection (only once per product within a short window)
      try {
        window.__selectPromptGuard = window.__selectPromptGuard || { pid: 0, ts: 0 };
        var now = Date.now();
        var guardOk = !(window.__selectPromptGuard.pid === pidNow && (now - window.__selectPromptGuard.ts) < 1500);
        if (pidNow && guardOk) {
          window.__selectPromptGuard.pid = pidNow; window.__selectPromptGuard.ts = now;
          if (status === 'expired') {
            confirmPretty({
              icon: 'error',
              title: 'Producto vencido',
              text: 'No se puede continuar agregar el producto porque está vencido.',
              ok: 'Aceptar',
              cancel: false
            }).then(function(){
              // Clear selection on acknowledge
              if (sel) {
                sel.value = '';
                if (typeof __choicesInst !== 'undefined' && __choicesInst) {
                  __choicesInst.removeActiveItems();
                  __choicesInst.setChoiceByValue('');
                }
                updatePickerFromSelection();
              }
            });
          } else if (status === 'warn') {
            confirmPretty({
              icon: 'warning',
              title: 'Próximo a vencer',
              text: 'Este producto está próximo a vencer. ¿Deseas continuar?',
              ok: 'Aceptar',
              cancel: 'Cancelar'
            }).then(function(yes){ if (!yes && sel) { sel.value = ''; if (typeof __choicesInst !== 'undefined' && __choicesInst) { __choicesInst.removeActiveItems(); __choicesInst.setChoiceByValue(''); } updatePickerFromSelection(); }});
          }
        }
      } catch(_){ }
    }
    function badgeFor(status){
      if (status === 'expired') return '<span class="badge badge-danger ml-2"><i class="fas fa-ban mr-1" aria-hidden="true"></i>Vencido</span>';
      if (status === 'low') return '<span class="badge badge-danger ml-2"><i class="fas fa-exclamation-circle mr-1" aria-hidden="true"></i>Stock bajo</span>';
      if (status === 'warn') return '<span class="badge badge-warning ml-2"><i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i>Stock medio</span>';
      return '<span class="badge badge-success ml-2"><i class="fas fa-check mr-1" aria-hidden="true"></i>OK</span>';
    }
    // Preview-only: show stock quantity with color-coded label next to the name
    function stockPreviewBadge(status, qty){
      var q = (typeof qty === 'number' ? qty : parseInt(qty||'0',10) || 0);
      var cls = 'badge-success';
      var txt = 'Stock: ' + q;
      if (status === 'low') { cls = 'badge-danger'; txt = 'Stock bajo: ' + q; }
      else if (status === 'warn') { cls = 'badge-warning'; txt = 'Próximo a acabarse: ' + q; }
      else if (status === 'expired') { cls = 'badge-danger'; txt = 'Vencido'; }
      return '<span class="badge ml-2 ' + cls + '">' + txt + '</span>';
    }
    // Append item to cart from data (used for localStorage pending cart)
    function addItemFromData(d){
      var pid = parseInt((d && d.product_id) || '0', 10) || 0;
      if (!pid) return;
      var sku = d.sku || '';
      var name = d.name || '';
      var desc = d.description || '';
      var status = (d.status || 'ok');
      var stock = parseInt(d.stock || '0', 10) || 0;
      var imgName = d.image || '';
      var catName = d.category_name || '';
      var supName = d.supplier_name || '';
      var q = Math.max(1, parseInt(d.qty || '1', 10) || 1);
      if (stock > 0 && q > stock) q = stock;
      var p = Math.max(0, Math.round(parseFloat(d.unit_price || '0') || 0));
      // build row (same layout as addItem)
      var tr = document.createElement('tr');
      var idx = body.querySelectorAll('tr').length + 1;
      var imgHtml = imgName ? `<img src="<?= BASE_URL ?>/uploads/${imgName}" alt="${name}" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #e5e5e5;margin-right:10px;vertical-align:middle;">` : '';
      tr.innerHTML = `
        <td class="align-middle">${idx}</td>
        <td class="align-middle">${sku}</td>
        <td class="align-middle" title="Precio: ${fmtCOP(p)} | Stock: ${isNaN(stock)?0:stock}">${imgHtml}<div class="d-flex flex-column"><span>${name} ${badgeFor(status)}</span>${desc ? `<small class=\"text-muted\">${desc}</small>` : ''}${catName ? `<small class=\"text-muted\">Categoría: ${catName}</small>` : ''}${supName ? `<small class=\"text-muted\">Proveedor: ${supName}</small>` : ''}</div></td>
        <td class="text-right"><input type="number" class="form-control form-control-sm text-right" name="qty[]" min="1" step="1" value="${q}"></td>
        <td class="text-right"><input type="number" class="form-control form-control-sm text-right" name="unit_price[]" min="0" step="1" value="${p}"></td>
        <td class="text-right line-import">$0</td>
        <td>
          <input type="hidden" name="product_id[]" value="${pid}">
          <button type="button" class="btn btn-sm btn-info btnRowInfo" title="Ver detalles">
            <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
            Detalles
          </button>
          <button type="button" class="btn btn-sm btn-danger btnRemove">
            <i class="fas fa-trash-alt mr-1" aria-hidden="true"></i>
            Quitar
          </button>
        </td>`;
      body.appendChild(tr);
      // Expose stock/name/sku for later validations (submit guard)
      try { tr.setAttribute('data-stock', String(isNaN(stock)?0:stock)); tr.setAttribute('data-sku', sku); tr.setAttribute('data-name', name); } catch(_){ }
      var qtyInput = tr.querySelector('input[name="qty[]"]');
      qtyInput.max = Number.isFinite(stock) && stock > 0 ? String(stock) : '';
      function enforceRowQty(){
        let v = parseInt(qtyInput.value || '1', 10);
        let req = v;
        if (Number.isFinite(stock) && stock > 0 && v > stock) {
          v = stock;
          centerToast4('error','Stock insuficiente','Disponible: ' + stock + ', solicitado: ' + req + '. Se ajustó a ' + stock + '.');
        }
        if (v < 1 || isNaN(v)) { v = 1; }
        qtyInput.value = String(v);
        recalc();
      }
      qtyInput.addEventListener('input', enforceRowQty);
      qtyInput.addEventListener('change', enforceRowQty);
      tr.querySelector('input[name="unit_price[]"]').addEventListener('input', recalc);
      var btnInfo = tr.querySelector('.btnRowInfo');
      if (btnInfo) {
        btnInfo.addEventListener('click', function(){
          const qNow = parseInt(tr.querySelector('input[name="qty[]"]').value || '0', 10) || 0;
          const pNow = Math.round(parseFloat(tr.querySelector('input[name="unit_price[]"]').value || '0')) || 0;
          const impNow = qNow * pNow;
          showItemDetails({ sku: sku, name: name, img: imgName ? '<?= BASE_URL ?>/uploads/' + imgName : '', desc: desc, qty: qNow, unit: pNow, importe: impNow, category: catName, supplier: supName });
        });
      }
      tr.querySelector('.btnRemove').addEventListener('click', function(){
        // Centered loading with minimum 4–5s
        try { centerLoading.show('Quitando...', 4000); } catch(_){ }
        // Remove immediately; keep loader visible for at least min time
        tr.remove();
        resequence();
        recalc();
        centerNotify('info','Artículo quitado','Se quitó "' + name + '" del carrito.');
        try { centerLoading.hide(); } catch(_){ }
      });
      recalc();
    }
    function addItem(){
      // Centered loading with minimum 4–6 seconds (using 4000ms minimum)
      try { centerLoading.show('Agregando...', 4000); } catch(_){ }
      // Resolve selected option robustly (in case of Choices.js)
      let opt = null; let cp = null;
      if (sel) {
        const val = sel.value;
        if (val) { opt = sel.querySelector('option[value="' + CSS.escape(val) + '"]'); }
        if (!opt) { opt = sel.options[sel.selectedIndex]; }
        try {
          if (typeof __choicesInst !== 'undefined' && __choicesInst) {
            var arr = __choicesInst.getValue();
            var selChoice = Array.isArray(arr) ? (arr[0] || null) : (arr || null);
            if (selChoice && selChoice.customProperties) cp = selChoice.customProperties;
          }
        } catch(_){ }
      }
      // Defensive: block zero-stock selection
      try {
        var st0 = 0;
        if (opt && opt.hasAttribute('data-stock')) st0 = parseInt(opt.getAttribute('data-stock')||'0', 10) || 0;
        else if (cp && typeof cp.stock !== 'undefined') st0 = parseInt(cp.stock||'0', 10) || 0;
        if (st0 <= 0) {
          try { centerLoading.hide(); } catch(_){ }
          // Clear selection to placeholder
          if (typeof __choicesInst !== 'undefined' && __choicesInst) {
            if (typeof __choicesInst.removeActiveItems === 'function') __choicesInst.removeActiveItems();
            if (typeof __choicesInst.setChoiceByValue === 'function') __choicesInst.setChoiceByValue('');
          }
          if (sel) { sel.value = ''; updatePickerFromSelection(); }
          centerToast4('error','Sin stock','Este producto no tiene stock.');
          return;
        }
      } catch(_){ }
      const pid = parseInt(opt && opt.value ? opt.value : '0', 10);
      if (!pid) { centerNotify('warning','Aviso','Selecciona un producto'); try { centerLoading.hide(); } catch(_){ } return; }
      // Status detection regardless of native <option> or Choices.js
      var status = (opt && opt.getAttribute('data-status')) || (cp ? cp.status : 'ok') || 'ok';
      // Handle expired: show blocking modal and do not add
      if (status === 'expired') {
        // Switch to canceling state centered; shorter min time
        try { centerLoading.show('Cancelando...', 1500); } catch(_){ }
        try { centerLoading.hide(); } catch(_){ }
        return void confirmPretty({ icon:'error', title:'Producto vencido', text:'No se puede continuar agregar el producto porque está vencido.', ok:'Aceptar', cancel:false })
          .then(function(){
            // Clear selection after acknowledge to avoid accidental re-add
            if (sel) {
              sel.value = '';
              if (typeof __choicesInst !== 'undefined' && __choicesInst) {
                __choicesInst.removeActiveItems();
                __choicesInst.setChoiceByValue('');
              }
              updatePickerFromSelection();
            }
          });
      }
      // Handle warn: ask user before proceeding
      if (status === 'warn') {
        try { centerLoading.hide(); } catch(_){ }
        return void confirmPretty({ icon:'warning', title:'Próximo a vencer', text:'Este producto está próximo a vencer. ¿Deseas continuar?', ok:'Aceptar', cancel:'Cancelar' })
          .then(function(yes){ if (yes) actuallyAdd(); });
      }
      // OK -> add directly
      actuallyAdd();

      function actuallyAdd(){
        const sku = (opt && (opt.getAttribute('data-sku'))) || (cp ? cp.sku : '') || '';
        const name = (opt && (opt.getAttribute('data-name') || opt.textContent.trim())) || (cp ? cp.name : '') || '';
        const desc = (opt && (opt.getAttribute('data-description'))) || (cp ? cp.description : '') || '';
        const stockRaw = (opt && opt.getAttribute('data-stock'));
        const stock = (stockRaw === null || stockRaw === '') ? NaN : parseInt(stockRaw, 10);
        const imgName = (opt && opt.getAttribute('data-image')) || (cp ? cp.image : '') || '';
        const catName = (opt && opt.getAttribute('data-category-name')) || (cp ? (cp.category_name || '') : '') || '';
        const supName = (opt && opt.getAttribute('data-supplier-name')) || (cp ? (cp.supplier_name || '') : '') || '';
        let q = Math.max(1, parseInt(qty.value || '1', 10));
        let origQ2 = q;
        if (Number.isFinite(stock) && stock > 0 && q > stock) { centerToast4('error','Stock insuficiente','Disponible: ' + stock + ', solicitado: ' + origQ2 + '. Se ajustó a ' + stock + '.'); q = stock; }
        const p = Math.max(0, Math.round(parseFloat(price.value || '0') || 0));
        const tr = document.createElement('tr');
        const idx = body.querySelectorAll('tr').length + 1;
        const imgHtml = imgName ? `<img src="<?= BASE_URL ?>/uploads/${imgName}" alt="${name}" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #e5e5e5;margin-right:10px;vertical-align:middle;">` : '';
        tr.innerHTML = `
          <td class="align-middle">${idx}</td>
          <td class="align-middle">${sku}</td>
          <td class="align-middle" title="Precio: ${fmtCOP(p)} | Stock: ${isNaN(stock)?0:stock}">${imgHtml}<div class="d-flex flex-column"><span>${name} ${badgeFor(status)}</span>${desc ? `<small class=\"text-muted\">${desc}</small>` : ''}${catName ? `<small class=\"text-muted\">Categoría: ${catName}</small>` : ''}${supName ? `<small class=\"text-muted\">Proveedor: ${supName}</small>` : ''}</div></td>
          <td class="text-right"><input type="number" class="form-control form-control-sm text-right" name="qty[]" min="1" step="1" value="${q}"></td>
          <td class="text-right"><input type="number" class="form-control form-control-sm text-right" name="unit_price[]" min="0" step="1" value="${p}"></td>
          <td class="text-right line-import">$0</td>
          <td>
            <input type="hidden" name="product_id[]" value="${pid}">
            <button type="button" class="btn btn-sm btn-info btnRowInfo" title="Ver detalles">
              <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
              Detalles
            </button>
            <button type="button" class="btn btn-sm btn-danger btnRemove">
              <i class="fas fa-trash-alt mr-1" aria-hidden="true"></i>
              Quitar
            </button>
          </td>`;
        body.appendChild(tr);
        // Expose stock/name/sku for later validations (submit guard)
        try { tr.setAttribute('data-stock', String(isNaN(stock)?0:stock)); tr.setAttribute('data-sku', sku); tr.setAttribute('data-name', name); } catch(_){ }
        // Enforce max stock
        var qtyInput = tr.querySelector('input[name="qty[]"]');
        qtyInput.max = Number.isFinite(stock) && stock > 0 ? String(stock) : '';
        function enforceRowQty2(){
          let v = parseInt(qtyInput.value || '1', 10);
          let req = v;
          if (Number.isFinite(stock) && stock > 0 && v > stock) {
            v = stock;
            centerToast4('error','Stock insuficiente','Disponible: ' + stock + ', solicitado: ' + req + '. Se ajustó a ' + stock + '.');
          }
          if (v < 1 || isNaN(v)) { v = 1; }
          qtyInput.value = String(v);
          recalc();
        }
        qtyInput.addEventListener('input', enforceRowQty2);
        qtyInput.addEventListener('change', enforceRowQty2);
        tr.querySelector('input[name="unit_price[]"]').addEventListener('input', recalc);
        var btnInfo = tr.querySelector('.btnRowInfo');
        if (btnInfo) {
          btnInfo.addEventListener('click', function(){
            const qNow = parseInt(tr.querySelector('input[name="qty[]"]').value || '0', 10) || 0;
            const pNow = Math.round(parseFloat(tr.querySelector('input[name="unit_price[]"]').value || '0')) || 0;
            const impNow = qNow * pNow;
            showItemDetails({ sku: sku, name: name, img: imgName ? '<?= BASE_URL ?>/uploads/' + imgName : '', desc: desc, qty: qNow, unit: pNow, importe: impNow, category: catName, supplier: supName });
          });
        }
        tr.querySelector('.btnRemove').addEventListener('click', function(){
          try { centerLoading.show('Quitando...', 4000); } catch(_){ }
          tr.remove(); resequence(); recalc();
          centerNotify('info','Artículo quitado','Se quitó "' + name + '" del carrito.');
          try { centerLoading.hide(); } catch(_){ }
        });
        recalc();
        centerNotify('success', 'Agregado', 'Se agregó "' + name + '" (x' + q + ') al carrito.');
        try { centerLoading.hide(); } catch(_){ }
      }
    }
    // addBySkuQuantityPrice solo disponible en Ventas del día
    function resequence(){
      let i = 1;
      body.querySelectorAll('tr').forEach(tr => { tr.firstElementChild.textContent = i++; });
    }
    addBtn && addBtn.addEventListener('click', addItem);
    // Import CSV handlers movidos a Ventas del día

    // Modal ligero para detalles del renglón
    var __idmWrap, __idmName, __idmSku, __idmQty, __idmUnit, __idmImp, __idmImg, __idmDesc, __idmDescRow, __idmCat, __idmSup;
    function ensureItemDetailModal(){
      if (__idmWrap) return;
      var wrap = document.createElement('div');
      wrap.id = 'itemDetailModal';
      wrap.style.position = 'fixed';
      wrap.style.left = 0; wrap.style.top = 0; wrap.style.right = 0; wrap.style.bottom = 0;
      wrap.style.background = 'rgba(0,0,0,0.45)';
      wrap.style.display = 'none';
      wrap.style.alignItems = 'center';
      wrap.style.justifyContent = 'center';
      wrap.style.zIndex = 1050;
      wrap.innerHTML = '\
        <div class="modal-card" style="background:#fff; max-width:560px; width:92%; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,.2); overflow:hidden;">\
          <div style="padding:14px 18px; border-bottom:1px solid #eee; display:flex; align-items:center;">\
            <h5 style="margin:0; font-weight:600;">Detalle del artículo</h5>\
            <button id="idmClose" type="button" class="btn btn-sm btn-light ml-auto">Cerrar</button>\
          </div>\
          <div style="padding:16px;">\
            <div style="display:flex; gap:12px; align-items:flex-start;">\
              <img id="idmImg" src="" alt="Imagen" style="width:180px;height:180px;object-fit:cover;border-radius:8px;border:1px solid #eee; display:none;">\
              <div style="flex:1 1 auto;">\
                <div><strong>SKU:</strong> <span id="idmSku"></span></div>\
                <div class="mt-1"><strong>Producto:</strong> <span id="idmName"></span></div>\
                <div class="mt-1" id="idmDescRow"><strong>Descripción:</strong> <span id="idmDesc"></span></div>\
                <div class="mt-1"><strong>Categoría:</strong> <span id="idmCat"></span></div>\
                <div class="mt-1"><strong>Proveedor:</strong> <span id="idmSup"></span></div>\
                <div class="mt-2"><strong>Cantidad:</strong> <span id="idmQty"></span></div>\
                <div><strong>Precio unitario:</strong> <span id="idmUnit"></span></div>\
                <div><strong>Importe:</strong> <span id="idmImp"></span></div>\
              </div>\
            </div>\
          </div>\
        </div>';
      document.body.appendChild(wrap);
      __idmWrap = wrap;
      __idmName = wrap.querySelector('#idmName');
      __idmSku = wrap.querySelector('#idmSku');
      __idmDesc = wrap.querySelector('#idmDesc');
      __idmDescRow = wrap.querySelector('#idmDescRow');
      __idmQty = wrap.querySelector('#idmQty');
      __idmUnit = wrap.querySelector('#idmUnit');
      __idmImp = wrap.querySelector('#idmImp');
      __idmImg = wrap.querySelector('#idmImg');
      __idmCat = wrap.querySelector('#idmCat');
      __idmSup = wrap.querySelector('#idmSup');
      var closeBtn = wrap.querySelector('#idmClose');
      function hide(){
        try { wrap.classList.remove('show'); } catch(_){ }
        var end = function(){ wrap.removeEventListener('transitionend', end); wrap.style.display = 'none'; };
        wrap.addEventListener('transitionend', end);
        document.removeEventListener('keydown', escHandler);
      }
      var escHandler = function(e){ if (e.key === 'Escape') hide(); };
      closeBtn.addEventListener('click', hide);
      wrap.addEventListener('click', function(e){ if (e.target === wrap) hide(); });
    }
    function showItemDetails(data){
      ensureItemDetailModal();
      __idmSku.textContent = data.sku || '—';
      __idmName.textContent = data.name || '—';
      var d = (data.desc || '').trim();
      if (d) { __idmDesc.textContent = d; if (__idmDescRow) __idmDescRow.style.display = ''; }
      else { __idmDesc.textContent = ''; if (__idmDescRow) __idmDescRow.style.display = 'none'; }
      __idmCat.textContent = (data.category || '').trim() || '—';
      __idmSup.textContent = (data.supplier || '').trim() || '—';
      __idmQty.textContent = String(data.qty || 0);
      __idmUnit.textContent = fmtCOP(data.unit || 0);
      __idmImp.textContent = fmtCOP(data.importe || 0);
      if (data.img) { __idmImg.src = data.img; __idmImg.style.display = ''; }
      else { __idmImg.removeAttribute('src'); __idmImg.style.display = 'none'; }
      __idmWrap.style.display = 'flex';
      requestAnimationFrame(function(){ try { __idmWrap.classList.add('show'); } catch(_){ } });
      setTimeout(function(){ document.addEventListener('keydown', function escHandler(e){ if (e.key === 'Escape') { try { __idmWrap.classList.remove('show'); } catch(_){ } setTimeout(function(){ __idmWrap.style.display = 'none'; }, 650); document.removeEventListener('keydown', escHandler); } }); }, 0);
    }
    // Vaciar carrito con confirmación
    var btnClear = document.getElementById('btnClearCart');
    if (btnClear) {
      btnClear.addEventListener('click', function(){
        if (!body.querySelector('tr')) { centerNotify('info','Sin cambios','El carrito ya está vacío'); return; }
        confirmPretty({ title:'Vaciar carrito', text:'¿Deseas quitar todos los artículos del carrito?', ok:'Vaciar', cancel:'Cancelar', icon:'warning' }).then(function(ok){
          if (!ok) return;
          try { centerLoading.show('Vaciando...', 4000); } catch(_){ }
          body.innerHTML = '';
          resequence();
          recalc();
          clearDraft();
          centerNotify('success','Carrito vacío','Se vaciaron todos los artículos.');
          // Sincronizar carrito flotante inmediatamente
          try {
            if (window.psCart && typeof window.psCart.clear === 'function') { window.psCart.clear(); }
            else if (window.psCart && typeof window.psCart.refresh === 'function') { window.psCart.refresh(); }
          } catch(_){ }
          try { centerLoading.hide(); } catch(_){ }
        });
      });
    }
    // Enhance select with Choices.js to show image + name inside dropdown and selection
    var __choicesInst = null;
    var __allChoices = [];
    if (sel && window.Choices) {
      try {
        // Build explicit choices from existing <option>s, but KEEP ONLY those that have image to avoid duplicates without images
        var placeholderOpt = null;
        try { placeholderOpt = sel.querySelector('option[value=""]') || null; } catch(_){ }
        var all = [];
        Array.prototype.forEach.call(sel.options, function(o){
          if (!o) return;
          if (!o.value) { if (!placeholderOpt) placeholderOpt = o; return; }
          var img = o.getAttribute('data-image') || '';
          // Do NOT skip items without image; include them so category filtering shows all
          var stRaw = o.getAttribute('data-stock') || '';
          var stNum = parseInt(stRaw, 10) || 0;
          all.push({
            value: o.value,
            label: o.textContent.trim(),
            disabled: o.disabled,
            // Never carry selection state from original <option>s
            selected: false,
            customProperties: {
              image: img,
              status: o.getAttribute('data-status') || 'ok',
              sku: o.getAttribute('data-sku') || '',
              price: o.getAttribute('data-price') || '0',
              stock: stRaw,
              name: o.getAttribute('data-name') || o.textContent.trim(),
              description: o.getAttribute('data-description') || '',
              category_id: o.getAttribute('data-category-id') || '',
              category_name: o.getAttribute('data-category-name') || '',
              supplier_id: o.getAttribute('data-supplier-id') || '',
              supplier_name: o.getAttribute('data-supplier-name') || '',
              no_stock: (stNum <= 0) ? '1' : '0'
            }
          });
        });
        // Clear current <option>s to prevent duplicates (we will feed Choices with filtered data)
        try { sel.innerHTML = ''; if (placeholderOpt) sel.appendChild(placeholderOpt); } catch(_){ }
        __choicesInst = new Choices(sel, {
          searchEnabled: true,
          placeholder: true,
          searchPlaceholderValue: 'Escribe el nombre del producto…',
          placeholderValue: 'Seleccione un producto',
          noResultsText: 'Sin coincidencias',
          noChoicesText: 'No hay productos',
          shouldSort: false,
          /* Render fewer DOM nodes at once for smoother scrolling */
          renderChoiceLimit: 120,
          searchResultLimit: 120,
          resetScrollPosition: false,
          itemSelectText: '',
          removeItemButton: false,
          allowHTML: true,
          choices: all,
          callbackOnCreateTemplates: function(template) {
            return {
              item: function(classNames, d) {
                var img = '';
                var status = (d.customProperties && d.customProperties.status) || '';
                var sku = (d.customProperties && d.customProperties.sku) || '';
                var imgName = (d.customProperties && d.customProperties.image) || '';
                if (imgName) img = '<img class="choice__img" src="<?= BASE_URL ?>/uploads/' + imgName + '" alt="" width="28" height="28" decoding="async" loading="lazy" fetchpriority="low">';
                var statusClass = status ? (' stock-' + status) : '';
                var skuHtml = sku ? ('<span class="choice__sku">' + sku + ' ·</span>') : '';
                return template('<div class="' + classNames.item + ' ' + (d.highlighted ? classNames.highlightedState : classNames.itemSelectable) + statusClass + '" data-item data-id="' + d.id + '" data-value="' + d.value + '" ' + (d.active ? 'aria-selected="true"' : '') + (d.disabled ? ' aria-disabled="true"' : '') + '><div class="choice__inner">' + img + skuHtml + '<span class="choice__text' + statusClass + '">' + d.label + '</span></div></div>');
              },
              choice: function(classNames, d) {
                var img = '';
                var status = (d.customProperties && d.customProperties.status) || '';
                var sku = (d.customProperties && d.customProperties.sku) || '';
                var imgName = (d.customProperties && d.customProperties.image) || '';
                if (imgName) img = '<img class="choice__img" src="<?= BASE_URL ?>/uploads/' + imgName + '" alt="" width="28" height="28" decoding="async" loading="lazy" fetchpriority="low">';
                var statusClass = status ? (' stock-' + status) : '';
                var skuHtml = sku ? ('<span class="choice__sku">' + sku + ' ·</span>') : '';
                var ns = (d.customProperties && String(d.customProperties.no_stock||'0') === '1');
                var extraCls = ns ? ' no-stock' : '';
                var extraAttr = ns ? ' data-no-stock="1"' : '';
                return template('<div class="' + classNames.item + ' ' + classNames.itemChoice + ' ' + (d.disabled ? classNames.itemDisabled : classNames.itemSelectable) + extraCls + '" data-select-text="" data-choice ' + (d.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + ' data-id="' + d.id + '" data-value="' + d.value + '" ' + (d.groupId > 0 ? 'role="treeitem"' : 'role="option"') + extraAttr + '><div class="choice__inner">' + img + skuHtml + '<span class="choice__text' + statusClass + '">' + d.label + '</span></div></div>');
              }
            };
          }
        });
        __allChoices = all.slice();
        // Ensure our listener still fires
        sel.addEventListener('change', updatePickerFromSelection);
        // Intercept click on zero-stock choices to prevent selection and show 4s toast
        var cont = sel.closest('.choices');
        if (cont) {
          cont.addEventListener('mousedown', function(ev){
            var el = ev.target && ev.target.closest('.choices__item[data-choice][data-no-stock="1"]');
            if (el) {
              ev.preventDefault(); ev.stopPropagation();
              centerToast4('error','Sin stock','Este producto no tiene stock.');
            }
          }, true);
        }
        // Guard: if a zero-stock item gets selected (keyboard), revert immediately and notify
        sel.addEventListener('addItem', function(ev){
          try {
            var cp = (ev && ev.detail && ev.detail.customProperties) || null;
            var st = cp ? parseInt(cp.stock || '0', 10) || 0 : 0;
            if (st <= 0) {
              if (ev && typeof ev.preventDefault === 'function') ev.preventDefault();
              if (typeof __choicesInst.removeActiveItems === 'function') __choicesInst.removeActiveItems();
              if (typeof __choicesInst.setChoiceByValue === 'function') __choicesInst.setChoiceByValue('');
              sel.value = '';
              centerToast4('error','Sin stock','Este producto no tiene stock.');
            }
          } catch(_){ }
        });
      } catch(_){ /* ignore */ }
    }
    sel && sel.addEventListener('change', updatePickerFromSelection);

    // Enhance category selector with Choices (searchable)
    var __choicesCat = null;
    if (catSel && window.Choices) {
      try {
        __choicesCat = new Choices(catSel, {
          searchEnabled: true,
          searchPlaceholderValue: 'Escriba la categoría…',
          placeholder: true,
          placeholderValue: 'Filtrar categoría',
          itemSelectText: '',
          shouldSort: false,
          removeItemButton: false,
          allowHTML: true
        });
        // Do not auto-select 'Todas'; keep placeholder visible until user chooses
      } catch(_){ }
    }
    // Category filter -> filter choices
    function applyCategoryFilter(){
      if (!__choicesInst) return;
      var val = (catSel && catSel.value) || '';
      var filtered = __allChoices;
      if (val && val !== 'all') {
        filtered = __allChoices.filter(function(ch){ return ch && ch.customProperties && String(ch.customProperties.category_id||'') === String(val); });
      }
      try {
        // 1) Remove any active items FIRST so they don't render above the list
        if (typeof __choicesInst.removeActiveItems === 'function') { __choicesInst.removeActiveItems(); }
        // 2) Clear internal store if available (defensive)
        if (typeof __choicesInst.clearStore === 'function') { __choicesInst.clearStore(); }
        // 3) Clear current choices and rebuild from filtered set
        __choicesInst.clearChoices();
        var filteredUnselected = filtered.map(function(ch){ ch.selected = false; return ch; });
        __choicesInst.setChoices(filteredUnselected, 'value', 'label', true);
        // 4) Ensure the native select has no value
        sel.value = '';
        updatePickerFromSelection();
        // Clear search input in Choices; keep dropdown closed until the user clicks it
        if (typeof __choicesInst.clearInput === 'function') { __choicesInst.clearInput(); }
      } catch(_){ }
    }
    if (catSel) {
      catSel.addEventListener('change', applyCategoryFilter);
      // Initialize with 'all'
      applyCategoryFilter();
    }
    // Initialize preview & price on page load for current selection
    updatePickerFromSelection();
    // Watcher: si otro componente (carrito flotante) limpia el borrador en localStorage, reflejarlo aquí sin recargar
    (function(){
      var lastDraft = null;
      try { lastDraft = localStorage.getItem(DRAFT_KEY); } catch(_){ }
      setInterval(function(){
        try {
          var cur = localStorage.getItem(DRAFT_KEY);
          if (lastDraft && !cur) {
            // Borrador fue limpiado externamente
            if (body.querySelector('tr')) {
              body.innerHTML = '';
              resequence();
              recalc();
              centerNotify('info','Carrito sincronizado','El carrito fue vaciado.');
            }
            var db = document.getElementById('draftBanner');
            if (db) db.style.display = 'none';
          }
          lastDraft = cur;
        } catch(_){ }
      }, 800);
    })();
    // Picker qty immediate cap by stock
    if (qty && sel) {
      function enforcePickerQty(){
        // Resolve current stock from option or Choices customProperties
        let ds = 0;
        let opt = null; let cp = null;
        const val = sel.value;
        if (val) { opt = sel.querySelector('option[value="' + CSS.escape(val) + '"]'); }
        if (!opt) { opt = sel.options[sel.selectedIndex]; }
        if (opt) { ds = parseInt(opt.getAttribute('data-stock') || '0', 10) || 0; }
        try {
          if ((!opt || !ds) && __choicesInst) {
            var arr = __choicesInst.getValue();
            var ch = Array.isArray(arr) ? (arr[0] || null) : (arr || null);
            cp = ch && ch.customProperties;
            if (cp && cp.stock !== '') ds = parseInt(cp.stock, 10) || 0;
          }
        } catch(_){ }
        let v = Math.max(1, parseInt(qty.value || '1', 10));
        let req = v;
        if (ds > 0 && v > ds) { v = ds; centerToast4('error','Stock insuficiente','Disponible: ' + ds + ', solicitado: ' + req + '. Se ajustó a ' + ds + '.'); }
        qty.value = String(v);
      }
      qty.addEventListener('input', enforcePickerQty);
      qty.addEventListener('change', enforcePickerQty);
    }
    // Si no hay productos activos disponibles, notificar
    if (sel) {
      var hasOptions = false;
      for (var i = 0; i < sel.options.length; i++) { if (sel.options[i].value) { hasOptions = true; break; } }
      if (!hasOptions) { centerNotify({icon:'info', title:'Sin inventario', text:'No hay productos activos disponibles para vender.'}); }
    }
    form && form.addEventListener('submit', function(e){
      // Solo evitar envío si no hay productos
      if (body.querySelectorAll('tr').length === 0) {
        e.preventDefault();
        try { if (typeof bannerLoading === 'function') bannerLoading(false); } catch(_){ }
        centerNotify('warning','Aviso','Agrega al menos un producto');
        return;
      }
      // Dejar que el backend haga la validación de stock
      clearDraft();
    });

    // Initialize submit state on load
    toggleSubmit();

    // Load saved draft first
    var restored = loadDraft();
    if (restored > 0) {
      centerNotify('success','Borrador restaurado','Se restauraron ' + restored + ' producto(s) del borrador.');
      try {
        var db = document.getElementById('draftBanner');
        if (db) { db.classList.remove('d-none'); db.style.display = ''; }
      } catch(_){ }
    }

    // Import pending cart from Productos (if any), then save
    try {
      var key = 'pharmasoft_pending_cart';
      var raw = localStorage.getItem(key);
      if (raw) {
        var arr = JSON.parse(raw || '[]') || [];
        if (Array.isArray(arr) && arr.length > 0) {
          arr.forEach(function(it){ addItemFromData(it || {}); });
          localStorage.removeItem(key);
          centerNotify('success','Carrito actualizado','Se importaron ' + arr.length + ' producto(s) desde Productos.');
          saveDraft();
          try { if (typeof setDraftBannerCount === 'function') setDraftBannerCount(); } catch(_){ }
        }
      }
    } catch(e) { /* ignore */ }

    // Manual Save Draft button
    (function(){
      var sd = document.getElementById('btnSaveDraft');
      if (!sd) return;
      sd.addEventListener('click', function(){
        if (!body.querySelector('tr')) { return centerNotify('info','Sin artículos','Agrega productos antes de guardar borrador.'); }
        saveDraft();
        centerNotify('success','Borrador guardado','Tu carrito fue guardado.');
      });
    })();

    // Draft banner actions
    (function(){
      var db = document.getElementById('draftBanner');
      var btnC = document.getElementById('btnDraftContinue');
      var btnD = document.getElementById('btnDraftDiscard');
      if (!db || !btnC || !btnD) return;
      btnC.addEventListener('click', function(){ db.style.display = 'none'; notify({ icon:'info', title:'Continuas con el borrador', text:'Puedes seguir editando tu venta.' }); });
      btnD.addEventListener('click', function(){
        confirmPretty({ title:'Descartar borrador', text:'¿Deseas descartar el borrador y vaciar el carrito?', ok:'Descartar', cancel:'Cancelar', icon:'warning' }).then(function(ok){
          if (!ok) return;
          // Vaciar carrito y borrar draft
          body.innerHTML = '';
          resequence();
          recalc();
          clearDraft();
          db.style.display = 'none';
          centerNotify('success','Borrador descartado','Se descartó el borrador.');
        });
      });
    })();

    // Banner count helpers
    function getCartCount(){ return body.querySelectorAll('tr').length; }
    function setDraftBannerCount(){
      try{
        var el = document.getElementById('draftCount');
        if (!el) return;
        var c = getCartCount();
        el.textContent = c + (c === 1 ? ' ítem' : ' ítems');
      }catch(_){ }
    }
    // Keep banner count updated
    setDraftBannerCount();
  })();
</script>
