<?php
// Determine current route for active menu highlighting
$currentUrl = strtolower(trim($_GET['url'] ?? '', '/'));
$userRole = ucfirst(strtolower(trim($_SESSION['user_role'] ?? 'Admin')));
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';

// Role-based dashboard link
$roleDashboardUrl = BASEURL . '/' . strtolower($userRole);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($data['title'] ?? 'Corporate Dashboard'); ?> - NNACO SA</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASEURL; ?>/img/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/img/logo-square.png">
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Noto+Sans:300,400,500,600,700,800" rel="stylesheet" />
    
    <!-- Nucleo Icons -->
    <link href="<?= BASEURL; ?>/css/nucleo-icons.css" rel="stylesheet" />
    <link href="<?= BASEURL; ?>/css/nucleo-svg.css" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Corporate UI Dashboard CSS -->
    <link id="pagestyle" href="<?= BASEURL; ?>/css/corporate-ui-dashboard.css?v=<?= time(); ?>" rel="stylesheet" />
    
    <!-- Corporate Custom Styles -->
    <link href="<?= BASEURL; ?>/css/corporate-custom.css?v=<?= time(); ?>" rel="stylesheet" />

    <!-- Theme Engine Stylesheet -->
    <link href="<?= BASEURL; ?>/css/theme.css?v=<?= time(); ?>" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <script>
        // Anti-flicker immediate state application
        (function() {
            try {
                // Sidenav state
                if (localStorage.getItem('nnaco_sidenav_minimized') === 'true' && window.innerWidth >= 1200) {
                    document.body.classList.add('g-sidenav-minimized');
                }
                // Theme state
                var pref = localStorage.getItem('nnaco_theme_preference');
                var serverPref = '<?= $_SESSION['user_theme'] ?? '' ?>';
                var chosen = pref || (serverPref ? serverPref : 'system');
                var resolved = chosen;
                if (chosen === 'system') {
                    resolved = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-theme', resolved);
                document.documentElement.setAttribute('data-theme-preference', chosen);
            } catch(e) {}
        })();
    </script>
    <script>
        window.NNACO_BASEURL = "<?= BASEURL; ?>";
        window.NNACO_IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.NNACO_USER_THEME = "<?= $_SESSION['user_theme'] ?? ''; ?>";
    </script>
    <script src="<?= BASEURL; ?>/js/theme-manager.js?v=<?= time(); ?>"></script>

    <!-- Sidenav Sidebar (Corporate UI Dark Slate) -->
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 fixed-start bg-slate-900" id="sidenav-main">
        <div class="sidenav-header d-flex align-items-center justify-content-between">
            <a class="navbar-brand d-flex align-items-center m-0 text-decoration-none" href="<?= $roleDashboardUrl; ?>" title="NNACO SA Corporate">
                <div class="icon icon-shape rounded-3 d-flex align-items-center justify-content-center me-2 shadow-sm brand-logo-icon overflow-hidden p-0" style="width: 38px; height: 38px; background-color: #f2efe8 !important;">
                    <img src="<?= BASEURL; ?>/img/logo-square.png" alt="Logo NNACO SA" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="sidenav-brand-labels">
                    <span class="sidenav-brand-text d-block lh-1">NNACO SA</span>
                    <span class="sidenav-brand-sub">CORPORATE UI</span>
                </div>
            </a>
            <i class="bi bi-x fs-3 cursor-pointer text-white-50 d-xl-none" id="iconSidenav" title="Tutup Menu"></i>
            <button class="btn btn-link text-white-50 p-1 mb-0 d-none d-xl-inline-flex align-items-center justify-content-center shadow-none rounded-circle btn-sidenav-collapse" id="btnMinimizeSidenav" type="button" title="Minimize / Perluas Sidebar">
                <i class="bi bi-text-indent-left fs-5" id="sidenavCollapseIcon"></i>
            </button>
        </div>

        <!-- User Profile Card -->
        <div class="sidenav-user-card d-flex align-items-center">
            <div class="avatar avatar-sm rounded-circle bg-white text-dark fw-bold d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.85rem;" title="<?= htmlspecialchars($userName); ?> (<?= htmlspecialchars($userRole); ?>)">
                <?= strtoupper(substr($userName, 0, 1)); ?>
            </div>
            <div class="overflow-hidden flex-grow-1 sidenav-user-details">
                <h6 class="text-white text-sm mb-0 text-truncate fw-semibold"><?= htmlspecialchars($userName); ?></h6>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 py-0 px-2 text-xxs font-weight-bold rounded-pill"><?= htmlspecialchars($userRole); ?></span>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse px-2 w-auto flex-grow-1" id="sidenav-collapse-main" style="max-height: calc(100vh - 240px); overflow-y: auto;">
            <ul class="navbar-nav">
                
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <?php 
                    $isDashActive = in_array($currentUrl, ['admin', 'operator', 'owner', 'customer']); 
                    ?>
                    <a class="nav-link <?= $isDashActive ? 'active' : ''; ?>" href="<?= $roleDashboardUrl; ?>" data-tooltip="Dashboard">
                        <div class="icon-shape">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <span class="nav-link-text">Dashboard</span>
                    </a>
                </li>

                <?php if ($userRole === 'Admin') : ?>
                    <!-- Section: MASTER DATA -->
                    <li class="nav-item mt-3">
                        <h6 class="sidenav-heading">Manajemen Toko</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'adminproduct') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/adminproduct" data-tooltip="Katalog Produk">
                            <div class="icon-shape">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <span class="nav-link-text">Katalog Produk</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'admincategory') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/admincategory" data-tooltip="Kategori Produk">
                            <div class="icon-shape">
                                <i class="bi bi-tags"></i>
                            </div>
                            <span class="nav-link-text">Kategori Produk</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'adminorder') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/adminorder" data-tooltip="Semua Pesanan">
                            <div class="icon-shape">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <span class="nav-link-text">Semua Pesanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'adminuser') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/adminuser" data-tooltip="Kelola Pengguna">
                            <div class="icon-shape">
                                <i class="bi bi-people"></i>
                            </div>
                            <span class="nav-link-text">Kelola Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'adminchatbot') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/adminchatbot" data-tooltip="Riwayat Chatbot">
                            <div class="icon-shape">
                                <i class="bi bi-robot"></i>
                            </div>
                            <span class="nav-link-text">Riwayat Chatbot</span>
                        </a>
                    </li>

                <?php elseif ($userRole === 'Operator') : ?>
                    <!-- Section: OPERASIONAL -->
                    <li class="nav-item mt-3">
                        <h6 class="sidenav-heading">Operasional</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'operatororder') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/operatororder" data-tooltip="Kelola Pesanan">
                            <div class="icon-shape">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <span class="nav-link-text">Kelola Pesanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'operatorproduct') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/operatorproduct" data-tooltip="Stok Produk">
                            <div class="icon-shape">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <span class="nav-link-text">Stok Produk</span>
                        </a>
                    </li>

                <?php elseif ($userRole === 'Owner') : ?>
                    <!-- Section: ANALISIS -->
                    <li class="nav-item mt-3">
                        <h6 class="sidenav-heading">Analisis & Laporan</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'ownerreport') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/ownerreport" data-tooltip="Laporan Keuangan">
                            <div class="icon-shape">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="nav-link-text">Laporan Keuangan</span>
                        </a>
                    </li>
                <?php elseif ($userRole === 'Customer') : ?>
                    <!-- Section: AREA PELANGGAN -->
                    <li class="nav-item mt-3">
                        <h6 class="sidenav-heading">Area Pelanggan</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($currentUrl, 'customer/history') === 0 || strpos($currentUrl, 'customer/orderdetail') === 0) ? 'active' : ''; ?>" href="<?= BASEURL; ?>/customer/history" data-tooltip="Riwayat Pesanan">
                            <div class="icon-shape">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <span class="nav-link-text">Riwayat Pesanan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'cart') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/cart" data-tooltip="Keranjang Belanja">
                            <div class="icon-shape">
                                <i class="bi bi-cart3"></i>
                            </div>
                            <span class="nav-link-text">Keranjang Belanja</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (in_array($userRole, ['Admin', 'Owner'])) : ?>
                    <!-- Section: PENGATURAN SISTEM -->
                    <li class="nav-item mt-3">
                        <h6 class="sidenav-heading">Sistem</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($currentUrl, 'settings') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/settings" data-tooltip="Pengaturan Sistem">
                            <div class="icon-shape">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <span class="nav-link-text">Pengaturan Sistem</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Section: TOKO PUBLIK -->
                <li class="nav-item mt-3">
                    <h6 class="sidenav-heading">Toko & Belanja</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASEURL; ?>" target="_blank" data-tooltip="Kunjungi Toko">
                        <div class="icon-shape">
                            <i class="bi bi-shop"></i>
                        </div>
                        <span class="nav-link-text">Kunjungi Toko <i class="bi bi-box-arrow-up-right ms-1 text-xs opacity-7"></i></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASEURL; ?>/product" target="_blank" data-tooltip="Katalog Belanja">
                        <div class="icon-shape">
                            <i class="bi bi-grid"></i>
                        </div>
                        <span class="nav-link-text">Katalog Belanja</span>
                    </a>
                </li>

                <!-- Section: AKUN SAYA -->
                <li class="nav-item mt-3">
                    <h6 class="sidenav-heading">Akun & Sesi</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($currentUrl, 'profile') === 0 ? 'active' : ''; ?>" href="<?= BASEURL; ?>/profile" data-tooltip="Profil Saya">
                        <div class="icon-shape">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <span class="nav-link-text">Profil Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?= BASEURL; ?>/auth/logout" onclick="return confirm('Yakin ingin keluar dari sesi?');" data-tooltip="Keluar">
                        <div class="icon-shape bg-danger bg-opacity-20 text-danger">
                            <i class="bi bi-box-arrow-right"></i>
                        </div>
                        <span class="nav-link-text text-danger">Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <!-- Top Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-4 shadow-none border-radius-xl my-2 mx-3" id="navbarBlur" navbar-scroll="true" style="position: relative; z-index: 1045;">
            <div class="container-fluid py-2 px-0 d-flex justify-content-between align-items-center">
                
                <!-- Breadcrumbs & Sidebar Toggle Button -->
                <div class="d-flex align-items-center">
                    <!-- Toggle / Minimize Button (Visible on both desktop & mobile) -->
                    <button class="btn btn-sm btn-white-corp p-2 me-3 d-inline-flex align-items-center justify-content-center shadow-xs btn-navbar-sidenav-toggle" id="iconNavbarSidenav" type="button" title="Minimize / Perluas Sidebar">
                        <i class="bi bi-text-indent-left fs-5 text-dark" id="navbarSidenavIcon"></i>
                    </button>
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-0 px-0">
                                <li class="breadcrumb-item"><a href="<?= $roleDashboardUrl; ?>" class="opacity-7 text-dark text-xs"><i class="bi bi-house-door me-1"></i> Dashboard</a></li>
                                <li class="breadcrumb-item active text-dark text-xs fw-bold" aria-current="page"><?= htmlspecialchars($userRole); ?></li>
                            </ol>
                        </nav>
                        <h5 class="font-weight-bold mb-0 text-dark"><?= htmlspecialchars($data['title'] ?? 'Dashboard'); ?></h5>
                    </div>
                </div>

                <!-- Right Toolbar -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Quick Store Link -->
                    <a href="<?= BASEURL; ?>" target="_blank" class="btn btn-sm btn-white-corp d-none d-sm-inline-flex align-items-center mb-0">
                        <i class="bi bi-shop me-1 text-primary"></i> Toko
                    </a>

                    <!-- Theme Switcher Dropdown (Corporate) -->
                    <div class="dropdown" style="position: relative; z-index: 1055;">
                        <button class="theme-selector-btn" type="button" id="themeDropdownCorp" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Tema Tampilan">
                            <i class="bi bi-display"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end theme-dropdown-menu shadow-lg" aria-labelledby="themeDropdownCorp" style="z-index: 1060;">
                            <li class="px-3 py-1 mb-1 border-bottom">
                                <span class="text-xxs text-uppercase fw-bold text-muted">Tema Tampilan</span>
                            </li>
                            <li>
                                <a class="theme-option-item" href="javascript:void(0)" data-set-theme="light" data-theme-value="light">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-sun-fill text-warning"></i>
                                        <span>Mode Terang</span>
                                    </span>
                                    <i class="bi bi-check-lg check-icon"></i>
                                </a>
                            </li>
                            <li>
                                <a class="theme-option-item" href="javascript:void(0)" data-set-theme="dark" data-theme-value="dark">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-moon-stars-fill text-primary"></i>
                                        <span>Mode Gelap</span>
                                    </span>
                                    <i class="bi bi-check-lg check-icon"></i>
                                </a>
                            </li>
                            <li>
                                <a class="theme-option-item" href="javascript:void(0)" data-set-theme="system" data-theme-value="system">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-display text-info"></i>
                                        <span>Ikuti Sistem</span>
                                    </span>
                                    <i class="bi bi-check-lg check-icon"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- User Dropdown Menu -->
                    <div class="dropdown ms-2" style="position: relative; z-index: 1055;">
                        <a href="javascript:;" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle no-caret" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar avatar-sm rounded-circle bg-slate-900 text-white fw-bold d-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                <?= strtoupper(substr($userName, 0, 1)); ?>
                            </div>
                            <span class="d-none d-lg-inline text-sm fw-semibold text-dark me-1"><?= htmlspecialchars($userName); ?></span>
                            <i class="bi bi-chevron-down text-xs opacity-7 ms-1"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 py-2" aria-labelledby="profileDropdown" style="border-radius: 12px; min-width: 240px; z-index: 1060;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark text-truncate text-sm"><?= htmlspecialchars($userName); ?></div>
                                <div class="text-xs text-muted text-truncate"><?= htmlspecialchars($userEmail); ?></div>
                                <span class="badge badge-corp badge-corp-dark mt-1"><?= htmlspecialchars($userRole); ?></span>
                            </li>
                            <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/profile"><i class="bi bi-person-gear me-2 text-primary"></i> Kelola Profil</a></li>
                            <li><a class="dropdown-item py-2 text-sm" href="<?= $roleDashboardUrl; ?>"><i class="bi bi-speedometer2 me-2 text-secondary"></i> Dashboard <?= htmlspecialchars($userRole); ?></a></li>
                            <?php if ($userRole === 'Admin') : ?>
                                <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/adminchatbot"><i class="bi bi-robot me-2 text-info"></i> Riwayat Chatbot AI</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 text-sm text-danger" href="<?= BASEURL; ?>/auth/logout" onclick="return confirm('Yakin ingin keluar?');"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </nav>
        <!-- End Top Navbar -->

        <!-- Page Content Container -->
        <div class="container-fluid py-4 px-4" style="position: relative; z-index: 1;">
            <!-- Flash Message Banner -->
            <?php Flasher::flash(); ?>
