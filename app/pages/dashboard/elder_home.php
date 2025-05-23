<?php
require_once '../auth/auth_middleware.php';
checkAuth();

if ($_SESSION['function_id'] != 10) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch dashboard data
require_once '../../config/config.php';

// Get the assemblies the Elder oversees
$elder_id = $_SESSION['member_id'];
$stmt = $pdo->prepare("
    SELECT assemblies_id 
    FROM member_household 
    WHERE member_id = :member_id
");
$stmt->execute(['member_id' => $elder_id]);
$elder_assemblies = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If the Elder is not assigned to any assemblies, show a message
if (empty($elder_assemblies)) {
    $total_assemblies = 0;
    $total_households = 0;
    $total_members = 0;
    $total_shepherds = 0;
    $total_saints = 0;
    $active_members = 0;
    $new_members = 0;
    $adult_ministry_members = 0; // New: Adult Ministry Members
    $children_ministry_members = 0; // New: Children's Ministry Members
    $male_members = 0; // New: Male Members
    $female_members = 0; // New: Female Members
    $members_per_assembly = [];
    $member_growth = [];
} else {
    // Total Assemblies (filtered for the Elder)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM assemblies WHERE assembly_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")");
    $stmt->execute($elder_assemblies);
    $total_assemblies = $stmt->fetch()['total'];

    // Total Households (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM households h
        WHERE h.assembly_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $total_households = $stmt->fetch()['total'];

    // Total Members (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $total_members = $stmt->fetch()['total'];

    // Total Shepherds (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.local_function_id = 11 
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $total_shepherds = $stmt->fetch()['total'];

    // Total Saints (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.local_function_id = 12 
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $total_saints = $stmt->fetch()['total'];

    // Active Members (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.status = 'Active saint' 
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $active_members = $stmt->fetch()['total'];

    // New Members (Last 30 Days, filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.joined_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $new_members = $stmt->fetch()['total'];

    // New: Adult Ministry Members (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.group_name = 'Adult Ministry'
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $adult_ministry_members = $stmt->fetch()['total'];

    // New: Children's Ministry Members (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.group_name = 'Children''s Ministry'
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $children_ministry_members = $stmt->fetch()['total'];

    // New: Male Members (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.gender = 'Male'
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $male_members = $stmt->fetch()['total'];

    // New: Female Members (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE m.gender = 'Female'
        AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
    ");
    $stmt->execute($elder_assemblies);
    $female_members = $stmt->fetch()['total'];

    // Data for Graphs
    // Members per Assembly (filtered for the Elder's assemblies)
    $stmt = $pdo->prepare("
        SELECT a.name AS assembly_name, COUNT(m.member_id) as member_count
        FROM assemblies a
        LEFT JOIN member_household mh ON a.assembly_id = mh.assemblies_id
        LEFT JOIN members m ON mh.member_id = m.member_id
        WHERE a.assembly_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
        GROUP BY a.assembly_id, a.name
    ");
    $stmt->execute($elder_assemblies);
    $members_per_assembly = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $members_per_assembly_json = json_encode($members_per_assembly);

    // Member Growth Over Time (last 12 months, filtered for the Elder's assemblies)
    $member_growth = [];
    if (!empty($elder_assemblies)) {
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(m.joined_date, '%Y-%m') AS month, COUNT(*) as count
            FROM members m
            JOIN member_household mh ON m.member_id = mh.member_id
            WHERE m.joined_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND mh.assemblies_id IN (" . implode(',', array_fill(0, count($elder_assemblies), '?')) . ")
            GROUP BY DATE_FORMAT(m.joined_date, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute($elder_assemblies);
        $raw_member_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate a list of the last 12 months
        $months = [];
        $currentDate = new DateTime();
        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $currentDate)->modify("-$i months")->format('Y-m');
            $months[$month] = 0; // Default count is 0
        }

        // Merge the query results with the list of months
        foreach ($raw_member_growth as $row) {
            $months[$row['month']] = (int)$row['count'];
        }

        // Convert to the format Chart.js expects
        foreach ($months as $month => $count) {
            $member_growth[] = ['month' => $month, 'count' => $count];
        }
    }
    $member_growth_json = json_encode($member_growth);
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elder Dashboard - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .nav-card {
            background-color: #ffffff;
            border: none;
            box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            margin-top: -30px;
            position: relative;
            top: -10px;
        }
        .nav-card .nav-link-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            text-decoration: none;
            color: #333;
            padding: 15px;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.2s;
        }
        .nav-link-btn:hover {
            background-color: #f1f3f5;
            transform: scale(1.05);
        }
        .nav-link-btn i {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        .nav-link-btn span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        [data-bs-theme="dark"] .nav-card {
            background-color: var(--card-bg-dark);
        }
        [data-bs-theme="dark"] .nav-link-btn {
            color: #e0e0e0;
        }
        [data-bs-theme="dark"] .nav-link-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        @media (prefers-color-scheme: dark) {
            .card {
                border: 1px solid #4a90e2 !important;
                box-shadow: 0 4px 12px -2px rgba(74, 144, 226, 0.5), 
                            0 0 20px rgba(74, 144, 226, 0.3) !important;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include '../../includes/header.php'; ?>
    <main class="container flex-grow-1 py-2">
        <!-- Navigation Card -->
        <?php include '../../includes/nav_card.php'; ?>

        <!-- Rest of dashboard content -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Elder Dashboard</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
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
                    <!-- New: Adult Ministry Members -->
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #3f51b5, #7986cb); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-person-arms-up fs-2"></i>
                                <h6 class="card-title text-white">Adult Ministry</h6>
                                <h3 class="card-text"><?php echo $adult_ministry_members; ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- New: Children's Ministry Members -->
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #ff9800, #ffb74d); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-emoji-smile fs-2"></i>
                                <h6 class="card-title text-white">Children's Ministry</h6>
                                <h3 class="card-text"><?php echo $children_ministry_members; ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- New: Male Members -->
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #2196f3, #64b5f6); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-gender-male fs-2"></i>
                                <h6 class="card-title text-white">Male Members</h6>
                                <h3 class="card-text"><?php echo $male_members; ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- New: Female Members -->
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
            </div>
        </div>

        <!-- Graph Dashboards -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Elder Dashboard</h4>
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
        console.log('Member Growth Data:', memberGrowthData); // Debug the data
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