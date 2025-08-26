<?php use App\Core\View; ?>
<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
      <h3 class="card-title mb-2 mb-md-0">
        <i class="fas fa-receipt mr-2 text-primary" aria-hidden="true"></i>
        Ventas
      </h3>
      <div class="btn-toolbar mb-0" role="toolbar" aria-label="Toolbar ventas">
        <div class="btn-group mr-2 mb-2" role="group" aria-label="Accesos rápidos">
          <a href="<?= BASE_URL ?>/sales" class="btn btn-link btn-sm"><i class="fas fa-calendar-day mr-1" aria-hidden="true"></i> Ventas del día</a>
        </div>
        <form class="form-inline mr-2 mb-2" method="get" action="<?= BASE_URL ?>/sales/all" role="search" aria-label="Buscar por ID">
          <div class="input-group input-group-sm">
            <div class="input-group-prepend"><span class="input-group-text">ID</span></div>
            <input type="text" pattern="[0-9]*" inputmode="numeric" title="Solo números" class="form-control" name="id" value="<?= View::e($filterId ?? '') ?>" placeholder="#">
            <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search mr-1" aria-hidden="true"></i> Buscar</button></div>
          </div>
        </form>
        <!-- Rango de fechas + Exportar -->
        <?php $today = (new \DateTimeImmutable('today', new \DateTimeZone('America/Bogota')))->format('Y-m-d'); ?>
        <form class="form-inline mb-2" method="get" action="<?= BASE_URL ?>/sales/export" target="_blank" id="salesExportForm" data-no-loader="1" aria-label="Exportar Excel">
          <div class="input-group input-group-sm mr-2">
            <div class="input-group-prepend"><span class="input-group-text">Desde</span></div>
            <input type="date" class="form-control" name="from" value="<?= View::e($_GET['from'] ?? $today) ?>">
            <div class="input-group-prepend"><span class="input-group-text">Hasta</span></div>
            <input type="date" class="form-control" name="to" value="<?= View::e($_GET['to'] ?? $today) ?>">
          </div>
          <div class="custom-control custom-checkbox mr-2">
            <input class="custom-control-input" type="checkbox" id="expAll" name="all" value="1">
            <label class="custom-control-label" for="expAll">Todas</label>
          </div>
          <button class="btn btn-success btn-sm" type="submit" title="Exportar a Excel">
            <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Exportar Excel
          </button>
        </form>
        <div class="btn-group ml-2 mb-2" role="group">
          <a href="<?= BASE_URL ?>/sales/create" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Nueva venta</a>
        </div>
      </div>
    </div>
  </div>
  <div class="table-responsive">
    <?php $hasFilters = !empty($filterId) || !empty($_GET['from']) || !empty($_GET['to']); ?>
    <?php if (empty($sales)): ?>
      <div class="ps-empty-state" role="status" aria-live="polite">
        <div class="box">
          <div class="title"><i class="fas fa-receipt mr-2" aria-hidden="true"></i> <?= $hasFilters ? 'No hay ventas que coincidan con los filtros' : 'No hay ventas' ?></div>
          <div class="desc"><?= $hasFilters ? 'Ajusta los filtros e inténtalo de nuevo.' : 'Registra una nueva venta para comenzar.' ?></div>
        </div>
      </div>
      <script>
        (function(){
          function showEmptyAllSales(){
            try {
              var hasFilters = <?= $hasFilters ? 'true' : 'false' ?>;
              var title = hasFilters ? 'No hay ventas que coincidan con los filtros' : 'No hay ventas';
              var text = hasFilters ? 'Ajusta los filtros e inténtalo de nuevo.' : 'Registra una nueva venta para comenzar.';
              if (window.Swal && typeof Swal.fire === 'function') {
                Swal.fire({ icon:'info', title:title, text:text, confirmButtonText:'Entendido', allowOutsideClick:true, allowEscapeKey:true });
              } else if (window.notify) {
                notify({ icon:'info', title:title, text:text, position:'center' });
              } else { alert(title); }
            } catch(_){}
          }
          if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showEmptyAllSales);
          else showEmptyAllSales();
        })();
      </script>
    <?php else: ?>
    <table class="table table-striped mb-0">
      <thead><tr><th>ID</th><th>SKU</th><th>Producto</th><th>Cant.</th><th>P. Unit</th><th>Total</th><th>Cliente</th><th>Teléfono</th><th>Atendido por</th><th>Fecha</th><th>Acciones</th></tr></thead>
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
            <td><?= View::e($s['customer_phone'] ?? '') ?></td>
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

<?php if (!empty($pagination) && is_array($pagination)): ?>
  <?php
    $pg = $pagination; $page = (int)($pg['page'] ?? 1); $pages = (int)($pg['pages'] ?? 1);
    $per = (int)($pg['per'] ?? 15); $total = (int)($pg['total'] ?? 0);
    $base = BASE_URL . '/sales/all';
    function salesAllPageUrl($base,$p,$per){ return $base . '?page=' . max(1,(int)$p) . '&per=' . (int)$per; }
  ?>
  <div class="d-flex justify-content-between align-items-center mt-2">
    <div class="text-muted small">Mostrando página <?= $page ?> de <?= $pages ?> (<?= $total ?> registros)</div>
    <nav aria-label="Paginación ventas">
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page <= 1 ? '#' : salesAllPageUrl($base,1,$per) ?>">Primera</a>
        </li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page <= 1 ? '#' : salesAllPageUrl($base,$page-1,$per) ?>">Anterior</a>
        </li>
        <li class="page-item disabled"><span class="page-link"><?= $page ?></span></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page >= $pages ? '#' : salesAllPageUrl($base,$page+1,$per) ?>">Siguiente</a>
        </li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $page >= $pages ? '#' : salesAllPageUrl($base,$pages,$per) ?>">Última</a>
        </li>
      </ul>
    </nav>
  </div>
<?php endif; ?>

<script>
  // Toggle rango vs todas
  (function(){
    var form = document.getElementById('salesExportForm'); if (!form) return;
    var ch = form.querySelector('#expAll');
    var f = form.querySelector('input[name="from"]');
    var t = form.querySelector('input[name="to"]');
    function sync(){
      var all = ch && ch.checked;
      if (f) f.disabled = !!all; if (t) t.disabled = !!all;
    }
    if (ch){ ch.addEventListener('change', sync); sync(); }
  })();
</script>
