<?php
require_once '../auth/auth_middleware.php';
checkAuth();

if ($_SESSION['function_id'] != 8) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch dashboard data
require_once '../../config/config.php';

// Get the assembly the Presiding Elder oversees
$presiding_elder_id = $_SESSION['member_id'];
$stmt = $pdo->prepare("SELECT assemblies_id FROM members WHERE member_id = :member_id AND local_function_id = 8");
$stmt->execute(['member_id' => $presiding_elder_id]);
$presiding_elder_assembly = $stmt->fetchColumn();

// If the Presiding Elder is not assigned to any assembly, show a message
if (empty($presiding_elder_assembly)) {
    $zone_name = 'N/A';
    $total_assemblies = 0;
    $total_households = 0;
    $total_members = 0;
    $total_shepherds = 0;
    $total_saints = 0;
    $active_members = 0;
    $new_members = 0;
    $adult_ministry_members = 0;
    $children_ministry_members = 0;
    $male_members = 0;
    $female_members = 0;
    $members_per_assembly = [];
    $member_growth = [];
    $recent_members = [];
    $household_distribution = [];
    $elders = [];
    $shepherds = [];
    $assembly_members = [];
} else {
    // Fetch Zone Name for the Presiding Elder's assembly
    $stmt = $pdo->prepare("
        SELECT z.name AS zone_name 
        FROM zones z
        JOIN assemblies a ON a.zone_id = z.zone_id
        WHERE a.assembly_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $zone_name = $stmt->fetchColumn() ?: 'N/A';

    // Total Assemblies
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM assemblies WHERE assembly_id = ?");
    $stmt->execute([$presiding_elder_assembly]);
    $total_assemblies = $stmt->fetch()['total'];

    // Total Households
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM households h
        WHERE h.assembly_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $total_households = $stmt->fetch()['total'];

    // Total Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $total_members = $stmt->fetch()['total'];

    // Total Shepherds
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.local_function_id = 11 
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $total_shepherds = $stmt->fetch()['total'];

    // Total Saints
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.local_function_id = 12 
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $total_saints = $stmt->fetch()['total'];

    // Active Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.status = 'Active saint' 
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $active_members = $stmt->fetch()['total'];

    // New Members (Last 30 Days)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.joined_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $new_members = $stmt->fetch()['total'];

    // Adult Ministry Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.group_name = 'Adult Ministry'
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $adult_ministry_members = $stmt->fetch()['total'];

    // Children's Ministry Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.group_name = 'Children''s Ministry'
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $children_ministry_members = $stmt->fetch()['total'];

    // Male Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.gender = 'Male'
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $male_members = $stmt->fetch()['total'];

    // Female Members
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.gender = 'Female'
        AND m.assemblies_id = ?
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $female_members = $stmt->fetch()['total'];

    // Recent Members (Last 7)
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.first_name, m.last_name, m.joined_date, m.status, m.gender, 
               h.name as household_name, a.name as assembly_name, m.profile_photo
        FROM members m
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        WHERE m.assemblies_id = ?
        ORDER BY m.joined_date DESC
        LIMIT 7
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $recent_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Elders
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.first_name, m.last_name, m.contact, m.digital_address, 
               m.profile_photo, h.name as household_name
        FROM members m
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        WHERE m.assemblies_id = ? 
        AND m.local_function_id = 8
        ORDER BY m.first_name, m.last_name
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $elders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Shepherds
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.first_name, m.last_name, m.contact, m.digital_address, 
               m.profile_photo, h.name as household_name
        FROM members m
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        WHERE m.assemblies_id = ? 
        AND m.local_function_id = 11
        ORDER BY m.first_name, m.last_name
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $shepherds = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Assembly Members (limit to 10)
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.first_name, m.last_name, m.contact, m.digital_address, 
               m.profile_photo, h.name as household_name
        FROM members m
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        WHERE m.assemblies_id = ?
        ORDER BY m.joined_date DESC
        LIMIT 10
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $assembly_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Household Distribution Data
    $stmt = $pdo->prepare("
        SELECT h.name AS household_name, COUNT(m.member_id) as member_count
        FROM households h
        LEFT JOIN member_household mh ON h.household_id = mh.household_id
        LEFT JOIN members m ON mh.member_id = m.member_id
        WHERE h.assembly_id = ?
        GROUP BY h.household_id, h.name
        ORDER BY member_count DESC
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $household_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $household_distribution_json = json_encode($household_distribution);

    // Data for Graphs
    // Members per Assembly
    $stmt = $pdo->prepare("
        SELECT a.name AS assembly_name, COUNT(m.member_id) as member_count
        FROM assemblies a
        LEFT JOIN members m ON a.assembly_id = m.assemblies_id
        WHERE a.assembly_id = ?
        GROUP BY a.assembly_id, a.name
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $members_per_assembly = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $members_per_assembly_json = json_encode($members_per_assembly);

    // Member Growth Over Time (last 12 months)
    $member_growth = [];
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(m.joined_date, '%Y-%m') AS month, COUNT(*) as count
        FROM members m
        WHERE m.joined_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        AND m.assemblies_id = ?
        GROUP BY DATE_FORMAT(m.joined_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $raw_member_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate a list of the last 12 months
    $months = [];
    $currentDate = new DateTime();
    for ($i = 11; $i >= 0; $i--) {
        $month = (clone $currentDate)->modify("-$i months")->format('Y-m');
        $months[$month] = 0;
    }

    // Merge the query results with the list of months
    foreach ($raw_member_growth as $row) {
        $months[$row['month']] = (int)$row['count'];
    }

    // Convert to the format Chart.js expects
    foreach ($months as $month => $count) {
        $member_growth[] = ['month' => $month, 'count' => $count];
    }
    $member_growth_json = json_encode($member_growth);

    // Gender Distribution Data
    $stmt = $pdo->prepare("
        SELECT m.gender, COUNT(*) as count
        FROM members m
        WHERE m.assemblies_id = ?
        GROUP BY m.gender
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $gender_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $gender_distribution_json = json_encode($gender_distribution);

    // Age Distribution Data
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                WHEN TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
                WHEN TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
                WHEN TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) BETWEEN 36 AND 50 THEN '36-50'
                WHEN TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) > 50 THEN 'Over 50'
                ELSE 'Unknown'
            END as age_group,
            COUNT(*) as count
        FROM members m
        WHERE m.assemblies_id = ?
        GROUP BY age_group
        ORDER BY 
            CASE age_group
                WHEN 'Under 18' THEN 1
                WHEN '18-25' THEN 2
                WHEN '26-35' THEN 3
                WHEN '36-50' THEN 4
                WHEN 'Over 50' THEN 5
                ELSE 6
            END
    ");
    $stmt->execute([$presiding_elder_assembly]);
    $age_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $age_distribution_json = json_encode($age_distribution);
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device=width, initial-scale=1.0">
    <title>Presiding Elder Dashboard - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/Ekklessia-church-management/Public/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            font-weight: 600;
        }
        .gradient-table-header {
            background: linear-gradient(45deg, #3b82f6, #6366f1);
            color: white;
        }
        .gradient-table-header-secondary {
            background: linear-gradient(45deg, rgb(2, 42, 123), rgb(85, 126, 183));
            color: white;
        }
        .gradient-table-header-success {
            background: linear-gradient(45deg, #10b981, rgb(5, 94, 66));
            color: white;
        }
        .gradient-table-header-warning {
            background: linear-gradient(45deg, rgb(11, 179, 245), rgb(13, 243, 235));
            color: white;
        }
        .badge-status {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 0.25rem;
            color: white !important;
        }
        .badge-active {
            background-color: #166534;
        }
        .badge-inactive {
            background-color: #991b1b;
        }
        .badge-new {
            background-color: #1e40af;
        }
        
        /* Enhanced Profile Picture Styles */
        .member-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 3px solid #3b82f6;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }
        .member-photo:hover {
            border-color: #10b981;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        /* Role-specific photo borders */
        .photo-elder {
            border-color: #8b5cf6;
        }
        .photo-shepherd {
            border-color: #10b981;
        }
        .photo-member {
            border-color: #3b82f6;
        }
        
        /* Photo placeholder styles */
        .photo-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            border: 3px solid #9ca3af;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .photo-placeholder i {
            color: #6b7280;
            font-size: 1.2rem;
        }
        
        .scrollable-table {
            max-height: 300px;
            overflow-y: auto;
        }
        .dashboard-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e40af;
            display: flex;
            align-items: center;
        }
        .dashboard-title i {
            margin-right: 10px;
        }
        
        /* Dark mode styles */
        @media (prefers-color-scheme: dark) {
            .card {
                border: 1px solid #4a90e2 !important;
                box-shadow: 0 4px 12px -2px rgba(74, 144, 226, 0.5), 
                            0 0 20px rgba(74, 144, 226, 0.3) !important;
            }
            .card-header {
                border-bottom: 1px solid rgba(74, 144, 226, 0.3);
            }
            .table th {
                background-color: #1a1a2e;
            }
            .table {
                color: #e0e0e0;
            }
            .dashboard-title {
                color: #93c5fd;
            }
            .photo-placeholder {
                background-color: #2d3748;
                border-color: #4b5563;
            }
            .photo-placeholder i {
                color: #9ca3af;
            }
            .member-photo {
                border-color: #6366f1;
                box-shadow: 0 2px 8px rgba(99, 102, 241, 0.5);
            }
            .member-photo:hover {
                border-color: #10b981;
            }
            .photo-elder {
                border-color: #8b5cf6;
            }
            .photo-shepherd {
                border-color: #10b981;
            }
            .photo-member {
                border-color: #6366f1;
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
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Presiding Elder Dashboard - Members Overview</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                        <div class="card-body text-center">
                            <i class="bi bi-globe-americas fs-2"></i>
                            <h6 class="card-title text-white">Zone</h6>
                            <h3 class="card-text"><?php echo htmlspecialchars($zone_name); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
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
                <div class="col-md-3">
                    <div class="card shadow-sm" style="background: linear-gradient(45deg, #3f51b5, #7986cb); color: white;">
                        <div class="card-body text-center">
                            <i class="bi bi-person-arms-up fs-2"></i>
                            <h6 class="card-title text-white">Adult Ministry</h6>
                            <h3 class="card-text"><?php echo $adult_ministry_members; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6>Gender Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="genderDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6>Age Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="ageDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6>Household Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="householdDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6>Member Growth (Last 12 Months)</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="memberGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Elders Table -->
                <div class="card mb-3">
                    <div class="card-header gradient-table-header-secondary text-white ">
                        <h6 class="mb-0">Elders</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Digital Address</th>
                                        <th>Household</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($elders)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No elders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($elders as $elder): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($elder['profile_photo'])): ?>
                                                        <img src="/Ekklessia-church-management/app/Public/assets/images/<?= htmlspecialchars($elder['profile_photo']) ?>" class="member-photo photo-elder" alt="Profile Photo">
                                                    <?php else: ?>
                                                        <div class="photo-placeholder">
                                                            <i class="bi bi-person text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($elder['first_name'] . ' ' . htmlspecialchars($elder['last_name'])) ?></td>
                                                <td><?= htmlspecialchars($elder['contact']) ?></td>
                                                <td><?= htmlspecialchars($elder['digital_address']) ?></td>
                                                <td><?= htmlspecialchars($elder['household_name'] ?? 'No household') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Shepherds Table -->
                <div class="card mb-3">
                    <div class="card-header gradient-table-header-success text-white ">
                        <h6 class="mb-0">Shepherds</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Digital Address</th>
                                        <th>Household</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($shepherds)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No shepherds found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($shepherds as $shepherd): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($shepherd['profile_photo'])): ?>
                                                        <img src="/Ekklessia-church-management/app/Public/assets/images/<?= htmlspecialchars($shepherd['profile_photo']) ?>" class="member-photo photo-shepherd" alt="Profile Photo">
                                                    <?php else: ?>
                                                        <div class="photo-placeholder">
                                                            <i class="bi bi-person text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($shepherd['first_name'] . ' ' . htmlspecialchars($shepherd['last_name'])) ?></td>
                                                <td><?= htmlspecialchars($shepherd['contact']) ?></td>
                                                <td><?= htmlspecialchars($shepherd['digital_address']) ?></td>
                                                <td><?= htmlspecialchars($shepherd['household_name'] ?? 'No household') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Assembly Members Table -->
                <div class="card">
                    <div class="card-header gradient-table-header text-white">
                        <h6 class="mb-0">Assembly Members (Recent 10)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive scrollable-table">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Contact</th>
                                        <th>Digital Address</th>
                                        <th>Household</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($assembly_members)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No members found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($assembly_members as $member): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($member['profile_photo'])): ?>
                                                        <img src="/Ekklessia-church-management/app/Public/assets/images/<?= htmlspecialchars($member['profile_photo']) ?>" class="member-photo photo-member" alt="Profile Photo">
                                                    <?php else: ?>
                                                        <div class="photo-placeholder">
                                                            <i class="bi bi-person text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                                <td>
                                                    <span class="badge <?= $member['status'] === 'Active saint' ? 'badge-active' : 'badge-inactive' ?>">
                                                        <?= htmlspecialchars($member['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($member['contact']) ?></td>
                                                <td><?= htmlspecialchars($member['digital_address']) ?></td>
                                                <td><?= htmlspecialchars($member['household_name'] ?? 'No household') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= $base_url ?>/tpd/members/index.php" class="btn btn-sm btn-outline-primary">
                            View All Members
                        </a>
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
        // Helper function to create gradients
        function createGradient(color1, color2) {
            const ctx = document.createElement('canvas').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 200, 0);
            gradient.addColorStop(0, color1);
            gradient.addColorStop(1, color2);
            return gradient;
        }

        // Gender Distribution Chart with gradient colors
        const genderData = <?php echo $gender_distribution_json; ?>;
        const genderLabels = genderData.map(item => item.gender || 'Unknown');
        const genderCounts = genderData.map(item => item.count);
        
        const genderGradients = [
            createGradient('#3b82f6', '#6366f1'),
            createGradient('#ec4899', '#d946ef'),
            createGradient('#94a3b8', '#64748b')
        ];

        const genderChart = new Chart(document.getElementById('genderDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: genderLabels,
                datasets: [{
                    data: genderCounts,
                    backgroundColor: genderGradients,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });

        // Age Distribution Chart with gradient colors
        const ageData = <?php echo $age_distribution_json; ?>;
        const ageLabels = ageData.map(item => item.age_group);
        const ageCounts = ageData.map(item => item.count);
        
        const ageGradients = [
            createGradient('#6366f1', '#4f46e5'),
            createGradient('#8b5cf6', '#7c3aed'),
            createGradient('#ec4899', '#db2777'),
            createGradient('#f43f5e', '#e11d48'),
            createGradient('#f97316', '#ea580c')
        ];

        const ageChart = new Chart(document.getElementById('ageDistributionChart'), {
            type: 'pie',
            data: {
                labels: ageLabels,
                datasets: [{
                    data: ageCounts,
                    backgroundColor: ageGradients,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Household Distribution Chart
        const householdData = <?php echo $household_distribution_json; ?>;
        const householdLabels = householdData.map(item => item.household_name || 'No household');
        const householdCounts = householdData.map(item => item.member_count);
        const householdColors = householdData.map((_, index) => {
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'];
            return colors[index % colors.length];
        });

        const householdChart = new Chart(document.getElementById('householdDistributionChart'), {
            type: 'bar',
            data: {
                labels: householdLabels,
                datasets: [{
                    label: 'Number of Members',
                    data: householdCounts,
                    backgroundColor: householdColors,
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
                            text: 'Household'
                        },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
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
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    tension: 0.4
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
                        display: false
                    }
                }
            }
        });
    });
    </script>
</body>
</html>





