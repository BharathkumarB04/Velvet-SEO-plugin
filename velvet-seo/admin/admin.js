document.addEventListener('DOMContentLoaded', function () {
    
    // Initialize standard core WP Color pickers gracefully if hook is active
    if (window.jQuery && jQuery.fn.wpColorPicker) {
        jQuery('.velvet-color-picker').wpColorPicker();
    }

    const cards = document.querySelectorAll('[data-toggle-card]');

    cards.forEach(card => {
        const radios = card.querySelectorAll('.switch-toggle input[type="radio"]');
        const contentBox = card.querySelector('.toggle-content');
        
        function updateUIState() {
            let activeRadio = card.querySelector('.switch-toggle input[type="radio"]:checked');
            let status = activeRadio ? activeRadio.value : 'no';

            if (status === 'yes') {
                contentBox.style.display = 'block';
                card.classList.add('active-card');
            } else {
                contentBox.style.display = 'none';
                card.classList.remove('active-card');
            }
        }

        // Initialize display configuration on runtime startup
        updateUIState();

        // Listen for user configuration shifts dynamically
        radios.forEach(radio => {
            radio.addEventListener('change', updateUIState);
        });
    });

    // Bonus Framework Feature: Live text processing length counters
    const characterTargets = document.querySelectorAll('.count-target');
    
    characterTargets.forEach(input => {
        const counterElement = input.parentElement.querySelector('.char-count');
        
        function refreshCount() {
            if (counterElement) {
                counterElement.textContent = input.value.length;
                if (input.value.length > (input.maxLength * 0.85)) {
                    counterElement.style.color = '#e11d48'; // Warn close to boundary limits
                } else {
                    counterElement.style.color = '#10b981'; // Optimal validation indicator
                }
            }
        }
        
        refreshCount();
        input.addEventListener('input', refreshCount);
    });
});