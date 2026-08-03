<?php
/**
 * @var array|null $user
 * @var string $csrf
 * @var string $title
 * @var list<array{title: string, body: string}> $notifications
 */
$notifications = $notifications ?? [];
$notificationCount = count($notifications);
?>
<header class="admin-header">
    <div class="header-left">
        <button type="button" class="icon-btn sidebar-toggle" data-sidebar-toggle aria-label="Toggle sidebar">
            <span></span><span></span><span></span>
        </button>
        <a href="<?= e(url('/dashboard')) ?>" class="brand-mark">VaidTrack</a>
        <span class="header-divider"></span>
        <form class="header-search" action="<?= e(url('/doctors')) ?>" method="get" role="search">
            <label class="sr-only" for="global-search">Search</label>
            <input
                id="global-search"
                type="search"
                name="q"
                placeholder="Search doctors…"
                value="<?= e($_GET['q'] ?? '') ?>"
                data-global-search
            >
        </form>
    </div>

    <div class="header-right">
        <button type="button" class="icon-btn theme-toggle" data-theme-toggle aria-label="Toggle theme" title="Theme">
            <span class="theme-icon" aria-hidden="true"></span>
        </button>

        <div class="dropdown" data-dropdown>
            <button type="button" class="icon-btn" data-dropdown-trigger aria-expanded="false" aria-label="Notifications">
                <span class="bell-icon" aria-hidden="true"></span>
                <?php if ($notificationCount > 0): ?>
                    <span class="notif-dot"><?= (int) $notificationCount ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end" hidden>
                <p class="dropdown-title">Notifications</p>
                <?php foreach ($notifications as $note): ?>
                    <div class="dropdown-item">
                        <strong><?= e($note['title']) ?></strong>
                        <span><?= e($note['body']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dropdown" data-dropdown>
            <button type="button" class="user-menu-btn" data-dropdown-trigger aria-expanded="false">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr($user['name'] ?? 'A', 0, 1))) ?></span>
                <span class="user-meta">
                    <strong><?= e($user['name'] ?? 'Admin') ?></strong>
                    <small><?= e($user['role_name'] ?? '') ?></small>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end" hidden>
                <div class="dropdown-item">
                    <strong><?= e($user['email'] ?? '') ?></strong>
                    <span>Signed in</span>
                </div>
                <form method="post" action="<?= e(url('/logout')) ?>">
                    <?= $csrf ?? \App\Core\Csrf::field() ?>
                    <button type="submit" class="dropdown-action">Sign out</button>
                </form>
            </div>
        </div>
    </div>
</header>
