<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$title = 'Belleza Natural - Formación Profesional en masajes, estética y bienestar';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</head>
<body class="min-h-screen flex flex-col bg-neutral-50">
    <?php include 'includes/nav.php'; ?>
    
    <main class="flex-grow">
        <?php
        $valid_pages = ['home', 'about', 'courses', 'contact'];
        if (in_array($page, $valid_pages)) {
            include "pages/$page.php";
        } else {
            include "pages/home.php";
        }
        ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/main.js"></script>
</body>
</html>
