document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const captchaInput = document.getElementById('captcha');
    const messageContainer = document.querySelector('.message');
    const refreshCaptchaBtn = document.getElementById('refresh-captcha');
    const captchaObject = document.querySelector('object[type="image/svg+xml"]');
    const captchaImage = document.querySelector('.captcha-image');
    const phoneRecoveryToggle = document.getElementById('phone-recovery-toggle');
    const emailRecoveryForm = document.getElementById('email-recovery-form');
    const phoneRecoveryForm = document.getElementById('phone-recovery-form');

    // Add refresh CAPTCHA functionality
    if (refreshCaptchaBtn && captchaObject) {
        refreshCaptchaBtn.addEventListener('click', function() {
            // Clear input
            captchaInput.value = '';
            
            // Get new CAPTCHA
            const timestamp = new Date().getTime();
            
            // Update both object and img (for fallback)
            captchaObject.setAttribute('data', 'includes/captcha_svg.php?t=' + timestamp);
            captchaObject.querySelector('img').src = 'includes/captcha_svg.php?t=' + timestamp;
        });
    }

    // Phone recovery toggle functionality
    if (phoneRecoveryToggle) {
        phoneRecoveryToggle.addEventListener('click', function() {
            // Toggle forms
            emailRecoveryForm.classList.toggle('hidden');
            phoneRecoveryForm.classList.toggle('hidden');
            
            // Clear any error messages
            clearErrorMessages();
        });
    }

    // Client-side form validation
    emailForm.addEventListener('submit', function(event) {
        // Reset previous error messages
        clearErrorMessages();

        let isValid = true;

        // Email validation
        if (!validateEmail(emailInput.value)) {
            displayError(emailInput, 'Please enter a valid email address');
            isValid = false;
        }

        // CAPTCHA validation
        if (captchaInput.value.trim() === '') {
            displayError(captchaInput, 'Please enter the CAPTCHA code');
            isValid = false;
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    });

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Display error message next to input
    function displayError(inputElement, message) {
        // Remove any existing error messages
        const existingError = inputElement.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        errorElement.style.color = '#a94442';
        errorElement.style.fontSize = '0.9rem';
        inputElement.parentNode.appendChild(errorElement);
    }

    // Clear all error messages
    function clearErrorMessages() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(error => error.remove());
    }
});