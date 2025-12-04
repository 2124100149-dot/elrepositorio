<?php
function conectarDB() {
    $host = "yamabiko.proxy.rlwy.net";
    $port = "39168";
    $basedatos = "healthnet";
    $user = "root";
    $pass = "JBExxPHraFCRUGRIvODbqiGvtJyhpOwl";
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$basedatos;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch por default
            PDO::ATTR_EMULATE_PREPARES => false               // Preparadas reales
        ]);

        return $pdo;

    } catch (PDOException $e) {
        error_log("Error BD: " . $e->getMessage());
        echo "Error en la conexión.";
        exit;
    }
}
