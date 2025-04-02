document.addEventListener('DOMContentLoaded', function() {
    // Archive Task Functionality
    const archiveButtons = document.querySelectorAll('.btn-archive');
    
    archiveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            
            // Confirmation dialog
            if (confirm('Are you sure you want to archive this task?')) {
                // AJAX request to archive the task
                fetch('includes/archive_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Archive response:', data);
                    if (data.success) {
                        // Remove the task row from the table
                        this.closest('tr').remove();
                        
                        // Update task count or show message if no tasks left
                        const tasksTable = document.querySelector('.tasks-list table tbody');
                        if (tasksTable && tasksTable.children.length === 0) {
                            document.querySelector('.tasks-list').innerHTML = `
                                <div class="no-tasks">
                                    <p>No tasks found. <a href="add_task.php">Add your first task!</a></p>
                                </div>
                            `;
                        }
                    } else {
                        console.error('Archive error:', data.message);
                        alert('Failed to archive task: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    alert('An error occurred while archiving the task. Please check the browser console for details.');
                });
            }
        });
    });
});