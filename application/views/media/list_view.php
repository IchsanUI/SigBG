<h1 class="page-title mb-4">Manajemen Media</h1>

<!-- Upload Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="section-title">Upload Media Baru</h2>
        <p class="text-secondary small-text mb-3">
            Format didukung:
            <strong>Gambar</strong> (jpg, jpeg, png, gif, webp) - durasi minimal 3 detik &nbsp;|&nbsp;
            <strong>Video</strong> (mp4, avi, mov, mkv, webm)
            &nbsp;|&nbsp;Ukuran maksimal 100 MB
        </p>

        <form id="mediaUploadForm" enctype="multipart/form-data" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Pilih File</label>
                <input type="file" class="form-control" id="userfile" name="userfile" accept="image/*,video/*" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Judul (opsional)</label>
                <input type="text" class="form-control" id="uploadTitle" name="title" placeholder="Kosongkan untuk pakai nama file" maxlength="150">
            </div>
            <div class="col-md-3" id="durationGroup" style="display: none;">
                <label class="form-label">Durasi Tayang (detik)</label>
                <input type="number" class="form-control" id="uploadDuration" name="duration" value="5" min="3" max="600" step="1">
                <small class="text-secondary">Min. 3 detik</small>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-accent w-100" id="uploadBtn">
                    <i class="bi bi-cloud-upload"></i> Upload
                </button>
            </div>
        </form>

        <!-- Upload progress -->
        <div id="uploadProgress" class="progress mt-3" style="display: none; height: 6px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <div id="uploadStatus" class="small-text mt-2"></div>
    </div>
</div>

<!-- Media Library -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title mb-0">Library Media</h2>
            <span class="text-secondary small-text"><?= count($media_list); ?> item</span>
        </div>

        <?php if (empty($media_list)): ?>
            <div class="alert alert-info mb-0">
                Belum ada media. Upload file pertama Anda di form di atas.
            </div>
        <?php else: ?>
            <div class="row g-3" id="mediaGrid">
                <?php foreach ($media_list as $m): ?>
                    <div class="col-md-3 col-sm-6 media-item" data-id="<?= $m->id; ?>" data-type="<?= $m->type; ?>">
                        <div class="media-card">
                            <div class="media-thumb">
                                <?php if ($m->type === 'image'): ?>
                                    <img src="<?= base_url('media/serve/' . $m->id); ?>" alt="<?= htmlspecialchars($m->title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <?php else: ?>
                                    <video muted preload="metadata">
                                        <source src="<?= base_url('media/serve/' . $m->id); ?>" type="<?= $m->mime_type; ?>">
                                    </video>
                                    <div class="video-badge"><i class="bi bi-play-circle-fill"></i></div>
                                <?php endif; ?>
                                <div class="media-actions">
                                    <button class="btn btn-sm btn-light" onclick="previewMedia(<?= $m->id; ?>, '<?= $m->type; ?>')" title="Preview">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light" onclick="editMedia(<?= $m->id; ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteMedia(<?= $m->id; ?>)" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="media-info">
                                <div class="media-title" title="<?= htmlspecialchars($m->title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($m->title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="media-meta">
                                    <?php if ($m->type === 'image'): ?>
                                        <span><i class="bi bi-clock"></i> <?= intval($m->duration); ?>s</span>
                                    <?php else: ?>
                                        <span><i class="bi bi-film"></i> Video</span>
                                        <?php if ($m->duration): ?>
                                            <span><i class="bi bi-clock"></i> <?= gmdate('i:s', $m->duration); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <span><i class="bi bi-hdd"></i> <?= round($m->file_size / 1024 / 1024, 1); ?> MB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="previewBody">
                <!-- preview content loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" id="editTitle" name="title" maxlength="150" required>
                    </div>
                    <div class="mb-3" id="editDurationGroup">
                        <label class="form-label">Durasi Tayang (detik)</label>
                        <input type="number" class="form-control" id="editDuration" name="duration" min="3" max="600" step="1">
                        <small class="text-secondary">Hanya untuk gambar. Video otomatis mengikuti panjang video.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-accent">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <h4 class="mt-3 mb-2">Hapus Media?</h4>
                <p class="text-secondary mb-0">Media "<span id="deleteTitle"></span>" akan ditandai sebagai terhapus.</p>
                <p class="text-secondary small-text mb-3">Anda bisa restore nanti atau hapus permanen dengan centang di bawah.</p>
                <input type="hidden" id="deleteId">
                <div class="form-check text-start">
                    <input class="form-check-input" type="checkbox" id="deleteFile">
                    <label class="form-check-label" for="deleteFile">
                        Hapus juga file dari storage
                    </label>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide duration field based on file type
document.getElementById('userfile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) {
        document.getElementById('durationGroup').style.display = 'none';
        return;
    }
    if (file.type.startsWith('image/')) {
        document.getElementById('durationGroup').style.display = 'block';
    } else {
        document.getElementById('durationGroup').style.display = 'none';
    }
});

// CSRF token retrieval helper
const CSRF_NAME = 'csrf_test_name';
function getCsrfName() { return CSRF_NAME; }
function getCsrfValue() {
    // First try hidden form input
    const el = document.querySelector('#csrfForm input[name="' + CSRF_NAME + '"]');
    if (el && el.value) return el.value;
    // Fallback to meta
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    return '';
}

// Upload handler
document.getElementById('mediaUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append(CSRF_NAME, getCsrfValue());

    const btn = document.getElementById('uploadBtn');
    const progress = document.getElementById('uploadProgress');
    const status = document.getElementById('uploadStatus');
    const progressBar = progress.querySelector('.progress-bar');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengupload...';
    progress.style.display = 'block';
    progressBar.style.width = '0%';
    status.textContent = '';
    status.className = 'small-text mt-2';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= base_url('media/upload'); ?>', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = pct + '%';
            status.textContent = 'Mengupload... ' + pct + '%';
        }
    };

    xhr.onload = function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-upload"></i> Upload';
        progress.style.display = 'none';

        let resp;
        try { resp = JSON.parse(xhr.responseText); } catch (_) { resp = { error: 'Invalid response' }; }

        // Refresh CSRF token
        if (resp.csrf_token) refreshCsrf(resp.csrf_token);

        if (xhr.status === 200 && resp.success) {
            status.className = 'small-text mt-2 text-success';
            status.textContent = 'Berhasil diupload: ' + (resp.title || resp.file_name);
            form.reset();
            document.getElementById('durationGroup').style.display = 'none';
            setTimeout(() => window.location.reload(), 800);
        } else {
            status.className = 'small-text mt-2 text-danger';
            status.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + (resp.error || 'Upload gagal.');
        }
    };

    xhr.onerror = function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-upload"></i> Upload';
        progress.style.display = 'none';
        status.className = 'small-text mt-2 text-danger';
        status.textContent = 'Koneksi gagal.';
    };

    xhr.send(formData);
});

// Refresh CSRF in hidden form
function refreshCsrf(token) {
    const el = document.querySelector('#csrfForm input[name="' + CSRF_NAME + '"]');
    if (el) el.value = token;
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) meta.setAttribute('content', token);
}

// Preview
function previewMedia(id, type) {
    const url = '<?= base_url('media/serve/'); ?>' + id;
    const titleEl = document.getElementById('previewTitle');
    const body = document.getElementById('previewBody');

    titleEl.textContent = 'Preview ' + (type === 'image' ? 'Gambar' : 'Video');

    if (type === 'image') {
        body.innerHTML = '<img src="' + url + '" class="img-fluid" style="max-height: 70vh;">';
    } else {
        body.innerHTML = '<video src="' + url + '" controls autoplay style="max-height: 70vh; max-width: 100%;"></video>';
    }

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Edit
function editMedia(id) {
    // Fetch media data via inline
    const items = document.querySelectorAll('.media-item');
    let media = null;
    items.forEach(function(el) {
        if (parseInt(el.dataset.id) === id) {
            const title = el.querySelector('.media-title').textContent.trim();
            const isVideo = el.dataset.type === 'video';
            let duration = null;
            if (!isVideo) {
                const durEl = el.querySelector('.media-meta');
                const match = durEl.textContent.match(/(\d+)s/);
                if (match) duration = parseInt(match[1]);
            }
            media = { id: id, title: title, duration: duration, type: el.dataset.type };
        }
    });

    if (!media) return;

    document.getElementById('editId').value = media.id;
    document.getElementById('editTitle').value = media.title;

    const durGroup = document.getElementById('editDurationGroup');
    const durInput = document.getElementById('editDuration');
    if (media.type === 'image') {
        durGroup.style.display = 'block';
        durInput.value = media.duration || 5;
    } else {
        durGroup.style.display = 'none';
    }

    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const title = document.getElementById('editTitle').value;
    const duration = document.getElementById('editDuration').value;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= base_url('media/edit/'); ?>' + id, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    const fd = new URLSearchParams();
    fd.append(CSRF_NAME, getCsrfValue());
    fd.append('title', title);
    fd.append('duration', duration);

    xhr.onload = function() {
        let resp;
        try { resp = JSON.parse(xhr.responseText); } catch (_) { resp = {}; }
        // Refresh CSRF token
        if (resp.csrf_token) refreshCsrf(resp.csrf_token);
        if (xhr.status === 200 && resp.success) {
            window.location.reload();
        } else {
            alert(resp.error || 'Gagal menyimpan.');
        }
    };
    xhr.send(fd.toString());
});

// Delete
function deleteMedia(id) {
    const items = document.querySelectorAll('.media-item');
    items.forEach(function(el) {
        if (parseInt(el.dataset.id) === id) {
            document.getElementById('deleteTitle').textContent =
                el.querySelector('.media-title').textContent.trim();
        }
    });
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteFile').checked = false;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function confirmDelete() {
    const id = document.getElementById('deleteId').value;
    const removeFile = document.getElementById('deleteFile').checked ? 1 : 0;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= base_url('media/delete/'); ?>' + id, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    const fd = new URLSearchParams();
    fd.append(CSRF_NAME, getCsrfValue());
    fd.append('remove_file', removeFile);

    xhr.onload = function() {
        let resp;
        try { resp = JSON.parse(xhr.responseText); } catch (_) { resp = {}; }
        // Refresh CSRF token
        if (resp.csrf_token) refreshCsrf(resp.csrf_token);
        if (xhr.status === 200 && resp.success) {
            window.location.reload();
        } else {
            alert(resp.error || 'Gagal menghapus.');
        }
    };
    xhr.send(fd.toString());
}
</script>