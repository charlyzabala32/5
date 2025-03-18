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

// Handle filtering
$filter_status = $_GET['status'] ?? 'pending'; // Default to 'pending'
$where_clause = '';

if ($filter_status != 'all') {
    $filter_status_escaped = $conn->real_escape_string($filter_status);
    $where_clause = "WHERE a.status = '$filter_status_escaped'";
}

// Obtener todos los turnos (filtered)
$appointments_sql = "SELECT a.*, u.name as user_name, s.name as service_name
                    FROM appointments a
                    JOIN users u ON a.user_id = u.id
                    JOIN services s ON a.service_id = s.id
                    $where_clause
                    AND a.appointment_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                    ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$appointments = $conn->query($appointments_sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Turnos - Panel de Administración</title>
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
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-gray-900">Turnos Reservados</h2>


            <form method="GET" class="mb-4">
                <label for="status" class="mr-2">Filtrar por estado:</label>
                <select name="status" id="status" class="border rounded p-1">
                    <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>Todos</option>
                    <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="confirmed" <?php echo $filter_status == 'confirmed' ? 'selected' : ''; ?>>Confirmado</option>
                    <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2">Filtrar</button>
            </form>

            <?php if ($appointments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left">
                                <th class="px-4 py-2">Cliente</th>
                                <th class="px-4 py-2">Servicio</th>
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Hora</th>
                                <th class="px-4 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($appointment = $appointments->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($appointment['user_name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td class="px-4 py-2"><?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td class="px-4 py-2"><?php echo $appointment['appointment_time']; ?></td>
                                <td class="px-4 py-2"><?php echo ucfirst($appointment['status']);?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No hay turnos reservados con el filtro seleccionado.</p>
            <?php endif; ?>
                  
        </div>
          <div class="bg-white p-6 rounded-lg shadow-md">

            <div class="mb-4">
                <a href="history.php" class="btn btn-secondary">Ver Historial (Más de 1 mes)</a>
            </div>
            </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="../js/main.js"></script>
</body>
</html>
