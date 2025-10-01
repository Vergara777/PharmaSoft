<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card products-card">
  <style>
    /* Scoped to products list only */
    .products-card .badge { font-weight: 600; letter-spacing: .2px; }
    .products-card td .badge { font-size: .9rem; padding: .45em .6em; }
    /* Vivid colors + subtle glow */
    .products-card .badge-danger { background: #ff1744; color: #fff; box-shadow: 0 0 6px rgba(255, 23, 68, .55); }
    .products-card .badge-warning { background: #ffb300; color: #111; box-shadow: 0 0 6px rgba(255, 179, 0, .55); }
    .products-card .badge-success { background: #00c853; color: #fff; box-shadow: 0 0 6px rgba(0, 200, 83, .45); }
    .products-card .badge-secondary { background: #5a6268; color: #fff; box-shadow: 0 0 6px rgba(90, 98, 104, .45); }
    .products-card .badge-primary { background: #3b82f6; color: #fff; box-shadow: 0 0 6px rgba(59, 130, 246, .45); }
    /* Orange add-to-cart button */
    .btn-add-cart-orange { background: #f59e0b; border: 1px solid #d97706; color: #111; font-weight: 700; }
    .btn-add-cart-orange:hover { background: #ea8a07; border-color: #c26a05; color: #111; }
    .btn-add-cart-orange:active { background: #d97e06; border-color: #a65a04; color: #111; }
    .btn-add-cart-orange:disabled { background: #f3f4f6; border-color: #e5e7eb; color: #9ca3af; }
    /* Purple edit button (bold white text) */
    .btn-edit-purple { background:#7c3aed; border:1px solid #6d28d9; color:#fff; font-weight:700; letter-spacing:.2px; }
    .btn-edit-purple:hover { background:#6d28d9; border-color:#5b21b6; color:#fff; }
    .btn-edit-purple:active { background:#5b21b6; border-color:#4c1d95; color:#fff; }
    .btn-edit-purple:focus { outline:0; box-shadow:0 0 0 .2rem rgba(124,58,237,.35); }
    /* Empty state */
    .ps-empty-state { display: grid; place-items: center; padding: 48px 16px; color: #6b7280; }
    .ps-empty-state .box { text-align: center; max-width: 680px; }
    .ps-empty-state .title { font-weight: 900; font-size: 1.25rem; color: #111827; }
    .ps-empty-state .desc { margin-top: 6px; font-weight: 700; }
    /* Full-width table, tighter rows, and vertical centering */
    .products-card table { width: 100%; margin: 0; table-layout: auto; }
    .products-card table thead th,
    .products-card table tbody td{ vertical-align: middle; text-align: center; padding-top: .55rem; padding-bottom: .55rem; }
    /* Column width balance (desktop) */
    .products-card table thead th:nth-child(1),
    .products-card table tbody td:nth-child(1){ width: 4%; min-width: 36px; }
    .products-card table thead th:nth-child(2),
    .products-card table tbody td:nth-child(2){ width: 10%; }
    .products-card table thead th:nth-child(3),
    .products-card table tbody td:nth-child(3){ min-width: 220px; width: 28%; }
    .products-card table thead th:nth-child(4),
    .products-card table tbody td:nth-child(4){ width: 14%; }
    .products-card table thead th:nth-child(5),
    .products-card table tbody td:nth-child(5){ width: 10%; }
    .products-card table thead th:nth-child(6),
    .products-card table tbody td:nth-child(6){ width: 8%; }
    .products-card table thead th:nth-child(7),
    .products-card table tbody td:nth-child(7){ width: 14%; }
    .products-card table thead th:nth-child(8),
    .products-card table tbody td:nth-child(8){ width: 8%; }
    /* Name cell content centered inside the cell (original behavior) */
    .products-card table tbody tr{ height: 112px; }
    .products-card table thead th:nth-child(3){ text-align: center; }
    .products-card table tbody td:nth-child(3){ display: flex; align-items: center; justify-content: center; gap: 10px; padding-top: 42px; padding-bottom: 0; }
    .products-card table tbody td:nth-child(3) img{ margin: 0; width: 56px; height: 56px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd; }
    .products-card table tbody td:nth-child(3) .name-text{
      font-weight:600; word-break: break-word; text-align: center; line-height: 1.0;
      display: flex; align-items: center; height: 56px; /* match avatar height */
    }
    /* Uniform action buttons (Actions column) */
    .products-card table thead th:last-child,
    .products-card table tbody td:last-child { min-width: 200px; width: 200px; }
    .products-card table tbody td:last-child .btn {
      width: 100%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
      padding: 0.3rem 0.55rem;
      min-height: 32px; /* tighter */
      font-size: .84rem;
      margin-bottom: 4px; /* less vertical space */
    }
    .products-card table tbody td:last-child .btn i { margin-right: .35rem; }
  </style>
  <div class="card-header">
    <?php $isAdmin = \App\Helpers\Auth::isAdmin(); $isRetired = !empty($retired); ?>
    <div class="products-toolbar d-flex flex-wrap align-items-center">
      <form class="form-inline mb-2 mb-sm-0 mr-3" method="get" action="<?= BASE_URL ?><?= $isRetired ? '/products/retired' : '/products' ?>" role="search" aria-label="Buscar productos">
        <div class="input-group input-group-sm search-group mr-2">
          <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search" aria-hidden="true"></i></span></div>
          <input type="text" class="form-control" name="q" value="<?= View::e($q ?? '') ?>" placeholder="Nombre o SKU">
          <div class="input-group-append">
            <button class="btn btn-primary">Buscar</button>
          </div>
        </div>
        <?php if (!empty($categories ?? [])): ?>
        <?php $selCat = isset($categoryId) ? (int)$categoryId : null; ?>
        <div class="input-group input-group-sm category-group mr-2">
          <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-tags" aria-hidden="true"></i></span></div>
          <select id="categoryFilter" name="category_id" class="form-control" title="Filtrar por categoría">
            <?php $__isNoCat = ($selCat === null); ?>
            <!-- Placeholder (not selectable), shown when no category selected -->
            <option value="" disabled data-placeholder="true" hidden <?= $__isNoCat ? 'selected' : '' ?>>Seleccionar categoría</option>
            <!-- Real 'Todas' option available for selection -->
            <option value="">Todas</option>
            <?php foreach (($categories ?? []) as $c): $cid = (int)$c['id']; ?>
              <option value="<?= $cid ?>" <?= ($selCat === $cid ? 'selected' : '') ?>><?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </form>
      <div class="btn-toolbar mb-0 flex-grow-1 align-items-center" role="toolbar" aria-label="Acciones y filtros">
        <div class="stock-legend d-none d-md-flex align-items-center mb-2 mr-3" aria-label="Leyenda de stock">
          <span class="small text-muted mr-2">Stock</span>
          <span class="badge badge-danger mr-1" title="Crítico">0–<?= (int)(defined('STOCK_DANGER') ? STOCK_DANGER : 20) ?></span>
          <span class="badge badge-warning mr-1" title="Bajo"><?= (int)((defined('STOCK_DANGER') ? STOCK_DANGER : 20) + 1) ?>–<?= (int)(defined('STOCK_WARN') ? STOCK_WARN : 60) ?></span>
          <span class="badge badge-success" title="Óptimo">≥ <?= (int)((defined('STOCK_WARN') ? STOCK_WARN : 60) + 1) ?></span>
        </div>
      </div>
      <?php if ($isAdmin && $isRetired): ?>
        <div class="ml-auto mb-2" role="group" aria-label="Volver a activos">
          <a class="btn btn-primary" href="<?= BASE_URL ?>/products"><i class="fas fa-check mr-2" aria-hidden="true"></i> Volver a activos</a>
        </div>
      <?php endif; ?>
      <?php if ($isAdmin && !$isRetired): ?>
        <div class="btn-group ml-auto mb-2" role="group" aria-label="Crear nuevo">
          <a class="btn btn-success btn-new-product" href="<?= BASE_URL ?>/products/create"><i class="fas fa-plus mr-2" aria-hidden="true"></i> Agregar nuevo producto</a>
          <div class="dropdown ml-2">
            <button class="btn btn-outline-secondary dropdown-toggle px-3" type="button" id="ddProductsActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Más opciones">
              <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right p-2" aria-labelledby="ddProductsActions" style="min-width: 260px;">
              <div class="px-2 py-1 text-muted small">Navegación</div>
              <div class="d-flex px-2 pb-2">
                <?php if (!$isRetired): ?>
                  <a class="dropdown-item px-0" href="<?= BASE_URL ?>/products"><i class="fas fa-list mr-2"></i> Productos activos</a>
                  <a class="dropdown-item px-0" href="<?= BASE_URL ?>/products/retired"><i class="fas fa-archive mr-2"></i> Productos retirados</a>
                <?php else: ?>
                  <a class="dropdown-item px-0" href="<?= BASE_URL ?>/products"><i class="fas fa-check mr-2"></i> Volver a activos</a>
                <?php endif; ?>
              </div>
              <div class="dropdown-divider"></div>
              <div class="px-2 py-1 text-muted small">Filtrar por vencimiento</div>
              <form class="px-2 pb-1" method="get" action="<?= BASE_URL ?><?= $isRetired ? '/products/retired' : '/products' ?>" aria-label="Filtro por vencimiento">
                <div class="form-row align-items-center" style="gap:.5rem;">
                  <div class="col">
                    <select name="expiry" class="form-control form-control-sm">
                      <?php $expSel = isset($expiry) && $expiry !== '' ? (string)$expiry : (string)($_GET['expiry'] ?? ($_GET['venc'] ?? 'all')); ?>
                      <option value="all" <?= ($expSel==='all'||$expSel==='')?'selected':'' ?>>Todos</option>
                      <option value="30" <?= ($expSel==='30')?'selected':'' ?>>≤ 30 días</option>
                      <option value="60" <?= ($expSel==='60')?'selected':'' ?>>≤ 60 días</option>
                    </select>
                  </div>
                  <div>
                    <?php $qv = $q ?? ''; $cidv = isset($categoryId)? (int)$categoryId : ''; ?>
                    <input type="hidden" name="q" value="<?= View::e($qv) ?>">
                    <input type="hidden" name="category_id" value="<?= View::e($cidv) ?>">
                    <?php if (!empty($stock ?? '')): ?>
                      <input type="hidden" name="stock" value="<?= View::e($stock) ?>">
                    <?php endif; ?>
                    <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter mr-1"></i> Aplicar</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <style>
      .products-toolbar{ gap:.5rem 1rem; --tb-h: 38px; }
      .products-toolbar .input-group-text{ background:#f8f9fa; }
      .products-toolbar .btn-toolbar .btn{ white-space: nowrap; }
      .products-toolbar .stock-legend{ white-space: nowrap; }
      .products-toolbar .stock-legend .badge{ font-size:.8rem; padding:.35em .55em; }
      .products-toolbar .search-group{ min-width:360px; }
      .products-toolbar .category-group{ min-width:320px; display:flex; align-items:center; flex-wrap:nowrap; width:100%; white-space:nowrap; }
      /* Make Choices fill the input group width */
      .products-toolbar .category-group .input-group-prepend{ display:flex; align-items:center; flex: 0 0 auto; }
      .products-toolbar .category-group .input-group-prepend .input-group-text{
        height: var(--tb-h); padding-top:.25rem; padding-bottom:.25rem; border-right:0;
      }
      .products-toolbar .category-group .choices{ width:100%; min-width: 0; flex: 1 1 auto; margin-left:-1px; display:flex; align-self: stretch; }
      .products-toolbar .category-group .choices__inner{
        min-height: var(--tb-h); height: var(--tb-h); display:flex; align-items:center;
        padding-top:.25rem; padding-bottom:.25rem; border-top-left-radius:0; border-bottom-left-radius:0;
      }
      /* Altura uniforme para inputs, selects y botones */
      .products-toolbar .input-group.input-group-sm > .form-control,
      .products-toolbar .input-group.input-group-sm > .custom-select,
      .products-toolbar .input-group.input-group-sm > .input-group-prepend > .input-group-text,
      .products-toolbar .input-group.input-group-sm > .input-group-append > .btn,
      .products-toolbar .btn-group .btn,
      .products-toolbar select.form-control { height: var(--tb-h); line-height: calc(var(--tb-h) - 2px); }
      .products-toolbar .input-group.input-group-sm > .form-control,
      .products-toolbar .input-group.input-group-sm > .custom-select { padding-top:.25rem; padding-bottom:.25rem; }
      .products-toolbar .btn-group .btn { display:inline-flex; align-items:center; }
      .products-toolbar .input-group .form-control{ border-left:0; }
      .products-toolbar .input-group .input-group-text{ border-right:0; }
      .products-toolbar .input-group:focus-within{ box-shadow:0 0 0 .15rem rgba(60,141,188,.25); border-radius:.2rem; }
      /* New product button (clean green, RGB) */
      .products-toolbar .btn-new-product{
        font-weight:600; font-size:.98rem; padding:.35rem 1rem; border-radius:.5rem;
        height: var(--tb-h); display:inline-flex; align-items:center; gap:.5rem; white-space:nowrap;
        background: rgb(16,185,129); /* emerald */
        border: 1px solid rgb(12,155,108); color:#fff;
        box-shadow: 0 6px 12px rgba(16,185,129,.22);
        letter-spacing:.1px;
      }
      .products-toolbar .btn-new-product .fa-plus{ font-size:1rem; line-height:1; }
      .products-toolbar .btn-new-product:hover{
        background: rgb(5,150,105);
        box-shadow: 0 8px 16px rgba(5,150,105,.28);
      }
      .products-toolbar .btn-new-product:focus{
        outline: 0;
        box-shadow: 0 0 0 .2rem rgba(16,185,129,.25), 0 6px 12px rgba(16,185,129,.22);
      }
      .products-toolbar .btn-new-product:active{ box-shadow: 0 4px 10px rgba(5,150,105,.22); }
      /* Single row from md and up */
      @media (min-width: 768px){
        .products-toolbar{ flex-wrap: nowrap !important; }
        .products-toolbar .search-group{ flex: 1 1 520px; width: auto; }
        .products-toolbar .category-group{ flex: 0 0 320px; }
        .products-toolbar [aria-label="Filtro por vencimiento"]{ flex: 0 0 260px; }
        .products-toolbar .stock-legend{ flex: 0 0 auto; }
        .products-toolbar .btn-group.ml-auto{ margin-left: auto !important; }
        .products-toolbar .btn-new-product{ padding:.4rem 1.15rem; font-size:1.02rem; }
        .products-toolbar .dropdown .dropdown-menu{ box-shadow:0 10px 24px rgba(0,0,0,.12); border:1px solid #eaeaea; }
      }
      /* Small screens: stacked */
      @media (max-width: 576px){
        .products-toolbar .search-group{ width:100%; }
        .products-toolbar .category-group{ width:100%; margin-top:.5rem; }
        .products-toolbar .btn-new-product{ width:100%; justify-content:center; }
      }
    </style>
    <script>
      (function(){
        var sel = document.getElementById('categoryFilter');
        if (sel) {
          // Submit form on change (works with native select and Choices)
          sel.addEventListener('change', function(){ try { this.form.submit(); } catch(_){} });
          // Enhance with Choices for scrollable dropdown
          if (window.Choices) {
            try {
              new Choices(sel, {
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Filtrar categoría',
                searchPlaceholderValue: 'Escribe para filtrar…',
                itemSelectText: '',
                allowHTML: true,
                shouldSort: false
              });
            } catch(_){ }
          }
        }
      })();
    </script>
  </div>
  <div class="table-responsive">
    <?php
      $catMap = [];
      if (!empty($categories ?? [])) { foreach (($categories ?? []) as $c) { $catMap[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? ''); } }
      $supMap = [];
      if (!empty($suppliers ?? [])) { foreach (($suppliers ?? []) as $s) { $supMap[(int)($s['id'] ?? 0)] = (string)($s['name'] ?? ''); } }
      $noProducts = empty($products);
      if ($noProducts):
        $hasQuery = !empty($q);
        $ctxTitle = strtolower((string)($title ?? ''));
        $expDays = isset($expiry) && ctype_digit((string)$expiry) ? (int)$expiry : null;
        $stockCtx = isset($stock) ? (string)$stock : '';
        // Defaults
        $msgTitle = $hasQuery ? 'Este producto no está en el inventario' : 'No existe ningún producto';
        $msgDesc = $hasQuery ? 'Verifica el nombre o SKU e inténtalo nuevamente.' : 'Ajusta los filtros o agrega un nuevo producto.';
        // Expired context
        if (strpos($ctxTitle, 'vencid') !== false) {
          $msgTitle = 'No hay productos vencidos';
          $msgDesc  = 'Excelente, no tienes productos vencidos en el inventario.';
        }
        // Expiring soon context (via title or expiry filter)
        elseif (strpos($ctxTitle, 'vencer') !== false || $expDays !== null) {
          $daysTxt = $expDays !== null ? ('en ≤ ' . $expDays . ' días') : 'próximamente';
          $msgTitle = 'No hay productos por vencer';
          $msgDesc  = 'No encontramos productos que venzan ' . $daysTxt . '. Continúa monitoreando tu inventario.';
        }
        // Low stock context
        elseif ($stockCtx === 'low') {
          $msgTitle = 'No hay productos con bajo stock';
          $msgDesc  = 'Genial, ninguno está por debajo del umbral de stock bajo.';
        }
    ?>
      <div class="ps-empty-state" role="status" aria-live="polite">
        <div class="box">
          <div class="title">
            <i class="fas fa-box-open mr-2" aria-hidden="true"></i> <?= View::e($msgTitle) ?>
          </div>
          <div class="desc">
            <?= View::e($msgDesc) ?>
          </div>
        </div>
      </div>
    <?php else: ?>
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Ubicación</th><th>Caducidad</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php
          // Use date-only comparison to avoid off-by-one from time-of-day
          $today = new \DateTimeImmutable('today');
          $soonCount = 0; // 1-31 días
          $midCount = 0;  // 32-60 días
          $okCount  = 0;  // 61+
          $expiredCount = 0; // dd <= 0
          $lowStockCount = 0;
          $thr = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5;
          $STOCK_DANGER = defined('STOCK_DANGER') ? (int)STOCK_DANGER : 20;
          $STOCK_WARN = defined('STOCK_WARN') ? (int)STOCK_WARN : 60;
        ?>
        <?php foreach (($products ?? []) as $p): ?>
          <?php
            $stock = (int)($p['stock'] ?? 0);
            if ($stock > 0 && $stock <= $thr) { $lowStockCount++; }
            $days = null; // dd = days until expiry (date-based), 0=today, <0=expired
            if (!empty($p['expires_at'])) {
              try {
                $expDate = new \DateTimeImmutable((string)$p['expires_at']);
                $diff = (int)$today->diff($expDate)->format('%r%a');
                // Note: positive means in future, 0 today, negative past
                $days = $diff;
                if ($days <= 0) { $expiredCount++; }
                elseif ($days <= 31) { $soonCount++; }
                elseif ($days <= 60) { $midCount++; }
                else { $okCount++; }
              } catch (\Throwable $_) { $days = null; }
            }
            // Color del badge SOLO por stock, usando umbrales configurables
            if ($stock <= $STOCK_DANGER) {
              $badgeClass = 'danger';
            } elseif ($stock <= $STOCK_WARN) {
              $badgeClass = 'warning';
            } else {
              $badgeClass = 'success';
            }
            // Ícono para bajo stock
            $badgeIconHtml = ($stock <= $STOCK_DANGER) ? '<i class="fas fa-exclamation-circle mr-1" aria-hidden="true"></i>' : '';
            // Resaltar fila por caducidad: expirado en rojo, ≤31 días en amarillo
            $rowClass = '';
            if ($days !== null) {
              if ($days <= 0) { $rowClass = 'table-danger'; }
              elseif ($days <= 31) { $rowClass = 'table-warning'; }
            }
          ?>
          <tr class="<?= $rowClass ?>" data-days="<?= $days === null ? '' : $days ?>">
            <td><?= View::e($p['display_no'] ?? $p['id']) ?></td>
            <td><?= View::e($p['sku']) ?></td>
            <td>
              <?php if (!empty($p['image'])): ?>
                <img src="<?= BASE_URL ?>/uploads/<?= View::e($p['image']) ?>"
                     alt="Imagen"
                     style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:1px solid #ddd;margin-right:10px;vertical-align:middle;">
              <?php endif; ?>
              <span class="name-text"><?= View::e($p['name']) ?></span>
            </td>
            <td><?= isset($p['category_id']) && isset($catMap[(int)$p['category_id']]) && $catMap[(int)$p['category_id']] !== '' ? View::e($catMap[(int)$p['category_id']]) : '—' ?></td>
            
            <td>$<?= number_format((float)($p['price'] ?? 0), 0, ',', '.') ?></td>
            <td>
              <span class="badge badge-<?= $badgeClass ?>" title="<?= !empty($p['expires_at']) ? ('Vence: ' . View::e($p['expires_at'])) : '' ?>"><?= $badgeIconHtml ?><span><?= View::e($stock) ?></span></span>
            </td>
            <td>
              <?php if (!empty($p['shelf']) || !empty($p['row']) || !empty($p['position'])): ?>
                <span class="badge bg-primary" 
                      data-toggle="tooltip" 
                      data-placement="top" 
                      title="Estante: <?= htmlspecialchars($p['shelf'] ?? 'N/A') ?> | Fila: <?= htmlspecialchars($p['row'] ?? 'N/A') ?> | Posición: <?= htmlspecialchars($p['position'] ?? 'N/A') ?>">
                  <i class="fas fa-warehouse"></i> 
                  <?= htmlspecialchars($p['shelf'] ?? '') ?><?= !empty($p['row']) ? '-' . $p['row'] : '' ?><?= !empty($p['position']) ? '-' . $p['position'] : '' ?>
                </span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($days === null): ?>
                —
              <?php else: ?>
                <?php if ($days <= 0): ?>
                  <?php if ($days === 0): ?>
                    <span class="badge badge-danger" title="Producto vence hoy"><?= View::e($p['expires_at']) ?> (Venció hoy)</span>
                  <?php else: ?>
                    <?php $absd = abs($days); $dlabel = ($absd === 1 ? 'día' : 'días'); ?>
                    <span class="badge badge-danger" title="Producto vencido"><?= View::e($p['expires_at']) ?> (Venció hace <?= $absd ?> <?= $dlabel ?>)</span>
                  <?php endif; ?>
                <?php elseif ($days <= 31): ?>
                  <?php $dlabel = ($days === 1 ? 'día' : 'días'); ?>
                  <span class="badge badge-warning" title="Por vencer"><?= View::e($p['expires_at']) ?> (Faltan <?= $days ?> <?= $dlabel ?>)</span>
                <?php else: ?>
                  <span class="badge badge-primary" title="Fecha válida">
                    <?= View::e($p['expires_at']) ?> (<?= $days ?> d)
                  </span>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td>
              <?php $st = strtolower($p['status'] ?? ''); $isActiveSt = ($st === 'active' || $st === 'activo'); ?>
              <span class="badge badge-<?= $isActiveSt ? 'success' : 'secondary' ?>"><?= $isActiveSt ? 'Activo' : 'Inactivo' ?></span>
            </td>
            <td>
              <?php
                $catName = (isset($p['category_id']) && isset($catMap[(int)$p['category_id']]) && $catMap[(int)$p['category_id']] !== '') ? $catMap[(int)$p['category_id']] : '';
                $supName = (isset($p['supplier_id']) && isset($supMap[(int)$p['supplier_id']]) && $supMap[(int)$p['supplier_id']] !== '') ? $supMap[(int)$p['supplier_id']] : '';
              ?>
              <button type="button"
                      class="btn btn-sm btn-info btnViewProduct"
                      data-id="<?= (int)$p['id'] ?>"
                      data-sku="<?= View::e($p['sku'] ?? '') ?>"
                      data-name="<?= View::e($p['name'] ?? '') ?>"
                      data-expdays="<?= ($days === null ? '' : (int)$days) ?>"
                      data-description="<?= View::e($p['description'] ?? '') ?>"
                      data-stock="<?= View::e($p['stock'] ?? 0) ?>"
                      data-price="<?= number_format((float)($p['price'] ?? 0), 2, '.', '') ?>"
                      data-expires_at="<?= View::e($p['expires_at'] ?? '') ?>"
                      data-status="<?= View::e($p['status'] ?? '') ?>"
                      data-image="<?= View::e($p['image'] ?? '') ?>"
                      data-category="<?= View::e($catName) ?>"
                      data-supplier="<?= View::e($supName) ?>"
                      data-hasref="<?= (!empty($hasSales) && isset($hasSales[(int)$p['id']])) ? '1' : '0' ?>"
                      data-stkdanger="<?= (int)$STOCK_DANGER ?>"
                      data-stkwarn="<?= (int)$STOCK_WARN ?>"
                      data-shelf="<?= View::e($p['shelf'] ?? '') ?>"
                      data-row="<?= View::e($p['row'] ?? '') ?>"
                      data-position="<?= View::e($p['position'] ?? '') ?>">
                <i class="fas fa-eye mr-1" aria-hidden="true"></i> Ver
              </button>
              <?php
                $isActiveSt = (strtolower($p['status'] ?? '') === 'active' || strtolower($p['status'] ?? '') === 'activo');
                // Deshabilitar si el producto no está activo, no tiene stock o ya venció (incluyendo hoy)
                $canAddCart = $isActiveSt && $stock > 0 && ($days === null || $days > 0);
              ?>
              <button type="button"
                      class="btn btn-sm btn-add-cart-orange btnAddToCartProduct"
                      data-id="<?= (int)$p['id'] ?>"
                      data-sku="<?= View::e($p['sku'] ?? '') ?>"
                      data-name="<?= View::e($p['name'] ?? '') ?>"
                      data-description="<?= View::e($p['description'] ?? '') ?>"
                      data-stock="<?= View::e($p['stock'] ?? 0) ?>"
                      data-price="<?= (int)($p['price'] ?? 0) ?>"
                      data-expires_at="<?= View::e($p['expires_at'] ?? '') ?>"
                      data-status="<?= View::e($p['status'] ?? '') ?>"
                      data-image="<?= View::e($p['image'] ?? '') ?>"
                      <?= $canAddCart ? '' : 'disabled' ?>
              >
                <i class="fas fa-cart-plus mr-1" aria-hidden="true"></i> Agregar al carrito
              </button>
              <?php if ($isAdmin): ?>
              <?php if ($isRetired): ?>
                <form method="post" action="<?= BASE_URL ?>/products/reactivate/<?= View::e($p['id']) ?>" style="display:inline">
                  <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
                  <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo-alt mr-1" aria-hidden="true"></i> Reactivar</button>
                </form>
              <?php else: ?>
                <a class="btn btn-sm btn-edit-purple" href="<?= BASE_URL ?>/products/edit/<?= View::e($p['id']) ?>"><i class="fas fa-edit mr-1" aria-hidden="true"></i> Editar</a>
                <?php
                  $pid = (int)($p['id'] ?? 0);
                  $hasRef = !empty($hasSales) && isset($hasSales[$pid]);
                ?>
                <?php if ($hasRef): ?>
                  <form method="post" action="<?= BASE_URL ?>/products/retire/<?= View::e($pid) ?>" style="display:inline" data-confirm="¿Seguro que deseas desactivar este producto? El stock se establecerá en 0 y no estará disponible para la venta.">
                    <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-ban mr-1" aria-hidden="true"></i> Desactivar</button>
                  </form>
                <?php else: ?>
                  <form method="post" action="<?= BASE_URL ?>/products/delete/<?= View::e($pid) ?>" style="display:inline" data-confirm="¿Seguro que deseas eliminar este producto? Esta acción no se puede deshacer.">
                    <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt mr-1" aria-hidden="true"></i> Eliminar</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; // noProducts ?>
  </div>
  <?php
    $hasAnyFilter = (!empty($q) || !empty($categoryId) || (!empty($expiry) && $expiry !== 'all') || !empty($stock));
  ?>
  <script>
    // Build detailed notifications (expired and expiring soon) with product names
    (function(){
      try {
        var tbl = document.querySelector('.table.table-hover tbody');
        if (!tbl) return;
        var expired = [], soon = [];
        tbl.querySelectorAll('tr').forEach(function(tr){
          try {
            var daysAttr = tr.getAttribute('data-days');
            if (daysAttr === null || daysAttr === '') return;
            var days = parseInt(daysAttr, 10);
            var nameEl = tr.querySelector('.name-text');
            var name = (nameEl && nameEl.textContent) ? nameEl.textContent.trim() : '';
            if (!name) return;
            if (!isNaN(days)) {
              if (days <= 0) expired.push(name);
              else if (days <= 31) soon.push(name);
            }
          } catch(_){ }
        });
        if (expired.length || soon.length) {
          var parts = [];
          if (expired.length) parts.push('<div><span class="badge badge-danger mr-1">Vencidos (incluye hoy): '+expired.length+'</span> '+expired.slice(0,10).join(', ')+(expired.length>10?'…':'')+'</div>');
          if (soon.length) parts.push('<div><span class="badge badge-warning mr-1">Por vencer (≤31d): '+soon.length+'</span> '+soon.slice(0,10).join(', ')+(soon.length>10?'…':'')+'</div>');
          var html = parts.join('');
          if (window.notify) {
            window.notify({ icon: 'info', title: 'Alertas de vencimiento', html: html, timeout: 8000 });
          } else if (window.Swal && window.Swal.fire) {
            window.Swal.fire({ icon: 'info', title: 'Alertas de vencimiento', html: html, confirmButtonText: 'Entendido' });
          }
        }
      } catch(_){ }
    })();
  </script>
  <?php if (!empty($noProducts) && $noProducts && $hasAnyFilter): ?>
  <script>
    (function(){
      function showNotFoundModal(){
        try {
          var isSearch = <?= !empty($q) ? 'true' : 'false' ?>;
          var title = isSearch ? 'Este producto no está en el inventario' : 'No hay productos que coincidan con los filtros';
          var text  = isSearch ? 'Verifica el nombre o SKU e inténtalo nuevamente.' : 'Ajusta los filtros (categoría, vencimiento, stock) e inténtalo de nuevo.';
          if (window.Swal && typeof Swal.fire === 'function') {
            Swal.fire({ icon: 'info', title: title, text: text, confirmButtonText: 'Entendido', allowOutsideClick: true, allowEscapeKey: true });
          } else if (window.notify) {
            notify({ icon: 'info', title: title, text: text, position: 'center' });
          } else { alert(title); }
        } catch(_){}
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showNotFoundModal);
      else showNotFoundModal();
    })();
  </script>
  <?php endif; ?>
</div>

<!-- Floating Cart Button and Modal (Products) -->
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
      <div class="text-muted p-3">No hay borrador de carrito en este navegador.</div>
    </div>
    <div class="ps-modal-footer d-flex justify-content-between align-items-center" id="cartModalFooter" style="display:none;">
      <div class="text-muted small">Borrador guardado localmente.</div>
      <div class="d-flex align-items-center" style="gap:.5rem;">
        <div class="mr-3"><strong>Total:</strong> <span id="cartModalTotal">$0</span></div>
        <a href="<?= BASE_URL ?>/sales/create" class="btn btn-outline-primary btn-sm"><i class="fas fa-cash-register mr-1"></i> Ir a realizar compra</a>
        <button type="button" class="btn btn-outline-danger btn-sm" id="cartModalClear"><i class="fas fa-trash mr-1"></i> Vaciar</button>
      </div>
    </div>
  </div>
  <div class="ps-modal-backdrop" id="cartModalBackdrop"></div>
</div>

<?php if (!empty($pagination) && is_array($pagination)): ?>
  <?php
    $pg = $pagination; $page = (int)($pg['page'] ?? 1); $pages = (int)($pg['pages'] ?? 1);
    $per = (int)($pg['per'] ?? 15); $total = (int)($pg['total'] ?? 0);
    $params = [];
    if (!empty($q)) $params['q'] = (string)$q;
    if (!empty($categoryId)) $params['category_id'] = (int)$categoryId;
    if (!empty($expiry) && $expiry !== 'all') $params['expiry'] = (string)$expiry;
    if (!empty($stock)) $params['stock'] = (string)$stock;
    $qs = function($arr){ $out=[]; foreach($arr as $k=>$v){ $out[] = urlencode((string)$k).'='.urlencode((string)$v); } return $out?('&'.implode('&',$out)) : ''; };
    $base = BASE_URL . ((isset($retired) && $retired) ? '/products/retired' : (isset($title) && strpos($title,'vencid')!==false ? '/products/expired' : '/products'));
    function pageUrl($base,$p,$per,$q){ return $base . '?page=' . max(1,(int)$p) . '&per=' . (int)$per . $q; }
    $qParam = $qs($params);
  ?>
  <div class="d-flex justify-content-between align-items-center mt-2">
    <div class="text-muted small">Mostrando página <?= $page ?> de <?= $pages ?> (<?= $total ?> registros)</div>
    <nav aria-label="Paginación productos">
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $page <= 1 ? '#' : pageUrl($base,1,$per,$qParam) ?>">Primera</a></li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $page <= 1 ? '#' : pageUrl($base,$page-1,$per,$qParam) ?>">Anterior</a></li>
        <li class="page-item disabled"><span class="page-link"><?= $page ?></span></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $page >= $pages ? '#' : pageUrl($base,$page+1,$per,$qParam) ?>">Siguiente</a></li>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $page >= $pages ? '#' : pageUrl($base,$pages,$per,$qParam) ?>">Última</a></li>
      </ul>
    </nav>
  </div>
<?php endif; ?>

<style>
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
  /* Modal status theming */
  .pd-theme-danger .modal-header { background: #ffebee; border-bottom-color: #ffcdd2; }
  .pd-theme-danger .modal-title { color: #c62828; }
  .pd-theme-danger .modal-content { border: 1px solid #ef9a9a; }
  .pd-theme-warning .modal-header { background: #fff8e1; border-bottom-color: #ffe082; }
  .pd-theme-warning .modal-title { color: #ef6c00; }
  .pd-theme-warning .modal-content { border: 1px solid #ffcc80; }
  .pd-theme-success .modal-header { background: #e8f5e9; border-bottom-color: #a5d6a7; }
  .pd-theme-success .modal-title { color: #2e7d32; }
  .pd-theme-success .modal-content { border: 1px solid #a5d6a7; }
  /* Uniform modal action buttons */
  #pd-actions .btn { min-width: 150px; }
  @media (max-width: 576px) {
    .ps-modal-dialog { width: 96%; margin: 10px auto; max-height: calc(100vh - 20px); }
    .ps-modal-body { max-height: calc(100vh - 170px); }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    // Loading banner (centered, blocks UI) used when agregando al carrito
    if (!window.loadingBar) {
      window.loadingBar = {
        start: function(msg){
          msg = msg || 'Cargando...';
          try {
            if (window.Swal && typeof Swal.fire === 'function') {
              Swal.fire({
                title: msg,
                html: '<div style="font-weight:600;margin-top:6px;">Agregando producto al carrito</div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                backdrop: true,
                didOpen: function(){ try { Swal.showLoading(); } catch(_){} }
              });
              return;
            }
          } catch(_){ }
          // Fallback overlay
          try {
            var ov = document.getElementById('ps-loading-overlay');
            if (ov) return;
            ov = document.createElement('div');
            ov.id = 'ps-loading-overlay';
            ov.style.position = 'fixed'; ov.style.inset = '0'; ov.style.zIndex = '2000';
            ov.style.background = 'rgba(0,0,0,.45)'; ov.style.display = 'flex'; ov.style.alignItems = 'center'; ov.style.justifyContent = 'center';
            var box = document.createElement('div');
            box.style.background = '#fff'; box.style.borderRadius = '10px'; box.style.padding = '18px 22px'; box.style.boxShadow = '0 12px 28px rgba(0,0,0,.25)';
            box.style.fontWeight = '700'; box.style.color = '#111'; box.textContent = msg + ' · Agregando producto al carrito';
            ov.appendChild(box);
            document.body.appendChild(ov);
            // Trap interaction
            function stop(e){ e.preventDefault(); e.stopPropagation(); return false; }
            ov.addEventListener('click', stop, true);
            document.addEventListener('keydown', stop, true);
            ov.dataset.trap = '1';
          } catch(_){}
        },
        stop: function(){
          try { if (window.Swal && typeof Swal.close === 'function') return Swal.close(); } catch(_){ }
          var ov = document.getElementById('ps-loading-overlay');
          if (ov && ov.parentNode) ov.parentNode.removeChild(ov);
        }
      };
    }
    var IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
    // If global cart is present in layout, hide/remove local cart UI to avoid duplicates
    try {
      var gFab = document.getElementById('globalCartFab');
      if (gFab) {
        var lf = document.getElementById('btnCartFloating'); if (lf) lf.style.display = 'none';
        var lm = document.getElementById('cartModal'); if (lm && lm.parentNode) lm.parentNode.removeChild(lm);
      }
    } catch(_){}
    // Modal de detalles
    function createDetailModal(){
      if (document.getElementById('productDetailModal')) return;
      var wrap = document.createElement('div');
      wrap.innerHTML = `
      <div class="modal fade" id="productDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content" id="pd-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fas fa-info-circle mr-2" aria-hidden="true"></i> Detalles del producto</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
              <div class="d-flex align-items-start" style="gap:14px;">
                <div>
                  <h6 class="font-weight-bold mb-1">Producto</h6>
                  <img id="pd-img" src="" alt="Imagen del producto" style="width: 140px; height: 140px; object-fit: cover; border: 1px solid #eee; border-radius: 6px; display: none;">
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex flex-wrap">
                    <div class="mr-3 mb-2"><strong>SKU:</strong> <span id="pd-sku">—</span></div>
                    <div class="mr-3 mb-2"><strong>Producto:</strong> <span id="pd-name">—</span></div>
                  </div>
                  <div class="d-flex flex-wrap small text-muted mb-2">
                    <div class="mr-3"><strong>Precio:</strong> <span id="pd-price">—</span></div>
                    <div class="mr-3"><strong>Stock:</strong> <span id="pd-stock">—</span></div>
                    <div class="mr-3"><strong>Caducidad:</strong> <span id="pd-exp">—</span></div>
                    <div class="mr-3"><strong>Estado:</strong> <span id="pd-status">—</span></div>
                    <div class="mr-3"><strong>Categoría:</strong> <span id="pd-cat">—</span></div>
                    <div class="mr-3"><strong>Proveedor:</strong> <span id="pd-sup">—</span></div>
                  </div>
                  <!-- Sección de Ubicación -->
                  <div class="mb-2">
                    <h6 class="font-weight-bold mb-1">Ubicación en Almacén</h6>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                      <div class="bg-light p-2 rounded" style="min-width: 100px;">
                        <div class="text-muted small">Estante</div>
                        <div id="pd-shelf" class="font-weight-bold">—</div>
                      </div>
                      <div class="bg-light p-2 rounded" style="min-width: 80px;">
                        <div class="text-muted small">Fila</div>
                        <div id="pd-row" class="font-weight-bold">—</div>
                      </div>
                      <div class="bg-light p-2 rounded" style="min-width: 100px;">
                        <div class="text-muted small">Posición</div>
                        <div id="pd-position" class="font-weight-bold">—</div>
                      </div>
                      <div class="d-flex align-items-end">
                        <a href="<?= BASE_URL ?>/locations" id="pd-view-map" class="btn btn-sm btn-outline-primary" style="display: none;">
                          <i class="fas fa-map-marker-alt mr-1"></i> Ver en Mapa
                        </a>
                      </div>
                    </div>
                  </div>
                  <div>
                    <h6 class="font-weight-bold mb-1">Descripción</h6>
                    <div id="pd-desc">—</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
              <div class="text-muted small" id="pd-hint"></div>
              <div class="d-flex align-items-center" id="pd-actions" style="gap:8px;">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      </div>`;
      document.body.appendChild(wrap.firstElementChild);
    }
    // Crear al cargar
    createDetailModal();

    function fmtCurrency(n){
      try{
        return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0, maximumFractionDigits:0 }).format(parseFloat(n||0));
      }catch(e){
        var v = Math.round(parseFloat(n||0));
        return '$' + v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      }
    }
    function decodeHtmlEntities(str){
      try { var ta = document.createElement('textarea'); ta.innerHTML = str; return ta.value; } catch(e){ return str; }
    }
    function showModalFallback(m){
      // Simple backdrop
      var bd = document.getElementById('ps-backdrop');
      if (!bd) {
        bd = document.createElement('div');
        bd.id = 'ps-backdrop';
        bd.style.position = 'fixed';
        bd.style.top = '0';
        bd.style.left = '0';
        bd.style.right = '0';
        bd.style.bottom = '0';
        bd.style.background = 'rgba(0,0,0,0.4)';
        bd.style.zIndex = '1040';
        document.body.appendChild(bd);
      }
      m.style.display = 'block';
      m.classList.add('show');
      m.style.zIndex = '1050';
      m.setAttribute('aria-modal','true');
      m.removeAttribute('aria-hidden');
      // Cerrar con backdrop o botón close
      function hide(){
        m.style.display = 'none';
        m.classList.remove('show');
        m.removeAttribute('aria-modal');
        m.setAttribute('aria-hidden','true');
        if (bd && bd.parentNode) bd.parentNode.removeChild(bd);
        document.removeEventListener('keydown', escHandler);
      }
      var escHandler = function(e){ if (e.key === 'Escape') hide(); };
      document.addEventListener('keydown', escHandler);
      bd.onclick = hide;
      var closeBtn = m.querySelector('.modal-header .close');
      if (closeBtn) closeBtn.onclick = hide;
    }
    function openDetails(btn){
      var m = document.getElementById('productDetailModal');
      if (!m) { try { createDetailModal(); m = document.getElementById('productDetailModal'); } catch(_){} }
      if (!m) {
        if (window.Swal && Swal.fire) {
          return Swal.fire({icon:'info', title:'Detalles', text:'No se pudo crear el modal de detalles.'});
        }
        return;
      }
      var el;
      el = m.querySelector('#pd-sku'); if (el) el.textContent = btn.getAttribute('data-sku') || '—';
      el = m.querySelector('#pd-name'); if (el) el.textContent = btn.getAttribute('data-name') || '—';
      el = m.querySelector('#pd-price'); if (el) el.textContent = fmtCurrency(btn.getAttribute('data-price'));
      el = m.querySelector('#pd-stock'); if (el) { el.textContent = btn.getAttribute('data-stock') || '0'; el.className = ''; }
      var exp = btn.getAttribute('data-expires_at') || '';
      el = m.querySelector('#pd-exp'); if (el) { el.textContent = exp !== '' ? exp : '—'; el.className = ''; }
      var st = btn.getAttribute('data-status') || '';
      el = m.querySelector('#pd-status'); if (el) {
        var stLower = (st||'').toLowerCase();
        var label = stLower==='active' || stLower==='activo' ? 'Activo' : 'Inactivo';
        el.textContent = label;
        el.classList.remove('badge','badge-success','badge-danger','badge-secondary');
        el.classList.add('badge', (label==='Activo' ? 'badge-success' : 'badge-danger'));
      }
      var desc = btn.getAttribute('data-description') || '';
      el = m.querySelector('#pd-desc'); if (el) el.textContent = desc !== '' ? desc : 'Sin descripción';
      var cat = btn.getAttribute('data-category') || '';
      el = m.querySelector('#pd-cat'); if (el) el.textContent = cat !== '' ? cat : '—';
      var sup = btn.getAttribute('data-supplier') || '';
      el = m.querySelector('#pd-sup'); if (el) el.textContent = sup !== '' ? sup : '—';
      
      // Mostrar información de ubicación
      var shelf = btn.getAttribute('data-shelf') || '';
      var row = btn.getAttribute('data-row') || '';
      var position = btn.getAttribute('data-position') || '';
      
      el = m.querySelector('#pd-shelf'); if (el) el.textContent = shelf !== '' ? shelf : '—';
      el = m.querySelector('#pd-row'); if (el) el.textContent = row !== '' ? row : '—';
      el = m.querySelector('#pd-position'); if (el) el.textContent = position !== '' ? position : '—';
      
      // Mostrar botón de ver en mapa solo si hay ubicación
      var viewMapBtn = m.querySelector('#pd-view-map');
      if (viewMapBtn) {
        if (shelf || row || position) {
          viewMapBtn.style.display = 'inline-flex';
          // Agregar parámetros de ubicación al enlace del mapa
          if (shelf) {
            var mapUrl = '<?= BASE_URL ?>/locations';
            if (shelf) mapUrl += '?shelf=' + encodeURIComponent(shelf);
            if (row) mapUrl += (shelf ? '&' : '?') + 'row=' + encodeURIComponent(row);
            viewMapBtn.href = mapUrl;
          }
        } else {
          viewMapBtn.style.display = 'none';
        }
      }
      
      // Imagen
      try {
        var imgName = btn.getAttribute('data-image') || '';
        var imgEl = m.querySelector('#pd-img');
        if (imgEl) {
          if (imgName) {
            imgEl.src = '<?= BASE_URL ?>/uploads/' + imgName;
            imgEl.style.display = '';
          } else {
            imgEl.removeAttribute('src');
            imgEl.style.display = 'none';
          }
        }
      } catch(_){ }
      // Theming and actions
      try {
        var stock = parseInt(btn.getAttribute('data-stock')||'0',10) || 0;
        var sd = parseInt(btn.getAttribute('data-stkdanger')||'20',10) || 20;
        var swCfg = parseInt(btn.getAttribute('data-stkwarn')||'60',10) || 60;
        var sw = Math.max(sd, swCfg); // warning hasta STOCK_WARN (por defecto 60)
        // Prefer server-computed days (exactly what the table uses) if available
        var btnDaysAttr = btn.getAttribute('data-expdays');
        var days = (btnDaysAttr !== null && btnDaysAttr !== '' && !isNaN(parseInt(btnDaysAttr,10))) ? parseInt(btnDaysAttr,10) : null;
        if (days === null && exp) {
          // Compute days until expiry using LOCAL date-only and round to avoid off-by-one in some environments
          (function(){
            try {
              var m = /^([0-9]{4})-([0-9]{2})-([0-9]{2})/.exec(exp);
              if (m) {
                var y = parseInt(m[1],10), mo = parseInt(m[2],10)-1, d = parseInt(m[3],10);
                var expLocal = new Date(y, mo, d); // local midnight of expiry
                var now = new Date();
                var todayLocal = new Date(now.getFullYear(), now.getMonth(), now.getDate()); // local midnight today
                days = Math.round((expLocal - todayLocal) / 86400000);
              }
            } catch(_){ /* keep days as null on parse error */ }
          })();
        }
        // Nivel de stock y de caducidad (se toma el peor para el tema del modal)
        var levelStock = 'success';
        if (stock <= sd) levelStock = 'danger';
        else if (stock <= sw) levelStock = 'warning';
        else levelStock = 'success'; // > STOCK_WARN
        var levelExp = 'success';
        if (typeof days === 'number') {
          if (days < 0) levelExp = 'danger';
          else if (days <= 31) levelExp = 'warning';
        }
        var level = (levelStock === 'danger' || levelExp === 'danger') ? 'danger'
                  : ((levelStock === 'warning' || levelExp === 'warning') ? 'warning' : 'success');
        var content = m.querySelector('#pd-content');
        if (content) {
          content.classList.remove('pd-theme-danger','pd-theme-warning','pd-theme-success');
          content.classList.add('pd-theme-' + level);
        }
        // Pintar el campo de Stock (badge)
        var stockEl = m.querySelector('#pd-stock');
        if (stockEl) {
          stockEl.classList.remove('badge','badge-danger','badge-warning','badge-success');
          stockEl.classList.add('badge', 'badge-' + levelStock);
          var label = (levelStock==='danger') ? 'Muy bajo' : (levelStock==='warning' ? 'Bajo' : 'Óptimo');
          stockEl.textContent = String(stock) + ' (' + label + ')';
          stockEl.setAttribute('title', 'Stock actual');
        }
        // Pintar caducidad como badge si aplica (con días restantes o vencidos)
        var expEl = m.querySelector('#pd-exp');
        if (expEl) {
          expEl.classList.remove('badge','badge-danger','badge-warning','badge-success','badge-primary');
          if (typeof days === 'number') {
            if (days < 0) {
              expEl.classList.add('badge','badge-danger');
              var absd = Math.abs(days);
              var dl = (absd === 1 ? 'día' : 'días');
              expEl.textContent = (exp || '') + ' (Venció hace ' + absd + ' ' + dl + ')';
            } else if (days === 0) {
              expEl.classList.add('badge','badge-danger');
              expEl.textContent = (exp || '') + ' (Venció hoy)';
            } else if (days <= 31) {
              expEl.classList.add('badge','badge-warning');
              var dl2 = (days === 1 ? 'día' : 'días');
              expEl.textContent = (exp || '') + ' (Faltan ' + days + ' ' + dl2 + ')';
            } else {
              // OK: azulito con días
              expEl.classList.add('badge','badge-primary');
              expEl.textContent = (exp || '—') + ' (' + days + ' d)';
            }
          } else {
            expEl.textContent = '—';
          }
        }
        var hint = m.querySelector('#pd-hint');
        if (hint) {
          hint.textContent = level==='danger' ? 'Estado: Peligro' : (level==='warning'?'Estado: Advertencia':'Estado: Correcto');
        }
        // Build actions: Desactivar (si activo y admin), Agregar al carrito (si disponible) y Cerrar
        var actions = m.querySelector('#pd-actions');
        if (actions) {
          var pid = parseInt(btn.getAttribute('data-id')||'0',10)||0;
          var name = btn.getAttribute('data-name')||'';
          var sku = btn.getAttribute('data-sku')||'';
          var desc = btn.getAttribute('data-description')||'';
          var price = Math.max(0, Math.round(parseFloat(btn.getAttribute('data-price')||'0')||0));
          var img = btn.getAttribute('data-image')||'';
          var hasRef = (btn.getAttribute('data-hasref')||'0') === '1';
          var status = (btn.getAttribute('data-status')||'').toLowerCase();
          var canAdd = (status==='active'||status==='activo') && stock>0 && !(days!==null && days<0);
          var html = '';
          if (IS_ADMIN && (status==='active' || status==='activo')) {
            html += '<form method="post" action="<?= BASE_URL ?>/products/retire/' + pid + '" style="display:inline;">'
                 + '<input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">'
                 + '<button type="submit" class="btn btn-sm ' + (hasRef ? 'btn-danger' : 'btn-outline-secondary') + '"><i class="fas fa-ban mr-1"></i> Desactivar</button>'
                 + '</form>';
          }
          // Botón Agregar al carrito
          html += (html ? ' ' : '')
               + '<button type="button" class="btn btn-sm btn-add-cart-orange btnAddToCartProduct" '
               + 'data-id="' + pid + '" data-name="' + (name||'') + '" '
               + 'data-sku="' + (sku||'') + '" '
               + 'data-description="' + desc + '" '
               + 'data-price="' + price + '" data-stock="' + stock + '" '
               + 'data-status="' + status + '" data-image="' + img + '" '
               + (canAdd ? '' : 'disabled') + '><i class="fas fa-cart-plus mr-1" aria-hidden="true"></i> Agregar al carrito</button>';
          // Botón Cerrar
          html += ' <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>';
          actions.innerHTML = html;
        }
      } catch(_){ }
      // No tabla adicional: solo campos principales y descripción
      // Mostrar modal
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) { jQuery(m).modal('show'); }
      else { showModalFallback(m); }
    }
    // Helper: nicer prompt to go to cart
    function goToCartPrompt(){
      if (window.Swal && Swal.fire) {
        return Swal.fire({
          title: 'Producto agregado',
          text: '¿Deseas ir al carrito de ventas ahora?',
          icon: 'success',
          showCancelButton: true,
          confirmButtonText: 'Ir al carrito',
          cancelButtonText: 'Seguir viendo productos',
          reverseButtons: true,
          focusCancel: true,
          heightAuto: false,
          toast: false,
          backdrop: true,
          allowOutsideClick: false,
          allowEscapeKey: false
        }).then(function(res){ if (res.isConfirmed) { window.location.href = '<?= BASE_URL ?>/sales/create'; } });
      }
      // Fallback: centered modal with backdrop
      try {
        var id = 'ps-go-cart-overlay';
        var old = document.getElementById(id);
        if (old && old.parentNode) old.parentNode.removeChild(old);
        var ov = document.createElement('div');
        ov.id = id;
        ov.style.position = 'fixed';
        ov.style.inset = '0';
        ov.style.background = 'rgba(0,0,0,.35)';
        ov.style.zIndex = '1060';
        ov.style.display = 'flex';
        ov.style.alignItems = 'center';
        ov.style.justifyContent = 'center';
        var card = document.createElement('div');
        card.style.background = '#fff';
        card.style.border = '1px solid #e5e5e5';
        card.style.borderRadius = '12px';
        card.style.boxShadow = '0 10px 30px rgba(0,0,0,.18)';
        card.style.padding = '18px 20px';
        card.style.maxWidth = '420px';
        card.style.width = '92%';
        card.innerHTML = '<div style="font-weight:600; font-size:1.05rem; margin-bottom:8px;">Producto agregado</div>\
                         <div style="font-size:.95rem; color:#555; margin-bottom:14px;">¿Deseas ir al carrito de ventas ahora?</div>\
                         <div style="display:flex; gap:10px; justify-content:flex-end;">\
                           <button id="ps-continue" class="btn btn-sm btn-light">Seguir viendo productos</button>\
                           <button id="ps-go" class="btn btn-sm btn-primary">Ir al carrito</button>\
                         </div>';
        ov.appendChild(card);
        document.body.appendChild(ov);
        var close = function(){ if (ov && ov.parentNode) ov.parentNode.removeChild(ov); };
        // Disable closing by clicking on the backdrop; only buttons close
        card.querySelector('#ps-continue').onclick = close;
        card.querySelector('#ps-go').onclick = function(){ window.location.href = '<?= BASE_URL ?>/sales/create'; };
      } catch(_){ window.location.href = '<?= BASE_URL ?>/sales/create'; }
    }
    // Delegación robusta de clics (soporta clic en <i> dentro del botón)
    document.addEventListener('click', function(ev){
      var t = ev.target;
      if (!t) return;
      var viewBtn = t.closest ? t.closest('.btnViewProduct') : null;
      if (viewBtn) {
        ev.preventDefault();
        try { console.debug('[Productos] abrir detalles', {id: viewBtn.getAttribute('data-id')}); } catch(_){ }
        openDetails(viewBtn);
        return;
      }
      var addBtn = t.closest ? t.closest('.btnAddToCartProduct') : null;
      if (addBtn) {
        ev.preventDefault();
        var btn = addBtn;
        var pid = parseInt(btn.getAttribute('data-id') || '0', 10) || 0;
        var name = btn.getAttribute('data-name') || '';
        var sku = btn.getAttribute('data-sku') || '';
        var desc = btn.getAttribute('data-description') || '';
        var price = Math.max(0, Math.round(parseFloat(btn.getAttribute('data-price') || '0')||0));
        var stock = parseInt(btn.getAttribute('data-stock') || '0', 10) || 0;
        var img = btn.getAttribute('data-image') || '';
        var status = (btn.getAttribute('data-status') || '').toLowerCase();
        if (!pid) return;
        if (status !== 'active' && status !== 'activo') {
          return centerNotify({ icon:'info', title:'No disponible', text:'El producto no está activo.' , position:'bottom-end', toast:true });
        }
        if (stock <= 0) {
          return centerNotify({ icon:'warning', title:'Sin stock', text:'No hay stock disponible.' , position:'bottom-end', toast:true });
        }
        var item = { product_id: pid, sku: sku, name: name, description: desc, unit_price: price, stock: stock, image: img, status: status, qty: 1 };
        var proceed = function(){
          // Show centered blocking overlay for ~3.5s while adding
          try { window.bannerLoadingMinDuration = 3500; } catch(_){ }
          try { if (window.bannerLoading) bannerLoading(true, 'Agregando productos...'); } catch(_){ }
          try {
            var uid = <?= (int)(\App\Helpers\Auth::id() ?? 0) ?>;
            var KEY = 'pharmasoft_sales_draft_' + uid;
            var LEGACY = 'pharmasoft_pending_cart';
            var SHARED = 'pharmasoft_sales_draft';
            // migrate legacy/shared if present
            (function migrate(){
              try {
                var shared = localStorage.getItem(SHARED);
                if (shared && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, shared); }
                var old = localStorage.getItem(LEGACY);
                if (old && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, old); localStorage.removeItem(LEGACY); }
              } catch(_){ }
            })();
            var arr = [];
            try { arr = JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch(e){ arr = []; }
            var found = false;
            for (var i=0;i<arr.length;i++) {
              if ((arr[i]||{}).product_id === pid) {
                var next = Math.min(stock, (parseInt(arr[i].qty||'1',10)||1)+1);
                arr[i].qty = next;
                arr[i].unit_price = price; arr[i].name = name; arr[i].sku = sku; arr[i].image = img; arr[i].status = status; arr[i].description = desc; arr[i].stock = stock;
                found = true; break;
              }
            }
            if (!found) arr.push(item);
            localStorage.setItem(KEY, JSON.stringify(arr));
            // live refresh FAB/modal if present
            try { if (window.psCart && typeof window.psCart.refresh === 'function') window.psCart.refresh(); } catch(_){ }
            centerNotify({ icon:'success', title:'Agregado', text:'"' + name + '" se agregó al carrito.', position:'bottom-end', toast:true });
            goToCartPrompt();
          } catch(e) {
            console.error('add to cart failed', e);
          } finally {
            try { if (window.bannerLoading) bannerLoading(false); } catch(_){ }
          }
        };
        // Confirmar costo antes de agregar
        if (window.Swal && Swal.fire) {
          Swal.fire({
            title: 'Confirmar agregado',
            html: 'Producto: <strong>' + (name || sku) + '</strong><br>Precio: <strong>' + fmtCurrency(price) + '</strong><br><small>Se agregará 1 unidad.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
          }).then(function(r){ if (r && r.isConfirmed) proceed(); });
        } else {
          if (confirm('Agregar "' + (name||sku) + '" por ' + fmtCurrency(price) + ' al carrito?')) proceed();
        }
      }
    });
    function centerNotify(opts){
      opts = opts || {};
      var pos = opts.position || 'center';
      var isToast = typeof opts.toast === 'undefined' ? false : !!opts.toast;
      var icon = opts.icon || 'info';
      var title = opts.title || '';
      var text = opts.text || '';
      var timer = opts.timer || 2500;
      try {
        if (typeof window.notify === 'function') {
          // Let custom notify handle stacking if it supports it
          return notify({ position: pos, toast: isToast, icon: icon, title: title, text: text, timer: timer });
        }
      } catch(_){ }
      if (window.Swal && Swal.fire) {
        // Use a mixin so multiple toasts can be displayed simultaneously
        var Toast = Swal.mixin({
          toast: isToast,
          position: pos,
          showConfirmButton: false,
          timer: timer,
          timerProgressBar: true,
          backdrop: false,
          target: document.body,
          heightAuto: false,
          customClass: { container: 'ps-swal-container' }
        });
        // Prefer title for toast; if not toast, use text as content too
        if (isToast) {
          return Toast.fire({ icon: icon, title: title || text });
        } else {
          return Toast.fire({ icon: icon, title: title, text: text });
        }
      }
    }
    try {
      var low = <?= (int)($lowStockCount ?? 0) ?>;
      var thr = <?= $thr ?>;
      var soon = <?= (int)($soonCount ?? 0) ?>;
      var expired = <?= (int)($expiredCount ?? 0) ?>;
      if (low > 0) { centerNotify({ icon:'warning', title:'Alerta de Stock', text:'Tienes ' + low + ' producto(s) con stock bajo (≤ ' + thr + ').', position:'bottom-end', toast:true }); }
      if (soon > 0) { centerNotify({ icon:'warning', title:'Próximos a vencer', text: soon + ' producto(s) vencen en ≤ 30 días.', position:'bottom-end', toast:true }); }
      if (expired > 0) { centerNotify({ icon:'error', title:'Productos vencidos', text: 'Tienes ' + expired + ' producto(s) VENCIDO(s).', position:'bottom-end', toast:true }); }
      // Notificar cuando no hay resultados en la búsqueda
      var tbody = document.querySelector('table tbody');
      var rows = tbody ? tbody.querySelectorAll('tr') : [];
      var q = <?= json_encode($q ?? '') ?>;
      if (tbody && rows.length === 0) {
        var txt = q && q.trim() !== '' ? ('Este producto no está en el inventario: "' + q + '"') : 'No hay productos para mostrar';
        centerNotify({ icon:'info', title:'Sin resultados', text: txt, position:'bottom-end', toast:true });
        // Insertar fila placeholder para feedback visual
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        var thCount = document.querySelectorAll('table thead th').length || 7;
        td.colSpan = thCount;
        td.className = 'text-center text-muted';
        td.textContent = q && q.trim() !== '' ? ('Este producto no está en el inventario: "' + q + '"') : 'No hay productos';
        tr.appendChild(td);
        tbody.appendChild(tr);
      }
      // Filtro de vencimiento en cliente
      var sel = document.getElementById('expiryFilter');
      <?php if ($isRetired): ?>
      // En listado de retirados, el filtro de vencimiento no aplica visualmente, pero lo dejamos sin efecto
      sel.disabled = true;
      <?php endif; ?>
      function applyFilter(){
        var val = sel.value;
        var rows = document.querySelectorAll('table tbody tr');
        rows.forEach(function(tr){
          var d = tr.getAttribute('data-days');
          var days = d === '' || d === null ? null : parseInt(d,10);
          var show = true;
          if (val === '30') {
            show = days !== null && days <= 31;
          } else if (val === '60') {
            show = days !== null && days <= 60;
          } else {
            show = true;
          }
          tr.style.display = show ? '' : 'none';
        });
      }
      sel.addEventListener('change', applyFilter);
      // Mantener selección tras navegación (opcional simple)
      applyFilter();
    } catch(e){}

    // ----- Cart modal (Products) -----
    (function cartModalProducts(){
      // If global cart exists, do not init local cart modal
      if (document.getElementById('globalCartFab')) return;
      var uid = <?= (int)(\App\Helpers\Auth::id() ?? 0) ?>;
      var KEY = 'pharmasoft_sales_draft_' + uid;
      var LEGACY = 'pharmasoft_pending_cart';
      var SHARED = 'pharmasoft_sales_draft';
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

      function fmt(n){ try { return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0}).format(n||0); } catch(e){ var v=Math.round(n||0); return '$'+String(v).replace(/\B(?=(\d{3})+(?!\d))/g,'.'); } }
      function migrate(){
        try {
          var shared = localStorage.getItem(SHARED);
          if (shared && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, shared); }
          var old = localStorage.getItem(LEGACY);
          if (old && !localStorage.getItem(KEY)) { localStorage.setItem(KEY, old); localStorage.removeItem(LEGACY); }
        } catch(_){ }
      }
      function read(){ migrate(); try { var raw = localStorage.getItem(KEY); var arr = raw ? JSON.parse(raw||'[]')||[] : []; return Array.isArray(arr)?arr:[]; } catch(_){ return []; } }
      function write(arr){ try { if (arr && arr.length) localStorage.setItem(KEY, JSON.stringify(arr)); else localStorage.removeItem(KEY); } catch(_){} }
      function total(arr){ var t=0; (arr||[]).forEach(function(it){ var q=parseInt(it.qty||0,10)||0; var p=Math.round(parseFloat(it.unit_price||0)||0); t+=q*p; }); return t; }
      function render(){
        var items = read();
        var cnt = items.length;
        if (fabCount){ fabCount.style.display = cnt>0?'inline-block':'none'; fabCount.textContent = String(cnt); }
        if (mCount){ mCount.textContent = String(cnt); }
        if (!cnt){
          var emptyHtml = '<div class="p-4 text-center text-muted">'
            + '<div style="font-size:28px; margin-bottom:8px;"><i class="fas fa-shopping-cart"></i></div>'
            + '<div style="font-weight:600;">No hay productos agregados al carrito</div>'
            + '<div class="mt-1">Tu carrito está vacío.</div>'
            + '</div>';
          if (mBody) mBody.innerHTML = emptyHtml;
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
        var btns = mBody ? mBody.querySelectorAll('.btnRemoveItem') : [];
        if (btns && btns.forEach){ btns.forEach(function(b){ b.addEventListener('click', function(){ var tr=b.closest('tr'); var idx = tr ? parseInt(tr.getAttribute('data-i')||'-1',10) : -1; var arr = read(); if (idx>=0 && idx < arr.length){ arr.splice(idx,1); write(arr); render(); } }); }); }
      }
      function open(){ if (!modal) return; render(); modal.style.display='block'; document.body.style.overflow='hidden'; try{ if (window.notify) notify({ icon:'info', title:'Carrito abierto', timer: 2000, position:'top-end', toast:true }); }catch(_){ } }
      function close(){ if (!modal) return; modal.style.display='none'; document.body.style.overflow=''; try{ if (window.notify) notify({ icon:'info', title:'Carrito cerrado', timer: 2000, position:'top-end', toast:true }); }catch(_){ } }
      if (fab) fab.addEventListener('click', function(e){ e.preventDefault(); open(); });
      if (mClose) mClose.addEventListener('click', function(){ close(); });
      if (mBackdrop) mBackdrop.addEventListener('click', function(e){ if (e.target===mBackdrop) close(); });
      if (mClear) mClear.addEventListener('click', function(){
        try {
          if (window.Swal && Swal.fire) {
            return Swal.fire({
              title: 'Vaciar carrito',
              text: '¿Desea vaciar el borrador del carrito?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sí, vaciar',
              cancelButtonText: 'No'
            }).then(function(res){ if (res && res.isConfirmed) { try{ if (window.loadingBar) loadingBar.start('Cargando...'); }catch(_){ } setTimeout(function(){ write([]); render(); try{ if (window.loadingBar) loadingBar.stop(); }catch(_){ } }, 500); } });
          }
          if (window.psConfirm) {
            return window.psConfirm({ title:'Vaciar carrito', text:'¿Desea vaciar el borrador del carrito?', ok:'Sí', cancel:'No' })
              .then(function(ok){ if (ok) { try{ if (window.loadingBar) loadingBar.start('Cargando...'); }catch(_){ } setTimeout(function(){ write([]); render(); try{ if (window.loadingBar) loadingBar.stop(); }catch(_){ } }, 500); } });
          }
        } catch(_){ }
        if (confirm('¿Desea vaciar el borrador del carrito?')) { try{ if (window.loadingBar) loadingBar.start('Cargando...'); }catch(_){ } setTimeout(function(){ write([]); render(); try{ if (window.loadingBar) loadingBar.stop(); }catch(_){ } }, 500); }
      });
      // Intercept checkout to show loading before navigating
      (function(){
        try {
          var footer = document.getElementById('cartModalFooter');
          if (footer) {
            var a = footer.querySelector('a[href$="/sales/create"]');
            if (a) {
              a.addEventListener('click', function(e){
                try{ if (window.loadingBar) loadingBar.start('Cargando...'); }catch(_){ }
                // allow normal navigation shortly after showing loading
                setTimeout(function(){ try{ if (window.loadingBar) loadingBar.stop(); }catch(_){ } window.location.href = a.href; }, 350);
                e.preventDefault();
              });
            }
          }
        } catch(_){ }
      })();

      // expose for live refresh
      window.psCart = window.psCart || {};
      window.psCart.refresh = render;
      // initial badge update
      render();
    })();
  });
</script>
