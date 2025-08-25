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
      <div class="form-group">
        <label>Precio</label>
        <?php $rawPrice = (int)round((float)($p['price'] ?? 0)); ?>
        <input type="hidden" name="price" id="priceRaw" value="<?= $rawPrice ?>">
        <input type="text" id="priceFmt" class="form-control" inputmode="numeric" autocomplete="off" placeholder="$0">
        <small class="form-text text-muted">Formato: $6.000 (sin decimales)</small>
      </div>
      <div class="form-group"><label>Fecha de caducidad</label><input type="date" name="expires_at" class="form-control" value="<?= View::e(substr($p['expires_at'] ?? '',0,10)) ?>"></div>
      <div class="form-group">
        <label>Estado</label>
        <select name="status" class="form-control">
          <option value="active" <?= (($p['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Activo</option>
          <option value="retired" <?= (($p['status'] ?? '') === 'retired') ? 'selected' : '' ?>>Retirado</option>
        </select>
      </div>
      <button class="btn btn-primary" data-loading-text="Actualizando producto..."><i class="fas fa-save mr-1" aria-hidden="true"></i> Actualizar</button>
      <a class="btn btn-secondary" href="<?= BASE_URL ?>/products"><i class="fas fa-times mr-1" aria-hidden="true"></i> Cancelar</a>
    </form>
  </div>
</div>
<script>
  (function(){
    function toNumber(str){ str = (str||'').toString().replace(/[^0-9]/g,''); return str === '' ? 0 : parseInt(str,10); }
    function fmtCOP(n){ try{ return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0}).format(n);}catch(e){ return '$' + (n||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }
    var raw = document.getElementById('priceRaw'); var fmt = document.getElementById('priceFmt');
    if (raw && fmt){
      function syncFromInput(){ var v = toNumber(fmt.value); raw.value = v; fmt.value = fmtCOP(v); }
      function setInitial(){ var v = parseInt(raw.value||'0',10)||0; fmt.value = fmtCOP(v); }
      fmt.addEventListener('focus', function(){ var v = toNumber(fmt.value); fmt.value = v ? String(v) : ''; setTimeout(function(){ try{ fmt.select(); }catch(_){ } }, 0); });
      fmt.addEventListener('input', function(){ this.value = this.value.replace(/[^0-9]/g,''); });
      fmt.addEventListener('blur', function(){ syncFromInput(); });
      setInitial();
      var form = fmt.closest('form'); if (form) { form.addEventListener('submit', function(){ syncFromInput(); }); }
    }
  })();
</script>
