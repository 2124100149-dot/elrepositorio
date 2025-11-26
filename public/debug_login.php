<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/UserModel.php';

echo "<h1>Debug del Sistema de Login</h1>";

// Test 1: Sesión actual
echo "<h2>1. Estado de la Sesión Actual</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

// Test 2: Probar login directo
echo "<h2>2. Probar Login Directo</h2>";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>Intentando login con: $correo</p>";
    
    $userModel = new UserModel();
    $user = $userModel->login($correo, $password);
    
    if ($user) {
        echo "<p style='color: green;'>✅ Login exitoso</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Establecer sesión
        $_SESSION['correo'] = $user['correo'];
        $_SESSION['rol'] = $user['rol'];
        
        echo "<p>Sesión establecida:</p>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<p><a href='" . BASE_URL . "/public/medico.php'>Ir a Médico</a></p>";
        echo "<p><a href='" . BASE_URL . "/public/admin.php'>Ir a Admin</a></p>";
        echo "<p><a href='" . BASE_URL . "/public/paciente.php'>Ir a Paciente</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Login fallido</p>";
    }
}

// Formulario de prueba
echo "<h2>3. Formulario de Prueba</h2>";
echo "<form method='POST'>";
echo "Correo: <input type='text' name='correo' value='medico@healthnet.com'><br>";
echo "Password: <input type='password' name='password' value='123456'><br>";
echo "<button type='submit'>Probar Login</button>";
echo "</form>";

// Test 3: Verificar usuarios en BD
echo "<h2>4. Verificar Base de Datos</h2>";
try {
    $userModel = new UserModel();
    
    // Probar conexión
    echo "<p>Probando conexión a BD...</p>";
    
    // Probar usuarios específicos
    $test_users = [
        'admin@healthnet.com' => '123456',
        'medico@healthnet.com' => '123456', 
        'usuario@healthnet.com' => '123456'
    ];
    
    foreach ($test_users as $email => $pass) {
        $result = $userModel->login($email, $pass);
        if ($result) {
            echo "<p style='color: green;'>✅ $email - FUNCIONA</p>";
        } else {
            echo "<p style='color: red;'>❌ $email - FALLA</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>