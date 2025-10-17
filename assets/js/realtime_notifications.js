// Real-time Notification System
let notificationCount = 0;
let notificationCheckInterval;

// Update notification badge and count
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    const countText = document.querySelector('.notification-count-text');
    
    if (count > 0) {
        if (!badge) {
            // Create badge if doesn't exist
            const bellButton = document.querySelector('.notification-bell-button');
            if (bellButton) {
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center';
                newBadge.textContent = count > 9 ? '9+' : count;
                bellButton.appendChild(newBadge);
            }
        } else {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = 'flex';
        }
        
        if (countText) {
            countText.textContent = count;
            countText.parentElement.style.display = 'inline-block';
        }
    } else {
        if (badge) {
            badge.style.display = 'none';
        }
        if (countText && countText.parentElement) {
            countText.parentElement.style.display = 'none';
        }
    }
    
    notificationCount = count;
}

// Fetch notifications from API
async function fetchNotifications() {
    try {
        const response = await fetch('api_notifications.php?action=get');
        const data = await response.json();
        
        if (data.success) {
            // Update badge count
            updateNotificationBadge(data.total_count);
            
            // Update notification panel if open
            updateNotificationPanel(data.notifications, data.total_count);
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

// Update notification panel content
function updateNotificationPanel(notifications, totalCount) {
    const panel = document.getElementById('notificationPanel');
    if (!panel || panel.classList.contains('hidden')) {
        return; // Don't update if panel is closed
    }
    
    const container = panel.querySelector('.max-h-96');
    if (!container) return;
    
    // Clear current content
    container.innerHTML = '';
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="p-4 text-center text-gray-500 text-sm">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p>Tidak ada notifikasi</p>
            </div>
        `;
    } else {
        notifications.forEach(notif => {
            const notifElement = createNotificationElement(notif);
            container.appendChild(notifElement);
        });
    }
}

// Create notification element
function createNotificationElement(notif) {
    const a = document.createElement('a');
    a.href = notif.link;
    a.className = 'block p-4 border-b border-gray-100 transition bg-blue-50 hover:bg-blue-100';
    
    let iconClass = 'fas fa-file-alt text-blue-500';
    if (notif.type === 'report') {
        iconClass = 'fas fa-chart-line text-green-500';
    } else if (notif.type === 'success') {
        iconClass = 'fas fa-check-circle text-green-500';
    } else if (notif.type === 'warning') {
        iconClass = 'fas fa-exclamation-circle text-yellow-500';
    }
    
    a.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-3">
                <i class="${iconClass} text-lg"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-900 font-bold">
                    ${notif.title}
                </p>
                <p class="text-xs text-gray-600 mt-1">
                    ${notif.message}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="far fa-clock mr-1"></i>${formatTime(notif.time)}
                </p>
            </div>
            <div class="flex-shrink-0 ml-2">
                <span class="w-2 h-2 bg-blue-600 rounded-full inline-block"></span>
            </div>
        </div>
    `;
    
    // Add click event to clear badge
    a.addEventListener('click', function(e) {
        onNotificationClick(e);
    });
    
    return a;
}

// Format time for display
function formatTime(timeString) {
    const date = new Date(timeString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return diffMins + ' menit lalu';
    if (diffHours < 24) return diffHours + ' jam lalu';
    if (diffDays < 7) return diffDays + ' hari lalu';
    
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Mark notifications as read
async function markNotificationsAsRead() {
    try {
        const response = await fetch('api_notifications.php?action=mark_read');
        const data = await response.json();
        
        if (data.success) {
            // Hide all blue indicators
            document.querySelectorAll('.notification-badge').forEach(badge => {
                badge.style.display = 'none';
            });
            
            // Remove blue background from notifications
            document.querySelectorAll('.bg-blue-50').forEach(elem => {
                elem.classList.remove('bg-blue-50', 'hover:bg-blue-100');
                elem.classList.add('bg-white', 'hover:bg-gray-50');
            });
            
            // Remove unread indicators
            document.querySelectorAll('.bg-blue-600').forEach(indicator => {
                indicator.remove();
            });
            
            // Update badge to 0
            updateNotificationBadge(0);
        }
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
}

// Enhanced toggleNotifications with mark as read
function toggleNotificationsRealtime() {
    const panel = document.getElementById('notificationPanel');
    const profilePanel = document.getElementById('profilePanel');
    profilePanel.classList.add('hidden');
    
    const wasHidden = panel.classList.contains('hidden');
    panel.classList.toggle('hidden');
    
    // If opening panel, immediately clear badge
    if (wasHidden && !panel.classList.contains('hidden')) {
        // Clear badge immediately when opening panel
        updateNotificationBadge(0);
        
        // Mark notifications as read after viewing
        setTimeout(() => {
            markNotificationsAsRead();
        }, 1000);
    }
}

// Start polling for notifications
function startNotificationPolling() {
    // Initial fetch
    fetchNotifications();
    
    // Poll every 3 seconds for real-time updates
    notificationCheckInterval = setInterval(fetchNotifications, 3000);
}

// Trigger immediate refresh (useful after actions like save/approve)
function refreshNotifications() {
    fetchNotifications();
}

// Stop polling (useful for cleanup)
function stopNotificationPolling() {
    if (notificationCheckInterval) {
        clearInterval(notificationCheckInterval);
    }
}

// Handle notification click - clear badge immediately
function onNotificationClick(event) {
    // Clear badge immediately when clicking a notification
    updateNotificationBadge(0);
    
    // Mark as read
    markNotificationsAsRead();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    startNotificationPolling();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopNotificationPolling();
});

