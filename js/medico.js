function mostrarSeccion(seccion) {
            // Ocultar todas las secciones
            document.querySelectorAll('.dashboard-section').forEach(sec => {
                sec.style.display = 'none';
            });
            
            // Mostrar la sección seleccionada
            document.getElementById('seccion-' + seccion).style.display = 'block';
            
            // Actualizar menú activo
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Mostrar dashboard por defecto
        document.addEventListener('DOMContentLoaded', function() {
            mostrarSeccion('dashboard');
        });