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

$client_id = intval($_GET['id']);

// Obtener información del cliente
$client = $conn->query("SELECT * FROM users WHERE id = $client_id")->fetch_assoc();

// Obtener historial de turnos
$appointments = $conn->query("
    SELECT
        a.*,
        s.name as service_name,
        s.price as service_price
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    WHERE a.user_id = $client_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cliente - Tu Salón</title>
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

        <div class="client-details">
            <h2>Información del Cliente</h2>
             <h3><?php echo htmlspecialchars($client['name']); ?></h3>
            <p>Email: <?php echo htmlspecialchars($client['email']); ?></p>
            <p>Teléfono: <?php echo htmlspecialchars($client['phone']); ?></p>
            <p>Fecha de registro: <?php echo date('d/m/Y', strtotime($client['created_at'])); ?></p>
        </div>

        <div class="appointments-history">
            <h2>Historial de Turnos</h2>
            <?php if ($appointments->num_rows > 0): ?>
                <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total_spent = 0;
                    while($appointment = $appointments->fetch_assoc()):
                        $total_spent += $appointment['service_price'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                            <td>$<?php echo number_format($appointment['service_price'], 2); ?></td>
                            <td><?php echo ucfirst($appointment['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total Gastado:</strong></td>
                            <td colspan="2">$<?php echo number_format($total_spent, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            <?php else: ?>
                <p>No hay turnos registrados para este cliente.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/main.js"></script>
</body>
</html>
