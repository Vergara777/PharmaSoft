<?php use App\Core\View; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0 d-flex align-items-center">
      <i class="fas fa-cash-register mr-2 text-primary" aria-hidden="true"></i>
      <a href="<?= BASE_URL ?>/sales" class="text-reset" style="text-decoration: none;">
        Ventas del día
      </a>
      <small class="ml-2 text-muted d-none d-md-inline" aria-hidden="true">(clic para volver)</small>
    </h3>
    <div class="d-flex align-items-center">
      <form class="form-inline mr-2" method="get" action="<?= BASE_URL ?>/sales">
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text">ID</span></div>
          <input type="text" pattern="[0-9]*" inputmode="numeric" title="Solo números" class="form-control" name="id" value="<?= View::e($filterId ?? '') ?>" placeholder="#">
          <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search mr-1" aria-hidden="true"></i> Buscar</button></div>
        </div>
      </form>
      <form class="form-inline mr-2" method="get" action="<?= BASE_URL ?>/sales" aria-label="Filtrar por fecha">
        <div class="input-group input-group-sm mr-1">
          <div class="input-group-prepend"><span class="input-group-text">Desde</span></div>
          <input type="date" class="form-control" name="from" value="<?= View::e($from ?? '') ?>" aria-label="Fecha desde">
        </div>
        <div class="input-group input-group-sm mr-1">
          <div class="input-group-prepend"><span class="input-group-text">Hasta</span></div>
          <input type="date" class="form-control" name="to" value="<?= View::e($to ?? '') ?>" aria-label="Fecha hasta">
        </div>
        <button type="submit" class="btn btn-primary btn-sm mr-1" title="Filtrar por fecha"><i class="fas fa-filter mr-1" aria-hidden="true"></i> Filtrar por fecha</button>
        <button type="button" id="btnSalesToday" class="btn btn-outline-secondary btn-sm mr-1" title="Hoy"><i class="fas fa-calendar-day mr-1" aria-hidden="true"></i> Hoy</button>
        <button type="button" id="btnSalesClear" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros"><i class="fas fa-eraser mr-1" aria-hidden="true"></i> Limpiar</button>
      </form>
      <div class="mr-2">
        <a href="<?= BASE_URL ?>/sales/template" id="btnDownloadSalesExcelTemplate" class="btn btn-link btn-sm">Plantilla Excel</a>
        <?php $canExport = \App\Helpers\Auth::isAdmin(); ?>
        <?php if ($canExport): ?>
          <a href="<?= BASE_URL ?>/sales/export?from=<?= urlencode($from ?? '') ?>&to=<?= urlencode($to ?? '') ?>" id="btnExportSalesExcel" class="btn btn-success btn-sm ml-1" download data-no-loader>
            <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Exportar Excel
          </a>
        <?php endif; ?>
      </div>
      <a href="<?= BASE_URL ?>/sales/all" class="btn btn-link btn-sm mr-2"><i class="fas fa-list mr-1" aria-hidden="true"></i> Ventas (todas)</a>
      <a href="<?= BASE_URL ?>/sales/create" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Nueva venta</a>
    </div>
  </div>
  <div class="table-responsive">
    <?php if (empty($sales)): ?>
      <?php $hasFilters = !empty($filterId) || !empty($from) || !empty($to); ?>
      <div class="ps-empty-state" role="status" aria-live="polite">
        <div class="box">
          <div class="title"><i class="fas fa-cash-register mr-2" aria-hidden="true"></i> <?= $hasFilters ? 'No hay ventas que coincidan con los filtros' : 'No hay ventas registradas hoy' ?></div>
          <div class="desc"><?= $hasFilters ? 'Ajusta los filtros e inténtalo de nuevo.' : 'Registra una nueva venta para comenzar.' ?></div>
        </div>
      </div>
      <script>
        (function(){
          function showEmptySales(){
            try {
              var hasFilters = <?= $hasFilters ? 'true' : 'false' ?>;
              var title = hasFilters ? 'No hay ventas que coincidan con los filtros' : 'No hay ventas registradas hoy';
              var text = hasFilters ? 'Ajusta los filtros e inténtalo de nuevo.' : 'Registra una nueva venta para comenzar.';
              if (window.Swal && typeof Swal.fire === 'function') {
                Swal.fire({ icon:'info', title:title, text:text, confirmButtonText:'Entendido', allowOutsideClick:true, allowEscapeKey:true });
              } else if (window.notify) {
                notify({ icon:'info', title:title, text:text, position:'center' });
              } else { alert(title); }
            } catch(_){}
          }
          if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showEmptySales);
          else showEmptySales();
        })();
  // Modal Ver venta: reusa lógica de Movements con fallback si no hay Bootstrap
  (function(){
    function show(el, on){ if (!el) return; el.style.display = on ? '' : 'none'; }
    function setText(id, t){ var el = document.getElementById(id); if (el) el.textContent = t || ''; }
    function formatMoney(n){ try { return new Intl.NumberFormat('es-CL', { style:'currency', currency:'CLP', maximumFractionDigits:0 }).format(n||0); } catch(_){ return '$' + String(n||0); } }
    function openModalById(id){
      if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery('#'+id).modal('show'); return; }
      var el = document.getElementById(id); if (!el) return;
      el.classList.add('show'); el.style.display='block'; el.removeAttribute('aria-hidden'); el.setAttribute('aria-modal','true');
      var bd = document.createElement('div'); bd.className='modal-backdrop fade show'; bd.id=id+'__backdrop'; document.body.appendChild(bd);
      el.querySelectorAll('[data-dismiss="modal"], .close').forEach(function(btn){ btn.addEventListener('click', function(){ closeModalById(id); }, {once:true}); });
    }
    function closeModalById(id){
      var el = document.getElementById(id); if (!el) return;
      el.classList.remove('show'); el.style.display='none'; el.setAttribute('aria-hidden','true'); el.removeAttribute('aria-modal');
      var bd = document.getElementById(id+'__backdrop'); if (bd) bd.parentNode.removeChild(bd);
    }
    document.addEventListener('DOMContentLoaded', function(){
      var tbody = document.querySelector('.table.table-striped tbody');
      if (!tbody) return;
      tbody.addEventListener('click', function(e){
        var btn = e.target.closest('.ps-view-sale');
        if (!btn) return;
        var id = parseInt(btn.getAttribute('data-id')||'0',10); if (!id) return;
        var loading = document.getElementById('psSaleLoading');
        var err = document.getElementById('psSaleError');
        var content = document.getElementById('psSaleContent');
        show(loading, true); show(err, false); show(content, false);
        openModalById('psSaleModal');
        fetch((window.BASE_URL||'<?= rtrim(BASE_URL,'/') ?>') + '/sales/show/' + id, { headers: { 'Accept':'application/json' }})
          .then(function(r){ if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
          .then(function(data){
            show(loading, false);
            if (!data || !data.ok){ err.textContent = (data && data.message) ? data.message : 'No se pudo cargar la venta.'; show(err,true); return; }
            var s = data.sale || {};
            setText('psSaleId', '#' + String(s.id||id));
            setText('psSaleDate', s.created_at || '');
            var cust = (s.customer_name||'') || 'N/D'; setText('psSaleCustomer', cust);
            var contact = []; if (s.customer_phone) contact.push(s.customer_phone); if (s.customer_email) contact.push(s.customer_email);
            setText('psSaleContact', contact.join(' · '));
            var attended = ((s.user_name||'') + (s.user_role?(' ('+s.user_role+')'):'')).trim(); setText('psSaleUser', attended);
            var items = Array.isArray(s.items) ? s.items : [];
            var tbodyItems = document.getElementById('psSaleItems'); if (tbodyItems) tbodyItems.innerHTML = '';
            var total = 0;
            items.forEach(function(it, idx){
              var qty = parseInt(it.qty||0,10) || 0;
              var up = parseInt(it.unit_price||0,10) || 0;
              var sub = qty*up; total += sub;
              var tr = document.createElement('tr');
              tr.innerHTML = '<td class="text-muted">'+ (idx+1) +'</td>'+
                             '<td>'+ (it.sku?String(it.sku).replace(/</g,'&lt;'):'') +'</td>'+
                             '<td>'+ (it.name?String(it.name).replace(/</g,'&lt;'):'') +'</td>'+
                             '<td class="text-right mono">'+ qty +'</td>'+
                             '<td class="text-right mono">'+ formatMoney(up) +'</td>'+
                             '<td class="text-right mono">'+ formatMoney(sub) +'</td>';
              if (tbodyItems) tbodyItems.appendChild(tr);
            });
            setText('psSaleItemsCount', String(items.length));
            setText('psSaleTotal', formatMoney(total));
            var foot = document.getElementById('psSaleTotalFoot'); if (foot) foot.textContent = formatMoney(total);
            var inv = document.getElementById('psSaleInvoiceLink'); if (inv) inv.href = (window.BASE_URL||'<?= rtrim(BASE_URL,'/') ?>') + '/sales/invoice/' + (s.id||id);
            show(content, true);
          })
          .catch(function(e){ show(loading,false); err.textContent = 'Error al cargar: ' + e.message; show(err,true); });
      }, false);
    });
  })();
      </script>
    <?php else: ?>
    <table class="table table-striped mb-0">
      <thead><tr><th>ID</th><th>SKU</th><th>Producto</th><th>Cant.</th><th>P. Unit</th><th>Total</th><th>Cliente</th><th>Contacto</th><th>Atendido por</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php foreach ($sales as $s): ?>
          <?php 
            $isCart = !empty($s['item_count']) && (int)$s['item_count'] > 0 && empty($s['product_id']);
            $sku = $isCart ? ($s['first_sku'] ?? '') : ($s['sku'] ?? '');
            $name = $isCart ? ($s['first_name'] ?? '') : ($s['name'] ?? '');
            if ($isCart && (int)$s['item_count'] > 1) {
              $name = trim($name . ' +' . ((int)$s['item_count'] - 1) . ' más');
            }
            $qty = $isCart ? (int)($s['items_qty'] ?? 0) : (int)($s['qty'] ?? 0);
            $punit = null;
            if ($isCart) {
              $q = (float)($s['items_qty'] ?? 0);
              $t = (float)($s['total'] ?? 0);
              if ($q > 0) { $punit = round($t / $q); }
            } else {
              if (isset($s['unit_price'])) { $punit = (int)$s['unit_price']; }
            }
            $attended = trim(($s['user_name'] ?? '') . ' ' . (($s['user_role'] ?? '') ? '(' . $s['user_role'] . ')' : ''));
          ?>
          <tr>
            <td><?= View::e($s['id']) ?></td>
            <td><?= View::e($sku) ?></td>
            <td><?= View::e($name) ?></td>
            <td><?= View::e($qty) ?></td>
            <td><?= ($punit !== null) ? ('$' . number_format((float)$punit, 0, ',', '.')) : '-' ?></td>
            <td>$<?= number_format((float)($s['total'] ?? 0), 0, ',', '.') ?></td>
            <td><?= View::e($s['customer_name'] ?? '') ?></td>
            <td>
              <?= View::e($s['customer_phone'] ?? '') ?>
              <?php if (!empty($s['customer_email'])): ?>
                <br><small class="text-muted"><?= View::e($s['customer_email']) ?></small>
              <?php endif; ?>
            </td>
            <td><?= View::e($attended) ?></td>
            <td><?= View::e($s['created_at']) ?></td>
            <td>
              <button type="button" class="btn btn-sm ps-btn-view mr-1 ps-view-sale" data-id="<?= (int)$s['id'] ?>">
                <i class="fas fa-eye mr-1" aria-hidden="true"></i> Ver venta
              </button>
              <a class="btn btn-sm btn-outline-primary mr-1" target="_blank" href="<?= BASE_URL ?>/sales/invoice/<?= View::e($s['id']) ?>">
                <i class="fas fa-receipt mr-1" aria-hidden="true"></i> Ver detalles
              </a>
              <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= BASE_URL ?>/sales/invoice/<?= View::e($s['id']) ?>?download=1">
                <i class="fas fa-file-pdf mr-1" aria-hidden="true"></i> PDF
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Modal para ver detalle de Venta (Sales Index) -->
<style>
  /* Botón verde gradiente para acciones "Ver …" */
  .ps-btn-view { background: linear-gradient(135deg, #2ecc71, #27ae60); color:#fff; border:0; }
  .ps-btn-view:hover, .ps-btn-view:focus { color:#fff; filter: brightness(0.95); }
  /* Scoped styles for the Sale Modal */
  #psSaleModal .modal-header {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    color: #fff;
  }
  #psSaleModal .modal-title { font-weight: 600; }
  .ps-chip { display:inline-flex; align-items:center; padding:2px 8px; border-radius:999px; font-size:12px; background:#f1f3f5; color:#495057; margin-right:6px; }
  .ps-chip .ico { margin-right:6px; opacity:.8; }
  .ps-summary { display:flex; gap:8px; flex-wrap:wrap; margin:6px 0 10px; }
  .ps-summary .ps-chip.total { background:#e7f5ff; color:#0b7285; }
  .ps-summary .ps-chip.items { background:#fff4e6; color:#d9480f; }
  #psSaleModal .table thead th { background:#f8f9fa; border-top:0; }
  #psSaleModal .table tfoot th { background:#f8f9fa; }
  #psSaleModal .muted { color:#6c757d; }
  #psSaleModal .section-title { font-size:14px; font-weight:600; color:#495057; margin:8px 0 6px; text-transform:uppercase; letter-spacing:.02em; }
  #psSaleModal .kv { font-size: 13px; }
  #psSaleModal .kv strong { color:#343a40; }
  #psSaleModal .mono { font-variant-numeric: tabular-nums; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  #psSaleModal .badge-soft { background:#f1f3f5; color:#495057; }
  #psSaleModal .divider { height:1px; background:#e9ecef; margin:8px 0; }
</style>
<div class="modal fade" id="psSaleModal" tabindex="-1" role="dialog" aria-labelledby="psSaleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="psSaleModalLabel">
          <i class="fas fa-shopping-bag mr-2" aria-hidden="true"></i>
          Detalle de venta <span class="badge badge-light ml-2 mono" id="psSaleId"></span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="psSaleLoading" class="text-center my-3" style="display:none"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
        <div id="psSaleError" class="alert alert-danger" style="display:none"></div>
        <div id="psSaleContent" style="display:none">
          <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="kv mb-1"><span class="muted">Fecha:</span> <strong id="psSaleDate"></strong></div>
            <div class="ps-summary">
              <span class="ps-chip items"><span class="ico"><i class="fas fa-list-ol"></i></span>Items: <span class="mono ml-1" id="psSaleItemsCount">0</span></span>
              <span class="ps-chip total"><span class="ico"><i class="fas fa-dollar-sign"></i></span>Total: <span class="mono ml-1" id="psSaleTotal"></span></span>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="section-title">Cliente</div>
              <div class="kv"><strong id="psSaleCustomer"></strong></div>
              <div class="kv"><span class="muted">Contacto:</span> <span id="psSaleContact"></span></div>
            </div>
            <div class="col-md-6">
              <div class="section-title">Atendido por</div>
              <div class="kv"><strong id="psSaleUser"></strong></div>
            </div>
          </div>
          <div class="divider"></div>
          <div class="section-title">Productos</div>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead><tr><th>#</th><th>SKU</th><th>Producto</th><th class="text-right">Cant.</th><th class="text-right">P. Unit</th><th class="text-right">Subtotal</th></tr></thead>
              <tbody id="psSaleItems"></tbody>
              <tfoot><tr><th colspan="5" class="text-right">Total</th><th class="text-right mono" id="psSaleTotalFoot"></th></tr></tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a id="psSaleInvoiceLink" target="_blank" class="btn btn-primary" href="#"><i class="fas fa-file-invoice mr-1"></i> Ver factura</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
  </div>

<script>
  (function(){ /* Import button removed by request */ })();
  // Export: sin interceptar; el enlace descarga directamente
  // Quick actions: Hoy / Limpiar for date filters
  (function(){
    function fmt(n){ return (n < 10 ? '0' + n : '' + n); }
    function todayYMD(){
      var d = new Date(<?= json_encode(date('Y-m-d')) ?>);
      // ensure local date string yyyy-mm-dd
      var y = d.getFullYear(); var m = fmt(d.getMonth()+1); var da = fmt(d.getDate());
      return y + '-' + m + '-' + da;
    }
    function qs(sel){ return document.querySelector(sel); }
    document.addEventListener('DOMContentLoaded', function(){
      var btnToday = document.getElementById('btnSalesToday');
      var btnClear = document.getElementById('btnSalesClear');
      // Select the date filter form specifically (avoid picking the ID search form)
      var form = document.querySelector('form[action$="/sales"][aria-label="Filtrar por fecha"]');
      var inpFrom = form ? form.querySelector('input[name="from"]') : null;
      var inpTo = form ? form.querySelector('input[name="to"]') : null;
      if (btnToday && form && inpFrom && inpTo) {
        btnToday.addEventListener('click', function(){
          try {
            var t = todayYMD();
            inpFrom.value = t; inpTo.value = t;
            form.submit();
          } catch(_){ }
        });
      }
      if (btnClear && form && inpFrom && inpTo) {
        btnClear.addEventListener('click', function(){
          try {
            inpFrom.value = ''; inpTo.value = '';
            // submit to return to daily default
            form.submit();
          } catch(_){ }
        });
      }
    });
  })();

  // Habilitar botón "Ver venta" cuando hay datos en tabla (fuera de branch vacío)
  (function(){
    function show(el, on){ if (!el) return; el.style.display = on ? '' : 'none'; }
    function setText(id, t){ var el = document.getElementById(id); if (el) el.textContent = t || ''; }
    function formatMoney(n){ try { return new Intl.NumberFormat('es-CL', { style:'currency', currency:'CLP', maximumFractionDigits:0 }).format(n||0); } catch(_){ return '$' + String(n||0); } }
    function openModalById(id){
      if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery('#'+id).modal('show'); return; }
      var el = document.getElementById(id); if (!el) return;
      el.classList.add('show'); el.style.display='block'; el.removeAttribute('aria-hidden'); el.setAttribute('aria-modal','true');
      var bd = document.createElement('div'); bd.className='modal-backdrop fade show'; bd.id=id+'__backdrop'; document.body.appendChild(bd);
      el.querySelectorAll('[data-dismiss="modal"], .close').forEach(function(btn){ btn.addEventListener('click', function(){ closeModalById(id); }, {once:true}); });
    }
    function closeModalById(id){
      var el = document.getElementById(id); if (!el) return;
      el.classList.remove('show'); el.style.display='none'; el.setAttribute('aria-hidden','true'); el.removeAttribute('aria-modal');
      var bd = document.getElementById(id+'__backdrop'); if (bd) bd.parentNode.removeChild(bd);
    }
    document.addEventListener('DOMContentLoaded', function(){
      var tbody = document.querySelector('table.table tbody');
      if (!tbody) return;
      tbody.addEventListener('click', function(e){
        var btn = e.target.closest('.ps-view-sale');
        if (!btn) return;
        var id = parseInt(btn.getAttribute('data-id')||'0',10); if (!id) return;
        var loading = document.getElementById('psSaleLoading');
        var err = document.getElementById('psSaleError');
        var content = document.getElementById('psSaleContent');
        show(loading, true); show(err, false); show(content, false);
        openModalById('psSaleModal');
        fetch((window.BASE_URL||'<?= rtrim(BASE_URL,'/') ?>') + '/sales/show/' + id, { headers: { 'Accept':'application/json' }})
          .then(function(r){ if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
          .then(function(data){
            show(loading, false);
            if (!data || !data.ok){ err.textContent = (data && data.message) ? data.message : 'No se pudo cargar la venta.'; show(err,true); return; }
            var s = data.sale || {};
            setText('psSaleId', '#' + String(s.id||id));
            setText('psSaleDate', s.created_at || '');
            var cust = (s.customer_name||'') || 'N/D'; setText('psSaleCustomer', cust);
            var contact = []; if (s.customer_phone) contact.push(s.customer_phone); if (s.customer_email) contact.push(s.customer_email);
            setText('psSaleContact', contact.join(' · '));
            var attended = ((s.user_name||'') + (s.user_role?(' ('+s.user_role+')'):'')).trim(); setText('psSaleUser', attended);
            var items = Array.isArray(s.items) ? s.items : [];
            var tbodyItems = document.getElementById('psSaleItems'); if (tbodyItems) tbodyItems.innerHTML = '';
            var total = 0;
            items.forEach(function(it, idx){
              var qty = parseInt(it.qty||0,10) || 0;
              var up = parseInt(it.unit_price||0,10) || 0;
              var sub = qty*up; total += sub;
              var tr = document.createElement('tr');
              tr.innerHTML = '<td class="text-muted">'+ (idx+1) +'</td>'+
                             '<td>'+ (it.sku?String(it.sku).replace(/</g,'&lt;'):'') +'</td>'+
                             '<td>'+ (it.name?String(it.name).replace(/</g,'&lt;'):'') +'</td>'+
                             '<td class="text-right mono">'+ qty +'</td>'+
                             '<td class="text-right mono">'+ formatMoney(up) +'</td>'+
                             '<td class="text-right mono">'+ formatMoney(sub) +'</td>';
              if (tbodyItems) tbodyItems.appendChild(tr);
            });
            setText('psSaleItemsCount', String(items.length));
            setText('psSaleTotal', formatMoney(total));
            var foot = document.getElementById('psSaleTotalFoot'); if (foot) foot.textContent = formatMoney(total);
            var inv = document.getElementById('psSaleInvoiceLink'); if (inv) inv.href = (window.BASE_URL||'<?= rtrim(BASE_URL,'/') ?>') + '/sales/invoice/' + (s.id||id);
            show(content, true);
          })
          .catch(function(e){ show(loading,false); err.textContent = 'Error al cargar: ' + e.message; show(err,true); });
      }, false);
    });
  })();
</script>

<?php if (!empty($pagination) && is_array($pagination)): ?>
  <?php
    $pg = $pagination; $page = (int)($pg['page'] ?? 1); $pages = (int)($pg['pages'] ?? 1);
    $per = (int)($pg['per'] ?? 15); $total = (int)($pg['total'] ?? 0);
    $base = BASE_URL . '/sales';
    function salesPageUrl($base,$p,$per){ return $base . '?page=' . max(1,(int)$p) . '&per=' . (int)$per; }
  ?>
  <div class="d-flex justify-content-between align-items-center mt-2">
    <div class="text-muted small">Mostrando página <?= $page ?> de <?= $pages ?> (<?= $total ?> registros)</div>
    <nav aria-label="Paginación ventas del día">
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page <= 1 ? '#' : salesPageUrl($base,1,$per) ?>">Primera</a>
        </li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page <= 1 ? '#' : salesPageUrl($base,$page-1,$per) ?>">Anterior</a>
        </li>
        <li class="page-item disabled"><span class="page-link"><?= $page ?></span></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page >= $pages ? '#' : salesPageUrl($base,$page+1,$per) ?>">Siguiente</a>
        </li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page >= $pages ? '#' : salesPageUrl($base,$pages,$per) ?>">Última</a>
        </li>
      </ul>
    </nav>
  </div>
<?php endif; ?>
