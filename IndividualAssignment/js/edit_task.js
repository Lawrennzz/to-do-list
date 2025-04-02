document.addEventListener('DOMContentLoaded', function() {
    const editTaskForm = document.getElementById('edit-task-form');
    const deleteTaskButton = document.getElementById('delete-task');
    const deleteModal = document.getElementById('delete-modal');
    const closeModalButtons = document.querySelectorAll('.close-modal');
    const confirmDeleteButton = document.getElementById('confirm-delete');

    // Form Validation Function
    function validateForm() {
        let isValid = true;
        const errorClass = 'input-error';

        // Title Validation
        const titleInput = document.getElementById('title');
        const titleError = document.getElementById('title-error');
        if (titleInput.value.trim() === '' || titleInput.value.length < 3) {
            titleError.textContent = titleInput.value.trim() === '' 
                ? 'Task title is required' 
                : 'Title must be at least 3 characters';
            titleInput.classList.add(errorClass);
            isValid = false;
        } else {
            titleError.textContent = '';
            titleInput.classList.remove(errorClass);
        }

        // Description Validation
        const descInput = document.getElementById('description');
        const descError = document.getElementById('description-error');
        if (descInput.value.trim() === '' || descInput.value.length < 10) {
            descError.textContent = descInput.value.trim() === ''
                ? 'Description is required'
                : 'Description must be at least 10 characters';
            descInput.classList.add(errorClass);
            isValid = false;
        } else {
            descError.textContent = '';
            descInput.classList.remove(errorClass);
        }

        // Due Date Validation
        const dueDateInput = document.getElementById('due_date');
        const dueDateError = document.getElementById('due-date-error');
        if (dueDateInput.value === '') {
            dueDateError.textContent = 'Please select a due date';
            dueDateInput.classList.add(errorClass);
            isValid = false;
        } else {
            dueDateError.textContent = '';
            dueDateInput.classList.remove(errorClass);
        }

        return isValid;
    }

    // Form Submission Event
    if (editTaskForm) {
        editTaskForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }

    // Delete Task Modal Functionality
    if (deleteTaskButton) {
        deleteTaskButton.addEventListener('click', function(e) {
            e.preventDefault();
            deleteModal.style.display = 'block';
            const taskId = this.getAttribute('data-id');
            confirmDeleteButton.setAttribute('href', `delete_task.php?id=${taskId}`);
        });
    }

    // Close Modal Buttons
    if (closeModalButtons) {
        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
        });
    }

    // Close Modal by Clicking Outside
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });

    // Auto-hide messages
    const messages = document.querySelectorAll('.message, .alert');
    if (messages.length > 0) {
        setTimeout(() => {
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);
    }
});