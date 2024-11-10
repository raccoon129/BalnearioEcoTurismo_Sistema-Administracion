<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building me-2"></i>Mi Balneario</h2>
            <button class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Editar Información
            </button>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Información General</h5>
                        <div id="balneario-info">
                            <p class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle me-2"></i>
                                Cargando información... Falta terminar este modulo
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Servicios Disponibles</h5>
                        <div id="servicios-list">
                            <p class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle me-2"></i>
                                Cargando servicios...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>