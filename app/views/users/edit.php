<?php use App\Helpers\Security; use App\Core\View; ?>
<div class="card">
  <div class="card-header"><h3 class="card-title">Editar usuario</h3></div>
  <div class="card-body">
    <form method="post" action="<?= BASE_URL ?>/users/update/<?= View::e($u['id']) ?>">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-group"><label>Nombre</label><input name="name" class="form-control" value="<?= View::e($u['name']) ?>" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= View::e($u['email']) ?>" required></div>
      <div class="form-group"><label>Rol</label>
        <select name="role" class="form-control">
          <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Administrador</option>
          <option value="tech" <?= $u['role']==='tech'?'selected':'' ?>>Técnico</option>
        </select>
      </div>
      <div class="form-group"><label>Nueva contraseña (opcional)</label><input type="password" name="password" class="form-control"></div>
      <button class="btn btn-primary">Actualizar</button>
    </form>
  </div>
</div>
