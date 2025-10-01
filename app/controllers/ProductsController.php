<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Flash;
use App\Helpers\Audit;

class ProductsController extends Controller {
    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $q = trim((string)($_GET['q'] ?? ''));
        $categoryId = isset($_GET['category_id']) && ctype_digit((string)$_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 9;
        if ($per < 9) { $per = 9; }
        // Optional filters
        $expiryParam = trim((string)($_GET['expiry'] ?? ''));
        $expiryDays = ($expiryParam !== '' && ctype_digit($expiryParam)) ? (int)$expiryParam : null; // e.g., 30 or 60
        $stockParam = strtolower(trim((string)($_GET['stock'] ?? '')));
        $stockFilter = ($stockParam === 'low') ? 'low' : null;
        $lowThr = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 5;
        $prod = new Product();
        // Use filtered counters if any filter is set; otherwise fallback to existing
        if ($expiryDays !== null || $stockFilter !== null) {
            $total = $prod->countFiltered($q, $categoryId, $expiryDays, $stockFilter, $lowThr);
        } else {
            $total = $prod->countSearch($q, $categoryId);
        }
        // If a category is selected, disable pagination and show all filtered products
        $disablePagination = ($categoryId !== null);
        if ($disablePagination) {
            $page = 1;
            $per = max(1, (int)$total);
        }
        $pages = max(1, (int)ceil(($per > 0 ? $total / $per : 1)));
        if ($page > $pages) { $page = $pages; }
        $offset = ($disablePagination ? 0 : (($page - 1) * $per));
        if ($expiryDays !== null || $stockFilter !== null) {
            $products = $prod->searchFilteredPaginated($q, $per, $offset, $categoryId, $expiryDays, $stockFilter, $lowThr);
        } else {
            $products = $prod->searchPaginated($q, $per, $offset, $categoryId);
        }
        // Build a quick lookup of products that have sales references to control UI (delete vs deactivate)
        $ids = array_map(static function($r){ return (int)($r['id'] ?? 0); }, (array)$products);
        $hasSalesIds = array_flip($prod->idsWithSales($ids));
        $categories = (new Category())->all();
        $suppliers = (new Supplier())->all();
        $this->view('products/index', [
            'products' => $products,
            'q' => $q,
            'title' => 'Productos',
            'hasSales' => $hasSalesIds,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'categoryId' => $categoryId,
            'expiry' => $expiryParam,
            'stock' => $stockFilter,
            'pagination' => $disablePagination ? null : [
                'page' => $page,
                'per' => $per,
                'total' => $total,
                'pages' => $pages,
            ]
        ]);
    }

    public function create(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para crear productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        $categories = (new Category())->all();
        $suppliers = (new Supplier())->all();
        $suppliers = (new Supplier())->all();
        $this->view('products/create', ['title' => 'Nuevo producto', 'categories' => $categories, 'suppliers' => $suppliers]);
    }

    public function store(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para crear productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $status = $_POST['status'] ?? 'active';
        $status = in_array($status, ['active','retired'], true) ? $status : 'active';
        $d = [
            'sku' => trim($_POST['sku'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image' => null,
            'stock' => (int)($_POST['stock'] ?? 0),
            'price' => (int)($_POST['price'] ?? 0),
            'expires_at' => $_POST['expires_at'] ?? null,
            'status' => $status,
            'category_id' => isset($_POST['category_id']) && ctype_digit((string)$_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'supplier_id' => isset($_POST['supplier_id']) && ctype_digit((string)$_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
            'shelf' => !empty($_POST['shelf']) ? trim($_POST['shelf']) : null,
            'row' => !empty($_POST['row']) ? (int)$_POST['row'] : null,
            'position' => !empty($_POST['position']) ? (int)$_POST['position'] : null,
        ];
        if ($d['sku'] === '' || $d['name'] === '') {
            Flash::error('SKU y Nombre son obligatorios', 'Datos incompletos', 6000, 'top-end');
            $this->redirect('/products/create');
        }
        if ($d['stock'] < 0) {
            Flash::error('El stock no puede ser negativo', 'Valor inválido', 6000, 'top-end');
            $this->redirect('/products/create');
        }
        if ($d['price'] < 0) {
            Flash::error('El precio no puede ser negativo', 'Valor inválido', 6000, 'top-end');
            $this->redirect('/products/create');
        }
        // Handle image upload (optional)
        if (!empty($_FILES['image']['name'] ?? '')) {
            $err = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($err === UPLOAD_ERR_OK) {
                $tmp = $_FILES['image']['tmp_name'];
                $name = $_FILES['image']['name'];
                $size = (int)($_FILES['image']['size'] ?? 0);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    Flash::error('Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.', 'Archivo inválido', 6000, 'top-end');
                    $this->redirect('/products/create');
                }
                if ($size > 5 * 1024 * 1024) { // 5MB
                    Flash::error('La imagen supera el tamaño máximo de 5MB.', 'Archivo demasiado grande', 6000, 'top-end');
                    $this->redirect('/products/create');
                }
                $destDir = __DIR__ . '/../../public/uploads';
                if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
                $newName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $destDir . DIRECTORY_SEPARATOR . $newName;
                if (!move_uploaded_file($tmp, $destPath)) {
                    Flash::error('No se pudo guardar la imagen en el servidor.', 'Error de carga', 6000, 'top-end');
                    $this->redirect('/products/create');
                }
                $d['image'] = $newName;
            } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                Flash::error('Error al subir la imagen (código ' . (int)$err . ').', 'Error de carga', 6000, 'top-end');
                $this->redirect('/products/create');
            }
        }
        $newId = (new Product())->create($d);
        // Audit: product created (with friendly summary)
        Audit::log('product', (int)$newId, 'create', [
            'summary' => 'Se creó el producto: ' . $d['name'] . ' (SKU ' . $d['sku'] . ')',
            'sku' => $d['sku'], 'name' => $d['name'], 'price' => $d['price'], 'stock' => $d['stock'], 'status' => $d['status']
        ]);
        Flash::success('Producto creado correctamente', 'Éxito', 6000, 'top-end');
        $this->redirect('/products');
    }

    public function edit($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para editar productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        $p = (new Product())->find((int)$id);
        $categories = (new Category())->all();
        $suppliers = (new Supplier())->all();
        $this->view('products/edit', ['p' => $p, 'title' => 'Editar producto', 'categories' => $categories, 'suppliers' => $suppliers]);
    }

    public function update($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para actualizar productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $status = $_POST['status'] ?? 'active';
        $status = in_array($status, ['active','retired'], true) ? $status : 'active';
        $existing = (new Product())->find((int)$id) ?: [];
        $d = [
            'sku' => trim($_POST['sku'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image' => $existing['image'] ?? null,
            'stock' => (int)($_POST['stock'] ?? 0),
            'price' => (int)($_POST['price'] ?? 0),
            'expires_at' => $_POST['expires_at'] ?? null,
            'status' => $status,
            'category_id' => isset($_POST['category_id']) && ctype_digit((string)$_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'supplier_id' => isset($_POST['supplier_id']) && ctype_digit((string)$_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
            'shelf' => !empty($_POST['shelf']) ? trim($_POST['shelf']) : null,
            'row' => !empty($_POST['row']) ? (int)$_POST['row'] : null,
            'position' => !empty($_POST['position']) ? (int)$_POST['position'] : null,
        ];
        if ($d['sku'] === '' || $d['name'] === '') {
            Flash::error('SKU y Nombre son obligatorios', 'Datos incompletos', 6000, 'top-end');
            $this->redirect('/products/edit/' . (int)$id);
        }
        if ($d['stock'] < 0) {
            Flash::error('El stock no puede ser negativo', 'Valor inválido', 6000, 'top-end');
            $this->redirect('/products/edit/' . (int)$id);
        }
        if ($d['price'] < 0) {
            Flash::error('El precio no puede ser negativo', 'Valor inválido', 6000, 'top-end');
            $this->redirect('/products/edit/' . (int)$id);
        }
        // Handle optional new image
        if (!empty($_FILES['image']['name'] ?? '')) {
            $err = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($err === UPLOAD_ERR_OK) {
                $tmp = $_FILES['image']['tmp_name'];
                $name = $_FILES['image']['name'];
                $size = (int)($_FILES['image']['size'] ?? 0);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    Flash::error('Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.', 'Archivo inválido', 6000, 'top-end');
                    $this->redirect('/products/edit/' . (int)$id);
                }
                if ($size > 5 * 1024 * 1024) { // 5MB
                    Flash::error('La imagen supera el tamaño máximo de 5MB.', 'Archivo demasiado grande', 6000, 'top-end');
                    $this->redirect('/products/edit/' . (int)$id);
                }
                $destDir = __DIR__ . '/../../public/uploads';
                if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
                $newName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $destDir . DIRECTORY_SEPARATOR . $newName;
                if (!move_uploaded_file($tmp, $destPath)) {
                    Flash::error('No se pudo guardar la imagen en el servidor.', 'Error de carga', 6000, 'top-end');
                    $this->redirect('/products/edit/' . (int)$id);
                }
                $d['image'] = $newName;
            } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                Flash::error('Error al subir la imagen (código ' . (int)$err . ').', 'Error de carga', 6000, 'top-end');
                $this->redirect('/products/edit/' . (int)$id);
            }
        }
        (new Product())->update((int)$id, $d);
        // Audit: product updated (only changed fields of interest)
        $keys = ['sku','name','description','image','stock','price','expires_at','status','category_id','supplier_id'];
        $before = array_intersect_key($existing, array_flip($keys));
        $after = $d;
        // Normalize numeric strings
        foreach (['stock','price'] as $nk) { if (isset($before[$nk])) $before[$nk] = (int)$before[$nk]; }
        $changes = Audit::diff($before, $after, $keys);
        // Add friendly summary if there are changes
        if (!empty($changes)) {
            try {
                $changedKeys = implode(', ', array_keys($changes));
            } catch (\Throwable $e) { $changedKeys = ''; }
            $changes['summary'] = 'Se actualizó el producto: ' . ($d['name'] ?: ('#' . (int)$id)) . ($changedKeys ? (' (cambios: ' . $changedKeys . ')') : '');
            Audit::log('product', (int)$id, 'update', $changes);
        }
        Flash::success('Producto actualizado correctamente', 'Éxito', 6000, 'top-end');
        $this->redirect('/products');
    }

    public function destroy($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para eliminar productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        try {
            // fetch product to include name/sku in audit
            $prodM = new Product();
            $prodRow = $prodM->find((int)$id) ?: [];
            $ok = $prodM->delete((int)$id);
            if ($ok) {
                Flash::success('Producto eliminado', 'Acción completada');
                // Audit: product deleted
                $nm = trim((string)($prodRow['name'] ?? ''));
                $sku = trim((string)($prodRow['sku'] ?? ''));
                Audit::log('product', (int)$id, 'delete', [
                    'summary' => 'Se eliminó el producto: ' . ($nm !== '' ? $nm : ('#' . (int)$id)) . ($sku !== '' ? (' (SKU ' . $sku . ')') : ''),
                    'sku' => $sku, 'name' => $nm
                ]);
            } else {
                Flash::error('No se puede eliminar el producto porque tiene ventas asociadas. Puedes marcarlo como "Retirado" para ocultarlo del catálogo activo.', 'Operación no permitida');
            }
        } catch (\Throwable $e) {
            Flash::error('No se pudo eliminar el producto: ' . $e->getMessage(), 'Error');
        }
        $this->redirect('/products');
    }

    /**
     * Eliminar en lote productos VENCIDOS (expires_at < CURDATE())
     */
    public function destroyExpired(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para eliminar productos', 'Acceso denegado'); $this->redirect('/dashboard'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        try {
            // Ejecutar borrado usando el modelo
            $prod = new Product();
            $before = $prod->countExpired();
            $deleted = 0;
            if ($before > 0) { $deleted = $prod->deleteExpired(); }
            $after = $prod->countExpired();
            $skipped = max(0, $after); // referenciados por ventas u otros motivos
            $msg = 'Eliminados ' . (int)$deleted . ' producto(s) vencido(s).';
            if ($skipped > 0) {
                $msg .= ' ' . $skipped . ' no se pudieron eliminar por tener ventas asociadas.';
            }
            Flash::success($msg, 'Limpieza completada');
        } catch (\Throwable $e) {
            Flash::error('No se pudo completar la eliminación: ' . $e->getMessage(), 'Error');
        }
        $this->redirect('/dashboard');
    }

    /**
     * Listado de productos vencidos
     */
    public function expired(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        // For simplicity, reuse search pagination but source is a fixed list; quick server-side pagination
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 9;
        if ($per < 9) { $per = 9; }
        $prod = new Product();
        $all = $prod->listExpired();
        $total = count($all);
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $slice = array_slice($all, $offset, $per);
        $ids = array_map(static function($r){ return (int)($r['id'] ?? 0); }, (array)$slice);
        $hasSalesIds = array_flip($prod->idsWithSales($ids));
        $categories = (new Category())->all();
        $suppliers = (new Supplier())->all();
        $this->view('products/index', [
            'products' => $slice, 
            'q' => '', 
            'title' => 'Productos vencidos', 
            'hasSales' => $hasSalesIds,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'pagination' => [
                'page' => $page, 'per' => $per, 'total' => $total, 'pages' => $pages
            ]
        ]);
    }

    /**
     * Listado de productos por vencer en ≤ 30 días
     */
    public function expiring30(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $products = (new Product())->listExpiringWithin(30);
        $this->view('products/index', ['products' => $products, 'q' => '', 'title' => 'Productos por vencer (≤ 30 días)']);
    }

    /**
     * Marcar como retirados todos los productos vencidos (status='retired', stock=0)
     */
    public function retireExpired(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para retirar productos', 'Acceso denegado'); $this->redirect('/dashboard'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        try {
            $prod = new Product();
            $retired = $prod->retireExpired();
            if ($retired > 0) {
                Flash::success('Retirados ' . (int)$retired . ' producto(s) vencido(s).', 'Operación completada');
            } else {
                Flash::info('No hay productos vencidos activos para retirar.', 'Sin cambios');
            }
        } catch (\Throwable $e) {
            Flash::error('No se pudo completar el retiro: ' . $e->getMessage(), 'Error');
        }
        $this->redirect('/dashboard');
    }

    /**
     * Listado de productos retirados
     */
    public function retired(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para ver productos retirados', 'Acceso denegado'); $this->redirect('/dashboard'); return; }
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 9;
        if ($per < 9) { $per = 9; }
        $all = (new Product())->listRetired();
        $total = count($all);
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $slice = array_slice($all, $offset, $per);
        $categories = (new Category())->all();
        $suppliers = (new Supplier())->all();
        $this->view('products/index', [
            'products' => $slice,
            'q' => '',
            'title' => 'Productos retirados',
            'retired' => true,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'pagination' => [
                'page' => $page, 'per' => $per, 'total' => $total, 'pages' => $pages
            ]
        ]);
    }

    /**
     * Reactivar un producto retirado
     */
    public function reactivate($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para reactivar productos', 'Acceso denegado'); $this->redirect('/products/retired'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $prodM = new Product();
        $row = $prodM->find((int)$id) ?: [];
        $prodM->reactivate((int)$id);
        Flash::success('Producto reactivado', 'Éxito');
        // Audit: reactivate
        $nm = trim((string)($row['name'] ?? ''));
        $sku = trim((string)($row['sku'] ?? ''));
        Audit::log('product', (int)$id, 'reactivate', [
            'summary' => 'Se reactivó el producto: ' . ($nm !== '' ? $nm : ('#' . (int)$id)) . ($sku !== '' ? (' (SKU ' . $sku . ')') : ''),
            'sku' => $sku, 'name' => $nm
        ]);
        $this->redirect('/products/retired');
    }

    /**
     * Desactivar (retirar) un producto activo
     */
    public function retire($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para desactivar productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $prodM = new Product();
        $row = $prodM->find((int)$id) ?: [];
        $prodM->retire((int)$id);
        Flash::success('Producto desactivado (retirado) correctamente', 'Éxito');
        // Audit: retire
        $nm = trim((string)($row['name'] ?? ''));
        $sku = trim((string)($row['sku'] ?? ''));
        Audit::log('product', (int)$id, 'retire', [
            'summary' => 'Se retiró el producto: ' . ($nm !== '' ? $nm : ('#' . (int)$id)) . ($sku !== '' ? (' (SKU ' . $sku . ')') : ''),
            'sku' => $sku, 'name' => $nm
        ]);
        $this->redirect('/products');
    }

    /**
     * Producto en formato JSON (para modal en Movimientos)
     */
    public function show($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { http_response_code(403); exit('No autorizado'); }
        $id = (int)$id;
        $row = (new Product())->find($id);
        header('Content-Type: application/json; charset=utf-8');
        if (!$row) {
            http_response_code(404);
            echo json_encode(['ok'=>false,'message'=>'Producto no encontrado']);
            return;
        }
        $img = $row['image'] ?? null;
        $imgUrl = $img ? (rtrim(BASE_URL,'/') . '/uploads/' . $img) : null;
        echo json_encode([
            'ok' => true,
            'product' => [
                'id' => (int)($row['id'] ?? 0),
                'sku' => (string)($row['sku'] ?? ''),
                'name' => (string)($row['name'] ?? ''),
                'description' => (string)($row['description'] ?? ''),
                'image' => $imgUrl,
                'stock' => (int)($row['stock'] ?? 0),
                'price' => (int)($row['price'] ?? 0),
                'expires_at' => ($row['expires_at'] ?? null),
                'status' => (string)($row['status'] ?? ''),
                'category_id' => isset($row['category_id']) ? (int)$row['category_id'] : null,
                'supplier_id' => isset($row['supplier_id']) ? (int)$row['supplier_id'] : null,
                'created_at' => (string)($row['created_at'] ?? ''),
                'updated_at' => (string)($row['updated_at'] ?? ''),
            ]
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}