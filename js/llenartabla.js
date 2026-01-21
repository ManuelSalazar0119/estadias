document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalJustificaciones');
    const abrir = document.getElementById('abrirModal');
    const cerrar = document.getElementById('cerrarModal');
    const tbody = document.querySelector('#tablaJustificaciones tbody');
    const guardarBtn = document.getElementById('guardarJustificaciones');

    function obtenerValores() {
        const id_area = document.getElementById('area-select')?.value;
        const anio = document.getElementById('anio')?.value;
        const mes  = document.getElementById('mes')?.value;
        if (!id_area || !anio || !mes) return null;
        return { id_area, anio, mes };
    }

    function cargarJustificaciones() {
        const vals = obtenerValores();
        if (!vals) {
            tbody.innerHTML = '<tr><td colspan="5" style="color:red;">Faltan año, mes o área.</td></tr>';
            return;
        }

        tbody.innerHTML = '<tr><td colspan="5">Cargando...</td></tr>';

        fetch(`../funciones/obtener_justificaciones.php?id_area=${vals.id_area}&anio=${vals.anio}&mes=${vals.mes}`)
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    tbody.innerHTML = '';
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5">No hay actividades que necesiten justificación.</td></tr>';
                        return;
                    }

                    data.forEach(act => {
                        const tr = document.createElement('tr');

                        // Columnas normales
                        tr.innerHTML = `
                            <td>${act.nombre_actividad}</td>
                            <td>${act.unidad}</td>
                            <td>${act.programado}</td>
                            <td>${act.realizado}</td>
                        `;

                        // Crear td y textarea dinámicamente
                        const td = document.createElement('td');
                        const textarea = document.createElement('textarea');
                        textarea.dataset.idGrupo = act.id_grupo;
                        textarea.dataset.unidad = act.unidad;
                        textarea.value = act.justificacion || ''; // mostrar justificación existente
                        td.appendChild(textarea);
                        tr.appendChild(td);

                        tbody.appendChild(tr);
                    });
                } catch (e) {
                    console.error('Respuesta no es JSON válido:', text);
                    tbody.innerHTML = `<tr><td colspan="5" style="color:red;">Error: ${e.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error('Error al cargar justificaciones:', err);
                tbody.innerHTML = `<tr><td colspan="5" style="color:red;">Error de conexión</td></tr>`;
            });
    }

    abrir.addEventListener('click', () => {
        modal.style.display = 'flex';
        cargarJustificaciones();
    });

    cerrar.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

    guardarBtn.addEventListener('click', () => {
        const textareas = tbody.querySelectorAll('textarea');
        if (!textareas.length) return;

        const payload = Array.from(textareas).map(t => ({
            id_grupo: parseInt(t.dataset.idGrupo),
            unidad: t.dataset.unidad,
            justificacion: t.value
        }));

        const vals = obtenerValores();
        if (!vals) {
            alert('No se pudieron obtener año, mes o área.');
            return;
        }

        console.log(payload);

        fetch('../funciones/guardar_justificaciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                anio: parseInt(vals.anio),
                mes: parseInt(vals.mes),
                justificaciones: payload
            })
        })
        .then(res => res.json())
        .then(resp => {
            alert(resp.mensaje);
            if (resp.success) modal.style.display = 'none';
        })
        .catch(err => {
            console.error('Error al guardar justificaciones:', err);
            alert('Ocurrió un error al guardar las justificaciones.');
        });
    });
});
// Añade esto al final de tu archivo HTML o en un archivo JS separado
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Mejorar visualmente los porcentajes
    const todasCeldas = document.querySelectorAll('.tg td');
    
    todasCeldas.forEach(celda => {
        const texto = celda.textContent.trim();
        
        // Si es un porcentaje
        if (texto.includes('%')) {
            const numero = parseFloat(texto);
            
            // Remover clases existentes
            celda.classList.remove('porcentaje-alto', 'porcentaje-medio', 'porcentaje-bajo');
            
            // Añadir clase según el valor
            if (numero >= 100) {
                celda.classList.add('porcentaje-alto');
            } else if (numero >= 50) {
                celda.classList.add('porcentaje-medio');
            } else {
                celda.classList.add('porcentaje-bajo');
            }
            
            // Añadir tooltip
            celda.title = `Avance: ${texto}`;
        }
        
        // Si es un número grande (con comas)
        if (texto.includes(',') && !texto.includes('%')) {
            celda.style.fontFamily = "'Courier New', monospace";
            celda.style.fontWeight = '600';
            celda.style.color = '#2c3e50';
        }
    });
    
    // 2. Efecto hover en filas
    const filas = document.querySelectorAll('.tg tbody tr');
    filas.forEach(fila => {
        fila.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f1f8ff';
            this.style.transition = 'background-color 0.3s';
        });
        
        fila.addEventListener('mouseleave', function() {
            // Restaurar color original basado en si es par o impar
            const esPar = Array.from(filas).indexOf(this) % 2 === 0;
            this.style.backgroundColor = esPar ? '#f8f9fa' : 'white';
        });
    });
    
    // 3. Resaltar celdas importantes
    const celdasImportantes = document.querySelectorAll('.tg-3yw8');
    celdasImportantes.forEach(celda => {
        celda.style.position = 'relative';
        
        // Añadir icono de atención
        if (!celda.querySelector('.icono-atencion')) {
            const icono = document.createElement('span');
            icono.innerHTML = '⚠️';
            icono.className = 'icono-atencion';
            icono.style.position = 'absolute';
            icono.style.left = '5px';
            icono.style.top = '50%';
            icono.style.transform = 'translateY(-50%)';
            icono.style.fontSize = '12px';
            celda.style.paddingLeft = '25px';
            celda.appendChild(icono);
        }
    });
    
    // 4. Añadir ordenamiento por columnas (opcional)
    const encabezados = document.querySelectorAll('.tg th');
    encabezados.forEach((th, index) => {
        th.style.cursor = 'pointer';
        th.title = 'Click para ordenar';
        
        th.addEventListener('click', function() {
            ordenarTabla(index);
        });
    });
    
    // 5. Función para ordenar tabla
    function ordenarTabla(colIndex) {
        const tabla = document.querySelector('.tg');
        const tbody = tabla.querySelector('tbody');
        const filas = Array.from(tbody.querySelectorAll('tr'));
        
        // No ordenar filas de grupo
        const filasParaOrdenar = filas.filter(fila => 
            !fila.classList.contains('tg-yla0') && 
            !fila.classList.contains('tg-qjdd') && 
            !fila.classList.contains('tg-g4z8')
        );
        
        const ordenActual = tbody.getAttribute('data-orden') || 'asc';
        const nuevoOrden = ordenActual === 'asc' ? 'desc' : 'asc';
        
        filasParaOrdenar.sort((a, b) => {
            const celdaA = a.children[colIndex].textContent.trim();
            const celdaB = b.children[colIndex].textContent.trim();
            
            // Convertir a número si es posible
            const numA = parseFloat(celdaA.replace(/,/g, ''));
            const numB = parseFloat(celdaB.replace(/,/g, ''));
            
            if (!isNaN(numA) && !isNaN(numB)) {
                return nuevoOrden === 'asc' ? numA - numB : numB - numA;
            }
            
            // Orden alfabético
            return nuevoOrden === 'asc' 
                ? celdaA.localeCompare(celdaB)
                : celdaB.localeCompare(celdaA);
        });
        
        // Reinsertar filas ordenadas
        filasParaOrdenar.forEach(fila => tbody.appendChild(fila));
        tbody.setAttribute('data-orden', nuevoOrden);
    }
    
    // 6. Añadir búsqueda rápida
    const contenedorTabla = document.querySelector('.tabla-scroll-x');
    if (contenedorTabla) {
        // Crear input de búsqueda
        const searchDiv = document.createElement('div');
        searchDiv.style.cssText = `
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        `;
        
        searchDiv.innerHTML = `
            <input type="text" id="buscadorTabla" placeholder="Buscar en la tabla..." 
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <small style="color: #666; display: block; margin-top: 5px;">
                Buscar por actividad, unidad o valor
            </small>
        `;
        
        contenedorTabla.parentNode.insertBefore(searchDiv, contenedorTabla);
        
        // Funcionalidad de búsqueda
        const buscador = document.getElementById('buscadorTabla');
        buscador.addEventListener('input', function() {
            const termino = this.value.toLowerCase();
            const filas = document.querySelectorAll('.tg tbody tr');
            
            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.includes(termino)) {
                    fila.style.display = '';
                    // Resaltar término buscado
                    resaltarTermino(fila, termino);
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
    
    // 7. Función para resaltar texto buscado
    function resaltarTermino(fila, termino) {
        if (!termino) return;
        
        const celdas = fila.querySelectorAll('td');
        celdas.forEach(celda => {
            const textoOriginal = celda.textContent;
            const regex = new RegExp(`(${termino})`, 'gi');
            const nuevoHTML = textoOriginal.replace(regex, '<mark style="background: #ffeb3b; padding: 2px;">$1</mark>');
            
            if (nuevoHTML !== textoOriginal) {
                celda.innerHTML = nuevoHTML;
            }
        });
    }
    
    // 8. Contador de filas visibles
    function actualizarContador() {
        const filasVisibles = document.querySelectorAll('.tg tbody tr[style=""]').length;
        const contador = document.getElementById('contadorFilas') || crearContador();
        contador.textContent = `Mostrando ${filasVisibles} de ${document.querySelectorAll('.tg tbody tr').length} registros`;
    }
    
    function crearContador() {
        const contador = document.createElement('div');
        contador.id = 'contadorFilas';
        contador.style.cssText = `
            margin: 10px 0;
            padding: 8px;
            background: #e3f2fd;
            border-radius: 4px;
            font-size: 14px;
            color: #0d47a1;
            text-align: center;
        `;
        
        const contenedor = document.querySelector('.tabla-scroll-x');
        contenedor.parentNode.insertBefore(contador, contenedor.nextSibling);
        return contador;
    }
    
    // Inicializar contador
    setTimeout(actualizarContador, 100);
});