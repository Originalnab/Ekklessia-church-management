<!-- Bulk Upload Modal -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Changed to modal-xl for larger size -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bulkUploadModalLabel">Bulk Upload Members</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Upload CSV -->
                <div id="uploadStep">
                    <form id="bulkUploadForm">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Upload CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                            <small class="form-text text-muted">
                                Please upload a CSV file with the following columns: 
                                first_name, last_name, date_of_birth, gender, marital_status, contact, email, address, 
                                digital_address, occupation, employer, work_phone, highest_education_level, institution, 
                                year_graduated, status.
                            </small>
                        </div>
                        <div class="mb-3">
                            <a href="download_sample_csv.php" class="btn btn-outline-primary">
                                <i class="bi bi-download"></i> Download Sample CSV
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload and Preview</button>
                    </form>
                </div>

                <!-- Step 2: Preview Data -->
                <div id="previewStep" style="display: none;">
                    <h5>Preview Data</h5>
                    <div id="previewContent"></div>
                    <button id="backToUpload" class="btn btn-secondary mt-3">Back to Upload</button>
                </div>
            </div>
        </div>
    </div>
</div>