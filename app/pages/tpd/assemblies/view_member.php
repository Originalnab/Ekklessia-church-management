<?php
session_start();
$page_title = "View Member Profile";
include "../../../config/db.php";

// Check if member_id is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Member ID not provided.";
    header("Location: assembly_management.php");
    exit;
}

$member_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT m.*, a.name AS assembly_name, cf.function_name AS role_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN church_functions cf ON m.local_function_id = cf.function_id
        WHERE m.member_id = ?
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        $_SESSION['error_message'] = "Member not found.";
        header("Location: assembly_management.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching member: " . $e->getMessage();
    header("Location: assembly_management.php");
    exit;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-4">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Navigation Card -->
    <div class="card nav-card" style="margin-top: -30px; position: relative; top: -10px;">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="dropdown dropdown-hover">
                        <a href="#" class="nav-link-btn dropdown-toggle" id="assembliesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-building-fill text-success"></i>
                            <span>Assemblies</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="assembliesDropdown">
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/assemblies/index.php">Assemblies</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/assemblies/assembly_management.php">Assembly Management</a></li>
                        </ul>
                    </div>
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

    <!-- Member Profile Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Member Profile</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="/Ekklessia-church-management/app/resources/assets/images/<?= htmlspecialchars($member['profile_photo'] ?? 'default.jpg') ?>" alt="Profile Photo" class="profile-photo mb-3">
                    <h5><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($member['role_name'] ?? 'N/A') ?></p>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Contact:</strong></label>
                            <p><?= htmlspecialchars($member['contact'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Email:</strong></label>
                            <p><?= htmlspecialchars($member['email'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Assembly:</strong></label>
                            <p><?= htmlspecialchars($member['assembly_name'] ?? 'Not Assigned') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Status:</strong></label>
                            <p><?= htmlspecialchars($member['status'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Created At:</strong></label>
                            <p><?= htmlspecialchars($member['created_at'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Updated At:</strong></label>
                            <p><?= htmlspecialchars($member['updated_at'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <a href="assembly_management.php" class="btn btn-gradient-blue">Back to Assembly Management</a>
            </div>
        </div>
    </div>
</main>

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
    .profile-photo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #007bff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .profile-photo:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .card {
        border-radius: 10px;
        border: none;
    }
    .card-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .form-label {
        font-weight: 500;
        color: #333;
    }
    p {
        margin-bottom: 0;
        color: #555;
    }
    .btn-gradient-blue {
        background: linear-gradient(45deg, #007bff, #00d4ff);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        transition: transform 0.2s;
    }
    .btn-gradient-blue:hover {
        transform: scale(1.05);
    }
    .dropdown-hover:hover .dropdown-menu {
        display: block;
    }
    .dropdown-hover .dropdown-menu {
        display: none;
        margin-top: 0;
    }
    .nav-card .nav-link-btn.dropdown-toggle {
        cursor: pointer;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>