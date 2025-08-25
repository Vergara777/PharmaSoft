<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Flash;

class ProductsController extends Controller {
    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $q = trim((string)($_GET['q'] ?? ''));
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 9;
        if ($per < 9) { $per = 9; }
        $prod = new Product();
        $total = $prod->countSearch($q);
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $products = $prod->searchPaginated($q, $per, $offset);
        $this->view('products/index', [
            'products' => $products,
            'q' => $q,
            'title' => 'Productos',
            'pagination' => [
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
        $this->view('products/create', ['title' => 'Nuevo producto']);
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
            'price' => (float)($_POST['price'] ?? 0),
            'expires_at' => $_POST['expires_at'] ?? null,
            'status' => $status,
        ];
        if ($d['sku'] === '' || $d['name'] === '') {
            Flash::error('SKU y Nombre son obligatorios', 'Datos incompletos');
            $this->redirect('/products/create');
        }
        if ($d['stock'] < 0) {
            Flash::error('El stock no puede ser negativo', 'Valor inválido');
            $this->redirect('/products/create');
        }
        if ($d['price'] < 0) {
            Flash::error('El precio no puede ser negativo', 'Valor inválido');
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
                    Flash::error('Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.', 'Archivo inválido');
                    $this->redirect('/products/create');
                }
                if ($size > 5 * 1024 * 1024) { // 5MB
                    Flash::error('La imagen supera el tamaño máximo de 5MB.', 'Archivo demasiado grande');
                    $this->redirect('/products/create');
                }
                $destDir = __DIR__ . '/../../public/uploads';
                if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
                $newName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $destDir . DIRECTORY_SEPARATOR . $newName;
                if (!move_uploaded_file($tmp, $destPath)) {
                    Flash::error('No se pudo guardar la imagen en el servidor.', 'Error de carga');
                    $this->redirect('/products/create');
                }
                $d['image'] = $newName;
            } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                Flash::error('Error al subir la imagen (código ' . (int)$err . ').', 'Error de carga');
                $this->redirect('/products/create');
            }
        }
        (new Product())->create($d);
        Flash::success('Producto creado correctamente', 'Éxito');
        $this->redirect('/products');
    }

    public function edit($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para editar productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        $p = (new Product())->find((int)$id);
        $this->view('products/edit', ['p' => $p, 'title' => 'Editar producto']);
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
            'price' => (float)($_POST['price'] ?? 0),
            'expires_at' => $_POST['expires_at'] ?? null,
            'status' => $status,
        ];
        if ($d['sku'] === '' || $d['name'] === '') {
            Flash::error('SKU y Nombre son obligatorios', 'Datos incompletos');
            $this->redirect('/products/edit/' . (int)$id);
        }
        if ($d['stock'] < 0) {
            Flash::error('El stock no puede ser negativo', 'Valor inválido');
            $this->redirect('/products/edit/' . (int)$id);
        }
        if ($d['price'] < 0) {
            Flash::error('El precio no puede ser negativo', 'Valor inválido');
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
                    Flash::error('Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.', 'Archivo inválido');
                    $this->redirect('/products/edit/' . (int)$id);
                }
                if ($size > 5 * 1024 * 1024) { // 5MB
                    Flash::error('La imagen supera el tamaño máximo de 5MB.', 'Archivo demasiado grande');
                    $this->redirect('/products/edit/' . (int)$id);
                }
                $destDir = __DIR__ . '/../../public/uploads';
                if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
                $newName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $destDir . DIRECTORY_SEPARATOR . $newName;
                if (!move_uploaded_file($tmp, $destPath)) {
                    Flash::error('No se pudo guardar la imagen en el servidor.', 'Error de carga');
                    $this->redirect('/products/edit/' . (int)$id);
                }
                $d['image'] = $newName;
            } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                Flash::error('Error al subir la imagen (código ' . (int)$err . ').', 'Error de carga');
                $this->redirect('/products/edit/' . (int)$id);
            }
        }
        (new Product())->update((int)$id, $d);
        Flash::success('Producto actualizado correctamente', 'Éxito');
        $this->redirect('/products');
    }

    public function destroy($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para eliminar productos', 'Acceso denegado'); $this->redirect('/products'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        try {
            $ok = (new Product())->delete((int)$id);
            if ($ok) {
                Flash::success('Producto eliminado', 'Acción completada');
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
        $all = (new Product())->listExpired();
        $total = count($all);
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $slice = array_slice($all, $offset, $per);
        $this->view('products/index', ['products' => $slice, 'q' => '', 'title' => 'Productos vencidos', 'pagination' => [
            'page' => $page, 'per' => $per, 'total' => $total, 'pages' => $pages
        ]]);
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
        $this->view('products/index', ['products' => $slice, 'q' => '', 'title' => 'Productos retirados', 'retired' => true, 'pagination' => [
            'page' => $page, 'per' => $per, 'total' => $total, 'pages' => $pages
        ]]);
    }

    /**
     * Reactivar un producto retirado
     */
    public function reactivate(int $id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { Flash::error('No tienes permisos para reactivar productos', 'Acceso denegado'); $this->redirect('/products/retired'); return; }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        try {
            $ok = (new Product())->reactivate($id);
            if ($ok) {
                Flash::success('Producto reactivado correctamente.', 'Éxito');
            } else {
                Flash::info('El producto ya estaba activo o no existe.', 'Sin cambios');
            }
        } catch (\Throwable $e) {
            Flash::error('No se pudo reactivar: ' . $e->getMessage(), 'Error');
        }
        $this->redirect('/products/retired');
    }
}

