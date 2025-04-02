document.addEventListener('DOMContentLoaded', function() {
    // Restore Task Functionality
    const restoreButtons = document.querySelectorAll('.btn-restore');
    
    restoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            
            // Confirmation dialog
            if (confirm('Are you sure you want to restore this task?')) {
                // AJAX request to restore the task
                fetch('includes/restore_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the task row from the table
                        this.closest('tr').remove();
                        
                        // Update task count or show message if no tasks left
                        const archiveTable = document.querySelector('.archive-list table tbody');
                        if (archiveTable && archiveTable.children.length === 0) {
                            document.querySelector('.archive-list').innerHTML = `
                                <div class="no-archived-tasks">
                                    <p>No archived tasks. Completed tasks will appear here.</p>
                                </div>
                            `;
                        }
                    } else {
                        alert('Failed to restore task: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring the task.');
                });
            }
        });
    });

    // Delete Task Functionality
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            
            // Confirmation dialog
            if (confirm('Are you sure you want to permanently delete this task? This action cannot be undone.')) {
                // AJAX request to delete the task
                fetch('includes/delete_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the task row from the table
                        this.closest('tr').remove();
                        
                        // Update task count or show message if no tasks left
                        const archiveTable = document.querySelector('.archive-list table tbody');
                        if (archiveTable && archiveTable.children.length === 0) {
                            document.querySelector('.archive-list').innerHTML = `
                                <div class="no-archived-tasks">
                                    <p>No archived tasks. Completed tasks will appear here.</p>
                                </div>
                            `;
                        }
                    } else {
                        alert('Failed to delete task: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the task.');
                });
            }
        });
    });
});