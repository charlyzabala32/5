<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="index.php" class="flex items-center">
                    <img src="https://ibyme.com.ar/img/logo.png" alt="IBYME Logo" class="h-12 w-auto mr-2">
                    <span class="ml-2 text-xl font-semibold text-gray-900">IBYME Escuela - Spa</span>
                </a>
            </div>

            <!-- Menú desktop -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="text-gray-700 hover:text-rose-500 transition-colors">Inicio</a>
                <a href="index.php?page=courses" class="text-gray-700 hover:text-rose-500 transition-colors">Cursos</a>
                <a href="index.php?page=about" class="text-gray-700 hover:text-rose-500 transition-colors">Nosotros</a>
                <a href="index.php?page=contact" class="text-gray-700 hover:text-rose-500 transition-colors">Contacto</a>
                <a href="/turnos/" class="text-gray-700 hover:text-rose-500 transition-colors">Turnos</a>
                </a>
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
            <a href="index.php" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Inicio</a>
            <a href="index.php?page=courses" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Cursos</a>
            <a href="index.php?page=about" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Nosotros</a>
            <a href="index.php?page=contact" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Contacto</a>
          <a href="/turnos/" class="block px-3 py-2 text-gray-700 hover:text-rose-500">Turnos</a>
        </div>
    </div>
</nav>
