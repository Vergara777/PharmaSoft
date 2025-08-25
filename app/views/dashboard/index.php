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
      <a href="<?= BASE_URL ?>/products?q=" class="small-box-footer">Inventario <i class="fas fa-arrow-circle-right"></i></a>
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
      <div class="inner"><h3><?= View::e($expiringSoon ?? 0) ?></h3><p>Por vencer (30d)</p></div>
      <div class="icon"><i class="fas fa-clock"></i></div>
      <a href="<?= BASE_URL ?>/products/expiring-30" class="small-box-footer">Ver <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3>$<?= number_format($todaySalesTotal, 2) ?></h3><p>Ventas de hoy (<?= View::e($todaySalesCount) ?>)</p></div>
      <div class="icon"><i class="fas fa-cash-register"></i></div>
      <a href="<?= BASE_URL ?>/sales" class="small-box-footer">Ir a ventas <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="alert alert-secondary d-flex align-items-center" role="alert">
  <i class="fas fa-bell mr-2 text-warning" aria-hidden="true"></i>
  <div class="mr-3">
    <strong>Resumen de inventario:</strong>
    <span class="badge badge-danger ml-2"><i class="fas fa-ban mr-1" aria-hidden="true"></i> Vencidos: <?= View::e($expired ?? 0) ?></span>
    <span class="badge badge-warning ml-2"><i class="fas fa-clock mr-1" aria-hidden="true"></i> Por vencer (30d): <?= View::e($expiringSoon ?? 0) ?></span>
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
    <a href="<?= BASE_URL ?>/products?q=" class="btn btn-warning inv-btn">
      <i class="fas fa-exclamation-triangle inv-icon" aria-hidden="true"></i>
      <span class="inv-text">Bajo<br>stock</span>
      <span class="badge badge-light inv-badge"><?= View::e($lowStock ?? 0) ?></span>
    </a>
  </div>
</div>

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
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-fire mr-2 text-danger" aria-hidden="true"></i> Heatmap semanal (importe)</h3>
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
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th style="min-width:48px">Día/Hora</th>
                <?php for ($h=0; $h<24; $h++): ?>
                  <th class="text-center"><small><?= $h < 10 ? ('0'.$h) : $h ?></small></th>
                <?php endfor; ?>
              </tr>
            </thead>
            <tbody>
              <?php for ($d=1; $d<=7; $d++): ?>
                <tr>
                  <th><?= View::e($labelsDow[$d]) ?></th>
                  <?php for ($h=0; $h<24; $h++): ?>
                    <?php $val = isset($heatmap[$d][$h]) ? (float)$heatmap[$d][$h] : 0.0; $ratio = ($maxHeat > 0 ? min(1.0, $val / $maxHeat) : 0.0); $alpha = 0.08 + $ratio * 0.6; ?>
                    <td class="text-center" title="$<?= number_format($val,2) ?>"
                        style="background: rgba(60,141,188, <?= number_format($alpha,2) ?>); color: <?= ($ratio > 0.5 ? '#fff' : '#000') ?>; min-width: 22px;">
                      <small><?= $val > 0 ? '$'.number_format($val/1000,1).'k' : '—' ?></small>
                    </td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="text-muted small mt-2">Intensidad según total de ventas por hora en los últimos 7 días.</div>
      </div>
    </div>
  </div>
</div>

<!-- Banner de advertencias de inventario -->
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
  @media (max-width: 576px) {
    .inv-actions { width: 100%; }
    .inv-actions .inv-btn { flex: 1 1 auto; justify-content: center; height: auto; padding: 10px 12px; }
    .inv-actions .inv-btn .inv-text { text-align: center; }
  }
  .table tfoot th { border-top: 2px solid #dee2e6; }
  .table tfoot th small { color: #6c757d; font-weight: 600; }
</style>
 

<?php if (!empty($expired) && (int)$expired > 0): ?>
<div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
  <div>
    <strong><?= View::e($expired) ?></strong> producto(s) vencido(s). Recomendado retirar del inventario.
  </div>
  <form method="post" action="<?= BASE_URL ?>/products/retire-expired" class="mb-0 js-confirmable" data-confirm-title="Retirar vencidos" data-confirm-text="¿Retirar todos los productos vencidos? Se pondrán como retirados y stock = 0." data-confirm-ok="Retirar">
    <input type="hidden" name="csrf" value="<?= View::e(App\Helpers\Security::csrfToken()) ?>">
    <button type="submit" class="btn btn-outline-light btn-sm"><i class="fas fa-box-open mr-1"></i>Retirar vencidos</button>
  </form>
 </div>
<?php endif; ?>

<?php if (!empty($lowStockList)): ?>
  <div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-thermometer-quarter mr-2 text-warning" aria-hidden="true"></i> Stock bajo (Top 10)</h3>
    <a href="<?= BASE_URL ?>/products?q=" class="btn btn-outline-warning btn-sm"><i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i> Ir a productos</a>
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

<script>
  // Cargar Chart.js (usamos CDN si no está ya cargado)
  (function addChartJs(){
    if (window.Chart) return;
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
    s.async = true; document.head.appendChild(s);
  })();
  // Ensure notification triggers after layout scripts define notify()
  document.addEventListener('DOMContentLoaded', function() {
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
              label: 'Importe (MXN)',
              data: buckets.map(function(v){ return Number(v.toFixed(2)); }),
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
  });
</script>

 

 

<!-- Confirmation handled globally by public/js/confirm-modal.js -->
