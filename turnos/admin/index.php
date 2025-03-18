<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Verificar si el usuario es administrador
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT is_admin FROM users WHERE id = $user_id");
$is_admin = $admin_check->fetch_assoc()['is_admin'] ?? 0;

if (!$is_admin) {
    header('Location: ../index.php');
    exit();
}

// Obtener estadísticas generales
$stats = $conn->query("
    SELECT
        COUNT(DISTINCT a.user_id) as total_clients,
        COUNT(a.id) as total_appointments,
        SUM(s.price) as total_revenue
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    WHERE a.status != 'cancelled'
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Tu Salón</title>
    <link rel="stylesheet" href="../css/styles.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
   <?php include '../includes/nav_admin.php'; ?>
    <div class="admin-container">
        <main>
            <section class="admin-stats">
                <div class="stat-card">
                    <h3>Total Clientes</h3>
                    <p><?php echo $stats['total_clients']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Turnos</h3>
                    <p><?php echo $stats['total_appointments']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Ingresos Totales</h3>
                    <p>$<?php echo number_format($stats['total_revenue'], 2); ?></p>
                </div>
            </section>

            <section class="admin-menu">
                <a href="services.php" class="admin-menu-item">
                    <h3>Gestionar Servicios</h3>
                    <p>Agregar, editar o eliminar servicios</p>
                </a>
                <a href="clients.php" class="admin-menu-item">
                    <h3>Gestionar Clientes</h3>
                    <p>Ver y editar información de clientes</p>
                </a>
                <a href="appointments.php" class="admin-menu-item">
                    <h3>Ver Turnos</h3>
                    <p>Gestionar turnos y reservas</p>
                </a>
            </section>
        </main>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/main.js"></script>
</body>
</html>
