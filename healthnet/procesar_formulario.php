<?php
session_start();

$precios_especialidades = [
    'cardiologia' => 900,
    'pediatria' => 800,
    'ginecologia' => 940,
    'traumatologia' => 1000,
    'neurologia' => 850,
    'oncologia' => 700,
    'oftamólogo' => 600,
    'cirugía' => 650,
    'dermatología' => 800,
    'medicina_general' => 820
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospital = htmlspecialchars(trim($_POST['hospital']));
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telefono = htmlspecialchars(trim($_POST['telefono']));
    $especialidad = htmlspecialchars(trim($_POST['especialidad']));
    $fecha_preferida = htmlspecialchars(trim($_POST['fecha_preferida']));
    $mensaje = htmlspecialchars(trim($_POST['mensaje']));
    $total = htmlspecialchars(trim($_POST['total']));
    
    if (empty($hospital) || empty($nombre) || empty($email) || empty($telefono) || empty($especialidad) || empty($fecha_preferida)) {
        header("Location: index.php?error=1");
        exit();
    }
    

    $usuario_info = isset($_SESSION['usuario']) ? $_SESSION['usuario']['nombre'] . ' (' . $_SESSION['usuario']['tipo'] . ')' : 'Visitante';
    
    $datos = "Nueva solicitud de cita:\n";
    $datos .= "Fecha de solicitud: " . date('Y-m-d H:i:s') . "\n";
    $datos .= "Usuario: " . $usuario_info . "\n";
    $datos .= "Hospital: " . $hospital . "\n";
    $datos .= "Nombre: " . $nombre . "\n";
    $datos .= "Email: " . $email . "\n";
    $datos .= "Teléfono: " . $telefono . "\n";
    $datos .= "Especialidad: " . $especialidad . "\n";
    $datos .= "Total: " . $total . "\n";  
    $datos .= "Fecha preferida: " . $fecha_preferida . "\n";
    $datos .= "Mensaje: " . ($mensaje ?: 'No especificado') . "\n";
    $datos .= "----------------------------------------\n\n";
    

    $archivo = 'citas.txt';
    if (file_put_contents($archivo, $datos, FILE_APPEND | LOCK_EX)) {
        header("Location: index.php?exito=1");
        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }
} else {
    header("Location: inicios.php");
    exit();
}
?>