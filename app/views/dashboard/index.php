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
      <div class="inner"><h3>$<?= number_format((float)$todaySalesTotal, 0, ',', '.') ?></h3><p>Ventas de hoy (<?= View::e($todaySalesCount) ?>)</p></div>
      <div class="icon"><i class="fas fa-cash-register"></i></div>
      <a href="<?= BASE_URL ?>/sales" class="small-box-footer">Ir a ventas <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner"><h3>$<?= number_format((float)($monthSalesTotal ?? 0), 0, ',', '.') ?></h3><p>Ganancias del mes</p></div>
      <div class="icon"><i class="fas fa-calendar-alt"></i></div>
      <a href="<?= BASE_URL ?>/sales?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-t') ?>" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-secondary">
      <div class="inner"><h3>$<?= number_format((float)($yearSalesTotal ?? 0), 0, ',', '.') ?></h3><p>Ganancias del año</p></div>
      <div class="icon"><i class="fas fa-calendar"></i></div>
      <a href="<?= BASE_URL ?>/sales?from=<?= date('Y-01-01') ?>&to=<?= date('Y-12-31') ?>" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

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
    <!-- Tercero: Retirar vencidos (visible siempre; deshabilitado si no hay vencidos) -->
    <form method="post" action="<?= BASE_URL ?>/products/retire-expired" class="mb-0" id="formRetireExpiredTop" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= View::e(App\Helpers\Security::csrfToken()) ?>">
      <?php $hasExpired = (int)($expired ?? 0) > 0; ?>
      <button type="submit" id="btnRetireExpiredTop" class="btn inv-btn <?= $hasExpired ? 'btn-outline-danger' : 'btn-outline-secondary' ?>" <?= $hasExpired ? '' : 'disabled title="No hay vencidos"' ?>>
        <i class="fas fa-box-open inv-icon" aria-hidden="true"></i>
        <span class="inv-text">Retirar<br>vencidos</span>
      </button>
    </form>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var btnRetireExpiredTop = document.getElementById('btnRetireExpiredTop');
        if (btnRetireExpiredTop) {
          btnRetireExpiredTop.addEventListener('click', function(e) {
            e.preventDefault();
            var confirmText = '¿Retirar todos los productos vencidos? Se pondrán como retirados y stock = 0.';
            if (typeof psConfirm === 'function') {
              psConfirm({
                title: 'Retirar vencidos',
                text: confirmText,
                confirm: function() {
                  document.getElementById('formRetireExpiredTop').submit();
                }
              });
            } else {
              if (confirm(confirmText)) {
                document.getElementById('formRetireExpiredTop').submit();
              }
            }
          });
        }
      });
    </script>
    <a href="<?= BASE_URL ?>/products?q=" class="btn btn-warning inv-btn">
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
                    <td class="text-center" title="$<?= number_format($val,0,',','.') ?>"
                        style="background: rgba(60,141,188, <?= number_format($alpha,2) ?>); color: <?= ($ratio > 0.5 ? '#fff' : '#000') ?>; min-width: 22px;">
                      <small><?= $val > 0 ? '$'.number_format($val/1000,1,',','.').'k' : '—' ?></small>
                    </td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="text-muted small mt-2">Intensidad según total de ventas por hora en los últimos 7 días.</div>

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
</style>

<?php if (!empty($expired) && (int)$expired > 0): ?>
<div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
  <div>
    <strong><?= View::e($expired) ?></strong> producto(s) vencido(s). Recomendado retirar del inventario.
  </div>
  <form method="post" action="<?= BASE_URL ?>/products/retire-expired" class="mb-0" id="formRetireExpiredAlert" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= View::e(App\Helpers\Security::csrfToken()) ?>">
    <button type="submit" id="btnRetireExpiredAlert" class="btn btn-outline-light btn-sm"><i class="fas fa-box-open mr-1"></i>Retirar vencidos</button>
  </form>
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
      var KEY = 'pharmasoft_sales_draft';
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
      function read(){
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
      f.addEventListener('submit', function(e){ e.preventDefault(); });
      b.addEventListener('click', function(e){
        if (b.disabled) return;
        e.preventDefault();
        var run = function(){
          try { b.disabled = true; b.classList.add('disabled'); } catch(_){ }
          try { if (typeof window.bannerLoading === 'function') window.bannerLoading(false); } catch(_){ }
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
          if (window.psConfirm) {
            window.psConfirm({ title:'Retirar vencidos', text:'¿Retirar todos los productos vencidos? Se pondrán como retirados y stock = 0.', ok:'Retirar', cancel:'Cancelar' })
              .then(function(ok){ if (ok) run(); });
          } else {
            if (window.confirm('¿Retirar todos los productos vencidos? Se pondrán como retirados y stock = 0.')) run();
          }
        } catch(_){ run(); }
      });
    }
    bindRetire('formRetireExpiredTop','btnRetireExpiredTop');
    bindRetire('formRetireExpiredAlert','btnRetireExpiredAlert');
  });
</script>

 

 

<!-- Confirmation handled globally by public/js/confirm-modal.js -->
