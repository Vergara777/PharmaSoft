<?php use App\Helpers\Security; use App\Core\View; ?>
<div class="card">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-edit mr-2 text-primary" aria-hidden="true"></i> Editar producto</h3></div>
  <div class="card-body">
    <form method="post" action="<?= BASE_URL ?>/products/update/<?= View::e($p['id']) ?>" enctype="multipart/form-data" data-loading-text="Actualizando producto...">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-group"><label>SKU</label><input name="sku" class="form-control" value="<?= View::e($p['sku']) ?>" required></div>
      <div class="form-group"><label>Nombre</label><input name="name" class="form-control" value="<?= View::e($p['name']) ?>" required></div>
      <div class="form-group"><label>Descripción</label><textarea name="description" class="form-control"><?= View::e($p['description']) ?></textarea></div>
      <div class="form-group">
        <label>Imagen (opcional)</label>
        <?php if (!empty($p['image'])): ?>
          <div class="mb-2">
            <img src="<?= BASE_URL ?>/uploads/<?= View::e($p['image']) ?>" alt="Imagen actual" style="max-height:120px;border:1px solid #ddd;padding:2px;border-radius:4px;">
          </div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="form-control-file">
      </div>
      <div class="form-group"><label>Stock</label><input type="number" name="stock" class="form-control" value="<?= View::e($p['stock']) ?>" min="0" required></div>
      <div class="form-group"><label>Precio</label><input type="number" name="price" class="form-control" value="<?= View::e(number_format((float)($p['price'] ?? 0), 2, '.', '')) ?>" min="0" step="0.01" required></div>
      <div class="form-group"><label>Fecha de caducidad</label><input type="date" name="expires_at" class="form-control" value="<?= View::e(substr($p['expires_at'] ?? '',0,10)) ?>"></div>
      <div class="form-group">
        <label>Estado</label>
        <select name="status" class="form-control">
          <option value="active" <?= (($p['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Activo</option>
          <option value="retired" <?= (($p['status'] ?? '') === 'retired') ? 'selected' : '' ?>>Retirado</option>
        </select>
      </div>
      <button class="btn btn-primary" data-loading-text="Actualizando producto..."><i class="fas fa-save mr-1" aria-hidden="true"></i> Actualizar</button>
    </form>
  </div>
</div>
