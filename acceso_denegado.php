<?php
// Destruir la sesión si existe
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .countdown {
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 1rem;
        }
        .progress {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="bi bi-exclamation-triangle-fill error-icon"></i>
        <h2 class="mb-4">¡Acceso Denegado!</h2>
        <p class="mb-3">No tienes permiso para acceder a esta sección o tu sesión ha expirado.</p>
        <div class="countdown">
            Redirigiendo en <span id="timer">3</span> segundos...
        </div>
        <div class="progress" style="height: 4px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                 role="progressbar" 
                 style="width: 100%">
            </div>
        </div>
    </div>

    <script>
        // Temporizador de redirección
        let timeLeft = 3;
        const timerElement = document.getElementById('timer');
        
        const countdown = setInterval(() => {
            timeLeft--;
            timerElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = 'index.php';
            }
        }, 1000);

        // Prevenir navegación hacia atrás
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function () {
            history.pushState(null, null, document.URL);
        });
    </script>
</body>
</html>

