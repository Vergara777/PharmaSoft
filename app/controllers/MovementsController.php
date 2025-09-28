<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Models\AuditLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MovementsController extends Controller {
    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        // Admin-only by default
        if (!Auth::isAdmin()) { http_response_code(403); exit('No autorizado'); }

        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $per = isset($_GET['per']) && ctype_digit((string)$_GET['per']) && (int)$_GET['per'] > 0 ? min((int)$_GET['per'], 100) : 20;
        if ($per < 10) { $per = 10; }

        $from = trim((string)($_GET['from'] ?? '')) ?: null;
        $to   = trim((string)($_GET['to'] ?? '')) ?: null;
        // Raw inputs
        $raw = [
            'id' => $_GET['id'] ?? '',
            'id_from' => $_GET['id_from'] ?? '',
            'id_to' => $_GET['id_to'] ?? '',
            'entity' => $_GET['entity'] ?? '',
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'user_name' => $_GET['user_name'] ?? '',
            'q' => $_GET['q'] ?? '',
        ];
        // Validate and normalize
        $filters = [];
        $errors = [];
        $hints = [];
        $isDigits = static function($v){ $s = trim((string)$v); return ($s !== '' && ctype_digit($s)); };
        // ID exact
        if (trim((string)$raw['id']) !== '') {
            if ($isDigits($raw['id'])) { $filters['id'] = trim((string)$raw['id']); } else { $errors[] = 'El campo ID debe ser un número entero positivo.'; }
        }
        // ID desde
        if (trim((string)$raw['id_from']) !== '') {
            if ($isDigits($raw['id_from'])) { $filters['id_from'] = trim((string)$raw['id_from']); } else { $errors[] = 'El campo Desde ID debe ser un número entero positivo.'; }
        }
        // ID hasta
        if (trim((string)$raw['id_to']) !== '') {
            if ($isDigits($raw['id_to'])) { $filters['id_to'] = trim((string)$raw['id_to']); } else { $errors[] = 'El campo Hasta ID debe ser un número entero positivo.'; }
        }
        // Consistencia de rango
        if (!empty($filters['id_from']) && !empty($filters['id_to']) && (int)$filters['id_from'] > (int)$filters['id_to']) {
            $errors[] = 'El rango de ID no es válido: "Desde ID" no puede ser mayor que "Hasta ID".';
        }
        // Entity / action as free text
        $filters['entity'] = trim((string)$raw['entity']);
        $filters['action'] = trim((string)$raw['action']);
        // User ID must be digits
        if (trim((string)$raw['user_id']) !== '') {
            if ($isDigits($raw['user_id'])) { $filters['user_id'] = trim((string)$raw['user_id']); }
            else { $errors[] = 'User ID debe ser numérico (entero positivo).'; }
        }
        // user_name free text
        $filters['user_name'] = trim((string)$raw['user_name']);
        // q is for texto libre en cambios/ip/agente
        $filters['q'] = trim((string)$raw['q']);
        if ($filters['q'] !== '' && ctype_digit($filters['q']) && empty($filters['id']) && empty($filters['id_from']) && empty($filters['id_to'])) {
            $hints[] = 'Notamos que escribiste un número en "Buscar". Si deseas filtrar por ID, usa los campos ID / Desde ID / Hasta ID.';
        }

        $m = new AuditLog();
        $total = $m->countFiltered($from, $to, $filters);
        $pages = max(1, (int)ceil($total / $per));
        if ($page > $pages) { $page = $pages; }
        $offset = ($page - 1) * $per;
        $rows = $m->listPaginated($per, $offset, $from, $to, $filters);

        $this->view('movements/index', [
            'title' => 'Movimientos del sistema',
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'filters' => array_merge($raw, $filters),
            'errors' => $errors,
            'hints' => $hints,
            'pagination' => [
                'page' => $page,
                'per' => $per,
                'pages' => $pages,
                'total' => $total,
            ],
        ]);
    }

    public function export(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Auth::isAdmin()) { http_response_code(403); exit('No autorizado'); }

        // Ensure a clean output environment for binary XLSX
        // Turn off compression and buffering that could corrupt output
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('output_buffering', '0');
        @ini_set('implicit_flush', '0');
        // Temporarily suppress error display to avoid mixing HTML/text into XLSX
        $prevDisplayErrors = @ini_set('display_errors', '0');
        $prevErrorReporting = error_reporting(0);
        // Clear any previous output buffers (including BOM/whitespace)
        while (ob_get_level() > 0) { @ob_end_clean(); }

        $exportAll = isset($_GET['all']) && (string)$_GET['all'] === '1';
        $from = trim((string)($_GET['from'] ?? '')) ?: null;
        $to   = trim((string)($_GET['to'] ?? '')) ?: null;
        // Build filters similar to index()
        $raw = [
            'id' => $_GET['id'] ?? '',
            'id_from' => $_GET['id_from'] ?? '',
            'id_to' => $_GET['id_to'] ?? '',
            'entity' => $_GET['entity'] ?? '',
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'user_name' => $_GET['user_name'] ?? '',
            'q' => $_GET['q'] ?? '',
        ];
        $filters = [];
        $isDigits = static function($v){ $s = trim((string)$v); return ($s !== '' && ctype_digit($s)); };
        if ($isDigits($raw['id'] ?? '')) { $filters['id'] = trim((string)$raw['id']); }
        if ($isDigits($raw['id_from'] ?? '')) { $filters['id_from'] = trim((string)$raw['id_from']); }
        if ($isDigits($raw['id_to'] ?? '')) { $filters['id_to'] = trim((string)$raw['id_to']); }
        $filters['entity'] = trim((string)$raw['entity']);
        $filters['action'] = trim((string)$raw['action']);
        if ($isDigits($raw['user_id'] ?? '')) { $filters['user_id'] = trim((string)$raw['user_id']); }
        $filters['user_name'] = trim((string)$raw['user_name']);
        $filters['q'] = trim((string)$raw['q']);

        // Increase limits for heavy exports
        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $m = new AuditLog();
        $rows = $exportAll ? $m->allAsc($from, $to, $filters) : $m->allAsc($from, $to, $filters);
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('Movimientos');
        $headers = ['ID','Fecha','Usuario ID','Usuario','Entidad','Entidad ID','Acción','Cambios (JSON)','IP','User Agent'];
        $sh->fromArray($headers, null, 'A1');
        $r = 2;
        foreach ($rows as $row) {
            $sh->fromArray([
                $row['id'] ?? '',
                $row['created_at'] ?? '',
                $row['user_id'] ?? '',
                $row['user_name'] ?? '',
                $row['entity'] ?? '',
                $row['entity_id'] ?? '',
                $row['action'] ?? '',
                $row['changes_json'] ?? '',
                $row['ip'] ?? '',
                $row['user_agent'] ?? '',
            ], null, 'A' . $r);
            $r++;
        }
        foreach (range('A','J') as $col) { $sh->getColumnDimension($col)->setAutoSize(true); }
        // Filename reflects current filter when exporting only sales
        $isSales = (isset($filters['entity']) && strtolower((string)$filters['entity']) === 'sale');
        $filename = $isSales ? 'movimientos_ventas.xlsx' : ($exportAll ? 'movimientos_todos.xlsx' : 'movimientos.xlsx');
        // Final clean before sending headers/content
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        try {
            // Write to a temporary in-memory stream to compute size and avoid partial output
            $tmp = fopen('php://temp', 'w+b');
            if ($tmp === false) { throw new \RuntimeException('No se pudo abrir stream temporal'); }
            $writer = new Xlsx($ss);
            $writer->save($tmp);
            $size = ftell($tmp);
            if ($size !== false && $size > 0) { header('Content-Length: ' . $size); }
            rewind($tmp);
            // Stream out in chunks
            while (!feof($tmp)) {
                $buf = fread($tmp, 8192);
                if ($buf === false) break;
                echo $buf;
            }
            fclose($tmp);
        } finally {
            // Restore error reporting settings
            if ($prevDisplayErrors !== false) { @ini_set('display_errors', (string)$prevDisplayErrors); }
            @error_reporting($prevErrorReporting);
        }
        exit;
    }
}
