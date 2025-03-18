document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    menuToggle.addEventListener('click', function() {
        navLinks.classList.toggle('active');

        // Toggle the 'X' animation
        this.classList.toggle('active');
        this.querySelectorAll('.bar').forEach((bar, index) => {
            if (index === 0) {
                bar.classList.toggle('bar1');
            } else if (index === 1) {
                bar.classList.toggle('bar2');
            } else if (index === 2) {
                bar.classList.toggle('bar3');
            }
        });
    });
});
