<?php
session_start();
require_once '../../config/config.php';

if (isset($_SESSION['member_id'])) {
    // Check assigned roles and redirect
    try {
        $stmt = $pdo->prepare("
            SELECT mr.role_id, r.role_name, mr.function_id, cf.function_name
            FROM member_role mr
            JOIN roles r ON mr.role_id = r.role_id
            JOIN church_functions cf ON mr.function_id = cf.function_id
            WHERE mr.member_id = :member_id
        ");
        $stmt->execute(['member_id' => $_SESSION['member_id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($roles)) {
            if (count($roles) > 1) {
                header("Location: ../dashboard/multi_role_dashboard.php");
            } else {
                // Map function IDs to dashboard pages
                $dashboardMap = [
                    '8' => '../dashboard/presiding_elder_home.php',
                    '9' => '../dashboard/assistant_presiding_elder_home.php',
                    '10' => '../dashboard/elder_home.php',
                    '11' => '../dashboard/shepherd_home.php',
                    '12' => '../dashboard/saint_home.php'
                ];

                $functionId = $roles[0]['function_id'];
                $dashboard = $dashboardMap[$functionId] ?? '../dashboard/multi_role_dashboard.php';
                header("Location: " . $dashboard);
            }
            exit;
        } else {
            error_log("No roles found for member_id: " . $_SESSION['member_id']);
            session_unset();
            session_destroy();
        }
    } catch (PDOException $e) {
        error_log("Error fetching roles: " . $e->getMessage());
        session_unset();
        session_destroy();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header-text {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header-text h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: black !important;
        }
        .header-text p {
            font-size: 1.2rem;
            color: #666;
        }
        .container {
            margin-bottom: 50px;
        }
        footer {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="header-text">
            <h1>Epistles of Christ Ministry</h1>
            <p>Apprenticing the Nations unto full Maturity in Christ</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2>Login</h2>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form method="POST" action="login_process.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="forgot_credentials.php" class="btn btn-link">Forgot Username/Password?</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>