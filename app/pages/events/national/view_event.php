<?php
session_start();
header('Content-Type: application/json');
include '../../../config/db.php';

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$render_html = isset($_GET['render']) && $_GET['render'] === 'html';

try {
    // Get event details with event type information
    $sql = "SELECT 
                e.event_id,
                e.title,
                e.description,
                e.start_date,
                e.end_date,
                e.location,
                e.is_recurring,
                e.frequency,
                e.created_at,
                e.updated_at,
                et.name as event_type_name,
                et.description as event_type_description,
                creator.first_name as creator_first_name,
                creator.last_name as creator_last_name,
                updater.first_name as updater_first_name,
                updater.last_name as updater_last_name
            FROM events e
            LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
            LEFT JOIN members creator ON e.created_by = creator.member_id
            LEFT JOIN members updater ON e.updated_by = updater.member_id
            WHERE e.event_id = ? AND e.level = 4";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        if ($render_html) {
            echo '<div class="alert alert-danger">Event not found.</div>';
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            exit;
        }
    }
    
    // Format dates for display
    $start_date = new DateTime($event['start_date']);
    $end_date = new DateTime($event['end_date']);
    
    $event['formatted_start_date'] = $start_date->format('F j, Y');
    $event['formatted_start_time'] = $start_date->format('g:i A');
    $event['formatted_end_date'] = $end_date->format('F j, Y');
    $event['formatted_end_time'] = $end_date->format('g:i A');
    $event['formatted_start_datetime'] = $start_date->format('F j, Y \a\t g:i A');
    $event['formatted_end_datetime'] = $end_date->format('F j, Y \a\t g:i A');
    
    // Calculate duration
    $duration = $start_date->diff($end_date);
    $event['duration'] = '';
    if ($duration->days > 0) {
        $event['duration'] .= $duration->days . ' day' . ($duration->days > 1 ? 's' : '') . ' ';
    }
    if ($duration->h > 0) {
        $event['duration'] .= $duration->h . ' hour' . ($duration->h > 1 ? 's' : '') . ' ';
    }
    if ($duration->i > 0) {
        $event['duration'] .= $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
    }
    $event['duration'] = trim($event['duration']) ?: 'Less than a minute';
    
    // Format created and updated dates
    if ($event['created_at']) {
        $created_date = new DateTime($event['created_at']);
        $event['formatted_created_at'] = $created_date->format('F j, Y \a\t g:i A');
    }
    
    if ($event['updated_at']) {
        $updated_date = new DateTime($event['updated_at']);
        $event['formatted_updated_at'] = $updated_date->format('F j, Y \a\t g:i A');
    }
    
    // Format creator and updater names
    $event['creator_name'] = trim(($event['creator_first_name'] ?? '') . ' ' . ($event['creator_last_name'] ?? ''));
    $event['updater_name'] = trim(($event['updater_first_name'] ?? '') . ' ' . ($event['updater_last_name'] ?? ''));
    
    if (empty($event['creator_name'])) {
        $event['creator_name'] = 'Unknown';
    }
    if (empty($event['updater_name'])) {
        $event['updater_name'] = 'Unknown';
    }
    
    if ($render_html) {
        ?>
        <div class="card border-0">
            <div class="card-body">
                <h4 class="card-title text-primary mb-3">
                    <i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['title'] ?? 'Untitled Event') ?>
                    <?php if ($event['is_recurring']): ?>
                        <span class="badge bg-info ms-2"><i class="bi bi-arrow-repeat"></i> Recurring (<?= htmlspecialchars($event['frequency'] ?? 'Unknown') ?>)</span>
                    <?php endif; ?>
                </h4>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong><i class="bi bi-tag"></i> Event Type:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-primary"><?= htmlspecialchars($event['event_type_name'] ?? 'Unknown') ?></span>
                        <?php if (!empty($event['event_type_description'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($event['event_type_description']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong><i class="bi bi-calendar-check"></i> Start:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['formatted_start_datetime']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong><i class="bi bi-calendar-x"></i> End:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['formatted_end_datetime']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong><i class="bi bi-clock"></i> Duration:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['duration']) ?></div>
                </div>
                <?php if (!empty($event['location'])): ?>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong><i class="bi bi-geo-alt"></i> Location:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['location']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($event['description'])): ?>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong><i class="bi bi-card-text"></i> Description:</strong></div>
                    <div class="col-sm-9"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
                </div>
                <?php endif; ?>
                <hr class="my-4">
                <div class="row mb-2">
                    <div class="col-sm-3"><strong><i class="bi bi-person-plus"></i> Created By:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['creator_name']) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3"><strong><i class="bi bi-calendar-plus"></i> Created:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['formatted_created_at'] ?? 'Unknown') ?></div>
                </div>
                <?php if (!empty($event['formatted_updated_at'])): ?>
                <div class="row mb-2">
                    <div class="col-sm-3"><strong><i class="bi bi-person-check"></i> Last Updated By:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['updater_name']) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-3"><strong><i class="bi bi-calendar-check"></i> Last Updated:</strong></div>
                    <div class="col-sm-9"><?= htmlspecialchars($event['formatted_updated_at']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'event' => $event
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in view_event.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in view_event.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching event details']);
}
?>
