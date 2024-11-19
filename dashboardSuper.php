<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

// Verificar autenticación y rol
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

try {
    $auth->checkAuth();
    
    // Verificar que sea superadministrador
    if ($_SESSION['rol_usuario'] !== 'superadministrador') {
        header('Location: acceso_denegado.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: acceso_denegado.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard para Balnearios - Sistema de Administración para Balnearios Eco Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard_common.css">
    <link rel="stylesheet" href="css/sidebar_super.css">
    <link rel="stylesheet" href="css/loader.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="sidebar">
        <h4 class="mb-4"><strong>Panel para Administradores</strong></h4>
        <a href="#inicio" data-page="views/dashboard/bienvenida_super.php" id="inicioLink">
            <i class="fas fa-home"></i> Inicio
        </a>
        <a href="#balneario" data-page="views/super/balnearios/lista.php">
            <i class="fas fa-water"></i> Balnearios
        </a>
        <a href="#usuarios" data-page="views/super/usuarios/lista.php">
            <i class="fas fa-user-alt"></i> Usuarios
        </a>
        <a href="#eventos" data-page="views/super/eventos/lista.php">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="#promociones" data-page="views/super/promociones/lista.php">
            <i class="fas fa-tags"></i> Promociones
        </a>
        <a href="#opiniones" data-page="views/super/opiniones/lista.php">
            <i class="fas fa-comments"></i> Opiniones
        </a>
        <a href="#boletines" data-page="views/super/boletines/lista.php">
            <i class="fas fa-envelope"></i> Boletines
        </a>
    </div>

    <div class="content" style="background-color: #f8f9fa;">
        <div class="loader" id="loader" style="display: none;"></div>
        <iframe id="contentFrame" src="" style="width: 100%; height: calc(100vh - 96px); border: none;"></iframe>
    </div>

    <?php include 'components/footer.php'; ?>

    <script>
        // Mapeo de hashes a páginas
        const hashMap = {
            '#inicio': 'views/dashboard/bienvenida_super.php',
            '#balnearios': 'views/super/balnearios/lista.php',
            '#usuarios': 'views/super/usuarios/lista.php',
            '#eventos': 'views/super/eventos/lista.php',
            '#promociones': 'views/super/promociones/lista.php',
            '#opiniones': 'views/super/opiniones/lista.php',
            '#boletines': 'views/super/boletines/lista.php',
            '#configuraciones': 'views/super/configuraciones/lista.php'
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
