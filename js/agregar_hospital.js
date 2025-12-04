// agrega_hospital.js - VERSIÓN MEJORADA
document.addEventListener('DOMContentLoaded', function () {
    console.log('Script de hospital cargado');
    
    // 1. SELECCIONAR/DESELECCIONAR TODOS LOS SERVICIOS
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    
    console.log('Botón Seleccionar Todos:', selectAllBtn);
    console.log('Botón Deseleccionar Todos:', deselectAllBtn);
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('Click en Seleccionar Todos');
            
            const checkboxes = document.querySelectorAll('input[name="servicios[]"]');
            console.log('Checkboxes encontrados:', checkboxes.length);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            // Cambiar texto temporalmente para confirmación visual
            const originalText = selectAllBtn.textContent;
            selectAllBtn.textContent = '¡Todos seleccionados!';
            selectAllBtn.style.backgroundColor = '#27ae60';
            selectAllBtn.style.color = 'white';
            
            setTimeout(() => {
                selectAllBtn.textContent = originalText;
                selectAllBtn.style.backgroundColor = '';
                selectAllBtn.style.color = '';
            }, 1000);
        });
    }
    
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('Click en Deseleccionar Todos');
            
            const checkboxes = document.querySelectorAll('input[name="servicios[]"]');
            console.log('Checkboxes encontrados:', checkboxes.length);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Cambiar texto temporalmente para confirmación visual
            const originalText = deselectAllBtn.textContent;
            deselectAllBtn.textContent = '¡Todos deseleccionados!';
            deselectAllBtn.style.backgroundColor = '#e74c3c';
            deselectAllBtn.style.color = 'white';
            
            setTimeout(() => {
                deselectAllBtn.textContent = originalText;
                deselectAllBtn.style.backgroundColor = '';
                deselectAllBtn.style.color = '';
            }, 1000);
        });
    }

    // 2. VALIDACIÓN DE TELÉFONO
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function (e) {
            // Permitir números, espacios, guiones, paréntesis y +
            this.value = this.value.replace(/[^\d\s\-\+\(\)]/g, '');
            
            // Validación en tiempo real
            validarTelefono(this);
        });
        
        telefonoInput.addEventListener('blur', function() {
            validarTelefono(this);
        });
    }

    // 3. VALIDACIÓN DE CÓDIGO POSTAL
    const cpInput = document.getElementById('cp');
    if (cpInput) {
        cpInput.addEventListener('input', function (e) {
            // Solo números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Máximo 5 dígitos
            if (this.value.length > 5) {
                this.value = this.value.slice(0, 5);
            }
            
            // Validación en tiempo real
            validarCP(this);
        });
        
        cpInput.addEventListener('blur', function() {
            validarCP(this);
        });
    }
    
    // 4. VALIDACIÓN DE NOMBRE
    const nombreInput = document.getElementById('nombre');
    if (nombreInput) {
        nombreInput.addEventListener('blur', function() {
            validarNombre(this);
        });
        
        nombreInput.addEventListener('input', function() {
            // Remover caracteres especiales no deseados
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.]/g, '');
        });
    }
    
    // 5. VALIDACIÓN DE MUNICIPIO (opcional, solo para mostrar visual)
    const municipioInput = document.getElementById('municipio_fk');
    if (municipioInput) {
        municipioInput.addEventListener('change', function() {
            if (this.value !== '') {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    // 6. BOTÓN DE REGISTRO - Agregar validación adicional
    const form = document.querySelector('.registro-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Formulario enviándose...');
            
            // Validar al menos un servicio seleccionado (opcional)
            const serviciosCheckboxes = document.querySelectorAll('input[name="servicios[]"]:checked');
            if (serviciosCheckboxes.length === 0) {
                const confirmar = confirm('⚠️ No has seleccionado ningún servicio. ¿Deseas continuar sin servicios?');
                if (!confirmar) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return validarFormulario();
        });
    }
    
    // 7. FUNCIÓN PARA CONTAR SERVICIOS SELECCIONADOS
    function contarServiciosSeleccionados() {
        const checkboxes = document.querySelectorAll('input[name="servicios[]"]:checked');
        return checkboxes.length;
    }
    
    // 8. ACTUALIZAR CONTADOR DE SERVICIOS EN TIEMPO REAL
    const serviciosContainer = document.querySelector('.servicios-grid');
    if (serviciosContainer) {
        serviciosContainer.addEventListener('change', function() {
            const contador = contarServiciosSeleccionados();
            console.log('Servicios seleccionados:', contador);
            
            // Opcional: Mostrar contador visual
            let contadorElement = document.getElementById('contador-servicios');
            if (!contadorElement) {
                contadorElement = document.createElement('div');
                contadorElement.id = 'contador-servicios';
                contadorElement.style.marginTop = '10px';
                contadorElement.style.fontSize = '14px';
                contadorElement.style.color = '#3498db';
                serviciosContainer.parentNode.insertBefore(contadorElement, serviciosContainer.nextSibling);
            }
            
            contadorElement.textContent = `${contador} servicio(s) seleccionado(s)`;
        });
        
        // Inicializar contador
        const eventoChange = new Event('change');
        serviciosContainer.dispatchEvent(eventoChange);
    }
});

// FUNCIONES DE VALIDACIÓN (GLOBALES)
function validarNombre(input) {
    const nombre = input.value.trim();
    if (nombre.length < 3) {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.5)';
        return false;
    } else {
        input.style.borderColor = '#27ae60';
        input.style.boxShadow = '0 0 5px rgba(39, 174, 96, 0.5)';
        return true;
    }
}

function validarTelefono(input) {
    const telefono = input.value.trim();
    const telefonoRegex = /^[\d\s\-\+\(\)]{8,20}$/;
    
    if (!telefonoRegex.test(telefono)) {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.5)';
        return false;
    } else {
        input.style.borderColor = '#27ae60';
        input.style.boxShadow = '0 0 5px rgba(39, 174, 96, 0.5)';
        return true;
    }
}

function validarCP(input) {
    const cp = input.value.trim();
    const cpRegex = /^\d{5}$/;
    
    if (cp === '' || cpRegex.test(cp)) {
        input.style.borderColor = '#27ae60';
        input.style.boxShadow = '0 0 5px rgba(39, 174, 96, 0.5)';
        return true;
    } else {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 5px rgba(231, 76, 60, 0.5)';
        return false;
    }
}

function validarFormulario() {
    console.log('Validando formulario...');
    
    const nombre = document.getElementById('nombre');
    const telefono = document.getElementById('telefono');
    const municipio = document.getElementById('municipio_fk');
    
    let valido = true;
    let mensajesError = [];
    
    if (!validarNombre(nombre)) {
        mensajesError.push('Por favor ingresa un nombre válido (mínimo 3 caracteres)');
        valido = false;
    }
    
    if (!validarTelefono(telefono)) {
        mensajesError.push('Por favor ingresa un teléfono válido (8-20 dígitos, puede incluir espacios, guiones, + y paréntesis)');
        valido = false;
    }
    
    if (municipio.value === '') {
        mensajesError.push('Por favor selecciona un municipio');
        valido = false;
    }
    
    if (!valido) {
        // Mostrar todos los errores en una sola alerta
        alert('❌ Errores en el formulario:\n\n' + mensajesError.join('\n'));
        
        // Enfocar el primer campo con error
        if (nombre.value.trim().length < 3) {
            nombre.focus();
        } else if (!validarTelefono(telefono)) {
            telefono.focus();
        } else if (municipio.value === '') {
            municipio.focus();
        }
    } else {
        console.log('Formulario válido, procediendo con envío...');
    }
    
    return valido;
}

// FUNCIÓN ADICIONAL PARA DEBUG
function debugServicios() {
    console.log('=== DEBUG SERVICIOS ===');
    console.log('Botón Seleccionar Todos:', document.getElementById('selectAllBtn'));
    console.log('Botón Deseleccionar Todos:', document.getElementById('deselectAllBtn'));
    
    const checkboxes = document.querySelectorAll('input[name="servicios[]"]');
    console.log('Total checkboxes:', checkboxes.length);
    
    checkboxes.forEach((cb, index) => {
        console.log(`Checkbox ${index}:`, cb.id, '- Checked:', cb.checked);
    });
    
    console.log('=== FIN DEBUG ===');
}

// Ejecutar debug al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un momento para que todo cargue
    setTimeout(debugServicios, 500);
});