document.addEventListener('DOMContentLoaded', function() {
    // Accept connection request
    document.querySelectorAll('.accept-connection').forEach(button => {
        button.addEventListener('click', function() {
            const connectionId = this.getAttribute('data-id');
            processConnectionRequest('accept', connectionId, this);
        });
    });
    
    // Reject connection request
    document.querySelectorAll('.reject-connection').forEach(button => {
        button.addEventListener('click', function() {
            const connectionId = this.getAttribute('data-id');
            processConnectionRequest('reject', connectionId, this);
        });
    });
    
    // Mark notification as read
    document.querySelectorAll('.mark-read').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            markNotificationAsRead(notificationId, this);
        });
    });
    
    // Function to process connection request
    function processConnectionRequest(action, connectionId, buttonElement) {
        // Show loading state
        const originalContent = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        buttonElement.disabled = true;
        
        // Disable sibling button
        const siblingButton = buttonElement.parentElement.querySelector(
            action === 'accept' ? '.reject-connection' : '.accept-connection'
        );
        if (siblingButton) siblingButton.disabled = true;
        
        // Send AJAX request
        const formData = new FormData();
        formData.append('action', action);
        formData.append('connection_id', connectionId);
        
        fetch('../elderly/process_connection_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show success message
                showToast(data.message, 'success');
                
                // Remove the connection request item
                const connectionItem = buttonElement.closest('.connection-request');
                if (connectionItem) {
                    connectionItem.style.height = connectionItem.offsetHeight + 'px';
                    connectionItem.style.opacity = '0';
                    connectionItem.style.padding = '0';
                    connectionItem.style.margin = '0';
                    
                    setTimeout(() => {
                        connectionItem.style.height = '0';
                        setTimeout(() => {
                            connectionItem.remove();
                            
                            // Update notification count
                            updateNotificationCount();
                            
                            // Check if there are no more connection requests
                            const connectionRequests = document.querySelectorAll('.connection-request');
                            if (connectionRequests.length === 0) {
                                const connectionGroup = document.querySelector('.notification-group');
                                if (connectionGroup) {
                                    connectionGroup.remove();
                                    
                                    // If no other notifications, show empty state
                                    const notificationGroups = document.querySelectorAll('.notification-group');
                                    if (notificationGroups.length === 0) {
                                        const dropdownMenu = document.querySelector('.notification-dropdown');
                                        if (dropdownMenu) {
                                            const emptyState = document.createElement('div');
                                            emptyState.className = 'text-center py-3';
                                            emptyState.innerHTML = `
                                                <i class="fas fa-check-circle text-muted mb-2" style="font-size: 2rem;"></i>
                                                <p class="mb-0">No new notifications</p>
                                            `;
                                            
                                            // Insert before the "View all notifications" link
                                            dropdownMenu.insertBefore(
                                                emptyState, 
                                                dropdownMenu.querySelector('.dropdown-divider')
                                            );
                                        }
                                    }
                                }
                            }
                        }, 300);
                    }, 300);
                }
            } else {
                // Show error message
                showToast(data.message, 'error');
                
                // Reset button state
                buttonElement.innerHTML = originalContent;
                buttonElement.disabled = false;
                if (siblingButton) siblingButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
            
            // Reset button state
            buttonElement.innerHTML = originalContent;
            buttonElement.disabled = false;
            if (siblingButton) siblingButton.disabled = false;
        });
    }
    
    // Function to mark notification as read
    function markNotificationAsRead(notificationId, buttonElement) {
        // Send AJAX request
        const formData = new FormData();
        formData.append('notification_id', notificationId);
        formData.append('action', 'mark_read');
        
        fetch('../elderly/process_notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove the notification item
                const notificationItem = buttonElement.closest('.notification-item');
                if (notificationItem) {
                    notificationItem.style.opacity = '0';
                    setTimeout(() => {
                        notificationItem.style.height = '0';
                        notificationItem.style.padding = '0';
                        notificationItem.style.margin = '0';
                        setTimeout(() => {
                            notificationItem.remove();
                            
                            // Update notification count
                            updateNotificationCount();
                            
                            // Check if there are no more notifications in this group
                            const groupEl = buttonElement.closest('.notification-group');
                            if (groupEl && groupEl.querySelectorAll('.notification-item').length === 0) {
                                groupEl.remove();
                            }
                            
                            // Check if there are no more notifications at all
                            const notificationGroups = document.querySelectorAll('.notification-group');
                            if (notificationGroups.length === 0) {
                                const dropdownMenu = document.querySelector('.notification-dropdown');
                                if (dropdownMenu) {
                                    const emptyState = document.createElement('div');
                                    emptyState.className = 'text-center py-3';
                                    emptyState.innerHTML = `
                                        <i class="fas fa-check-circle text-muted mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-0">No new notifications</p>
                                    `;
                                    
                                    // Insert before the "View all notifications" link
                                    dropdownMenu.insertBefore(
                                        emptyState, 
                                        dropdownMenu.querySelector('.dropdown-divider')
                                    );
                                }
                            }
                        }, 300);
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Function to update notification count
    function updateNotificationCount() {
        const badge = document.querySelector('#notificationsDropdown .badge');
        const notificationItems = document.querySelectorAll('.notification-item');
        const connectionRequests = document.querySelectorAll('.connection-request');
        const total = notificationItems.length + connectionRequests.length;
        
        if (badge) {
            if (total > 0) {
                badge.textContent = total;
            } else {
                badge.remove();
            }
        } else if (total > 0) {
            // Create new badge if doesn't exist but should
            const newBadge = document.createElement('span');
            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            newBadge.innerHTML = `
                ${total}
                <span class="visually-hidden">unread notifications</span>
            `;
            document.querySelector('#notificationsDropdown').appendChild(newBadge);
        }
    }
    
    // Function to show toast notifications
    function showToast(message, type) {
        // Remove any existing toast
        const existingToast = document.getElementById('notification-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create new toast
        const toast = document.createElement('div');
        toast.id = 'notification-toast';
        toast.className = `toast show ${type === 'error' ? 'toast-error' : 'toast-success'}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${type === 'error' ? 'Error' : 'Success'}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        // Add to body
        document.body.appendChild(toast);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
        
        // Close button functionality
        toast.querySelector('.btn-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }
});