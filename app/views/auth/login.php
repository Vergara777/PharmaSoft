<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i> Iniciar sesión</h3></div>
      <div class="card-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= View::e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>/auth/login">
          <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt mr-1" aria-hidden="true"></i> Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>
