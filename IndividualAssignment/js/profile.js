document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and content
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Show corresponding content
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('new_password');
    const passwordStrength = document.getElementById('password-strength');
    const passwordText = document.getElementById('password-strength-text');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            let strength = 0;
            let strengthText = 'Weak';
            
            // Length check
            if (password.length >= 8) {
                strength += 25;
            }
            
            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 25;
            }
            
            // Lowercase check
            if (/[a-z]/.test(password)) {
                strength += 25;
            }
            
            // Number and special character check
            if (/[0-9!@#$%^&*]/.test(password)) {
                strength += 25;
            }
            
            // Set strength meter width and text
            passwordStrength.style.width = strength + '%';
            
            if (strength < 25) {
                passwordStrength.style.backgroundColor = '#ff4d4d';
                strengthText = 'Very Weak';
            } else if (strength < 50) {
                passwordStrength.style.backgroundColor = '#ffa64d';
                strengthText = 'Weak';
            } else if (strength < 75) {
                passwordStrength.style.backgroundColor = '#ffff4d';
                strengthText = 'Medium';
            } else if (strength < 100) {
                passwordStrength.style.backgroundColor = '#4dff4d';
                strengthText = 'Strong';
            } else {
                passwordStrength.style.backgroundColor = '#4dff4d';
                strengthText = 'Very Strong';
            }
            
            passwordText.textContent = 'Password strength: ' + strengthText;
        });
    }
    
    // Password confirmation match
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    }
    
    // Form validation alerts
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show validation errors
                const invalidInputs = form.querySelectorAll(':invalid');
                invalidInputs.forEach(input => {
                    const formGroup = input.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                        
                        // Create error message
                        let errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = input.validationMessage || 'This field is invalid';
                        
                        // Remove any existing error message
                        const existingError = formGroup.querySelector('.error-message');
                        if (existingError) {
                            formGroup.removeChild(existingError);
                        }
                        
                        formGroup.appendChild(errorMsg);
                    }
                });
            }
        });
    });
    
    // Reset validation errors when input changes
    const inputs = document.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const formGroup = input.closest('.form-group');
            if (formGroup && formGroup.classList.contains('has-error')) {
                formGroup.classList.remove('has-error');
                
                const errorMsg = formGroup.querySelector('.error-message');
                if (errorMsg) {
                    formGroup.removeChild(errorMsg);
                }
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 1000);
        }, 5000);
    });
});