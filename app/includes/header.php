<?php
// app/includes/header.php
$base_url = '/Ekklessia-church-management/app/pages';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("Session variables in header.php: " . print_r($_SESSION, true));
require_once __DIR__ . '/../config/config.php';

$image_base_url = '/Ekklessia-church-management/app/resources/assets/images/';

$username = 'Guest';
$profile_picture = '';
$church_name = 'Epistles of Christ';
$assembly_name = 'N/A';
$household_name = 'N/A';
$assembly_id = 'N/A';
$household_id = 'N/A';

if (isset($_SESSION['member_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.username, m.profile_photo, 
                   a.name AS assembly_name, a.assembly_id AS assembly_id,
                   h.name AS household_name, h.household_id AS household_id
            FROM members m
            LEFT JOIN member_household mh ON m.member_id = mh.member_id
            LEFT JOIN assemblies a ON mh.assemblies_id = a.assembly_id
            LEFT JOIN households h ON mh.household_id = h.household_id
            WHERE m.member_id = :member_id
            LIMIT 1
        ");
        $stmt->execute(['member_id' => $_SESSION['member_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $username = htmlspecialchars($user['username'] ?? 'Guest');
            $assembly_name = htmlspecialchars($user['assembly_name'] ?? 'N/A');
            $household_name = htmlspecialchars($user['household_name'] ?? 'N/A');
            
            if (!empty($user['profile_photo']) && strtolower($user['profile_photo']) !== 'null') {
                $profile_photo_cleaned = basename(trim($user['profile_photo']));
                $profile_picture_path = $image_base_url . rawurlencode($profile_photo_cleaned);
                
                $file_path = realpath(__DIR__ . '/../resources/assets/images/' . $profile_photo_cleaned);
                if ($file_path && file_exists($file_path)) {
                    $profile_picture = $profile_picture_path;
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching user details: " . $e->getMessage());
    }
}
?>

<?php if (!isset($page_title)) $page_title = "Ekklessia Church Management"; ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/Ekklessia-church-management/public/css/style.css" rel="stylesheet">
    <link href="/Ekklessia-church-management/public/css/members.css" rel="stylesheet">
    
    <style>
        .top-header {
            position: fixed;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #1e90ff 0%, #0047ab 100%);
            color: white;
            z-index: 1050;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            height: 60px;
        }
        
        .profile-image-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: border-color 0.2s ease;
        }
        
        .profile-image-container:hover {
            border-color: rgba(255, 255, 255, 0.8);
        }
        
        .church-info {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .separator {
            margin: 0 8px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .brand-logo {
            font-size: 1.3rem;
            font-weight: 700;
            color: white !important;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: opacity 0.2s ease;
        }
        
        .brand-logo:hover {
            opacity: 0.9;
        }
        
        #sidebarCollapse {
            background: transparent;
            border: none;
            color: white;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        #sidebarCollapse:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .theme-toggle {
            color: white;
            background: transparent;
            border: none;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .notification-icon {
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            color: white;
        }
        
        .notification-icon:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            transform: translate(25%, -25%);
        }
        
        .dropdown-menu {
            background: white;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 8px 0;
        }
        
        .dropdown-item {
            padding: 8px 16px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease;
        }
        
        .dropdown-item i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-divider {
            margin: 8px 0;
            border-top: 1px solid #eee;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        [data-bs-theme="dark"] .top-header {
            background: linear-gradient(135deg, #0a192f 0%, #1a365d 100%);
        }
        
        [data-bs-theme="dark"] .dropdown-menu {
            background: #222;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        [data-bs-theme="dark"] .dropdown-item {
            color: #fff;
        }
        
        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-bs-theme="dark"] .dropdown-divider {
            border-top-color: rgba(255, 255, 255, 0.1);
        }
    </style>
    
    <!-- Role Management CSS -->
    <?php if (strpos($_SERVER['PHP_SELF'], '/roles/') !== false): ?>
        <link href="/Ekklessia-church-management/Public/assets/css/roles.css" rel="stylesheet">
    <?php endif; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global modal cleanup function
        window.cleanupModals = function() {
            const existingBackdrops = document.querySelectorAll('.modal-backdrop');
            existingBackdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        };

        // Clean up modals on page load
        cleanupModals();

        // Handle all modal events globally
        document.body.addEventListener('show.bs.modal', function(event) {
            // Clean up any existing modals before showing new one
            cleanupModals();
        });

        document.body.addEventListener('hidden.bs.modal', function(event) {
            // Clean up after modal is hidden
            cleanupModals();
        });

        // Rest of your existing header.php script
        // Theme initialization
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        const themeIcon = document.querySelector('.theme-toggle i');
        themeIcon.classList.add(savedTheme === 'dark' ? 'bi-sun' : 'bi-moon-stars');

        // Theme toggle
        document.querySelector('.theme-toggle').addEventListener('click', function() {
            const htmlEl = document.documentElement;
            const isDark = htmlEl.getAttribute('data-bs-theme') === 'dark';
            htmlEl.setAttribute('data-bs-theme', isDark ? 'light' : 'dark');
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
            
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-moon-stars', !isDark);
            icon.classList.toggle('bi-sun', isDark);
        });

        // Sidebar toggle
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            const sidebar = document.getElementById("mySidebar");
            if (!sidebar) return;

            sidebar.style.width = sidebar.style.width === "250px" ? "0" : "250px";
        });

        // Initialize dropdown properly
        const userDropdown = document.getElementById('userDropdown');
        if (userDropdown) {
            // Create a dropdown instance
            const dropdown = new bootstrap.Dropdown(userDropdown);
            
            // Toggle on click
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.toggle();
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target)) {
                    dropdown.hide();
                }
            });
        }
    });
    </script>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Top Header -->
<header class="top-header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <button id="sidebarCollapse" class="btn d-flex align-items-center justify-content-center">
            <i class="bi bi-list fs-4"></i>
        </button>
        <a href="/Ekklessia-church-management/public/index.php" class="brand-logo ms-3">EPCADMIN</a>
    </div>
    
    <div class="d-flex align-items-center gap-4">
        <div class="church-info d-none d-md-block">
            <span class="church-name"><?php echo $church_name; ?></span>
            <span class="separator">|</span>
            <span class="assembly-name"><?php echo $assembly_name; ?></span>
            <span class="separator">|</span>
            <span class="household-name"><?php echo $household_name; ?></span>
        </div>

        <button class="theme-toggle btn d-flex align-items-center justify-content-center">
            <i class="bi bi-moon-stars fs-5"></i>
        </button>

        <div class="notification-icon position-relative d-flex align-items-center justify-content-center">
            <i class="bi bi-bell fs-5"></i>
            <span class="badge bg-danger notification-badge">3</span>
        </div>

        <div class="dropdown">
            <button class="user-profile border-0 bg-transparent p-0" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-flex align-items-center gap-2">
                    <div class="profile-image-container">
                        <?php if (!empty($profile_picture)): ?>
                            <img src="<?php echo $profile_picture; ?>" alt="Profile" class="w-100 h-100 object-fit-cover">
                        <?php else: ?>
                            <div class="profile-placeholder d-flex align-items-center justify-content-center h-100 bg-light">
                                <i class="bi bi-person text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-none d-md-flex align-items-center text-white">
                        <span class="me-2"><?php echo $username; ?></span>
                        <i class="bi bi-chevron-down small"></i>
                    </div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end mt-2">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear-fill"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/Ekklessia-church-management/app/pages/auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a></li>
            </ul>
        </div>
    </div>
</header>

<!-- Main Content Area -->
<div class="flex-grow-1">
    <main class="container-fluid py-3 px-4">
        <!-- Page content will be included here -->
    </main>
</div>

<!-- jQuery first, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="/Ekklessia-church-management/public/assets/js/script.js"></script>

<!-- Role Management JS -->
<?php if (strpos($_SERVER['PHP_SELF'], '/roles/') !== false): ?>
    <script src="/Ekklessia-church-management/Public/assets/js/roles.js"></script>
<?php endif; ?>
</body>
</html>