function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
    // Limpiar parámetros de URL
    window.history.pushState({}, document.title, window.location.pathname);
}

function toggleCamposEdicion() {
    const rol = document.getElementById('rol').value;
    const camposPaciente = document.getElementById('camposPaciente');

    if (rol === 'Usuario') {
        camposPaciente.style.display = 'block';
    } else {
        camposPaciente.style.display = 'none';
    }
}

function calcularEdad() {
    const fechaNacimiento = document.getElementById('fecha_nacimiento').value;
    if (fechaNacimiento) {
        const fechaNac = new Date(fechaNacimiento);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }

        document.getElementById('edad').value = edad;
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function () {
    toggleCamposEdicion();
});
window.onclick = function (event) {
    const modal = document.getElementById('modalEditar');
    if (event.target == modal) {
        cerrarModal();
    }
}

// Cerrar con tecla ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModal();
    }
});

function actualizarEntidadFK() {
    const municipioSelect = document.getElementById('municipio_fk');
    const entidadInput = document.getElementById('entidad_fk');

    if (municipioSelect.selectedIndex > 0) {
        const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
        const entidad = selectedOption.getAttribute('data-entidad');
        entidadInput.value = entidad;
        console.log('Entidad FK actualizada a:', entidad);
    } else {
        entidadInput.value = '';
    }
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function () {
    // Actualizar entidad_fk si ya hay un municipio seleccionado
    if (document.getElementById('municipio_fk').value !== '') {
        actualizarEntidadFK();
    }

    // Agregar evento de cambio
    document.getElementById('municipio_fk').addEventListener('change', actualizarEntidadFK);
});

function validarFormularioEdicion() {
    const municipio = document.getElementById('municipio_fk');
    const entidad = document.getElementById('entidad_fk');

    if (municipio.value === '') {
        alert('Por favor selecciona un municipio');
        municipio.focus();
        return false;
    }

    if (entidad.value === '') {
        alert('Error: No se pudo determinar la entidad. Por favor selecciona otro municipio.');
        municipio.focus();
        return false;
    }

    return true;
}

// Inicializar la visibilidad de los campos del paciente
document.addEventListener('DOMContentLoaded', function () {
    toggleCamposEdicion();
    // Actualizar entidad_fk si ya hay un municipio seleccionado
    if (document.getElementById('municipio_fk').value !== '') {
        actualizarEntidadFK();
    }
});

// Función para cerrar el modal
function cerrarModal() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.style.display = 'none';
    }
    // Limpiar parámetros de la URL
    window.history.pushState({}, document.title, window.location.pathname);
}

// Mostrar/ocultar campos del paciente según el rol
function toggleCamposEdicion() {
    const rol = document.getElementById('rol');
    const camposPaciente = document.getElementById('camposPaciente');

    if (rol && camposPaciente) {
        if (rol.value === 'Usuario') {
            camposPaciente.style.display = 'block';
        } else {
            camposPaciente.style.display = 'none';
        }
    }
}

// Calcular edad automáticamente
function calcularEdad() {
    const fechaNacimiento = document.getElementById('fecha_nacimiento');
    const edadInput = document.getElementById('edad');

    if (fechaNacimiento && fechaNacimiento.value) {
        const fechaNac = new Date(fechaNacimiento.value);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }

        if (edadInput) {
            edadInput.value = edad;
        }
    }
}

// Actualizar entidad_fk automáticamente
function actualizarEntidadFK() {
    const municipioSelect = document.getElementById('municipio_fk');
    const entidadInput = document.getElementById('entidad_fk');

    if (municipioSelect && entidadInput) {
        const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
        if (selectedOption && selectedOption.getAttribute('data-entidad')) {
            entidadInput.value = selectedOption.getAttribute('data-entidad');
        } else {
            entidadInput.value = '';
        }
    }
}

// Validar formulario antes de enviar
function validarFormularioEdicion() {
    const rol = document.getElementById('rol').value;

    // Si es paciente, validar campos específicos
    if (rol === 'Usuario') {
        const municipio = document.getElementById('municipio_fk');
        const entidad = document.getElementById('entidad_fk');

        if (!municipio || municipio.value === '') {
            alert('Por favor selecciona un municipio');
            municipio.focus();
            return false;
        }

        if (!entidad || entidad.value === '') {
            alert('Error: No se pudo determinar la entidad. Por favor selecciona otro municipio.');
            municipio.focus();
            return false;
        }
    }

    return true;
}

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function (event) {
    const modal = document.getElementById('modalEditar');
    if (event.target === modal) {
        cerrarModal();
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModal();
    }
});

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    // Mostrar/ocultar campos según el rol
    toggleCamposEdicion();

    // Actualizar entidad_fk si ya hay un municipio seleccionado
    const municipioSelect = document.getElementById('municipio_fk');
    if (municipioSelect && municipioSelect.value !== '') {
        actualizarEntidadFK();
    }

    // Agregar evento de cambio al rol
    const rolSelect = document.getElementById('rol');
    if (rolSelect) {
        rolSelect.addEventListener('change', toggleCamposEdicion);
    }

    // Agregar evento de cambio al municipio
    if (municipioSelect) {
        municipioSelect.addEventListener('change', actualizarEntidadFK);
    }
});