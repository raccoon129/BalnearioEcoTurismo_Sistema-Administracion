<footer class="pie-pagina">
    <div class="contenido-pie">
        <!-- Contenido principal que siempre está visible -->
        <div class="contenido-principal">
            Sistema de Administración para Balnearios v0.9 rev T3 2024
            <span class="separador">|</span>
            Proyecto EcoTurismo - Hidalgo
        </div>
        
        <!-- Contenido que se revela al pasar el cursor -->
        <div class="contenido-expandible">
            Desarrollado por VGB & EMS
            <span class="separador">|</span>
            Algunos derechos reservados
            <span class="separador">|</span>
            Ingeniería en Sistemas Computacionales ITESHU
        </div>
    </div>
</footer>

<style>
/* Estilos base del pie de página */
.pie-pagina {
    /* Posicionamiento fijo en la parte inferior */
    position: fixed;
    bottom: 0;
    width: 100%;
    
    /* Estilos visuales base */
    background-color: #343a40;
    color: #ffffff;
    font-size: 0.9rem;
    
    /* Control de comportamiento */
    z-index: 1000;
    overflow: hidden;
    
    /* Altura inicial y transición suave */
    height: 40px;
    transition: height 0.3s ease-in-out;
    padding: 0;
}

/* Cambio de altura al pasar el cursor */
.pie-pagina:hover {
    height: 53px; /* Aumentada para mejor espaciado */
}

/* Contenedor principal del contenido */
.contenido-pie {
    /* Centrado y espaciado */
    padding: 12px 20px;
    text-align: center;
    
    /* Transición suave para el desplazamiento */
    transform: translateY(0);
    transition: transform 0.3s ease-in-out;
}

/* Ajuste de posición al hacer hover */
.pie-pagina:hover .contenido-pie {
    transform: translateY(-8px); /* Reducido para menor desplazamiento */
}

/* Estilos del contenido principal */
.contenido-principal {
    font-weight: 500;
    margin-bottom: 10px; /* Aumentado para mejor espaciado */
    line-height: 1.2; /* Mejor altura de línea */
}

/* Estilos del contenido expandible */
.contenido-expandible {
    font-size: 0.85rem;
    opacity: 0;
    transform: translateY(5px); /* Posición inicial ligeramente abajo */
    transition: all 0.3s ease-in-out;
    line-height: 1.2; /* Mejor altura de línea */
    margin-top: 10px; /* Espacio adicional arriba */
}

/* Mostrar contenido expandible al hacer hover */
.pie-pagina:hover .contenido-expandible {
    opacity: 1;
    transform: translateY(0);
}

/* Separadores entre elementos */
.separador {
    margin: 0 10px;
    opacity: 0.5;
    display: inline-block;
}

/* Ajuste del contenido principal para el footer */
.content {
    margin-bottom: 40px; /* Igual a la altura inicial del pie de página */
}

/* Ajustes para dispositivos móviles */
@media (max-width: 768px) {
    /* Reducción de tamaños de fuente */
    .contenido-principal, 
    .contenido-expandible {
        font-size: 0.8rem;
    }
    
    /* Reducción de márgenes en separadores */
    .separador {
        margin: 0 5px;
    }
    
    /* Ajuste de alturas para móviles */
    .pie-pagina {
        height: 35px;
    }
    
    .pie-pagina:hover {
        height: 65px;
    }
}

/* Animación para el contenido expandible */
.contenido-expandible {
    /* Transición suave para todos los cambios */
    transition: 
        opacity 0.3s ease-in-out,
        transform 0.3s ease-in-out;
}

/* Estado hover del contenido expandible */
.pie-pagina:hover .contenido-expandible {
    opacity: 0.9; /* Ligeramente transparente */
}
</style> 