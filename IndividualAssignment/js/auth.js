document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        const strengthMeter = document.querySelector('.strength-meter-fill');
        const strengthText = document.querySelector('.strength-text span');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            strengthMeter.setAttribute('data-strength', strength);
            
            // Update strength text
            if (strength === 0) {
                strengthText.textContent = 'Empty';
            } else if (strength === 1) {
                strengthText.textContent = 'Weak';
            } else if (strength === 2) {
                strengthText.textContent = 'Fair';
            } else if (strength === 3) {
                strengthText.textContent = 'Good';
            } else {
                strengthText.textContent = 'Strong';
            }
        });
    }
    
    // Password and confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput && passwordInput) {
        const form = confirmPasswordInput.closest('form');
        
        form.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                
                // Create error message if it doesn't exist
                let errorMessage = form.querySelector('.password-match-error');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message password-match-error';
                    errorMessage.textContent = 'Passwords do not match';
                    
                    // Insert after confirm password field
                    const formGroup = confirmPasswordInput.closest('.form-group');
                    formGroup.appendChild(errorMessage);
                }
            }
        });
        
        // Clear error message on input
        confirmPasswordInput.addEventListener('input', function() {
            const errorMessage = form.querySelector('.password-match-error');
            if (errorMessage) {
                errorMessage.remove();
            }
        });
    }
    
    // Calculate password strength (0-4)
    function calculatePasswordStrength(password) {
        if (!password) return 0;
        
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength += 1;
        
        // Contains lowercase and uppercase
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        
        // Contains numbers
        if (/\d/.test(password)) strength += 1;
        
        // Contains special characters
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        
        return strength;
    }
});