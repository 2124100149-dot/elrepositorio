function toggleCampos() {
    let rol = document.getElementById("rol").value;
    let camposUsuario = document.getElementById("camposUsuario");
    let camposMedico = document.getElementById("camposMedico");

    // Ocultar todos los grupos primero
    camposUsuario.classList.remove('active');
    camposMedico.classList.remove('active');

    // Deshabilitar todos los campos primero
    document.querySelectorAll('.campo-rol').forEach(campo => {
        campo.disabled = true;
        campo.classList.add('campo-deshabilitado');
    });

    // Mostrar y habilitar campos según el rol
    if (rol === "Usuario") {
        camposUsuario.classList.add('active');
        camposUsuario.querySelectorAll('.campo-rol').forEach(campo => {
            campo.disabled = false;
            campo.classList.remove('campo-deshabilitado');
        });
    } else if (rol === "Medico") {
        camposMedico.classList.add('active');
        camposMedico.querySelectorAll('.campo-rol').forEach(campo => {
            campo.disabled = false;
            campo.classList.remove('campo-deshabilitado');
        });
    }
    // Para administradores no se muestran campos adicionales
}

document.addEventListener('DOMContentLoaded', function () {
    toggleCampos();
});

// Función para mostrar/ocultar campos según el rol
function toggleCampos() {
    const rol = document.getElementById('rol').value;
    const camposUsuario = document.getElementById('camposUsuario');
    const camposMedico = document.getElementById('camposMedico');

    // Ocultar todos los campos primero
    camposUsuario.style.display = 'none';
    camposMedico.style.display = 'none';

    // Mostrar campos según el rol
    if (rol === 'Usuario') {
        camposUsuario.style.display = 'block';
        // Hacer obligatorios solo los campos de usuario
        setRequiredFields('camposUsuario', true);
        setRequiredFields('camposMedico', false);
    } else if (rol === 'Medico') {
        camposMedico.style.display = 'block';
        // Hacer obligatorios solo los campos de médico
        setRequiredFields('camposUsuario', false);
        setRequiredFields('camposMedico', true);
    } else {
        // Para otros roles, ocultar ambos
        setRequiredFields('camposUsuario', false);
        setRequiredFields('camposMedico', false);
    }
}

function setRequiredFields(sectionId, isRequired) {
    const section = document.getElementById(sectionId);
    const inputs = section.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.required = isRequired;
    });
}

// Validación de fechas en tiempo real
function validarFechas() {
    const fechaActual = new Date().toISOString().split('T')[0];
    const fechaNac = document.querySelector('input[name="fecha_nac"]');
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
    const fechaFin = document.querySelector('input[name="fecha_fin"]');
    const fechaEfectiva = document.querySelector('input[name="fecha_efectiva"]');

    // Establecer límites en los inputs
    if (fechaNac) {
        fechaNac.max = fechaActual;
    }
    if (fechaInicio) {
        fechaInicio.min = fechaActual;
    }
    if (fechaEfectiva) {
        fechaEfectiva.min = fechaActual;
    }

    // Validar que fecha fin sea mayor que fecha inicio
    if (fechaInicio && fechaFin) {
        fechaFin.min = fechaInicio.value;

        // Si se cambia la fecha inicio, ajustar fecha fin si es necesario
        fechaInicio.addEventListener('change', function () {
            fechaFin.min = this.value;
            if (fechaFin.value && fechaFin.value < this.value) {
                fechaFin.value = this.value;
            }
        });
    }
}

// Inicializar cuando cargue la página
document.addEventListener('DOMContentLoaded', function () {
    toggleCampos();
    validarFechas();
});

document.addEventListener('DOMContentLoaded', function () {
    const fechaNacInput = document.querySelector('input[name="fecha_nac"]');
    const edadInput = document.getElementById('edad');

    function calcularEdad() {
        if (fechaNacInput.value) {
            const fechaNac = new Date(fechaNacInput.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNac.getFullYear();
            const mes = hoy.getMonth() - fechaNac.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                edad--;
            }
            edadInput.value = edad;
        } else {
            edadInput.value = '';
        }
    }

    fechaNacInput.addEventListener('change', calcularEdad);
});
