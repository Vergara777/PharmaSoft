<?php use App\Core\View; ?>
<div class="card mt-3">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i> Movimientos del sistema</h3>
    <div>
      <?php
      $qsAll = $_GET ?? [];
      // Detect active tab
      $ent = trim((string)($filters['entity'] ?? ''));
      $act = trim((string)($filters['action'] ?? ''));
      $active = 'all';
      if ($ent === 'product' && $act === '') { $active = 'product'; }
      elseif ($ent === 'sale') { $active = 'sale'; }
      elseif ($act === 'delete') { $active = 'delete'; }
      elseif ($act === 'retire' && $ent === 'product') { $active = 'retire'; }

      // Base export params: keep only date range
      $exportQs = [];
      if (!empty($from)) $exportQs['from'] = $from;
      if (!empty($to)) $exportQs['to'] = $to;
      // Lock filters by tab
      if ($active === 'product') { $exportQs['entity'] = 'product'; }
      if ($active === 'sale')    { $exportQs['entity'] = 'sale'; }
      if ($active === 'delete')  { $exportQs['action'] = 'delete'; }
      if ($active === 'retire')  { $exportQs['entity'] = 'product'; $exportQs['action'] = 'retire'; }

      $exportUrl = rtrim(BASE_URL, '/') . '/movements/export?' . http_build_query($exportQs);
      $dlNameMap = [
        'product' => 'movimientos_productos.xlsx',
        'sale'    => 'movimientos_ventas.xlsx',
        'delete'  => 'movimientos_eliminaciones.xlsx',
        'retire'  => 'movimientos_retirados.xlsx',
        'all'     => 'movimientos.xlsx',
      ];
      $dlName = $dlNameMap[$active] ?? 'movimientos.xlsx';
      $printScope = $active; // used by JS to filter rows on print
    ?>
    <a id="btnMovementsExport" class="btn btn-sm btn-outline-primary mr-2" href="<?= View::e($exportUrl) ?>" rel="noopener" download="<?= View::e($dlName) ?>" type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" data-no-loader><i class="fas fa-file-excel mr-1"></i> <span class="btn-text">Exportar XLSX</span></a>
    <button type="button" id="btnMovementsExportPdf" data-scope="<?= View::e($printScope) ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf mr-1"></i> <span class="btn-text">Exportar PDF</span></button>
     </div>

<!-- Estilos locales -->
<style>
  /* Botón verde gradiente para acciones "Ver …" en tabla */
  .ps-btn-view { background: linear-gradient(135deg, #2ecc71, #27ae60); color:#fff; border:0; }
  .ps-btn-view:hover, .ps-btn-view:focus { color:#fff; filter: brightness(0.95); }
</style>
  </div>
  <div class="card-body">
    <?php
      // Helper: URLs de tabs, reseteando filtros no relacionados (conserva solo fechas)
      function ps_tab_url($entityVal, $actionVal, $from, $to){
        $qs = [];
        if (!empty($from)) $qs['from'] = $from;
        if (!empty($to))   $qs['to'] = $to;
        if ($entityVal !== null) $qs['entity'] = $entityVal;
        if ($actionVal !== null) $qs['action'] = $actionVal;
        return rtrim(BASE_URL,'/') . '/movements?' . http_build_query($qs);
      }
    ?>
    <ul class="nav nav-pills mb-3">
      <li class="nav-item mr-2"><a class="nav-link <?= (($filters['entity']??'')==='product' && ($filters['action']??'')==='')?'active':'' ?>" href="<?= View::e(ps_tab_url('product','', $from ?? null, $to ?? null)) ?>"><i class="fas fa-box mr-1"></i> Historial de Productos</a></li>
      <li class="nav-item mr-2"><a class="nav-link <?= (($filters['entity']??'')==='sale')?'active':'' ?>" href="<?= View::e(ps_tab_url('sale','', $from ?? null, $to ?? null)) ?>"><i class="fas fa-shopping-cart mr-1"></i> Historial de Ventas</a></li>
      <li class="nav-item mr-2"><a class="nav-link <?= (($filters['action']??'')==='delete')?'active':'' ?>" href="<?= View::e(ps_tab_url('', 'delete', $from ?? null, $to ?? null)) ?>"><i class="fas fa-trash mr-1"></i> Historial de Eliminaciones</a></li>
      <li class="nav-item"><a class="nav-link <?= (($filters['action']??'')==='retire')?'active':'' ?>" href="<?= View::e(ps_tab_url('product','retire', $from ?? null, $to ?? null)) ?>"><i class="fas fa-exclamation-triangle mr-1"></i> Historial de Retiros</a></li>
    </ul>
    <?php if (!empty($errors ?? [])): ?>
      <div class="alert alert-danger">
        <strong>Hay problemas con los filtros:</strong>
        <ul class="mb-0">
          <?php foreach (($errors ?? []) as $e): ?>
            <li><?= View::e($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <?php if (!empty($hints ?? [])): ?>
      <div class="alert alert-info">
        <ul class="mb-0">
          <?php foreach (($hints ?? []) as $h): ?>
            <li><?= View::e($h) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form id="movementsFilterForm" method="get" action="<?= BASE_URL ?>/movements" class="mb-3">
      <div class="form-row">
        <div class="col-md-2 mb-2">
          <label>ID</label>
          <input type="text" name="id" value="<?= View::e($filters['id'] ?? '') ?>" class="form-control" placeholder="Exacto">
        </div>
        <div class="col-md-2 mb-2">
          <label>Desde ID</label>
          <input type="text" name="id_from" value="<?= View::e($filters['id_from'] ?? '') ?>" class="form-control" placeholder=">= ID">
        </div>
        <div class="col-md-2 mb-2">
          <label>Hasta ID</label>
          <input type="text" name="id_to" value="<?= View::e($filters['id_to'] ?? '') ?>" class="form-control" placeholder="<= ID">
        </div>
        <div class="col-md-2 mb-2">
          <label>Desde</label>
          <input type="date" name="from" value="<?= View::e($from ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-2 mb-2">
          <label>Hasta</label>
          <input type="date" name="to" value="<?= View::e($to ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-2 mb-2">
          <label>Entidad</label>
          <input type="text" name="entity" value="<?= View::e($filters['entity'] ?? '') ?>" placeholder="producto, venta, usuario" class="form-control">
        </div>
        <div class="col-md-2 mb-2">
          <label>Acción</label>
          <input type="text" name="action" value="<?= View::e($filters['action'] ?? '') ?>" placeholder="crear, actualizar, eliminar" class="form-control">
        </div>
        <div class="col-md-2 mb-2">
          <label>User ID</label>
          <input type="text" name="user_id" value="<?= View::e($filters['user_id'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-2 mb-2">
          <label>Usuario</label>
          <input type="text" name="user_name" value="<?= View::e($filters['user_name'] ?? '') ?>" class="form-control">
        </div>
      </div>
      <div class="form-row">
        <div class="col-md-6 mb-2">
          <label>Buscar</label>
          <input type="text" name="q" value="<?= View::e($filters['q'] ?? '') ?>" placeholder="Buscar en cambios, IP o agente" class="form-control">
        </div>
        <div class="col-md-6 mb-2 d-flex align-items-end justify-content-end">
          <button class="btn btn-primary mr-2" type="submit"><i class="fas fa-search mr-1"></i> Filtrar</button>
          <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/movements"><i class="fas fa-undo mr-1"></i> Limpiar</a>
        </div>
      </div>
    </form>

    <?php if (empty($rows)): ?>
      <div class="ps-empty-state">
        <div class="box">
          <div class="title">Sin movimientos</div>
          <div class="desc">No hay registros para los filtros seleccionados.</div>
        </div>
      </div>
    <?php else: ?>
    <div class="table-responsive" id="psMovementsTableWrap">
      <table class="table table-sm table-striped" id="psMovementsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Entidad</th>
            <th>Acción</th>
            <th>Entidad ID</th>
            <th>Cambios</th>
            <th>IP</th>
            <th>Agente</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
              <?php
                $entityRaw = (string)($r['entity'] ?? '');
                $actionRaw = (string)($r['action'] ?? '');
                $mapEntity = ['product' => 'Producto', 'sale' => 'Venta', 'user' => 'Usuario'];
                // Pasados en español (más naturales para "Historial")
                $mapAction = ['create' => 'Ingresado', 'update' => 'Actualizado', 'delete' => 'Eliminado', 'retire' => 'Retirado', 'reactivate' => 'Reactivado', 'sell' => 'Vendido'];
                $entityLabel = $mapEntity[$entityRaw] ?? $entityRaw;
                $actionLabel = $mapAction[$actionRaw] ?? $actionRaw;
              ?>
            <tr class="ps-row ps-entity-<?= View::e($entityRaw) ?> ps-action-<?= View::e($actionRaw) ?>">
              <td><?= (int)($r['id'] ?? 0) ?></td>
              <td><?= View::e($r['created_at'] ?? '') ?></td>
              <td><?= View::e(trim(($r['user_name'] ?? '') . (($r['user_id'] ?? '') ? (' (#' . $r['user_id'] . ')') : ''))) ?></td>
              <td><span class="badge badge-info"><?= View::e($entityLabel) ?></span></td>
              <td><span class="badge badge-secondary"><?= View::e($actionLabel) ?></span></td>
              <td><?= View::e($r['entity_id'] ?? '') ?></td>
              <?php
                $raw = (string)($r['changes_json'] ?? '');
                $friendly = '';
                $titleAttr = $raw;
                $viewUrl = '';
                $viewText = '';
                $viewPid = 0;
                $viewSaleId = 0;
                if ($raw !== '') {
                  $arr = json_decode($raw, true);
                  if (json_last_error() === JSON_ERROR_NONE && is_array($arr)) {
                    // Prefer explicit summary
                    if (!empty($arr['summary'])) { $friendly = (string)$arr['summary']; }
                    // Build richer message y acciones contextuales
                    $ent = $entityRaw;
                    $act = $actionRaw;
                    $pid = (int)($r['entity_id'] ?? 0);
                    $nm = '';
                    if (array_key_exists('name', $arr) && !is_array($arr['name'])) {
                      $nm = trim((string)$arr['name']);
                    }
                    $sku = '';
                    if (array_key_exists('sku', $arr) && !is_array($arr['sku'])) {
                      $sku = trim((string)$arr['sku']);
                    }
                    // Ventas: descripción amigable + botón "Ver venta"
                    if ($ent === 'sale') {
                      $sid = (int)($r['entity_id'] ?? 0);
                      if ($sid > 0) {
                        $viewSaleId = $sid;
                        $viewText = 'Ver venta';
                        // Construir texto en español y específico
                        $cust = '';
                        if (isset($arr['customer_name']) && !is_array($arr['customer_name'])) {
                          $cust = trim((string)$arr['customer_name']);
                        } elseif (isset($arr['customer']) && !is_array($arr['customer'])) {
                          $cust = trim((string)$arr['customer']);
                        }
                        $att = '';
                        if (isset($arr['user_name']) && !is_array($arr['user_name'])) {
                          $att = trim((string)$arr['user_name']);
                        } elseif (isset($arr['attended_by']) && !is_array($arr['attended_by'])) {
                          $att = trim((string)$arr['attended_by']);
                        }
                        $itemsCount = null;
                        if (!empty($arr['items']) && is_array($arr['items'])) {
                          $itemsCount = count($arr['items']);
                        } elseif (isset($arr['items_count']) && !is_array($arr['items_count'])) {
                          $itemsCount = (int)$arr['items_count'];
                        }
                        $total = null;
                        if (isset($arr['total']) && !is_array($arr['total'])) { $total = (float)$arr['total']; }
                        elseif (isset($arr['amount']) && !is_array($arr['amount'])) { $total = (float)$arr['amount']; }
                        // Fallback si viene before/after con totales
                        if ($total === null && isset($arr['after']['total'])) { $total = (float)$arr['after']['total']; }
                        // Mensajes según acción
                        if ($act === 'create' || $act === 'sell') {
                          $parts = [];
                          $parts[] = 'Se realizó la venta #' . $sid;
                          if ($itemsCount !== null) $parts[] = 'con ' . $itemsCount . ' item' . ($itemsCount===1?'':'s');
                          if ($total !== null) $parts[] = 'por un total de $' . number_format($total,0,',','.');
                          if ($cust !== '') $parts[] = 'al cliente ' . $cust;
                          if ($att !== '') $parts[] = '(atendida por ' . $att . ')';
                          $friendly = implode(' ', $parts);
                        } elseif ($act === 'update') {
                          $parts = [];
                          $parts[] = 'Se actualizó la venta #' . $sid;
                          if ($total !== null) $parts[] = '(total: $' . number_format($total,0,',','.') . ')';
                          if ($itemsCount !== null) $parts[] = '· ' . $itemsCount . ' items';
                          if ($cust !== '') $parts[] = '· cliente: ' . $cust;
                          $friendly = implode(' ', $parts);
                        } elseif ($act === 'delete') {
                          $friendly = 'Se eliminó la venta #' . $sid;
                        } else {
                          if ($friendly === '') {
                            $friendly = 'Se registró la venta #' . $sid;
                          }
                        }
                      }
                    }
                    if ($ent === 'product') {
                      if ($act === 'create') {
                        if ($nm !== '' || $sku !== '') {
                          $friendly = 'Se creó el producto: ' . ($nm !== '' ? $nm : ('#' . $pid)) . ($sku !== '' ? (' (SKU ' . $sku . ')') : '');
                        } else if ($friendly === '') {
                          $friendly = 'Se creó el producto #' . $pid;
                        }
                        $viewPid = $pid;
                        $viewText = 'Ver producto';
                      } elseif ($act === 'update') {
                        // List changed keys if available
                        $changed = [];
                        if (isset($arr['before']) && isset($arr['after']) && is_array($arr['before']) && is_array($arr['after'])) {
                          foreach ($arr['after'] as $k=>$v) {
                            if (!array_key_exists($k, $arr['before'])) continue;
                            if ($arr['before'][$k] !== $v) $changed[] = $k;
                          }
                        } else {
                          $changed = array_values(array_filter(array_keys($arr), static function($k){ return !in_array($k,['summary','before','after'], true); }));
                        }
                        $chgTxt = !empty($changed) ? (' (cambios: ' . implode(', ', $changed) . ')') : '';
                        $friendly = 'Se actualizó el producto: ' . ($nm !== '' ? $nm : ('#' . $pid)) . $chgTxt;
                        $viewPid = $pid;
                        $viewText = 'Ver producto';
                      } elseif ($act === 'delete') {
                        $friendly = 'Se eliminó el producto: ' . ($nm !== '' ? $nm : ('#' . $pid)) . ($sku !== '' ? (' (SKU ' . $sku . ')') : '');
                        // Producto ya no existe: enlazar a listado con búsqueda por nombre o SKU
                        $q = $nm !== '' ? $nm : $sku;
                        $viewUrl = rtrim(BASE_URL,'/') . '/products?q=' . urlencode($q);
                        $viewText = 'Buscar en productos';
                      } elseif ($act === 'retire') {
                        $friendly = 'Se retiró el producto: ' . ($nm !== '' ? $nm : ('#' . $pid));
                        $viewPid = $pid;
                        $viewText = 'Ver producto';
                      } elseif ($act === 'reactivate') {
                        $friendly = 'Se reactivó el producto: ' . ($nm !== '' ? $nm : ('#' . $pid));
                        $viewPid = $pid;
                        $viewText = 'Ver producto';
                      }
                    }
                    // Tooltip: pretty one-line JSON
                    $titleAttr = json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                  }
                }
                $friendly = trim($friendly !== '' ? $friendly : ($raw !== '' ? $raw : ''));
              ?>
              <td class="small changes-cell" data-json='<?= View::e($raw) ?>' style="cursor:pointer; max-width:460px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="Click para ver detalles">
                <div class="text-truncate" style="max-width:460px;">
                  <?= View::e($friendly) ?>
                  <?php if ($viewPid && $viewText): ?>
                    · <button type="button" class="btn btn-sm ps-btn-view align-baseline py-0 px-2 ps-view-product" data-id="<?= (int)$viewPid ?>"><?= View::e($viewText) ?></button>
                  <?php elseif ($viewSaleId && $viewText): ?>
                    · <button type="button" class="btn btn-sm ps-btn-view align-baseline py-0 px-2 ps-view-sale" data-id="<?= (int)$viewSaleId ?>"><?= View::e($viewText) ?></button>
                  <?php elseif ($viewUrl && $viewText): ?>
                    · <a href="<?= View::e($viewUrl) ?>" class="small"><?= View::e($viewText) ?></a>
                  <?php endif; ?>
                </div>
              </td>
              <td><?= View::e($r['ip'] ?? '') ?></td>
              <td class="small" style="max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= View::e($r['user_agent'] ?? '') ?>"><?= View::e($r['user_agent'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php $p = $pagination ?? null; if ($p): $page=(int)$p['page']; $pages=(int)$p['pages']; $per=(int)$p['per']; $total=(int)$p['total']; ?>
      <nav aria-label="Paginación" class="mt-2">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= View::e($_SERVER['REQUEST_URI']) ?>&page=<?= max(1,$page-1) ?>">&laquo;</a></li>
          <li class="page-item disabled"><span class="page-link">Página <?= $page ?> de <?= $pages ?> (<?= $total ?>)</span></li>
          <li class="page-item <?= $page>=$pages?'disabled':'' ?>"><a class="page-link" href="<?= View::e($_SERVER['REQUEST_URI']) ?>&page=<?= min($pages,$page+1) ?>">&raquo;</a></li>
        </ul>
      </nav>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Estilos de impresión para exportar PDF (mostrar solo la tabla; y solo filas del tab activo) -->
<style media="print">
  body * { visibility: hidden; }
  #psMovementsTableWrap, #psMovementsTableWrap * { visibility: visible; }
  #psMovementsTableWrap { position: absolute; left: 0; top: 0; width: 100%; }
  .nav, .pagination, .modal, .card-header, form, .btn, .alert { display: none !important; }
  /* Mostrar solo filas del scope actual */
  body.ps-print-scope-product #psMovementsTableWrap tbody tr { display: none !important; }
  body.ps-print-scope-product #psMovementsTableWrap tbody tr.ps-entity-product { display: table-row !important; }
  body.ps-print-scope-sale #psMovementsTableWrap tbody tr { display: none !important; }
  body.ps-print-scope-sale #psMovementsTableWrap tbody tr.ps-entity-sale { display: table-row !important; }
  body.ps-print-scope-delete #psMovementsTableWrap tbody tr { display: none !important; }
  body.ps-print-scope-delete #psMovementsTableWrap tbody tr.ps-action-delete { display: table-row !important; }
  body.ps-print-scope-retire #psMovementsTableWrap tbody tr { display: none !important; }
  body.ps-print-scope-retire #psMovementsTableWrap tbody tr.ps-entity-product.ps-action-retire { display: table-row !important; }
</style>

<!-- Modal para mensajes de validación -->
<div class="modal fade" id="psValidationModal" tabindex="-1" role="dialog" aria-labelledby="psValidationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="psValidationModalLabel">Validación de filtros</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="psValidationContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
      </div>
    </div>
  </div>
 </div>

<!-- Modal para ver Cambios (JSON pretty) -->
<div class="modal fade" id="psChangesModal" tabindex="-1" role="dialog" aria-labelledby="psChangesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="psChangesModalLabel">Detalles de cambios</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <pre id="psChangesPre" class="mb-0" style="white-space:pre-wrap"></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para ver detalle de Producto -->
<div class="modal fade" id="psProductModal" tabindex="-1" role="dialog" aria-labelledby="psProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="psProductModalLabel">Detalle de producto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="psProductLoading" class="text-center my-3" style="display:none"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
        <div id="psProductError" class="alert alert-danger" style="display:none"></div>
        <div id="psProductContent" style="display:none">
          <div class="media">
            <img id="psProdImg" src="" alt="" class="mr-3 rounded" style="width:96px;height:96px;object-fit:cover;display:none">
            <div class="media-body">
              <h5 class="mt-0" id="psProdName"></h5>
              <div class="small text-muted" id="psProdSku"></div>
              <div class="mt-2" id="psProdDesc"></div>
              <div class="mt-2">
                <span class="badge badge-info" id="psProdStatus"></span>
                <span class="badge badge-secondary" id="psProdStock"></span>
                <span class="badge badge-success" id="psProdPrice"></span>
                <span class="badge badge-warning" id="psProdExpiry" style="display:none"></span>
              </div>
              <div class="mt-2" id="psProdChangesBadges" style="display:none">
                <span class="badge badge-danger mr-1" id="psChName" style="display:none">Nombre actualizado</span>
                <span class="badge badge-danger mr-1" id="psChSku" style="display:none">SKU actualizado</span>
                <span class="badge badge-danger mr-1" id="psChDesc" style="display:none">Descripción actualizada</span>
                <span class="badge badge-danger mr-1" id="psChStatus" style="display:none">Estado actualizado</span>
                <span class="badge badge-danger mr-1" id="psChStock" style="display:none">Stock actualizado</span>
                <span class="badge badge-danger mr-1" id="psChPrice" style="display:none">Precio actualizado</span>
                <span class="badge badge-danger mr-1" id="psChExpiry" style="display:none">Vencimiento actualizado</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
  </div>

<!-- Estilos y Modal para ver detalle de Venta (polished) -->
<style>
  #psSaleModal .modal-header { background: linear-gradient(135deg, #0d6efd, #6610f2); color:#fff; }
  #psSaleModal .modal-title { font-weight:600; }
  #psSaleModal .mono { font-variant-numeric: tabular-nums; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  .ps-chip { display:inline-flex; align-items:center; padding:2px 8px; border-radius:999px; font-size:12px; background:#f1f3f5; color:#495057; margin-right:6px; }
  .ps-chip .ico { margin-right:6px; opacity:.8; }
  .ps-summary { display:flex; gap:8px; flex-wrap:wrap; margin:6px 0 10px; }
  .ps-summary .ps-chip.total { background:#e7f5ff; color:#0b7285; }
  .ps-summary .ps-chip.items { background:#fff4e6; color:#d9480f; }
  #psSaleModal .table thead th { background:#f8f9fa; border-top:0; }
  #psSaleModal .table tfoot th { background:#f8f9fa; }
  #psSaleModal .muted { color:#6c757d; }
  #psSaleModal .section-title { font-size:14px; font-weight:600; color:#495057; margin:8px 0 6px; text-transform:uppercase; letter-spacing:.02em; }
  #psSaleModal .kv { font-size:13px; }
  #psSaleModal .kv strong { color:#343a40; }
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
(function(){
  var form = document.getElementById('movementsFilterForm');
  if (!form) return;
  function isDigits(v){ return /^\d+$/.test(v.trim()); }
  form.addEventListener('submit', function(ev){
    var errs = [];
    var id = form.id.value||'';
    var idFrom = form.id_from.value||'';
    var idTo = form.id_to.value||'';
    var userId = form.user_id.value||'';
    if (id && !isDigits(id)) errs.push('El campo ID debe ser un número entero positivo.');
    if (idFrom && !isDigits(idFrom)) errs.push('El campo Desde ID debe ser un número entero positivo.');
    if (idTo && !isDigits(idTo)) errs.push('El campo Hasta ID debe ser un número entero positivo.');
    if (idFrom && idTo && parseInt(idFrom,10) > parseInt(idTo,10)) errs.push('El rango de ID no es válido: "Desde ID" no puede ser mayor que "Hasta ID".');
    if (userId && !isDigits(userId)) errs.push('User ID debe ser numérico (entero positivo).');
    if (errs.length){
      ev.preventDefault();
      var html = '<ul class="mb-0">'+errs.map(function(e){return '<li>'+e.replace(/</g,'&lt;')+'</li>';}).join('')+'</ul>';
      var el = document.getElementById('psValidationContent'); if (el) el.innerHTML = html;
      if (window.jQuery && typeof jQuery.fn.modal === 'function') {
        jQuery('#psValidationModal').modal('show');
      } else if (window.notify) {
        notify({icon:'error', title:'Revisa los filtros', html: html, toast:false});
      } else {
        alert(errs.join('\n'));
      }
      return false;
    }
  }, false);
})();

(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var btnPdf = document.getElementById('btnMovementsExportPdf');
    if (!btnPdf) return;
    btnPdf.addEventListener('click', function(){
      try {
        var scope = btnPdf.getAttribute('data-scope') || '';
        var cls = '';
        if (scope === 'product') cls = 'ps-print-scope-product';
        else if (scope === 'sale') cls = 'ps-print-scope-sale';
        else if (scope === 'delete') cls = 'ps-print-scope-delete';
        else if (scope === 'retire') cls = 'ps-print-scope-retire';
        if (cls) document.body.classList.add(cls);
        var cleanup = function(){ if (cls) document.body.classList.remove(cls); };
        if (window.matchMedia) {
          var mql = window.matchMedia('print');
          var onChange = function(e){ if (!e.matches) { cleanup(); mql.removeListener(onChange); } };
          mql.addListener(onChange);
        }
        window.onafterprint = function(){ cleanup(); window.onafterprint = null; };
        window.print();
      } catch(_) {}
    });
  });
})();

(function(){
  var tbody = document.querySelector('table tbody');
  if (!tbody) return;
  // Al hacer click en la celda "Cambios":
  // - Si es una venta y existe el botón "Ver venta", abrimos ese modal.
  // - Para cualquier otro caso, NO abrimos nada (se elimina el modal de JSON).
  var changesHandler = function(e){
    var td = e.target.closest('.changes-cell');
    if (!td) return;
    if (e.target.closest('.ps-view-product, .ps-view-sale')) return; // clicks directos en botones mantienen su comportamiento
    var saleBtn = td.querySelector('.ps-view-sale');
    if (saleBtn) { saleBtn.click(); }
    // Caso contrario: no hacer nada (sin modal JSON)
  };
  tbody.addEventListener('click', changesHandler, false);
})();

(function(){
  function q(sel){ return document.querySelector(sel); }
  function show(el, on){ if (!el) return; el.style.display = on ? '' : 'none'; }
  function setText(id, t){ var el = document.getElementById(id); if (el) el.textContent = t||''; }
  function formatMoney(n){ try { return new Intl.NumberFormat('es-CL', { style:'currency', currency:'CLP', maximumFractionDigits:0 }).format(n||0); } catch(_){ return '$' + String(n||0); } }
  function setBadge(id, t){ setText(id, t); }
  function setImg(id, url){ var el = document.getElementById(id); if (!el) return; if (url){ el.src = url; el.style.display=''; } else { el.removeAttribute('src'); el.style.display='none'; } }
  function openModalById(id){
    var m = (window.jQuery && typeof jQuery.fn.modal==='function');
    if (m) { jQuery('#'+id).modal('show'); return; }
    var el = document.getElementById(id); if (!el) return;
    el.classList.add('show'); el.style.display='block'; el.removeAttribute('aria-hidden'); el.setAttribute('aria-modal','true');
    // simple backdrop
    var bd = document.createElement('div'); bd.className='modal-backdrop fade show'; bd.id=id+'__backdrop'; document.body.appendChild(bd);
    // close handlers
    el.querySelectorAll('[data-dismiss="modal"], .close').forEach(function(btn){ btn.addEventListener('click', function(){ closeModalById(id); }, {once:true}); });
  }
  function closeModalById(id){
    var el = document.getElementById(id); if (!el) return;
    el.classList.remove('show'); el.style.display='none'; el.setAttribute('aria-hidden','true'); el.removeAttribute('aria-modal');
    var bd = document.getElementById(id+'__backdrop'); if (bd) bd.parentNode.removeChild(bd);
  }
  var tbody = document.querySelector('table tbody');
  if (!tbody) return;
  tbody.addEventListener('click', function(e){
    var btn = e.target.closest('.ps-view-product');
    if (!btn) return;
    var id = parseInt(btn.getAttribute('data-id')||'0',10);
    if (!id) return;
    var loading = document.getElementById('psProductLoading');
    var err = document.getElementById('psProductError');
    var content = document.getElementById('psProductContent');
    show(loading, true); show(err, false); show(content, false);
    openModalById('psProductModal');
    // Determinar cambios de la fila (para badges rojos)
    var tr = btn.closest('tr');
    var changesRaw = '';
    if (tr) {
      var ctd = tr.querySelector('.changes-cell');
      if (ctd) changesRaw = ctd.getAttribute('data-json') || '';
    }
    var changedKeys = {};
    try {
      var cobj = JSON.parse(changesRaw);
      if (cobj && typeof cobj === 'object') {
        if (cobj.before && cobj.after && typeof cobj.before==='object' && typeof cobj.after==='object') {
          Object.keys(cobj.after).forEach(function(k){ if (k in cobj.before && cobj.before[k] !== cobj.after[k]) changedKeys[k]=true; });
        } else {
          Object.keys(cobj).forEach(function(k){ if (['summary','before','after'].indexOf(k)===-1) changedKeys[k]=true; });
        }
      }
    } catch(_){ }
    // Reset badges
    ['psChName','psChSku','psChDesc','psChStatus','psChStock','psChPrice','psChExpiry'].forEach(function(id){ var el=document.getElementById(id); if (el){ el.style.display='none'; } });
    var chWrap = document.getElementById('psProdChangesBadges'); if (chWrap) chWrap.style.display='none';
    fetch((window.BASE_URL||'<?= rtrim(BASE_URL,'/') ?>') + '/products/show/' + id, { headers: { 'Accept':'application/json' }})
      .then(function(r){ if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(data){
        show(loading, false);
        if (!data || !data.ok){ err.textContent = (data && data.message) ? data.message : 'No se pudo cargar el producto.'; show(err,true); return; }
        var p = data.product || {};
        setText('psProdName', p.name || ('Producto #' + (p.id||id)));
        setText('psProdSku', p.sku ? ('SKU: ' + p.sku) : '');
        setText('psProdDesc', p.description || '');
        setImg('psProdImg', p.image || '');
        setBadge('psProdStatus', p.status ? ('Estado: ' + p.status) : '');
        setBadge('psProdStock', (typeof p.stock!=='undefined') ? ('Stock: ' + p.stock) : '');
        setBadge('psProdPrice', (typeof p.price!=='undefined') ? ('Precio: ' + p.price) : '');
        if (p.expires_at){ var ex = document.getElementById('psProdExpiry'); if (ex){ ex.textContent = 'Vence: ' + p.expires_at; ex.style.display=''; } }
        // Mostrar badges de cambios
        var any=false;
        if (changedKeys['name']) { var el=document.getElementById('psChName'); if (el){ el.style.display='inline-block'; any=true; } }
        if (changedKeys['sku']) { var el=document.getElementById('psChSku'); if (el){ el.style.display='inline-block'; any=true; } }
        if (changedKeys['description']) { var el=document.getElementById('psChDesc'); if (el){ el.style.display='inline-block'; any=true; } }
        if (changedKeys['status']) { var el=document.getElementById('psChStatus'); if (el){ el.style.display='inline-block'; any=true; } }
        if (changedKeys['stock']) { var el=document.getElementById('psChStock'); if (el){ el.style.display='inline-block'; any=true; } }
        if (changedKeys['price'] || changedKeys['unit_price']) { var el=document.getElementById('psChPrice'); if (el){ el.style.display='inline-block'; any=true; } }
        if (changedKeys['expires_at'] || changedKeys['expiry'] || changedKeys['expiration']) { var el=document.getElementById('psChExpiry'); if (el){ el.style.display='inline-block'; any=true; } }
        if (any && chWrap) chWrap.style.display='';
        show(content, true);
      })
      .catch(function(e){ show(loading,false); err.textContent = 'Error al cargar: ' + e.message; show(err,true); });
  }, false);

  // Ventas: ver detalle
  tbody.addEventListener('click', function(e){
    var btn = e.target.closest('.ps-view-sale');
    if (!btn) return;
    var id = parseInt(btn.getAttribute('data-id')||'0',10);
    if (!id) return;
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
        var cust = (s.customer_name||'') || 'N/D';
        setText('psSaleCustomer', cust);
        var contact = [];
        if (s.customer_phone) contact.push(s.customer_phone);
        if (s.customer_email) contact.push(s.customer_email);
        setText('psSaleContact', contact.join(' · '));
        var attended = ((s.user_name||'') + (s.user_role?(' ('+s.user_role+')'):'')).trim();
        setText('psSaleUser', attended);
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
})();
</script>
