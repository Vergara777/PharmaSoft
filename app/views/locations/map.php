<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-warehouse"></i> Mapa de Ubicaciones
                </h1>
                <a href="<?= BASE_URL ?>/locations" class="btn btn-primary">
                    <i class="fas fa-list"></i> Ver Lista
                </a>
            </div>
            <p class="text-muted">Visualiza la distribución de productos en el almacén</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="warehouse-map">
                        <?php if (empty($shelves)): ?>
                            <div class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="fas fa-inbox fa-4x"></i>
                                </div>
                                <h5>No hay ubicaciones registradas</h5>
                                <p class="text-muted">Agrega ubicaciones a los productos para ver el mapa.</p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-4">
                                <?php foreach ($shelves as $shelf): ?>
                                    <div class="shelf-container">
                                        <div class="shelf-header bg-primary text-white p-2 text-center mb-2 rounded">
                                            <h5 class="m-0">Estante <?= htmlspecialchars($shelf['name']) ?></h5>
                                            <small><?= $shelf['productCount'] ?> productos</small>
                                        </div>
                                        
                                        <?php foreach ($shelf['rows'] as $row): ?>
                                            <div class="row-shelf mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="badge bg-secondary">Fila <?= $row ?></span>
                                                    <a href="<?= BASE_URL ?>/locations?shelf=<?= urlencode($shelf['name']) ?>&row=<?= $row ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        Ver productos
                                                    </a>
                                                </div>
                                                
                                                <?php 
                                                $products = $this->productModel->getProductsByLocation($shelf['name'], $row);
                                                $positions = array_column($products, 'position');
                                                $maxPosition = !empty($positions) ? max($positions) : 0;
                                                ?>
                                                
                                                <div class="row-items d-flex gap-1">
                                                    <?php for ($i = 1; $i <= max(3, $maxPosition); $i++): ?>
                                                        <?php 
                                                        $product = array_filter($products, function($p) use ($i) {
                                                            return $p->position == $i;
                                                        });
                                                        $product = !empty($product) ? reset($product) : null;
                                                        ?>
                                                        <div class="position-slot flex-grow-1 text-center p-2 border rounded" 
                                                             style="min-width: 80px; height: 80px;"
                                                             data-bs-toggle="tooltip" 
                                                             title="<?= $product ? htmlspecialchars($product->name) : 'Vacío' ?>">
                                                            <?php if ($product): ?>
                                                                <div class="product-badge bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                                     style="width: 40px; height: 40px; margin: 0 auto; cursor: pointer;"
                                                                     onclick="window.location.href='<?= BASE_URL ?>/products/edit/<?= $product->id ?>'">
                                                                    <?= substr($product->name, 0, 1) ?>
                                                                </div>
                                                                <small class="d-block mt-1"><?= $product->stock ?> uds.</small>
                                                            <?php else: ?>
                                                                <div class="text-muted">
                                                                    <i class="fas fa-box-open"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.warehouse-map {
    overflow-x: auto;
    padding: 10px 0;
}

.shelf-container {
    min-width: 300px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.shelf-header {
    position: relative;
    z-index: 1;
}

.row-shelf {
    background: white;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #e9ecef;
}

.position-slot {
    transition: all 0.2s;
    background: #f8f9fa;
}

.position-slot:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 1;
}

.product-badge {
    transition: all 0.2s;
}

.product-badge:hover {
    transform: scale(1.1);
}

.row-items {
    min-height: 60px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
