<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header">
    <?php $isAdmin = \App\Helpers\Auth::isAdmin(); $isRetired = !empty($retired); ?>
    <form class="form-inline" method="get" action="<?= BASE_URL ?><?= $isRetired ? '/products/retired' : '/products' ?>">
      <div class="input-group">
        <input type="text" class="form-control" name="q" value="<?= View::e($q ?? '') ?>" placeholder="Buscar por nombre o SKU">
        <div class="input-group-append">
          <button class="btn btn-outline-secondary"><i class="fas fa-search mr-1" aria-hidden="true"></i> Buscar</button>
        </div>
      </div>
      <?php if ($isAdmin && !$isRetired): ?>
      <a class="btn btn-primary ml-2" href="<?= BASE_URL ?>/products/create"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Nuevo</a>
      <a class="btn btn-outline-secondary ml-2" href="<?= BASE_URL ?>/products/retired"><i class="fas fa-archive mr-1" aria-hidden="true"></i> Retirados</a>
      <?php endif; ?>
      <?php if ($isRetired): ?>
      <a class="btn btn-outline-secondary ml-2" href="<?= BASE_URL ?>/products"><i class="fas fa-check mr-1" aria-hidden="true"></i> Activos</a>
      <?php endif; ?>
      <div class="ml-3">
        <label class="mr-2 mb-0">Vencimiento:</label>
        <select id="expiryFilter" class="form-control">
          <option value="all">Todos</option>
          <option value="30">≤ 30 días</option>
          <option value="60">≤ 60 días</option>
        </select>
      </div>
      <div class="ml-3 d-none d-md-flex align-items-center">
        <span class="mr-2 small text-muted">Leyenda stock:</span>
        <span class="badge badge-danger mr-1">0–<?= (int)(defined('STOCK_DANGER') ? STOCK_DANGER : 20) ?></span>
        <span class="badge badge-warning mr-1"><?= (int)((defined('STOCK_DANGER') ? STOCK_DANGER : 20) + 1) ?>–<?= (int)(defined('STOCK_WARN') ? STOCK_WARN : 60) ?></span>
        <span class="badge badge-success">≥ <?= (int)((defined('STOCK_WARN') ? STOCK_WARN : 60) + 1) ?></span>
      </div>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>SKU</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Caducidad</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php
          $now = time();
          $soonCount = 0; // 1-30 días
          $midCount = 0;  // 31-60 días
          $okCount  = 0;  // 61+
          $expiredCount = 0; // < 0 días
          $lowStockCount = 0;
          $thr = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5;
          $STOCK_DANGER = defined('STOCK_DANGER') ? (int)STOCK_DANGER : 20;
          $STOCK_WARN = defined('STOCK_WARN') ? (int)STOCK_WARN : 60;
        ?>
        <?php foreach (($products ?? []) as $p): ?>
          <?php
            $stock = (int)($p['stock'] ?? 0);
            if ($stock > 0 && $stock <= $thr) { $lowStockCount++; }
            $days = null;
            if (!empty($p['expires_at'])) {
              $ts = strtotime($p['expires_at']);
              if ($ts !== false) {
                $days = (int)floor(($ts - $now) / 86400);
                if ($days < 0) { $expiredCount++; /* expirado */ }
                elseif ($days <= 30) $soonCount++;
                elseif ($days <= 60) $midCount++;
                else $okCount++;
              }
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
            // Resaltar fila por caducidad: expirado en rojo, <=30 días en amarillo
            $rowClass = '';
            if ($days !== null) {
              if ($days < 0) { $rowClass = 'table-danger'; }
              elseif ($days <= 30) { $rowClass = 'table-warning'; }
            }
          ?>
          <tr class="<?= $rowClass ?>" data-days="<?= $days === null ? '' : $days ?>">
            <td><?= View::e($p['display_no'] ?? $p['id']) ?></td>
            <td><?= View::e($p['sku']) ?></td>
            <td>
              <?php if (!empty($p['image'])): ?>
                <img src="<?= BASE_URL ?>/uploads/<?= View::e($p['image']) ?>"
                     alt="Imagen"
                     style="width:72px;height:72px;object-fit:cover;border-radius:50%;border:1px solid #ddd;margin-right:12px;vertical-align:middle;">
              <?php endif; ?>
              <span><?= View::e($p['name']) ?></span>
            </td>
            <td>$<?= number_format((float)($p['price'] ?? 0), 2) ?></td>
            <td>
              <span class="badge badge-<?= $badgeClass ?>" title="<?= !empty($p['expires_at']) ? ('Vence: ' . View::e($p['expires_at'])) : '' ?>"><?= $badgeIconHtml ?><span><?= View::e($stock) ?></span></span>
            </td>
            <td>
              <?php if ($days === null): ?>
                —
              <?php else: ?>
                <?php if ($days < 0): ?>
                  <?= View::e($p['expires_at']) ?> (Vencido)
                <?php else: ?>
                  <?= View::e($p['expires_at']) ?> (<?= $days ?> d)
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td>
              <?php $st = strtolower($p['status'] ?? ''); $isActiveSt = ($st === 'active' || $st === 'activo'); ?>
              <span class="badge badge-<?= $isActiveSt ? 'success' : 'secondary' ?>"><?= $isActiveSt ? 'Activo' : 'Inactivo' ?></span>
            </td>
            <td>
              <button type="button"
                      class="btn btn-sm btn-info btnViewProduct"
                      data-id="<?= (int)$p['id'] ?>"
                      data-sku="<?= View::e($p['sku'] ?? '') ?>"
                      data-name="<?= View::e($p['name'] ?? '') ?>"
                      data-description="<?= View::e($p['description'] ?? '') ?>"
                      data-stock="<?= View::e($p['stock'] ?? 0) ?>"
                      data-price="<?= number_format((float)($p['price'] ?? 0), 2, '.', '') ?>"
                      data-expires_at="<?= View::e($p['expires_at'] ?? '') ?>"
                      data-status="<?= View::e($p['status'] ?? '') ?>">
                <i class="fas fa-eye mr-1" aria-hidden="true"></i> Ver
              </button>
              <?php if ($isAdmin): ?>
              <?php if ($isRetired): ?>
                <form method="post" action="<?= BASE_URL ?>/products/reactivate/<?= View::e($p['id']) ?>" style="display:inline">
                  <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
                  <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo-alt mr-1" aria-hidden="true"></i> Reactivar</button>
                </form>
              <?php else: ?>
                <a class="btn btn-sm btn-warning" href="<?= BASE_URL ?>/products/edit/<?= View::e($p['id']) ?>"><i class="fas fa-edit mr-1" aria-hidden="true"></i> Editar</a>
                <form method="post" action="<?= BASE_URL ?>/products/delete/<?= View::e($p['id']) ?>" style="display:inline">
                  <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
                  <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt mr-1" aria-hidden="true"></i> Eliminar</button>
                </form>
              <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($pagination) && is_array($pagination)): ?>
  <?php
    $pg = $pagination; $page = (int)($pg['page'] ?? 1); $pages = (int)($pg['pages'] ?? 1);
    $per = (int)($pg['per'] ?? 15); $total = (int)($pg['total'] ?? 0);
    $qParam = isset($q) && $q !== '' ? ('&q=' . urlencode($q)) : '';
    $base = BASE_URL . ((isset($retired) && $retired) ? '/products/retired' : (isset($title) && strpos($title,'vencid')!==false ? '/products/expired' : '/products'));
    function pageUrl($base,$p,$per,$q){ return $base . '?page=' . max(1,(int)$p) . '&per=' . (int)$per . $q; }
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

<script>
  document.addEventListener('DOMContentLoaded', function(){
    // Modal de detalles
    function createDetailModal(){
      if (document.getElementById('productDetailModal')) return;
      var wrap = document.createElement('div');
      wrap.innerHTML = `
      <div class="modal fade" id="productDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fas fa-info-circle mr-2" aria-hidden="true"></i> Detalles del producto</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <dl class="row mb-2">
                    <dt class="col-sm-4">ID</dt><dd class="col-sm-8" id="pd-id">—</dd>
                    <dt class="col-sm-4">SKU</dt><dd class="col-sm-8" id="pd-sku">—</dd>
                    <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8" id="pd-name">—</dd>
                  </dl>
                </div>
                <div class="col-md-6">
                  <dl class="row mb-2">
                    <dt class="col-sm-4">Precio</dt><dd class="col-sm-8" id="pd-price">—</dd>
                    <dt class="col-sm-4">Stock</dt><dd class="col-sm-8" id="pd-stock">—</dd>
                    <dt class="col-sm-4">Caducidad</dt><dd class="col-sm-8" id="pd-exp">—</dd>
                    <dt class="col-sm-4">Estado</dt><dd class="col-sm-8" id="pd-status">—</dd>
                  </dl>
                </div>
              </div>
              <div class="mt-2">
                <h6 class="font-weight-bold">Descripción</h6>
                <div id="pd-desc">—</div>
              </div>
              
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>`;
      document.body.appendChild(wrap.firstElementChild);
    }
    // Crear al cargar
    createDetailModal();

    function fmtCurrency(n){ try{ return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(parseFloat(n||0)); }catch(e){ return '$' + (parseFloat(n||0)).toFixed(2); } }
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
      m.querySelector('#pd-id').textContent = btn.getAttribute('data-id') || '—';
      m.querySelector('#pd-sku').textContent = btn.getAttribute('data-sku') || '—';
      m.querySelector('#pd-name').textContent = btn.getAttribute('data-name') || '—';
      m.querySelector('#pd-price').textContent = fmtCurrency(btn.getAttribute('data-price'));
      m.querySelector('#pd-stock').textContent = btn.getAttribute('data-stock') || '0';
      var exp = btn.getAttribute('data-expires_at') || '';
      m.querySelector('#pd-exp').textContent = exp !== '' ? exp : '—';
      var st = btn.getAttribute('data-status') || '';
      m.querySelector('#pd-status').textContent = st ? (st.toLowerCase()==='active'?'Activo':(st.toLowerCase()==='retired'?'Retirado':st)) : '—';
      var desc = btn.getAttribute('data-description') || '';
      m.querySelector('#pd-desc').textContent = desc !== '' ? desc : 'Sin descripción';
      // No tabla adicional: solo campos principales y descripción
      // Mostrar modal
      if (window.jQuery && jQuery.fn && jQuery.fn.modal) { jQuery(m).modal('show'); }
      else { showModalFallback(m); }
    }
    // Delegación para asegurar que siempre capture clics
    document.addEventListener('click', function(ev){
      var t = ev.target;
      if (!t) return;
      if (t.classList && t.classList.contains('btnViewProduct')) {
        ev.preventDefault();
        try { console.debug('[Productos] abrir detalles', {id: t.getAttribute('data-id')}); } catch(_){ }
        openDetails(t);
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
            show = days !== null && days <= 30;
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
  });
</script>
