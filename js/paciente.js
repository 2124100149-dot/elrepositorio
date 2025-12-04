function mostrarSeccion(seccionId) {
    // Ocultar todas las secciones
    document.querySelectorAll('.content-section').forEach(seccion => {
        seccion.classList.remove('active');
    });

    // Mostrar la sección seleccionada
    document.getElementById(seccionId).classList.add('active');

    // Actualizar menú activo en sidebar
    document.querySelectorAll('.sidebar-menu a').forEach(enlace => {
        enlace.classList.remove('active');
    });

    // Encontrar y activar el enlace correspondiente en sidebar
    document.querySelectorAll('.sidebar-menu a').forEach(enlace => {
        if (enlace.getAttribute('href') === '#' + seccionId) {
            enlace.classList.add('active');
        }
    });
}

function cambiarFiltro(tipo) {
    document.getElementById('tipoBusqueda').value = tipo;

    // Actualizar botones de filtro activos
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    // Enviar formulario automáticamente
    document.getElementById('formBuscador').submit();
}

function seleccionarHospital(nombreHospital) {
    // Buscar el hospital en el select de citas y seleccionarlo
    const selectHospital = document.getElementById('hospital_fk');
    if (selectHospital) {
        for (let i = 0; i < selectHospital.options.length; i++) {
            if (selectHospital.options[i].text === nombreHospital) {
                selectHospital.selectedIndex = i;
                break;
            }
        }
    }

    // Cambiar a la sección de citas
    mostrarSeccion('cita');

    // Mostrar mensaje de confirmación
    alert('✅ Hospital "' + nombreHospital + '" seleccionado. Ahora completa el formulario de cita.');
}

// Configurar fecha mínima para la cita (hoy)
document.addEventListener('DOMContentLoaded', function () {
    const fechaInput = document.getElementById('fecha_cita');
    if (fechaInput) {
        const hoy = new Date().toISOString().split('T')[0];
        fechaInput.min = hoy;
    }

    // Configurar hora actual como mínima
    const horaInput = document.getElementById('hora_cita');
    if (horaInput) {
        const ahora = new Date();
        const horas = ahora.getHours().toString().padStart(2, '0');
        const minutos = ahora.getMinutes().toString().padStart(2, '0');
        const horaActual = `${horas}:${minutos}`;
        horaInput.min = horaActual;
    }

    // Inicializar con la sección de búsqueda activa
    mostrarSeccion('busqueda');
});

// Función para mostrar detalles completos de póliza
function mostrarDetallesPoliza() {
    alert('📋 Mostrando detalles completos de la póliza...');
    // Aquí podrías agregar lógica para expandir/contraer detalles
}

// Función para solicitar cambio de póliza
function solicitarCambioPoliza() {
    alert('🔄 Función de cambio de póliza - Esta funcionalidad estará disponible pronto.');
    // Aquí podrías abrir un modal o redirigir a un formulario
}

// Validación del formulario de cita antes de enviar
document.addEventListener('DOMContentLoaded', function () {
    const formCita = document.querySelector('form[method="POST"]');
    if (formCita) {
        formCita.addEventListener('submit', function (e) {
            const hospital = document.getElementById('hospital_fk').value;
            const fecha = document.getElementById('fecha_cita').value;
            const hora = document.getElementById('hora_cita').value;
            const motivo = document.getElementById('motivo_consulta').value;

            if (!hospital || !fecha || !hora || !motivo) {
                e.preventDefault();
                alert('❌ Por favor completa todos los campos obligatorios.');
                return false;
            }

            // Validar que la fecha no sea en el pasado
            const fechaCita = new Date(fecha + 'T' + hora);
            const ahora = new Date();
            if (fechaCita < ahora) {
                e.preventDefault();
                alert('❌ No puedes agendar una cita en el pasado.');
                return false;
            }

            return true;
        });
    }
});

// Helper function to get policy type (from PHP session)
function obtenerTipoPoliza() {
    // Esta información debería venir de tu backend PHP
    // Por ahora devolvemos un valor por defecto
    return '<?php echo !empty($datos_usuario["tipo_poliza"]) ? $datos_usuario["tipo_poliza"] : "Normal"; ?>';
}

// Función para copiar número de póliza al portapapeles
function copiarNumeroPoliza() {
    const numeroPoliza = '<?php echo !empty($datos_usuario["poliza_id"]) ? $datos_usuario["poliza_id"] : ""; ?>';
    if (numeroPoliza) {
        navigator.clipboard.writeText(numeroPoliza).then(function () {
            alert('✅ Número de póliza copiado: ' + numeroPoliza);
        }, function (err) {
            alert('❌ Error al copiar el número de póliza');
        });
    } else {
        alert('❌ No hay número de póliza disponible');
    }
}

// Función para mostrar/ocultar contraseña (si aplica)
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    }
}

// Navegación con teclado
document.addEventListener('keydown', function (e) {
    // Ctrl + 1: Búsqueda
    if (e.ctrlKey && e.key === '1') {
        e.preventDefault();
        mostrarSeccion('busqueda');
    }
    // Ctrl + 2: Perfil
    else if (e.ctrlKey && e.key === '2') {
        e.preventDefault();
        mostrarSeccion('perfil');
    }
    // Ctrl + 3: Póliza
    else if (e.ctrlKey && e.key === '3') {
        e.preventDefault();
        mostrarSeccion('poliza');
    }
    // Ctrl + 4: Cita
    else if (e.ctrlKey && e.key === '4') {
        e.preventDefault();
        mostrarSeccion('cita');
    }
});

// Función para buscar hospitales con Enter
document.addEventListener('DOMContentLoaded', function () {
    const busquedaInput = document.querySelector('input[name="busqueda"]');
    if (busquedaInput) {
        busquedaInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('formBuscador').submit();
            }
        });
    }
});

// Auto-guardado del formulario de cita (opcional)
let autoSaveTimer;
document.addEventListener('DOMContentLoaded', function () {
    const formElements = document.querySelectorAll('#cita input, #cita select, #cita textarea');
    formElements.forEach(element => {
        element.addEventListener('input', function () {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function () {
                // Aquí podrías implementar auto-guardado local
                console.log('Cambios detectados en el formulario de cita');
            }, 2000);
        });
    });
});

// Función para limpiar formulario de cita
function limpiarFormularioCita() {
    if (confirm('¿Estás seguro de que quieres limpiar el formulario de cita?')) {
        document.getElementById('hospital_fk').selectedIndex = 0;
        document.getElementById('fecha_cita').value = '';
        document.getElementById('hora_cita').value = '';
        document.getElementById('motivo_consulta').value = '';
    }
}

// Tooltips informativos
document.addEventListener('DOMContentLoaded', function () {
    // Agregar tooltips a elementos importantes
    const tooltipElements = document.querySelectorAll('.profile-group label, .form-group label');
    tooltipElements.forEach(element => {
        element.title = 'Haz clic para más información';
        element.style.cursor = 'help';
    });
});

function mostrarOpciones() {
    const tipo = document.getElementById('tipo_cita').value;
    const opcionHospital = document.getElementById('opcion-hospital');
    const opcionMedico = document.getElementById('opcion-medico');

    // Ocultar todo primero
    opcionHospital.style.display = 'none';
    opcionMedico.style.display = 'none';

    // Mostrar la opción seleccionada
    if (tipo === 'hospital') {
        opcionHospital.style.display = 'block';
        document.getElementById('hospital_fk').required = true;
        document.getElementById('medico_fk').required = false;
    } else if (tipo === 'medico') {
        opcionMedico.style.display = 'block';
        document.getElementById('hospital_fk').required = false;
        document.getElementById('medico_fk').required = true;
    }
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    mostrarOpciones();
});

function mostrarSeccion(seccionId) {
    // Ocultar todas las secciones
    document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
    });

    // Mostrar la sección seleccionada
    document.getElementById(seccionId).classList.add('active');

    // Actualizar menú activo
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + seccionId) {
            link.classList.add('active');
        }
    });
}

function cambiarFiltro(tipo) {
    document.getElementById('tipoBusqueda').value = tipo;
    document.getElementById('formBuscador').submit();
}

// Función para filtrar médicos según especialidad seleccionada
function filtrarMedicos() {
    const especialidadSelect = document.getElementById('especialidad_cita');
    const medicoSelect = document.getElementById('medico_fk');
    const especialidadId = especialidadSelect.value;

    // Si no hay especialidad seleccionada, limpiar select
    if (!especialidadId) {
        medicoSelect.innerHTML = '<option value="">Primero selecciona una especialidad</option>';
        return;
    }

    // Filtrar opciones de médicos que tengan la especialidad seleccionada
    const options = medicoSelect.querySelectorAll('option');
    medicoSelect.innerHTML = '<option value="">Selecciona un médico</option>';

    options.forEach(option => {
        if (option.value) {
            const especialidades = option.getAttribute('data-especialidades') || '';
            if (especialidades.includes(especialidadSelect.options[especialidadSelect.selectedIndex].text)) {
                medicoSelect.appendChild(option.cloneNode(true));
            }
        }
    });

    // Si no hay médicos para esa especialidad
    if (medicoSelect.options.length === 1) {
        medicoSelect.innerHTML = '<option value="">No hay médicos disponibles para esta especialidad</option>';
    }
}

// Inicializar filtro si hay especialidad seleccionada
document.addEventListener('DOMContentLoaded', function () {
    const especialidadSelect = document.getElementById('especialidad_cita');
    if (especialidadSelect.value) {
        filtrarMedicos();
    }

    // Establecer fecha mínima como hoy
    const fechaInput = document.getElementById('fecha_cita');
    const today = new Date().toISOString().split('T')[0];
    fechaInput.min = today;
});

// Función para filtrar citas por estado
function filtrarCitas() {
    const filtro = document.getElementById('filtro-estado').value;
    const citas = document.querySelectorAll('.cita-item');

    citas.forEach(cita => {
        const estado = cita.getAttribute('data-estado');

        if (filtro === 'todas' || estado === filtro) {
            cita.style.display = 'block';
        } else {
            cita.style.display = 'none';
        }
    });
}

// Función para mostrar/ocultar secciones
function mostrarSeccion(seccionId) {
    // Ocultar todas las secciones
    document.querySelectorAll('.content-section').forEach(seccion => {
        seccion.classList.remove('active');
    });

    // Mostrar la sección seleccionada
    document.getElementById(seccionId).classList.add('active');

    // Actualizar menú activo
    document.querySelectorAll('.sidebar-menu a').forEach(enlace => {
        enlace.classList.remove('active');
        if (enlace.getAttribute('href') === '#' + seccionId) {
            enlace.classList.add('active');
        }
    });
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    // Si hay parámetro en la URL, mostrar esa sección
    const hash = window.location.hash.substring(1);
    if (hash) {
        mostrarSeccion(hash);
    }

    // Configurar evento para cambiar sección desde menú
    document.querySelectorAll('.sidebar-menu a').forEach(enlace => {
        enlace.addEventListener('click', function (e) {
            e.preventDefault();
            const seccionId = this.getAttribute('href').substring(1);
            mostrarSeccion(seccionId);
            // Actualizar URL sin recargar página
            history.pushState(null, null, '#' + seccionId);
        });
    });
});