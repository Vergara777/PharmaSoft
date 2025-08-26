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
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Categoría</label>
          <select name="category_id" class="form-control">
            <option value="">(Sin categoría)</option>
            <?php foreach (($categories ?? []) as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-6">
          <label>Proveedor</label>
          <select name="supplier_id" class="form-control">
            <option value="">(Sin proveedor)</option>
            <?php foreach (($suppliers ?? []) as $s): ?>
              <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Stock</label><input type="number" name="stock" class="form-control" value="0" min="0" required></div>
      <div class="form-group">
        <label>Precio</label>
        <input type="hidden" name="price" id="priceRaw" value="0">
        <input type="text" id="priceFmt" class="form-control" inputmode="numeric" autocomplete="off" placeholder="$0" value="$0">
        <small class="form-text text-muted">Formato: $6.000 (sin decimales)</small>
      </div>
      <div class="form-group"><label>Fecha de caducidad</label><input type="date" name="expires_at" class="form-control"></div>
      <div class="form-group">
        <label>Estado</label>
        <select name="status" class="form-control">
          <option value="active" selected>Activo</option>
          <option value="retired">Retirado</option>
        </select>
      </div>
      <div class="d-flex align-items-center">
        <button class="btn btn-primary mr-2" data-loading-text="Guardando producto..."><i class="fas fa-save mr-1" aria-hidden="true"></i> Guardar</button>
        <a href="<?= BASE_URL ?>/products" class="btn btn-secondary"><i class="fas fa-times mr-1" aria-hidden="true"></i> Cancelar</a>
      </div>
    </form>
  </div>
</div>
<script>
  (function(){
    function toNumber(str){
      str = (str||'').toString().replace(/[^0-9]/g,'');
      return str === '' ? 0 : parseInt(str,10);
    }
    function fmtCOP(n){
      try { return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0}).format(n); }
      catch(e){ return '$' + (n||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    }
    var raw = document.getElementById('priceRaw');
    var fmt = document.getElementById('priceFmt');
    if (raw && fmt){
      function syncFromInput(){ var v = toNumber(fmt.value); raw.value = v; fmt.value = fmtCOP(v); }
      // On focus, show plain digits for easier editing
      fmt.addEventListener('focus', function(){ var v = toNumber(fmt.value); fmt.value = v ? String(v) : ''; setTimeout(function(){ try{ fmt.select(); }catch(_){ } }, 0); });
      // On blur, format as COP
      fmt.addEventListener('blur', function(){ syncFromInput(); });
      // On input, keep only digits
      fmt.addEventListener('input', function(){ this.value = this.value.replace(/[^0-9]/g,''); });
      // Initialize
      syncFromInput();
      // Ensure submit keeps raw value
      var form = fmt.closest('form'); if (form) { form.addEventListener('submit', function(){ syncFromInput(); }); }
    }
  })();
</script>
