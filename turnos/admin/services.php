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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            $credits_cost = intval($_POST['credits_cost']); // Changed to credits_cost
            $duration = intval($_POST['duration']);

            if ($_POST['action'] == 'add') {
                $sql = "INSERT INTO services (name, description, credits_cost, duration)
                        VALUES ('$name', '$description', $credits_cost, $duration)"; // Changed to credits_cost
            } else {
                $id = intval($_POST['id']);
                $sql = "UPDATE services
                        SET name = '$name', description = '$description',
                            credits_cost = $credits_cost, duration = $duration
                        WHERE id = $id"; // Changed to credits_cost
            }

            if ($conn->query($sql)) {
                $success = "Servicio " . ($_POST['action'] == 'add' ? "agregado" : "actualizado") . " exitosamente";
            } else {
                $error = "Error al " . ($_POST['action'] == 'add' ? "agregar" : "actualizar") . " el servicio";
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = intval($_POST['id']);
            if ($conn->query("DELETE FROM services WHERE id = $id")) {
                $success = "Servicio eliminado exitosamente";
            } else {
                $error = "Error al eliminar el servicio";
            }
        }
    }
}

// Obtener lista de servicios
$services = $conn->query("SELECT * FROM services ORDER BY name");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Servicios - Tu Salón</title>
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

        <form class="service-form" method="POST">
            <h2>Agregar/Editar Servicio</h2>
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Nombre del Servicio:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="credits_cost">Costo en Créditos:</label>
                <input type="number" id="credits_cost" name="credits_cost" step="1" required>
            </div>
            <div class="form-group">
                <label for="duration">Duración (minutos):</label>
                <input type="number" id="duration" name="duration" required>
            </div>
            <button type="submit" class="btn btn-primary">Agregar Servicio</button>
        </form>

        <div class="services-list">
            <h2>Servicios Actuales</h2>
            <?php while($service = $services->fetch_assoc()): ?>
                <div class="service-card">
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <p>Costo: <?php echo $service['credits_cost']; ?> créditos</p>
                    <p>Duración: <?php echo $service['duration']; ?> minutos</p>
                    <div class="service-actions">
                        <button onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)"
                                class="btn btn-secondary">Editar</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('¿Está seguro de eliminar este servicio?')">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script>
        function editService(service) {
            document.querySelector('[name="action"]').value = 'edit';
            document.querySelector('[name="name"]').value = service.name;
            document.querySelector('[name="description"]').value = service.description;
            document.querySelector('[name="credits_cost"]').value = service.credits_cost;
            document.querySelector('[name="duration"]').value = service.duration;

            const form = document.querySelector('.service-form');
            const hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'id';
            hiddenId.value = service.id;
            form.appendChild(hiddenId);

            document.querySelector('.btn-primary').textContent = 'Actualizar Servicio';
        }
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
