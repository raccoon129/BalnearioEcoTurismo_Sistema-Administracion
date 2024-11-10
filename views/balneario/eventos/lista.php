<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS y JS -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
</head>
<body class="bg-light p-4">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/eventos/EventoController.php';

    // Verificar autenticación
    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $eventoController = new EventoController($db);
    $eventos = $eventoController->obtenerEventos($_SESSION['id_balneario']);
    ?>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-event me-2"></i>Eventos</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEvento">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Evento
            </button>
        </div>

        <script>
            // Configuración de Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Mostrar mensajes si existen
            <?php if (isset($_GET['success'])): ?>
                toastr.success('<?php echo htmlspecialchars($_GET['success']); ?>', 'Éxito');
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                toastr.error('<?php echo htmlspecialchars($_GET['error']); ?>', 'Error');
            <?php endif; ?>
        </script>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaEventos" class="table table-hover">
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
                            <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evento['titulo_evento']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($evento['fecha_fin_evento'])); ?></td>
                                <td>
                                    <?php
                                    $hoy = strtotime('today');
                                    $inicio = strtotime($evento['fecha_inicio_evento']);
                                    $fin = strtotime($evento['fecha_fin_evento']);
                                    
                                    if ($hoy < $inicio):
                                    ?>
                                        <span class="badge bg-info">Próximo</span>
                                    <?php elseif ($hoy > $fin): ?>
                                        <span class="badge bg-secondary">Finalizado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">En curso</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="editarEvento(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="convertirABoletin(<?php echo $evento['id_evento']; ?>)">
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

    <!-- Modal para Nuevo Evento -->
    <div class="modal fade" id="modalEvento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEvento" action="../../../controllers/balneario/eventos/guardar.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título del Evento</label>
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

                            <div class="col-12">
                                <label class="form-label">Imagen del Evento</label>
                                <input type="file" class="form-control" name="imagen" accept="image/*">
                                <div class="form-text">
                                    Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEvento" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Evento
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
                    <p>¿Está seguro que desea eliminar este evento?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" action="../../controllers/balneario/eventos/eliminar.php" method="POST">
                        <input type="hidden" name="id_evento" id="id_evento_eliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Evento
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Convertir a Boletín -->
    <div class="modal fade" id="modalConvertirBoletin" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Convertir a Boletín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Desea crear un boletín con la información de este evento?</p>
                    <p>Se creará un borrador que podrá revisar antes de enviarlo.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="../../controllers/balneario/eventos/convertir_boletin.php" method="POST">
                        <input type="hidden" name="id_evento" id="id_evento_convertir">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope me-2"></i>Crear Boletín
                        </button>
                    </form>
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
            $('#tablaEventos').DataTable({
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

        function editarEvento(id) {
            window.location.href = 'editar.php?id=' + id;
        }

        function confirmarEliminacion(id) {
            document.getElementById('id_evento_eliminar').value = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion')).show();
        }

        function convertirABoletin(id) {
            document.getElementById('id_evento_convertir').value = id;
            new bootstrap.Modal(document.getElementById('modalConvertirBoletin')).show();
        }
    </script>
</body>
</html>