function agregarUsuario() {
            document.getElementById('modalUsuario').style.display = 'flex';
        }

        function generarReporte() {
            alert('📊 Generando reporte del sistema...\nEsta función estará disponible en la próxima actualización.');
        }

        function gestionarCitas() {
            alert('📅 Redirigiendo a gestión de citas...\nMódulo en desarrollo.');
        }

        function configuracionSistema() {
            alert('⚙️ Accediendo a configuración del sistema...\nPróximamente disponible.');
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Cerrar modal haciendo clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Panel de administración cargado correctamente');
        });