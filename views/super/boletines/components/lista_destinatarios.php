<?php
$destinatarios = $boletinController->obtenerListaDestinatarios();

// Obtener filtros
$filtros = [
    'tipo' => $_GET['tipo'] ?? '',
    'balneario' => $_GET['balneario'] ?? '',
    'buscar' => $_GET['buscar'] ?? ''
];
?>

<!-- Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-people me-2"></i>Total Destinatarios
                </h6>
                <h2 class="mb-0"><?php echo $destinatarios['totales']['total']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-person-gear me-2"></i>SuperAdministradores
                </h6>
                <h2 class="mb-0"><?php echo $destinatarios['totales']['superadministradores']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-person-workspace me-2"></i>Administradores
                </h6>
                <h2 class="mb-0"><?php echo $destinatarios['totales']['administradores']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-person-check me-2"></i>Suscriptores
                </h6>
                <h2 class="mb-0"><?php echo $destinatarios['totales']['suscriptores']; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipo de Destinatario</label>
                <select class="form-select filtro-destinatario" id="filtroTipo">
                    <option value="">Todos los tipos</option>
                    <option value="superadmin">SuperAdministradores</option>
                    <option value="admin">Administradores</option>
                    <option value="suscriptor">Suscriptores</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Balneario</label>
                <select class="form-select filtro-destinatario" id="filtroBalneario">
                    <option value="">Todos los balnearios</option>
                    <?php foreach ($balnearios as $balneario): ?>
                    <option value="<?php echo htmlspecialchars($balneario['nombre_balneario']); ?>">
                        <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <input type="text" class="form-control filtro-destinatario" id="filtroBusqueda" 
                           placeholder="Buscar por nombre o correo...">
                    <button type="button" class="btn btn-outline-secondary" id="limpiarFiltros">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Destinatarios -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaDestinatarios">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Tipo</th>
                        <th>Balneario</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- SuperAdministradores -->
                    <?php foreach ($destinatarios['superadministradores'] as $superadmin): ?>
                    <tr class="fila-destinatario" 
                        data-tipo="superadmin" 
                        data-nombre="<?php echo htmlspecialchars($superadmin['nombre_usuario']); ?>"
                        data-correo="<?php echo htmlspecialchars($superadmin['email_usuario']); ?>">
                        <td>
                            <i class="bi bi-person-gear text-info me-2"></i>
                            <?php echo htmlspecialchars($superadmin['nombre_usuario']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($superadmin['email_usuario']); ?></td>
                        <td><span class="badge bg-info">SuperAdministrador</span></td>
                        <td>-</td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Administradores -->
                    <?php foreach ($destinatarios['administradores'] as $admin): ?>
                    <tr class="fila-destinatario"
                        data-tipo="admin"
                        data-balneario="<?php echo htmlspecialchars($admin['balneario']); ?>"
                        data-nombre="<?php echo htmlspecialchars($admin['nombre_usuario']); ?>"
                        data-correo="<?php echo htmlspecialchars($admin['email_usuario']); ?>">
                        <td>
                            <i class="bi bi-person-workspace text-success me-2"></i>
                            <?php echo htmlspecialchars($admin['nombre_usuario']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($admin['email_usuario']); ?></td>
                        <td><span class="badge bg-success">Administrador</span></td>
                        <td>
                            <i class="bi bi-water me-1"></i>
                            <?php echo htmlspecialchars($admin['balneario']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Suscriptores -->
                    <?php foreach ($destinatarios['suscriptores'] as $suscriptor): ?>
                    <tr class="fila-destinatario"
                        data-tipo="suscriptor"
                        data-balneario="<?php echo htmlspecialchars($suscriptor['balneario']); ?>"
                        data-nombre="<?php echo htmlspecialchars($suscriptor['nombre_usuario']); ?>"
                        data-correo="<?php echo htmlspecialchars($suscriptor['email_usuario']); ?>">
                        <td>
                            <i class="bi bi-person-check text-warning me-2"></i>
                            <?php echo htmlspecialchars($suscriptor['nombre_usuario']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($suscriptor['email_usuario']); ?></td>
                        <td><span class="badge bg-warning text-dark">Suscriptor</span></td>
                        <td>
                            <?php if ($suscriptor['balneario']): ?>
                                <i class="bi bi-water me-1"></i>
                                <?php echo htmlspecialchars($suscriptor['balneario']); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const filtros = document.querySelectorAll('.filtro-destinatario');
    const filas = document.querySelectorAll('.fila-destinatario');
    const btnLimpiar = document.getElementById('limpiarFiltros');
    const contadores = {
        total: document.querySelector('.card h2:nth-child(1)'),
        superadmin: document.querySelector('.card h2:nth-child(2)'),
        admin: document.querySelector('.card h2:nth-child(3)'),
        suscriptor: document.querySelector('.card h2:nth-child(4)')
    };

    // Función para filtrar destinatarios
    function filtrarDestinatarios() {
        const tipo = document.getElementById('filtroTipo').value.toLowerCase();
        const balneario = document.getElementById('filtroBalneario').value.toLowerCase();
        const busqueda = document.getElementById('filtroBusqueda').value.toLowerCase();

        let conteos = {
            total: 0,
            superadmin: 0,
            admin: 0,
            suscriptor: 0
        };

        filas.forEach(fila => {
            const tipoFila = fila.dataset.tipo;
            const balnearioFila = (fila.dataset.balneario || '').toLowerCase();
            const nombre = fila.dataset.nombre.toLowerCase();
            const correo = fila.dataset.correo.toLowerCase();

            let mostrar = true;

            // Aplicar filtros
            if (tipo && tipoFila !== tipo) mostrar = false;
            if (balneario && balnearioFila !== balneario) mostrar = false;
            if (busqueda && !nombre.includes(busqueda) && !correo.includes(busqueda)) mostrar = false;

            // Mostrar/ocultar fila
            fila.style.display = mostrar ? '' : 'none';

            // Actualizar conteos si la fila es visible
            if (mostrar) {
                conteos.total++;
                conteos[tipoFila]++;
            }
        });

        // Actualizar contadores
        contadores.total.textContent = conteos.total;
        contadores.superadmin.textContent = conteos.superadmin;
        contadores.admin.textContent = conteos.admin;
        contadores.suscriptor.textContent = conteos.suscriptor;
    }

    // Event listeners
    filtros.forEach(filtro => {
        filtro.addEventListener('input', filtrarDestinatarios);
        filtro.addEventListener('change', filtrarDestinatarios);
    });

    // Limpiar filtros
    btnLimpiar.addEventListener('click', () => {
        filtros.forEach(filtro => {
            if (filtro.tagName === 'SELECT') {
                filtro.selectedIndex = 0;
            } else {
                filtro.value = '';
            }
        });
        filtrarDestinatarios();
    });

    // Inicializar conteos
    filtrarDestinatarios();
});
</script> 