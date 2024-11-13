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
        <a href="#inicio" data-page="views/dashboard/bienvenida_balneario.php" id="inicioLink">
            <i class="fas fa-home"></i> Inicio
        </a>
        <a href="#mi-balneario" data-page="views/balneario/mi_balneario/ver.php">
            <i class="fas fa-user"></i> Mi Balneario
        </a>
        <a href="#eventos" data-page="views/balneario/eventos/lista.php">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="#promociones" data-page="views/balneario/promociones/lista.php">
            <i class="fas fa-tags"></i> Promociones
        </a>
        <a href="#opiniones" data-page="views/balneario/opiniones/lista.php">
            <i class="fas fa-comments"></i> Opiniones
        </a>
        <a href="#boletines" data-page="views/balneario/boletines/lista.php">
            <i class="fas fa-envelope"></i> Boletines
        </a>
    </div>

    <div class="content" style="background-color: #f8f9fa;">
        <div class="loader" id="loader" style="display: none;"></div>
        <iframe id="contentFrame" src="" style="width: 100%; height: calc(100vh - 56px); border: none; margin-top: 56px;"></iframe>
    </div>

    <script>
        // Mapeo de hashes a páginas
        const hashMap = {
            '#inicio': 'views/dashboard/bienvenida_balneario.php',
            '#mi-balneario': 'views/balneario/mi_balneario/ver.php',
            '#eventos': 'views/balneario/eventos/lista.php',
            '#promociones': 'views/balneario/promociones/lista.php',
            '#opiniones': 'views/balneario/opiniones/lista.php',
            '#boletines': 'views/balneario/boletines/lista.php'
        };

        function loadPage(page, element) {
            document.getElementById('loader').style.display = 'block';
            document.querySelector('.content').classList.add('content-blur');

            // Remover clase active de todos los enlaces
            const links = document.querySelectorAll('.sidebar a');
            links.forEach(link => {
                link.classList.remove('active');
            });

            // Añadir clase active al enlace seleccionado
            element.classList.add('active');

            // Cargar la página en el iframe
            document.getElementById('contentFrame').src = page;

            document.getElementById('contentFrame').onload = function() {
                document.getElementById('loader').style.display = 'none';
                document.querySelector('.content').classList.remove('content-blur');
            };
        }

        // Manejar clics en los enlaces
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', function(e) {
                const page = this.getAttribute('data-page');
                loadPage(page, this);
            });
        });

        // Función para cargar página basada en hash
        function loadPageFromHash() {
            const hash = window.location.hash || '#inicio';
            const page = hashMap[hash];
            if (page) {
                const link = document.querySelector(`a[href="${hash}"]`);
                if (link) {
                    loadPage(page, link);
                }
            }
        }

        // Escuchar cambios en el hash
        window.addEventListener('hashchange', loadPageFromHash);

        // Cargar página inicial
        window.onload = function() {
            loadPageFromHash();
        };

        // Si no hay hash, establecer el hash inicial
        if (!window.location.hash) {
            window.location.hash = '#inicio';
        }
    </script>
</body>
</html>
