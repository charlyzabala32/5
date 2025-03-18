<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Salón de Belleza</title>
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

    <main>
        <section class="hero">
            <div class="container">
                <h1>Bienvenida/o a IBYME </h1>
                <p>Descubre una experiencia única de belleza y bienestar.</p>
                <div class="cta-buttons">
                    <a href="appointments.php" class="btn btn-primary">Reservar Turnos</a>
                    <a href="services.php" class="btn btn-secondary">Nuestros Servicios</a>
                </div>
            </div>
        </section>

         <section class="about-us">
            <div class="container">
                <h2>Sobre Nosotros</h2>
                <p>
                    En IBYME, nos dedicamos a realzar tu belleza natural y brindarte un servicio de la más alta calidad. Nuestro equipo de profesionales está comprometido con tu satisfacción y bienestar.
                </p>
            </div>
        </section>
    </main>

  <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
