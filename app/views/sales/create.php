<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title d-flex align-items-center">
      <i class="fas fa-cart-plus mr-2 text-primary" aria-hidden="true"></i>
      Registrar venta
    </h3>
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
    </style>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= View::e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>/sales/store" data-loading-text="Guardando venta..." id="cartForm">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-row align-items-end">
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
              <option value="<?= View::e($pr['id']) ?>" <?= $isExpired ? 'disabled' : '' ?> data-sku="<?= View::e($pr['sku']) ?>" data-name="<?= View::e($pr['name']) ?>" data-price="<?= View::e(number_format((float)($pr['price'] ?? 0), 2, '.', '')) ?>" data-expired="<?= $isExpired ? '1' : '0' ?>" data-status="<?= View::e($status) ?>" data-stock="<?= View::e($pr['stock'] ?? 0) ?>" title="<?= $isExpired ? 'Producto vencido' : (!empty($expires) ? ('Vence: ' . View::e($expires)) : '') ?>">
                <?= View::e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted d-block d-md-none">Los productos vencidos aparecen deshabilitados.</small>
        </div>
        <div class="form-group col-6 col-md-2 col-lg-2 mb-0">
          <label class="mb-1">Cantidad</label>
          <input type="number" min="1" step="1" id="pickQty" value="1" class="form-control">
        </div>
        <div class="form-group col-6 col-md-2 col-lg-2 mb-0">
          <label class="mb-1">Precio unitario</label>
          <input type="number" min="0" step="0.01" id="pickPrice" value="0.00" class="form-control">
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
        <div class="form-group col-md-6">
          <label>Nombre del cliente</label>
          <input type="text" name="customer_name" class="form-control" placeholder="Nombre y apellido">
        </div>
        <div class="form-group col-md-6">
          <label>Teléfono</label>
          <input type="text" name="customer_phone" class="form-control" placeholder="(000) 000-0000">
        </div>
      </div>

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
              <th class="text-right" id="cartTotal">$0.00</th>
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
    const addBtn = document.getElementById('btnAddItem');
    const body = document.getElementById('cartBody');
    const totalEl = document.getElementById('cartTotal');
    const form = document.getElementById('cartForm');
    const submitBtn = form ? form.querySelector('button[type="submit"], button:not([type])') : null;

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

    function fmt(n){ return new Intl.NumberFormat('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2}).format(n); }
    function recalc(){
      let t = 0;
      body.querySelectorAll('tr').forEach(tr => {
        const q = parseFloat(tr.querySelector('input[name="qty[]"]').value || '0');
        const p = parseFloat(tr.querySelector('input[name="unit_price[]"]').value || '0');
        const imp = q * p;
        tr.querySelector('.line-import').textContent = '$' + fmt(imp);
        t += imp;
      });
      totalEl.textContent = '$' + fmt(t);
      toggleSubmit();
    }
    function toggleSubmit(){
      if (!submitBtn) return;
      const hasItems = !!body.querySelector('tr');
      submitBtn.disabled = !hasItems;
      submitBtn.classList.toggle('disabled', !hasItems);
      submitBtn.title = hasItems ? '' : 'Agrega al menos un producto';
    }
    function updatePriceFromSelection(){
      const opt = sel && sel.options[sel.selectedIndex];
      if (opt) {
        const dp = opt.getAttribute('data-price');
        if (dp !== null) { price.value = (parseFloat(dp || '0') || 0).toFixed(2); }
      }
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
      const status = opt.getAttribute('data-status') || 'ok';
      const stock = parseInt(opt.getAttribute('data-stock') || '0', 10);
      if (!isFinite(stock) || stock <= 0) { centerNotify('info','Sin stock','Este producto no tiene stock disponible'); return; }
      let q = Math.max(1, parseInt(qty.value || '1', 10));
      if (q > stock) { centerNotify('warning','Cantidad ajustada','Supera stock disponible (' + stock + '). Se ajustó a ' + stock); q = stock; }
      const p = Math.max(0, parseFloat(price.value || '0'));
      const tr = document.createElement('tr');
      const idx = body.querySelectorAll('tr').length + 1;
      tr.innerHTML = `
        <td class="align-middle">${idx}</td>
        <td class="align-middle">${sku}</td>
        <td class="align-middle" title="Precio: $${p.toFixed(2)} | Stock: ${isNaN(stock)?0:stock}">${name} ${badgeFor(status)}</td>
        <td class="text-right"><input type="number" class="form-control form-control-sm text-right" name="qty[]" min="1" step="1" value="${q}"></td>
        <td class="text-right"><input type="number" class="form-control form-control-sm text-right" name="unit_price[]" min="0" step="0.01" value="${p.toFixed(2)}"></td>
        <td class="text-right line-import">$0.00</td>
        <td>
          <input type="hidden" name="product_id[]" value="${pid}">
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
    function resequence(){
      let i = 1;
      body.querySelectorAll('tr').forEach(tr => { tr.firstElementChild.textContent = i++; });
    }
    addBtn && addBtn.addEventListener('click', addItem);
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
    sel && sel.addEventListener('change', updatePriceFromSelection);
    // Initialize price on page load for current selection
    updatePriceFromSelection();
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
