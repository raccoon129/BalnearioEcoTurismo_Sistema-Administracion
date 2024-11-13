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
    <style>
        /* Mejoras visuales para el formulario */
        .login-form {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .input-group-text {
            border: none;
            background-color: #f8f9fa;
        }

        .btn-primary {
            background-color: #0056b3;
            border: none;
            padding: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #004494;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        /* Animación para el spinner */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner {
            display: none;
            width: 1rem;
            height: 1rem;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        /* Mejoras en los mensajes de error */
        .error-message {
            display: none;
            border-left: 4px solid #dc3545;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Contenedor con dimensiones fijas para evitar rebote */
        .gif-container {
            height: 120px; /* Ajusta según el tamaño de tu GIF */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        /* Estilos para la imagen GIF */
        .logo-gif {
            max-height: 100%;
            width: auto;
            object-fit: contain; /* Mantiene la proporción */
        }

        /* Asegurar que el contenedor principal tenga suficiente espacio */
        .login-form {
            min-height: 100vh; /* Altura mínima para evitar compresión */
            overflow-y: auto; /* Permite scroll si el contenido es muy alto */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row login-container">
            <!-- Formulario de Login -->
            <div class="col-12 col-md-4 login-form d-flex align-items-center">
                <div class="w-100 p-4 p-md-5">
                    <div class="text-center mb-5">
                        <!-- Contenedor para la imagen con dimensiones fijas -->
                        <div class="gif-container mb-4">
                            <img src="assets/img/1.gif" alt="Logo Animado" class="logo-gif">
                        </div>
                        <h2 class="fw-bold">Sistema de Administración para Balnearios</h2>
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
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required autocomplete="email">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="submitButton">
                            <div class="spinner" id="submitSpinner"></div>
                            <span id="submitText">Iniciar Sesión</span>
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

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const submitButton = document.getElementById('submitButton');
            const submitSpinner = document.getElementById('submitSpinner');
            const submitText = document.getElementById('submitText');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            // Manejar envío del formulario
            loginForm.addEventListener('submit', function(e) {
                // Deshabilitar el botón y mostrar spinner
                submitButton.disabled = true;
                submitSpinner.style.display = 'inline-block';
                submitText.textContent = 'Iniciando sesión...';
            });

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });

            // Mostrar mensaje de error si existe
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error) {
                const errorMessage = document.getElementById('error-message');
                const errorText = document.getElementById('error-text');
                errorMessage.style.display = 'block';
                errorText.textContent = decodeURIComponent(error);

                // Habilitar el botón si hay error
                submitButton.disabled = false;
                submitSpinner.style.display = 'none';
                submitText.textContent = 'Iniciar Sesión';
            }
        });
    </script>
</body>
</html>