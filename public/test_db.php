<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "Conexión exitosa a la base de datos!";
    
    // Probar una consulta simple
    $result = $db->query("SELECT COUNT(*) as total FROM usuario");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Total de usuarios: " . $row['total'];
    } else {
        echo "Error en la consulta: " . $db->error;
    }
    
    $db->close();
} else {
    echo "Error al conectar a la base de datos";
}
?>