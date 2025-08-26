<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-tags mr-2 text-primary"></i> Categorías</h3>
    <a href="<?= BASE_URL ?>/categories/create" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Nueva</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <?php if (empty($categories)): ?>
        <div class="ps-empty-state" role="status" aria-live="polite">
          <div class="box">
            <div class="title"><i class="fas fa-tags mr-2" aria-hidden="true"></i> No hay categorías</div>
            <div class="desc">Crea una nueva categoría para comenzar.</div>
          </div>
        </div>
        <script>
          (function(){
            function showEmptyCategories(){
              try {
                var title = 'No hay categorías';
                var text = 'Crea una nueva categoría para comenzar.';
                if (window.Swal && typeof Swal.fire === 'function') {
                  Swal.fire({ icon:'info', title:title, text:text, confirmButtonText:'Entendido', allowOutsideClick:true, allowEscapeKey:true });
                } else if (window.notify) {
                  notify({ icon:'info', title:title, text:text, position:'center' });
                } else { alert(title); }
              } catch(_){}
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showEmptyCategories);
            else showEmptyCategories();
          })();
        </script>
      <?php else: ?>
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach ($categories as $c): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= View::e($c['name'] ?? '') ?></td>
              <td class="text-right">
                <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/categories/edit/<?= (int)$c['id'] ?>"><i class="fas fa-edit"></i></a>
                <form method="post" action="<?= BASE_URL ?>/categories/delete/<?= (int)$c['id'] ?>" style="display:inline;" onsubmit="return window.confirmAction ? window.confirmAction({title:'Eliminar categoría', text:'Esta acción no se puede deshacer'}) : confirm('¿Eliminar categoría?');">
                  <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
