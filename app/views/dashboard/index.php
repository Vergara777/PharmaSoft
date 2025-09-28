<?php use App\Core\View; ?>
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3><?= View::e($totalProducts) ?></h3><p>Productos</p></div>
      <div class="icon"><i class="fas fa-pills"></i></div>
      <a href="<?= BASE_URL ?>/products" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= View::e($lowStock) ?></h3><p>Bajo stock (≤ <?= defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5 ?>)</p></div>
      <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
      <a href="<?= BASE_URL ?>/products?stock=low" class="small-box-footer">Inventario <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner"><h3><?= View::e($expired ?? 0) ?></h3><p>Vencidos</p></div>
      <div class="icon"><i class="fas fa-ban"></i></div>
      <a href="<?= BASE_URL ?>/products/expired" class="small-box-footer">Ver <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= View::e($expiringSoon ?? 0) ?></h3><p>Por vencer (31d)</p></div>
      <div class="icon"><i class="fas fa-clock"></i></div>
      <a href="<?= BASE_URL ?>/products/expiring-30" class="small-box-footer">Ver <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3>$<?= number_format((float)$todaySalesTotal, 0, ',', '.') ?></h3><p>Ventas de hoy (<?= View::e($todaySalesCount) ?>)</p></div>
      <div class="icon"><i class="fas fa-cash-register"></i></div>
      <a href="<?= BASE_URL ?>/sales" class="small-box-footer">Ir a ventas <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner"><h3>$<?= number_format((float)($monthSalesTotal ?? 0), 0, ',', '.') ?></h3><p>Ventas del mes</p></div>
      <div class="icon"><i class="fas fa-calendar-alt"></i></div>
      <a href="<?= BASE_URL ?>/sales?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-t') ?>" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-secondary">
      <div class="inner"><h3>$<?= number_format((float)($yearSalesTotal ?? 0), 0, ',', '.') ?></h3><p>Ventas del año</p></div>
      <div class="icon"><i class="fas fa-calendar"></i></div>
      <a href="<?= BASE_URL ?>/sales?from=<?= date('Y-01-01') ?>&to=<?= date('Y-12-31') ?>" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-teal">
      <div class="inner"><h3>$<?= number_format((float)($monthProfit ?? 0), 0, ',', '.') ?></h3><p>Utilidad del mes</p></div>
      <div class="icon"><i class="fas fa-chart-line"></i></div>
      <a href="<?= BASE_URL ?>/sales?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-t') ?>" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-navy">
      <div class="inner"><h3>$<?= number_format((float)($yearProfit ?? 0), 0, ',', '.') ?></h3><p>Utilidad del año</p></div>
      <div class="icon"><i class="fas fa-coins"></i></div>
      <a href="<?= BASE_URL ?>/sales?from=<?= date('Y-01-01') ?>&to=<?= date('Y-12-31') ?>" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<?php if (!empty($expired) && (int)$expired > 0): ?>
<div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
  <div>
    <strong><?= View::e($expired) ?></strong> producto(s) vencido(s). Recomendado retirar del inventario.
  </div>
  <form method="post" action="<?= BASE_URL ?>/products/retire-expired" class="mb-0 js-confirmable" id="formRetireExpiredAlert" autocomplete="off"
        data-confirm-title="Retirar vencidos"
        data-confirm-text="¿Desea retirar todos los productos vencidos? Serán marcados como 'retirados' y su stock se pondrá en 0."
        data-confirm-ok="Retirar"
        data-confirm-cancel="Cancelar"
        data-loading-text="Retirando vencidos...">
    <input type="hidden" name="csrf" value="<?= View::e(App\Helpers\Security::csrfToken()) ?>">
    <button type="submit" id="btnRetireExpiredAlert" class="btn btn-retire-expired btn-sm"><i class="fas fa-box-open mr-1"></i>Retirar vencidos</button>
  </form>
</div>
<?php endif; ?>

<!-- Floating Cart Button and Modal -->
<button id="btnCartFloating" class="cart-fab" title="Ver carrito" aria-label="Ver carrito">
  <i class="fas fa-shopping-cart" aria-hidden="true"></i>
  <span class="badge badge-danger cart-fab-badge" id="cartFabCount" style="display:none;">0</span>
  <span class="sr-only">Carrito</span>
</button>

<div id="cartModal" class="ps-modal" style="display:none;">
  <div class="ps-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cartModalTitle">
    <div class="ps-modal-header">
      <h5 id="cartModalTitle" class="mb-0 d-flex align-items-center">
        <i class="fas fa-shopping-cart mr-2 text-primary" aria-hidden="true"></i>
        Carrito
        <span class="badge badge-secondary ml-2" id="cartModalCount">0</span>
      </h5>
      <button type="button" class="close" id="cartModalClose" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="ps-modal-body" id="cartModalBody">
      <div class="text-muted">No hay borrador de carrito en este navegador.</div>
    </div>
    <div class="ps-modal-footer d-flex justify-content-between align-items-center" id="cartModalFooter" style="display:none;">
      <div class="text-muted small">Borrador guardado localmente.</div>
      <div class="d-flex align-items-center" style="gap:.5rem;">
        <div class="mr-3"><strong>Total:</strong> <span id="cartModalTotal">$0</span></div>
        <a href="<?= BASE_URL ?>/sales/create" class="btn btn-outline-primary btn-sm"><i class="fas fa-cash-register mr-1"></i> Ir a ventas</a>
        <button type="button" class="btn btn-outline-danger btn-sm" id="cartModalClear"><i class="fas fa-trash mr-1"></i> Vaciar</button>
      </div>
    </div>
  </div>
  <div class="ps-modal-backdrop" id="cartModalBackdrop"></div>
  </div>

<div id="invSummary" class="alert alert-secondary d-flex align-items-center" role="alert" style="display:none;">
  <i class="fas fa-bell mr-2 text-warning" aria-hidden="true"></i>
  <div class="mr-3">
    <strong>Resumen de inventario:</strong>
    <span class="badge badge-danger ml-2"><i class="fas fa-ban mr-1" aria-hidden="true"></i> Vencidos: <?= View::e($expired ?? 0) ?></span>
    <span class="badge badge-warning ml-2"><i class="fas fa-clock mr-1" aria-hidden="true"></i> Por vencer (31d): <?= View::e($expiringSoon ?? 0) ?></span>
    <span class="badge badge-danger ml-2"><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Sin stock: <?= View::e($zeroStock ?? 0) ?></span>
    <span class="badge badge-warning ml-2"><i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i> Bajo stock (≤ <?= defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5 ?>): <?= View::e($lowStock ?? 0) ?></span>
    <?php $okInv = max(0, (int)$totalProducts - (int)($lowStock ?? 0)); ?>
    <span class="badge badge-success ml-2"><i class="fas fa-check mr-1" aria-hidden="true"></i> OK: <?= View::e($okInv) ?></span>
  </div>
  <div class="ml-auto inv-actions d-flex">
    <a href="<?= BASE_URL ?>/products" class="btn btn-primary inv-btn">
      <i class="fas fa-pills inv-icon" aria-hidden="true"></i>
      <span class="inv-text">Gestionar<br>productos</span>
    </a>
    <a href="<?= BASE_URL ?>/products/expired" class="btn btn-danger inv-btn">
      <i class="fas fa-ban inv-icon" aria-hidden="true"></i>
      <span class="inv-text">Vencidos</span>
      <span class="badge badge-light inv-badge"><?= View::e($expired ?? 0) ?></span>
    </a>
    <!-- Tercero: Retirar vencidos (visible siempre; deshabilitado si no hay vencidos) -->
    <form method="post" action="<?= BASE_URL ?>/products/retire-expired" class="mb-0 js-confirmable" id="formRetireExpiredTop" autocomplete="off"
          data-confirm-title="Retirar vencidos"
          data-confirm-text="¿Desea retirar todos los productos vencidos? Serán marcados como 'retirados' y su stock se pondrá en 0."
          data-confirm-ok="Retirar"
          data-confirm-cancel="Cancelar"
          data-loading-text="Retirando vencidos...">
      <input type="hidden" name="csrf" value="<?= View::e(App\Helpers\Security::csrfToken()) ?>">
      <?php $hasExpired = (int)($expired ?? 0) > 0; ?>
      <button type="submit" id="btnRetireExpiredTop" class="btn inv-btn btn-retire-expired <?= $hasExpired ? '' : 'disabled' ?>" <?= $hasExpired ? '' : 'disabled title="No hay vencidos"' ?>>
        <i class="fas fa-box-open inv-icon" aria-hidden="true"></i>
        <span class="inv-text">Retirar<br>vencidos</span>
      </button>
    </form>
    
    <a href="<?= BASE_URL ?>/products?stock=low" class="btn btn-warning inv-btn">
      <i class="fas fa-exclamation-triangle inv-icon" aria-hidden="true"></i>
      <span class="inv-text">Bajo<br>stock</span>
      <span class="badge badge-light inv-badge"><?= View::e($lowStock ?? 0) ?></span>
    </a>
  </div>
</div>

<?php if (!empty($lowStockList)): ?>
  <div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-thermometer-quarter mr-2 text-warning" aria-hidden="true"></i> Stock bajo (Top 10)</h3>
    <a href="<?= BASE_URL ?>/products?stock=low" class="btn btn-outline-warning btn-sm"><i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i> Ir a productos</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>ID</th><th>SKU</th><th>Producto</th><th class="text-right">Stock</th></tr></thead>
      <tbody>
        <?php
          $STOCK_DANGER = defined('STOCK_DANGER') ? (int)STOCK_DANGER : 20;
          $STOCK_WARN = defined('STOCK_WARN') ? (int)STOCK_WARN : 60;
        ?>
        <?php foreach ($lowStockList as $p): ?>
          <?php
            $s = (int)($p['stock'] ?? 0);
            if ($s <= $STOCK_DANGER) { $cls = 'danger'; $ico = '<i class="fas fa-exclamation-circle mr-1" aria-hidden="true"></i>'; }
            elseif ($s <= $STOCK_WARN) { $cls = 'warning'; $ico = ''; }
            else { $cls = 'success'; $ico = ''; }
          ?>
          <tr>
            <td><?= View::e($p['id']) ?></td>
            <td><?= View::e($p['sku']) ?></td>
            <td><?= View::e($p['name']) ?></td>
            <td class="text-right"><span class="badge badge-<?= $cls ?>"><?= $ico ?><span><?= View::e($s) ?></span></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer text-muted small">Umbral: ≤ <?= defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5 ?></div>
 </div>
<?php endif; ?>

<!-- Top productos en primera posición -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-trophy mr-2 text-warning" aria-hidden="true"></i> Top productos (30 días)</h3>
        <a href="<?= BASE_URL ?>/sales" class="btn btn-outline-secondary btn-sm">Ver ventas</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>#</th><th>SKU</th><th>Producto</th><th class="text-right">Cantidad</th></tr></thead>
          <tbody>
            <?php $i=1; foreach (($topProducts ?? []) as $tp): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= View::e($tp['sku'] ?? '') ?></td>
                <td><?= View::e($tp['name'] ?? '') ?></td>
                <td class="text-right"><span class="badge badge-primary"><?= (int)($tp['qty'] ?? 0) ?></span></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($topProducts)): ?>
              <tr><td colspan="4" class="text-center text-muted">Sin datos recientes</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

 

<!-- Heatmap semanal al final -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-fire mr-2 text-danger" aria-hidden="true"></i> Heatmap semanal (importe)</h3>
        <?php
          // Mostrar fecha/hora colombiana
          try {
            $tzCo = new DateTimeZone('America/Bogota');
            $nowCo = new DateTime('now', $tzCo);
            $toCo = clone $nowCo;
            $fromCo = (clone $nowCo)->modify('-6 days')->setTime(0,0,0);
            $rangeText = $fromCo->format('d/m/Y') . ' - ' . $toCo->format('d/m/Y H:i');
          } catch (\Throwable $e) { $rangeText = date('d/m/Y') . ' - ' . date('d/m/Y H:i'); }
        ?>
        <div class="text-muted small">
          <i class="far fa-clock mr-1" aria-hidden="true"></i>
          <span>Colombia: <?= View::e($rangeText) ?></span>
        </div>
      </div>
      <div class="card-body">
        <?php
          $labelsDow = [1=>'Dom',2=>'Lun',3=>'Mar',4=>'Mié',5=>'Jue',6=>'Vie',7=>'Sáb'];
          $maxHeat = 0.0;
          if (!empty($heatmap)) {
            foreach ($heatmap as $dRow) { foreach ($dRow as $v) { if ($v > $maxHeat) $maxHeat = $v; } }
          }
        ?>
        <div class="table-responsive">
          <table class="table table-sm mb-0 heatmap-table">
            <thead>
              <tr>
                <th class="heatmap-sticky">Día/Hora</th>
                <?php for ($h=0; $h<24; $h++): ?>
                  <th class="text-center heatmap-hour"><small><?= $h < 10 ? ('0'.$h) : $h ?></small></th>
                <?php endfor; ?>
              </tr>
            </thead>
            <tbody>
              <?php for ($d=1; $d<=7; $d++): ?>
                <tr>
                  <th class="heatmap-sticky"><?= View::e($labelsDow[$d]) ?></th>
                  <?php for ($h=0; $h<24; $h++): ?>
                    <?php $val = isset($heatmap[$d][$h]) ? (float)$heatmap[$d][$h] : 0.0; $ratio = ($maxHeat > 0 ? min(1.0, $val / $maxHeat) : 0.0); $alpha = 0.06 + $ratio * 0.72; ?>
                    <td class="text-center heat-cell" title="$<?= number_format($val,0,',','.') ?> (<?= ($h<10?'0':'').$h ?>:00)"
                        style="--heat-alpha: <?= number_format($alpha,2) ?>; color: <?= ($ratio > 0.55 ? '#fff' : '#1f2d3d') ?>;">
                      <small><?= $val > 0 ? '$'.number_format($val/1000,1,',','.').'k' : '—' ?></small>
                    </td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-2">
          <div class="text-muted small">Intensidad según total de ventas por hora (últimos 7 días).</div>
          <div class="heatmap-legend d-flex align-items-center">
            <span class="mr-2 small text-muted">Bajo</span>
            <div class="legend-bar"></div>
            <span class="ml-2 small text-muted">Alto</span>
          </div>
        </div>

<style>
  /* Scoped styles for dashboard tweaks */
  .inv-actions .btn { border-width: 0; font-weight: 600; border-radius: 12px; padding: 12px 16px; box-shadow: 0 2px 6px rgba(0,0,0,.06); text-decoration: none; }
  .inv-actions .btn:hover, .inv-actions .btn:focus { text-decoration: none; border-bottom: none; }
  .inv-actions .btn i { opacity: .95; }
  .inv-actions .btn + .btn { margin-left: .5rem; }
  /* New inventory action buttons styling */
  .inv-actions { flex-wrap: wrap; gap: .5rem; }
  .inv-actions .inv-btn { display: inline-flex; align-items: center; gap: .5rem; border-radius: 12px; padding: 10px 16px; height: 48px; line-height: 1.1; transition: all .15s ease-in-out; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
  .inv-actions .inv-btn .inv-icon { font-size: 1.1rem; opacity: .95; }
  .inv-actions .inv-btn .inv-text { display: inline-block; text-align: left; font-weight: 600; }
  .inv-actions .inv-btn .inv-badge { margin-left: .25rem; font-weight: 700; }
  .inv-actions .btn-primary { background: #3c8dbc; border-color: #3c8dbc; color: #fff; }
  .inv-actions .btn-primary:hover { background: #357ea8; border-color: #357ea8; box-shadow: 0 4px 10px rgba(60,141,188,.25); }
  .inv-actions .btn-danger  { background: #e74c3c; border-color: #e74c3c; color: #fff; }
  .inv-actions .btn-danger:hover  { background: #cf3f2f; border-color: #cf3f2f; box-shadow: 0 4px 10px rgba(231,76,60,.25); }
  .inv-actions .btn-warning { background: #f39c12; border-color: #f39c12; color: #1f2d3d; }
  .inv-actions .btn-warning:hover { background: #d98c10; border-color: #d98c10; box-shadow: 0 4px 10px rgba(243,156,18,.25); }
  .inv-actions .inv-btn:focus { outline: none; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
  /* Custom style for 'Retirar vencidos' */
  .btn-retire-expired { background: #ff5c5c; border-color: #ff5c5c; color: #111 !important; font-weight: 700; }
  .btn-retire-expired:hover { background: #ff3d3d; border-color: #ff3d3d; color: #000 !important; box-shadow: 0 4px 12px rgba(255,61,61,.35); }
  .btn-retire-expired.disabled, .btn-retire-expired:disabled { background: #f1b5b5; border-color: #f1b5b5; color: #666 !important; opacity: .85; }
  @media (max-width: 576px) {
    .inv-actions { width: 100%; }
    .inv-actions .inv-btn { flex: 1 1 auto; justify-content: center; height: auto; padding: 10px 12px; }
    .inv-actions .inv-btn .inv-text { text-align: center; }
  }
  .table tfoot th { border-top: 2px solid #dee2e6; }
  .table tfoot th small { color: #6c757d; font-weight: 600; }
  /* Floating cart button */
  .cart-fab { position: fixed; right: 20px; bottom: 20px; z-index: 1040; border: none; border-radius: 50%; width: 56px; height: 56px; background: #3c8dbc; color: #fff; box-shadow: 0 6px 16px rgba(0,0,0,.25); display: inline-flex; align-items: center; justify-content: center; }
  .cart-fab:hover { background: #357ea8; }
  .cart-fab .fa-shopping-cart { font-size: 1.25rem; }
  .cart-fab-badge { position: absolute; top: -6px; right: -6px; border-radius: 10px; font-weight: 700; }
  /* Lightweight modal */
  .ps-modal { position: fixed; inset: 0; z-index: 1050; display: none; }
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
  /* Heatmap styles */
  .heatmap-table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
  .heatmap-sticky { position: sticky; left: 0; background: #fff; z-index: 2; min-width: 64px; }
  .heatmap-hour { min-width: 28px; }
  .heat-cell { min-width: 28px; width: 28px; height: 28px; background: rgba(60,141,188, var(--heat-alpha, .08)); border-radius: 6px; transition: transform .08s ease, box-shadow .08s ease; }
  .heat-cell:hover { transform: scale(1.08); box-shadow: 0 0 0 2px rgba(60,141,188,.25) inset; }
  .heatmap-legend .legend-bar { width: 120px; height: 8px; border-radius: 999px; background: linear-gradient(90deg, rgba(60,141,188,.08) 0%, rgba(60,141,188,.78) 100%); }
</style>

 

<script>
  // Cargar Chart.js (usamos CDN si no está ya cargado)
  (function addChartJs(){
    if (window.Chart) return;
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
    s.async = true; document.head.appendChild(s);
  })();
  // Ensure notification triggers after layout scripts define notify()
  (function(){
    function revealInv(){
      try {
        var inv = document.getElementById('invSummary');
        if (inv) { setTimeout(function(){ inv.style.display = ''; }, 2000); }
      } catch(_){ }
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', revealInv);
    } else {
      // DOMContentLoaded already fired; run immediately
      revealInv();
    }
  })();
    try {
      var low = <?= (int)($lowStock ?? 0) ?>;
      var thr = <?= defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5 ?>;
      if (low > 0 && typeof window.notify === 'function') {
        var html = '<div class="d-flex align-items-start">'
          + '<div class="mr-2" aria-hidden="true"><i class="fas fa-exclamation-triangle text-warning"></i></div>'
          + '<div><strong>Tienes ' + low + ' producto(s) con stock bajo</strong><br><span class="text-muted">Umbral: ≤ ' + thr + '</span></div>'
          + '</div>';
        window.notify({ icon: 'warning', title: 'Alerta de Stock', html: html });
      }
    } catch (e) { /* noop */ }

    // Gráfica: ventas de hoy por hora
    (function buildSalesByHour(){
      try {
        var raw = <?php echo json_encode($todaySales ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        var buckets = new Array(24).fill(0);
        raw.forEach(function(r){
          var dt = r && r.created_at ? new Date(r.created_at.replace(' ', 'T')) : null;
          var h = (dt && !isNaN(dt.getTime())) ? dt.getHours() : null;
          if (h !== null) buckets[h] += parseFloat(r.total || 0);
        });
        var labels = buckets.map(function(_v,i){ return (i<10?'0':'') + i + ':00'; });
        var ctx = document.getElementById('chartSalesByHour');
        if (!ctx || !window.Chart) return;
        new Chart(ctx.getContext('2d'), {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Importe (COP)',
              data: buckets.map(function(v){ return Math.round(v||0); }),
              backgroundColor: '#3c8dbc'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
          }
        });
      } catch(_){}
    })();

    // Gráfica: estado del inventario
    (function buildInventory(){
      try {
        var total = <?= (int)($totalProducts ?? 0) ?>;
        var low = <?= (int)($lowStock ?? 0) ?>;
        var expSoon = <?= (int)($expiringSoon ?? 0) ?>;
        var expired = <?= (int)($expired ?? 0) ?>;
        var ok = Math.max(0, total - low); // aproximación visual
        var ctx = document.getElementById('chartInventory');
        if (!ctx || !window.Chart) return;
        new Chart(ctx.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: ['Bajo stock','Por vencer (30d)','Vencidos','OK'],
            datasets: [{
              data: [low, expSoon, expired, ok],
              backgroundColor: ['#f39c12','#ffc107','#e74c3c','#00a65a']
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
          }
        });
      } catch(_){}
    })();

    // ----- Cart modal (localStorage draft) -----
    (function cartModal(){
      var uid = <?= (int)(\App\Helpers\Auth::id() ?? 0) ?>;
      var KEY = 'pharmasoft_sales_draft_' + uid;
      var SHARED = 'pharmasoft_sales_draft';
      var LEGACY = 'pharmasoft_pending_cart';
      var fab = document.getElementById('btnCartFloating');
      var fabCount = document.getElementById('cartFabCount');
      var modal = document.getElementById('cartModal');
      var mBody = document.getElementById('cartModalBody');
      var mFooter = document.getElementById('cartModalFooter');
      var mCount = document.getElementById('cartModalCount');
      var mTotal = document.getElementById('cartModalTotal');
      var mClose = document.getElementById('cartModalClose');
      var mBackdrop = document.getElementById('cartModalBackdrop');
      var mClear = document.getElementById('cartModalClear');

      function fmt(n){
        try { return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0}).format(n||0); }
        catch(e){ var v=Math.round(n||0); return '$'+String(v).replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
      }
      function migrate(){
        try {
          var shared = localStorage.getItem(SHARED);
          if (shared && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, shared); }
          var old = localStorage.getItem(LEGACY);
          if (old && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, old); localStorage.removeItem(LEGACY); }
        } catch(_){ }
      }
      function read(){
        migrate();
        try { var raw = localStorage.getItem(KEY); var arr = raw ? JSON.parse(raw||'[]')||[] : []; return Array.isArray(arr)?arr:[]; } catch(_){ return []; }
      }
      function write(arr){ try { if (arr && arr.length) localStorage.setItem(KEY, JSON.stringify(arr)); else localStorage.removeItem(KEY); } catch(_){} }
      function total(arr){ var t=0; (arr||[]).forEach(function(it){ var q=parseInt(it.qty||0,10)||0; var p=Math.round(parseFloat(it.unit_price||0)||0); t+=q*p; }); return t; }
      function render(){
        var items = read();
        var cnt = items.length;
        if (fabCount){ fabCount.style.display = cnt>0?'inline-block':'none'; fabCount.textContent = String(cnt); }
        if (mCount){ mCount.textContent = String(cnt); }
        if (!cnt){
          if (mBody) mBody.innerHTML = '<div class="p-3 text-muted">No hay borrador de carrito.</div>';
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
        // bind remove buttons
        var btns = mBody ? mBody.querySelectorAll('.btnRemoveItem') : [];
        if (btns && btns.forEach){ btns.forEach(function(b){ b.addEventListener('click', function(){ var tr=b.closest('tr'); var idx = tr ? parseInt(tr.getAttribute('data-i')||'-1',10) : -1; var arr = read(); if (idx>=0 && idx < arr.length){ arr.splice(idx,1); write(arr); render(); } }); }); }
      }
      function open(){ if (!modal) return; render(); modal.style.display='block'; document.body.style.overflow='hidden'; }
      function close(){ if (!modal) return; modal.style.display='none'; document.body.style.overflow=''; }
      if (fab) fab.addEventListener('click', function(e){ e.preventDefault(); open(); });
      if (mClose) mClose.addEventListener('click', function(){ close(); });
      if (mBackdrop) mBackdrop.addEventListener('click', function(e){ if (e.target===mBackdrop) close(); });
      if (mClear) mClear.addEventListener('click', function(){
        // Pretty confirm: Swal -> psConfirm -> native
        try {
          if (window.Swal && Swal.fire) {
            return Swal.fire({
              title: 'Vaciar carrito',
              text: '¿Desea vaciar el borrador del carrito?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sí, vaciar',
              cancelButtonText: 'No'
            }).then(function(res){ if (res && res.isConfirmed) { write([]); render(); } });
          }
          if (window.psConfirm) {
            return window.psConfirm({ title:'Vaciar carrito', text:'¿Desea vaciar el borrador del carrito?', ok:'Sí', cancel:'No' })
              .then(function(ok){ if (ok) { write([]); render(); } });
          }
        } catch(_){ }
        if (confirm('¿Desea vaciar el borrador del carrito?')) { write([]); render(); }
      });
      // initial badge update
      render();
    })();

    // Local confirm + submit for "Retirar vencidos" (evita conflictos y estados colgados)
    function bindRetire(idForm, idBtn) {
      var f = document.getElementById(idForm);
      var b = document.getElementById(idBtn);
      if (!f || !b) return;
      // If the form uses the shared js-confirmable flow, do not bind custom logic to avoid double prompts
      try { if (f.classList && f.classList.contains('js-confirmable')) return; } catch(_){ }
      f.addEventListener('submit', function(e){ e.preventDefault(); });
      b.addEventListener('click', function(e){
        if (b.disabled) return;
        e.preventDefault();
        var run = function(){
          try { b.disabled = true; b.classList.add('disabled'); } catch(_){ }
          // Show processing/loading state while submitting
          try {
            if (typeof window.bannerLoading === 'function') {
              window.bannerLoading(true, 'Retirando vencidos...');
            } else {
              // Minimal fallback overlay
              var ov = document.getElementById('retireProcessingOverlay');
              if (!ov) {
                ov = document.createElement('div');
                ov.id = 'retireProcessingOverlay';
                ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:2000;';
                ov.innerHTML = '<div style="background:#fff;padding:12px 16px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.25);font-weight:700;display:inline-flex;align-items:center;gap:.6rem;"><span class="spinner-border spinner-border-sm text-danger" role="status" aria-hidden="true"></span><span>Retirando vencidos...</span></div>';
                document.body.appendChild(ov);
              }
              ov.style.display = 'flex';
            }
          } catch(_){ }
          try {
            if (!f.method || f.method.toLowerCase() !== 'post') f.method = 'post';
            HTMLFormElement.prototype.submit.call(f);
          } catch(_) {
            try { f.method = 'post'; } catch(_2){}
            f.submit();
          }
        };
        // Use psConfirm if available; otherwise native confirm
        try {
          var msg = '¿Desea retirar todos los productos vencidos? Serán marcados como "retirados" y su stock se pondrá en 0. Esta acción no afecta ventas ya registradas.';
          if (window.psConfirm) {
            window.psConfirm({ title:'Retirar vencidos', text: msg, ok:'Retirar', cancel:'Cancelar' })
              .then(function(ok){ if (ok) run(); });
          } else {
            if (window.confirm(msg)) run();
          }
        } catch(_){ run(); }
      });
    }
    bindRetire('formRetireExpiredTop','btnRetireExpiredTop');
    bindRetire('formRetireExpiredAlert','btnRetireExpiredAlert');

</script>

 


<!-- Confirmation handled globally by public/js/confirm-modal.js -->

<?php if (!empty($welcome) && $welcome && !empty($user)): ?>
  <?php
    $name = isset($user['name']) ? $user['name'] : '';
    $roleRaw = isset($user['role']) ? strtolower((string)$user['role']) : '';
    $roleLabel = 'Usuario';
    if ($roleRaw === 'admin' || $roleRaw === 'administrator') $roleLabel = 'Administrador';
    elseif ($roleRaw === 'tecnico' || $roleRaw === 'technician' || $roleRaw === 'tech') $roleLabel = 'Técnico';
    $welcomeTitleText = ((int)$welcome === 1) ? '¡Bienvenido' : '¡Bienvenido de vuelta';
    $isAdmin = ($roleLabel === 'Administrador');
    $uid = isset($user['id']) ? (int)$user['id'] : 0;
  ?>
  <div id="welcomeModal" class="ps-modal ps-show" style="display:block; z-index:3050;">
    <div class="ps-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="welcomeTitle">
      <div class="ps-modal-header" style="background: linear-gradient(135deg,#16a34a,#22c55e,#10b981); color:#fff;">
        <h5 id="welcomeTitle" class="mb-0 d-flex align-items-center" style="font-weight:900; letter-spacing:.2px;">
          <i class="fas fa-hand-peace mr-2" aria-hidden="true"></i>
          <?= View::e($welcomeTitleText) ?>, <?= View::e($name) ?>!
        </h5>
        <button type="button" class="close" id="welcomeCloseBtn" aria-label="Cerrar" style="color:#fff; opacity:.85;"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="ps-modal-body">
        <div class="p-3">
          <style>
            /* Scoped to welcome modal only */
            #welcomeModal .lead { font-weight: 700; color: #111827; }
            #welcomeModal .kpis { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 12px; margin: 10px 0 14px; }
            #welcomeModal .kpi { background:#f9fafb; border:1px solid #eee; border-radius:18px; padding:14px; display:flex; align-items:center; gap:12px; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
            #welcomeModal .kpi i { font-size:1rem; opacity:.9; }
            #welcomeModal .cta-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap:10px; }
            #welcomeModal .cta-grid .btn { display:flex; align-items:center; justify-content:center; gap:8px; font-weight:800; border-radius:999px; padding:12px 14px; box-shadow: 0 6px 16px rgba(0,0,0,.05); }
            #welcomeModal .btn-dash { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
            #welcomeModal .btn-products { background:#ecfeff; color:#0e7490; border:1px solid #a5f3fc; }
            #welcomeModal .btn-sales { background:#ecfccb; color:#3f6212; border:1px solid #bbf7d0; }
            #welcomeModal .btn-users { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
            #welcomeModal .btn-notify { background:#fff7ed; color:#b45309; border:1px solid #fde68a; }
            #welcomeModal .btn-cart { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
            #welcomeModal .tips { background:#f8fafc; border:1px dashed #cbd5e1; border-radius:16px; padding:12px; }
            #welcomeModal .chip { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:999px; font-weight:800; font-size:.8rem; border:1px solid #e5e7eb; margin-right:6px; box-shadow: 0 4px 12px rgba(0,0,0,.04); }
            #welcomeModal .chip.role { background:#eef2ff; color:#3730a3; border-color:#c7d2fe; }
            #welcomeModal .chip.first { background:#ecfeff; color:#065f46; border-color:#a5f3fc; }
            #welcomeModal .chip.back { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
            /* New colorful chips */
            #welcomeModal .chip.success { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
            #welcomeModal .chip.warning { background:#fff7ed; color:#b45309; border-color:#fde68a; }
            #welcomeModal .chip.danger  { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
            #welcomeModal .chip.info    { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
            /* Round dialog and header */
            #welcomeModal .ps-modal-dialog { border-radius:24px; overflow:hidden; box-shadow: 0 18px 48px rgba(0,0,0,.18), 0 8px 24px rgba(0,0,0,.12); }
            #welcomeModal .ps-modal-header { border-top-left-radius:24px; border-top-right-radius:24px; }
            /* Softer backdrop with slight blur */
            #welcomeModal .ps-modal-backdrop { background: rgba(15,23,42,.40); backdrop-filter: blur(3px); position: fixed; inset: 0; }
            /* Subtle hover scale for CTAs */
            #welcomeModal .cta-grid .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 20px rgba(0,0,0,.08); }
          </style>
          <div class="mb-2">
            <span class="chip role"><i class="fas fa-id-badge"></i> Rol: <?= View::e($roleLabel) ?></span>
            <?php if ((int)$welcome === 1): ?><span class="chip first"><i class="fas fa-sparkles"></i> Primera vez aquí</span><?php else: ?><span class="chip back"><i class="fas fa-undo"></i> De vuelta</span><?php endif; ?>
          </div>
          <?php if ((int)$welcome === 1): ?>
            <p class="lead mb-2">Gracias por iniciar sesión en <strong>PharmaSoft</strong>. Aquí tienes accesos directos y un resumen según tu rol.</p>
          <?php else: ?>
            <p class="lead mb-2">¡Nos alegra verte de nuevo! Usa estos atajos y recuerda tus funciones principales.</p>
          <?php endif; ?>
          <ul class="pl-3 mb-3">
            <?php if ($roleLabel === 'Administrador'): ?>
              <li>Gestiona productos, proveedores y usuarios del sistema.</li>
              <li>Visualiza ventas, utilidades y reportes.</li>
              <li>Configura parámetros y seguridad.</li>
            <?php elseif ($roleLabel === 'Técnico'): ?>
              <li>Registra ventas y consulta inventario.</li>
              <li>Controla vencimientos y bajo stock.</li>
              <li>Apoya la operación diaria.</li>
            <?php else: ?>
              <li>Consulta inventario y realiza operaciones permitidas por tu rol.</li>
              <li>Accede al módulo de ventas y reportes básicos.</li>
            <?php endif; ?>
          </ul>
          <div class="kpis">
            <div class="kpi"><i class="fas fa-bell text-warning"></i><div><div class="font-weight-bold">Notificaciones</div><div id="wmNotifyText" class="text-muted small">Sin notificaciones</div></div></div>
            <div class="kpi"><i class="fas fa-shopping-cart text-primary"></i><div><div class="font-weight-bold">Carrito</div><div id="wmCartText" class="text-muted small">Carrito vacío</div></div></div>
          </div>
          <div class="cta-grid mb-2">
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-dash" id="wmGoDash"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= BASE_URL ?>/products" class="btn btn-products" id="wmGoProducts"><i class="fas fa-pills"></i> Productos</a>
            <a href="<?= BASE_URL ?>/sales" class="btn btn-sales" id="wmGoSales"><i class="fas fa-cash-register"></i> Ventas</a>
            <?php if ($isAdmin): ?>
              <a href="<?= BASE_URL ?>/users" class="btn btn-users" id="wmGoUsers"><i class="fas fa-users"></i> Usuarios</a>
            <?php endif; ?>
            <a href="#" class="btn btn-notify" id="wmGoNotify"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="#" class="btn btn-cart" id="wmGoCart"><i class="fas fa-shopping-cart"></i> Carrito</a>
          </div>
          <div class="tips">
            <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
            Puedes cerrar este mensaje con la X, presionando ESC o haciendo clic fuera.
          </div>
        </div>
      </div>
      <div class="ps-modal-footer d-flex justify-content-end">
        <button type="button" class="btn btn-primary btn-sm" id="welcomeOkBtn"><i class="fas fa-check mr-1" aria-hidden="true"></i> Entendido</button>
      </div>
    </div>
    <div class="ps-modal-backdrop" id="welcomeBackdrop"></div>
  </div>
  <script>
    (function(){
      try {
        // Debug: log welcome flag to ensure view condition passed
        console.log('PharmaSoft welcome flag:', <?= (int)$welcome ?>);
        var m = document.getElementById('welcomeModal');
        var btnX = document.getElementById('welcomeCloseBtn');
        var btnOk = document.getElementById('welcomeOkBtn');
        var bd = document.getElementById('welcomeBackdrop');
        // Lock page scroll and interactions while modal is open
        var _prevOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        function restorePage(){ document.body.style.overflow = _prevOverflow || ''; }
        function preventScroll(e){ e.preventDefault(); e.stopPropagation(); return false; }
        if (bd) {
          ['wheel','touchmove','scroll'].forEach(function(ev){ bd.addEventListener(ev, preventScroll, { passive:false }); });
        }
        function close(){ if (!m) return; m.style.display='none'; restorePage(); }
        if (btnX) btnX.addEventListener('click', close);
        if (btnOk) btnOk.addEventListener('click', close);
        if (bd) bd.addEventListener('click', function(e){ if (e.target === bd) close(); });
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });

        // Populate dynamic texts: notifications (detailed) and cart
        try {
          var wmNotifyText = document.getElementById('wmNotifyText');
          if (wmNotifyText) {
            var expiredCnt = <?= (int)($expired ?? 0) ?>;
            var soonCnt    = <?= (int)($expiringSoon ?? 0) ?>;
            var lowCnt     = <?= (int)($lowStock ?? 0) ?>;
            var zeroCnt    = <?= (int)($zeroStock ?? 0) ?>;
            var STOCK_DANGER = <?= defined('STOCK_DANGER') ? (int)STOCK_DANGER : 20 ?>;
            var STOCK_WARN   = <?= defined('STOCK_WARN') ? (int)STOCK_WARN : 60 ?>;
            var expiredDetails = <?php echo json_encode($expiredDetails ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            var expiringDetails = <?php echo json_encode($expiringDetails ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            var lowStockList = <?php echo json_encode($lowStockList ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            var zeroStockList = <?php echo json_encode($zeroStockList ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

            function esc(t){ return (t||'').toString().replace(/[&<>]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c]; }); }

            var html = '';
            // Expired
            if (expiredCnt > 0) {
              html += '<div class="mb-1"><span class="badge badge-danger mr-1"><i class="fas fa-ban mr-1"></i>Vencidos: '+expiredCnt+'</span></div>';
              var lines = [];
              (expiredDetails||[]).slice(0,6).forEach(function(r){
                var name = esc(r && (r.name||''));
                var dn = parseInt(r && (r.days_over||0),10) || 0;
                var dstr = esc(r && (r.d||''));
                lines.push('<span class="chip danger" title="Vencido"><i class="fas fa-ban"></i> '+name+' · '+dn+'d'+(dstr?(' · '+dstr):'')+'</span>');
              });
              if (lines.length) html += '<div style="display:flex;flex-wrap:wrap;gap:6px;">'+lines.join('')+'</div>';
            }
            // Expiring soon
            if (soonCnt > 0) {
              html += '<div class="mt-2 mb-1"><span class="badge badge-warning mr-1"><i class="fas fa-clock mr-1"></i>Por vencer (≤30d): '+soonCnt+'</span></div>';
              var lines2 = [];
              (expiringDetails||[]).slice(0,6).forEach(function(r){
                var name = esc(r && (r.name||''));
                var dl = parseInt(r && (r.days_left||0),10) || 0;
                var dstr = esc(r && (r.d||''));
                lines2.push('<span class="chip warning" title="Por vencer"><i class="fas fa-clock"></i> '+name+' · '+dl+'d'+(dstr?(' · '+dstr):'')+'</span>');
              });
              if (lines2.length) html += '<div style="display:flex;flex-wrap:wrap;gap:6px;">'+lines2.join('')+'</div>';
            }
            // Low stock
            if (lowCnt > 0) {
              html += '<div class="mt-2 mb-1"><span class="badge badge-warning mr-1"><i class="fas fa-exclamation-triangle mr-1"></i>Stock</span></div>';
              var lines3 = [];
              (lowStockList||[]).slice(0,6).forEach(function(r){
                var name = esc(r && (r.name||''));
                var st = parseInt(r && (r.stock||0),10) || 0;
                var sev = (st <= STOCK_DANGER) ? '<span class="text-danger font-weight-bold">stock bajo</span>'
                          : (st <= STOCK_WARN) ? '<span class="text-warning font-weight-bold">stock en advertencia</span>'
                          : '<span class="text-success">stock OK</span>';
                var cls = (st <= STOCK_DANGER) ? 'danger' : (st <= STOCK_WARN) ? 'warning' : 'success';
                var ico = (st <= STOCK_DANGER) ? 'fa-exclamation-circle' : (st <= STOCK_WARN) ? 'fa-exclamation-triangle' : 'fa-check';
                lines3.push('<span class="chip '+cls+'" title="Stock: '+st+'"><i class="fas '+ico+'"></i> '+name+' · '+st+'</span>');
              });
              if (lines3.length) html += '<div style="display:flex;flex-wrap:wrap;gap:6px;">'+lines3.join('')+'</div>';
            }

            // Zero stock (Sin stock)
            if (zeroCnt > 0) {
              html += '<div class="mt-2 mb-1"><span class="badge badge-danger mr-1"><i class="fas fa-times-circle mr-1"></i>Sin stock: '+zeroCnt+'</span></div>';
              var lines0 = [];
              (zeroStockList||[]).slice(0,6).forEach(function(r){
                var name = esc(r && (r.name||''));
                lines0.push('<span class="chip danger" title="Sin stock"><i class="fas fa-times-circle"></i> '+name+' · 0</span>');
              });
              if (lines0.length) html += '<div style="display:flex;flex-wrap:wrap;gap:6px;">'+lines0.join('')+'</div>';
            }

            wmNotifyText.innerHTML = html || 'Sin notificaciones';
          }
        } catch(_){ }
        try {
          var wmCartText = document.getElementById('wmCartText');
          var cnt = 0;
          var uid = <?= (int)$uid ?>;
          var nsKey = 'pharmasoft_sales_draft_' + uid;
          var chips = [];
          function esc(t){ return (t||'').toString().replace(/[&<>]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c]; }); }
          try {
            var rawNS = localStorage.getItem(nsKey);
            if (!rawNS) {
              var legacy = localStorage.getItem('pharmasoft_sales_draft');
              if (legacy) { localStorage.setItem(nsKey, legacy); }
              rawNS = localStorage.getItem(nsKey);
            }
            var arr = rawNS ? JSON.parse(rawNS||'[]')||[] : [];
            if (Array.isArray(arr)) {
              cnt = arr.length;
              for (var i=0;i<Math.min(arr.length,6);i++) {
                var it = arr[i]||{};
                var nm = esc((it.name||'').toString());
                var q = parseInt(it.qty||0,10)||0;
                if (nm) chips.push('<span class="chip success" title="En carrito"><i class="fas fa-check"></i> '+nm+(q?(' · x'+q):'')+'</span>');
              }
            }
          } catch(e){ cnt = 0; chips = []; }
          if (wmCartText) {
            wmCartText.innerHTML = cnt>0
              ? ('<div style="display:flex;flex-wrap:wrap;gap:6px;">'
                 + '<span class="chip info"><i class="fas fa-shopping-cart"></i> '+cnt+' producto(s)</span>'
                 + chips.join('')
                 + (cnt>6 ? '<span class="chip info" title="Más">…</span>' : '')
                 + '</div>')
              : 'Carrito vacío';
          }
        } catch(_){ }

        // Quick actions: open existing modals if available
        var goNotify = document.getElementById('wmGoNotify');
        if (goNotify) goNotify.addEventListener('click', function(e){ e.preventDefault(); try { var fab=document.getElementById('psNotifyFab'); if (fab) fab.click(); } catch(_){ } close(); });
        var goCart = document.getElementById('wmGoCart');
        if (goCart) goCart.addEventListener('click', function(e){ e.preventDefault(); try { var fab=document.getElementById('globalCartFab'); if (fab) fab.click(); } catch(_){ } close(); });
      } catch(_){ }
    })();
  </script>
<?php endif; ?>
