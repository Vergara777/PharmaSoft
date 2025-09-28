<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Helpers\Flash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        // Date filter
        $from = trim((string)($_GET['from'] ?? ''));
        $to = trim((string)($_GET['to'] ?? ''));
        $isYmd = function(string $d){ return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
        $useDateFilter = $isYmd($from) && $isYmd($to);
        $explicitDateFilter = (isset($_GET['from']) || isset($_GET['to']));
        if (!$useDateFilter) {
            $from = (new \DateTimeImmutable('today'))->format('Y-m-d');
            $to = $from;
            $useDateFilter = true;
        }
        if ($explicitDateFilter) {
            // Show full table for explicit range (no pagination)
            $sales = $sale->byDatePaginated($from, $to, 1000000, 0);
            $this->view('sales/index', [
                'sales' => $sales,
                'filterId' => $filterId,
                'title' => 'Ventas del día',
                'from' => $from,
                'to' => $to,
            ]);
        } else {
            // Default daily view with pagination
            $total = $sale->countByDate($from, $to);
            $pages = max(1, (int)ceil($total / $per));
            if ($page > $pages) { $page = $pages; }
            $offset = ($page - 1) * $per;
            $sales = $sale->byDatePaginated($from, $to, $per, $offset);
            $this->view('sales/index', [
                'sales' => $sales,
                'filterId' => $filterId,
                'title' => 'Ventas del día',
                'from' => $from,
                'to' => $to,
                'pagination' => [
                    'page' => $page,
                    'per' => $per,
                    'total' => $total,
                    'pages' => $pages,
                ]
            ]);
        }
    }

    public function create(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $products = (new Product())->all();
        $categories = (new Category())->all();
        $suppliers = (new Supplier())->all();
        $this->view('sales/create', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'title' => 'Realizar venta'
        ]);
    }

    public function store(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $customerName = trim((string)($_POST['customer_name'] ?? '')) ?: null;
        $customerPhone = trim((string)($_POST['customer_phone'] ?? '')) ?: null;
        $customerEmail = trim((string)($_POST['customer_email'] ?? '')) ?: null;
        $productIds = $_POST['product_id'] ?? null; // can be scalar or array
        $qtys = $_POST['qty'] ?? null;
        $prices = $_POST['unit_price'] ?? null;
        // If arrays are provided, treat as cart
        $isArrayCart = is_array($productIds) && is_array($qtys) && is_array($prices);
        try {
            // Build a flat list of product IDs to validate
            $idsToValidate = [];
            if ($isArrayCart) {
                $n = min(count($productIds), count($qtys), count($prices));
                for ($i = 0; $i < $n; $i++) {
                    $pid = (int)$productIds[$i];
                    $q = (int)$qtys[$i];
                    if ($pid > 0 && $q > 0) { $idsToValidate[] = $pid; }
                }
            } else {
                if (is_scalar($productIds)) {
                    $pid = (int)$productIds;
                    $q = (int)$qtys;
                    if ($pid > 0 && $q > 0) { $idsToValidate[] = $pid; }
                }
            }
            // Validate: no expired products allowed
            if (!empty($idsToValidate)) {
                $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
                $pm = new Product();
                foreach (array_unique($idsToValidate) as $pid) {
                    $p = $pm->find((int)$pid);
                    if (!$p || ($p['status'] ?? '') !== 'active') {
                        throw new \RuntimeException('Producto inválido o inactivo en el carrito.');
                    }
                    $exp = $p['expires_at'] ?? null;
                    if (!empty($exp) && $exp < $today) {
                        $sku = trim((string)($p['sku'] ?? ''));
                        $name = trim((string)($p['name'] ?? ''));
                        $label = $sku !== '' ? ($sku . ' - ' . $name) : $name;
                        throw new \RuntimeException('No es posible vender productos vencidos: ' . ($label ?: ('ID ' . $pid)) . '.');
                    }
                }
            }
            if ($isArrayCart) {
                $items = [];
                $n = min(count($productIds), count($qtys), count($prices));
                for ($i = 0; $i < $n; $i++) {
                    $pid = (int)$productIds[$i];
                    $q = (int)$qtys[$i];
                    $pr = (int)$prices[$i];
                    if ($pid > 0 && $q > 0) {
                        $items[] = ['product_id' => $pid, 'qty' => $q, 'unit_price' => $pr];
                    }
                }
                if (empty($items)) {
                    throw new \RuntimeException('El carrito está vacío. Agrega al menos un producto.');
                }
                $saleId = (new Sale())->createCart($items, $customerName, $customerPhone, $customerEmail);
            } else {
                // Legacy single-item form
                $productId = (int)$productIds;
                $qty = (int)$qtys;
                $unitPrice = (int)$prices;
                if ($productId <= 0 || $qty <= 0) {
                    throw new \RuntimeException('Selecciona un producto y una cantidad válida.');
                }
                $saleId = (new Sale())->create($productId, $qty, $unitPrice, $customerName, $customerPhone, $customerEmail);
            }
            // Flash success notification
            Flash::success('Venta realizada exitosamente', 'Venta exitosa');
            $this->redirect('/sales/invoice/' . $saleId);
        } catch (\Throwable $e) {
            $products = (new Product())->all();
            $categories = (new Category())->all();
            $suppliers = (new Supplier())->all();
            $this->view('sales/create', [
                'products' => $products,
                'categories' => $categories,
                'suppliers' => $suppliers,
                'error' => $e->getMessage(),
                'title' => 'Realizar venta'
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
        // Optional date filter for 'todas'
        $from = trim((string)($_GET['from'] ?? ''));
        $to = trim((string)($_GET['to'] ?? ''));
        $isYmd = function(string $d){ return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
        $hasRange = $isYmd($from) && $isYmd($to) && (isset($_GET['from']) || isset($_GET['to']));
        if ($hasRange) {
            // Full table for the provided range (no pagination footer)
            $sales = $sale->byDatePaginated($from, $to, 1000000, 0);
            $this->view('sales/all', [
                'sales' => $sales,
                'filterId' => $filterId,
                'title' => 'Ventas',
                'from' => $from,
                'to' => $to,
            ]);
            return;
        }
        // Default: all with pagination
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

    /** Importar ventas desde CSV. Cada fila es una venta independiente.
     * Columnas soportadas (con o sin encabezado):
     *  customer_name, customer_phone, customer_email, sku, qty, unit_price
     * Retorna JSON con cantidad creada y errores por fila.
     */
    // Import endpoint removed. Kept as stub to avoid hard errors if called directly.
    public function import(): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Importación deshabilitada']);
    }

    /** Descargar Excel con ventas del rango from/to actual. */
    public function export(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        // Restrict export to administrators only
        if (!Auth::isAdmin()) { http_response_code(403); exit('No autorizado'); }
        // Allow exporting all history with all=1
        $exportAll = isset($_GET['all']) && (string)$_GET['all'] === '1';
        $from = trim((string)($_GET['from'] ?? ''));
        $to = trim((string)($_GET['to'] ?? ''));
        $isYmd = function(string $d){ return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };

        // Increase limits for heavy exports
        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $sale = new Sale();
        if ($exportAll) {
            $rows = $sale->allAsc();
            $from = 'todas';
            $to = '';
        } else {
            if (!$isYmd($from) || !$isYmd($to)) {
                $from = (new \DateTimeImmutable('today'))->format('Y-m-d');
                $to = $from;
            }
            // Large upper bound to cover big ranges without pagination
            $rows = $sale->byDatePaginated($from, $to, 1000000, 0);
        }

        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('Ventas');
        // Header (now includes Email)
        $headers = ['ID','SKU','Producto','Cant.','P. Unit','Total','Cliente','Teléfono','Email','Atendido por','Fecha'];
        $sh->fromArray($headers, null, 'A1');
        $r = 2;
        foreach ($rows as $s) {
            $isCart = !empty($s['item_count']) && (int)$s['item_count'] > 0 && empty($s['product_id']);
            $sku = $isCart ? ($s['first_sku'] ?? '') : ($s['sku'] ?? '');
            $name = $isCart ? ($s['first_name'] ?? '') : ($s['name'] ?? '');
            if ($isCart && (int)$s['item_count'] > 1) {
                $name = trim($name . ' +' . ((int)$s['item_count'] - 1) . ' más');
            }
            $qty = $isCart ? (int)($s['items_qty'] ?? 0) : (int)($s['qty'] ?? 0);
            $punit = null;
            if ($isCart) {
                $q = (float)($s['items_qty'] ?? 0); $t = (float)($s['total'] ?? 0); if ($q > 0) { $punit = round($t / $q); }
            } else {
                if (isset($s['unit_price'])) { $punit = (int)$s['unit_price']; }
            }
            $attended = trim(($s['user_name'] ?? '') . ' ' . (($s['user_role'] ?? '') ? '(' . $s['user_role'] . ')' : ''));
            $row = [
                $s['id'] ?? '', $sku, $name, $qty, $punit, $s['total'] ?? 0, $s['customer_name'] ?? '', $s['customer_phone'] ?? '', $s['customer_email'] ?? '', $attended, $s['created_at'] ?? ''
            ];
            $sh->fromArray($row, null, 'A' . $r);
            $r++;
        }
        // Autosize
        foreach (range('A','K') as $col) { $sh->getColumnDimension($col)->setAutoSize(true); }

        $filename = $exportAll ? 'ventas_todas.xlsx' : ('ventas_' . $from . '_a_' . $to . '.xlsx');
        // Clean output buffers to avoid corrupting the XLSX and ensure headers are sent properly
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        $writer = new Xlsx($ss);
        $writer->save('php://output');
        exit;
    }

    /** Descargar plantilla Excel para importar ventas (una por fila). */
    public function template(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('Plantilla import');
        $sh->fromArray(['customer_name','customer_phone','customer_email','sku','qty','unit_price'], null, 'A1');
        $sh->fromArray(['Juan Perez','3000000000','juan@example.com','ABC123',2,15000], null, 'A2');
        $sh->fromArray(['Ana Gomez','3011111111','', 'LMN456',3,12000], null, 'A3');
        foreach (range('A','F') as $col) { $sh->getColumnDimension($col)->setAutoSize(true); }
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="plantilla_import_ventas.xlsx"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        $writer = new Xlsx($ss);
        $writer->save('php://output');
        exit;
    }

    /**
     * Venta (cabecera + ítems) en JSON para modal en Movimientos.
     */
    public function show($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { http_response_code(403); exit('No autorizado'); }
        $id = (int)$id;
        $sale = (new Sale())->findByIdWithItems($id);
        header('Content-Type: application/json; charset=utf-8');
        if (!$sale) { http_response_code(404); echo json_encode(['ok'=>false,'message'=>'Venta no encontrada']); return; }
        echo json_encode(['ok'=>true, 'sale'=>$sale], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}
