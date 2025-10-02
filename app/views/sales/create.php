<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title d-flex align-items-center">
      <i class="fas fa-cart-plus mr-2 text-primary" aria-hidden="true"></i>
      Realizar venta
    </h3>
  <!-- Choices.js script include placed before page scripts to ensure availability -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</div>
  <div class="card-body">
    <style>
      /* Mostrar opciones vencidas en azul en el desplegable nativo */
      select[name="product_pick"] option[data-expired="1"],
      select[name="product_pick"] option[data-status="expired"] { color: #007bff !important; font-weight: 600; }
      /* Próximo a vencer (≤31 días) en amarillo */
      select[name="product_pick"] option[data-status="exp_warn"] { color: #d39e00 !important; font-weight: 600; }
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
      /* Expired: blue color as requested */
      .stock-expired .choice__text, .choice__text.stock-expired { color:#007bff; font-weight:600; }
      .stock-low .choice__text, .choice__text.stock-low { color:#dc3545; font-weight:500; }
      /* Near-expiry highlight */
      .exp-warn .choice__text, .choice__text.exp-warn { color:#d39e00; font-weight:600; }
      .choice__tag { margin-left:6px; font-size:.85em; font-weight:600; }
      .choice__tag.tag-expired { color:#007bff; }
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
      #centerLoading { position: fixed; inset: 0; display: none; z-index: 5000; background: rgba(0,0,0,.35); align-items: center; justify-content: center; cursor: wait; user-select: none; }
      #centerLoading .box { background: #fff; color: #1f2d3d; padding: 14px 18px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.25); font-weight: 700; display: inline-flex; align-items: center; gap: .6rem; }
      #centerLoading .box .spinner { width: 1rem; height: 1rem; border: 2px solid #3c8dbc; border-top-color: transparent; border-radius: 50%; animation: sp 1s linear infinite; }
      @keyframes sp { to { transform: rotate(360deg); } }
      /* When blocking is active, prevent scroll at root level */
      html.cl-block, body.cl-block { overflow: hidden !important; touch-action: none !important; overscroll-behavior: contain !important; }
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
                title: 'Error al realizar venta',
                text: String(msg || ''),
                position: 'top-end',
                toast: true,
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false
              });
            } else if (typeof window.notify === 'function') {
              // Fallback to notify() API if available
              try { notify({ icon: 'error', title: 'Error al realizar venta', text: String(msg||''), position: 'top-end', timer: 4000 }); } catch(_){ notify('error', String(msg||'')); }
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
    <form method="post" action="<?= BASE_URL ?>/sales/store" data-loading-text="Realizando venta..." class="js-confirmable" data-confirm-title="Confirmar venta" data-confirm-text="¿Deseas realizar esta venta?" data-confirm-ok="Sí, realizar" data-confirm-cancel="No" id="cartForm">
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
              $todayDT = new DateTimeImmutable('today');
              $today = $todayDT->format('Y-m-d');
              $catMap = [];
              if (!empty($categories ?? [])) { foreach (($categories ?? []) as $c) { $catMap[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? ''); } }
              $supMap = [];
              if (!empty($suppliers ?? [])) { foreach (($suppliers ?? []) as $s) { $supMap[(int)($s['id'] ?? 0)] = (string)($s['name'] ?? ''); } }
              foreach ($products as $pr):
                $expires = $pr['expires_at'] ?? null;
                $expDT = null;
                try { if (!empty($expires)) { $expDT = new DateTimeImmutable($expires); } } catch (Exception $e) { $expDT = null; }
                $isExpired = ($expDT instanceof DateTimeImmutable) ? ($expDT <= $todayDT) : false;
                $label = $pr['sku'] . ' - ' . $pr['name'];
                // Status precedence: expired > expire-soon (≤31 days) > stock thresholds
                $status = 'ok';
                $expireDays = '';
                if ($isExpired) { $label .= ' (Vencido)'; $status = 'expired'; }
                else {
                  // Check if expiration is within 31 days (inclusive)
                  if ($expDT instanceof DateTimeImmutable) {
                    $diffDays = (int)$todayDT->diff($expDT)->days;
                    $expireDays = (string)$diffDays;
                    if ($diffDays >= 1 && $diffDays <= 31) {
                      $status = 'exp_warn';
                    }
                  }
                  // If not warn by expiry, fallback to stock-based status
                  if ($status === 'ok') {
                    $stk = (int)($pr['stock'] ?? 0);
                    if ($stk <= 20) { $status = 'low'; }
                    elseif ($stk <= 50) { $status = 'stock_warn'; }
                    else { $status = 'ok'; }
                  }
                }
                $catId = (int)($pr['category_id'] ?? 0);
                $supId = (int)($pr['supplier_id'] ?? 0);
                $catName = isset($catMap[$catId]) && $catMap[$catId] !== '' ? $catMap[$catId] : '';
                $supName = isset($supMap[$supId]) && $supMap[$supId] !== '' ? $supMap[$supId] : '';
            ?>
              <?php
                $cp = [
                  'status' => $status,
                  'stock' => (int)($pr['stock'] ?? 0),
                  'image' => (string)($pr['image'] ?? ''),
                  'expired' => $isExpired ? '1' : '0',
                  'no_stock' => ((int)($pr['stock'] ?? 0) <= 0) ? '1' : '0',
                ];
                $cp_json = htmlspecialchars(json_encode($cp, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
              ?>
              <option value="<?= View::e($pr['id']) ?>"
                data-sku="<?= View::e($pr['sku']) ?>"
                data-name="<?= View::e($pr['name']) ?>"
                data-description="<?= View::e($pr['description'] ?? '') ?>"
                data-price="<?= View::e((int)($pr['price'] ?? 0)) ?>"
                data-image="<?= View::e($pr['image'] ?? '') ?>"
                data-expired="<?= $isExpired ? '1' : '0' ?>"
                data-status="<?= View::e($status) ?>"
                data-expire-days="<?= View::e($expireDays) ?>"
                data-stock="<?= View::e($pr['stock'] ?? 0) ?>"
                data-category-id="<?= $catId ?>"
                data-category-name="<?= View::e($catName) ?>"
                data-supplier-id="<?= $supId ?>"
                data-supplier-name="<?= View::e($supName) ?>"
                data-custom-properties='<?= $cp_json ?>'
                title="<?= $isExpired ? 'Producto vencido' : ('Stock: ' . (int)($pr['stock'] ?? 0)) ?>">
                <?= View::e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted d-block d-md-none">Los productos vencidos se muestran en azul y no se pueden agregar. Los próximos a vencer se muestran en amarillo.</small>
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
            Agregar al carrito
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
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1" aria-hidden="true"></i>
        Realizar venta
      </button>
      <button type="button" class="btn btn-outline-primary" id="btnSaveDraft">
        <i class="fas fa-bookmark mr-1" aria-hidden="true"></i>
        Guardar borrador
      </button>
      <a class="btn btn-outline-secondary" id="btnBackToProducts" href="<?= BASE_URL ?>/products">
        <i class="fas fa-store mr-1" aria-hidden="true"></i>
        Seguir viendo productos
      </a>
      <a class="btn btn-secondary" id="btnCancelSale" href="<?= BASE_URL ?>/sales">
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
      // Activate global blocking (no scroll, no key scroll)
      try {
        document.documentElement.classList.add('cl-block');
        document.body && document.body.classList.add('cl-block');
      } catch(_){ }
      // Attach event blockers
      try {
        window.__clWheelHandler = function(e){ e.preventDefault(); e.stopPropagation(); };
        window.__clTouchHandler = function(e){ e.preventDefault(); e.stopPropagation(); };
        window.__clPointerHandler = function(e){ e.preventDefault(); e.stopPropagation(); };
        window.__clKeyHandler = function(e){
          var k = e.key;
          if (!k) return;
          // Block common scroll keys
          if (k === ' ' || k === 'Spacebar' || k === 'PageUp' || k === 'PageDown' || k === 'ArrowUp' || k === 'ArrowDown' || k === 'Home' || k === 'End') {
            e.preventDefault(); e.stopPropagation();
          }
        };
        window.addEventListener('wheel', window.__clWheelHandler, { passive: false, capture: true });
        window.addEventListener('touchmove', window.__clTouchHandler, { passive: false, capture: true });
        window.addEventListener('keydown', window.__clKeyHandler, true);
        if (__clWrap) {
          __clWrap.addEventListener('pointerdown', window.__clPointerHandler, true);
          __clWrap.addEventListener('mousedown', window.__clPointerHandler, true);
          __clWrap.addEventListener('click', window.__clPointerHandler, true);
        }
      } catch(_){ }
      __clActive = true; __clStarted = Date.now();
    }
    function clHide(){
      if (!__clActive) { if (__clWrap) { __clWrap.style.display = 'none'; __clWrap.setAttribute('aria-hidden','true'); } return; }
      var elapsed = Math.max(0, Date.now() - __clStarted);
      var remaining = Math.max(0, __clMin - elapsed);
      function __doHide(){
        __clActive=false;
        if (__clWrap){ __clWrap.style.display='none'; __clWrap.setAttribute('aria-hidden','true'); }
        // Remove global blockers
        try {
          document.documentElement.classList.remove('cl-block');
          document.body && document.body.classList.remove('cl-block');
          if (window.__clWheelHandler) { window.removeEventListener('wheel', window.__clWheelHandler, { capture: true }); }
          if (window.__clTouchHandler) { window.removeEventListener('touchmove', window.__clTouchHandler, { capture: true }); }
          if (window.__clKeyHandler) { window.removeEventListener('keydown', window.__clKeyHandler, true); }
          if (window.__clPointerHandler && __clWrap) {
            __clWrap.removeEventListener('pointerdown', window.__clPointerHandler, true);
            __clWrap.removeEventListener('mousedown', window.__clPointerHandler, true);
            __clWrap.removeEventListener('click', window.__clPointerHandler, true);
          }
          window.__clWheelHandler = window.__clTouchHandler = window.__clKeyHandler = window.__clPointerHandler = null;
        } catch(_){ }
      }
      if (remaining > 0){ __clTimer = setTimeout(__doHide, remaining); }
      else { __doHide(); }
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
    // Submission guard to avoid watchers clearing the UI while we submit
    var __salesSubmitting = false;
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
      } catch(_){ }
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
    // Draft autosave (per-user namespaced)
    var uid = <?= (int)(\App\Helpers\Auth::id() ?? 0) ?>;
    var DRAFT_KEY = 'pharmasoft_sales_draft_' + uid;
    var SHARED = 'pharmasoft_sales_draft';
    var LEGACY = 'pharmasoft_pending_cart';
    function migrateDraft(){
      try {
        var shared = localStorage.getItem(SHARED);
        if (shared && !localStorage.getItem(DRAFT_KEY)) { localStorage.setItem(DRAFT_KEY, shared); }
        var old = localStorage.getItem(LEGACY);
        if (old && !localStorage.getItem(DRAFT_KEY)) { localStorage.setItem(DRAFT_KEY, old); localStorage.removeItem(LEGACY); }
      } catch(_){ }
    }
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
          // Extraer descripción, categoría y proveedor
          var descTxt = '';
          var catTxt = '';
          var supTxt = '';
          
          // Buscar todos los elementos small.text-muted en la celda del producto
          var smEls = tr.querySelectorAll('td:nth-child(3) .small.text-muted');
          smEls.forEach(function(sm){
            var t = (sm.textContent || '').trim();
            if (!t) return;
            var low = t.toLowerCase();
            if (low.indexOf('categoría:') === 0) {
              catTxt = t.replace(/^Categoría:\s*/i,'').trim();
            } else if (low.indexOf('proveedor:') === 0) {
              supTxt = t.replace(/^Proveedor:\s*/i,'').trim();
            } else {
              // Si no es ni categoría ni proveedor, es la descripción
              descTxt = t.trim();
            }
          });
          items.push({
            product_id: pid,
            sku: skuText.trim(),
            name: nameText.trim(),
            description: descTxt, // Incluir la descripción
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
        migrateDraft();
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
      // Non-blocking notices on selection (avoid confirmation modals here)
      try {
        window.__selectPromptGuard = window.__selectPromptGuard || { pid: 0, ts: 0 };
        var now = Date.now();
        var guardOk = !(window.__selectPromptGuard.pid === pidNow && (now - window.__selectPromptGuard.ts) < 1500);
        if (pidNow && guardOk) {
          window.__selectPromptGuard.pid = pidNow; window.__selectPromptGuard.ts = now;
          if (status === 'expired') {
            // Show toast and clear selection without blocking
            centerToast4('error','Producto vencido','No puedes agregar este producto.');
            if (sel) {
              sel.value = '';
              if (typeof __choicesInst !== 'undefined' && __choicesInst) {
                __choicesInst.removeActiveItems();
                __choicesInst.setChoiceByValue('');
              }
              updatePickerFromSelection();
            }
          } else if (status === 'exp_warn') {
            // Informative, do not block interaction (expiry)
            try {
              var dleft = sel && sel.selectedOptions && sel.selectedOptions[0] ? parseInt(sel.selectedOptions[0].getAttribute('data-expire-days')||'0',10) : NaN;
              var sDay = (dleft === 1 ? 'día' : 'días');
              if (!isNaN(dleft)) centerToast4('warning','Por vencer','Faltan ' + dleft + ' ' + sDay + ' para vencer.');
              else centerToast4('warning','Por vencer','Este producto está próximo a vencer.');
            } catch(_){ centerToast4('warning','Por vencer','Este producto está próximo a vencer.'); }
          } else if (status === 'stock_warn') {
            // Stock medium warning
            centerToast4('info','Próximo a acabarse','Quedan pocas unidades en stock.');
          }
        }
      } catch(_){ }
    }
    function badgeFor(status){
      if (status === 'expired') return '<span class="badge badge-danger ml-2"><i class="fas fa-ban mr-1" aria-hidden="true"></i>Vencido</span>';
      if (status === 'low') return '<span class="badge badge-danger ml-2"><i class="fas fa-exclamation-circle mr-1" aria-hidden="true"></i>Stock bajo</span>';
      if (status === 'stock_warn') return '<span class="badge badge-warning ml-2"><i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i>Próximo a acabarse</span>';
      if (status === 'exp_warn') return '<span class="badge badge-warning ml-2"><i class="fas fa-hourglass-half mr-1" aria-hidden="true"></i>Por vencer</span>';
      return '<span class="badge badge-success ml-2"><i class="fas fa-check mr-1" aria-hidden="true"></i>OK</span>';
    }
    // Preview-only: show stock quantity with color-coded label next to the name
    function stockPreviewBadge(status, qty){
      var q = (typeof qty === 'number' ? qty : parseInt(qty||'0',10) || 0);
      var cls = 'badge-success';
      var txt = 'Stock: ' + q;
      if (status === 'low') { cls = 'badge-danger'; txt = 'Stock bajo: ' + q; }
      else if (status === 'stock_warn') { cls = 'badge-warning'; txt = 'Próximo a acabarse: ' + q; }
      else if (status === 'exp_warn') { cls = 'badge-warning'; txt = 'Por vencer'; }
      else if (status === 'expired') { cls = 'badge-danger'; txt = 'Vencido'; }
      return '<span class="badge ml-2 ' + cls + '">' + txt + '</span>';
    }
    // Append item to cart from data (used for localStorage pending cart)
    function addItemFromData(d){
      var pid = parseInt((d && d.product_id) || '0', 10) || 0;
      if (!pid) return;
      var sku = d.sku || '';
      var name = d.name || '';
      // Obtener la descripción de cualquier campo posible
      var desc = (d.desc || d.description || d.descripcion || '').trim();
      // Get status and clean the name from any existing status badge or 'OK' text
      var status = (d.status || 'ok');
      // Remove any existing status badge from the name to prevent duplicates
      name = name.replace(/\s*<span class="badge[^"]*">[^<]*<\/span>\s*$/, '').trim();
      // Remove any trailing 'OK' text to prevent duplication with the badge
      name = name.replace(/\s*OK\s*$/i, '').trim();
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
        <td class="align-middle" title="Precio: ${fmtCOP(p)} | Stock: ${isNaN(stock)?0:stock}">${imgHtml}<div class="d-flex flex-column"><span>${name} ${badgeFor(status)}</span>${desc ? `<div class="small text-muted" style="white-space: pre-line;">${desc}</div>` : ''}${catName ? `<div class="small text-muted">Categoría: ${catName}</div>` : ''}${supName ? `<div class="small text-muted">Proveedor: ${supName}</div>` : ''}</div></td>
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
      // Early guard: if no product selected, do not show loader; just notify
      const pidEarly = parseInt(opt && opt.value ? opt.value : '0', 10) || 0;
      if (!pidEarly) { centerToast4('warning','Elige un producto por favor',''); return; }
      // Defensive: block zero-stock selection
      try {
        var st0 = 0;
        if (opt && opt.hasAttribute('data-stock')) st0 = parseInt(opt.getAttribute('data-stock')||'0', 10) || 0;
        else if (cp && typeof cp.stock !== 'undefined') st0 = parseInt(cp.stock||'0', 10) || 0;
        if (st0 <= 0) {
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
      const pid = pidEarly;
      // Status detection regardless of native <option> or Choices.js
      var status = (opt && opt.getAttribute('data-status')) || (cp ? cp.status : 'ok') || 'ok';
      // Handle expired: show blocking modal and do not add
      if (status === 'expired') {
        // Non-blocking: notify and clear selection; do not add
        centerToast4('error','Producto vencido','No puedes agregar este producto.');
        if (sel) {
          sel.value = '';
          if (typeof __choicesInst !== 'undefined' && __choicesInst) {
            __choicesInst.removeActiveItems();
            __choicesInst.setChoiceByValue('');
          }
          updatePickerFromSelection();
        }
        return;
      }
      // Expiration warn: proceed automatically after showing a toast (no confirmation modal)
      if (status === 'exp_warn') {
        try {
          var dleft2 = opt && opt.hasAttribute('data-expire-days') ? parseInt(opt.getAttribute('data-expire-days')||'0',10) : NaN;
          var sDay2 = (dleft2 === 1 ? 'día' : 'días');
          if (!isNaN(dleft2)) centerToast4('warning','Por vencer','Agregando: faltan ' + dleft2 + ' ' + sDay2 + '.');
          else centerToast4('warning','Por vencer','Agregando producto próximo a vencer.');
        } catch(_){ centerToast4('warning','Por vencer','Agregando producto próximo a vencer.'); }
        // fall-through to actuallyAdd()
      }
      // Stock warn (medium): proceed automatically with info toast
      if (status === 'stock_warn') {
        centerToast4('info','Próximo a acabarse','Agregando producto con stock medio.');
        // fall-through to actuallyAdd()
      }
      // OK -> add directly
      actuallyAdd();

      function actuallyAdd(){
        // Show loader only when we are actually adding a valid product
        try { centerLoading.show('Agregando...', 4000); } catch(_){ }
        const sku = (opt && (opt.getAttribute('data-sku'))) || (cp ? cp.sku : '') || '';
        const name = (opt && (opt.getAttribute('data-name') || opt.textContent.trim())) || (cp ? cp.name : '') || '';
        // Obtener la descripción de cualquier atributo posible
        const desc = (opt && (opt.getAttribute('data-desc') || opt.getAttribute('data-description') || opt.getAttribute('data-descripcion'))) || 
                   (cp ? (cp.desc || cp.description || cp.descripcion) : '') || '';
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
          <td class="align-middle" title="Precio: ${fmtCOP(p)} | Stock: ${isNaN(stock)?0:stock}">
            ${imgHtml}
            <div class="d-flex flex-column">
              <span>${name} ${badgeFor(status)}</span>
              ${desc ? `<div class="small text-muted" style="white-space: pre-line;">${desc}</div>` : ''}
              ${catName ? `<div class="small text-muted">Categoría: ${catName}</div>` : ''}
              ${supName ? `<div class="small text-muted">Proveedor: ${supName}</div>` : ''}
            </div>
          </td>
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
        var all = Array.prototype.map.call(sel.options, function(o){
          if (!o || !o.value) return null;
          var stRaw = o.getAttribute('data-stock') || '';
          var img = o.getAttribute('data-image') || '';
          var stNum = parseInt(stRaw||'0',10) || 0;
          var stOk = (stNum > 50);
          var stWarn = (stNum > 20 && stNum <= 50);
          var stLow = (stNum <= 20);
          var isExpired = (o.getAttribute('data-expired') === '1') || (o.getAttribute('data-status') === 'expired');
          return {
            value: o.value,
            label: o.textContent,
            // Do NOT disable expired here so they remain visible; selection is blocked via JS guards
            disabled: !!o.disabled,
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
              no_stock: (stNum <= 0) ? '1' : '0',
              expired: isExpired ? '1' : '0',
              color_class: (isExpired ? 'stock-expired' : (stLow ? 'stock-low' : (stWarn ? 'stock-warn' : 'stock-ok')))
            }
          };
        });
        // Initialize Choices on existing native <option>s (do NOT clear them)
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
          // Provide initial choices so list is populated immediately
          choices: (all || []).filter(Boolean),
          callbackOnCreateTemplates: function(template) {
            return {
              item: function(classNames, d) {
                var img = '';
                var status = (d.customProperties && d.customProperties.status) || '';
                var sku = (d.customProperties && d.customProperties.sku) || '';
                var imgName = (d.customProperties && d.customProperties.image) || '';
                if (imgName) img = '<img class="choice__img" src="<?= BASE_URL ?>/uploads/' + imgName + '" alt="" width="28" height="28" decoding="async" loading="lazy" fetchpriority="low">';
                var statusClass = '';
                try {
                  var cp = d.customProperties || {};
                  var color = cp.color_class || '';
                  if (!color) {
                    var stv = parseInt(cp.stock||'0',10) || 0;
                    var expired = (status === 'expired') || String(cp.expired||'0') === '1';
                    color = expired ? 'stock-expired' : (stv <= 20 ? 'stock-low' : (stv <= 50 ? 'stock-warn' : 'stock-ok'));
                  }
                  statusClass = ' ' + color;
                  // If near-expiry, add explicit exp-warn class for yellow emphasis
                  if ((cp.status||status) === 'exp_warn') statusClass += ' exp-warn';
                } catch(_){ }
                var skuHtml = sku ? ('<span class="choice__sku">' + sku + ' ·</span>') : '';
                var expTag = '';
                try { var cp2 = d.customProperties || {}; var isExp2 = (status === 'expired') || String(cp2.expired||'0') === '1'; if (isExp2) expTag = '<span class="choice__tag tag-expired">Vencido</span>'; } catch(_){ }
                return template('<div class="' + classNames.item + ' ' + (d.highlighted ? classNames.highlightedState : classNames.itemSelectable) + statusClass + '" data-item data-id="' + d.id + '" data-value="' + d.value + '" ' + (d.active ? 'aria-selected="true"' : '') + (d.disabled ? ' aria-disabled="true"' : '') + '><div class="choice__inner">' + img + skuHtml + '<span class="choice__text' + statusClass + '">' + d.label + '</span>' + expTag + '</div></div>');
              },
              choice: function(classNames, d) {
                var img = '';
                var status = (d.customProperties && d.customProperties.status) || '';
                var sku = (d.customProperties && d.customProperties.sku) || '';
                var imgName = (d.customProperties && d.customProperties.image) || '';
                if (imgName) img = '<img class="choice__img" src="<?= BASE_URL ?>/uploads/' + imgName + '" alt="" width="28" height="28" decoding="async" loading="lazy" fetchpriority="low">';
                var statusClass = '';
                var isExpired = false;
                try {
                  var cp = d.customProperties || {};
                  var stv = parseInt(cp.stock||'0',10) || 0;
                  isExpired = (status === 'expired') || String(cp.expired||'0') === '1';
                  var color = cp.color_class || (isExpired ? 'stock-expired' : (stv <= 20 ? 'stock-low' : (stv <= 50 ? 'stock-warn' : 'stock-ok')));
                  statusClass = ' ' + color;
                  if ((cp.status||status) === 'exp_warn') statusClass += ' exp-warn';
                } catch(_){ }
                var skuHtml = sku ? ('<span class="choice__sku">' + sku + ' ·</span>') : '';
                var ns = (d.customProperties && String(d.customProperties.no_stock||'0') === '1');
                var extraCls = ns ? ' no-stock' : '';
                var extraAttr = '';
                if (ns) extraAttr += ' data-no-stock="1"';
                if (isExpired) extraAttr += ' data-expired="1"';
                var expTag2 = isExpired ? '<span class="choice__tag tag-expired">Vencido</span>' : '';
                return template('<div class="' + classNames.item + ' ' + classNames.itemChoice + ' ' + (d.disabled ? classNames.itemDisabled : classNames.itemSelectable) + extraCls + '" data-select-text="" data-choice ' + (d.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + ' data-id="' + d.id + '" data-value="' + d.value + '" ' + (d.groupId > 0 ? 'role="treeitem"' : 'role="option"') + extraAttr + '><div class="choice__inner">' + img + skuHtml + '<span class="choice__text' + statusClass + '">' + d.label + '</span>' + expTag2 + '</div></div>');
              }
            };
          }
        });
        // Remove nulls (e.g., placeholder option without value)
        __allChoices = (all || []).filter(Boolean);
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
            var el2 = ev.target && ev.target.closest('.choices__item[data-choice][data-expired="1"]');
            if (el2) {
              ev.preventDefault(); ev.stopPropagation();
              centerToast4('error','Producto vencido','No puedes seleccionar este producto.');
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
            var isExp = cp && (String(cp.expired||'0') === '1' || String(cp.status||'') === 'expired');
            if (isExp) {
              if (ev && typeof ev.preventDefault === 'function') ev.preventDefault();
              if (typeof __choicesInst.removeActiveItems === 'function') __choicesInst.removeActiveItems();
              if (typeof __choicesInst.setChoiceByValue === 'function') __choicesInst.setChoiceByValue('');
              sel.value = '';
              centerToast4('error','Producto vencido','No puedes seleccionar este producto.');
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
    function __rebuildAllChoicesFromNative(){
      try {
        var arr = Array.prototype.map.call(sel.options, function(o){
          if (!o || !o.value) return null;
          var stRaw = o.getAttribute('data-stock') || '';
          var stNum = parseInt(stRaw||'0',10) || 0;
          var stWarn = (stNum > 20 && stNum <= 50);
          var stLow = (stNum <= 20);
          var isExpired = (o.getAttribute('data-expired') === '1') || (o.getAttribute('data-status') === 'expired');
          return {
            value: o.value,
            label: o.textContent,
            disabled: (o.disabled || isExpired),
            selected: false,
            customProperties: {
              image: o.getAttribute('data-image') || '',
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
              no_stock: (stNum <= 0) ? '1' : '0',
              expired: isExpired ? '1' : '0',
              color_class: (isExpired ? 'stock-expired' : (stLow ? 'stock-low' : (stWarn ? 'stock-warn' : 'stock-ok')))
            }
          };
        }).filter(Boolean);
        __allChoices = arr;
      } catch(_){ }
    }
    function applyCategoryFilter(){
      if (!__choicesInst) return;
      // Only filter when a specific category is selected; by default show all
      var val = (catSel && (catSel.value || catSel.getAttribute('data-value') || '')) || '';
      var vlow = String(val || '').trim().toLowerCase();
      if (vlow === 'todas') val = 'all';
      if (!__allChoices || !__allChoices.length) { __rebuildAllChoicesFromNative(); }
      var filtered;
      if (!val || val === 'all') {
        filtered = __allChoices.slice();
      } else {
        filtered = __allChoices.filter(function(ch){ return ch && ch.customProperties && String(ch.customProperties.category_id||'') === String(val); });
      }
      // Fallback: if filter produced 0, show all to avoid empty state
      if (!filtered || !filtered.length) { filtered = __allChoices.slice(); }
      try {
        if (typeof __choicesInst.removeActiveItems === 'function') { __choicesInst.removeActiveItems(); }
        if (typeof __choicesInst.clearStore === 'function') { __choicesInst.clearStore(); }
        __choicesInst.clearChoices();
        var filteredUnselected = (filtered || []).filter(Boolean).map(function(ch){ ch.selected = false; return ch; });
        // If still empty for any reason, repopulate with all
        if (!filteredUnselected.length) {
          filteredUnselected = (__allChoices || []).slice();
        }
        __choicesInst.setChoices(filteredUnselected, 'value', 'label', true);
        // Keep placeholder visible
        try { if (typeof __choicesInst.setChoiceByValue === 'function') __choicesInst.setChoiceByValue(''); } catch(_){ }
      } catch(_){ }
    }
    // Bind category changes to re-apply filter; also run once on load
    if (catSel) {
      try { catSel.addEventListener('change', applyCategoryFilter); } catch(_){ }
      // Choices for category may fire custom events; ensure native change bubbles
      try { catSel.addEventListener('addItem', applyCategoryFilter); } catch(_){ }
    }
    // Ensure initial population shows all choices by default
    try { applyCategoryFilter(); } catch(_){ }
    // Initialize preview & price on page load for current selection
    updatePickerFromSelection();
    // Watcher: si otro componente (carrito flotante) limpia el borrador en localStorage, reflejarlo aquí sin recargar
    (function(){
      var lastDraft = null;
      try { lastDraft = localStorage.getItem(DRAFT_KEY); } catch(_){ }
      setInterval(function(){
        try {
          // If we are in the middle of a submission, do not react to draft changes
          if (typeof __salesSubmitting !== 'undefined' && __salesSubmitting) { return; }
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
      // Marcamos que estamos enviando para que los watchers no vacíen el carrito
      __salesSubmitting = true;
      // No limpiar el borrador aquí; se limpiará después según el flujo (éxito o acciones del usuario)
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
      sd.addEventListener('click', function(e){
        try { if (e) { e.preventDefault(); e.stopPropagation(); } } catch(_){ }
        if (!body.querySelector('tr')) { return centerNotify('info','Sin artículos','Agrega productos antes de guardar borrador.'); }
        saveDraft();
        centerNotify('success','Borrador guardado','Tu carrito fue guardado.');
      });
    })();

    // Clear cart with confirm (avoid any submit)
    (function(){
      var cc = document.getElementById('btnClearCart');
      if (!cc) return;
      cc.addEventListener('click', function(e){
        try { if (e) { e.preventDefault(); e.stopPropagation(); } } catch(_){ }
        if (!body.querySelector('tr')) { return centerNotify('info','Carrito vacío','No hay productos para eliminar.'); }
        confirmPretty({ title:'Vaciar carrito', text:'¿Deseas eliminar todos los productos del carrito?', ok:'Sí, vaciar', cancel:'Cancelar', icon:'warning' }).then(function(ok){
          if (!ok) return;
          try { body.innerHTML = ''; resequence(); recalc(); clearDraft(); } catch(_){ }
          centerNotify('success','Carrito vaciado','Se eliminaron todos los productos.');
        });
      });
    })();

    // Safe navigate buttons inside form (avoid accidental submit)
    (function(){
      function safeNav(anchor){
        if (!anchor) return;
        anchor.addEventListener('click', function(e){
          try { if (e) { e.preventDefault(); e.stopPropagation(); } } catch(_){ }
          var href = anchor.getAttribute('href') || '#';
          if (!href || href === '#') return;
          // No loaders or confirms; direct navigation
          window.location.href = href;
        });
      }
      safeNav(document.getElementById('btnBackToProducts'));
      safeNav(document.getElementById('btnCancelSale'));
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
