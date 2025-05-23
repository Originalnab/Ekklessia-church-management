<?php
require_once '../auth/auth_check.php';
checkAuth();

require_once '../../config/config.php';

// Get the shepherd's member ID and their assembly
$shepherd_id = $_SESSION['member_id'];
$stmt = $pdo->prepare("SELECT assemblies_id FROM members WHERE member_id = :member_id");
$stmt->execute(['member_id' => $shepherd_id]);
$shepherd_assembly = $stmt->fetchColumn();

// If the shepherd is not assigned to any assembly, show defaults
if (empty($shepherd_assembly)) {
    $zone_name = 'N/A';
    $household_name = 'N/A';
    $assembly_name = 'N/A';
    $total_assigned_members = 0;
    $male_members = 0;
    $female_members = 0;
    $adult_ministry_members = 0;
    $children_ministry_members = 0;
    $total_referrals = 0;
    $members_comparison = [];
    $member_growth = [];
    $assigned_members = [];
    $presiding_elder_assistant = [];
    $household_members = [];
    $assembly_members = [];
    $referrals = [];
} else {
    // Fetch Zone Name for the Shepherd's assembly
    $stmt = $pdo->prepare("
        SELECT z.name AS zone_name 
        FROM zones z
        JOIN assemblies a ON a.zone_id = z.zone_id
        WHERE a.assembly_id = ?
    ");
    $stmt->execute([$shepherd_assembly]);
    $zone_name = $stmt->fetchColumn() ?: 'N/A';

    // Fetch Assembly Name
    $stmt = $pdo->prepare("
        SELECT name AS assembly_name 
        FROM assemblies 
        WHERE assembly_id = ?
    ");
    $stmt->execute([$shepherd_assembly]);
    $assembly_name = $stmt->fetchColumn() ?: 'N/A';

    // Fetch Household Name (assuming the shepherd oversees one primary household)
    $stmt = $pdo->prepare("
        SELECT h.name AS household_name 
        FROM households h
        JOIN member_household mh ON h.household_id = mh.household_id
        WHERE mh.shepherd_id = ?
        LIMIT 1
    ");
    $stmt->execute([$shepherd_id]);
    $household_name = $stmt->fetchColumn() ?: 'N/A';

    // Total Assigned Members (members assigned to this shepherd)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        WHERE mh.shepherd_id = ?
    ");
    $stmt->execute([$shepherd_id]);
    $total_assigned_members = $stmt->fetch()['total'];

    // Male Members (assigned to this shepherd)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        WHERE mh.shepherd_id = ? AND m.gender = 'Male'
    ");
    $stmt->execute([$shepherd_id]);
    $male_members = $stmt->fetch()['total'];

    // Female Members (assigned to this shepherd)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        WHERE mh.shepherd_id = ? AND m.gender = 'Female'
    ");
    $stmt->execute([$shepherd_id]);
    $female_members = $stmt->fetch()['total'];

    // Adult Ministry Members (assigned to this shepherd)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        WHERE mh.shepherd_id = ? AND m.group_name = 'Adult Ministry'
    ");
    $stmt->execute([$shepherd_id]);
    $adult_ministry_members = $stmt->fetch()['total'];

    // Children's Ministry Members (assigned to this shepherd)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        WHERE mh.shepherd_id = ? AND m.group_name = 'Children''s Ministry'
    ");
    $stmt->execute([$shepherd_id]);
    $children_ministry_members = $stmt->fetch()['total'];

    // Total Referrals (members referred by this shepherd)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM members m
        WHERE m.referral_id = ?
    ");
    $stmt->execute([$shepherd_id]);
    $total_referrals = $stmt->fetch()['total'];

    // Data for Household vs Assembly Comparison Chart
    // Get count of members in the shepherd's household
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as member_count
        FROM member_household mh
        JOIN households h ON mh.household_id = h.household_id
        WHERE mh.shepherd_id = ? AND h.name = ?
    ");
    $stmt->execute([$shepherd_id, $household_name]);
    $shepherd_household_count = $stmt->fetch()['member_count'];

    // Get count of members in the entire assembly
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as member_count
        FROM members
        WHERE assemblies_id = ?
    ");
    $stmt->execute([$shepherd_assembly]);
    $assembly_count = $stmt->fetch()['member_count'];

    // Prepare data for comparison chart
    $members_comparison = [
        [
            'label' => 'Your Household',
            'count' => $shepherd_household_count
        ],
        [
            'label' => 'Entire Assembly',
            'count' => $assembly_count
        ]
    ];
    $members_comparison_json = json_encode($members_comparison);

    // Member Growth Over Time (last 12 months)
    // Get growth data for shepherd's household
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(m.joined_date, '%Y-%m') AS month, COUNT(*) as count
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        JOIN households h ON mh.household_id = h.household_id
        WHERE mh.shepherd_id = ? AND h.name = ? AND m.joined_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(m.joined_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$shepherd_id, $household_name]);
    $raw_shepherd_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get assembly-wide growth data
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(joined_date, '%Y-%m') AS month, COUNT(*) as count
        FROM members
        WHERE assemblies_id = ? AND joined_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(joined_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$shepherd_assembly]);
    $raw_assembly_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate a list of the last 12 months
    $months = [];
    $currentDate = new DateTime();
    for ($i = 11; $i >= 0; $i--) {
        $month = (clone $currentDate)->modify("-$i months")->format('Y-m');
        $months[$month] = [
            'shepherd_count' => 0,
            'assembly_count' => 0
        ];
    }

    // Merge the query results with the list of months
    foreach ($raw_shepherd_growth as $row) {
        $months[$row['month']]['shepherd_count'] = (int)$row['count'];
    }
    
    foreach ($raw_assembly_growth as $row) {
        $months[$row['month']]['assembly_count'] = (int)$row['count'];
    }

    // Convert to the format Chart.js expects
    $member_growth = [];
    foreach ($months as $month => $counts) {
        $member_growth[] = [
            'month' => $month,
            'shepherd_count' => $counts['shepherd_count'],
            'assembly_count' => $counts['assembly_count']
        ];
    }
    $member_growth_json = json_encode($member_growth);

    // Fetch Assigned Members for Table
    $stmt = $pdo->prepare("
        SELECT m.first_name, m.last_name, m.contact, m.profile_photo
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        WHERE mh.shepherd_id = ?
    ");
    $stmt->execute([$shepherd_id]);
    $assigned_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Presiding Elder and Assistant Presiding Elder for Table
    $stmt = $pdo->prepare("
        SELECT m.first_name, m.last_name, m.contact, m.profile_photo, m.local_function_id
        FROM members m
        WHERE m.assemblies_id = ? AND m.local_function_id IN (8, 9)
    ");
    $stmt->execute([$shepherd_assembly]);
    $presiding_elder_assistant = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Household Members for Table (filtered by $household_name)
    $stmt = $pdo->prepare("
        SELECT m.profile_photo, m.first_name, m.last_name, m.gender, m.contact, m.digital_address
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        JOIN households h ON mh.household_id = h.household_id
        WHERE mh.shepherd_id = ? AND h.name = ?
    ");
    $stmt->execute([$shepherd_id, $household_name]);
    $household_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch All Assembly Members for New Table
    $stmt = $pdo->prepare("
        SELECT m.profile_photo, m.first_name, m.last_name, m.contact, m.digital_address
        FROM members m
        WHERE m.assemblies_id = ?
    ");
    $stmt->execute([$shepherd_assembly]);
    $assembly_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Referrals for New Table
    $stmt = $pdo->prepare("
        SELECT m.profile_photo, m.first_name, m.last_name, m.contact, m.digital_address
        FROM members m
        WHERE m.referral_id = ?
    ");
    $stmt->execute([$shepherd_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shepherd Dashboard - Ekklessia Church Management</title>
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
        
        .profile-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #007bff;
        }
        .dashboard-section {
            margin-bottom: 20px;
        }
        .table-responsive {
            max-height: 300px;
            overflow-y: auto;
        }
        .assembly-members-table th, .assembly-members-table td {
            padding: 8px;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include '../../includes/header.php'; ?>
    <main class="container flex-grow-1 py-2">
        <!-- Navigation Card -->
        <?php include '../../includes/nav_card.php'; ?>

        <!-- Shepherd Dashboard Card -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Shepherd Dashboard</h4>
            </div>
            <div class="card-body">
                <!-- Mini Dashboard -->
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
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-people fs-2"></i>
                                <h6 class="card-title text-white">Assigned Members</h6>
                                <h3 class="card-text"><?php echo $total_assigned_members; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #ffc107, #ffca28); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-house-fill fs-2"></i>
                                <h6 class="card-title text-white">Household Name</h6>
                                <h3 class="card-text"><?php echo htmlspecialchars($household_name); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #2196f3, #64b5f6); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-gender-male fs-2"></i>
                                <h6 class="card-title text-white">Male Members</h6>
                                <h3 class="card-text"><?php echo $male_members; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #e91e63, #f06292); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-gender-female fs-2"></i>
                                <h6 class="card-title text-white">Female Members</h6>
                                <h3 class="card-text"><?php echo $female_members; ?></h3>
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
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #ff9800, #ffb74d); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-emoji-smile fs-2"></i>
                                <h6 class="card-title text-white">Children's Ministry</h6>
                                <h3 class="card-text"><?php echo $children_ministry_members; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm" style="background: linear-gradient(45deg, #6b7280, #9ca3af); color: white;">
                            <div class="card-body text-center">
                                <i class="bi bi-person-plus fs-2"></i>
                                <h6 class="card-title text-white">Total Referrals</h6>
                                <h3 class="card-text"><?php echo $total_referrals; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graph Dashboards -->
                <div class="row g-3 mb-4">
                    <!-- Household vs Assembly Comparison Chart -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Household vs Assembly Members</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="membersComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- Member Growth Over Time Chart -->
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

                <!-- Tables Section -->
                <div class="row g-3">                    <!-- Assigned Members Table -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Assigned Members List</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($assigned_members)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">No assigned members found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($assigned_members as $member): ?>
                                                    <tr>
                                                        <td>
                                                            <img src="/Ekklessia-church-management/app/resources/assets/images/<?php echo $member['profile_photo'] ?: 'default.jpg'; ?>" alt="Profile Photo" class="profile-photo">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['contact']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>                    <!-- Presiding Elder and Assistant Table -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Presiding Elder & Assistant List</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Contact</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($presiding_elder_assistant)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No Presiding Elder or Assistant found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($presiding_elder_assistant as $person): ?>
                                                    <tr>
                                                        <td>
                                                            <img src="/Ekklessia-church-management/app/resources/assets/images/<?php echo $person['profile_photo'] ?: 'default.jpg'; ?>" alt="Profile Photo" class="profile-photo">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></td>
                                                        <td><?php echo $person['local_function_id'] == 8 ? 'Presiding Elder' : 'Assistant Presiding Elder'; ?></td>
                                                        <td><?php echo htmlspecialchars($person['contact']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Household Members Table -->
                    <div class="col-md-6 mt-3">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><?php echo htmlspecialchars($household_name); ?> Household Members</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Contact</th>
                                                <th>Digital Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($household_members)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No members found in this household.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($household_members as $member): ?>
                                                    <tr>                                        <td>
                                                            <img src="/Ekklessia-church-management/app/resources/assets/images/<?php echo $member['profile_photo'] ?: 'default.jpg'; ?>" alt="Profile Photo" class="profile-photo">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['gender']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['contact']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['digital_address']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Assembly Members Table -->
                    <div class="col-md-6 mt-3">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><?php echo htmlspecialchars($assembly_name); ?> Assembly Members</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover assembly-members-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Digital Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($assembly_members)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No members found in this assembly.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($assembly_members as $member): ?>
                                                    <tr>                                                        <td>
                                                            <img src="/Ekklessia-church-management/app/resources/assets/images/<?php echo htmlspecialchars($member['profile_photo'] ?: 'default.jpg'); ?>" alt="Profile Photo" class="profile-photo">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['contact']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['digital_address']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Referral List Table -->
                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-header text-white" style="background: linear-gradient(45deg, #3b82f6, #8b5cf6);">
                                <h5 class="mb-0">Referral List</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Digital Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($referrals)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No referrals found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($referrals as $referral): ?>
                                                    <tr>                                                        <td>
                                                            <img src="/Ekklessia-church-management/app/resources/assets/images/<?php echo $referral['profile_photo'] ?: 'default.jpg'; ?>" alt="Profile Photo" class="profile-photo">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($referral['contact']); ?></td>
                                                        <td><?php echo htmlspecialchars($referral['digital_address']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
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
        // Household vs Assembly Comparison Chart
        const membersComparisonData = <?php echo $members_comparison_json; ?>;
        const comparisonLabels = membersComparisonData.map(item => item.label);
        const comparisonCounts = membersComparisonData.map(item => item.count);

        const membersComparisonChart = new Chart(document.getElementById('membersComparisonChart'), {
            type: 'bar',
            data: {
                labels: comparisonLabels,
                datasets: [{
                    label: 'Number of Members',
                    data: comparisonCounts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
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
                            text: ''
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
        const shepherdGrowth = memberGrowthData.map(item => item.shepherd_count);
        const assemblyGrowth = memberGrowthData.map(item => item.assembly_count);

        const memberGrowthChart = new Chart(document.getElementById('memberGrowthChart'), {
            type: 'line',
            data: {
                labels: growthLabels,
                datasets: [
                    {
                        label: 'Your Household Growth',
                        data: shepherdGrowth,
                        fill: false,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Assembly Growth',
                        data: assemblyGrowth,
                        fill: false,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        tension: 0.1
                    }
                ]
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