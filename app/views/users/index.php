<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-users mr-2 text-primary" aria-hidden="true"></i> Usuarios</h3>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary"><i class="fas fa-user-plus mr-1" aria-hidden="true"></i> Nuevo</a>
  </div>
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= View::e($u['id']) ?></td>
          <td><?= View::e($u['name']) ?></td>
          <td><?= View::e($u['email']) ?></td>
          <td><?= View::e($u['role']) ?></td>
          <td>
            <a href="<?= BASE_URL ?>/users/edit/<?= View::e($u['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit mr-1" aria-hidden="true"></i> Editar</a>
            <form method="post" action="<?= BASE_URL ?>/users/delete/<?= View::e($u['id']) ?>" style="display:inline" class="js-confirmable" data-confirm-title="Eliminar usuario" data-confirm-text="¿Seguro que deseas eliminar a ‘<?= View::e($u['name']) ?>’? Esta acción no se puede deshacer." data-confirm-ok="Eliminar">
              <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
              <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt mr-1" aria-hidden="true"></i> Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Confirmation handled globally by public/js/confirm-modal.js -->
