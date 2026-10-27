<?php
/*
 * The "Import from CSV" box of a list page. Set $importType (batches | teachers | students) before including it.
 * It has its own Alpine component (Sobuj_csvImport in import.js), so it works inside any page.
 * The page opens it with the "Import CSV" button (the toggle-import event).
 */

$importLabel = ['batches' => 'batches', 'teachers' => 'teachers', 'students' => 'students'][$importType];
$isAdminUser = (App\Controllers\Auth::currentUser()['role'] ?? '') === 'admin';
?>

<div @toggle-import.window="toggle()" x-data="Sobuj_csvImport('<?= url('api/import.php') ?>', '<?= $importType ?>')">

    <div class="card border-light shadow-sm mb-3" x-cloak x-show="open">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <p class="mb-0">
                    Add many <?= $importLabel ?> at once from a CSV file. In Excel, use <strong>File &gt; Save As &gt; CSV (Comma delimited)</strong>.
                </p>
                <button type="button" class="btn btn-light flex-shrink-0" @click="showInfo = !showInfo">
                    <i class="bi bi-info-circle me-1"></i> Columns &amp; values
                </button>
            </div>

            <div class="text-muted small" x-show="loadingRef">Loading...</div>

            <!-- the columns and the values that exist -->
            <div class="border rounded p-3 mb-3 bg-light" x-cloak x-show="showInfo && ref">
                <div class="fw-semibold mb-2">Columns of the file</div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr><th>Column</th><th>Needed</th><th>What to write</th></tr>
                        </thead>
                        <tbody>
                            <template x-for="column in (ref ? ref.columns : [])" :key="column.name">
                                <tr>
                                    <td class="fw-semibold" x-text="column.name"></td>
                                    <td><span class="badge-table" :class="column.required ? 'failed' : 'pending'" x-text="column.required ? 'Required' : 'Optional'"></span></td>
                                    <td style="white-space: normal;" x-text="column.help"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="fw-semibold mt-3 mb-1">Values that exist now</div>
                <div class="small">
                    <div class="mb-1"><span class="text-muted">Departments (code):</span>
                        <template x-for="department in (ref ? ref.departments : [])" :key="department.code">
                            <span class="badge-table pending me-1" x-text="department.code + ' - ' + department.name"></span>
                        </template>
                    </div>
                    <?php if ($importType === 'batches'): ?>
                        <div class="mb-1"><span class="text-muted">Admission sessions (name):</span>
                            <template x-for="session in (ref ? ref.sessions : [])" :key="session.name">
                                <span class="badge-table pending me-1" x-text="session.name"></span>
                            </template>
                            <span class="text-danger" x-show="ref && ref.sessions.length === 0">none yet</span>
                        </div>
                        <div class="mb-1"><span class="text-muted">Shift:</span> Regular, Evening</div>
                    <?php endif; ?>
                    <?php if ($importType === 'students'): ?>
                        <div class="mb-1"><span class="text-muted">Batches (code):</span>
                            <template x-for="batch in (ref ? ref.batches : [])" :key="batch.code">
                                <span class="badge-table pending me-1" x-text="batch.code + ' (' + batch.department + ')'"></span>
                            </template>
                            <span class="text-danger" x-show="ref && ref.batches.length === 0">none yet</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($importType === 'teachers'): ?>
                        <div class="mb-1"><span class="text-muted">Designations:</span>
                            <template x-for="designation in (ref ? ref.designations : [])" :key="designation">
                                <span class="badge-table pending me-1" x-text="designation"></span>
                            </template>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- step 1: pick the values -->
            <div class="fw-semibold mb-2">1. Pick the values for your file</div>

            <div class="alert alert-warning py-2" x-cloak x-show="noSession">
                There is no academic session yet. A batch needs an admission session, so
                <?php if ($isAdminUser): ?>
                    <a href="<?= url('admin/university/sessions/index.php') ?>" class="alert-link">add an academic session first</a>,
                <?php else: ?>
                    ask the administrator to add an academic session first,
                <?php endif; ?>
                then come back here.
            </div>

            <div class="alert alert-warning py-2" x-cloak x-show="noBatch">
                This department has no batch yet. Students need a batch, so
                <a href="<?= url('admin/university/batches/index.php') ?>" class="alert-link">add the batch first</a>
                (you can also import batches), then come back here.
            </div>

            <div class="row g-3 mb-3" x-show="ref">
                <div class="col-12 col-md-4" x-show="ref && ref.departments.length !== 1">
                    <label class="form-label-custom">Department <span class="text-danger">*</span></label>
                    <select class="form-select-custom" x-model="picks.department" @change="departmentChanged()">
                        <option value="">Select department</option>
                        <template x-for="department in (ref ? ref.departments : [])" :key="department.code">
                            <option :value="department.code" x-text="department.code + ' - ' + department.name"></option>
                        </template>
                    </select>
                </div>

                <?php if ($importType === 'batches'): ?>
                    <div class="col-12 col-md-4">
                        <label class="form-label-custom">Admission session <span class="text-danger">*</span></label>
                        <select class="form-select-custom" x-model="picks.session" :disabled="noSession">
                            <option value="">Select session</option>
                            <template x-for="session in (ref ? ref.sessions : [])" :key="session.name">
                                <option :value="session.name" x-text="session.name"></option>
                            </template>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($importType === 'students'): ?>
                    <div class="col-12 col-md-4">
                        <label class="form-label-custom">Batch <span class="text-danger">*</span></label>
                        <select class="form-select-custom" x-model="picks.batch" :disabled="picks.department === '' || noBatch">
                            <option value="">Select batch</option>
                            <template x-for="batch in batchChoices" :key="batch.code">
                                <option :value="batch.code" x-text="batch.code + ' - ' + batch.name"></option>
                            </template>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <!-- step 2: the sample -->
            <div class="fw-semibold mb-2">2. Download the sample and fill it</div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <a :href="canDownload ? sampleUrl : '#'" class="btn btn-outline-primary" :class="canDownload ? '' : 'disabled'"
                    @click="if (!canDownload) $event.preventDefault()">
                    <i class="bi bi-download me-1"></i> Download sample CSV
                </a>
                <span class="small text-muted" x-show="!canDownload && ref">Pick the values above first. The sample will already hold them.</span>
                <span class="small text-muted" x-show="canDownload">The two dummy rows use your picked values. Replace the names and keep the first line.</span>
            </div>

            <!-- step 3: the upload -->
            <div class="fw-semibold mb-2">3. Upload the file</div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="file" class="form-control-custom" style="max-width: 320px;" accept=".csv,text/csv" x-ref="file" @change="result = null">

                <button type="button" class="btn btn-primary" :disabled="uploading" @click="upload()">
                    <i class="bi bi-upload me-1"></i> <span x-text="uploading ? 'Uploading...' : 'Upload'"></span>
                </button>
            </div>
            <div class="small text-muted mt-1">Rows with a problem are skipped and listed below; the other rows are saved.</div>

            <div class="mt-3" x-cloak x-show="result">
                <div class="alert py-2 mb-2" :class="result && result.success ? (result.failed ? 'alert-warning' : 'alert-success') : 'alert-danger'">
                    <span x-text="result ? result.message : ''"></span>
                    <a href="#" class="alert-link ms-2" x-show="result && result.created > 0" @click.prevent="window.location.reload()">Reload the list</a>
                </div>

                <div class="table-responsive" x-show="result && result.errors && result.errors.length > 0">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Name</th>
                                <th>Problem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in (result ? result.errors : [])" :key="item.line">
                                <tr>
                                    <td x-text="item.line"></td>
                                    <td x-text="item.name || '-'"></td>
                                    <td style="white-space: normal;">
                                        <template x-for="message in item.messages">
                                            <div class="text-danger small" x-text="message"></div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
