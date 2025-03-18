<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $conn->real_escape_string($_POST['code']);

    // Verificar si el cupón existe y está disponible
    $coupon_sql = "SELECT * FROM coupons WHERE code = '$code' AND NOT is_used";
    $coupon_result = $conn->query($coupon_sql);

    if ($coupon_result->num_rows > 0) {
        $coupon = $coupon_result->fetch_assoc();

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Marcar cupón como usado
            $update_coupon = "UPDATE coupons 
                            SET is_used = true, 
                                used_by = '$user_id', 
                                used_at = NOW() 
                            WHERE code = '$code'";
            $conn->query($update_coupon);

            // Actualizar o crear registro de créditos
            $check_credits = "SELECT id FROM credits WHERE user_id = '$user_id'";
            $credits_result = $conn->query($check_credits);

            if ($credits_result->num_rows > 0) {
                $update_credits = "UPDATE credits 
                                 SET amount = amount + {$coupon['amount']},
                                     updated_at = NOW()
                                 WHERE user_id = '$user_id'";
                $conn->query($update_credits);
            } else {
                $insert_credits = "INSERT INTO credits (user_id, amount) 
                                 VALUES ('$user_id', {$coupon['amount']})";
                $conn->query($insert_credits);
            }

            // Registrar transacción
            $insert_transaction = "INSERT INTO credit_transactions 
                                 (user_id, amount, type, reference_id) 
                                 VALUES ('$user_id', {$coupon['amount']}, 'coupon', '{$coupon['id']}')";
            $conn->query($insert_transaction);

            $conn->commit();
            $success = "¡Cupón canjeado exitosamente! Se agregaron {$coupon['amount']} créditos a tu cuenta.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al procesar el cupón. Por favor, intenta nuevamente.";
        }
    } else {
        $error = "Cupón inválido o ya utilizado.";
    }
}

// Obtener créditos actuales
$credits_sql = "SELECT amount FROM credits WHERE user_id = '$user_id'";
$credits_result = $conn->query($credits_sql);
$current_credits = $credits_result->num_rows > 0 ? $credits_result->fetch_assoc()['amount'] : 0;

// Obtener historial de transacciones
$transactions_sql = "
    SELECT 
        ct.*,
        CASE 
            WHEN ct.type = 'coupon' THEN c.code
            WHEN ct.type = 'appointment' THEN CONCAT(s.name, ' - ', DATE_FORMAT(a.appointment_date, '%d/%m/%Y'))
            ELSE NULL
        END as reference_details
    FROM credit_transactions ct
    LEFT JOIN coupons c ON ct.type = 'coupon' AND ct.reference_id = c.id
    LEFT JOIN appointments a ON ct.type = 'appointment' AND ct.reference_id = a.id
    LEFT JOIN services s ON a.service_id = s.id
    WHERE ct.user_id = '$user_id'
    ORDER BY ct.created_at DESC
";
$transactions = $conn->query($transactions_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canjear Cupón - Tu Salón</title>
    <link rel="stylesheet" href="../css/styles.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <div class="credits-section">
            <div class="bg-white p-6 rounded-lg shadow-md mb-8" style="margin-top: 2rem;">
                <h2 class="text-2xl font-semibold mb-4 text-gray-900">Mis Créditos</h2>
                <div class="current-credits">
                    <p class="text-lg">Créditos disponibles: <strong><?php echo $current_credits; ?></strong></p>
                </div>
            </div>

            
            <form method="POST" class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h3 class="text-xl font-semibold mb-4 text-gray-900">Canjear Cupón</h3>
                 <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="code">Código del Cupón:</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <button type="submit" class="btn btn-primary">Canjear</button>
            </form>

            <div class="bg-white p-6 rounded-lg shadow-md" style="margin-bottom: 2rem;">
                <h3 class="text-xl font-semibold mb-4 text-gray-900">Historial de Transacciones</h3>
                <div class="table-responsive">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left">
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">Monto</th>
                                <th class="px-4 py-2">Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($transaction = $transactions->fetch_assoc()): ?>
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-center"><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <?php
                                        switch($transaction['type']) {
                                            case 'coupon':
                                                echo 'Cupón';
                                                break;
                                            case 'appointment':
                                                echo 'Turno';
                                                break;
                                            case 'refund':
                                                echo 'Reembolso';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td class="px-4 py-2 <?php echo $transaction['amount'] > 0 ? 'text-green-600' : 'text-red-600'; ?> text-center">
                                        <?php echo $transaction['amount'] > 0 ? '+' : ''; ?>
                                        <?php echo $transaction['amount']; ?>
                                    </td>
                                    <td class="px-4 py-2 text-center" ><?php echo $transaction['reference_details'] ?? '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
