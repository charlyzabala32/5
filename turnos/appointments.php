<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's credits
$credits_sql = "SELECT amount FROM credits WHERE user_id = $user_id";
$credits_result = $conn->query($credits_sql);
$current_credits = $credits_result->num_rows > 0 ? $credits_result->fetch_assoc()['amount'] : 0;

// Get services with credit costs
$services_sql = "SELECT id, name, credits_cost FROM services ORDER BY name";
$services = $conn->query($services_sql);

// Get user's appointments
$appointments_sql = "SELECT a.*, s.name as service_name, s.credits_cost
                    FROM appointments a
                    JOIN services s ON a.service_id = s.id
                    WHERE user_id = $user_id AND a.status != 'cancelled'
                    ORDER BY a.appointment_date, a.appointment_time";
$appointments = $conn->query($appointments_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Turnos - Tu Salón</title>
    <link rel="stylesheet" href="../css/styles.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        <div class="credits-info" style="background-color: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
            <h3>Mis Créditos</h3>
            <p>Créditos disponibles: <strong><?php echo $current_credits; ?></strong></p>
            <a href="redeem_coupon.php" class="btn btn-primary">Canjear Cupón</a>
        </div>

        <div class="booking-section">
            <h2>Reservar Nuevo Turno</h2>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="appointment-form" method="POST">
                <div class="form-group">
                    <label for="service">Servicio:</label>
                    <select id="service" name="service" required>
                        <?php while($service = $services->fetch_assoc()): ?>
                            <option value="<?php echo $service['id']; ?>" data-credits="<?php echo $service['credits_cost']; ?>">
                                <?php echo $service['name']; ?> - <?php echo $service['credits_cost']; ?> créditos
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Fecha:</label>
                    <input type="text" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="time-slots">Hora:</label>
                    <div id="time-slots">
                        <!-- Time slots will be rendered here -->
                    </div>
                    <input type="hidden" id="selected_time" name="selected_time" value="">
                </div>
                <button type="submit" class="btn btn-primary">Reservar Turno</button>
            </form>
        </div>

        <details class="cancellation-policy-section bg-white p-6 rounded-lg shadow-md mb-8">
            <summary class="flex items-center justify-between cursor-pointer text-rose-500 hover:text-rose-600 focus:outline-none">
                <h2 class="text-2xl font-semibold">Política de Cancelación de Turnos (Click para leer más)</h2>
            </summary>
            <div class="mt-4">
                <p class="text-gray-700 mb-4">
                    Entendemos que a veces los planes cambian.  Aquí te explicamos cómo funciona la cancelación de turnos y el reembolso de créditos:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-600">
                    <li><strong>Cancelación con 48 horas o más de anticipación:</strong> Recibirás un reembolso completo de los créditos utilizados para reservar el turno.</li>
                    <li><strong>Cancelación con 24 horas de anticipación:</strong> Recibirás un reembolso del 50% de los créditos utilizados para reservar el turno.</li>
                    <li><strong>Cancelación con menos de 24 horas de anticipación o no presentarse al turno:</strong> No se realizará ningún reembolso de créditos.</li>
                </ul>
                <p class="text-gray-700 mt-4">
                    Para cancelar un turno, haz clic en el botón "Cancelar" junto al turno correspondiente en la lista de "Mis Turnos". Se te pedirá que confirmes la cancelación y se te informará sobre el reembolso de créditos, si corresponde.
                </p>
            </div>
        </details>

        <div class="appointments-section">
            <h2>Mis Turnos</h2>
            <div class="appointments-list">
                <?php if ($appointments->num_rows > 0): ?>
                    <?php while($appointment = $appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <h3><?php echo $appointment['service_name']; ?></h3>
                            <p>Fecha: <?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></p>
                            <p>Hora: <?php echo $appointment['appointment_time']; ?></p>
                            <p>Créditos: <?php echo $appointment['credits_cost']; ?></p>
                            <p>Estado: <?php echo ucfirst($appointment['status']); ?></p>
                            <div class="appointment-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <button type="button" class="btn btn-danger" onclick="confirmCancellation(<?php echo $appointment['id']; ?>, <?php echo $appointment['credits_cost']; ?>, '<?php echo $appointment['appointment_date']; ?>')">Cancelar</button>
                                </form>
                                <button class="btn btn-secondary" onclick="showRescheduleForm(<?php echo $appointment['id']; ?>)">Reprogramar</button>
                            </div>

                            <!-- Reschedule Form -->
                            <form method="POST" id="reschedule-form-<?php echo $appointment['id']; ?>" style="display: none;" class="reschedule-form">
                                <input type="hidden" name="action" value="reschedule">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                <div class="form-group">
                                    <label for="new_date-<?php echo $appointment['id']; ?>">Nueva Fecha:</label>
                                    <input type="text" id="new_date-<?php echo $appointment['id']; ?>" name="new_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="new-time-slots-<?php echo $appointment['id']; ?>">Nueva Hora:</label>
                                    <div id="new-time-slots-<?php echo $appointment['id']; ?>">
                                        <!-- Time slots will be rendered here -->
                                    </div>
                                    <input type="hidden" id="selected_time-<?php echo $appointment['id']; ?>" name="selected_time" value="">
                                </div>
                                <button type="submit" class="btn btn-primary">Reprogramar</button>
                                <button type="button" class="btn btn-secondary" onclick="hideRescheduleForm(<?php echo $appointment['id']; ?>)">Cancelar</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No tienes turnos reservados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service');
            const currentCredits = <?php echo $current_credits; ?>;

            // Check if user has enough credits when selecting a service
            serviceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const requiredCredits = parseFloat(selectedOption.dataset.credits);
                
                if (requiredCredits > currentCredits) {
                    alert('No tienes suficientes créditos para este servicio. Por favor, canjea un cupón para obtener más créditos.');
                }
            });

            // Initialize Flatpickr
            const fp = flatpickr("#date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                locale: "es",
                onChange: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        fetchAndRenderTimeSlots(dateStr, 'time-slots', null);
                    } else {
                        document.getElementById('time-slots').innerHTML = '';
                    }
                }
            });

            // Rescheduling functions
            window.showRescheduleForm = function(appointmentId) {
                document.getElementById('reschedule-form-' + appointmentId).style.display = 'block';
                flatpickr("#new_date-" + appointmentId, {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: "es",
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) {
                            fetchAndRenderTimeSlots(dateStr, 'new-time-slots-' + appointmentId, appointmentId);
                        } else {
                            document.getElementById('new-time-slots-' + appointmentId).innerHTML = '';
                        }
                    }
                });
            }

            window.hideRescheduleForm = function(appointmentId) {
                document.getElementById('reschedule-form-' + appointmentId).style.display = 'none';
            }

            // Time slots functions
            async function fetchAndRenderTimeSlots(date, containerId, rescheduleAppointmentId) {
                renderTimeSlots(date, containerId, rescheduleAppointmentId);
            }

            async function renderTimeSlots(date, containerId, rescheduleAppointmentId) {
                const container = document.getElementById(containerId);
                container.innerHTML = '';

                const startHour = 9;
                const endHour = 19;
                const interval = 30;

                const slots = [];
                for (let hour = startHour; hour <= endHour; hour++) {
                    for (let minute = 0; minute < 60; minute += interval) {
                        const timeStr = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
                        const slotDiv = document.createElement('div');
                        slotDiv.classList.add('time-slot');
                        slotDiv.textContent = timeStr;
                        slotDiv.dataset.time = timeStr;
                        container.appendChild(slotDiv);
                        slots.push(slotDiv);
                    }
                }

                for (const slotDiv of slots) {
                    const timeStr = slotDiv.dataset.time;
                    try {
                        const response = await fetch(`check_slot.php?date=${date}&time=${timeStr}`);
                        const data = await response.json();

                        if (data.booked) {
                            slotDiv.classList.add('booked');
                            if (data.isUser) {
                                slotDiv.classList.add('user-appointment');
                                slotDiv.title = 'Tienes un turno agendado en este horario';
                            } else {
                                slotDiv.title = 'No disponible';
                            }
                        } else {
                            slotDiv.classList.add('available');
                            slotDiv.title = 'Disponible';
                            slotDiv.addEventListener('click', function() {
                                if (rescheduleAppointmentId === null) {
                                    document.getElementById('selected_time').value = timeStr;
                                } else {
                                    document.getElementById('selected_time-' + rescheduleAppointmentId).value = timeStr;
                                }
                                const selectedSlots = container.querySelectorAll('.selected');
                                selectedSlots.forEach(slot => slot.classList.remove('selected'));
                                this.classList.add('selected');
                            });
                        }
                    } catch (error) {
                        console.error("Error checking slot:", error);
                        slotDiv.innerHTML += `<p>Error</p>`;
                    }
                }
            }

            // Form submission validation
            const appointmentForm = document.querySelector('.appointment-form');
            appointmentForm.addEventListener('submit', function(event) {
                const selectedService = serviceSelect.options[serviceSelect.selectedIndex];
                const requiredCredits = parseFloat(selectedService.dataset.credits);

                if (requiredCredits > currentCredits) {
                    alert('No tienes suficientes créditos para este servicio.');
                    event.preventDefault();
                    return false;
                }

                if (!document.querySelector('.time-slot.selected')) {
                    alert('Por favor, selecciona un horario.');
                    event.preventDefault();
                    return false;
                }
            });

            // Accordion functionality
            const detailsElement = document.querySelector('.cancellation-policy-section');
            const readMoreBtn = detailsElement.querySelector('.read-more-text');

            detailsElement.addEventListener('toggle', function() {
                if (detailsElement.open) {
                    readMoreBtn.textContent = 'Leer menos';
                } else {
                    readMoreBtn.textContent = 'Leer más';
                }
            });
        });

        function confirmCancellation(appointmentId, creditsCost, appointmentDate) {
            const today = new Date();
            const appointmentDateTime = new Date(appointmentDate);
            const diffInDays = Math.floor((appointmentDateTime - today) / (1000 * 60 * 60 * 24));

            let refundMessage = '';
            let refundPercentage = 0;

            if (diffInDays >= 2) {
                refundMessage = `Si cancelas ahora, se te reembolsarán ${creditsCost} créditos (100% del costo).`;
                refundPercentage = 100;
            } else if (diffInDays === 1) {
                refundMessage = `Si cancelas ahora, se te reembolsarán ${Math.floor(creditsCost / 2)} créditos (50% del costo).`;
                refundPercentage = 50;
            } else {
                refundMessage = 'Si cancelas con menos de 24 horas de anticipación, no se realizará reembolso de créditos.';
                refundPercentage = 0;
            }

            const confirmation = confirm(`${refundMessage}\n\n¿Estás seguro de que quieres cancelar este turno?`);

            if (confirmation) {
                // Perform the cancellation via AJAX
                fetch('process_appointment.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `appointment_id=${appointmentId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Turno cancelado exitosamente. ${data.success}`);
                        location.reload(); // Reload the page to reflect the changes
                    } else {
                        alert(`Error al cancelar el turno: ${data.error}`);
                    }
                });
            }
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
