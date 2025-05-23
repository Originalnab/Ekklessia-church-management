<?php
$page_title = "Create Event";
include "../../../config/db.php";

// Fetch zones, assemblies, and households for dropdowns
$zones = $pdo->query("SELECT zone_id, name FROM zones")->fetchAll(PDO::FETCH_ASSOC);
$assemblies = $pdo->query("SELECT assembly_id, name FROM assemblies")->fetchAll(PDO::FETCH_ASSOC);
$households = $pdo->query("SELECT household_id, name FROM households")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_level = $_POST['event_level'] ?? '';
    $zone_id = $_POST['zone_id'] ?? null;
    $assembly_id = $_POST['assembly_id'] ?? null;
    $household_id = $_POST['household_id'] ?? null;
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurrence_type = $_POST['recurrence_type'] ?? null;
    $recurrence_interval = $_POST['recurrence_interval'] ?? 1;
    $recurrence_day_of_week = $_POST['recurrence_day_of_week'] ?? null;
    $recurrence_end_date = $_POST['recurrence_end_date'] ?? null;
    $start_date = $_POST['start_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? null;
    $location = $_POST['location'] ?? null;
    $created_by = 1; // Placeholder; using a default value since there's no login

    try {
        // Insert the event into the events table
        $stmt = $pdo->prepare("
            INSERT INTO events (title, description, event_level, zone_id, assembly_id, household_id, is_recurring, 
                recurrence_type, recurrence_interval, recurrence_day_of_week, recurrence_end_date, 
                start_date, start_time, end_time, location, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $description, $event_level, 
            $event_level === 'Zonal' ? $zone_id : null, 
            $event_level === 'Assembly' ? $assembly_id : null, 
            $event_level === 'Household' ? $household_id : null, 
            $is_recurring, $recurrence_type, $recurrence_interval, $recurrence_day_of_week, $recurrence_end_date, 
            $start_date, $start_time, $end_time, $location, $created_by
        ]);

        $event_id = $pdo->lastInsertId();

        // Create the first instance for a non-recurring event or the initial instance for a recurring event
        $stmt = $pdo->prepare("
            INSERT INTO event_instances (event_id, instance_date, start_time, end_time, location)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$event_id, $start_date, $start_time, $end_time, $location]);

        // For recurring events, generate future instances (e.g., for the next 6 months)
        if ($is_recurring) {
            $current_date = new DateTime($start_date);
            $end_date = $recurrence_end_date ? new DateTime($recurrence_end_date) : (clone $current_date)->modify('+6 months');
            
            while ($current_date <= $end_date) {
                $current_day_of_week = $current_date->format('l'); // e.g., "Wednesday"
                if ($recurrence_type === 'Weekly' && $current_day_of_week === $recurrence_day_of_week) {
                    $stmt = $pdo->prepare("
                        INSERT INTO event_instances (event_id, instance_date, start_time, end_time, location)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$event_id, $current_date->format('Y-m-d'), $start_time, $end_time, $location]);
                }
                $current_date->modify("+{$recurrence_interval} weeks");
            }
        }

        // Instead of session-based message, use a simple variable for feedback
        $success_message = "Event created successfully.";
    } catch (PDOException $e) {
        $error_message = "Error creating event: " . $e->getMessage();
    }
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<?php include "../../../includes/sidebar.php"; ?>

<main class="container flex-grow-1 py-2">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Event Creation Form -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Create Event</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="title" class="form-label">Event Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="event_level" class="form-label">Event Level</label>
                    <select class="form-control" id="event_level" name="event_level" onchange="toggleLevelFields()" required>
                        <option value="">Select Level</option>
                        <option value="National">National</option>
                        <option value="Zonal">Zonal</option>
                        <option value="Assembly">Assembly</option>
                        <option value="Household">Household</option>
                    </select>
                </div>
                <div class="mb-3" id="zone_field" style="display: none;">
                    <label for="zone_id" class="form-label">Zone</label>
                    <select class="form-control" id="zone_id" name="zone_id">
                        <option value="">Select Zone</option>
                        <?php foreach ($zones as $zone): ?>
                            <option value="<?= $zone['zone_id'] ?>"><?= htmlspecialchars($zone['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3" id="assembly_field" style="display: none;">
                    <label for="assembly_id" class="form-label">Assembly</label>
                    <select class="form-control" id="assembly_id" name="assembly_id">
                        <option value="">Select Assembly</option>
                        <?php foreach ($assemblies as $assembly): ?>
                            <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3" id="household_field" style="display: none;">
                    <label for="household_id" class="form-label">Household</label>
                    <select class="form-control" id="household_id" name="household_id">
                        <option value="">Select Household</option>
                        <?php foreach ($households as $household): ?>
                            <option value="<?= $household['household_id'] ?>"><?= htmlspecialchars($household['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="is_recurring" class="form-label">Is this a recurring event?</label>
                    <input type="checkbox" id="is_recurring" name="is_recurring" onchange="toggleRecurrenceFields()">
                </div>
                <div id="recurrence_fields" style="display: none;">
                    <div class="mb-3">
                        <label for="recurrence_type" class="form-label">Recurrence Type</label>
                        <select class="form-control" id="recurrence_type" name="recurrence_type">
                            <option value="Weekly">Weekly</option>
                            <!-- Add more options like Daily, Monthly if needed -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="recurrence_interval" class="form-label">Recurrence Interval (e.g., every X weeks)</label>
                        <input type="number" class="form-control" id="recurrence_interval" name="recurrence_interval" value="1" min="1">
                    </div>
                    <div class="mb-3">
                        <label for="recurrence_day_of_week" class="form-label">Day of the Week</label>
                        <select class="form-control" id="recurrence_day_of_week" name="recurrence_day_of_week">
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="recurrence_end_date" class="form-label">Recurrence End Date (optional)</label>
                        <input type="date" class="form-control" id="recurrence_end_date" name="recurrence_end_date">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                </div>
                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time (optional)</label>
                    <input type="time" class="form-control" id="end_time" name="end_time">
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location">
                </div>
                <button type="submit" class="btn btn-primary">Create Event</button>
            </form>
        </div>
    </div>
</main>

<script>
    function toggleLevelFields() {
        const eventLevel = document.getElementById('event_level').value;
        document.getElementById('zone_field').style.display = eventLevel === 'Zonal' ? 'block' : 'none';
        document.getElementById('assembly_field').style.display = eventLevel === 'Assembly' ? 'block' : 'none';
        document.getElementById('household_field').style.display = eventLevel === 'Household' ? 'block' : 'none';
    }

    function toggleRecurrenceFields() {
        const isRecurring = document.getElementById('is_recurring').checked;
        document.getElementById('recurrence_fields').style.display = isRecurring ? 'block' : 'none';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>