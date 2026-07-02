<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->security->get_csrf_hash(); ?>">
    <title><?= isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' — ' : ''; ?>SigBG Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css'); ?>">
</head>
<body class="admin-body">
    <nav class="navbar navbar-expand-md" style="background: var(--primary-navy);">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-semibold" href="<?= base_url('dashboard'); ?>">
                SigBG
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white-50 small-text me-3">
                    <?= htmlspecialchars($this->session->userdata('admin_username') ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <a href="<?= base_url('auth/logout'); ?>" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <aside class="sidebar-nav" style="background: var(--primary-navy); width: 240px; min-height: calc(100vh - 56px);">
            <ul class="nav flex-column pt-3">
                <li class="nav-item">
                    <a class="nav-link <?= ($this->router->class === 'dashboard' ? 'active' : ''); ?>" href="<?= base_url('dashboard'); ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($this->router->class === 'media' ? 'active' : ''); ?>" href="<?= base_url('media'); ?>">
                        <i class="bi bi-images"></i> Media
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($this->router->class === 'playlist' ? 'active' : ''); ?>" href="<?= base_url('playlist'); ?>">
                        <i class="bi bi-list-ul"></i> Playlist
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($this->router->class === 'schedule' ? 'active' : ''); ?>" href="<?= base_url('schedule'); ?>">
                        <i class="bi bi-calendar-week"></i> Jadwal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($this->router->class === 'ticker' ? 'active' : ''); ?>" href="<?= base_url('ticker'); ?>">
                        <i class="bi bi-textarea-resize"></i> Running Text
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('player'); ?>" target="_blank">
                        <i class="bi bi-play-circle"></i> Preview Player
                    </a>
                </li>
            </ul>
        </aside>

        <main class="flex-grow-1 p-4" style="background: var(--surface-bg);">
            <?php $this->load->view($content_view, isset($content_data) ? $content_data : []); ?>
        </main>
    </div>

    <!-- CSRF hidden input for AJAX requests -->
    <form id="csrfForm" style="display: none;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    </form>

    <script src="<?= base_url('assets/vendor/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/admin.js'); ?>"></script>
</body>
</html>
