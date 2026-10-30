
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
        
        function toggleSubMenu(element) {
            const navItem = element.closest('.nav-item');
            navItem.classList.toggle('expanded');
        }
        
        // Close mobile sidebar when the overlay or outside content is clicked
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleButton = event.target.closest('.toggle-btn');

            if (sidebar.classList.contains('active') && !toggleButton && !sidebar.contains(event.target)) {
                closeSidebar();
            }
        });
        
        // Sidebar Scroll Position Preservation
        (function() {
            function restoreSidebarScroll() {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) return;
                const saved = sessionStorage.getItem('sidebarScrollPosition');
                if (saved !== null) {
                    sidebar.scrollTop = parseInt(saved, 10);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) return;

                restoreSidebarScroll();
                setTimeout(restoreSidebarScroll, 50);
                setTimeout(restoreSidebarScroll, 200);

                sidebar.addEventListener('scroll', function() {
                    sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                });

                sidebar.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                    });
                });
            });

            window.addEventListener('beforeunload', function() {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                }
            });
        })();

        // Global Real-time Dashboard & Charts Management
        let dashboardCharts = {};

        function updateDashboardCharts(data) {
            if (!window.Chart || !data) return;

            const chartConfigs = {
                // Sparkline Mini Charts
                'chart_sales_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_sales || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.25)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_milk_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_milk || [],
                            backgroundColor: 'rgba(255, 255, 255, 0.8)',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_customer_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_customers || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_orders_spark': {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'Completed', 'Cancelled'],
                        datasets: [{
                            data: [
                                data.chart_orders ? data.chart_orders.pending : 0,
                                data.chart_orders ? data.chart_orders.completed : 0,
                                data.chart_orders ? data.chart_orders.canceled : 0
                            ],
                            backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }
                },
                'chart_inventory_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_inventory ? data.chart_inventory.map(i => i.name) : [],
                        datasets: [{
                            data: data.chart_inventory ? data.chart_inventory.map(i => i.stock_quantity) : [],
                            backgroundColor: 'rgba(255, 255, 255, 0.85)',
                            borderRadius: 3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_camels_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_milk || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_performance_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_performance ? data.chart_performance.map(p => p.name) : [],
                        datasets: [{
                            data: data.chart_performance ? data.chart_performance.map(p => p.total_score) : [],
                            backgroundColor: 'rgba(255, 255, 255, 0.85)',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_delivery_spark': {
                    type: 'doughnut',
                    data: {
                        labels: ['Assigned', 'In Transit', 'Delivered', 'Cancelled'],
                        datasets: [{
                            data: [
                                data.chart_delivery ? data.chart_delivery.assigned : 0,
                                data.chart_delivery ? data.chart_delivery.in_transit : 0,
                                data.chart_delivery ? data.chart_delivery.delivered : 0,
                                data.chart_delivery ? data.chart_delivery.cancelled : 0
                            ],
                            backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }
                },

                // Full Interactive Main Charts
                'chart_sales_trend': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Sales Volume (Orders)',
                            data: data.chart_sales || [],
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.12)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4e73df',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_revenue_trend': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Revenue ($)',
                            data: data.chart_revenue || [],
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0.15)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#1cc88a',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_orders_overview': {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Completed', 'Cancelled'],
                        datasets: [{
                            label: 'Orders Count',
                            data: [
                                data.chart_orders ? data.chart_orders.pending : 0,
                                data.chart_orders ? data.chart_orders.completed : 0,
                                data.chart_orders ? data.chart_orders.canceled : 0
                            ],
                            backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                },
                'chart_customer_growth': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Total Customers',
                            data: data.chart_customers || [],
                            borderColor: '#36b9cc',
                            backgroundColor: 'rgba(54, 185, 204, 0.15)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#36b9cc',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_milk_stats': {
                    type: 'bar',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Milk Collected (Liters)',
                            data: data.chart_milk || [],
                            backgroundColor: 'rgba(246, 194, 62, 0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_inventory_overview': {
                    type: 'bar',
                    data: {
                        labels: data.chart_inventory ? data.chart_inventory.map(i => i.name) : [],
                        datasets: [{
                            label: 'Current Stock',
                            data: data.chart_inventory ? data.chart_inventory.map(i => i.stock_quantity) : [],
                            backgroundColor: 'rgba(111, 66, 193, 0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                },
                'chart_performance_overview': {
                    type: 'bar',
                    data: {
                        labels: data.chart_performance ? data.chart_performance.map(p => p.name) : [],
                        datasets: [{
                            label: 'Score',
                            data: data.chart_performance ? data.chart_performance.map(p => p.total_score) : [],
                            backgroundColor: 'rgba(231, 74, 59, 0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { max: 100 } } }
                },
                'chart_delivery_status': {
                    type: 'doughnut',
                    data: {
                        labels: ['Assigned', 'In Transit', 'Delivered', 'Cancelled'],
                        datasets: [{
                            data: [
                                data.chart_delivery ? data.chart_delivery.assigned : 0,
                                data.chart_delivery ? data.chart_delivery.in_transit : 0,
                                data.chart_delivery ? data.chart_delivery.delivered : 0,
                                data.chart_delivery ? data.chart_delivery.cancelled : 0
                            ],
                            backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                }
            };

            Object.keys(chartConfigs).forEach(canvasId => {
                const el = document.getElementById(canvasId);
                if (!el) return;
                const config = chartConfigs[canvasId];
                if (dashboardCharts[canvasId]) {
                    dashboardCharts[canvasId].data = config.data;
                    dashboardCharts[canvasId].update('none');
                } else {
                    dashboardCharts[canvasId] = new Chart(el, config);
                }
            });
        }

        // Global Real-time Polling for Notifications and Dashboards
        function pollGlobalData() {
            fetch('api_dashboard.php')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const d = res.data;

                        // Notifications Badge
                        const badge = document.getElementById('global_notification_badge');
                        if (badge) {
                            if (d.unread_notifications > 0) {
                                badge.textContent = d.unread_notifications;
                                badge.style.display = 'inline-block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }

                        // Update Real-Time Admin Dashboard Card Stats
                        if (d.global_sales !== undefined) {
                            const el = document.getElementById('dash_global_sales');
                            if (el) el.textContent = '$' + Number(d.global_sales).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                        if (d.global_milk !== undefined) {
                            const el = document.getElementById('dash_global_milk');
                            if (el) el.textContent = Number(d.global_milk).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1}) + ' L';
                        }
                        if (d.global_users !== undefined) {
                            const el = document.getElementById('dash_global_users');
                            if (el) el.textContent = Number(d.global_users).toLocaleString();
                        }
                        if (d.active_orders !== undefined) {
                            const el = document.getElementById('dash_active_orders');
                            if (el) el.textContent = Number(d.active_orders).toLocaleString() + ' Orders';
                        }
                        if (d.count_products !== undefined) {
                            const el = document.getElementById('dash_count_products');
                            if (el) el.textContent = Number(d.count_products).toLocaleString() + ' Items';
                        }
                        if (d.count_camels !== undefined) {
                            const el = document.getElementById('dash_count_camels');
                            if (el) el.textContent = Number(d.count_camels).toLocaleString() + ' Camels';
                        }
                        if (d.completed_today !== undefined) {
                            const el = document.getElementById('dash_completed_today');
                            if (el) el.textContent = Number(d.completed_today).toLocaleString() + ' Delivered';
                        }
                        
                        // Driver Dashboard metrics
                        if (d.assigned_orders !== undefined) {
                            const el = document.getElementById('dash_assigned');
                            if (el) el.textContent = d.assigned_orders;
                        }
                        if (d.accepted_orders !== undefined) {
                            const el = document.getElementById('dash_accepted');
                            if (el) el.textContent = d.accepted_orders;
                        }
                        if (d.today_deliveries !== undefined) {
                            const el = document.getElementById('dash_today_completed');
                            if (el) el.textContent = d.today_deliveries;
                        }
                        if (d.today_cancellations !== undefined) {
                            const el = document.getElementById('dash_today_cancelled');
                            if (el) el.textContent = d.today_cancellations;
                        }
                        if (d.completed_orders !== undefined) {
                            const el = document.getElementById('dash_total_completed');
                            if (el) el.textContent = d.completed_orders;
                        }
                        if (d.canceled_orders !== undefined) {
                            const el = document.getElementById('dash_total_cancelled');
                            if (el) el.textContent = d.canceled_orders;
                        }
                        if (d.active_queue !== undefined) {
                            const queueEl = document.getElementById('dash_active_queue');
                            if (queueEl) {
                                queueEl.innerHTML = '';
                                if (d.active_queue.length === 0) {
                                    queueEl.innerHTML = '<tr><td colspan="4" class="text-center">No active deliveries assigned.</td></tr>';
                                } else {
                                    d.active_queue.forEach(q => {
                                        const tr = document.createElement('tr');
                                        let statusBadge = q.status === 'assigned' ? '<span class="badge bg-warning">Assigned</span>' : '<span class="badge bg-info">In Transit</span>';
                                        tr.innerHTML = `
                                            <td>#${q.order_number}</td>
                                            <td>${q.customer_name}<br><small class="text-muted">${q.address}</small></td>
                                            <td>${statusBadge}</td>
                                            <td><a href="?page=delivery&subpage=tracking" class="btn btn-sm btn-primary">View</a></td>
                                        `;
                                        queueEl.appendChild(tr);
                                    });
                                }
                            }
                        }
                        
                        // Driver Recent Activity
                        if (d.recent_activity !== undefined) {
                            const recentBody = document.getElementById('dash_recent_activity');
                            if (recentBody) {
                                recentBody.innerHTML = '';
                                if (d.recent_activity.length === 0) {
                                    recentBody.innerHTML = '<tr><td colspan="4" class="text-center">No recent activity</td></tr>';
                                } else {
                                    d.recent_activity.forEach(item => {
                                        const tr = document.createElement('tr');
                                        let statusBadge = 'secondary';
                                        if (item.status === 'delivered') statusBadge = 'success';
                                        if (item.status === 'cancelled') statusBadge = 'danger';
                                        if (item.status === 'in_transit') statusBadge = 'info';
                                        
                                        const actionTime = item.delivered_at || item.cancelled_at || item.accepted_at || '';
                                        const formattedTime = actionTime ? new Date(actionTime).toLocaleString() : 'N/A';
                                        
                                        tr.innerHTML = `
                                            <td>${item.order_number}</td>
                                            <td>${item.customer_name}</td>
                                            <td><span class="badge bg-${statusBadge}">${item.status.replace('_', ' ').toUpperCase()}</span></td>
                                            <td>${formattedTime}</td>
                                        `;
                                        recentBody.appendChild(tr);
                                    });
                                }
                            }
                        }

                        // Update Live Activities Stream
                        const actEl = document.getElementById('dash_live_activities');
                        if (actEl && d.recent_activities) {
                            actEl.innerHTML = '';
                            if (d.recent_activities.length === 0) {
                                actEl.innerHTML = '<div class="text-muted text-center py-3">No recent activities.</div>';
                            } else {
                                d.recent_activities.forEach(act => {
                                    const item = document.createElement('div');
                                    item.className = 'd-flex align-items-center justify-content-between p-2.5 mb-2 rounded bg-light border-start border-4 border-' + (act.badge || 'primary');
                                    item.style.backgroundColor = 'var(--bs-gray-100)';
                                    item.innerHTML = `
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-${act.badge || 'primary'} me-3 p-2 fs-6 rounded-circle"><i class="bi ${act.icon || 'bi-activity'}"></i></div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">${act.title}</h6>
                                                <small class="text-muted">${act.desc}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-${act.badge || 'secondary'} bg-opacity-15 text-${act.badge || 'dark'} mb-1">${act.status}</span>
                                            <div><small class="text-muted" style="font-size: 0.75rem;">${act.time}</small></div>
                                        </div>
                                    `;
                                    actEl.appendChild(item);
                                });
                            }
                        }

                        // Update Live Notifications Stream
                        const notifEl = document.getElementById('dash_live_notifications');
                        if (notifEl && d.notifications) {
                            notifEl.innerHTML = '';
                            if (d.notifications.length === 0) {
                                notifEl.innerHTML = '<div class="text-muted text-center py-3">No active notifications.</div>';
                            } else {
                                d.notifications.forEach(n => {
                                    const item = document.createElement('div');
                                    item.className = 'd-flex align-items-start p-2.5 mb-2 rounded border-bottom';
                                    item.innerHTML = `
                                        <div class="me-2 text-warning fs-5"><i class="bi bi-bell-fill"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">${n.title || 'System Notification'}</div>
                                            <div class="text-muted" style="font-size: 0.8rem;">${n.message || ''}</div>
                                            <div class="text-muted text-end" style="font-size: 0.7rem;">${n.created_at ? new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : ''}</div>
                                        </div>
                                    `;
                                    notifEl.appendChild(item);
                                });
                            }
                        }

                        // Update Charts
                        updateDashboardCharts(d);
                    }
                })
                .catch(err => console.error("Polling error:", err));
        }

        // Run global polling immediately and every 5 seconds
        document.addEventListener('DOMContentLoaded', pollGlobalData);
        setInterval(pollGlobalData, 5000);
    