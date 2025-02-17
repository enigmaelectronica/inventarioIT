class Enigmatool {
    static init() {
        this.configurarEventos();
        if (document.getElementById('tabla-equipos')) this.cargarEquipos();
    }

    static configurarEventos() {
        // Login
        document.getElementById('login-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const usuario = document.getElementById('usuario').value;
            const contrasena = document.getElementById('contrasena').value;
            await this.autenticar(usuario, contrasena);
        });

        // Búsqueda
        document.getElementById('busqueda')?.addEventListener('input', (e) => {
            this.filtrarEquipos(e.target.value.toLowerCase());
        });

        // Modal
        document.getElementById('nuevo-equipo')?.addEventListener('click', () => this.mostrarModal());
        document.querySelector('.cerrar-modal')?.addEventListener('click', () => {
            document.getElementById('modal-equipo').style.display = 'none';
        });
    }

    static async autenticar(usuario, contrasena) {
        try {
            const response = await fetch('php/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario, contrasena })
            });
            
            const data = await response.json();
            if (data.success) window.location.href = 'inventario.html';
            else this.mostrarError(data.message || 'Error de autenticación');
        } catch (error) {
            this.mostrarError('Error de conexión');
        }
    }

    static async cargarEquipos() {
        try {
            const response = await fetch('php/equipos.php');
            this.equipos = await response.json();
            this.renderizarTabla();
        } catch (error) {
            this.mostrarError('Error cargando equipos');
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
                    <button class="btn editar">Editar</button>
                    <button class="btn eliminar">Eliminar</button>
                </td>
            </tr>
        `).join('');
    }

    static mostrarError(mensaje) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = mensaje;
        errorDiv.style.display = 'block';
        setTimeout(() => errorDiv.style.display = 'none', 5000);
    }
}

document.addEventListener('DOMContentLoaded', () => Enigmatool.init());
