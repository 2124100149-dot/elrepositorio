<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro HealthNet</title>
    <link rel="stylesheet" href="../../assets/css/estilo_IS.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../../assets/imagenes/logoH.png" alt="HealthNet Logo">
            <h1>HEALTHNET</h1>
        </div>

        <div class="right">
            <h2>Registro de usuario</h2>

            <?php if (isset($error)) { ?>
                <div style="color: red; text-align: center; margin-bottom: 15px;">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" action="">
                <label for="correo">Nombre de Usuario:</label>
                <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required>

                <label for="rol">Rol</label>
                <div class="select-box">
                    <select name="rol" id="rol" required>
                        <option value="" disabled selected>Seleccione un rol</option>
                        <option value="AdminGeneral">Administrador General</option>
                        <option value="AdminRegional">Administrador Regional</option>
                        <option value="medico">Médico</option>
                        <option value="Usuario">Usuario</option>
                    </select>
                </div>

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>

                <label for="reviewPassword">Ingrese su contraseña nuevamente:</label>
                <input type="password" id="reviewPassword" name="reviewPassword" placeholder="Repetir contraseña" required>

                <button type="submit" class="btn">Registrarse</button>
                <button type="button" class="btn" onclick="location.href='../../public/index.php'">Iniciar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>