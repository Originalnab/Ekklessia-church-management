<?php
$base_url = '/Ekklessia-church-management/app/pages';
?>

<!-- The Sidebar -->
<div id="mySidebar" class="sidebar">
    <div class="sidebar-header">
        <h5 class="text-white mb-0 ps-4 py-3">Navigation</h5>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= $base_url ?>/executive_counsel/index.php" class="nav-link">
            <i class="bi bi-shield-check"></i>
            <span>Executive Counsel</span>
        </a>
        
        <a href="<?= $base_url ?>/ped/index.php" class="nav-link">
            <i class="bi bi-bank"></i>
            <span>PED</span>
        </a>
        
        <!-- TPD Section with Submenu -->
        <div class="nav-item-group">
            <a href="#" class="nav-link has-submenu" onclick="toggleSubmenu('tpdSubmenu')">
                <i class="bi bi-people-fill"></i>
                <span>TPD</span>
                <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
            </a>
            <div id="tpdSubmenu" class="submenu">
                <a href="<?= $base_url ?>/tpd/zones/index.php" class="nav-link">
                    <i class="bi bi-globe2"></i>
                    <span>Zones</span>
                </a>
                <a href="<?= $base_url ?>/tpd/assemblies/index.php" class="nav-link">
                    <i class="bi bi-building"></i>
                    <span>Assemblies</span>
                </a>
                <a href="<?= $base_url ?>/tpd/households/index.php" class="nav-link">
                    <i class="bi bi-houses"></i>
                    <span>Households</span>
                </a>
                <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link">
                    <i class="bi bi-person-vcard"></i>
                    <span>Members</span>
                </a>
                <a href="<?= $base_url ?>/tpd/events/index.php" class="nav-link">
                    <i class="bi bi-calendar-event"></i>
                    <span>Events</span>
                </a>
                
                <!-- Shepherd Management Link -->
                <a href="<?= $base_url ?>/tpd/shepherd_assignment/index.php" class="nav-link">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Shepherd Management</span>
                </a>
                
                <!-- Member Roles Submenu -->
                <div class="nav-item-group">
                    <a href="#" class="nav-link has-submenu" onclick="toggleSubmenu('membersSubmenu')">
                        <i class="bi bi-person-lock"></i>
                        <span>Member Roles</span>
                        <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                    </a>
                    <div id="membersSubmenu" class="submenu">
                        <a href="<?= $base_url ?>/tpd/saints/saints_home.php" class="nav-link">
                            <i class="bi bi-person-check"></i>
                            <span>Saints</span>
                        </a>
                        <a href="<?= $base_url ?>/tpd/shepherds/shepherds_home.php" class="nav-link">
                            <i class="bi bi-person-badge"></i>
                            <span>Shepherds</span>
                        </a>
                    </div>
                </div>
            </div>
        </div> <!-- End of TPD nav-item-group -->

        <!-- Event Center Section with Submenu -->
        <div class="nav-item-group">
            <a href="#" class="nav-link has-submenu" onclick="toggleSubmenu('eventCenterSubmenu')">
                <i class="bi bi-calendar3"></i>
                <span>Event Center</span>
                <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
            </a>
            <div id="eventCenterSubmenu" class="submenu">
                <a href="<?= $base_url ?>/events/household/index.php" class="nav-link">
                    <i class="bi bi-house"></i>
                    <span>Household Events</span>
                </a>
                <a href="<?= $base_url ?>/events/assembly/index.php" class="nav-link">
                    <i class="bi bi-building"></i>
                    <span>Assembly Events</span>
                </a>
                <a href="<?= $base_url ?>/events/zone/index.php" class="nav-link">
                    <i class="bi bi-globe"></i>
                    <span>Zone Events</span>
                </a>
                <a href="<?= $base_url ?>/events/national/index.php" class="nav-link">
                    <i class="bi bi-flag"></i>
                    <span>National Events</span>
                </a>
                <a href="<?= $base_url ?>/events/event_types/index.php" class="nav-link">
                    <i class="bi bi-tags"></i>
                    <span>Event Types</span>
                </a>
            </div>
        </div>
        
        <a href="<?= $base_url ?>/finance/index.php" class="nav-link">
            <i class="bi bi-cash-stack"></i>
            <span>Finance</span>
        </a>
        
        <!-- Role Center Section -->
        <div class="nav-item-group">
            <a href="#" class="nav-link has-submenu" onclick="toggleSubmenu('roleCenterSubmenu')">
                <i class="bi bi-shield-lock"></i>
                <span>Role Center</span>
                <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
            </a>
            <div id="roleCenterSubmenu" class="submenu">
                <a href="<?= $base_url ?>/roles/index.php" class="nav-link">
                    <i class="bi bi-person-gear"></i>
                    <span>Manage Roles</span>
                </a>
                <a href="<?= $base_url ?>/roles/assignment/index.php" class="nav-link">
                    <i class="bi bi-person-check"></i>
                    <span>Role Assignment</span>
                </a>
                <a href="<?= $base_url ?>/roles/permissions.php" class="nav-link">
                    <i class="bi bi-shield-check"></i>
                    <span>Manage Permissions</span>
                </a>
                <a href="<?= $base_url ?>/roles/scopes/index.php" class="nav-link">
                    <i class="bi bi-diagram-2"></i>
                    <span>Manage Scopes</span>
                </a>
            </div>
        </div>
        
        <!-- Ministries Center Section -->
        <div class="nav-item-group">
            <a href="#" class="nav-link has-submenu" onclick="toggleSubmenu('ministriesCenterSubmenu')">
                <i class="bi bi-heart"></i>
                <span>Ministries Center</span>
                <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
            </a>
            <div id="ministriesCenterSubmenu" class="submenu">
                <a href="<?= $base_url ?>/specialized_ministries/index.php" class="nav-link">
                    <i class="bi bi-people-fill"></i>
                    <span>Manage Ministries</span>
                </a>
            </div>
        </div>
    </nav>
</div>

<style>
    .sidebar {
        width: 0;
        position: fixed;
        z-index: 1031;
        top: 60px;
        left: 0;
        height: calc(100% - 60px);
        background: linear-gradient(135deg, #1e90ff 0%, #0047ab 100%);
        overflow-x: hidden;
        transition: all 0.3s ease-in-out;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
    }

    .sidebar-nav {
        padding: 10px 0;
        overflow-y: auto;
        height: calc(100% - 60px);
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        border-left: 3px solid transparent;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-left-color: #4a90e2;
    }

    .nav-link i {
        font-size: 1.1rem;
        min-width: 25px;
        margin-right: 10px;
        color: white;
    }

    .nav-link span {
        flex: 1;
        color: white;
    }

    .has-submenu {
        cursor: pointer;
    }

    .submenu {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-10px);
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.3s ease,
                    transform 0.3s ease;
        background: rgba(30, 144, 255, 0.1);
    }

    [data-bs-theme="dark"] .submenu {
        background: rgba(0, 0, 0, 0.2);
    }

    .submenu.show {
        max-height: 1000px;
        opacity: 1;
        transform: translateY(0);
    }

    .submenu .nav-link {
        padding-left: 55px;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        transform: translateX(-10px);
        transition: transform 0.3s ease, color 0.2s ease, background-color 0.2s ease;
    }

    .submenu.show .nav-link {
        transform: translateX(0);
    }

    .nav-link:hover, .submenu .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border-left-color: #4a90e2;
    }

    .submenu-arrow {
        font-size: 0.8rem;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: white;
    }

    [data-bs-theme="dark"] .sidebar {
        background: linear-gradient(180deg, #0a2558 0%, #1a365d 100%);
    }
</style>

<script>
function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    const allSubmenus = document.querySelectorAll('.submenu');
    const parentLink = submenu.previousElementSibling;
    
    // Close other submenus at the same level
    const siblingSubmenus = submenu.parentElement.parentElement.querySelectorAll('.submenu');
    siblingSubmenus.forEach(sibling => {
        if (sibling !== submenu) {
            sibling.classList.remove('show');
            const siblingLink = sibling.previousElementSibling;
            if (siblingLink) {
                siblingLink.classList.remove('active');
                const arrow = siblingLink.querySelector('.submenu-arrow');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
    });
    
    // Toggle current submenu
    submenu.classList.toggle('show');
    if (parentLink) {
        parentLink.classList.toggle('active');
        const arrow = parentLink.querySelector('.submenu-arrow');
        if (arrow) {
            arrow.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    }
}

// Initialize sidebar state when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('mySidebar');
    const sidebarState = localStorage.getItem('sidebarOpen') === 'true';
    
    if (sidebarState) {
        sidebar.style.width = '250px';
    }
    
    // Add hover effect for submenu items
    document.querySelectorAll('.has-submenu').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(255, 255, 255, 0.1)';
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.background = '';
            }
        });
    });
});
</script>