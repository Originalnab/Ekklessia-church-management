<?php
// This script expects a member_id via POST and returns a modal with member details
include '../../../config/db.php';
$logFile = __DIR__ . '/debug.log';
function log_debug($type, $message) {
    global $logFile;
    $entry = '[' . date('Y-m-d H:i:s') . "] [$type] $message\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
}

if (!isset($_POST['member_id'])) {
    log_debug('ERROR', 'No member_id provided in AJAX request.');
    echo '<div class="modal-body"><div class="alert alert-danger">No member selected.</div></div>';
    exit;
}
$member_id = $_POST['member_id'];
try {
    $stmt = $pdo->prepare("
        SELECT m.*, a.name AS assembly_name, cf.function_name AS role_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN church_functions cf ON m.local_function_id = cf.function_id
        WHERE m.member_id = ?
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$member) {
        log_debug('ERROR', 'Member not found for member_id: ' . $member_id);
        echo '<div class="modal-body"><div class="alert alert-danger">Member not found.</div></div>';
        exit;
    }
    log_debug('SUCCESS', 'Loaded member details for member_id: ' . $member_id);
} catch (Exception $e) {
    log_debug('ERROR', 'Exception: ' . $e->getMessage());
    echo '<div class="modal-body"><div class="alert alert-danger">Error loading member details.</div></div>';
    exit;
}
?>
<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">Member Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/Ekklessia-church-management/app/resources/assets/images/<?= htmlspecialchars($member['profile_photo'] ?? 'default.jpg') ?>" alt="Profile Photo" class="profile-photo mb-3" style="width:120px;height:120px;border-radius:50%;object-fit:cover;">
            <h5><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h5>
            <p class="text-muted mb-1">Role: <?= htmlspecialchars($member['role_name'] ?? 'N/A') ?></p>
            <p class="text-muted">Assembly: <?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-8">
            <div class="mb-2"><strong>Contact:</strong> <?= htmlspecialchars($member['contact'] ?? 'N/A') ?></div>
            <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($member['email'] ?? 'N/A') ?></div>
            <div class="mb-2"><strong>Status:</strong> <?= htmlspecialchars($member['status'] ?? 'N/A') ?></div>
            <div class="mb-2"><strong>Created At:</strong> <?= htmlspecialchars($member['created_at'] ?? 'N/A') ?></div>
            <div class="mb-2"><strong>Updated At:</strong> <?= htmlspecialchars($member['updated_at'] ?? 'N/A') ?></div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
