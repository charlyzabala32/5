$(document).ready(function() {
    // Menú móvil
    $('.menu-toggle').click(function() {
        $('.mobile-menu').toggleClass('active');
    });

    // Animaciones al hacer scroll
    function handleScrollAnimations() {
        $('.fade-in').each(function() {
            const elementTop = $(this).offset().top;
            const windowBottom = $(window).scrollTop() + $(window).height();
            
            if (windowBottom > elementTop) {
                $(this).addClass('visible');
            }
        });
    }

    // Iniciar animaciones al cargar y al hacer scroll
    handleScrollAnimations();
    $(window).scroll(handleScrollAnimations);
});
