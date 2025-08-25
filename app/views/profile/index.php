<?php use App\Core\View; use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-user mr-2 text-primary" aria-hidden="true"></i> Mi perfil</h3>
    <button type="button" id="btnEditProfile" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit mr-1" aria-hidden="true"></i> Editar</button>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-4">
        <?php $__av = $user['avatar'] ?? 'https://via.placeholder.com/300x300?text=Avatar'; if (strpos($__av, 'http://') !== 0 && strpos($__av, 'https://') !== 0) { $__av = BASE_URL . '/' . ltrim($__av, '/'); } ?>
        <img class="img-fluid img-thumbnail" src="<?= View::e($__av) ?>" alt="Avatar" style="object-fit:cover; width:100%; max-width:300px; height:auto;">
        <form class="mt-3" method="post" action="<?= BASE_URL ?>/profile/avatar" enctype="multipart/form-data" data-loading-text="Subiendo avatar...">
          <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
          <div class="form-group"><input type="file" name="avatar" accept="image/*" required></div>
          <button class="btn btn-secondary" data-loading-text="Subiendo avatar..."><i class="fas fa-upload mr-1" aria-hidden="true"></i> Actualizar avatar</button>
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
            <span class="text-muted small">Rol: <?= View::e($user['role'] ?? '') ?></span>
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
            <span class="text-muted small">Rol: <?= View::e($user['role'] ?? '') ?></span>
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
  })();
</script>
