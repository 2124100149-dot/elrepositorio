<?php
function conectarDB() {
    $host = "localhost";
    $usuario = "root";  // o el usuario que creaste
    $password = "";     // contraseña de MySQL
    $basedatos = "healthnet";
    
    try {
        $db = new mysqli($host, $usuario, $password, $basedatos);
        $db->set_charset("utf8");
        return $db;
    } catch (Exception $e) {
        echo "Error en la conexión: " . $e->getMessage();
        exit;
    }
}
?>