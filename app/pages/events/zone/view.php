<?php
// View a single zone event by ID (AJAX endpoint, returns HTML for modal)
session_start();
include '../../../config/db.php';
if (!isset($_SESSION['member_id'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Not authorized</div>';
    exit;
}
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$event_id) {
    echo '<div class="alert alert-danger">Invalid event ID</div>';
    exit;
}
$stmt = $pdo->prepare("SELECT e.*, et.name as event_type, COALESCE(z.name, 'Not Assigned') as zone_name FROM events e LEFT JOIN event_types et ON e.event_type_id = et.event_type_id LEFT JOIN zones z ON e.zone_id = z.zone_id WHERE e.event_id = ? AND e.level = 3 LIMIT 1");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$event) {
    echo '<div class="alert alert-warning">Event not found</div>';
    exit;
}
function formatDateWords($dateStr) {
    $date = strtotime($dateStr);
    if (!$date) return htmlspecialchars($dateStr);
    return date('F j, Y . g:i a', $date);
}
?>
<div class="card shadow-sm border-primary">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['title']) ?></h5>
  </div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-4">Event Type</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($event['event_type']) ?></dd>
      <dt class="col-sm-4">Zone</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($event['zone_name']) ?></dd>
      <dt class="col-sm-4">Start Date & Time</dt>
      <dd class="col-sm-8"><?= formatDateWords($event['start_date']) ?></dd>
      <dt class="col-sm-4">End Date & Time</dt>
      <dd class="col-sm-8"><?= formatDateWords($event['end_date']) ?></dd>
      <dt class="col-sm-4">Recurring</dt>
      <dd class="col-sm-8"><?= $event['is_recurring'] ? 'Yes' : 'No' ?></dd>
      <dt class="col-sm-4">Description</dt>
      <dd class="col-sm-8"><?= nl2br(htmlspecialchars($event['description'])) ?></dd>
    </dl>
  </div>
</div>
