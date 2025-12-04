function mostrarSeccion(seccion) {
            document.querySelectorAll('.dashboard-section').forEach(sec => {
                sec.style.display = 'none';
            });
            
            document.getElementById('seccion-' + seccion).style.display = 'block';
            
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            mostrarSeccion('dashboard');
        });