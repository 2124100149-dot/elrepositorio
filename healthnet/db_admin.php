<?php
session_start();


// Datos de ejemplo (en un sistema real esto vendría de una base de datos)
$usuarios = [
    [
        'id' => 1,
        'nombre' => 'Dr. Carlos Rodríguez',
        'email' => 'carlos@hospital.com',
        'telefono' => '(555) 123-4567',
        'tipo' => 'Médico',
        'especialidad' => 'Cardiología',
        'fecha_registro' => '2024-01-15',
        'estado' => 'Activo'
    ],
    [
        'id' => 2,
        'nombre' => 'Ana Martínez López',
        'email' => 'ana.martinez@email.com',
        'telefono' => '(555) 987-6543',
        'tipo' => 'Paciente',
        'especialidad' => 'N/A',
        'fecha_registro' => '2024-02-20',
        'estado' => 'Activo'
    ],
    [
        'id' => 3,
        'nombre' => 'Dr. María González',
        'email' => 'maria.gonzalez@hospital.com',
        'telefono' => '(555) 456-7890',
        'tipo' => 'Médico',
        'especialidad' => 'Pediatría',
        'fecha_registro' => '2024-01-10',
        'estado' => 'Activo'
    ],
    [
        'id' => 4,
        'nombre' => 'Roberto Sánchez',
        'email' => 'roberto.sanchez@email.com',
        'telefono' => '(555) 234-5678',
        'tipo' => 'Paciente',
        'especialidad' => 'N/A',
        'fecha_registro' => '2024-03-05',
        'estado' => 'Inactivo'
    ]
];

// Leer citas agendadas
$citas = [];
if (file_exists('citas.txt')) {
    $citas_content = file_get_contents('citas.txt');
    $citas_array = explode("----------------------------------------", $citas_content);
    
    foreach ($citas_array as $cita) {
        if (trim($cita)) {
            $citas[] = $cita;
        }
    }
    $citas = array_reverse($citas);
}

// Estadísticas
$total_usuarios = count($usuarios);
$total_medicos = count(array_filter($usuarios, function($user) {
    return $user['tipo'] === 'Médico';
}));
$total_pacientes = count(array_filter($usuarios, function($user) {
    return $user['tipo'] === 'Paciente';
}));
$total_citas = count($citas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Hospital Healthnet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .admin-title h1 {
            color: #1a6fc4;
            font-size: 2.2rem;
            margin-bottom: 5px;
        }

        .admin-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 111, 196, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #1a6fc4;
            border: 2px solid #1a6fc4;
        }

        .btn-secondary:hover {
            background: #1a6fc4;
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #1a6fc4;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: bold;
            color: #1a6fc4;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }

        /* Main Content */
        .admin-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 25px;
        }

        .content-main {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .content-sidebar {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-title {
            color: #1a6fc4;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e3f2fd;
            font-size: 1.4rem;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e3f2fd;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .data-table th {
            background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e3f2fd;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-success {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .badge-warning {
            background: #fff3e0;
            color: #ef6c00;
        }

        .badge-info {
            background: #e3f2fd;
            color: #1a6fc4;
        }

        /* User List */
        .user-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e3f2fd;
            transition: background-color 0.3s;
        }

        .user-item:hover {
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .user-info h4 {
            color: #1a6fc4;
            margin-bottom: 5px;
        }

        .user-info p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            gap: 15px;
            margin-bottom: 25px;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #1a6fc4;
            font-weight: 500;
        }

        .action-btn:hover {
            background: #1a6fc4;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 111, 196, 0.3);
        }

        /* Recent Activity */
        .activity-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #e3f2fd;
            display: flex;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e3f2fd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #1a6fc4;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content p {
            margin-bottom: 3px;
            color: #333;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .admin-content {
                grid-template-columns: 1fr;
            }
            
            .content-sidebar {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-actions {
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-top">
                <div class="admin-title">
                    <h1>Panel de Administración</h1>
                    <p>Hospital Healthnet - Control total del sistema</p>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary">Volver al Sitio</a>
                    <a href="?logout=1" class="btn btn-primary">Cerrar Sesión</a>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_usuarios; ?></div>
                    <div class="stat-label">Usuarios Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍⚕️</div>
                    <div class="stat-number"><?php echo $total_medicos; ?></div>
                    <div class="stat-label">Médicos Registrados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👤</div>
                    <div class="stat-number"><?php echo $total_pacientes; ?></div>
                    <div class="stat-label">Pacientes Activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $total_citas; ?></div>
                    <div class="stat-label">Citas Agendadas</div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="admin-content">
            <!-- Main Section -->
            <main class="content-main">
                <!-- Usuarios Registrados -->
                <section style="margin-bottom: 40px;">
                    <h2 class="section-title">Usuarios Registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Tipo</th>
                                    <th>Especialidad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id']; ?></td>
                                    <td><strong><?php echo $usuario['nombre']; ?></strong></td>
                                    <td><?php echo $usuario['email']; ?></td>
                                    <td><?php echo $usuario['telefono']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $usuario['tipo'] === 'Médico' ? 'badge-info' : 'badge-success'; ?>">
                                            <?php echo $usuario['tipo']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $usuario['especialidad']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $usuario['estado'] === 'Activo' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $usuario['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 8px 15px; font-size: 0.9rem;">
                                            Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Citas Recientes -->
                <section>
                    <h2 class="section-title">Citas Recientes</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Hospital</th>
                                    <th>Especialidad</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($citas)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 30px; color: #666;">
                                            No hay citas agendadas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php for ($i = 0; $i < min(5, count($citas)); $i++): ?>
                                        <?php
                                        $lineas = explode("\n", $citas[$i]);
                                        $paciente = $hospital = $especialidad = $fecha = $total = '';
                                        foreach ($lineas as $linea) {
                                            if (strpos($linea, 'Nombre:') !== false) {
                                                $paciente = trim(str_replace('Nombre:', '', $linea));
                                            } elseif (strpos($linea, 'Hospital:') !== false) {
                                                $hospital = trim(str_replace('Hospital:', '', $linea));
                                            } elseif (strpos($linea, 'Especialidad:') !== false) {
                                                $especialidad = trim(str_replace('Especialidad:', '', $linea));
                                            } elseif (strpos($linea, 'Fecha preferida:') !== false) {
                                                $fecha = trim(str_replace('Fecha preferida:', '', $linea));
                                            } elseif (strpos($linea, 'Total:') !== false) {
                                                $total = trim(str_replace('Total:', '', $linea));
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $paciente; ?></strong></td>
                                            <td><?php echo $hospital; ?></td>
                                            <td><?php echo $especialidad; ?></td>
                                            <td><?php echo $fecha; ?></td>
                                            <td><strong><?php echo $total; ?></strong></td>
                                            <td>
                                                <span class="badge badge-success">Confirmada</span>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <!-- Sidebar -->
            <aside class="content-sidebar">
                <!-- Acciones Rápidas -->
                <section style="margin-bottom: 30px;">
                    <h2 class="section-title">Acciones Rápidas</h2>
                    <div class="quick-actions">
                        <div class="action-btn" onclick="alert('Función de agregar usuario')">
                            ➕ Agregar Usuario
                        </div>
                        <div class="action-btn" onclick="alert('Función de generar reporte')">
                            📊 Generar Reporte
                        </div>
                        <div class="action-btn" onclick="alert('Función de configuración')">
                            ⚙️ Configuración
                        </div>
                        <div class="action-btn" onclick="alert('Función de backup')">
                            💾 Backup Sistema
                        </div>
                    </div>
                </section>

                <!-- Actividad Reciente -->
                <section>
                    <h2 class="section-title">Actividad Reciente</h2>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">👤</div>
                            <div class="activity-content">
                                <p>Nuevo paciente registrado</p>
                                <div class="activity-time">Hace 5 minutos</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">📅</div>
                            <div class="activity-content">
                                <p>Cita agendada - Cardiología</p>
                                <div class="activity-time">Hace 15 minutos</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">👨‍⚕️</div>
                            <div class="activity-content">
                                <p>Dr. Rodríguez actualizó perfil</p>
                                <div class="activity-time">Hace 1 hora</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">💰</div>
                            <div class="activity-content">
                                <p>Pago procesado - $900 MXN</p>
                                <div class="activity-time">Hace 2 horas</div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <script>
        // Funcionalidades básicas del dashboard
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Panel de administración cargado');
            
            // Ejemplo de funcionalidad adicional
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Aquí puedes agregar más funcionalidades
                });
            });
        });
    </script>
</body>
</html>