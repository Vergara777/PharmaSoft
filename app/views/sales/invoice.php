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
    body { background: #f5f6f7; font-size: 14px; }
    .invoice-box { max-width: 960px; margin: 24px auto; background: #fff; padding: 28px; border: 1px solid #e9ecef; border-radius: 8px; }
    .title { font-size: 22px; font-weight: 700; }
    .meta { color: #666; font-size: 13px; }
    .brand { font-weight: 800; font-size: 18px; }
    .muted { color: #6c757d; }
    .hr { height:1px; background:#e9ecef; margin: 14px 0; }
    table.table-sm th, table.table-sm td { padding-top: .5rem; padding-bottom: .5rem; }
    .totals-row th, .totals-row td { border-top: 2px solid #dee2e6; font-weight: 700; }
    /* Print improvements */
    @page { size: A4; margin: 12mm; }
    @media print {
      html, body { height: auto; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .no-print { display: none !important; }
      .invoice-box { border: none; box-shadow: none; margin: 0; padding: 0; max-width: unset; }
    }
  </style>
</head>
<body>
  <div class="invoice-box">
    <div class="d-flex justify-content-between align-items-start mb-2">
      <div>
        <div class="title"><i class="fas fa-file-invoice mr-2 text-primary" aria-hidden="true"></i> Factura electrónica</div>
        <div class="meta">Folio #<?= View::e($sale['id']) ?> · <?= View::e($sale['created_at'] ?? '') ?></div>
      </div>
      <div class="text-right">
        <div class="brand">PharmaSoft</div>
        <div class="meta">Sistema de Ventas</div>
      </div>
    </div>

    <div class="hr"></div>

    <div class="row">
      <div class="col-md-6">
        <h6>Cliente</h6>
        <div><strong><?= View::e($sale['customer_name'] ?? 'Consumidor final') ?></strong></div>
        <div class="meta">Tel: <?= View::e($sale['customer_phone'] ?? '') ?></div>
        <?php if (!empty($sale['customer_email'] ?? '')): ?>
          <div class="meta">Email: <?= View::e($sale['customer_email']) ?></div>
        <?php endif; ?>
      </div>
      <div class="col-md-6 text-md-right">
        <h6>Detalles</h6>
        <div>Fecha: <?= View::e($sale['created_at'] ?? '') ?></div>
        <div>Folio: #<?= View::e($sale['id']) ?></div>
        <?php $attended = trim(($sale['user_name'] ?? '') . ' ' . ((!empty($sale['user_role'])) ? '(' . $sale['user_role'] . ')' : '')); ?>
        <?php if (!empty($attended)): ?>
          <div>Atendido por: <?= View::e($attended) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-responsive mt-3">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Producto</th>
            <th class="text-right">Cantidad</th>
            <th class="text-right">P. Unitario</th>
            <th class="text-right">Importe</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($sale['items']) && is_array($sale['items'])): ?>
            <?php foreach ($sale['items'] as $it): ?>
              <tr>
                <td><?= View::e($it['sku'] ?? '') ?></td>
                <td>
                  <span><?= View::e($it['name'] ?? '') ?></span>
                </td>
                <td class="text-right"><?= View::e($it['qty'] ?? 0) ?></td>
                <td class="text-right">$<?= number_format((float)($it['unit_price'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right">$<?= number_format((float)($it['line_total'] ?? 0), 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
              <tr>
                <td><?= View::e($sale['sku'] ?? '') ?></td>
                <td>
                  <span><?= View::e($sale['name'] ?? '') ?></span>
                </td>
                <td class="text-right"><?= View::e($sale['qty'] ?? 0) ?></td>
                <td class="text-right">$<?= number_format((float)($sale['unit_price'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right">$<?= number_format((float)($sale['total'] ?? 0), 0, ',', '.') ?></td>
              </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="totals-row">
            <th colspan="4" class="text-right">Total</th>
            <th class="text-right">$<?= number_format((float)($sale['total'] ?? 0), 0, ',', '.') ?></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="d-flex justify-content-between mt-4 no-print">
      <a class="btn btn-secondary" href="<?= BASE_URL ?>/sales"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Volver</a>
      <div class="d-flex gap-2">
        <button class="btn btn-info mr-2" id="btnDownloadPdf"><i class="fas fa-file-pdf mr-1" aria-hidden="true"></i> Descargar PDF</button>
        <button class="btn btn-primary" id="btnPrint"><i class="fas fa-print mr-1" aria-hidden="true"></i> Imprimir</button>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
  <script>
    (function(){
      function safeFilename(name) {
        return String(name).replace(/[^\w\-\.#]+/g,'_');
      }
      function downloadPdf(){
        const el = document.querySelector('.invoice-box');
        const id = '<?= View::e($sale['id']) ?>';
        const ts = '<?= View::e(substr($sale['created_at'] ?? '',0,10)) ?>';
        const opt = {
          margin: [10,10,10,10],
          filename: safeFilename(`Factura_${id}_${ts}.pdf`),
          image: { type: 'jpeg', quality: 0.98 },
          // Force white background to avoid patterned canvas capture
          html2canvas: {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            dpi: 192,
            logging: false
          },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
          pagebreak: { mode: ['css','legacy'] }
        };
        try { if (window.bannerLoading) bannerLoading(true, 'Generando PDF...'); } catch(e){}
        // Temporarily force white body background during render
        const prevBg = document.body.style.backgroundColor;
        document.body.style.backgroundColor = '#ffffff';
        window.html2pdf().set(opt).from(el).save().finally(function(){
          document.body.style.backgroundColor = prevBg || '';
          try { if (window.bannerLoading) bannerLoading(false); } catch(e){}
        });
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
