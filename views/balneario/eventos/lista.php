<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .event-card {
            transition: transform 0.2s;
            border-radius: 12px;
            overflow: hidden;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .event-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .event-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .nav-pills .nav-link {
            color: #6c757d;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
        }
        .event-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .event-card:hover .event-actions {
            opacity: 1;
        }
        .stats-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }
        .card-title a:hover {
            color: #0d6efd !important;
            transition: color 0.2s;
        }
        .event-actions .btn {
            margin-left: 0.25rem;
        }
        .event-actions .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }
        .event-actions .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../config/config.php';
    require_once '../../../controllers/balneario/eventos/EventoController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $eventoController = new EventoController($db);
    $eventos = $eventoController->obtenerEventos($_SESSION['id_balneario']);

    // Clasificar eventos
    $eventosProximos = [];
    $eventosActivos = [];
    $eventosFinalizados = [];
    $hoy = strtotime('today');

    foreach ($eventos as $evento) {
        $inicio = strtotime($evento['fecha_inicio_evento']);
        $fin = strtotime($evento['fecha_fin_evento']);
        
        if ($hoy < $inicio) {
            $eventosProximos[] = $evento;
        } elseif ($hoy > $fin) {
            $eventosFinalizados[] = $evento;
        } else {
            $eventosActivos[] = $evento;
        }
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestión de Eventos</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    Administra los eventos de tu balneario
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEvento">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Evento
            </button>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($eventosActivos); ?></h3>
                        <p class="text-muted mb-0">Eventos Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($eventosProximos); ?></h3>
                        <p class="text-muted mb-0">Próximos Eventos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($eventosFinalizados); ?></h3>
                        <p class="text-muted mb-0">Eventos Finalizados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-pills mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="pill" href="#activos">
                    <i class="bi bi-calendar-check me-2"></i>Activos
                    <span class="badge bg-primary ms-2"><?php echo count($eventosActivos); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#proximos">
                    <i class="bi bi-calendar-plus me-2"></i>Próximos
                    <span class="badge bg-success ms-2"><?php echo count($eventosProximos); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#finalizados">
                    <i class="bi bi-calendar-x me-2"></i>Finalizados
                    <span class="badge bg-secondary ms-2"><?php echo count($eventosFinalizados); ?></span>
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content">
            <!-- Eventos Activos -->
            <div id="activos" class="tab-pane fade show active">
                <div class="row g-4">
                    <?php foreach ($eventosActivos as $evento): ?>
                    <div class="col-md-6">
                        <div class="card event-card h-100">
                            <?php if ($evento['url_imagen_evento']): ?>
                            <img src="<?php echo BASE_URL . htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                 class="event-image" alt="Imagen del evento">
                            <?php endif; ?>
                            <div class="card-body position-relative">
                                <!-- Acciones rápidas -->
                                <div class="event-actions">
                                    <a href="ver.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmarEliminacion(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" 
                                            onclick="convertirABoletin(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                </div>

                                <h5 class="card-title mb-3">
                                    <a href="ver.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($evento['titulo_evento']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text mb-3">
                                    <?php echo nl2br(htmlspecialchars(substr($evento['descripcion_evento'], 0, 150))); ?>...
                                </p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success status-badge">
                                        <i class="bi bi-calendar-check me-1"></i>En curso
                                    </span>
                                    <div class="event-date">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        <?php 
                                        echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])) . 
                                             ' - ' . 
                                             date('d/m/Y', strtotime($evento['fecha_fin_evento'])); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($eventosActivos)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar2-x display-1"></i>
                            <p class="mt-3">No hay eventos activos</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Eventos Próximos -->
            <div id="proximos" class="tab-pane fade">
                <div class="row g-4">
                    <?php foreach ($eventosProximos as $evento): ?>
                    <div class="col-md-6">
                        <div class="card event-card h-100">
                            <?php if ($evento['url_imagen_evento']): ?>
                            <img src="<?php echo BASE_URL . htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                 class="event-image" alt="Imagen del evento">
                            <?php endif; ?>
                            <div class="card-body position-relative">
                                <!-- Acciones rápidas -->
                                <div class="event-actions">
                                    <a href="ver.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmarEliminacion(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" 
                                            onclick="convertirABoletin(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                </div>

                                <h5 class="card-title mb-3">
                                    <a href="ver.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($evento['titulo_evento']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text mb-3">
                                    <?php echo nl2br(htmlspecialchars(substr($evento['descripcion_evento'], 0, 150))); ?>...
                                </p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-info status-badge">
                                        <i class="bi bi-calendar-plus me-1"></i>Próximo
                                    </span>
                                    <div class="event-date">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        <?php 
                                        echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])) . 
                                             ' - ' . 
                                             date('d/m/Y', strtotime($evento['fecha_fin_evento'])); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($eventosProximos)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar2-plus display-1"></i>
                            <p class="mt-3">No hay eventos próximos</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Eventos Finalizados -->
            <div id="finalizados" class="tab-pane fade">
                <div class="row g-4">
                    <?php foreach ($eventosFinalizados as $evento): ?>
                    <div class="col-md-6">
                        <div class="card event-card h-100">
                            <?php if ($evento['url_imagen_evento']): ?>
                            <img src="<?php echo BASE_URL . htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                 class="event-image" alt="Imagen del evento">
                            <?php endif; ?>
                            <div class="card-body position-relative">
                                <!-- Acciones rápidas -->
                                <div class="event-actions">
                                    <a href="ver.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="confirmarEliminacion(<?php echo $evento['id_evento']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <h5 class="card-title mb-3">
                                    <a href="ver.php?id=<?php echo $evento['id_evento']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($evento['titulo_evento']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text mb-3">
                                    <?php echo nl2br(htmlspecialchars(substr($evento['descripcion_evento'], 0, 150))); ?>...
                                </p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-secondary status-badge">
                                        <i class="bi bi-calendar-x me-1"></i>Finalizado
                                    </span>
                                    <div class="event-date">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        <?php 
                                        echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])) . 
                                             ' - ' . 
                                             date('d/m/Y', strtotime($evento['fecha_fin_evento'])); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($eventosFinalizados)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar2-x display-1"></i>
                            <p class="mt-3">No hay eventos finalizados</p>
                        </div>
                    </div>
                    <?php endif; ?>
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
                    <form id="formEliminar" action="../../../controllers/balneario/eventos/eliminar.php" method="POST">
                        <input type="hidden" name="id_evento" id="id_evento_eliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Evento
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
                    <p>¿Desea crear un boletín con la información de este evento?</p>
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
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Validación de fechas
        $('input[name="fecha_fin"]').on('change', function() {
            const fechaInicio = $('input[name="fecha_inicio"]').val();
            const fechaFin = $(this).val();
            
            if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                toastr.error('La fecha de fin no puede ser anterior a la fecha de inicio');
                $(this).val('');
            }
        });

        // Manejar envío del formulario de nuevo evento
        $('#formEvento').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btnSubmit = $(this).find('button[type="submit"]');
            const btnText = btnSubmit.html();
            
            btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    $('#modalEvento').modal('hide');
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function() {
                toastr.error('Error al procesar la solicitud');
            })
            .always(function() {
                btnSubmit.prop('disabled', false).html(btnText);
            });
        });
    });

    function confirmarEliminacion(id) {
        $('#id_evento_eliminar').val(id);
        new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion')).show();
    }

    let datosConversion = null;

    function convertirABoletin(id) {
        $.get('../../../controllers/balneario/eventos/obtener.php', { id_evento: id })
            .done(function(response) {
                if (response.success) {
                    const evento = response.data;
                    const fechaInicioObj = new Date(evento.fecha_inicio_evento);
                    const fechaFinObj = new Date(evento.fecha_fin_evento);
                    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    
                    const fechaInicioFormateada = fechaInicioObj.toLocaleDateString('es-ES', opciones);
                    const fechaFinFormateada = fechaFinObj.toLocaleDateString('es-ES', opciones);
                    
                    const contenido = `${evento.descripcion_evento}\n\nFecha del evento: Del ${fechaInicioFormateada} hasta ${fechaFinFormateada}`;
                    
                    datosConversion = {
                        id_evento: id,
                        titulo_boletin: evento.titulo_evento,
                        contenido_boletin: contenido
                    };

                    document.getElementById('previewContenido').innerHTML = `
                        <strong>${evento.titulo_evento}</strong><br><br>
                        ${contenido.replace(/\n/g, '<br>')}
                    `;

                    new bootstrap.Modal(document.getElementById('modalConfirmarConversion')).show();
                } else {
                    toastr.error('Error al obtener los datos del evento');
                }
            })
            .fail(function() {
                toastr.error('Error al procesar la solicitud');
            });
    }

    document.getElementById('btnConfirmarConversion').addEventListener('click', function() {
        if (!datosConversion) return;

        const btn = $(this);
        const btnText = btn.html();
        btn.prop('disabled', true)
           .html('<i class="bi bi-hourglass-split me-2"></i>Procesando...');

        $.ajax({
            url: '../../../controllers/balneario/boletines/convertir_evento.php',
            method: 'POST',
            data: datosConversion,
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                setTimeout(() => window.location.href = '../boletines/lista.php', 1500);
            } else {
                toastr.error(response.message || 'Error al convertir el evento');
            }
        })
        .fail(function(xhr) {
            const errorResponse = xhr.responseJSON || {};
            toastr.error(errorResponse.message || 'Error al procesar la solicitud');
        })
        .always(function() {
            btn.prop('disabled', false).html(btnText);
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarConversion')).hide();
        });
    });
    </script>
</body>
</html>