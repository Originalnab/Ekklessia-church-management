<?php
// Bootstrap modal for adding a new household event
?>
<!-- Add Household Event Modal -->
<div class="modal fade" id="addHouseholdEventModal" tabindex="-1" aria-labelledby="addHouseholdEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addHouseholdEventModalLabel">Add New Household Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addHouseholdEventForm">          <!-- Form fields -->
          <div class="mb-3">
            <label for="eventTitle" class="form-label">Event Title</label>
            <input type="text" class="form-control" id="eventTitle" name="title" required>
          </div>
          
          <!-- Add any other form fields needed -->
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Event</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
