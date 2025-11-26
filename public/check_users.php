<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Error: No se pudo conectar a la base de datos.");
}

echo "<h2>Verificando usuarios en la base de datos</h2>";

// Verificar la estructura de la tabla
$result = $db->query("DESCRIBE usuario");
echo "<h3>Estructura de la tabla 'usuario':</h3>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Mostrar los usuarios existentes
$result = $db->query("SELECT * FROM usuario");
echo "<h3>Usuarios en la base de datos:</h3>";
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Correo</th><th>Rol</th><th>Contraseña (MD5)</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . ($row['id_usuario'] ?? $row['usuario_pk'] ?? $row['id'] ?? 'N/A') . "</td>";
        echo "<td>" . $row['correo'] . "</td>";
        echo "<td>" . $row['rol'] . "</td>";
        echo "<td>" . $row['contrasena'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay usuarios en la base de datos</p>";
}

// Probar un login específico
echo "<h3>Probar consulta de login:</h3>";
$test_email = "admin@healthnet.com"; // Cambia por un email que exista
$test_password = "123456"; // Cambia por una contraseña que exista

$stmt = $db->prepare("SELECT correo, rol FROM usuario WHERE correo = ? AND contrasena = MD5(?)");
$stmt->bind_param("ss", $test_email, $test_password);
$stmt->execute();
$stmt->store_result();

echo "<p>Probando login con: $test_email / $test_password</p>";
echo "<p>Usuarios encontrados: " . $stmt->num_rows . "</p>";

if ($stmt->num_rows > 0) {
    $stmt->bind_result($correo_db, $rol_db);
    $stmt->fetch();
    echo "<p style='color: green;'>✅ Login exitoso: $correo_db - $rol_db</p>";
} else {
    echo "<p style='color: red;'>❌ Login fallido</p>";
}

$stmt->close();
$db->close();
?>