class Enigmatool {
    static init() {
        this.configurarLogin();
        this.configurarInventario();
        this.configurarRecuperacion();
        this.configurarRestablecer();
    }

    static configurarLogin() {
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const usuario = document.getElementById('usuario').value;
                const contrasena = document.getElementById('contrasena').value;
                await this.autenticar(usuario, contrasena);
            });
        }
    }

    static configurarInventario() {
        const tablaEquipos = document.getElementById('tabla-equipos');
        if (tablaEquipos) {
            this.cargarEquipos();
            document.getElementById('busqueda').addEventListener('input', (e) => {
                this.filtrarEquipos(e.target.value.toLowerCase());
            });
            document.getElementById('nuevo-equipo').addEventListener('click', () => this.mostrarModal());
            tablaEquipos.addEventListener('click', (e) => this.manejarAcciones(e));
        }
    }

    static async autenticar(usuario, contrasena) {
        try {
            const response = await fetch('php/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario, contrasena })
            });
            
            const data = await response.json();
            if (data.success) {
                window.location.href = 'inventario.html';
            } else {
                this.mostrarMensaje('error-message', data.message || 'Error de autenticación');
            }
        } catch (error) {
            this.mostrarMensaje('error-message', 'Error de conexión');
        }
    }

    static async cargarEquipos() {
        try {
            const response = await fetch('php/equipos.php');
            this.equipos = await response.json();
            this.renderizarTabla();
        } catch (error) {
            this.mostrarMensaje('error-message', 'Error cargando equipos');
        }
    }

    static renderizarTabla() {
        const tbody = document.getElementById('tabla-equipos');
        tbody.innerHTML = this.equipos.map(equipo => `
            <tr data-id="${equipo.id}">
                <td>${equipo.tipo}</td>
                <td>${equipo.marca}</td>
                <td>${equipo.modelo}</td>
                <td>${equipo.numero_serie}</td>
                <td>
                    <button class="btn editar">✏️ Editar</button>
                    <button class="btn eliminar">🗑️ Eliminar</button>
                </td>
            </tr>
        `).join('');
    }

    static configurarRecuperacion() {
        const formRecuperar = document.getElementById('form-recuperar');
        if (formRecuperar) {
            formRecuperar.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('email').value;
                try {
                    const response = await fetch('php/recuperar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    
                    const data = await response.json();
                    this.mostrarMensaje('mensaje', data.mensaje, data.success ? 'exito' : 'error');
                } catch (error) {
                    this.mostrarMensaje('mensaje', 'Error de conexión', 'error');
                }
            });
        }
    }

    static configurarRestablecer() {
        const formRestablecer = document.getElementById('form-restablecer');
        if (formRestablecer) {
            const urlParams = new URLSearchParams(window.location.search);
            document.getElementById('token').value = urlParams.get('token');
            
            formRestablecer.addEventListener('submit', async (e) => {
                e.preventDefault();
                const nuevaContrasena = document.getElementById('nueva_contrasena').value;
                const confirmarContrasena = document.getElementById('confirmar_contrasena').value;

                if (nuevaContrasena !== confirmarContrasena) {
                    this.mostrarMensaje('mensaje', 'Las contraseñas no coinciden', 'error');
                    return;
                }

                try {
                    const response = await fetch('php/restablecer.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            token: document.getElementById('token').value,
                            nueva_contrasena: nuevaContrasena
                        })
                    });
                    
                    const data = await response.json();
                    this.mostrarMensaje('mensaje', data.mensaje, data.success ? 'exito' : 'error');
                    
                    if (data.success) {
                        setTimeout(() => window.location.href = 'index.html', 2000);
                    }
                } catch (error) {
                    this.mostrarMensaje('mensaje', 'Error de conexión', 'error');
                }
            });
        }
    }

    static mostrarMensaje(elementId, texto, tipo = 'error') {
        const elemento = document.getElementById(elementId);
        elemento.textContent = texto;
        elemento.className = `mensaje ${tipo}`;
        elemento.style.display = 'block';
        setTimeout(() => elemento.style.display = 'none', 5000);
    }

    static manejarAcciones(e) {
        const btn = e.target;
        if (btn.classList.contains('eliminar')) {
            this.eliminarEquipo(btn.closest('tr').dataset.id);
        } else if (btn.classList.contains('editar')) {
            this.mostrarModal(btn.closest('tr').dataset.id);
        }
    }

    static async eliminarEquipo(id) {
        if (!confirm('¿Estás seguro de eliminar este equipo?')) return;
        
        try {
            const response = await fetch(`php/equipos.php?id=${id}`, { method: 'DELETE' });
            if (response.ok) this.cargarEquipos();
        } catch (error) {
            this.mostrarMensaje('error-message', 'Error eliminando equipo');
        }
    }

    static mostrarModal(id = null) {
        const modal = document.getElementById('modal-equipo');
        modal.style.display = 'block';
        // Lógica completa para cargar datos en el modal
    }
}

// Inicializar aplicación
document.addEventListener('DOMContentLoaded', () => Enigmatool.init());
