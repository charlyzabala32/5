<?php
$current_credits = 0;
if (isset($_SESSION['user_id'])) {
    $credits_sql = "SELECT amount FROM credits WHERE user_id = {$_SESSION['user_id']}";
    $credits_result = $conn->query($credits_sql);
    if ($credits_result->num_rows > 0) {
        $current_credits = $credits_result->fetch_assoc()['amount'];
    }
}
?>
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="../index.php" class="flex items-center">
                    <img src="https://ibyme.com.ar/img/logo.png" alt="IBYME Logo" class="h-12 w-auto mr-2">
                    <span class="ml-2 text-xl font-semibold text-gray-900">IBYME Escuela - Spa</span>
                </a>
            </div>

            <!-- Menú desktop -->
            <div class="hidden md:flex items-center space-x-8">

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div><a href="login.php">Iniciar Sesión</a></div>
                    <div><a href="register.php">Registrarse</a></div>
                <?php elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <div><a href="admin/index.php">Panel de Administración</a></div>
                    <div><a href="admin/coupons.php">Gestionar Cupones</a></div>
                    <div><a href="logout.php">Cerrar Sesión</a></div>
                <?php else: ?>
                    <div>
                        <span class="text-gray-600">Créditos: $<?php echo number_format($current_credits, 2); ?></span>
                    </div>
                    <div><a href="appointments.php">Mis Turnos</a></div>
                    <div><a href="redeem_coupon.php">Canjear Cupón</a></div>
                    <div><a href="edit_profile.php">Editar Perfil</a></div>
                    <div><a href="logout.php">Cerrar Sesión</a></div>
                <?php endif; ?>
            </div>

            <!-- Botón menú móvil -->
            <div class="md:hidden flex items-center">
                <button class="menu-toggle text-gray-700 hover:text-rose-500">
                    <i class="fas fa-bars h-6 w-6"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú móvil -->
    <div class="mobile-menu md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">

            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Iniciar Sesión</a>
                <a href="register.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Registrarse</a>
            <?php elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="admin/index.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Panel de Administración</a>
                <a href="admin/coupons.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Gestionar Cupones</a>
                <a href="logout.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Cerrar Sesión</a>
            <?php else: ?>
                <div class="px-3 py-2 text-gray-600">
                    Créditos: $<?php echo number_format($current_credits, 2); ?>
                </div>
                <a href="appointments.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Mis Turnos</a>
                <a href="redeem_coupon.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Canjear Cupón</a>
                <a href="edit_profile.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Editar Perfil</a>
                <a href="logout.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Cerrar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</nav>