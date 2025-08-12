<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérification de la connexion admin
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Récupération des statistiques avancées
try {
    $pdo = getDBConnection();
    
    // Statistiques de base
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'Terminée'");
    $totalRevenue = $stmt->fetch()['total'] ?: 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM support_tickets WHERE status = 'Ouvert'");
    $openTickets = $stmt->fetch()['total'];
    
    // Statistiques par mois (12 derniers mois)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as orders,
            SUM(total_price) as revenue,
            AVG(total_price) as avg_order_value
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    $monthlyStats = $stmt->fetchAll();
    
    // Top des services
    $stmt = $pdo->query("
        SELECT 
            s.name,
            s.platform,
            COUNT(o.id) as order_count,
            SUM(o.total_price) as total_revenue,
            AVG(o.total_price) as avg_revenue
        FROM services s
        LEFT JOIN orders o ON s.id = o.service_id AND o.status = 'Terminée'
        GROUP BY s.id
        ORDER BY order_count DESC
        LIMIT 10
    ");
    $topServices = $stmt->fetchAll();
    
    // Statistiques par plateforme
    $stmt = $pdo->query("
        SELECT 
            s.platform,
            COUNT(o.id) as orders,
            SUM(o.total_price) as revenue,
            AVG(o.total_price) as avg_value
        FROM services s
        LEFT JOIN orders o ON s.id = o.service_id AND o.status = 'Terminée'
        GROUP BY s.platform
        ORDER BY revenue DESC
    ");
    $platformStats = $stmt->fetchAll();
    
    // Statistiques des clients
    $stmt = $pdo->query("
        SELECT 
            customer_email,
            customer_name,
            COUNT(*) as order_count,
            SUM(total_price) as total_spent,
            MAX(created_at) as last_order
        FROM orders 
        GROUP BY customer_email
        HAVING order_count > 1
        ORDER BY total_spent DESC
        LIMIT 10
    ");
    $topCustomers = $stmt->fetchAll();
    
    // Métriques de performance
    $performanceMetrics = getPerformanceMetrics();
    
    // Statistiques des tickets
    $stmt = $pdo->query("
        SELECT 
            status,
            priority,
            COUNT(*) as count
        FROM support_tickets
        GROUP BY status, priority
    ");
    $ticketStats = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erreur de base de données.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics et Statistiques - SMM Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .admin-container {
            padding-top: 20px;
            min-height: 100vh;
            background: var(--dark-bg);
        }
        
        .admin-nav {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .admin-nav .nav-link {
            color: var(--text-secondary);
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .admin-nav .nav-link:hover,
        .admin-nav .nav-link.active {
            background: var(--primary-color);
            color: var(--dark-bg);
        }
        
        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }
        
        .stats-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .chart-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .chart-title {
            color: var(--text-primary);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .table {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table th {
            background: var(--darker-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .table td {
            border-color: var(--border-color);
            color: var(--text-secondary);
        }
        
        .metric-card {
            background: var(--darker-bg);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .metric-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .metric-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-white">
                    <i class="fas fa-chart-line me-2"></i>Analytics et Statistiques
                </h1>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
                </a>
            </div>
            
            <!-- Navigation Admin -->
            <div class="admin-nav">
                <nav class="nav nav-pills">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Commandes
                    </a>
                    <a class="nav-link" href="services.php">
                        <i class="fas fa-cogs me-2"></i>Services
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Catégories
                    </a>
                    <a class="nav-link" href="tickets.php">
                        <i class="fas fa-ticket-alt me-2"></i>Tickets
                    </a>
                    <a class="nav-link active" href="analytics.php">
                        <i class="fas fa-chart-line me-2"></i>Analytics
                    </a>
                </nav>
            </div>
            
            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($totalOrders, 0, ',', ' '); ?></div>
                        <div class="stats-label">Total Commandes</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stats-number"><?php echo formatPrice($totalRevenue); ?></div>
                        <div class="stats-label">Chiffre d'Affaires</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stats-number"><?php echo number_format($openTickets, 0, ',', ' '); ?></div>
                        <div class="stats-label">Tickets Ouverts</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stats-number"><?php echo $performanceMetrics['conversion_rate']; ?>%</div>
                        <div class="stats-label">Taux de Conversion</div>
                    </div>
                </div>
            </div>
            
            <!-- Graphiques -->
            <div class="row">
                <!-- Évolution des commandes -->
                <div class="col-lg-8">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-area me-2"></i>Évolution des Commandes (12 derniers mois)
                        </h3>
                        <canvas id="ordersChart" height="100"></canvas>
                    </div>
                </div>
                
                <!-- Répartition par plateforme -->
                <div class="col-lg-4">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-pie me-2"></i>Répartition par Plateforme
                        </h3>
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Métriques de performance -->
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="text-white mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>Métriques de Performance
                    </h3>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-value">
                            <?php echo round($performanceMetrics['avg_processing_time'] ?? 0, 1); ?>h
                        </div>
                        <div class="metric-label">Temps moyen de traitement</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-value">
                            <?php echo $totalOrders > 0 ? round(($totalOrders / max(1, date('d'))) * 30, 1) : 0; ?>
                        </div>
                        <div class="metric-label">Commandes/mois (projection)</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-value">
                            <?php echo $totalOrders > 0 ? formatPrice($totalRevenue / $totalOrders) : '0 FCFA'; ?>
                        </div>
                        <div class="metric-label">Valeur moyenne/commande</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-value">
                            <?php echo $openTickets > 0 ? round(($openTickets / max(1, $totalOrders)) * 100, 1) : 0; ?>%
                        </div>
                        <div class="metric-label">Taux de tickets</div>
                    </div>
                </div>
            </div>
            
            <!-- Top des services et clients -->
            <div class="row">
                <!-- Top des services -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-trophy me-2"></i>Top 10 des Services
                        </h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Plateforme</th>
                                        <th>Commandes</th>
                                        <th>Revenus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topServices as $service): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($service['name']); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($service['platform']); ?></span>
                                            </td>
                                            <td><?php echo number_format($service['order_count'], 0, ',', ' '); ?></td>
                                            <td class="fw-bold"><?php echo formatPrice($service['total_revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Top des clients -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-users me-2"></i>Top 10 des Clients
                        </h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Commandes</th>
                                        <th>Total dépensé</th>
                                        <th>Dernière commande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCustomers as $customer): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($customer['customer_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($customer['customer_email']); ?></small>
                                            </td>
                                            <td><?php echo number_format($customer['order_count'], 0, ',', ' '); ?></td>
                                            <td class="fw-bold text-success"><?php echo formatPrice($customer['total_spent']); ?></td>
                                            <td><?php echo formatDateFrench($customer['last_order']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Configuration des couleurs pour Chart.js
        const chartColors = {
            primary: '#00ff88',
            secondary: '#00cc6a',
            accent: '#00aaff',
            warning: '#ffaa00',
            danger: '#ff4444',
            success: '#00ff88',
            info: '#00aaff'
        };
        
        // Graphique des commandes
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        const ordersChart = new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_reverse(array_column($monthlyStats, 'month'))); ?>,
                datasets: [{
                    label: 'Commandes',
                    data: <?php echo json_encode(array_reverse(array_column($monthlyStats, 'orders'))); ?>,
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Revenus (FCFA)',
                    data: <?php echo json_encode(array_reverse(array_column($monthlyStats, 'revenue'))); ?>,
                    borderColor: chartColors.accent,
                    backgroundColor: chartColors.accent + '20',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nombre de commandes'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenus (FCFA)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff'
                        }
                    }
                }
            }
        });
        
        // Graphique des plateformes
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformChart = new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($platformStats, 'platform')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($platformStats, 'revenue')); ?>,
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.secondary,
                        chartColors.accent,
                        chartColors.warning
                    ],
                    borderWidth: 2,
                    borderColor: '#1a1a1a'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#ffffff',
                            padding: 20
                        }
                    }
                }
            }
        });
        
        // Animation des cartes de statistiques
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stats-card, .chart-container').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>