<?php
// Bootstrap modal for deleting a household event
?>
<!-- Delete Household Event Modal -->
<div class="modal fade" id="deleteHouseholdEventModal" tabindex="-1" aria-labelledby="deleteHouseholdEventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteHouseholdEventModalLabel">Delete Household Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this household event?</p>
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>
