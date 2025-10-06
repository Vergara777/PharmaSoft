<?php use App\Core\View; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= View::e($title ?? ('Factura electrónica #' . ($sale['id'] ?? ''))) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
    
    body { 
      background: #f8f9fa; 
      font-family: 'Roboto', 'Segoe UI', sans-serif;
      font-size: 14px; 
      line-height: 1.4;
      color: #333;
      padding: 20px 10px;
      margin: 0;
    }
    
    .invoice-box { 
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      padding: 0;
      background: #fff; 
      border: 1px solid #e0e6ed;
      box-shadow: 0 0 20px rgba(0,0,0,0.08);
      border-radius: 8px;
      overflow: hidden;
    }
    
    .header-section {
      background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
      padding: 20px 30px;
      color: white;
    }
    
    .title { 
      font-size: 24px; 
      font-weight: 700; 
      color: white;
      margin-bottom: 5px;
    }
    
    .meta { 
      color: rgba(255,255,255,0.9); 
      font-size: 13px; 
      margin-bottom: 5px;
    }
    
    .brand { 
      font-weight: 800; 
      font-size: 20px; 
      color: white;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .muted { color: #6c757d; }
    
    .header-section {
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #e9ecef;
    }
    
    .customer-section, .details-section {
      margin-bottom: 25px;
    }
    
    h6 {
      font-size: 14px;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    
    .table th {
      background-color: #f8f9fa;
      color: #495057;
      font-weight: 600;
      text-align: left;
      padding: 12px 15px;
      border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
      padding: 12px 15px;
      border-bottom: 1px solid #e9ecef;
      vertical-align: top;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    .totals-row th, .totals-row td { 
      border-top: 2px solid #dee2e6; 
      font-weight: 700; 
      padding-top: 15px;
      padding-bottom: 15px;
    }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    
    .print-actions {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #e9ecef;
    }
    
    /* Estilos para impresión */
    @page { 
      size: A4;
      margin: 10mm 15mm 15mm 15mm;
      
      /* Eliminar encabezados y pies de página del navegador */
      @top-center {
        content: '';
      }
      @bottom-center {
        content: '';
      }
      @top-right {
        content: '';
      }
      @bottom-right {
        content: '';
      }
      @top-left {
        content: '';
      }
      @bottom-left {
        content: '';
      }
    }
    
    @media print {
      /* Eliminar márgenes predeterminados del navegador */
      @page {
        margin: 0;
      }
      
      /* Ocultar todo excepto la factura */
      body * {
        visibility: hidden;
        margin: 0 !important;
        padding: 0 !important;
      }
      
      /* Mostrar solo la factura */
      .invoice-box,
      .invoice-box * {
        visibility: visible;
      }
      
      .invoice-box {
        position: relative;
        width: 100%;
        margin: 0 auto;
        padding: 0;
        box-shadow: none;
        border: none;
      }
      
      /* Asegurar que los colores se impriman */
      .header-section {
        background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%) !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      
      html, body { 
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-size: 12px !important;
      }
      
      /* Asegurar que el contenido sea visible */
      .invoice-box * {
        color: #000 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      
      /* Asegurar que los textos sean visibles */
      .text-muted {
        color: #6c757d !important;
      }
      
      /* Asegurar que la tabla se muestre correctamente */
      .table {
        width: 100% !important;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
      }
      
      .table th,
      .table td {
        padding: 0.5rem;
        vertical-align: top;
        border: 1px solid #dee2e6;
      }
      
      .table th {
        background-color: #f8f9fa !important;
      }
      
      /* Asegurar que la tabla se muestre correctamente */
      .table {
        width: 100% !important;
        margin-bottom: 1rem;
        color: #212529;
      }
      
      .table th,
      .table td {
        padding: 0.5rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
      }
      
      /* Asegurar que el contenido ocupe todo el ancho */
      .content-wrapper, .content {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100% !important;
      }
      
      .table th {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      
      /* Mejorar la legibilidad */
      .table td, .table th {
        padding: 5px 8px !important;
        font-size: 11px !important;
      }
      
      .title {
        font-size: 18px !important;
        margin-bottom: 5px !important;
      }
    }
  </style>
</head>
<body>
  <div class="invoice-box" style="margin:0 auto; max-width:210mm; background:#fff;">
    <!-- Encabezado -->
    <div class="header-section">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="title">
            <i class="fas fa-file-invoice mr-2 text-primary" aria-hidden="true"></i> 
            FACTURA ELECTRÓNICA
          </div>
          <div class="meta">Folio #<?= View::e($sale['id']) ?> · <?= View::e($sale['created_at'] ?? '') ?></div>
        </div>
        <div class="text-right">
          <div class="brand">PHARMASOFT</div>
          <div class="meta">Sistema de Gestión Farmacéutica</div>
          <div class="meta">RUT: 12.345.678-9</div>
        </div>
      </div>
    </div>

    <!-- Datos del Cliente y Detalles -->
    <div class="row mb-4" style="page-break-inside: avoid;">
      <div class="col-md-6">
        <div class="p-3" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; height: 100%;">
          <h6 style="font-size: 14px; font-weight: 700; color: #2c3e50; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; margin: 0 0 10px 0;">
            <i class="fas fa-user mr-1"></i> DATOS DEL CLIENTE
          </h6>
          <div style="font-size: 14px; font-weight: 600; margin-bottom: 12px; padding: 5px; background: #fff; border: 1px solid #dee2e6; border-radius: 3px;">
            <?= strtoupper(View::e($sale['customer_name'] ?? 'CONSUMIDOR FINAL')) ?>
          </div>
          <?php if (!empty($sale['customer_rut'] ?? '')): ?>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted" style="font-size: 11px;">RUT:</span>
              <span style="font-weight: 500; font-size: 11px;"><?= View::e($sale['customer_rut']) ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($sale['customer_phone'] ?? '')): ?>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted" style="font-size: 11px;">Teléfono:</span>
              <span style="font-weight: 500; font-size: 11px;"><?= View::e($sale['customer_phone']) ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($sale['customer_email'] ?? '')): ?>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted" style="font-size: 11px;">Correo:</span>
              <span style="font-weight: 500; font-size: 11px;"><?= View::e($sale['customer_email']) ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($sale['customer_address'] ?? '')): ?>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted" style="font-size: 11px;">Dirección:</span>
              <span style="font-weight: 500; font-size: 11px; text-align: right;"><?= View::e($sale['customer_address']) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="p-3" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; height: 100%;">
          <h6 style="font-size: 14px; font-weight: 700; color: #2c3e50; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; margin: 0 0 10px 0;">
            <i class="fas fa-file-invoice mr-1"></i> DETALLES DE FACTURA
          </h6>
          <div class="d-flex justify-content-between py-1">
            <span style="font-size: 12px; font-weight: 600; color: #495057;">Fecha:</span>
            <span style="font-weight: 500; font-size: 12px;"><?= date('d/m/Y H:i', strtotime($sale['created_at'] ?? '')) ?></span>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span style="font-size: 12px; font-weight: 600; color: #495057;">Folio:</span>
            <span style="font-weight: 700; font-size: 12px;">#<?= str_pad($sale['id'], 8, '0', STR_PAD_LEFT) ?></span>
          </div>
          <?php if (!empty($sale['payment_method'] ?? '')): ?>
            <div class="d-flex justify-content-between py-1">
              <span style="font-size: 12px; font-weight: 600; color: #495057;">Método de pago:</span>
              <span style="font-weight: 500; font-size: 12px;"><?= View::e($sale['payment_method']) ?></span>
            </div>
          <?php endif; ?>
          <?php $attended = trim(($sale['user_name'] ?? '') . ' ' . ((!empty($sale['user_role'])) ? '(' . $sale['user_role'] . ')' : '')); ?>
          <?php if (!empty($attended)): ?>
            <div class="d-flex justify-content-between py-1">
              <span style="font-size: 12px; font-weight: 600; color: #495057;">Atendido por:</span>
              <span style="font-weight: 500; font-size: 12px;"><?= View::e($attended) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Tabla de productos -->
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 10%;">CÓDIGO</th>
            <th style="width: 45%;">DESCRIPCIÓN</th>
            <th class="text-center" style="width: 10%;">CANT.</th>
            <th class="text-right" style="width: 15%;">P. UNITARIO</th>
            <th class="text-right" style="width: 20%;">IMPORTE</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $subtotal = 0;
          $iva = 0.19; // 19% de IVA
          $totalIva = 0;
          $total = 0;
          
          if (!empty($sale['items']) && is_array($sale['items'])): 
              foreach ($sale['items'] as $it): 
                  $lineTotal = (float)($it['line_total'] ?? 0);
                  $subtotal += $lineTotal;
                  $totalIva += $lineTotal * $iva;
          ?>
              <tr>
                <td class="text-muted"><?= View::e($it['sku'] ?? 'N/A') ?></td>
                <td>
                  <div style="font-weight: 500;"><?= View::e($it['name'] ?? '') ?></div>
                  <?php if (!empty($it['description'] ?? '')): ?>
                    <div class="text-muted small"><?= View::e($it['description']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= number_format((float)($it['qty'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right">$<?= number_format((float)($it['unit_price'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right">$<?= number_format($lineTotal, 0, ',', '.') ?></td>
              </tr>
          <?php 
              endforeach; 
          else: 
              $lineTotal = (float)($sale['total'] ?? 0);
              $subtotal = $lineTotal;
              $totalIva = $lineTotal * $iva;
          ?>
              <tr>
                <td class="text-muted"><?= View::e($sale['sku'] ?? 'N/A') ?></td>
                <td>
                  <div style="font-weight: 500;"><?= View::e($sale['name'] ?? '') ?></div>
                </td>
                <td class="text-center"><?= number_format((float)($sale['qty'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right">$<?= number_format((float)($sale['unit_price'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right">$<?= number_format($lineTotal, 0, ',', '.') ?></td>
              </tr>
          <?php 
          endif; 
          
          $total = $subtotal + $totalIva;
          ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" rowspan="4" style="border:none;">
              <div class="mt-3">
                <div class="text-muted small">Método de pago: <strong>Efectivo</strong></div>
                <div class="text-muted small">Condición de venta: <strong>Contado</strong></div>
                <div class="text-muted small">Vendedor: <strong><?= View::e($sale['user_name'] ?? 'Sistema') ?></strong></div>
              </div>
            </td>
            <th class="text-right" style="padding-right: 15px;">Subtotal:</th>
            <td class="text-right">$<?= number_format($subtotal, 0, ',', '.') ?></td>
          </tr>
          <tr>
            <th class="text-right" style="padding-right: 15px;">IVA (19%):</th>
            <td class="text-right">$<?= number_format($totalIva, 0, ',', '.') ?></td>
          </tr>
          <tr class="totals-row">
            <th class="text-right" style="padding-right: 15px;">TOTAL:</th>
            <th class="text-right">$<?= number_format($total, 0, ',', '.') ?></th>
          </tr>
          <tr>
            <td colspan="2" class="text-right">
              <small class="text-muted">
                Incluye impuestos de ley
              </small>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Mensaje de agradecimiento -->
    <div class="text-center" style="margin: 30px 0;">
      <div style="display: inline-block; text-align: center; width: 100%;">
        <div style="margin-bottom: 10px;">
          <span style="font-size: 14px; color: #4361ee; font-weight: 600;">
            ¡Gracias por su compra!
          </span>
        </div>
        <div style="margin-bottom: 15px;">
          <span style="font-size: 12px; color: #6c757d;">
            Este documento es una representación impresa de un comprobante electrónico
          </span>
        </div>
        <div style="background: #f8f9fa; padding: 15px 30px; border-radius: 8px; border: 1px solid #e9ecef; display: inline-block; text-align: center;">
          <div style="font-size: 13px; color: #2c3e50; font-weight: 500; margin-bottom: 5px;">
            Farmacia PharmaSoft
          </div>
          <div style="font-size: 12px; color: #6c757d;">
            Av. Principal #1234 · Tel: (2) 2345 6789
          </div>
        </div>
      </div>
    </div>
    
    <!-- Acciones -->
    <div class="d-flex justify-content-between mt-4 no-print print-actions">
      <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/sales">
        <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Volver a Ventas
      </a>
      <div class="d-flex gap-2">
        <button class="btn btn-info" id="btnDownloadPdf">
          <i class="fas fa-file-pdf mr-1" aria-hidden="true"></i> Descargar PDF
        </button>
        <button class="btn btn-primary" id="btnPrint">
          <i class="fas fa-print mr-1" aria-hidden="true"></i> Imprimir Factura
        </button>
      </div>
    </div>
    <!-- Pie de página -->
    <div class="footer-section text-center py-3" style="background: #f1f5fd; border-top: 1px solid #dee2e6; margin-top: 30px;">
      <div style="color: #4361ee; font-size: 13px; font-weight: 600; letter-spacing: 0.5px;">
        PharmaSoft 2025 - Sistema de Gestión Farmacéutica
      </div>
      <div style="color: #6c757d; font-size: 11px; margin-top: 5px;">
        © <?= date('Y') ?> Todos los derechos reservados
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
  <script>
    (function(){
      // Función para formatear fechas
      function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CL', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        }).replace(/\//g, '-');
      }

      // Función para limpiar el nombre del archivo
      function safeFilename(name) {
        return String(name)
          .normalize('NFD')
          .replace(/[^\w\s-]/g, '')
          .trim()
          .replace(/\s+/g, '_')
          .toUpperCase();
      }

      // Función para mostrar carga
      function showLoading(message) {
        if (window.Swal && Swal.isLoading()) {
          Swal.update({ title: message });
          return;
        }
        if (window.Swal) {
          Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
          });
        } else if (window.bannerLoading) {
          bannerLoading(true, message);
        }
      }

      // Función para ocultar carga
      function hideLoading() {
        if (window.Swal) {
          Swal.close();
        }
        if (window.bannerLoading) {
          bannerLoading(false);
        }
      }

      // Función para ocultar elementos durante la generación del PDF
      function togglePdfElements(show = true) {
        const elements = document.querySelectorAll('.print-actions, .no-print');
        elements.forEach(el => {
          if (el) {
            if (show) {
              el.style.display = '';
              el.style.visibility = '';
            } else {
              el.style.display = 'none';
              el.style.visibility = 'hidden';
            }
          }
        });
      }

      // Función principal para generar el PDF
      async function downloadPdf() {
        const el = document.querySelector('.invoice-box');
        if (!el) {
          console.error('No se encontró el elemento de la factura');
          return;
        }

        const id = '<?= View::e($sale['id']) ?>';
        const date = formatDate('<?= View::e($sale['created_at'] ?? '') ?>');
        const fileName = `FACTURA_${id}_${date || new Date().toISOString().slice(0, 10)}.pdf`;
        
        // Configuración optimizada para una sola página
        const opt = {
          margin: [10, 10, 10, 10],
          filename: safeFilename(fileName),
          image: { 
            type: 'jpeg', 
            quality: 0.95,
            useCORS: true
          },
          html2canvas: {
            scale: 1.5,
            useCORS: true,
            backgroundColor: '#ffffff',
            letterRendering: true,
            logging: false,
            allowTaint: true,
            scrollX: 0,
            scrollY: 0,
            windowWidth: el.scrollWidth,
            windowHeight: el.scrollHeight,
            onclone: (clonedDoc) => {
              // Ocultar elementos en el clon del documento
              const elements = clonedDoc.querySelectorAll('.print-actions, .no-print');
              elements.forEach(el => {
                if (el) {
                  el.style.display = 'none';
                  el.style.visibility = 'hidden';
                }
              });
            }
          },
          jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait'
          },
          pagebreak: { 
            mode: ['avoid-all', 'css'],
            avoid: 'tr, td, th, div, p, h1, h2, h3, h4, h5, h6'
          }
        };

        // Mostrar carga
        showLoading('Generando factura en PDF...');
        
        try {
          // Ocultar temporalmente los botones
          togglePdfElements(false);
          
          // Crear el PDF
          await window.html2pdf()
            .set(opt)
            .from(el)
            .save();
            
        } catch (error) {
          console.error('Error al generar el PDF:', error);
          if (window.Swal) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Ocurrió un error al generar el PDF. Por favor, intente nuevamente.'
            });
          }
        } finally {
          // Restaurar visibilidad de los botones
          togglePdfElements(true);
          hideLoading();
        }
      }
      const btn = document.getElementById('btnDownloadPdf');
      if (btn) btn.addEventListener('click', downloadPdf);
      const btnPrint = document.getElementById('btnPrint');
      if (btnPrint) btnPrint.addEventListener('click', function(){
        try { if (window.bannerLoading) bannerLoading(true, 'Preparando impresión...'); } catch(e){}
        setTimeout(function(){
          window.print();
          try { if (window.bannerLoading) bannerLoading(false); } catch(e){}
        }, 50);
      });
      // Auto-download if ?download=1
      const p = new URLSearchParams(window.location.search);
      if (p.get('download') === '1') {
        setTimeout(downloadPdf, 200);
      }
    })();
  </script>
  <!-- Post-success: clear carts and drafts so the cart is empty after sale -->
  <script>
    (function(){
      try {
        // 1) Clear floating cart implementations if present
        if (window.psCart && typeof window.psCart.clear === 'function') {
          window.psCart.clear();
        } else if (window.psCart && typeof window.psCart.refresh === 'function') {
          // Fallback to refresh if clear is not available
          window.psCart.refresh();
        }
      } catch(_){}
      try {
        if (window.cart && typeof window.cart.clear === 'function') {
          window.cart.clear();
        } else if (window.cart && typeof window.cart.update === 'function') {
          window.cart.update();
        }
      } catch(_){}

      try {
        // 2) Remove localStorage drafts/old carts for all users
        for (var i = localStorage.length - 1; i >= 0; i--) {
          var k = localStorage.key(i) || '';
          if (k.indexOf('pharmasoft_sales_draft_') === 0 ||
              k.indexOf('pharmasoft_cart_') === 0 ||
              k === 'pharmasoft_pending_cart') {
            try { localStorage.removeItem(k); } catch(_){}
          }
        }
      } catch(_){}

      try {
        // 3) Brief toast to confirm cart was cleared
        if (window.Swal && Swal.fire) {
          Swal.fire({
            icon: 'success',
            title: 'Carrito vaciado',
            text: 'El carrito se vació después de realizar la venta.',
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
          });
        } else if (typeof window.notify === 'function') {
          notify({ icon: 'success', title: 'Carrito vaciado', text: 'El carrito se vació después de realizar la venta.', position: 'top-end', timer: 3000 });
        }
      } catch(_){}
    })();
  </script>
  <?php $___fl = \App\Helpers\Flash::popAll(); if (!empty($___fl)): ?>
  <script>
    (function(){
      // Minimal top-right toast to match app behavior
      const Toast = (window.Swal ? Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4200,
        timerProgressBar: true,
        showCloseButton: true
      }) : null);
      const msgs = <?php echo json_encode($___fl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      if (Toast && Array.isArray(msgs)) {
        msgs.forEach(function(m){
          Toast.fire({ icon: m.type || 'info', title: m.title || '', text: m.message || '' });
        });
      }
    })();
  </script>
  <?php endif; ?>
</body>
</html>
