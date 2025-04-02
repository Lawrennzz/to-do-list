// Delete reminder confirmation
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this reminder?')) {
            e.preventDefault();
        }
    });
});

// Form validation
document.querySelector('.reminder-form').addEventListener('submit', function(e) {
    const taskSelect = document.getElementById('task_id');
    const daysInput = document.getElementById('days_before');
    
    if (taskSelect.value === '') {
        alert('Please select a task');
        e.preventDefault();
        return;
    }
    
    if (daysInput.value < 1 || daysInput.value > 30) {
        alert('Please enter a value between 1 and 30');
        e.preventDefault();
        return;
    }
});

// Check for unread notifications and display notification badge
function checkUnreadNotifications() {
    fetch('includes/check_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationBadge = document.querySelector('.notification-badge');
            if (notificationBadge) {
                notificationBadge.textContent = data.count;
                notificationBadge.style.display = data.count > 0 ? 'block' : 'none';
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Check notifications every minute
setInterval(checkUnreadNotifications, 60000);

// Initial check
checkUnreadNotifications();
