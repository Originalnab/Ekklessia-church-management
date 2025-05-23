<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['member_id']) || !isset($_SESSION['force_credentials_change'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['new_username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if the new username is already taken
            $stmt = $pdo->prepare("SELECT member_id FROM members WHERE username = :username AND member_id != :member_id");
            $stmt->execute(['username' => $new_username, 'member_id' => $_SESSION['member_id']]);
            if ($stmt->fetch()) {
                $error = "Username is already taken.";
            } else {
                // Update the members table with new credentials
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE members 
                    SET username = :username, password = :password 
                    WHERE member_id = :member_id
                ");
                $stmt->execute([
                    'username' => $new_username,
                    'password' => $hashed_password,
                    'member_id' => $_SESSION['member_id']
                ]);

                // Delete the temporary credentials
                $stmt = $pdo->prepare("DELETE FROM temp_credentials WHERE member_id = :member_id");
                $stmt->execute(['member_id' => $_SESSION['member_id']]);

                // Clear the force change flag
                unset($_SESSION['force_credentials_change']);

                // Redirect based on role
                switch ($_SESSION['function_id']) {
                    case 8:
                        header("Location: ../dashboard/presiding_elder_home.php");
                        break;
                    case 9:
                        header("Location: ../dashboard/assistant_presiding_elder_home.php");
                        break;
                    case 10:
                        header("Location: ../dashboard/elder_home.php");
                        break;
                    case 11:
                        header("Location: ../dashboard/shepherd_home.php");
                        break;
                    case 12:
                        header("Location: ../dashboard/saint_home.php");
                        break;
                    default:
                        header("Location: login.php");
                        break;
                }
                exit;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Credentials - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-5">
        <h2>Change Your Credentials</h2>
        <p>You must set a new username and password to continue.</p>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="new_username" class="form-label">New Username</label>
                <input type="text" class="form-control" id="new_username" name="new_username" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>