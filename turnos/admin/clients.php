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

// Procesar edición de cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $client_id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);

        $sql = "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $client_id";

        if ($conn->query($sql)) {
            $success = "Cliente actualizado exitosamente";
        } else {
            $error = "Error al actualizar el cliente";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = $conn->real_escape_string($_POST['new_name']);
        $email = $conn->real_escape_string($_POST['new_email']);
        $phone = $conn->real_escape_string($_POST['new_phone']);
        $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT); // Hash the password

        $sql = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$password')";

        if ($conn->query($sql)) {
            $success = "Cliente agregado exitosamente";
        } else {
            $error = "Error al agregar el cliente: " . $conn->error;
        }
    }
}

// Obtener lista de clientes con sus estadísticas
$clients = $conn->query("
    SELECT
        u.*,
        COUNT(a.id) as total_appointments,
        SUM(s.price) as total_spent
    FROM users u
    LEFT JOIN appointments a ON u.id = a.user_id
    LEFT JOIN services s ON a.service_id = s.id
    WHERE u.is_admin = 0
    GROUP BY u.id
    ORDER BY u.name
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Clientes - Tu Salón</title>
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
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <h2>Agregar Nuevo Cliente</h2>
        <form method="POST" class="client-form">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="new_name">Nombre:</label>
                <input type="text" id="new_name" name="new_name" required>
            </div>
            <div class="form-group">
                <label for="new_email">Email:</label>
                <input type="email" id="new_email" name="new_email" required>
            </div>
            <div class="form-group">
                <label for="new_phone">Teléfono:</label>
                <input type="tel" id="new_phone" name="new_phone" required>
            </div>
            <div class="form-group">
                <label for="new_password">Contraseña:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Agregar Cliente</button>
        </form>

        <div class="clients-list">
            <h2>Clientes Existentes</h2>
            <?php while($client = $clients->fetch_assoc()): ?>
                <div class="client-card">
                    <div class="client-info">
                        <h3><?php echo htmlspecialchars($client['name']); ?></h3>
                        <p>Email: <?php echo htmlspecialchars($client['email']); ?></p>
                        <p>Teléfono: <?php echo htmlspecialchars($client['phone']); ?></p>
                        <p>Total Turnos: <?php echo $client['total_appointments']; ?></p>
                        <p>Total Gastado: $<?php echo number_format($client['total_spent'], 2); ?></p>
                    </div>
                    <div class="client-actions">
                        <button onclick="editClient(<?php echo htmlspecialchars(json_encode($client)); ?>)"
                                class="btn btn-secondary">Editar</button>
                        <a href="client_history.php?id=<?php echo $client['id']; ?>"
                           class="btn btn-primary">Ver Historial</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal de edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Editar Cliente</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label for="editName">Nombre:</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editPhone">Teléfono:</label>
                    <input type="tel" id="editPhone" name="phone" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
        function editClient(client) {
            document.getElementById('editId').value = client.id;
            document.getElementById('editName').value = client.name;
            document.getElementById('editEmail').value = client.email;
            document.getElementById('editPhone').value = client.phone;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Cerrar modal si se hace clic fuera de él
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>
     <script src="../js/main.js"></script>
</body>
</html>
