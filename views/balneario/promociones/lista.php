<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Promociones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/promociones/PromocionController.php';

    // Verificar autenticación
    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $promocionController = new PromocionController($db);
    $promociones = $promocionController->obtenerPromociones($_SESSION['id_balneario']);
    ?>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-tag me-2"></i>Promociones</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPromocion">
                <i class="bi bi-plus-lg me-2"></i>Nueva Promoción
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaPromociones" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promociones as $promocion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($promocion['titulo_promocion']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($promocion['fecha_fin_promocion'])); ?></td>
                                <td>
                                    <?php
                                    $hoy = strtotime('today');
                                    $inicio = strtotime($promocion['fecha_inicio_promocion']);
                                    $fin = strtotime($promocion['fecha_fin_promocion']);
                                    
                                    if ($hoy < $inicio):
                                    ?>
                                        <span class="badge bg-info">Próxima</span>
                                    <?php elseif ($hoy > $fin): ?>
                                        <span class="badge bg-secondary">Finalizada</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $promocion['id_promocion']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="editarPromocion(<?php echo $promocion['id_promocion']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?php echo $promocion['id_promocion']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="convertirABoletin(
                                        <?php echo $promocion['id_promocion']; ?>, 
                                        '<?php echo htmlspecialchars($promocion['titulo_promocion']); ?>', 
                                        '<?php echo htmlspecialchars($promocion['descripcion_promocion']); ?>', 
                                        '<?php echo $promocion['fecha_inicio_promocion']; ?>', 
                                        '<?php echo $promocion['fecha_fin_promocion']; ?>'
                                    )">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Promoción -->
    <div class="modal fade" id="modalPromocion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPromocion" action="../../../controllers/balneario/promociones/guardarPromocion.php" method="POST">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título de la Promoción</label>
                                <input type="text" class="form-control" name="titulo" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="4" required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formPromocion" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Promoción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar esta promoción?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" action="../../../controllers/balneario/promociones/eliminarPromocion.php" method="POST">
                        <input type="hidden" name="id_promocion" id="id_promocion_eliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Promoción
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Conversión a Boletín -->
    <div class="modal fade" id="modalConfirmarConversion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Convertir a Boletín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Desea crear un boletín con la información de esta promoción?</p>
                    <p>Se creará un borrador que podrá revisar antes de enviarlo.</p>
                    <div id="previewContenido" class="mt-3 p-3 bg-light rounded">
                        <!-- Aquí se mostrará la vista previa del contenido -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnConfirmarConversion">
                        <i class="bi bi-envelope me-2"></i>Crear Boletín
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaPromociones').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[1, 'desc']]
            });

            // Validación de fechas
            document.querySelector('input[name="fecha_fin"]').addEventListener('change', function() {
                var fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
                var fechaFin = this.value;
                
                if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                    alert('La fecha de fin no puede ser anterior a la fecha de inicio');
                    this.value = '';
                }
            });
        });

        function verDetalles(id) {
            window.location.href = 'ver.php?id=' + id;
        }

        function editarPromocion(id) {
            window.location.href = 'editar.php?id=' + id;
        }

        function confirmarEliminacion(id) {
            document.getElementById('id_promocion_eliminar').value = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion')).show();
        }

        let datosConversion = null;

        function convertirABoletin(id, titulo, descripcion, fechaInicio, fechaFin) {
            // Formatear las fechas
            const fechaInicioObj = new Date(fechaInicio);
            const fechaFinObj = new Date(fechaFin);
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            
            const fechaInicioFormateada = fechaInicioObj.toLocaleDateString('es-ES', opciones);
            const fechaFinFormateada = fechaFinObj.toLocaleDateString('es-ES', opciones);
            
            // Crear el contenido del boletín
            const contenido = `${descripcion}\n\nVálido del ${fechaInicioFormateada} hasta ${fechaFinFormateada}`;
            
            // Guardar datos para usar después de la confirmación
            datosConversion = {
                id_promocion: id,
                titulo_boletin: titulo,
                contenido_boletin: contenido
            };

            // Mostrar vista previa en el modal
            document.getElementById('previewContenido').innerHTML = `
                <strong>${titulo}</strong><br><br>
                ${contenido.replace(/\n/g, '<br>')}
            `;

            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('modalConfirmarConversion')).show();
        }

        // Manejar la confirmación
        document.getElementById('btnConfirmarConversion').addEventListener('click', function() {
            if (!datosConversion) return;

            // Enviar datos al controlador
            $.post('../../../controllers/balneario/boletines/convertir_promocion.php', datosConversion)
                .done(function(response) {
                    if (response.success) {
                        toastr.success('Promoción convertida a boletín exitosamente');
                        setTimeout(() => window.location.href = '../boletines/lista.php', 1500);
                    } else {
                        toastr.error(response.message || 'Error al convertir la promoción');
                    }
                })
                .fail(function() {
                    toastr.error('Error al procesar la solicitud');
                });

            // Cerrar el modal
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarConversion')).hide();
        });
    </script>
</body>
</html>