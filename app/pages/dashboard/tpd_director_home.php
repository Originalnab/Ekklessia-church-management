<?php
require_once '../auth/auth_middleware.php';
checkAuth();

if ($_SESSION['function_id'] != 9) { // Updated to check for TPD Director (function_id = 9)
    header("Location: ../auth/login.php");
    exit;
}

// Fetch dashboard data
require_once '../../config/config.php';

// Total Zones
$stmt = $pdo->query("SELECT COUNT(*) as total FROM zones");
$total_zones = $stmt->fetch()['total'];

// Total Assemblies
$stmt = $pdo->query("SELECT COUNT(*) as total FROM assemblies");
$total_assemblies = $stmt->fetch()['total'];

// Total Households
$stmt = $pdo->query("SELECT COUNT(*) as total FROM households");
$total_households = $stmt->fetch()['total'];

// Total Members
$stmt = $pdo->query("SELECT COUNT(*) as total FROM members");
$total_members = $stmt->fetch()['total'];

// Total Shepherds
$stmt = $pdo->query("SELECT COUNT(*) as total FROM members WHERE local_function_id = 11");
$total_shepherds = $stmt->fetch()['total'];

// Total Saints
$stmt = $pdo->query("SELECT COUNT(*) as total FROM members WHERE local_function_id = 12");
$total_saints = $stmt->fetch()['total'];

// Active Members
$stmt = $pdo->query("SELECT COUNT(*) as total FROM members WHERE status = 'Active saint'");
$active_members = $stmt->fetch()['total'];

// New Members (Last 30 Days)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM members 
    WHERE joined_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");
$stmt->execute();
$new_members = $stmt->fetch()['total'];

// Adult Ministry Members
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM members 
    WHERE group_name = 'Adult Ministry'
");
$adult_ministry_members = $stmt->fetch()['total'];

// Children's Ministry Members
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM members 
    WHERE group_name = 'Children''s Ministry'
");
$children_ministry_members = $stmt->fetch()['total'];

// Male Members
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM members 
    WHERE gender = 'Male'
");
$male_members = $stmt->fetch()['total'];

// Female Members
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM members 
    WHERE gender = 'Female'
");
$female_members = $stmt->fetch()['total'];

// Data for Graphs
// Members per Assembly
$stmt = $pdo->query("
    SELECT a.name AS assembly_name, COUNT(m.member_id) as member_count
    FROM assemblies a
    LEFT JOIN members m ON a.assembly_id = m.assemblies_id
    GROUP BY a.assembly_id, a.name
");
$members_per_assembly = $stmt->fetchAll(PDO::FETCH_ASSOC);
$members_per_assembly_json = json_encode($members_per_assembly);

// Member Growth Over Time (last 12 months)
$stmt = $pdo->query("
    SELECT DATE_FORMAT(joined_date, '%Y-%m') AS month, COUNT(*) as count
    FROM members
    WHERE joined_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(joined_date, '%Y-%m')
    ORDER BY month ASC
");
$member_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);
$member_growth_json = json_encode($member_growth);

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPD Director Dashboard - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/Ekklessia-church-management/Public/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --card-bg-dark: hsla(267, 57.90%, 3.70%, 0.42);
        }
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #0a192f 0%, #1a365d 100%);
            min-height: 100vh;
        }
        [data-bs-theme="dark"] .card {
            background: var(--card-bg-dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .stat-card {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-bs-theme="dark"] .stat-card .card-title,
        [data-bs-theme="dark"] .stat-card .card-text {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        [data-bs-theme="dark"] .tpd-stats h3,
        [data-bs-theme="dark"] .tpd-stats h6 {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        [data-bs-theme="dark"] .stats-overview {
            color: rgba(255, 255, 255, 0.9);
        }

        [data-bs-theme="dark"] .chart-container canvas {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }
    </style>
    <?php include '../../includes/header.php'; ?>
</head>
<body>
    <?php include '../../includes/nav_card.php'; ?>
    <main class="container flex-grow-1 py-2">
        <!-- Navigation Card -->
        <div class="card nav-card">
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                            <i class="bi bi-people-fill text-primary"></i>
                            <span>Members</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="<?= $base_url ?>/tpd/assemblies/index.php" class="nav-link-btn">
                            <i class="bi bi-building-fill text-success"></i>
                            <span>Assemblies</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="<?= $base_url ?>/tpd/households/index.php" class="nav-link-btn">
                            <i class="bi bi-house-fill text-warning"></i>
                            <span>Households</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="<?= $base_url ?>/tpd/zones/index.php" class="nav-link-btn">
                            <i class="bi bi-globe-americas text-info"></i>
                            <span>Zones</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="<?= $base_url ?>/roles/index.php" class="nav-link-btn">
                            <i class="bi bi-person-gear text-secondary"></i>
                            <span>Roles</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini Dashboard -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-globe-americas fs-2"></i>
                        <h6 class="card-title text-white">Total Zones</h6>
                        <h3 class="card-text"><?php echo $total_zones; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-building fs-2"></i>
                        <h6 class="card-title text-white">Total Assemblies</h6>
                        <h3 class="card-text"><?php echo $total_assemblies; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #ffc107, #ffca28); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-house-fill fs-2"></i>
                        <h6 class="card-title text-white">Total Households</h6>
                        <h3 class="card-text"><?php echo $total_households; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-2"></i>
                        <h6 class="card-title text-white">Total Members</h6>
                        <h3 class="card-text"><?php echo $total_members; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #ff6f61, #ff9f84); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-person-check fs-2"></i>
                        <h6 class="card-title text-white">Total Shepherds</h6>
                        <h3 class="card-text"><?php echo $total_shepherds; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #9c27b0, #ce93d8); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-person-heart fs-2"></i>
                        <h6 class="card-title text-white">Total Saints</h6>
                        <h3 class="card-text"><?php echo $total_saints; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #4caf50, #81c784); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-person-check fs-2"></i>
                        <h6 class="card-title text-white">Active Members</h6>
                        <h3 class="card-text"><?php echo $active_members; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #dc3545, #ff6b6b); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-clock fs-2"></i>
                        <h6 class="card-title text-white">New Members (Last 30 Days)</h6>
                        <h3 class="card-text"><?php echo $new_members; ?></h3>
                    </div>
                </div>
            </div>
            <!-- Adult Ministry Members -->
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #3f51b5, #7986cb); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-person-arms-up fs-2"></i>
                        <h6 class="card-title text-white">Adult Ministry</h6>
                        <h3 class="card-text"><?php echo $adult_ministry_members; ?></h3>
                    </div>
                </div>
            </div>
            <!-- Children's Ministry Members -->
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #ff9800, #ffb74d); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-emoji-smile fs-2"></i>
                        <h6 class="card-title text-white">Children's Ministry</h6>
                        <h3 class="card-text"><?php echo $children_ministry_members; ?></h3>
                    </div>
                </div>
            </div>
            <!-- Male Members -->
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #2196f3, #64b5f6); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-gender-male fs-2"></i>
                        <h6 class="card-title text-white">Male Members</h6>
                        <h3 class="card-text"><?php echo $male_members; ?></h3>
                    </div>
                </div>
            </div>
            <!-- Female Members -->
            <div class="col-md-3">
                <div class="card shadow-sm" style="background: linear-gradient(45deg, #e91e63, #f06292); color: white;">
                    <div class="card-body text-center">
                        <i class="bi bi-gender-female fs-2"></i>
                        <h6 class="card-title text-white">Female Members</h6>
                        <h3 class="card-text"><?php echo $female_members; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graph Dashboards -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">TPD Director Dashboard</h4> <!-- Updated header -->
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Members per Assembly (Bar Chart) -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Members per Assembly</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="membersPerAssemblyChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- Member Growth Over Time (Line Chart) -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Member Growth Over Time (Last 12 Months)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="memberGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Members per Assembly Chart
        const membersPerAssemblyData = <?php echo $members_per_assembly_json; ?>;
        const assemblyLabels = membersPerAssemblyData.map(item => item.assembly_name || 'N/A');
        const assemblyCounts = membersPerAssemblyData.map(item => item.member_count);

        const membersPerAssemblyChart = new Chart(document.getElementById('membersPerAssemblyChart'), {
            type: 'bar',
            data: {
                labels: assemblyLabels,
                datasets: [{
                    label: 'Number of Members',
                    data: assemblyCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Members'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Assembly'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        // Member Growth Over Time Chart
        const memberGrowthData = <?php echo $member_growth_json; ?>;
        const growthLabels = memberGrowthData.map(item => item.month);
        const growthCounts = memberGrowthData.map(item => item.count);

        const memberGrowthChart = new Chart(document.getElementById('memberGrowthChart'), {
            type: 'line',
            data: {
                labels: growthLabels,
                datasets: [{
                    label: 'New Members',
                    data: growthCounts,
                    fill: false,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of New Members'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    });
    </script>
</body>
</html>