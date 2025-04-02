document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        const today = new Date();
        const year = today.getFullYear();
        let month = today.getMonth() + 1;
        let day = today.getDate();
        
        // Add leading zero if needed
        month = month < 10 ? '0' + month : month;
        day = day < 10 ? '0' + day : day;
        
        const formattedDate = `${year}-${month}-${day}`;
        dueDateInput.min = formattedDate;
        dueDateInput.value = formattedDate;
    }

    // Form validation
    const addTaskForm = document.getElementById('add-task-form');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate title
            const titleInput = document.getElementById('title');
            const titleError = document.getElementById('title-error');
            if (titleInput.value.trim() === '') {
                titleError.textContent = 'Please enter a task title';
                titleInput.focus();
                isValid = false;
            } else if (titleInput.value.trim().length < 3) {
                titleError.textContent = 'Title must be at least 3 characters';
                titleInput.focus();
                isValid = false;
            } else {
                titleError.textContent = '';
            }
            
            // Validate description
            const descInput = document.getElementById('description');
            const descError = document.getElementById('description-error');
            if (descInput.value.trim() === '') {
                descError.textContent = 'Please enter a task description';
                descInput.focus();
                isValid = false;
            } else if (descInput.value.trim().length < 10) {
                descError.textContent = 'Description must be at least 10 characters';
                descInput.focus();
                isValid = false;
            } else {
                descError.textContent = '';
            }
            
            // Validate category
            const categorySelect = document.getElementById('category');
            const categoryError = document.getElementById('category-error');
            if (categorySelect.value === '' || categorySelect.value === null) {
                categoryError.textContent = 'Please select a category';
                categorySelect.focus();
                isValid = false;
            } else {
                categoryError.textContent = '';
            }
            
            // Validate priority
            const prioritySelect = document.getElementById('priority');
            const priorityError = document.getElementById('priority-error');
            if (prioritySelect.value === '' || prioritySelect.value === null) {
                priorityError.textContent = 'Please select a priority level';
                prioritySelect.focus();
                isValid = false;
            } else {
                priorityError.textContent = '';
            }
            
            // Validate due date
            const dueDateError = document.getElementById('due-date-error');
            if (dueDateInput.value === '') {
                dueDateError.textContent = 'Please select a due date';
                dueDateInput.focus();
                isValid = false;
            } else {
                const selectedDate = new Date(dueDateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    dueDateError.textContent = 'Due date cannot be in the past';
                    dueDateInput.focus();
                    isValid = false;
                } else {
                    dueDateError.textContent = '';
                }
            }
            
            // Validate status
            const statusSelect = document.getElementById('status');
            const statusError = document.getElementById('status-error');
            if (statusSelect.value === '' || statusSelect.value === null) {
                statusError.textContent = 'Please select a status';
                statusSelect.focus();
                isValid = false;
            } else {
                statusError.textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Reset button functionality
        const resetButton = addTaskForm.querySelector('button[type="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                // Clear all error messages
                const errorMessages = document.querySelectorAll('.error-message');
                errorMessages.forEach(message => {
                    message.textContent = '';
                });
                
                // Reset the form
                addTaskForm.reset();
                
                // Set default due date
                const today = new Date();
                const year = today.getFullYear();
                let month = today.getMonth() + 1;
                let day = today.getDate();
                
                month = month < 10 ? '0' + month : month;
                day = day < 10 ? '0' + day : day;
                
                const formattedDate = `${year}-${month}-${day}`;
                dueDateInput.value = formattedDate;
            });
        }
    }
    
    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message');
    if (messages.length > 0) {
        setTimeout(function() {
            messages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
});