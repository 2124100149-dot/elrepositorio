<?php
session_start();

/* ========================================
   CONEXIÓN A LA BASE DE DATOS
========================================= */

// Conexión directa y simple
$conexion = new mysqli('yamabiko.proxy.rlwy.net', 'root', 'JBExxPHraFCRUGRIvODbqiGvtJyhpOwl', 'healthnet', '39168');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

/* ========================================
   1. RECIBIR TIPO DE REGISTRO
========================================= */
$tipo = $_POST['tipo_registro'] ?? '';

/* ========================================
   2. REGISTRAR ADMINISTRADORES Y MÉDICOS
========================================= */
if ($tipo === "admin" || $tipo === "medico") {

    $correo = $_POST['correo'];
    $pass = $_POST['contraseña'];
    $rol = $_POST['rol']; // AdminGeneral, AdminRegional, Medico

    if (empty($correo) || empty($pass) || empty($rol)) {
        die("Todos los campos son obligatorios");
    }

    // Verificar si ya existe
    $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Este correo ya está registrado.");
    }

    $check->close();

    // Encriptar contraseña
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    // Insertar
    $insert = $conexion->prepare("INSERT INTO usuarios (correo, contraseña, rol) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $correo, $hash, $rol);

    if ($insert->execute()) {
        echo "Administrador o Médico registrado correctamente.";
    } else {
        echo "Error: " . $insert->error;
    }

    $insert->close();
    $conexion->close();
    exit();
}

/* ========================================
   3. REGISTRAR USUARIO NORMAL + POLIZA
========================================= */

if ($tipo === "usuario") {

    // Datos del usuario
    $correo = $_POST['correo'];
    $pass = $_POST['contraseña'];

    if (empty($correo) || empty($pass)) {
        die("Correo y contraseña obligatorios");
    }

    // Verificar correo duplicado
    $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Este correo ya está registrado.");
    }

    $check->close();

    // Crear usuario normal
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $rol = "Usuario";

    $insertU = $conexion->prepare("INSERT INTO usuarios (correo, contraseña, rol) VALUES (?, ?, ?)");
    $insertU->bind_param("sss", $correo, $hash, $rol);

    if (!$insertU->execute()) {
        die("Error creando usuario: " . $insertU->error);
    }

    // Obtener el ID generado
    $usuario_fk = $insertU->insert_id;
    $insertU->close();

    /* ========================================
       3.1 INSERTAR POLIZA
    ========================================= */

    $sql = "INSERT INTO poliza (
        usuario_fk, poliza_id, id_hospital, tipo_poliza, fecha_inicio, fecha_fin, fecha_efectiva,
        id_cobertura, id_servicio, nombre, apellido1, apellido2, telefono, fecha_nacimiento, sexo,
        estatus, edad, calle, num_exterior, num_interior, colonia, codigo_postal, municipio_fk, entidad_fk
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $insertP = $conexion->prepare($sql);

    $insertP->bind_param(
        "isssssssissssssisssssis",
        $usuario_fk,
        $_POST['poliza_id'],
        $_POST['id_hospital'],
        $_POST['tipo_poliza'],
        $_POST['fecha_inicio'],
        $_POST['fecha_fin'],
        $_POST['fecha_efectiva'],
        $_POST['id_cobertura'],
        $_POST['id_servicio'],
        $_POST['nombre'],
        $_POST['apellido1'],
        $_POST['apellido2'],
        $_POST['telefono'],
        $_POST['fecha_nacimiento'],
        $_POST['sexo'],
        $_POST['estatus'],
        $_POST['edad'],
        $_POST['calle'],
        $_POST['num_exterior'],
        $_POST['num_interior'],
        $_POST['colonia'],
        $_POST['codigo_postal'],
        $_POST['municipio_fk'],
        $_POST['entidad_fk']
    );

    if ($insertP->execute()) {
        echo "Usuario registrado con póliza correctamente.";
    } else {
        echo "Error: " . $insertP->error;
    }

    $insertP->close();
    $conexion->close();
    exit();
}

echo "Error: tipo de registro inválido.";
?>
