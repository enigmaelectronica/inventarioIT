// script.js - Lógica principal de Enigmatool

// =============================================
// Configuración inicial
// =============================================
const API_BASE = 'php/';
const config = {
    equipos: {
        list: 'equipos.php',
        create: 'equipos.php',
        update: 'equipos.php',
        delete: 'equipos.php'
    },
    receptores: {
        list: 'receptores.php',
        create: 'receptores.php'
    },
    auth: 'auth.php',
    upload: 'upload.php'
};

// =============================================
// Módulo de Autenticación
// =============================================
const Auth = {
    init: () => {
        const loginForm = document.getElementById('login-form');
        if(loginForm) {
            loginForm.addEventListener('submit', Auth.handleLogin);
        }
    },

    handleLogin: async (e) => {
        e.preventDefault();
        const usuario = document.getElementById('usuario').value;
        const contrasena = document.getElementById('contrasena').value;

        try {
            const response = await fetch(API_BASE + config.auth, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ usuario, contrasena })
            });
            
            const data = await response.json();
            
            if(data.success) {
                window.location.href = 'inventario.html';
            } else {
                UI.showError(data.message || 'Error de autenticación');
            }
        } catch (error) {
            UI.showError('Error de conexión con el servidor');
            console.error('Login error:', error);
        }
    }
};

// =============================================
// Módulo de Inventario
// =============================================
const Inventario = {
    init: () => {
        // Cargar datos iniciales
        Inventario.loadEquipos();
        
        // Configurar eventos
        document.getElementById('busqueda').addEventListener('input', Inventario.handleSearch);
        document.getElementById('agregar-equipo').addEventListener('click', UI.showEquipoForm);
        
        // Delegación de eventos para acciones
        document.querySelector('#tabla-equipos tbody').addEventListener('click', (e) => {
            if(e.target.classList.contains('editar')) {
                Inventario.handleEdit(e);
            } else if(e.target.classList.contains('eliminar')) {
                Inventario.handleDelete(e);
            }
        });
    },

    loadEquipos: async () => {
        try {
            const response = await fetch(API_BASE + config.equipos.list);
            const equipos = await response.json();
            UI.renderTable(equipos);
        } catch (error) {
            UI.showError('Error cargando inventario');
            console.error('Load error:', error);
        }
    },

    handleSearch: (e) => {
        const termino = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#tabla-equipos tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(termino) ? '' : 'none';
        });
    },

    handleEdit: (e) => {
        const row = e.target.closest('tr');
        const equipoId = row.dataset.id;
        // Implementar lógica de edición...
        console.log('Editar equipo ID:', equipoId);
    },

    handleDelete: async (e) => {
        const row = e.target.closest('tr');
        const equipoId = row.dataset.id;
        
        if(confirm('¿Estás seguro de eliminar este equipo?')) {
            try {
                const response = await fetch(`${API_BASE}${config.equipos.delete}?id=${equipoId}`, {
                    method: 'DELETE'
                });
                
                if(response.ok) {
                    row.remove();
                    UI.showSuccess('Equipo eliminado correctamente');
                }
            } catch (error) {
                UI.showError('Error eliminando equipo');
                console.error('Delete error:', error);
            }
        }
    }
};

// =============================================
// Módulo de Interfaz de Usuario
// =============================================
const UI = {
    showError: (message) => {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        setTimeout(() => errorDiv.style.display = 'none', 5000);
    },

    showSuccess: (message) => {
        alert(message); // Puedes reemplazar con un toast mejorado
    },

    renderTable: (equipos) => {
        const tbody = document.querySelector('#tabla-equipos tbody');
        tbody.innerHTML = '';
        
        equipos.forEach(equipo => {
            const row = document.createElement('tr');
            row.dataset.id = equipo.id;
            row.innerHTML = `
                <td>${equipo.tipo}</td>
                <td>${equipo.marca}</td>
                <td>${equipo.modelo}</td>
                <td>${equipo.numero_serie}</td>
                <td class="acciones">
                    <button class="btn editar">Editar</button>
                    <button class="btn eliminar">Eliminar</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    },

    showEquipoForm: (equipo = null) => {
        // Implementar formulario modal con campos según tipo de equipo
        console.log('Mostrar formulario para:', equipo || 'nuevo equipo');
    }
};

// =============================================
// Inicialización de la aplicación
// =============================================
document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('login-form')) Auth.init();
    if(document.getElementById('tabla-equipos')) Inventario.init();
});
