<?php
require_once __DIR__ . "/../functions/role_management.php";
$function_id = $_SESSION['function_id'] ?? null;
$base_url = '/Ekklessia-church-management/app/pages';
?>

<!-- Navigation Card -->
<div class="nav-card mb-4">
    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                <i class="bi bi-people-fill text-primary"></i>
                <span>Members</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dropdown">
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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <nav class="nav nav-pills">
            <?php if (isset($_SESSION['member_id']) && memberHasPermission($_SESSION['member_id'], 'manage_roles')): ?>
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/roles/') !== false ? 'active' : '' ?>" 
                   href="/Ekklessia-church-management/app/pages/roles/">
                    <i class="bi bi-shield-lock me-1"></i>Roles
                </a>
            <?php endif; ?>
            <?php if (isset($_SESSION['member_id']) && memberHasPermission($_SESSION['member_id'], 'manage_permissions')): ?>
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/roles/permissions.php') !== false ? 'active' : '' ?>" 
                   href="/Ekklessia-church-management/app/pages/roles/permissions.php">
                    <i class="bi bi-key me-1"></i>Permissions
                </a>
            <?php endif; ?>
        </nav>
    </div>
</div>
