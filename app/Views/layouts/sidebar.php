<?php

/**
 * @var string $menu
 */

 $sessionAvatar = trim((string) (session('avatar') ?? 'user-1.jpg'));

$menuData = is_array($menu ?? null) ? $menu : [];

$resolveHref = static function (?string $link): string {
    $link = trim((string) $link);
    if ($link === '' || $link === '#') {
        return 'javascript:void(0)';
    } else {
        return base_url($link);
    }
};

$sortedMenu = $menuData;
usort($sortedMenu, static function (array $a, array $b): int {
    $cmp = (int) ($a['urutan'] ?? 0) <=> (int) ($b['urutan'] ?? 0);
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcmp((string) ($a['menu_name'] ?? ''), (string) ($b['menu_name'] ?? ''));
});

$topMenus = [];
$childByHeader = [];
foreach ($sortedMenu as $item) {
    $headerMenu = trim((string) ($item['header_menu'] ?? ''));
    if ($headerMenu === '') {
        $topMenus[] = $item;
        continue;
    }
    $childByHeader[$headerMenu][] = $item;
}
?>
<aside class="left-sidebar with-vertical">
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <div class=" d-flex align-items-center justify-content-center">
                <img src="<?= base_url(APP_LOGO_PATH); ?>" style="height: 40px;" alt="<?= esc(APP_NAME); ?>" />
                <h5 class="m-1 text-primary fw-bolder"><?= session("toko_nama") ?></h5>
            </div>
            <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
                <i class="ti ti-x"></i>
            </a>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul id="sidebarnav">
                <?php foreach ($topMenus as $topMenu): ?>
                    <?php
                    $menuId = (string) ($topMenu['menu_id'] ?? '');
                    $children = $childByHeader[$menuId] ?? [];
                    $has_arrow = $topMenu['link'] == "#" ? "has-arrow" : "";
                    ?>
                    <li class="sidebar-item">
                        <a class="sidebar-link <?= $has_arrow ?>" href="<?= esc($resolveHref($topMenu['link'] ?? '#')); ?>" aria-expanded="false">
                            <span class="d-flex">
                                <i class="<?= esc((string) ($topMenu['icon'] ?? 'nav-icon fas fa-circle')); ?>"></i>
                            </span>
                            <span class="hide-menu"><?= esc((string) ($topMenu['menu_name'] ?? 'Menu')); ?></span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <?php foreach ($children as $child): ?>
                                <li class="sidebar-item">
                                    <a href="<?= esc($resolveHref($child['link'] ?? '#')); ?>" class="sidebar-link">
                                        <span class="d-flex">
                                            <i style="font-size: medium;" class="<?= esc((string) ($child['icon'] ?? 'nav-icon fas fa-circle')); ?>"></i>
                                        </span>
                                        <span class="hide-menu"><?= esc((string) ($child['menu_name'] ?? 'Menu')); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>

        </nav>
        <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
            <div class="hstack gap-3">
                <div class="john-img">
                    <img src="<?= base_url('/assets/images/profile/' . $sessionAvatar) ?>" class="rounded-circle sidebar-avatar" width="40" height="40" alt="modernize-img" />
                </div>
                <div class="john-title">
                    <h6 class="mb-0 fs-4 fw-semibold"><?= strtok(session("fullname"), " ") ?></h6>
                    <span class="fs-2"><?= session("level_name") ?></span>
                </div>
                <a href="<?= base_url("logout") ?>" class="border-0 bg-transparent text-primary ms-auto" tabindex="0" type="button" aria-label="logout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="logout">
                    <i class="ti ti-power fs-6"></i>
                </a>
            </div>
        </div>
    </div>
</aside>
