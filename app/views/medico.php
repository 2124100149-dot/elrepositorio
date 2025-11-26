<?php
// Verificar sesión al inicio de la vista
if (!isset($_SESSION['correo']) || !isset($_SESSION['rol'])) {
    header("Location: " . BASE_URL . "/public/index.php");
    exit();
}

// Incluir configuración para las rutas
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médico HealthNet</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/estilo_M.css">
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="user-info">
                    <h1>Bienvenido, Dr. <?php echo htmlspecialchars(explode('@', $_SESSION['correo'])[0]); ?></h1>
                    <p>Panel de Control Médico - HealthNet</p>
                </div>
                <div class="header-actions">
                    <a href="<?php echo BASE_URL; ?>/public/logout.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <nav class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#dashboard" class="active" onclick="mostrarSeccion('dashboard')">📊 Dashboard</a></li>
                    <li><a href="#citas" onclick="mostrarSeccion('citas')">📅 Mis Citas</a></li>
                    <li><a href="#buzon" onclick="mostrarSeccion('buzon')">📬 Buzón de Mensajes</a></li>
                    <li><a href="#perfil" onclick="mostrarSeccion('perfil')">👤 Mi Perfil</a></li>
                </ul>
            </nav>

            <main class="main-content">
                <!-- Dashboard Section -->
                <section id="seccion-dashboard" class="dashboard-section">
                    <h2 class="section-title">Dashboard Médico</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $estadisticas['total_citas']; ?></div>
                            <div class="stat-label">Citas Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $estadisticas['citas_hoy']; ?></div>
                            <div class="stat-label">Citas Hoy</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $estadisticas['proximas_citas']; ?></div>
                            <div class="stat-label">Próximas Citas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($mensajes); ?></div>
                            <div class="stat-label">Mensajes</div>
                        </div>
                    </div>

                    <h3 style="margin: 30px 0 20px 0; color: #0d47a1; font-size: 1.3rem;">Próximas Citas</h3>
                    <div class="citas-list">
                        <?php if (empty($citas)): ?>
                            <div class="cita-item">
                                <p style="text-align: center; color: #666; font-style: italic;">
                                    No hay citas agendadas.
                                </p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $citas_proximas = array_slice($citas, 0, 3);
                            foreach ($citas_proximas as $cita): 
                            ?>
                                <div class="cita-item">
                                    <div class="cita-header">
                                        <span class="cita-paciente">
                                            <?php echo $cita['paciente_nombre'] . ' ' . ($cita['apellido'] ?? ''); ?>
                                        </span>
                                        <span class="cita-fecha">
                                            <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?> 
                                            a las <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                        </span>
                                    </div>
                                    <div class="cita-detalles">
                                        <p><strong>Teléfono:</strong> <?php echo $cita['telefono'] ?? 'No disponible'; ?></p>
                                        <p><strong>Motivo:</strong> <?php echo $cita['motivo'] ?? 'Consulta general'; ?></p>
                                        <p><strong>Estado:</strong> 
                                            <span style="color: <?php echo $cita['estado'] == 'confirmada' ? '#27ae60' : '#e74c3c'; ?>">
                                                <?php echo ucfirst($cita['estado'] ?? 'pendiente'); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Otras secciones (citas, buzon, perfil) -->
                <section id="seccion-citas" class="dashboard-section" style="display: none;">
                    <h2>Mis Citas - En desarrollo</h2>
                </section>

                <section id="seccion-buzon" class="dashboard-section" style="display: none;">
                    <h2>Buzón de Mensajes - En desarrollo</h2>
                </section>

                <section id="seccion-perfil" class="dashboard-section" style="display: none;">
                    <h2>Mi Perfil - En desarrollo</h2>
                </section>
            </main>
        </div>
    </div>

    <script src="<?php echo JS_URL; ?>/medico.js"></script>
</body>
</html>