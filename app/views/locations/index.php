<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-map-marker-alt"></i> Ubicaciones de Productos
                </h1>
                <a href="<?= BASE_URL ?>/locations/map" class="btn btn-primary">
                    <i class="fas fa-map"></i> Ver Mapa
                </a>
            </div>
            <p class="text-muted">Administra las ubicaciones de los productos en el almacén</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= BASE_URL ?>/locations">
                        <div class="mb-3">
                            <label for="shelf" class="form-label">Estante</label>
                            <select class="form-select" id="shelf" name="shelf">
                                <option value="">Todos los estantes</option>
                                <?php foreach ($shelves as $shelf): ?>
                                    <option value="<?= htmlspecialchars($shelf) ?>" 
                                            <?= ($currentShelf === $shelf) ? 'selected' : '' ?>>
                                        Estante <?= htmlspecialchars($shelf) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <div class="text-center py-5">
                            <div class="text-muted mb-3">
                                <i class="fas fa-inbox fa-4x"></i>
                            </div>
                            <h5>No se encontraron productos</h5>
                            <p class="text-muted">
                                <?php if ($currentShelf): ?>
                                    No hay productos en el estante <?= htmlspecialchars($currentShelf) ?>
                                <?php else: ?>
                                    No hay productos con ubicación registrada.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>SKU</th>
                                        <th>Ubicación</th>
                                        <th>Stock</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($product->image): ?>
                                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($product->image) ?>" 
                                                             alt="" class="me-3 rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($product->name) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($product->category_name ?? 'Sin categoría') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($product->sku) ?></td>
                                            <td>
                                                <?php if ($product->shelf): ?>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-warehouse"></i> 
                                                        Estante <?= htmlspecialchars($product->shelf) ?>
                                                        <?php if ($product->row): ?>
                                                            - Fila <?= $product->row ?>
                                                        <?php endif; ?>
                                                        <?php if ($product->position): ?>
                                                            - Pos. <?= $product->position ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin ubicación</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $product->stock > 0 ? 'success' : 'danger' ?>">
                                                    <?= $product->stock ?> unidades
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/products/edit/<?= $product->id ?>#location" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Editar ubicación">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/products/view/<?= $product->id ?>" 
                                                   class="btn btn-sm btn-outline-info"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
