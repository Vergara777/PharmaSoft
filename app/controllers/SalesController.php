<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\Sale;
use App\Models\Product;
use App\Helpers\Flash;

class SalesController extends Controller {
    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $filterId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
        if ($filterId !== '' && ctype_digit($filterId)) {
            $sales = (new Sale())->todayById((int)$filterId);
            $this->view('sales/index', ['sales' => $sales, 'filterId' => $filterId, 'title' => 'Ventas del día']);
            return;
        }
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 9;
        if ($per < 9) { $per = 9; }
        $sale = new Sale();
        $total = $sale->countToday();
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $sales = $sale->todayPaginated($per, $offset);
        $this->view('sales/index', [
            'sales' => $sales,
            'filterId' => $filterId,
            'title' => 'Ventas del día',
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
        $products = (new Product())->all();
        $this->view('sales/create', ['products' => $products, 'title' => 'Registrar venta']);
    }

    public function store(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $customerName = trim((string)($_POST['customer_name'] ?? '')) ?: null;
        $customerPhone = trim((string)($_POST['customer_phone'] ?? '')) ?: null;
        $productIds = $_POST['product_id'] ?? null; // can be scalar or array
        $qtys = $_POST['qty'] ?? null;
        $prices = $_POST['unit_price'] ?? null;
        // If arrays are provided, treat as cart
        $isArrayCart = is_array($productIds) && is_array($qtys) && is_array($prices);
        try {
            if ($isArrayCart) {
                $items = [];
                $n = min(count($productIds), count($qtys), count($prices));
                for ($i = 0; $i < $n; $i++) {
                    $pid = (int)$productIds[$i];
                    $q = (int)$qtys[$i];
                    $pr = (float)$prices[$i];
                    if ($pid > 0 && $q > 0) {
                        $items[] = ['product_id' => $pid, 'qty' => $q, 'unit_price' => $pr];
                    }
                }
                if (empty($items)) {
                    throw new \RuntimeException('El carrito está vacío. Agrega al menos un producto.');
                }
                $saleId = (new Sale())->createCart($items, $customerName, $customerPhone);
            } else {
                // Legacy single-item form
                $productId = (int)$productIds;
                $qty = (int)$qtys;
                $unitPrice = (float)$prices;
                if ($productId <= 0 || $qty <= 0) {
                    throw new \RuntimeException('Selecciona un producto y una cantidad válida.');
                }
                $saleId = (new Sale())->create($productId, $qty, $unitPrice, $customerName, $customerPhone);
            }
            // Flash success notification
            Flash::success('Venta registrada exitosamente', 'Venta exitosa');
            $this->redirect('/sales/invoice/' . $saleId);
        } catch (\Throwable $e) {
            $products = (new Product())->all();
            $this->view('sales/create', [
                'products' => $products,
                'error' => $e->getMessage(),
                'title' => 'Registrar venta'
            ]);
        }
    }

    public function invoice($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        // Prefer multi-item view
        $sale = (new Sale())->findByIdWithItems((int)$id);
        if (!$sale) { http_response_code(404); exit('Venta no encontrada'); }
        $this->view('sales/invoice', ['sale' => $sale, 'title' => 'Factura electrónica #' . $id]);
    }

    public function all(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $filterId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
        if ($filterId !== '' && ctype_digit($filterId)) {
            $sales = (new Sale())->allById((int)$filterId);
            $this->view('sales/all', ['sales' => $sales, 'filterId' => $filterId, 'title' => 'Ventas']);
            return;
        }
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 9;
        if ($per < 9) { $per = 9; }
        $sale = new Sale();
        $total = $sale->countAll();
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $sales = $sale->allPaginated($per, $offset);
        $this->view('sales/all', [
            'sales' => $sales,
            'filterId' => $filterId,
            'title' => 'Ventas',
            'pagination' => [
                'page' => $page,
                'per' => $per,
                'total' => $total,
                'pages' => $pages,
            ]
        ]);
    }
}
