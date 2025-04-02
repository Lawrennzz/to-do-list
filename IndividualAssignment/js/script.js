document.addEventListener('DOMContentLoaded', function() {
    // Improved email validation function
    function validateEmail(email) {
        // More permissive email validation that allows international characters
        const re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        return re.test(String(email).trim());
    }
    
    // Improved phone validation function for international numbers
    function validatePhone(phone) {
        // Allow various international phone number formats
        // Supports: 
        // +1 (123) 456-7890
        // +44 20 1234 5678
        // +86 123 4567 8901
        // Allows spaces, dashes, parentheses, and starts with + for country code
        const re = /^\+?[\d\s()-]{10,20}$/;
        return re.test(String(phone).trim());
    }
    
    // Email recovery form validation
    const emailForm = document.getElementById('emailRecoveryForm');
    
    if (emailForm) {
        emailForm.addEventListener('submit', function(event) {
            const email = document.getElementById('email').value.trim();
            const captchaInput = document.getElementById('captcha-input').value;
            const emailError = document.getElementById('email-error');
            const captchaError = document.getElementById('captcha-error');
            const messageEl = document.getElementById('message');
            
            // Reset error messages
            emailError.textContent = '';
            captchaError.textContent = '';
            messageEl.textContent = '';
            messageEl.className = '';
            
            let isValid = true;
            
            // Validate email
            if (!validateEmail(email)) {
                emailError.textContent = 'Please enter a valid email address';
                isValid = false;
            }
            
            // Validate captcha
            if (captchaInput.trim() !== captchaValue) {
                captchaError.textContent = 'Incorrect captcha. Please try again';
                generateCaptcha();
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            } else {
                // Show loading spinner
                document.getElementById('loading').style.display = 'block';
                
                // Add a timeout for better user experience
                localStorage.setItem('recoveryEmail', email);
            }
        });
    }
    
    // Phone recovery form validation
    const phoneForm = document.getElementById('phoneRecoveryForm');
    
    if (phoneForm) {
        phoneForm.addEventListener('submit', function(event) {
            const phone = document.getElementById('phone').value.trim();
            const phoneError = document.getElementById('phone-error');
            const messageEl = document.getElementById('message');
            
            // Reset error messages
            phoneError.textContent = '';
            messageEl.textContent = '';
            messageEl.className = '';
            
            // Validate phone
            if (!validatePhone(phone)) {
                phoneError.textContent = 'Please enter a valid international phone number';
                event.preventDefault();
            } else {
                // Show loading spinner
                document.getElementById('loading').style.display = 'block';
                
                // Store phone for verification
                localStorage.setItem('recoveryPhone', phone);
                
                // For the demo, we'll show the verification form after successful submission
                // In a real app, this would happen after the server confirms the SMS was sent
                document.getElementById('phone-for-verification').value = phone;
                
                const showVerificationForm = function() {
                    // Hide loading spinner
                    document.getElementById('loading').style.display = 'none';
                    
                    // Switch to verification form
                    const tabPanes = document.querySelectorAll('.tab-pane');
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    document.getElementById('verify-code').classList.add('active');
                    
                    // Update tab buttons visually
                    const tabButtons = document.querySelectorAll('.tab-btn');
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                };
                
                // Set timeout to simulate server processing
                setTimeout(showVerificationForm, 1500);
                
                // Prevent default form submission for demo purposes
                // In production, you would process normally
                event.preventDefault();
            }
        });
    }
    
    // Input field validation on blur for email
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            const emailError = document.getElementById('email-error');
            
            if (email && !validateEmail(email)) {
                emailError.textContent = 'Please enter a valid email address';
            } else {
                emailError.textContent = '';
            }
        });
    }
    
    // Input field validation on blur for phone
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            const phone = this.value.trim();
            const phoneError = document.getElementById('phone-error');
            
            if (phone && !validatePhone(phone)) {
                phoneError.textContent = 'Please enter a valid international phone number';
            } else {
                phoneError.textContent = '';
            }
        });
    }

    // Rest of the existing script remains the same...
});