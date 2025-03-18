<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = intval($_POST['service']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['selected_time']);

    // Verificar si el usuario tiene suficientes créditos
    $service_sql = "SELECT credits_cost FROM services WHERE id = $service_id";
    $service_result = $conn->query($service_sql);
    $service = $service_result->fetch_assoc();

    $credits_sql = "SELECT amount FROM credits WHERE user_id = '$user_id'";
    $credits_result = $conn->query($credits_sql);
    $credits = $credits_result->num_rows > 0 ? $credits_result->fetch_assoc()['amount'] : 0;

    if ($credits < $service['credits_cost']) {
        echo json_encode(['error' => 'No tienes suficientes créditos para reservar este turno.']);
        exit();
    }

    // Verificar disponibilidad del horario
    $check_sql = "SELECT id FROM appointments 
                  WHERE appointment_date = '$date' 
                  AND appointment_time = '$time' 
                  AND status != 'cancelled'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo json_encode(['error' => 'El horario seleccionado ya no está disponible.']);
        exit();
    }

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Crear el turno
        $insert_sql = "INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, status) 
                      VALUES ('$user_id', $service_id, '$date', '$time', 'confirmed')";
        if (!$conn->query($insert_sql)) {
            throw new Exception("Error creating appointment: " . $conn->error);
        }
        $appointment_id = $conn->insert_id;

        // Descontar créditos (como enteros)
        $update_credits = "UPDATE credits 
                          SET amount = amount - {$service['credits_cost']}
                          WHERE user_id = '$user_id'";
        if (!$conn->query($update_credits)) {
            throw new Exception("Error updating credits: " . $conn->error);
        }

        // Registrar transacción (como enteros)
        $insert_transaction = "INSERT INTO credit_transactions 
                             (user_id, amount, type, reference_id) 
                             VALUES ('$user_id', -{$service['credits_cost']}, 'appointment', '$appointment_id')";
        if (!$conn->query($insert_transaction)) {
             throw new Exception("Error inserting transaction: " . $conn->error);
        }

        $conn->commit();
        echo json_encode(['success' => 'Turno reservado exitosamente.']); // Success *after* commit

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => 'Error al procesar la reserva: ' . $e->getMessage()]); // More detailed error
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    $appointment_id = intval($data['appointment_id']);

    // Verificar que el turno pertenece al usuario
    $appointment_sql = "SELECT a.*, s.credits_cost 
                       FROM appointments a
                       JOIN services s ON a.service_id = s.id
                       WHERE a.id = $appointment_id AND a.user_id = '$user_id'";
    $appointment_result = $conn->query($appointment_sql);

    if ($appointment_result->num_rows == 0) {
        echo json_encode(['error' => 'Turno no encontrado.']);
        exit();
    }

    $appointment = $appointment_result->fetch_assoc();
    $cancellation_date = new DateTime();
    $appointment_date = new DateTime($appointment['appointment_date']);
    $days_difference = $cancellation_date->diff($appointment_date)->days;

    // Check if cancellation is on the same day
    if ($cancellation_date->format('Y-m-d') == $appointment_date->format('Y-m-d')) {
        $days_difference = 0; // Treat same-day cancellations as 0 days difference
    }

    // Calcular reembolso según la política
    $refund_amount = 0;
    if ($days_difference >= 2) {
        $refund_amount = $appointment['credits_cost'];
    } elseif ($days_difference == 1) {
        $refund_amount = floor($appointment['credits_cost'] / 2); // Integer division
    }

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Actualizar estado del turno
        $update_sql = "UPDATE appointments 
                      SET status = 'cancelled',
                          credits_refunded = $refund_amount
                      WHERE id = $appointment_id";

        if(! $conn->query($update_sql)){
            throw new Exception("Error updating appointment status: " . $conn->error);
        }

        if ($refund_amount > 0) {
            // Reembolsar créditos (como enteros)
            $update_credits = "UPDATE credits 
                             SET amount = amount + $refund_amount
                             WHERE user_id = '$user_id'";
            if(! $conn->query($update_credits)){
                throw new Exception("Error refunding credits: " . $conn->error);
            }

            // Registrar transacción de reembolso (como enteros)
            $insert_transaction = "INSERT INTO credit_transactions 
                                 (user_id, amount, type, reference_id) 
                                 VALUES ('$user_id', $refund_amount, 'refund', '$appointment_id')";
            if(! $conn->query($insert_transaction)){
                throw new Exception("Error adding refund transaction: " . $conn->error);
            }
        }

        $conn->commit();
        echo json_encode([
            'success' => "Turno cancelado exitosamente. Se te han reembolsado $refund_amount créditos.",
            'refund_amount' => $refund_amount
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => 'Error al cancelar el turno: ' . $e->getMessage()]); // More detailed error
    }
}
?>
