<?php use App\Core\View; use App\Helpers\Security; ?>
<style>
  /* Scoped to login view */
  .login-shell { min-height: calc(100vh - 60px); display: grid; place-items: center; padding: 24px 12px; }
  .login-modal { width: 100%; max-width: 420px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 30px 80px rgba(0,0,0,.18); overflow: hidden; }
  .login-header { padding: 24px 22px; display: flex; align-items: center; gap: 12px; background: linear-gradient(90deg, #0b1220, #0f1a33); color: #f9fafb; }
  .login-logo { width: 44px; height: 44px; border-radius: 10px; display: grid; place-items: center; background: #3c8dbc; color: #fff; box-shadow: inset 0 -2px 0 rgba(0,0,0,.2); font-size: 20px; }
  .login-title { margin: 0; font-weight: 800; letter-spacing: .2px; }
  .login-sub { margin: 2px 0 0; opacity: .85; font-size: 12px; }
  .login-body { padding: 20px 18px; }
  .login-body .form-group label { font-weight: 600; color: #374151; }
  .login-body .form-control { height: 44px; border-radius: 8px; }
  .login-actions { padding: 16px 18px 22px; }
  .login-btn { height: 44px; border-radius: 10px; font-weight: 700; }
  .login-footer { text-align: center; padding: 10px 18px 18px; color: #6b7280; font-size: 12px; }
  /* Make background neutral when login is active */
  body.login-body { background: #f3f4f6; }
</style>

<div class="login-shell">
  <div class="login-modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <div class="login-header">
      <div class="login-logo" aria-hidden="true"><i class="fas fa-capsules"></i></div>
      <div>
        <h3 id="loginTitle" class="login-title">PharmaSoft</h3>
        <div class="login-sub">Iniciar sesión</div>
      </div>
    </div>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger m-3 mb-0"><?= View::e($error) ?></div>
    <?php endif; ?>
    <div class="login-body">
      <form method="post" action="<?= BASE_URL ?>/auth/login" data-loading-text="Validando...">
        <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
        <div class="form-group">
          <label for="loginEmail">Email</label>
          <input id="loginEmail" type="email" name="email" class="form-control" placeholder="tu@correo.com" required autofocus>
        </div>
        <div class="form-group">
          <label for="loginPass">Contraseña</label>
          <input id="loginPass" type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="login-actions">
          <button class="btn btn-primary btn-block login-btn"><i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i> Entrar</button>
        </div>
      </form>
      <div class="login-footer">© <?= date('Y') ?> PharmaSoft</div>
    </div>
  </div>
</div>
