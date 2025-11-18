<?php
include 'conexion.php';

$db = conectarDB();

if($db->connect_error) {
    die("Error de conexión: " . $db->connect_error);
} else {
    echo "¡Conexión exitosa a la base de datos!";
    
    // Probar consulta
    $resultado = $db->query("SELECT * FROM usuarios LIMIT 1");
    if($resultado->num_rows > 0) {
        echo "<br>Se encontraron usuarios en la base de datos";
    }
}
?>