<?php
$dateRange = $_GET['date_range'] ?? 'month';
$eventType = $_GET['event_type'] ?? 'all';
$scopeLevel = $_GET['scope_level'] ?? $visibilityScope['level'];
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="eventFilters" class="row g-3">
                    <div class="col-md-3">
                        <label for="dateRange" class="form-label">Date Range</label>
                        <select class="form-select" id="dateRange" name="date_range">
                            <option value="week" <?php echo $dateRange === 'week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo $dateRange === 'month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="quarter" <?php echo $dateRange === 'quarter' ? 'selected' : ''; ?>>This Quarter</option>
                            <option value="year" <?php echo $dateRange === 'year' ? 'selected' : ''; ?>>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="eventType" class="form-label">Event Type</label>
                        <select class="form-select" id="eventType" name="event_type">
                            <option value="all" <?php echo $eventType === 'all' ? 'selected' : ''; ?>>All Events</option>
                            <option value="service" <?php echo $eventType === 'service' ? 'selected' : ''; ?>>Services</option>
                            <option value="meeting" <?php echo $eventType === 'meeting' ? 'selected' : ''; ?>>Meetings</option>
                            <option value="activity" <?php echo $eventType === 'activity' ? 'selected' : ''; ?>>Activities</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="scopeLevel" class="form-label">View Level</label>
                        <select class="form-select" id="scopeLevel" name="scope_level">
                            <?php if ($visibilityScope['household_id']): ?>
                            <option value="household" <?php echo $scopeLevel === 'household' ? 'selected' : ''; ?>>Household</option>
                            <?php endif; ?>
                            <?php if ($visibilityScope['assembly_id']): ?>
                            <option value="assembly" <?php echo $scopeLevel === 'assembly' ? 'selected' : ''; ?>>Assembly</option>
                            <?php endif; ?>
                            <?php if ($visibilityScope['zone_id']): ?>
                            <option value="zone" <?php echo $scopeLevel === 'zone' ? 'selected' : ''; ?>>Zone</option>
                            <?php endif; ?>
                            <?php if ($visibilityScope['level'] === 'church'): ?>
                            <option value="church" <?php echo $scopeLevel === 'church' ? 'selected' : ''; ?>>All Church</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
