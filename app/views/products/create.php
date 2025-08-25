<?php use App\Helpers\Security; ?>
<div class="card">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-box-open mr-2 text-primary" aria-hidden="true"></i> Nuevo producto</h3></div>
  <div class="card-body">
    <form method="post" action="<?= BASE_URL ?>/products/store" enctype="multipart/form-data" data-loading-text="Guardando producto...">
      <input type="hidden" name="csrf" value="<?= Security::csrfToken() ?>">
      <div class="form-group"><label>SKU</label><input name="sku" class="form-control" required></div>
      <div class="form-group"><label>Nombre</label><input name="name" class="form-control" required></div>
      <div class="form-group"><label>Descripción</label><textarea name="description" class="form-control"></textarea></div>
      <div class="form-group"><label>Imagen (opcional)</label><input type="file" name="image" accept="image/*" class="form-control-file"></div>
      <div class="form-group"><label>Stock</label><input type="number" name="stock" class="form-control" value="0" min="0" required></div>
      <div class="form-group"><label>Precio</label><input type="number" name="price" class="form-control" value="0.00" min="0" step="0.01" required></div>
      <div class="form-group"><label>Fecha de caducidad</label><input type="date" name="expires_at" class="form-control"></div>
      <div class="form-group">
        <label>Estado</label>
        <select name="status" class="form-control">
          <option value="active" selected>Activo</option>
          <option value="retired">Retirado</option>
        </select>
      </div>
      <button class="btn btn-primary" data-loading-text="Guardando producto..."><i class="fas fa-save mr-1" aria-hidden="true"></i> Guardar</button>
    </form>
  </div>
</div>
