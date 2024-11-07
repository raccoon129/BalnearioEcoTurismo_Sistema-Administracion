<?php
session_start();

// Si ya está autenticado, redirigir al dashboard correspondiente
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Balnearios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row login-container">
            <!-- Formulario de Login -->
            <div class="col-12 col-md-4 login-form d-flex align-items-center">
                <div class="w-100 p-4 p-md-5">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold">Sistema de Administración de Balnearios</h2>
                        <br>
                        <h3 class="fw-bold">Bienvenido</h3>
                        <p class="text-muted">Ingrese sus credenciales para continuar</p>
                    </div>

                    <div class="alert alert-danger error-message" id="error-message" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="error-text"></span>
                    </div>

                    <form id="loginForm" action="controllers/auth/login_controller.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Iniciar Sesión
                        </button>
                    </form>
                </div>
            </div>

            <!-- Imagen de fondo -->
            <div class="col-md-8 d-none d-md-block p-0">
                <div class="login-image"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Show error message if exists in URL
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error) {
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            errorMessage.style.display = 'block';
            errorText.textContent = decodeURIComponent(error);
        }
    </script>
</body>
</html>