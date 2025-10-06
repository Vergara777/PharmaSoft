<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Registro de Accesos</h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn" title="Actualizar">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="loginLogsTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID Registro</th>
                            <th>Usuario</th>
                            <th>Tipo de Usuario</th>
                            <th>Estado de Acceso</th>
                            <th>Dirección IP</th>
                            <th>Inicio de Sesión</th>
                            <th>Cierre de Sesión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay registros de acceso</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $perPage = 20; // Default items per page
                            $counter = ($pagination['current'] - 1) * $perPage + 1;
                            foreach ($logs as $log): 
                            ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <a href="/users/view/<?= $log['user_id'] ?>">
                                                <?= htmlspecialchars($log['name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($log['name']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $log['role'] === 'admin' ? 'primary' : 'info' ?>">
                                            <?= ucfirst(htmlspecialchars($log['role'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $log['status'] === 'success' ? 'success' : 'danger' ?>">
                                            <?= $log['status'] === 'success' ? 'Éxito' : 'Fallido' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-monospace">
                                            <?= htmlspecialchars($log['ip_address']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-nowrap">
                                            <div><?= date('d/m/Y', strtotime($log['login_time'])) ?></div>
                                            <div class="text-muted small"><?= date('h:i a', strtotime($log['login_time'])) ?></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($log['logout_time'])): ?>
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="text-nowrap">
                                                    <div><?= date('d/m/Y', strtotime($log['logout_time'])) ?></div>
                                                    <div class="text-muted small"><?= date('h:i a', strtotime($log['logout_time'])) ?></div>
                                                </div>
                                                <span class="badge badge-secondary mt-1">Inactivo</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="text-muted small">- - -</div>
                                                <span class="badge badge-success mt-1">Sesión activa</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-details" 
                                                data-toggle="modal" 
                                                data-target="#loginDetailsModal"
                                                data-name="<?= htmlspecialchars($log['name']) ?>"
                                                data-role="<?= htmlspecialchars($log['role']) ?>"
                                                data-status="<?= $log['status'] === 'success' ? 'Éxito' : 'Fallido' ?>"
                                                data-ip="<?= htmlspecialchars($log['ip_address']) ?>"
                                                data-useragent="<?= htmlspecialchars($log['user_agent']) ?>"
                                                data-logintime="<?= date('d/m/Y h:i a', strtotime($log['login_time'])) ?>"
                                                data-logouttime="<?= !empty($log['logout_time']) ? date('d/m/Y h:i a', strtotime($log['logout_time'])) : 'Sesión activa' ?>">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pagination) && $pagination['total'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                            <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current'] < $pagination['total']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="text-center text-muted small">
                        Mostrando página <?= $pagination['current'] ?> de <?= $pagination['total'] ?> 
                        (Total: <?= number_format($pagination['total_items'], 0, ',', '.') ?> registros)
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para detalles del registro -->
<div class="modal fade" id="loginDetailsModal" tabindex="-1" role="dialog" aria-labelledby="loginDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loginDetailsModalLabel">Detalles del Inicio de Sesión</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row mb-3">
                            <h6 class="font-weight-bold">Información del Usuario</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th class="bg-light" style="width: 40%">Nombre:</th>
                                    <td id="detail-name"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Tipo de Usuario:</th>
                                    <td id="detail-role"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Estado de Acceso:</th>
                                    <td id="detail-status"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Dirección IP:</th>
                                    <td id="detail-ip"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Navegador/Dispositivo:</th>
                                    <td id="detail-useragent"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Hora de Inicio:</th>
                                    <td id="detail-login-time"></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Hora de Cierre:</th>
                                    <td id="detail-logout-time"></td>
                                </tr>
                            </table>
{{ ... }}
<!-- jQuery (required for Bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
    // Botón de actualizar
    $('#refreshBtn').on('click', function(e) {
        e.preventDefault();
        window.location.reload();
    });

    // Inicializar el modal de detalles
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var modal = $('#loginDetailsModal');
        
        // Actualizar el contenido del modal con los datos del botón
        modal.find('#detail-name').text(button.data('name') || 'No disponible');
        modal.find('#detail-role').text(button.data('role') || 'No disponible');
        
        // Actualizar estado con el estilo correcto
        var status = button.data('status') || 'Fallido';
        var statusClass = status === 'Éxito' ? 'success' : 'danger';
        modal.find('#detail-status').html(
            '<span class="badge badge-' + statusClass + '">' + status + '</span>'
        );
        
        // Actualizar el resto de la información
        modal.find('#detail-ip').text(button.data('ip') || 'No disponible');
        modal.find('#detail-useragent').text(button.data('useragent') || 'No disponible');
        modal.find('#detail-login-time').text(button.data('logintime') || 'No disponible');
        
        // Actualizar el estado de cierre de sesión para que coincida con la columna
        var logoutTime = button.data('logouttime');
        if (logoutTime && logoutTime !== 'Sesión activa') {
            modal.find('#detail-logout-time').html(
                '<div class="text-nowrap">' +
                '   <div>' + logoutTime.split(' ')[0] + '</div>' +
                '   <div class="text-muted small">' + logoutTime.split(' ').slice(1).join(' ') + '</div>' +
                '</div>' +
                '<span class="badge bg-secondary mt-1">Inactivo</span>'
            );
        } else {
            modal.find('#detail-logout-time').html(
                '<div class="text-muted small">- - -</div>' +
                '<span class="badge bg-success mt-1">Sesión activa</span>'
            );
        }
        
        // Mostrar el modal (nueva sintaxis de Bootstrap 5)
        var modalInstance = new bootstrap.Modal(modal[0]);
        modalInstance.show();
    });
});
} else {
    console.error('jQuery no se ha cargado correctamente');
}
</script>

