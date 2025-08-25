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
      /* On md and up, hide inline preview to keep fields perfectly aligned */
      @media (min-width: 768px) {
        #pickPreview { display:none; }
      }
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
      /* Status-based colors inside Choices */
      .stock-ok .choice__text, .choice__text.stock-ok { color:#28a745; }
      .stock-warn .choice__text, .choice__text.stock-warn { color:#d39e00; font-weight:600; }
      .stock-expired .choice__text, .choice__text.stock-expired { color:#dc3545; font-weight:600; }
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
      }
    </style>
    <!-- Choices.js (vanilla) for rich select rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= View::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>/sales/store" data-loading-text="Guardando venta..." id="cartForm">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-row align-items-center">
        <div class="form-group col-12 col-md-6 col-lg-6 mb-0">
          <label class="mb-1">Producto</label>
          <select name="product_pick" class="form-control">
            <option value="">-- Selecciona --</option>
            <?php
              $today = (new DateTimeImmutable('today'))->format('Y-m-d');
              foreach ($products as $pr):
                $expires = $pr['expires_at'] ?? null;
                $isExpired = !empty($expires) && $expires < $today;
                $label = $pr['sku'] . ' - ' . $pr['name'];
                $status = 'ok';
                if ($isExpired) { $label .= ' (Vencido)'; $status = 'expired'; }
                elseif (!empty($expires)) { $days = (int) floor((strtotime($expires) - strtotime($today)) / 86400); if ($days <= 30) { $status = 'warn'; $label .= ' (Próx. a vencer)'; } }
            ?>
              <option value="<?= View::e($pr['id']) ?>" <?= $isExpired ? 'disabled' : '' ?> data-sku="<?= View::e($pr['sku']) ?>" data-name="<?= View::e($pr['name']) ?>" data-description="<?= View::e($pr['description'] ?? '') ?>" data-price="<?= View::e((int)($pr['price'] ?? 0)) ?>" data-image="<?= View::e($pr['image'] ?? '') ?>" data-expired="<?= $isExpired ? '1' : '0' ?>" data-status="<?= View::e($status) ?>" data-stock="<?= View::e($pr['stock'] ?? 0) ?>" title="<?= $isExpired ? 'Producto vencido' : (!empty($expires) ? ('Vence: ' . View::e($expires)) : '') ?>">
                <?= View::e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted d-block d-md-none">Los productos vencidos aparecen deshabilitados.</small>
          <div id="pickPreview">
            <img id="pickImg" alt="Imagen producto">
            <div class="meta">
              <div><strong id="pickName">—</strong> <span id="pickBadges"></span></div>
            </div>
          </div>
        </div>
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
<?php if (!empty($error)): ?>
<script>
  try { if (window.notify) notify('error', <?= json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>); } catch(e){}
  // If error is product expired, highlight product selector
  (function(){
    var sel = document.querySelector('select[name="product_pick"]');
    if (sel && /vencid/i.test(<?= json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)) {
      sel.classList.add('is-invalid');
      sel.focus();
    }
  })();
</script>
<?php endif; ?>

<script>
  (function(){
    const sel = document.querySelector('select[name="product_pick"]');
    const qty = document.getElementById('pickQty');
    const price = document.getElementById('pickPrice');
    const pv = {
      wrap: document.getElementById('pickPreview'),
      img: document.getElementById('pickImg'),
      name: document.getElementById('pickName'),
      badges: document.getElementById('pickBadges')
    };
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
            return notify({ icon: optsOrType, title: maybeTitle || '', text: maybeText || '', position: 'top-end' });
          }
          var o = optsOrType || {}; o.position = 'top-end';
          return notify(o);
        }
      } catch(_){}
      var o2 = (typeof optsOrType === 'string') ? { icon: optsOrType, title: maybeTitle||'', text: maybeText||'' } : (optsOrType||{});
      if (window.Swal && Swal.fire) {
        Swal.fire({ icon: o2.icon || 'info', title: o2.title || '', text: o2.text || '', position: 'top-end', toast: true, timer: 2500, timerProgressBar: true, showConfirmButton: false });
      }
    }

    // Lightweight confirm modal (always available on this page)
    var __cmWrap, __cmTitle, __cmText, __cmOk, __cmCancel, __cmOnOk;
    function ensureConfirmModal(){
      if (__cmWrap) return;
      var wrap = document.createElement('div');
      wrap.id = 'confirmModal';
      wrap.style.position = 'fixed';
      wrap.style.left = 0; wrap.style.top = 0; wrap.style.right = 0; wrap.style.bottom = 0;
      wrap.style.background = 'rgba(0,0,0,0.45)';
      wrap.style.display = 'none';
      wrap.style.alignItems = 'center';
      wrap.style.justifyContent = 'center';
      wrap.style.zIndex = 1050;
      wrap.innerHTML = '\
        <div style="background:#fff; max-width:520px; width:92%; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,.2); overflow:hidden;">\
          <div style="padding:14px 18px; border-bottom:1px solid #eee; display:flex; align-items:center;">\
            <div class="mr-2" style="width:28px; height:28px; border-radius:50%; background:#f8d7da; color:#721c24; display:flex; align-items:center; justify-content:center; font-weight:700;">!</div>\
            <h5 id="cmTitle" style="margin:0; font-weight:600;">Confirmación</h5>\
          </div>\
          <div style="padding:18px; color:#444;"><div id="cmText">¿Deseas continuar?</div></div>\
          <div style="padding:12px 18px; border-top:1px solid #eee; display:flex; justify-content:flex-end; gap:8px;">\
            <button id="cmCancel" type="button" class="btn btn-secondary">Cancelar</button>\
            <button id="cmOk" type="button" class="btn btn-danger">Aceptar</button>\
          </div>\
        </div>';
      document.body.appendChild(wrap);
      __cmWrap = wrap;
      __cmTitle = wrap.querySelector('#cmTitle');
      __cmText = wrap.querySelector('#cmText');
      __cmOk = wrap.querySelector('#cmOk');
      __cmCancel = wrap.querySelector('#cmCancel');
      __cmCancel.addEventListener('click', function(){ hideConfirmModal(); });
      wrap.addEventListener('click', function(e){ if (e.target === wrap) hideConfirmModal(); });
      __cmOk.addEventListener('click', function(){ var fn = __cmOnOk; __cmOnOk = null; hideConfirmModal(); if (typeof fn === 'function') fn(); });
    }
    function showConfirmModal(title, text, okLabel, onOk){
      ensureConfirmModal();
      __cmTitle.textContent = title || 'Confirmación';
      __cmText.textContent = text || '¿Deseas continuar?';
      __cmOk.textContent = okLabel || 'Aceptar';
      __cmOnOk = onOk || null;
      __cmWrap.style.display = 'flex';
    }
    function hideConfirmModal(){ if (!__cmWrap) return; __cmWrap.style.display = 'none'; }
    function confirmModal(title, text, okLabel, onOk){ showConfirmModal(title, text, okLabel, onOk); }

    function fmtCOP(n){
      try { return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0, maximumFractionDigits:0 }).format(n||0); }
      catch(e){ var v = Math.round(n||0); return '$' + String(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    }
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
    }
    function toggleSubmit(){
      if (!submitBtn) return;
      const hasItems = !!body.querySelector('tr');
      submitBtn.disabled = !hasItems;
      submitBtn.classList.toggle('disabled', !hasItems);
      submitBtn.title = hasItems ? '' : 'Agrega al menos un producto';
    }
    function updatePickerFromSelection(){
      const opt = sel && sel.options[sel.selectedIndex];
      if (!opt || !opt.value) {
        if (pv.img) { pv.img.style.display = 'none'; }
        if (pv.name) pv.name.textContent = '—';
        return;
      }
      const dp = opt.getAttribute('data-price');
      const ds = parseInt(opt.getAttribute('data-stock') || '0', 10) || 0;
      const status = opt.getAttribute('data-status') || 'ok';
      if (dp !== null) {
        const v = Math.round(parseFloat(dp || '0') || 0);
        price.value = String(v);
      }
      if (pv.name) pv.name.textContent = (opt.getAttribute('data-name') || opt.textContent || '').trim();
      if (pv.badges) pv.badges.innerHTML = badgeFor(status);
      const imgName = opt.getAttribute('data-image') || '';
      if (pv.img) {
        if (imgName) { pv.img.src = '<?= BASE_URL ?>/uploads/' + imgName; pv.img.style.display = ''; }
        else { pv.img.removeAttribute('src'); pv.img.style.display = 'none'; }
      }
      // Clamp qty to stock immediately
      let q = Math.max(1, parseInt(qty.value || '1', 10));
      if (ds > 0 && q > ds) { q = ds; qty.value = String(q); centerNotify('warning','Cantidad ajustada','Supera stock disponible ('+ds+').'); }
    }
    function badgeFor(status){
      if (status === 'expired') return '<span class="badge badge-danger ml-2">Vencido</span>';
      if (status === 'warn') return '<span class="badge badge-warning ml-2">Próx. a vencer</span>';
      return '<span class="badge badge-success ml-2">OK</span>';
    }
    function addItem(){
      const opt = sel.options[sel.selectedIndex];
      const pid = parseInt(opt && opt.value ? opt.value : '0', 10);
      if (!pid) { centerNotify('warning','Aviso','Selecciona un producto'); return; }
      if (opt.getAttribute('data-expired') === '1') { centerNotify('error','Producto vencido','No se puede vender'); return; }
      const sku = opt.getAttribute('data-sku') || '';
      const name = opt.getAttribute('data-name') || opt.textContent.trim();
      const desc = opt.getAttribute('data-description') || '';
      const status = opt.getAttribute('data-status') || 'ok';
      const stock = parseInt(opt.getAttribute('data-stock') || '0', 10);
      const imgName = opt.getAttribute('data-image') || '';
      if (!isFinite(stock) || stock <= 0) { centerNotify('info','Sin stock','Este producto no tiene stock disponible'); return; }
      let q = Math.max(1, parseInt(qty.value || '1', 10));
      if (q > stock) { centerNotify('warning','Cantidad ajustada','Supera stock disponible (' + stock + '). Se ajustó a ' + stock); q = stock; }
      const p = Math.max(0, Math.round(parseFloat(price.value || '0') || 0));
      const tr = document.createElement('tr');
      const idx = body.querySelectorAll('tr').length + 1;
      const imgHtml = imgName ? `<img src="<?= BASE_URL ?>/uploads/${imgName}" alt="${name}" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #e5e5e5;margin-right:10px;vertical-align:middle;">` : '';
      tr.innerHTML = `
        <td class="align-middle">${idx}</td>
        <td class="align-middle">${sku}</td>
        <td class="align-middle" title="Precio: ${fmtCOP(p)} | Stock: ${isNaN(stock)?0:stock}">${imgHtml}<div class="d-flex flex-column"><span>${name} ${badgeFor(status)}</span>${desc ? `<small class=\"text-muted\">${desc}</small>` : ''}</div></td>
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
      // Enforce max stock on quantity input
      var qtyInput = tr.querySelector('input[name="qty[]"]');
      qtyInput.max = String(stock);
      qtyInput.addEventListener('input', function(){
        let v = parseInt(qtyInput.value || '1', 10);
        if (v > stock) { v = stock; centerNotify('info','Cantidad ajustada','Stock disponible: ' + stock); }
        if (v < 1 || isNaN(v)) { v = 1; }
        qtyInput.value = String(v);
        recalc();
      });
      tr.querySelector('input[name="unit_price[]"]').addEventListener('input', recalc);
      // Ver detalles del renglón
      var btnInfo = tr.querySelector('.btnRowInfo');
      if (btnInfo) {
        btnInfo.addEventListener('click', function(){
          const qNow = parseInt(tr.querySelector('input[name="qty[]"]').value || '0', 10) || 0;
          const pNow = Math.round(parseFloat(tr.querySelector('input[name="unit_price[]"]').value || '0')) || 0;
          const impNow = qNow * pNow;
          showItemDetails({
            sku: sku,
            name: name,
            img: imgName ? '<?= BASE_URL ?>/uploads/' + imgName : '',
            desc: desc,
            qty: qNow,
            unit: pNow,
            importe: impNow
          });
        });
      }
      // Added row; recalc totals
      // Attach confirm remove with styled modal
      tr.querySelector('.btnRemove').addEventListener('click', function(){
        confirmModal('Quitar artículo', '¿Deseas quitar "' + name + '" del carrito?', 'Quitar', function(){
          tr.remove();
          resequence();
          recalc();
          centerNotify('info','Artículo quitado','Se quitó "' + name + '" del carrito.');
        });
      });
      recalc();
      // Notify on add
      centerNotify('success', 'Agregado', 'Se agregó "' + name + '" (x' + q + ') al carrito.');
    }
    // addBySkuQuantityPrice solo disponible en Ventas del día
    function resequence(){
      let i = 1;
      body.querySelectorAll('tr').forEach(tr => { tr.firstElementChild.textContent = i++; });
    }
    addBtn && addBtn.addEventListener('click', addItem);
    // Import CSV handlers movidos a Ventas del día

    // Modal ligero para detalles del renglón
    var __idmWrap, __idmName, __idmSku, __idmQty, __idmUnit, __idmImp, __idmImg, __idmDesc, __idmDescRow;
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
        <div style="background:#fff; max-width:560px; width:92%; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,.2); overflow:hidden;">\
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
      var closeBtn = wrap.querySelector('#idmClose');
      function hide(){ wrap.style.display = 'none'; document.removeEventListener('keydown', escHandler); }
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
      __idmQty.textContent = String(data.qty || 0);
      __idmUnit.textContent = fmtCOP(data.unit || 0);
      __idmImp.textContent = fmtCOP(data.importe || 0);
      if (data.img) { __idmImg.src = data.img; __idmImg.style.display = ''; }
      else { __idmImg.removeAttribute('src'); __idmImg.style.display = 'none'; }
      __idmWrap.style.display = 'flex';
      setTimeout(function(){ document.addEventListener('keydown', function escHandler(e){ if (e.key === 'Escape') { __idmWrap.style.display = 'none'; document.removeEventListener('keydown', escHandler); } }); }, 0);
    }
    // Vaciar carrito con confirmación
    var btnClear = document.getElementById('btnClearCart');
    if (btnClear) {
      btnClear.addEventListener('click', function(){
        if (body.querySelector('tr')) { /* proceed */ } else { centerNotify('info','Sin cambios','El carrito ya está vacío'); return; }
        confirmModal('Vaciar carrito', '¿Deseas quitar todos los artículos del carrito?', 'Vaciar', function(){
          body.innerHTML = '';
          resequence();
          recalc();
          centerNotify('success','Carrito vacío','Se vaciaron todos los artículos.');
        });
      });
    }
    // Enhance select with Choices.js to show image + name inside dropdown and selection
    if (sel && window.Choices) {
      try {
        var choices = new Choices(sel, {
          searchEnabled: true,
          shouldSort: false,
          itemSelectText: '',
          removeItemButton: false,
          allowHTML: true,
          callbackOnCreateTemplates: function(template) {
            return {
              item: function(classNames, data) {
                var img = '';
                var status = '';
                var sku = '';
                try {
                  var el = data.customProperties || {};
                  var imgName = (el && el.image) ? el.image : (data.valueElement && data.valueElement.getAttribute ? data.valueElement.getAttribute('data-image') : '');
                  status = (el && el.status) ? el.status : (data.valueElement && data.valueElement.getAttribute ? data.valueElement.getAttribute('data-status') : '');
                  sku = (el && el.sku) ? el.sku : (data.valueElement && data.valueElement.getAttribute ? data.valueElement.getAttribute('data-sku') : '');
                  if (imgName) img = '<img class="choice__img" src="<?= BASE_URL ?>/uploads/' + imgName + '" alt="">';
                } catch(_){ }
                var statusClass = status ? (' stock-' + status) : '';
                var skuHtml = sku ? ('<span class="choice__sku">' + sku + ' ·</span>') : '';
                return template('<div class="' + classNames.item + ' ' + (data.highlighted ? classNames.highlightedState : classNames.itemSelectable) + statusClass + '" data-item data-id="' + data.id + '" data-value="' + data.value + '" ' + (data.active ? 'aria-selected="true"' : '') + (data.disabled ? ' aria-disabled="true"' : '') + '><div class="choice__inner">' + img + skuHtml + '<span class="choice__text' + statusClass + '">' + data.label + '</span></div></div>');
              },
              choice: function(classNames, data) {
                var img = '';
                var status = '';
                var sku = '';
                try {
                  var imgName = data.customProperties && data.customProperties.image ? data.customProperties.image : (data.element ? data.element.getAttribute('data-image') : '');
                  status = data.customProperties && data.customProperties.status ? data.customProperties.status : (data.element ? data.element.getAttribute('data-status') : '');
                  sku = data.customProperties && data.customProperties.sku ? data.customProperties.sku : (data.element ? data.element.getAttribute('data-sku') : '');
                  if (imgName) img = '<img class="choice__img" src="<?= BASE_URL ?>/uploads/' + imgName + '" alt="">';
                } catch(_){ }
                var statusClass = status ? (' stock-' + status) : '';
                var skuHtml = sku ? ('<span class="choice__sku">' + sku + ' ·</span>') : '';
                return template('<div class="' + classNames.item + ' ' + classNames.itemChoice + ' ' + (data.disabled ? classNames.itemDisabled : classNames.itemSelectable) + '" data-select-text="" data-choice ' + (data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + ' data-id="' + data.id + '" data-value="' + data.value + '" ' + (data.groupId > 0 ? 'role="treeitem"' : 'role="option"') + '><div class="choice__inner">' + img + skuHtml + '<span class="choice__text' + statusClass + '">' + data.label + '</span></div></div>');
              }
            };
          },
          callbackOnInit: function(){
            // Inject customProperties so templates can access data-image for initial render
            try {
              var opts = sel.options;
              for (var i=0;i<opts.length;i++) {
                var o = opts[i];
                if (!o.value) continue;
                var img = o.getAttribute('data-image') || '';
                var st = o.getAttribute('data-status') || '';
                var sku = o.getAttribute('data-sku') || '';
                var choice = this._store.getChoiceById(i+1) || null; // best-effort
                if (choice) choice.customProperties = { image: img, status: st, sku: sku };
              }
            } catch(_){ }
          }
        });
      } catch(_){ /* ignore */ }
    }
    sel && sel.addEventListener('change', updatePickerFromSelection);
    // Initialize preview & price on page load for current selection
    updatePickerFromSelection();
    // Picker qty immediate cap by stock
    if (qty && sel) {
      qty.addEventListener('input', function(){
        const opt = sel.options[sel.selectedIndex];
        const ds = opt ? (parseInt(opt.getAttribute('data-stock') || '0', 10) || 0) : 0;
        let v = Math.max(1, parseInt(qty.value || '1', 10));
        if (ds > 0 && v > ds) { v = ds; centerNotify('warning','Cantidad ajustada','Supera stock disponible ('+ds+').'); }
        qty.value = String(v);
      });
    }
    // Si no hay productos activos disponibles, notificar
    if (sel) {
      var hasOptions = false;
      for (var i = 0; i < sel.options.length; i++) { if (sel.options[i].value) { hasOptions = true; break; } }
      if (!hasOptions) { centerNotify({icon:'info', title:'Sin inventario', text:'No hay productos activos disponibles para vender.'}); }
    }
    form && form.addEventListener('submit', function(e){
      if (body.querySelectorAll('tr').length === 0) {
        e.preventDefault();
        try { if (typeof bannerLoading === 'function') bannerLoading(false); } catch(_){}
        centerNotify('warning','Aviso','Agrega al menos un producto');
      }
    });

    // Initialize submit state on load
    toggleSubmit();
  })();
</script>
