<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Helpers\Auth;

class LocationsController extends Controller {
    private $productModel;

    public function __construct() {
        parent::__construct();
        if (!Auth::check()) {
            $this->redirect('/auth/login');
        }
        $this->productModel = new Product();
    }

    public function index() {
        $shelf = $_GET['shelf'] ?? null;
        $products = $this->productModel->getProductsByLocation($shelf);
        
        $this->view('locations/index', [
            'title' => 'Ubicaciones de Productos',
            'shelves' => $this->productModel->getShelves(),
            'products' => $products,
            'currentShelf' => $shelf,
            'currentRow' => null
        ]);
    }

    public function map() {
        $shelvesData = [];
        $shelves = $this->productModel->getShelves();
        
        foreach ($shelves as $shelf) {
            $rows = [];
            $products = $this->productModel->getProductsByLocation($shelf);
            
            // Agrupar por fila
            foreach ($products as $product) {
                $row = $product->row ?? 1;
                if (!isset($rows[$row])) {
                    $rows[$row] = [];
                }
                $rows[$row][] = $product;
            }
            
            // Ordenar las filas numéricamente
            ksort($rows);
            
            $shelvesData[] = [
                'name' => $shelf,
                'rows' => $rows,
                'productCount' => count($products)
            ];
        }
        
        $this->view('locations/map', [
            'title' => 'Mapa de Ubicaciones',
            'shelves' => $shelvesData
        ]);
    }
}
