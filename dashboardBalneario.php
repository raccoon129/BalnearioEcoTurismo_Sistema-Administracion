<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard para Balnearios - Sistema de Administración para Balnearios Eco Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/estilo_dashboard.css">
    <link rel="stylesheet" href="css/loader.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow">
        <div class="container-fluid">
            <span class="navbar-brand">Sistema de Administración para Balnearios Eco Turismo</span>
            <button class="btn btn-danger logout-btn" type="button" onclick="window.location.href='logout.php'">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </button>
        </div>
    </nav>

    <div class="sidebar">
        <h4><strong>Panel administrativo para Balnearios</strong></h4>
        <br>
        <a href="#" onclick="loadPage('views/dashboard/bienvenida_balneario.php', this)" id="inicioLink"><i class="fas fa-home"></i> Inicio</a>
        <a href="#" onclick="loadPage('views/balneario/mi_balneario/detalles.php', this)"><i class="fas fa-user"></i> Mi Balneario</a>
        <a href="#" onclick="loadPage('views/balneario/eventos/lista.php', this)"><i class="fas fa-calendar-alt"></i> Eventos</a>
        <a href="#" onclick="loadPage('views/balneario/promociones/lista.php', this)"><i class="fas fa-tags"></i> Promociones</a>
        <a href="#" onclick="loadPage('views/balneario/opiniones/lista.php', this)"><i class="fas fa-comments"></i> Opiniones</a>
        <a href="#" onclick="loadPage('views/balneario/boletines/lista.php', this)"><i class="fas fa-envelope"></i> Boletines</a>
    </div>

    <div class="content" style="background-color: #f8f9fa;">
        <div class="loader" id="loader" style="display: none;"></div>
        <iframe id="contentFrame" src="" style="width: 100%; height: calc(100vh - 56px); border: none; margin-top: 56px;"></iframe>
    </div>

    <script>
        function loadPage(page, element) {
            document.getElementById('loader').style.display = 'block';
            document.querySelector('.content').classList.add('content-blur');

            const links = document.querySelectorAll('.sidebar a');
            links.forEach(link => {
                link.classList.remove('active');
            });

            element.classList.add('active');

            document.getElementById('contentFrame').src = page;

            document.getElementById('contentFrame').onload = function() {
                document.getElementById('loader').style.display = 'none';
            };
        }

        window.onload = function() {
            const inicioLink = document.getElementById('inicioLink');
            loadPage('views/dashboard/bienvenida_balneario.php', inicioLink);
        };
    </script>
</body>
</html>
