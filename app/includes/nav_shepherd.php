<?php 
$base_url = '/Ekklessia-church-management/app/pages';
?>
<div class="card nav-card mt-5 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $base_url ?>/dashboard/shepherd_home.php" class="nav-link-btn">
                    <i class="bi bi-speedometer2 text-danger"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $base_url ?>/tpd/assemblies/index.php" class="nav-link-btn">
                    <i class="bi bi-building-fill text-success"></i>
                    <span>Assemblies</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                    <i class="bi bi-people-fill text-primary"></i>
                    <span>Members</span>
                </a>
            </div>            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $base_url ?>/tpd/households/index.php" class="nav-link-btn">
                    <i class="bi bi-house-fill text-warning"></i>
                    <span>Households</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $base_url ?>/events/view/index.php" class="nav-link-btn">
                    <i class="bi bi-calendar-event text-primary"></i>
                    <span>Events</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= $base_url ?>/specialized_ministries/index.php" class="nav-link-btn">
                    <i class="bi bi-person-vcard text-success"></i>
                    <span>Ministries</span>
                </a>
            </div>
        </div>
    </div>
</div>
