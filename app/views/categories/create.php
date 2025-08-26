<?php use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-tags mr-2 text-primary"></i> Nueva categoría</h3></div>
  <div class="card-body">
    <form method="post" action="<?= BASE_URL ?>/categories/store">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-group"><label>Nombre</label><input name="name" class="form-control" required></div>
      <div class="text-right">
        <a href="<?= BASE_URL ?>/categories" class="btn btn-secondary">Cancelar</a>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
