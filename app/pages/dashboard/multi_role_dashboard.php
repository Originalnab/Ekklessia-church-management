<?php
session_start();
$page_title = "Role Selection";
include "../../config/config.php";
include "../auth/auth_check.php";
include "../../functions/role_management.php";

// Get all roles for the current member
$memberId = $_SESSION['member_id'];

try {
    // Get all roles for the member
    $stmt = $pdo->prepare("
        SELECT mr.role_id, r.role_name, r.hierarchy_level
        FROM member_role mr
        JOIN roles r ON mr.role_id = r.role_id
        WHERE mr.member_id = :member_id
        ORDER BY r.hierarchy_level ASC
    ");
    $stmt->execute(['member_id' => $memberId]);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle role selection
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_role'])) {
        $selectedRoleId = (int)$_POST['selected_role'];
        
        // Verify this role belongs to the member
        $validRole = false;
        foreach ($roles as $role) {
            if ($role['role_id'] === $selectedRoleId) {
                $validRole = true;
                $_SESSION['role_id'] = $role['role_id'];
                $_SESSION['role_name'] = $role['role_name'];
                $_SESSION['hierarchy_level'] = $role['hierarchy_level'];
                
                // Redirect based on role
                switch ($role['role_id']) {
                    case 4: // Shepherd
                        header("Location: shepherd_home.php");
                        break;
                    case 3: // Presiding Elder
                        header("Location: presiding_elder_home.php");
                        break;
                    case 2: // Zone Director
                        header("Location: tpd_director_home.php");
                        break;
                    case 1: // EXCO
                        header("Location: exco_home.php");
                        break;
                    default:
                        header("Location: member_home.php");
                }
                exit;
            }
        }
        
        if (!$validRole) {
            $_SESSION['error'] = "Invalid role selection";
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <?php include '../../includes/header.php'; ?>
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
        .role-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Dark mode enhancements */
        [data-bs-theme="dark"] .card {
            background: var(--card-bg-dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .role-card {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-bs-theme="dark"] .role-card h5,
        [data-bs-theme="dark"] .role-card p {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        [data-bs-theme="dark"] .role-description {
            color: rgba(255, 255, 255, 0.7);
        }

        [data-bs-theme="dark"] .role-actions .btn {
            border-color: rgba(255, 255, 255, 0.2);
        }

        [data-bs-theme="dark"] .role-actions .btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }
        </style>
    </head>
<body>
    <?php include '../../includes/nav_card.php'; ?>

    <div class="container py-4">
        <?php include "../../includes/alerts.php"; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Select Your Role</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            You have multiple roles assigned. Please select the role you want to use:
                        </p>
                        <form method="POST" action="">
                            <?php foreach ($roles as $role): ?>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="selected_role" 
                                           id="role_<?php echo $role['role_id']; ?>" 
                                           value="<?php echo $role['role_id']; ?>" required>
                                    <label class="form-check-label" for="role_<?php echo $role['role_id']; ?>">
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary">Continue</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
