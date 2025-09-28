<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card profile-card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-user mr-2 text-primary" aria-hidden="true"></i> Mi perfil</h3>
    <button type="button" id="btnEditProfile" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit mr-1" aria-hidden="true"></i> Editar</button>
  </div>
  <div class="card-body">
    <!-- Spectacular Hero Header -->
    <?php
      $__roleKey = strtolower((string)($user['role'] ?? ''));
      $__roleName = $__roleKey ? ucfirst($__roleKey) : 'Usuario';
      $__roleClass = 'role-default';
      if (in_array($__roleKey, ['admin','administrator'], true)) { $__roleName = 'Administrador'; $__roleClass = 'role-admin'; }
      elseif (in_array($__roleKey, ['technician','tecnico','técnico'], true)) { $__roleName = 'Técnico'; $__roleClass = 'role-tech'; }
      elseif (in_array($__roleKey, ['worker','trabajador'], true)) { $__roleName = 'Trabajador'; $__roleClass = 'role-worker'; }
      $__name = $user['name'] ?? 'Usuario';
      $__email = $user['email'] ?? '';
      $__avh = $user['avatar'] ?? 'https://via.placeholder.com/300x300?text=Avatar';
      if (strpos($__avh, 'http://') !== 0 && strpos($__avh, 'https://') !== 0) { $__avh = BASE_URL . '/' . ltrim($__avh, '/'); }
    ?>
    <div class="profile-hero mb-4">
      <div class="ph-bg"></div>
      <div class="ph-content">
        <div class="ph-avatar">
          <img src="<?= View::e($__avh) ?>" alt="Avatar de perfil">
        </div>
        <div class="ph-info">
          <div class="ph-name"><?= View::e($__name) ?></div>
          <div class="ph-sub">
            <span class="ph-email"><i class="fas fa-envelope mr-1" aria-hidden="true"></i> <?= View::e($__email) ?></span>
            <span class="role-badge <?= $__roleClass ?>" title="Rol del usuario">
              <i class="fas fa-user-shield mr-1" aria-hidden="true"></i> <?= View::e($__roleName) ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 d-flex flex-column align-items-center text-center">
        <?php $__av = $user['avatar'] ?? 'https://via.placeholder.com/300x300?text=Avatar'; if (strpos($__av, 'http://') !== 0 && strpos($__av, 'https://') !== 0) { $__av = BASE_URL . '/' . ltrim($__av, '/'); } ?>
        <div class="text-muted small mb-2">Cambiar avatar</div>
        <form class="mt-2 w-100 profile-avatar-form" method="post" action="<?= BASE_URL ?>/profile/avatar" enctype="multipart/form-data" data-loading-text="Subiendo avatar...">
          <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
          <div class="custom-file mb-2">
            <input type="file" class="custom-file-input" id="avatarInput" name="avatar" accept="image/*" required>
            <label class="custom-file-label text-left" for="avatarInput">Elegir imagen...</label>
          </div>
          <button class="btn btn-secondary btn-block" data-loading-text="Subiendo avatar..."><i class="fas fa-upload mr-1" aria-hidden="true"></i> Actualizar avatar</button>
          <div class="text-muted small mt-2">Formatos: JPG, PNG, GIF, WEBP</div>
        </form>
      </div>
      <div class="col-md-8">
        <!-- Read-only view -->
        <div id="profileView">
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="text-muted small">Nombre</div>
              <div class="font-weight-bold"><?= View::e($user['name'] ?? '') ?></div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="text-muted small">Email</div>
              <div class="font-weight-bold"><?= View::e($user['email'] ?? '') ?></div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="text-muted small">Teléfono</div>
              <div><?= View::e($user['phone'] ?? '—') ?></div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="text-muted small">Cédula/ID</div>
              <div><?= View::e($user['id_number'] ?? '—') ?></div>
            </div>
            <div class="col-12 mb-3">
              <div class="text-muted small">Dirección</div>
              <div><?= View::e($user['address'] ?? '—') ?></div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="text-muted small">Cargo/Posición</div>
              <div><?= View::e($user['position'] ?? '—') ?></div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="text-muted small">Fecha ingreso</div>
              <div><?= View::e($user['hire_date'] ?? '—') ?></div>
            </div>
            <div class="col-md-3 mb-3">
              <div class="text-muted small">Fecha nacimiento</div>
              <div><?= View::e($user['birth_date'] ?? '—') ?></div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-2">
            <span class="role-badge <?= $__roleClass ?>"><i class="fas fa-user-shield mr-1" aria-hidden="true"></i> <?= View::e($__roleName) ?></span>
            <!-- Duplicate Edit button for mobile within view -->
            <button type="button" class="btn btn-outline-primary btn-sm d-md-none" onclick="toggleEdit(true)"><i class="fas fa-edit mr-1" aria-hidden="true"></i> Editar</button>
          </div>
        </div>

        <form method="post" action="<?= BASE_URL ?>/profile/update" class="mt-2" data-loading-text="Guardando perfil...">
          <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Nombre</label>
              <input type="text" name="name" class="form-control" value="<?= View::e($user['name'] ?? '') ?>" required>
            </div>
            <div class="form-group col-md-6">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="<?= View::e($user['email'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Teléfono</label>
              <input type="text" name="phone" class="form-control" value="<?= View::e($user['phone'] ?? '') ?>">
            </div>
            <div class="form-group col-md-6">
              <label>Cédula/ID</label>
              <input type="text" name="id_number" class="form-control" value="<?= View::e($user['id_number'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="address" class="form-control" value="<?= View::e($user['address'] ?? '') ?>">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Cargo/Posición</label>
              <input type="text" name="position" class="form-control" value="<?= View::e($user['position'] ?? '') ?>">
            </div>
            <div class="form-group col-md-3">
              <label>Fecha ingreso</label>
              <input type="date" name="hire_date" class="form-control" value="<?= View::e($user['hire_date'] ?? '') ?>">
            </div>
            <div class="form-group col-md-3">
              <label>Fecha nacimiento</label>
              <input type="date" name="birth_date" class="form-control" value="<?= View::e($user['birth_date'] ?? '') ?>">
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="role-badge <?= $__roleClass ?>"><i class="fas fa-user-shield mr-1" aria-hidden="true"></i> <?= View::e($__roleName) ?></span>
            <div class="btn-group">
              <button type="button" class="btn btn-light" onclick="toggleEdit(false)"><i class="fas fa-times mr-1" aria-hidden="true"></i> Cancelar</button>
              <button type="submit" class="btn btn-primary" data-loading-text="Guardando perfil..."><i class="fas fa-save mr-1" aria-hidden="true"></i> Guardar cambios</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Hide form by default; show read-only view
  (function(){
    var form = document.querySelector('form[action$="/profile/update"]');
    var view = document.getElementById('profileView');
    function setMode(edit) {
      if (!form || !view) return;
      if (edit) { form.classList.remove('d-none'); view.classList.add('d-none'); }
      else { form.classList.add('d-none'); view.classList.remove('d-none'); }
    }
    window.toggleEdit = setMode;
    setMode(false); // start in view mode
    var btn = document.getElementById('btnEditProfile');
    if (btn) btn.addEventListener('click', function(){ setMode(true); });

    // Show chosen file name for custom-file input (Bootstrap 4 behavior)
    var fileInput = document.getElementById('avatarInput');
    if (fileInput) {
      fileInput.addEventListener('change', function(){
        var lbl = document.querySelector('label[for="avatarInput"]');
        if (lbl && fileInput.files && fileInput.files.length) {
          lbl.textContent = fileInput.files[0].name;
        }
      });
    }
  })();
</script>

<style>
  /* Scoped styles for profile */
  .profile-hero { position: relative; border-radius: 14px; overflow: hidden; background: #0b1220; color: #e5e7eb; box-shadow: 0 10px 30px rgba(0,0,0,.12); animation: ps-hero-float 7s ease-in-out infinite; }
  .profile-hero::before { content: ""; position: absolute; inset: -2px; border-radius: 16px; padding: 2px; background: linear-gradient(90deg,#38bdf8,#a78bfa,#fb7185,#34d399,#38bdf8); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; animation: ps-hero-rgb 9s linear infinite; }
  .profile-hero .ph-bg { position: absolute; inset: 0; background:
      radial-gradient(1200px 400px at -10% 0%, rgba(59,130,246,.28), transparent 60%),
      radial-gradient(800px 300px at 120% 0%, rgba(236,72,153,.22), transparent 60%),
      radial-gradient(600px 260px at 50% 100%, rgba(34,197,94,.18), transparent 60%);
      filter: blur(2px);
    }
  .profile-hero .ph-content { position: relative; display: flex; align-items: center; gap: 18px; padding: 18px; }
  .profile-hero .ph-avatar { width: 86px; height: 86px; min-width: 86px; border-radius: 50%; border: 3px solid rgba(255,255,255,.85); overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.35); background: #111827; }
  .profile-hero .ph-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .profile-hero .ph-info { display: grid; gap: 6px; }
  .profile-hero .ph-name { font-weight: 900; font-size: 1.35rem; letter-spacing: .2px; color: #f9fafb; }
  .profile-hero .ph-sub { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
  .profile-hero .ph-email { color: #cbd5e1; font-weight: 700; }

  .role-badge { display: inline-flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 900; padding: 6px 10px; border-radius: 999px; border: 1px solid transparent; box-shadow: 0 6px 18px rgba(0,0,0,.12); }
  .role-admin  { background: rgba(239,68,68,.12); color: #fecaca; border-color: rgba(239,68,68,.45); }
  .role-tech   { background: rgba(59,130,246,.14); color: #bfdbfe; border-color: rgba(59,130,246,.45); }
  .role-worker { background: rgba(245,158,11,.14); color: #fde68a; border-color: rgba(245,158,11,.45); }
  .role-default{ background: rgba(148,163,184,.16); color: #e5e7eb; border-color: rgba(148,163,184,.35); }

  .profile-card { overflow: hidden; }
  .profile-avatar { width: 180px; height: 180px; border-radius: 50%; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,.12); border: 3px solid #fff; background: #f8f9fa; }
  .profile-avatar-img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .profile-avatar-form .custom-file-label { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
  @keyframes ps-hero-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-2px); } }
  @keyframes ps-hero-rgb { 0% { filter: hue-rotate(0deg); } 100% { filter: hue-rotate(360deg); } }
  @media (max-width: 576px) {
    .profile-hero .ph-content { padding: 16px; gap: 14px; }
    .profile-hero .ph-avatar { width: 76px; height: 76px; min-width: 76px; }
    .profile-avatar { width: 140px; height: 140px; }
  }
</style>
