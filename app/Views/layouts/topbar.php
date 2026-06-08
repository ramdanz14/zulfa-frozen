<?php $sessionAvatar = trim((string) (session('avatar') ?? 'user-1.jpg')); ?>
<header class="topbar">
    <div class="with-vertical">
        <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
                <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="nav-item nav-icon-hover-bg rounded-circle d-none d-lg-flex">
                    <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#menuSearchModal">
                        <i class="ti ti-search"></i>
                    </a>
                </li>
            </ul>
            <div class="d-block d-lg-none py-4 d-flex align-items-center justify-content-center">
                <img src="<?= base_url(); ?>/assets/images/Zulfa.png" style="height: 40px;" alt="Logo-Dark" />
                <h2 class="m-1 text-primary fw-bolder"><?= session("toko_nama") ?></h2>
            </div>
            <a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="ti ti-dots fs-7"></i>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex align-items-center justify-content-end">
                    <ul class="navbar-nav flex-row  align-items-center justify-content-center">
                        <li class="nav-item nav-icon-hover-bg rounded-circle d-block d-lg-none d-flex">
                            <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#menuSearchModal">
                                <i class="ti ti-search"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                        <!-- ------------------------------- -->
                        <!-- start profile Dropdown -->
                        <!-- ------------------------------- -->
                        <li class="nav-item dropdown">
                            <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="user-profile-img">
                                        <img src="<?= base_url('/assets/images/profile/' . $sessionAvatar) ?>" class="rounded-circle topbar-avatar" width="35" height="35" alt="modernize-img" />
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                                <div class="profile-dropdown position-relative" data-simplebar>
                                    <div class="py-3 px-7 pb-0">
                                        <h5 class="mb-0 fs-5 fw-semibold">User Profile</h5>
                                    </div>
                                    <div class="d-flex align-items-center py-9 mx-7 border-bottom">
                                        <img src="<?= base_url('/assets/images/profile/' . $sessionAvatar) ?>" class="rounded-circle topbar-avatar-lg" width="80" height="80" alt="modernize-img" />
                                        <div class="ms-3">
                                            <h5 class="mb-1 fs-3"><?= session("fullname") ?></h5>
                                            <span class="mb-1 d-block"><?= session("level_name") ?></span>
                                        </div>
                                    </div>
                                    <div class="message-body">
                                        <a href="<?= base_url("profile") ?>" class="py-8 px-7 mt-8 d-flex align-items-center">
                                            <span class="d-flex align-items-center justify-content-center text-bg-light rounded-1 p-6">
                                                <img src="<?= base_url() ?>/assets/images/svgs/icon-account.svg" alt="modernize-img" width="24" height="24" />
                                            </span>
                                            <div class="w-100 ps-3">
                                                <h6 class="mb-1 fs-3 fw-semibold lh-base">My Profile</h6>
                                                <span class="fs-2 d-block text-body-secondary">Account Settings</span>
                                            </div>
                                        </a>

                                    </div>
                                    <div class="d-grid py-4 px-7 pt-8">

                                        <a href="<?= base_url('/logout') ?>" class="btn btn-primary">Log Out</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- ------------------------------- -->
                        <!-- end profile Dropdown -->
                        <!-- ------------------------------- -->
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>

<div class="modal fade" id="menuSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-1">
            <div class="modal-header border-bottom">
                <input type="search" class="form-control fs-3" placeholder="Search menu..." id="menu-search-input" />
                <a href="javascript:void(0)" data-bs-dismiss="modal" class="lh-1">
                    <i class="ti ti-x fs-5 ms-3"></i>
                </a>
            </div>
            <div class="modal-body message-body" data-simplebar>
                <h5 class="mb-0 fs-5 p-1">Quick Page Links</h5>
                <ul class="list mb-0 py-2" id="menu-search-result"></ul>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const menuItems = <?= $menuJson ?? '[]'; ?>;
        const resultContainer = document.getElementById("menu-search-result");
        const searchInput = document.getElementById("menu-search-input");

        if (!resultContainer || !searchInput) {
            return;
        }

        const baseUrl = "<?= rtrim(base_url(), '/'); ?>";
        const idToName = {};
        menuItems.forEach((item) => {
            const id = (item.menu_id || "").toString().trim();
            if (id) {
                idToName[id] = (item.menu_name || "").toString();
            }
        });

        const flatItems = menuItems.map((item) => {
            const parentId = (item.header_menu || "").toString().trim();
            return {
                label: (item.menu_name || "").toString(),
                url: (item.link || "#").toString(),
                section: parentId ? (idToName[parentId] || "Menu") : "Main Menu"
            };
        });

        function normalizeUrl(url) {
            if (!url || url === "#") {
                return "javascript:void(0)";
            }
            if (url.startsWith("http://") || url.startsWith("https://")) {
                return url;
            }
            if (url.startsWith("/")) {
                return `${baseUrl}${url}`;
            }
            return `${baseUrl}/${url}`;
        }

        function renderList(keyword = "") {
            const term = keyword.trim().toLowerCase();
            const filtered = flatItems.filter((item) => {
                if (!term) {
                    return true;
                }
                return item.label.toLowerCase().includes(term) ||
                    item.section.toLowerCase().includes(term) ||
                    item.url.toLowerCase().includes(term);
            });

            resultContainer.innerHTML = filtered.length ?
                filtered.map((item) => `
                    <li class="p-1 mb-1 bg-hover-light-black">
                        <a href="${normalizeUrl(item.url)}">
                            <span class="d-block">${item.label}</span>
                            <span class="text-muted d-block">${item.section} - ${item.url}</span>
                        </a>
                    </li>
                `).join("") :
                '<li class="p-1 mb-1 text-muted">Menu not found</li>';
        }

        renderList();
        searchInput.addEventListener("input", (event) => renderList(event.target.value));
    })();
</script>
