<?php
// Debug panel for event calendar view
// Only visible in development environment
$showDebugPanel = false; // Set to true to enable the debug panel

// Check if debug parameter is present
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    $showDebugPanel = true;
}

// Get debug log content if panel is enabled
$debugLogContent = [];
if ($showDebugPanel) {
    $logFile = dirname(__DIR__, 4) . '/debug.log';
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $debugLogContent = array_slice(explode("\n", $logContent), -50); // Get last 50 lines
    }
}
?>

<?php if ($showDebugPanel): ?>
<div class="card mt-4 debug-panel">
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="mb-0">Event Calendar Debug Panel</h5>
        <div>
            <button class="btn btn-sm btn-outline-light" id="refreshDebugBtn">Refresh</button>
            <button class="btn btn-sm btn-outline-light" id="clearDebugBtn">Clear Log</button>
            <button class="btn btn-sm btn-outline-light" id="toggleDebugBtn">Hide</button>
        </div>
    </div>
    <div class="card-body" id="debugContent">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">User Context</h6>
                    </div>
                    <div class="card-body">
                        <?php if (isset($userContext)): ?>
                            <pre><?php print_r($userContext); ?></pre>
                        <?php else: ?>
                            <p class="text-danger">User context not available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Current Filters</h6>
                    </div>
                    <div class="card-body">
                        <div id="currentFilters">Loading filters...</div>
                        <div class="mt-3">
                            <strong>Theme Preference:</strong> <span id="themePreference">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">Debug Log (<?= dirname(__DIR__, 4) ?>/debug.log)</h6>
            </div>
            <div class="card-body p-0">
                <pre class="m-0 p-3 bg-light" style="max-height: 400px; overflow-y: auto;" id="debugLog"><?php echo implode("\n", $debugLogContent); ?></pre>
            </div>
        </div>
    </div>
</div>

<script>
// Debug panel functionality
document.addEventListener('DOMContentLoaded', function() {
    // Display current filters
    function updateFilters() {
        const startDate = document.getElementById('startDate').value || 'Not set';
        const endDate = document.getElementById('endDate').value || 'Not set';
        const eventLevel = document.getElementById('eventLevel');
        const eventLevelText = eventLevel.options[eventLevel.selectedIndex].text;
        const eventType = document.getElementById('eventType');
        const eventTypeText = eventType.options[eventType.selectedIndex].text;
        
        document.getElementById('currentFilters').innerHTML = `
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Start Date
                    <span class="badge bg-primary rounded-pill">${startDate}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    End Date
                    <span class="badge bg-primary rounded-pill">${endDate}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Event Level
                    <span class="badge bg-primary rounded-pill">${eventLevelText}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Event Type
                    <span class="badge bg-primary rounded-pill">${eventTypeText}</span>
                </li>
            </ul>
        `;
    }
    
    // Refresh debug log
    function refreshDebugLog() {
        fetch('get_debug_log.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('debugLog').textContent = data;
                // Scroll to bottom
                const debugLog = document.getElementById('debugLog');
                debugLog.scrollTop = debugLog.scrollHeight;
            })
            .catch(error => {
                console.error('Error fetching debug log:', error);
            });
    }
    
    // Clear debug log
    document.getElementById('clearDebugBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the debug log?')) {
            fetch('clear_debug_log.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('debugLog').textContent = 'Debug log cleared.';
                    } else {
                        alert('Failed to clear debug log: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error clearing debug log:', error);
                });
        }
    });
    
    // Toggle debug panel
    document.getElementById('toggleDebugBtn').addEventListener('click', function() {
        const debugContent = document.getElementById('debugContent');
        const isVisible = debugContent.style.display !== 'none';
        
        debugContent.style.display = isVisible ? 'none' : 'block';
        this.textContent = isVisible ? 'Show' : 'Hide';
    });
    
    // Refresh debug log
    document.getElementById('refreshDebugBtn').addEventListener('click', refreshDebugLog);
      // Update filters when they change
    document.getElementById('eventFilters').addEventListener('change', updateFilters);
    
    // Update theme preference display
    function updateThemePreference() {
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const themePreferenceEl = document.getElementById('themePreference');
        
        if (savedTheme) {
            themePreferenceEl.innerHTML = `<span class="badge bg-${savedTheme === 'dark' ? 'dark' : 'light'} text-${savedTheme === 'dark' ? 'light' : 'dark'}">User selected: ${savedTheme}</span>`;
        } else {
            themePreferenceEl.innerHTML = `<span class="badge bg-secondary">System default: ${systemPrefersDark ? 'dark' : 'light'}</span>`;
        }
    }
    
    // Listen for theme changes
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            updateThemePreference();
        }
    });
    
    // Initial updates
    updateFilters();
    updateThemePreference();
    
    // Set up periodic refresh
    setInterval(refreshDebugLog, 5000); // Refresh every 5 seconds
});
</script>
<?php endif; ?>
