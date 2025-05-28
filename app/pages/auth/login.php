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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header-text {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }
        .header-text h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-text p {
            font-size: 1.3rem;
            color: #34495e;
            font-weight: 300;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .login-card .card-body {
            padding: 3rem;
        }
        .login-card h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        .password-toggle {
            cursor: pointer;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-left: none;
            background: transparent;
        }
        .btn-primary {
            background-color: #4a90e2;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 1rem;
        }
        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
        }
        .btn-link {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .btn-link:hover {
            color: #357abd;
            text-decoration: underline;
        }
        footer {
            margin-top: auto;
            padding: 20px 0;
            background: rgba(255, 255, 255, 0.9);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-text">
            <h1>Epistles of Christ Ministry</h1>
            <p>Apprenticing the Nations unto full Maturity in Christ</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="card-body">
                        <h2>Welcome Back</h2>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form method="POST" action="login_process.php">
                            <div class="mb-4">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <span class="password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                            <div class="text-center">
                                <a href="forgot_credentials.php" class="btn-link">
                                    <i class="fas fa-key me-1"></i>Forgot Username/Password?
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>