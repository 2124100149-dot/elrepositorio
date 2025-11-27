<?php
function conectarDB() {
    // Configuración para XAMPP (local)
    $host = "localhost";
    $basedatos = "healthnet";
    $user = "root";
    $pass = ""; // Contraseña vacía por defecto en XAMPP
    $charset = 'utf8mb4';

    try {
        $db = new mysqli($host, $user, $pass, $basedatos);
        $db->set_charset("utf8");
        return $db;
    } catch (Exception $e) {
        echo "Error en la conexión: " . $e->getMessage();
        exit;
    }
}
?>