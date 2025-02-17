<?php
session_start();
require_once 'php/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Enigmatool</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🔄 Restablecer Contraseña</h1>
            <p class="subtitulo">Crea una nueva contraseña segura</p>
        </div>

        <?php
        // Verificar token válido
        $token_valido = false;
        if (isset($_GET['token'])) {
            try {
                $db = ConexionBD::obtenerInstancia();
                $stmt = $db->prepare("SELECT id FROM usuarios 
                                    WHERE token_recuperacion = ? 
                                    AND expiracion_token > NOW()");
                $stmt->execute([$_GET['token']]);
                $token_valido = $stmt->rowCount() > 0;
            } catch(PDOException $e) {
                echo '<div class="mensaje error">Error validando token</div>';
            }
        }
        
        if ($token_valido) :
        ?>
        
        <form id="form-restablecer" class="form-login">
            <input type="hidden" id="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            
            <div class="form-group">
                <label for="nueva_contrasena">🔒 Nueva Contraseña:</label>
                <input 
                    type="password" 
                    id="nueva_contrasena" 
                    required
                    minlength="8"
                    placeholder="Mínimo 8 caracteres"
                    class="form-control"
                >
            </div>

            <div class="form-group">
                <label for="confirmar_contrasena">✅ Confirmar Contraseña:</label>
                <input 
                    type="password" 
                    id="confirmar_contrasena" 
                    required
                    minlength="8"
                    placeholder="Repite tu nueva contraseña"
                    class="form-control"
                >
            </div>

            <button type="submit" class="btn btn-block btn-success">
                🚀 Actualizar Contraseña
            </button>
        </form>

        <?php else : ?>
        
        <div class="mensaje error">
            ⚠️ Enlace inválido o expirado. Solicita un nuevo enlace de recuperación.
        </div>
        <div class="login-footer">
            <a href="recuperar-contrasena.html" class="link">Solicitar nuevo enlace</a>
        </div>

        <?php endif; ?>

        <div id="mensaje" class="mensaje"></div>
    </div>

    <script>
    document.getElementById('form-restablecer').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const token = document.getElementById('token').value;
        const nuevaContrasena = document.getElementById('nueva_contrasena').value;
        const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
        const mensaje = document.getElementById('mensaje');

        // Validar coincidencia
        if (nuevaContrasena !== confirmarContrasena) {
            mensaje.textContent = 'Las contraseñas no coinciden';
            mensaje.className = 'mensaje error';
            mensaje.style.display = 'block';
            return;
        }

        try {
            const response = await fetch('php/restablecer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: token,
                    nueva_contrasena: nuevaContrasena
                })
            });
            
            const data = await response.json();
            
            mensaje.textContent = data.mensaje;
            mensaje.className = `mensaje ${data.success ? 'exito' : 'error'}`;
            mensaje.style.display = 'block';

            if (data.success) {
                setTimeout(() => window.location.href = 'index.html', 2000);
            }
        } catch (error) {
            mensaje.textContent = 'Error de conexión';
            mensaje.className = 'mensaje error';
            mensaje.style.display = 'block';
        }
    });
    </script>
</body>
</html>
