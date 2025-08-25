<?php use App\Core\View; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">
      <i class="fas fa-cash-register mr-2 text-primary" aria-hidden="true"></i>
      Ventas del día
    </h3>
    <div class="d-flex align-items-center">
      <form class="form-inline mr-2" method="get" action="<?= BASE_URL ?>/sales">
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text">ID</span></div>
          <input type="text" pattern="[0-9]*" inputmode="numeric" title="Solo números" class="form-control" name="id" value="<?= View::e($filterId ?? '') ?>" placeholder="#">
          <div class="input-group-append"><button class="btn btn-outline-secondary"><i class="fas fa-search mr-1" aria-hidden="true"></i> Buscar</button></div>
        </div>
      </form>
      <form class="form-inline mr-2" method="get" action="<?= BASE_URL ?>/sales">
        <div class="input-group input-group-sm mr-1">
          <div class="input-group-prepend"><span class="input-group-text">Desde</span></div>
          <input type="date" class="form-control" name="from" value="<?= View::e($from ?? '') ?>">
        </div>
        <div class="input-group input-group-sm mr-1">
          <div class="input-group-prepend"><span class="input-group-text">Hasta</span></div>
          <input type="date" class="form-control" name="to" value="<?= View::e($to ?? '') ?>">
        </div>
        <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter mr-1" aria-hidden="true"></i> Filtrar</button>
      </form>
      <div class="mr-2">
        <a href="<?= BASE_URL ?>/sales/template" id="btnDownloadSalesExcelTemplate" class="btn btn-link btn-sm">Plantilla Excel</a>
        <a href="<?= BASE_URL ?>/sales/export?from=<?= urlencode($from ?? '') ?>&to=<?= urlencode($to ?? '') ?>" class="btn btn-success btn-sm ml-1">
          <i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Exportar Excel
        </a>
      </div>
      <a href="<?= BASE_URL ?>/sales/all" class="btn btn-link btn-sm mr-2"><i class="fas fa-list mr-1" aria-hidden="true"></i> Ventas (todas)</a>
      <a href="<?= BASE_URL ?>/sales/create" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Nueva venta</a>
    </div>
  </div>
  <div class="table-responsive">
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
  </div>
</div>

<script>
  (function(){ /* Import button removed by request */ })();
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
