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

// Handle actions (generate, edit, delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'generate') {
        $amount = intval($_POST['amount']);
        $quantity = intval($_POST['quantity']);

        for ($i = 0; $i < $quantity; $i++) {
            do {
                $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                $check = $conn->query("SELECT id FROM coupons WHERE code = '$code'");
            } while ($check->num_rows > 0);

            $sql = "INSERT INTO coupons (code, amount, created_by) VALUES ('$code', $amount, '$user_id')";
            if ($conn->query($sql)) {
                $success = "Se generaron $quantity cupones exitosamente.";
            } else {
                $error = "Error al generar cupones: " . $conn->error;
            }
        }


    } elseif ($action == 'edit' && isset($_POST['id'])) {
        $coupon_id = intval($_POST['id']);
        $new_amount = intval($_POST['amount']);
        $update_sql = "UPDATE coupons SET amount = $new_amount WHERE id = $coupon_id";
        if ($conn->query($update_sql)) {
            $success = "Cupón actualizado exitosamente.";
        } else {
            $error = "Error al actualizar el cupón: " . $conn->error;
        }

    } elseif ($action == 'delete' && isset($_POST['id'])) {
        $coupon_id = intval($_POST['id']);

        // Prevent deleting used coupons
        $check_used_sql = "SELECT is_used FROM coupons WHERE id = $coupon_id";
        $used_result = $conn->query($check_used_sql);
        if ($used_result && $used_result->fetch_assoc()['is_used']) {
            $error = "No se puede eliminar un cupón que ya ha sido utilizado.";
        } else {
            $delete_sql = "DELETE FROM coupons WHERE id = $coupon_id";
            if ($conn->query($delete_sql)) {
                $success = "Cupón eliminado exitosamente.";
            } else {
                $error = "Error al eliminar el cupón: " . $conn->error;
            }
        }
    }
}

// Obtener lista de cupones
$coupons = $conn->query("
    SELECT c.*, u.name as used_by_name, a.name as created_by_name
    FROM coupons c
    LEFT JOIN users u ON c.used_by = u.id
    LEFT JOIN users a ON c.created_by = a.id
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Cupones - Panel de Administración</title>
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

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-gray-900">Gestionar Cupones</h2>
            <form method="POST" class="coupon-form" id="couponForm">
                <input type="hidden" name="action" id="action" value="generate">
                <input type="hidden" name="id" id="couponId" value="">
                <div class="form-group">
                    <label for="amount">Monto de Créditos:</label>
                    <input type="number" id="amount" name="amount" step="1" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Cantidad de Cupones (solo para generar):</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="1">
                </div>
                <button type="submit" class="btn btn-primary" id="formButton">Generar Cupones</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-gray-900">Cupones Generados</h2>
            <div class="table-responsive">
                <table class="w-full">
                    <thead>
                        <tr class="text-left">
                            <th class="px-4 py-2">Código</th>
                            <th class="px-4 py-2">Monto</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2">Usado Por</th>
                            <th class="px-4 py-2">Fecha de Uso</th>
                            <th class="px-4 py-2">Creado Por</th>
                            <th class="px-4 py-2">Fecha de Creación</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($coupon = $coupons->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?php echo $coupon['code']; ?></td>
                                <td class="px-4 py-2"><?php echo $coupon['amount']; ?></td>
                                <td class="px-4 py-2"><?php echo $coupon['is_used'] ? 'Usado' : 'Disponible'; ?></td>
                                <td class="px-4 py-2"><?php echo $coupon['used_by_name'] ?? '-'; ?></td>
                                <td class="px-4 py-2"><?php echo $coupon['used_at'] ? date('d/m/Y H:i', strtotime($coupon['used_at'])) : '-'; ?></td>
                                <td class="px-4 py-2"><?php echo $coupon['created_by_name']; ?></td>
                                <td class="px-4 py-2"><?php echo date('d/m/Y H:i', strtotime($coupon['created_at'])); ?></td>
                                <td class="px-4 py-2">
                                    <button onclick="editCoupon('<?php echo $coupon['id']; ?>', '<?php echo $coupon['amount']; ?>')" class="btn btn-secondary">Editar</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $coupon['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este cupón?');">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script>
        function editCoupon(id, amount) {
            document.getElementById('action').value = 'edit';
            document.getElementById('couponId').value = id;
            document.getElementById('amount').value = amount;
            document.getElementById('quantity').value = 1; // Ensure quantity is reset
            document.getElementById('quantity').disabled = true; // Disable quantity for editing
            document.getElementById('formButton').textContent = 'Actualizar Cupón';
            window.scrollTo({ top: 0, behavior: 'smooth' });

        }

        // Reset form when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('couponForm').reset();
            document.getElementById('quantity').disabled = false;

        });
    </script>
    <script src="../js/main.js"></script>
</body>
</html>
