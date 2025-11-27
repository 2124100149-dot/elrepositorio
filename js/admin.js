document.getElementById('codigo_postal').addEventListener('input', function(e) {
        const cp = e.target.value;
        // Solo permitir números
        e.target.value = cp.replace(/[^0-9]/g, '');
    });