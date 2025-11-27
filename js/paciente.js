function mostrarSeccion(seccionId) {
            // Hide all sections
            document.querySelectorAll('.content-area > section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            document.getElementById(seccionId).style.display = 'block';
            
            // Update active nav links
            document.querySelectorAll('nav a, .sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });
            
            document.querySelector(`nav a[href="#${seccionId}"]`).classList.add('active');
            document.querySelector(`.sidebar-menu a[href="#${seccionId}"]`).classList.add('active');
        }
        
        // Initialize with search section
        document.addEventListener('DOMContentLoaded', function() {
            mostrarSeccion('busqueda');
            
            // Set up navigation
            document.querySelectorAll('nav a, .sidebar-menu a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('href').substring(1);
                    mostrarSeccion(target);
                });
            });
        });
        
        // Search functions
        function cambiarFiltro(tipo) {
            document.getElementById('tipoBusqueda').value = tipo;
            
            // Update active filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Submit form automatically
            document.getElementById('formBuscador').submit();
        }
        
        function seleccionarHospital(nombreHospital) {
            document.getElementById('hospital').value = nombreHospital;
            mostrarSeccion('cita');
            
            // Show success message
            alert('Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
        }
        
        // Policy functions
        function mostrarDetallesPoliza() {
            alert('Mostrando detalles completos de la póliza...');
            // Aquí iría la lógica para mostrar los detalles completos
        }
        
        function solicitarCambioPoliza() {
            document.getElementById('modalCambioPoliza').style.display = 'flex';
        }
        
        // Modal functions
        function abrirLogin() {
            document.getElementById('modalLogin').style.display = 'flex';
        }
        
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        // Policy change cost display
        document.getElementById('nueva_poliza').addEventListener('change', function() {
            const costoAdicional = document.getElementById('costo_adicional');
            if (this.value === 'premium') {
                costoAdicional.style.display = 'block';
            } else {
                costoAdicional.style.display = 'none';
            }
        });
        
        // Form submission for policy change
        document.getElementById('formCambioPoliza').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Solicitud de cambio de póliza enviada. Te contactaremos para confirmar los detalles.');
            cerrarModal('modalCambioPoliza');
        });
        
        // Helper function to get policy type (this would come from your backend)
        // Helper function to get policy type (this would come from your backend)
function obtenerTipoPoliza() {
    // Esta función ahora usa el valor real de PHP
    return '<?php echo obtenerTipoPoliza(); ?>';
}