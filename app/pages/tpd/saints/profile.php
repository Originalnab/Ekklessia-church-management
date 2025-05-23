<?php
session_start();
$page_title = "My Profile";
include "../../../../config/db.php";

// Check if the user is a Saint (function_id = 12)
$member_id = $_SESSION['member_id'] ?? null;
if (!$member_id) {
    $_SESSION['error_message'] = "Please log in to access this page.";
    header("Location: /Ekklessia-church-management/app/pages/tpd/index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT local_function_id FROM members WHERE member_id = ?");
$stmt->execute([$member_id]);
$function_id = $stmt->fetchColumn();
if ($function_id != 12) { // 12 = Saint
    $_SESSION['error_message'] = "Access denied. You are not a Saint.";
    header("Location: /Ekklessia-church-management/app/pages/tpd/index.php");
    exit;
}

// Fetch member details
try {
    $stmt = $pdo->prepare("
        SELECT m.first_name, m.last_name, m.contact, m.email, m.joined_date, m.status, 
               h.name AS household_name, a.name AS assembly_name
        FROM members m
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id
        WHERE m.member_id = ?
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching profile data: " . $e->getMessage();
    $member = [];
}

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = $_POST['contact'] ?? '';
    $email = $_POST['email'] ?? '';

    try {
        $stmt = $pdo->prepare("UPDATE members SET contact = ?, email = ? WHERE member_id = ?");
        $stmt->execute([$contact, $email, $member_id]);
        $_SESSION['success_message'] = "Profile updated successfully.";
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
    }
}

$base_url = '/Ekklessia-church-management/app/pages';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']);
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<?php include "../../../../includes/sidebar.php"; ?>

<main class="container flex-grow-1 py-2">
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation Card -->
    <div class="card nav-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/saints/profile.php" class="nav-link-btn">
                        <i class="bi bi-person-fill text-primary"></i><span>My Profile</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/saints/household.php" class="nav-link-btn">
                        <i class="bi bi-house-fill text-warning"></i><span>My Household</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/saints/events.php" class="nav-link-btn">
                        <i class="bi bi-calendar-event text-success"></i><span>Events</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">My Profile</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="bi bi-person-circle" style="font-size: 5rem; color: #007bff;"></i>
                        <h5 class="mt-3"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h5>
                        <p class="text-muted">Saint</p>
                    </div>
                </div>
                <div class="col-md-8">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Household</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($member['household_name'] ?? 'Not Assigned') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assembly</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($member['contact'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Joined Date</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($member['joined_date'] ? (new DateTime($member['joined_date']))->format('jS F, Y') : 'N/A') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($member['status'] ?? 'N/A') ?>" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .nav-card { background-color: #ffffff; border: none; box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px; padding: 20px; margin-bottom: 20px; }
    .nav-card .nav-link-btn { display: flex; flex-direction: column; align-items: center; text-align: center; text-decoration: none; color: #333; padding: 15px; border-radius: 8px; transition: background-color 0.3s, transform 0.2s; }
    .nav-link-btn:hover { background-color: #f1f3f5; transform: scale(1.05); }
    .nav-link-btn i { font-size: 1.5rem; margin-bottom: 8px; }
    .nav-link-btn span { font-size: 0.9rem; font-weight: 500; }
    [data-bs-theme="dark"] .nav-card { background-color: var(--card-bg-dark); }
    [data-bs-theme="dark"] .nav-link-btn { color: #e0e0e0; }
    [data-bs-theme="dark"] .nav-link-btn:hover { background-color: rgba(255, 255, 255, 0.1); }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>