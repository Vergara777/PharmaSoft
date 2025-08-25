<?php use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header"><h3 class="card-title">Crear usuario</h3></div>
  <div class="card-body">
    <form method="post" action="<?= BASE_URL ?>/users/store">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-group"><label>Nombre</label><input name="name" class="form-control" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="form-group"><label>Rol</label>
        <select name="role" class="form-control">
          <option value="admin">Administrador</option>
          <option value="tech" selected>Técnico</option>
        </select>
      </div>
      <div class="form-group"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary">Guardar</button>
    </form>
  </div>
</div>
