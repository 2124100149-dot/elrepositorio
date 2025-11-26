<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - HealthNet</title>
    <link rel="stylesheet" href="../../assets/css/estilo_A.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-top">
                <div class="admin-title">
                    <h1>Bienvenido a HealthNet</h1>
                    <p>Hola, <?php echo $_SESSION['correo']; ?> - Rol: <?php echo $_SESSION['rol']; ?></p>
                </div>
                <div class="header-actions">
                    <a href="../../public/index.php" class="btn btn-primary">Cerrar Sesión</a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $estadisticas['total_usuarios']; ?></div>
                    <div class="stat-label">Usuarios Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍⚕️</div>
                    <div class="stat-number"><?php echo $estadisticas['total_medicos']; ?></div>
                    <div class="stat-label">Médicos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👤</div>
                    <div class="stat-number"><?php echo $estadisticas['total_pacientes']; ?></div>
                    <div class="stat-label">Pacientes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $estadisticas['total_citas']; ?></div>
                    <div class="stat-label">Citas Hoy</div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <main class="content-main">
                <section style="margin-bottom: 40px;">
                    <h2 class="section-title">Usuarios Recientes</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 30px; color: #666;">
                                            No hay usuarios registrados
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $usuario['tipo'] === 'Medico' ? 'badge-info' : ($usuario['tipo'] === 'Admin' ? 'badge-warning' : 'badge-success'); ?>">
                                                <?php echo $usuario['tipo']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $usuario['fecha_registro']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <aside class="content-sidebar">
                <section style="margin-bottom: 30px;">
                    <h2 class="section-title">Acciones Rápidas</h2>
                    <div class="quick-actions">
                        <button class="action-btn" onclick="agregarUsuario()">
                            ➕ Agregar Usuario
                        </button>
                        <button class="action-btn" onclick="generarReporte()">
                            📊 Generar Reporte
                        </button>
                        <button class="action-btn" onclick="gestionarCitas()">
                            📅 Gestionar Citas
                        </button>
                        <button class="action-btn" onclick="configuracionSistema()">
                            ⚙️ Configuración
                        </button>
                    </div>
                </section>

                <section>
                    <h2 class="section-title">Información del Sistema</h2>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">✅</div>
                            <div class="activity-content">
                                <p>Sistema funcionando correctamente</p>
                                <div class="activity-time">Estado: Óptimo</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">📊</div>
                            <div class="activity-content">
                                <p><?php echo $estadisticas['total_usuarios']; ?> usuarios en total</p>
                                <div class="activity-time">Base de datos</div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <div id="modalUsuario" class="modal">
        <div class="modal-content">
            <h3>Agregar Nuevo Usuario</h3>
            <p>Esta funcionalidad estará disponible próximamente.</p>
            <div class="modal-buttons">
                <button class="close-btn" onclick="cerrarModal('modalUsuario')">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
</body>
</html>