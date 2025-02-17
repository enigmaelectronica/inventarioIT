// script.js - Lógica unificada para toda la aplicación

// ======================== LOGIN ========================
document.getElementById('login-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const usuario = document.getElementById('usuario').value;
    const contrasena = document.getElementById('contrasena').value;

    try {
        const response = await fetch('php/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario, contrasena })
        });
        
        const data = await response.json();
        
        if(data.success) {
            window.location.href = 'inventario.html';
        } else {
            mostrarError(data.message || 'Error de autenticación');
        }
    } catch (error) {
        mostrarError('Error de conexión');
    }
});

// ======================== INVENTARIO ========================
if (document.getElementById('tabla-equipos')) {
    // Cargar equipos al iniciar
    cargarEquipos();
    
    // Busqueda en tiempo real
    document.getElementById('busqueda').addEventListener('input', buscarEquipos);
    
    // Subida de archivos
    document.getElementById('form-archivos').addEventListener('submit', subirArchivos);
}

async function cargarEquipos() {
    try {
        const response = await fetch('php/equipos.php');
        const equipos = await response.json();
        renderizarTabla(equipos);
    } catch (error) {
        mostrarError('Error cargando inventario');
    }
}

function renderizarTabla(equipos) {
    const tbody = document.querySelector('#tabla-equipos tbody');
    tbody.innerHTML = '';
    
    equipos.forEach(equipo => {
        tbody.innerHTML += `
            <tr data-id="${equipo.id}">
                <td>${equipo.tipo}</td>
                <td>${equipo.marca}</td>
                <td>${equipo.modelo}</td>
                <td>${equipo.numero_serie}</td>
                <td>
                    <button class="editar">Editar</button>
                    <button class="eliminar">Eliminar</button>
                </td>
            </tr>
        `;
    });
}

// ======================== SUBIDA DE ARCHIVOS ========================
async function subirArchivos(e) {
    e.preventDefault();
    
    const formData = new FormData();
    const archivos = document.getElementById('archivos').files;
    
    Array.from(archivos).forEach((archivo, i) => {
        formData.append(`archivos[${i}]`, archivo);
    });
    
    try {
        const response = await fetch('php/upload.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if(data.success) {
            alert('Archivos subidos correctamente');
        } else {
            mostrarError(data.message);
        }
    } catch (error) {
        mostrarError('Error subiendo archivos');
    }
}

// Función auxiliar para mostrar errores
function mostrarError(mensaje) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = mensaje;
    errorDiv.style.display = 'block';
    setTimeout(() => errorDiv.style.display = 'none', 5000);
}
