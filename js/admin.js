// API de Código Postal
    document.getElementById('codigo_postal').addEventListener('input', function(e) {
        const cp = e.target.value;
        const resultadoDiv = document.getElementById('resultado-cp');
        
        if (cp.length === 5) {
            // Consultar API de código postal
            fetch(`https://api-codigos-postales.herokuapp.com/v2/codigo_postal/${cp}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const ubicacion = data[0];
                        resultadoDiv.innerHTML = `
                            <div class="cp-info">
                                <h4>Información del Código Postal</h4>
                                <p><strong>Estado:</strong> ${ubicacion.estado || 'No disponible'}</p>
                                <p><strong>Municipio:</strong> ${ubicacion.municipio || 'No disponible'}</p>
                                <p><strong>Ciudad:</strong> ${ubicacion.ciudad || 'No disponible'}</p>
                                <p><strong>Asignar región:</strong> 
                                    <select id="select-region">
                                        <option value="Norte">Norte</option>
                                        <option value="Sur">Sur</option>
                                        <option value="Oriental">Oriental</option>
                                        <option value="Occidental">Occidental</option>
                                    </select>
                                    <button onclick="asignarRegion('${cp}')">Asignar</button>
                                </p>
                            </div>
                        `;
                    } else {
                        resultadoDiv.innerHTML = '<p class="cp-error">Código postal no encontrado</p>';
                    }
                })
                .catch(error => {
                    resultadoDiv.innerHTML = '<p class="cp-error">Error al consultar el código postal</p>';
                });
        } else {
            resultadoDiv.innerHTML = '';
        }
    });

    function asignarRegion(cp) {
        const region = document.getElementById('select-region').value;
        // Aquí puedes enviar esta información al servidor para guardarla
        alert(`Código Postal ${cp} asignado a la región: ${region}`);
    }