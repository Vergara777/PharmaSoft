<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-truck mr-2 text-primary"></i> Editar proveedor</h3></div>
  <div class="card-body">
    <form method="post" action="<?= BASE_URL ?>/suppliers/update/<?= (int)$s['id'] ?>">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-group"><label>Nombre</label><input name="name" class="form-control" value="<?= View::e($s['name']) ?>" required></div>
      <div class="form-row">
        <div class="form-group col-md-6"><label>Teléfono</label><input name="phone" class="form-control" value="<?= View::e($s['phone'] ?? '') ?>"></div>
        <div class="form-group col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="<?= View::e($s['email'] ?? '') ?>"></div>
      </div>
      <div class="text-right">
        <a href="<?= BASE_URL ?>/suppliers" class="btn btn-secondary">Cancelar</a>
        <button class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
