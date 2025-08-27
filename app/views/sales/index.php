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
        <a href="<?= BASE_URL ?>/sales/export?from=<?= urlencode($from ?? '') ?>&to=<?= urlencode($to ?? '') ?>" id="btnExportSalesExcel" class="btn btn-success btn-sm ml-1">
          <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Exportar Excel
        </a>
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

<script>
  (function(){ /* Import button removed by request */ })();
  // Sales Excel/Template download: hidden iframe + quick button spinner (no overlay, never stuck)
  (function(){
    function setBtnLoading(btn, isLoading){
      if (!btn) return;
      if (isLoading) {
        if (!btn._origHtml) btn._origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('disabled');
        var label = btn.getAttribute('data-loading') || 'Descargando…';
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>' + label;
      } else {
        btn.disabled = false;
        btn.classList.remove('disabled');
        if (btn._origHtml) btn.innerHTML = btn._origHtml;
      }
    }
    function withIframeDownload(anchor){
      if (!anchor) return;
      anchor.addEventListener('click', function(e){
        try { if (!anchor.href) return; } catch(_){ }
        e.preventDefault();
        var url = anchor.href;
        setBtnLoading(anchor, true);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.setAttribute('aria-hidden','true');
        var restored = false;
        function restore(){ if (restored) return; restored = true; setBtnLoading(anchor, false); }
        var done = function(){ try { iframe.remove(); } catch(_){ } restore(); };
        iframe.onload = done; iframe.onerror = done;
        document.body.appendChild(iframe);
        iframe.src = url;
        // Fallback: auto-restore quickly even if onload doesn't fire
        setTimeout(restore, 1500);
      }, false);
    }
    document.addEventListener('DOMContentLoaded', function(){
      try {
        var btnExport = document.getElementById('btnExportSalesExcel');
        var btnTpl = document.getElementById('btnDownloadSalesExcelTemplate');
        if (btnExport) btnExport.setAttribute('data-loading','Exportando…');
        if (btnTpl) btnTpl.setAttribute('data-loading','Preparando…');
        withIframeDownload(btnExport);
        withIframeDownload(btnTpl);
      } catch(_){ }
    });
  })();
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
