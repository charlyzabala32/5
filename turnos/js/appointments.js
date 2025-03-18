document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    
    // Validate appointment time
    timeSelect.addEventListener('change', function() {
        const selectedTime = this.value;
        const [hours] = selectedTime.split(':');
        
        // Validate business hours (9 AM to 7 PM)
        if (hours < 9 || hours > 19) {
            alert('Por favor selecciona un horario entre las 9:00 y las 19:00');
            this.value = '09:00';
        }
    });
});
