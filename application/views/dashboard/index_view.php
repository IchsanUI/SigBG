<h1 class="page-title mb-4">Dashboard</h1>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="section-title">Welcome</h2>
                <p class="text-secondary mb-0">
                    Halo, <strong><?= htmlspecialchars($username ?? 'admin', ENT_QUOTES, 'UTF-8'); ?></strong>.
                    Anda sudah login ke SigBG Digital Signage.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="section-title">Quick Actions</h2>
                <a href="<?= base_url('player'); ?>" target="_blank" class="btn btn-accent btn-sm">
                    <i class="bi bi-play-circle"></i> Buka Player
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="section-title">Status</h2>
                <p class="mb-1 small-text text-secondary">Sistem aktif</p>
                <span class="badge badge-active">Online</span>
            </div>
        </div>
    </div>
</div>
