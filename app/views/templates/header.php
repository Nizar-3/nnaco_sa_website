<?php
// Check if current route is a Corporate Dashboard route
$urlPath = strtolower(trim($_GET['url'] ?? '', '/'));
$dashboardPrefixes = [
    'admin', 'adminproduct', 'admincategory', 'adminuser', 'adminorder', 'adminchatbot',
    'operator', 'operatorproduct', 'operatororder', 
    'owner', 'ownerreport',
    'customer', 'profile', 'assistant', 'settings'
];
$isDashboardRoute = false;

foreach ($dashboardPrefixes as $prefix) {
    if ($urlPath === $prefix || strpos($urlPath, $prefix . '/') === 0) {
        $isDashboardRoute = true;
        break;
    }
}
if (isset($data['is_dashboard'])) {
    $isDashboardRoute = (bool)$data['is_dashboard'];
}

if ($isDashboardRoute) {
    require __DIR__ . '/corporate_header.php';
    return;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'NNACO SA'); ?> - Corporate UI Store</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASEURL; ?>/img/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/img/logo-square.png">
    
    <!-- Fonts & Icons (Corporate UI Dashboard Laravel) -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Noto+Sans:300,400,500,600,700,800" rel="stylesheet" />
    <link href="<?= BASEURL; ?>/css/nucleo-icons.css" rel="stylesheet" />
    <link href="<?= BASEURL; ?>/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Corporate UI Dashboard Stylesheet -->
    <link href="<?= BASEURL; ?>/css/corporate-ui-dashboard.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="<?= BASEURL; ?>/css/corporate-custom.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="<?= BASEURL; ?>/css/theme.css?v=<?= time(); ?>" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css?v=<?= time(); ?>">

    <!-- Anti-flicker Synchronous Theme Application -->
    <script>
        (function() {
            try {
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
</head>
<body class="bg-gray-100" style="font-family: 'Open Sans', 'Noto Sans', sans-serif;">

    <!-- Corporate UI Top Store Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top bg-white border-bottom shadow-xs py-3" style="border-color: #e2e8f0 !important; backdrop-filter: blur(8px); z-index: 1045;">
        <div class="container d-flex justify-content-between align-items-center">
            
            <!-- Logo Brand -->
            <a class="navbar-brand d-flex align-items-center text-decoration-none m-0" href="<?= BASEURL; ?>">
                <div class="icon icon-shape rounded-3 me-2 shadow-xs d-flex align-items-center justify-content-center overflow-hidden p-0" style="width: 38px; height: 38px; background-color: #f2efe8 !important;">
                    <img src="<?= BASEURL; ?>/img/logo-square.png" alt="Logo NNACO SA" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="lh-1">
                    <span class="fs-5 fw-bold text-slate-900 d-block" style="letter-spacing: 0.5px; font-family: 'Noto Sans', sans-serif;">NNACO SA</span>
                    <span class="text-xs text-muted fw-semibold" style="letter-spacing: 1.2px;">CORPORATE STORE</span>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2 text-dark"></i>
            </button>

            <!-- Navigation Links (Center) -->
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav fw-semibold" style="font-size: 0.875rem;">
                    <li class="nav-item mx-1"><a class="nav-link px-3 py-2 rounded-2 <?= empty($urlPath) ? 'text-primary fw-bold' : 'text-slate-600'; ?>" href="<?= BASEURL; ?>">Home</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3 py-2 rounded-2 <?= strpos($urlPath, 'product') === 0 ? 'text-primary fw-bold' : 'text-slate-600'; ?>" href="<?= BASEURL; ?>/product">Katalog Belanja</a></li>
                    <li class="nav-item dropdown mx-1" style="position: relative; z-index: 1055;">
                        <a class="nav-link dropdown-toggle no-caret px-3 py-2 rounded-2 text-slate-600 d-inline-flex align-items-center" href="#" id="collectionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Koleksi <i class="bi bi-chevron-down text-xs ms-1 opacity-75"></i>
                        </a>
                        <ul class="dropdown-menu border-0 shadow-lg py-2 mt-2" aria-labelledby="collectionsDropdown" style="border-radius: 12px; font-size: 0.85rem; border: 1px solid #e2e8f0 !important; z-index: 1060;">
                            <li><a class="dropdown-item py-2 fw-medium text-dark" href="<?= BASEURL; ?>/product"><i class="bi bi-grid me-2 text-muted"></i> Semua Koleksi</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 fw-medium text-dark" href="<?= BASEURL; ?>/product?category=1"><i class="bi bi-gem me-2 text-warning"></i> Kalung (Necklaces)</a></li>
                            <li><a class="dropdown-item py-2 fw-medium text-dark" href="<?= BASEURL; ?>/product?category=2"><i class="bi bi-circle me-2 text-info"></i> Gelang (Bracelets)</a></li>
                            <li><a class="dropdown-item py-2 fw-medium text-dark" href="<?= BASEURL; ?>/product?category=3"><i class="bi bi-stars me-2 text-danger"></i> Anting (Earrings)</a></li>
                            <li><a class="dropdown-item py-2 fw-medium text-dark" href="<?= BASEURL; ?>/product?category=4"><i class="bi bi-record-circle me-2 text-success"></i> Cincin (Rings)</a></li>
                            <li><a class="dropdown-item py-2 fw-medium text-dark" href="<?= BASEURL; ?>/product?category=5"><i class="bi bi-flower1 me-2 text-primary"></i> Jepit Rambut (Hair Clips)</a></li>
                        </ul>
                    </li>
                    <li class="nav-item mx-1"><a class="nav-link px-3 py-2 rounded-2 <?= strpos($urlPath, 'about') === 0 ? 'text-primary fw-bold' : 'text-slate-600'; ?>" href="<?= BASEURL; ?>/about">Tentang Kami</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3 py-2 rounded-2 <?= strpos($urlPath, 'blog') === 0 ? 'text-primary fw-bold' : 'text-slate-600'; ?>" href="<?= BASEURL; ?>/blog">Blog</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3 py-2 rounded-2 <?= strpos($urlPath, 'contact') === 0 ? 'text-primary fw-bold' : 'text-slate-600'; ?>" href="<?= BASEURL; ?>/contact">Kontak</a></li>
                    
                    <?php if(isset($_SESSION['user_id'])) : ?>
                        <li class="nav-item d-lg-none mt-2 pt-2 border-top">
                            <a class="nav-link text-primary fw-semibold" href="<?= BASEURL; ?>/profile"><i class="bi bi-person-gear me-1"></i> Profil (<?= htmlspecialchars($_SESSION['user_name'] ?? ''); ?>)</a>
                        </li>
                        <li class="nav-item d-lg-none">
                            <a class="nav-link text-dark" href="<?= BASEURL; ?>/<?= strtolower($_SESSION['user_role'] ?? 'customer'); ?>"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                        </li>
                        <li class="nav-item d-lg-none">
                            <a class="nav-link text-danger fw-semibold" href="<?= BASEURL; ?>/auth/logout" onclick="return confirm('Yakin ingin keluar?');"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                        </li>
                    <?php else : ?>
                        <li class="nav-item d-lg-none mt-2 pt-2 border-top">
                            <a class="nav-link text-dark" href="<?= BASEURL; ?>/auth/login"><i class="bi bi-person me-1"></i> Masuk / Daftar</a>
                        </li>
                    <?php endif; ?>

                    <!-- Mobile Theme Switcher -->
                    <li class="nav-item d-lg-none mt-2 pt-2 border-top">
                        <div class="d-flex align-items-center justify-content-between px-3 py-2">
                            <span class="text-xs fw-bold text-muted text-uppercase">Tema Tampilan</span>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Pilih Tema">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-set-theme="light" data-theme-value="light" title="Mode Terang">
                                    <i class="bi bi-sun-fill text-warning"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-set-theme="dark" data-theme-value="dark" title="Mode Gelap">
                                    <i class="bi bi-moon-stars-fill text-primary"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-set-theme="system" data-theme-value="system" title="Ikuti Sistem">
                                    <i class="bi bi-display text-info"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Right Toolbar (Search, Cart, Theme, User) -->
            <div class="d-none d-lg-flex align-items-center gap-2">
                <!-- Search Button -->
                <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center" title="Cari Produk">
                    <i class="bi bi-search me-1"></i> <span class="d-none d-xl-inline">Cari</span>
                </a>

                <!-- Cart Button -->
                <?php 
                    $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; 
                ?>
                <a href="<?= BASEURL; ?>/cart" class="btn btn-sm btn-white-corp position-relative mb-0 d-inline-flex align-items-center" title="Keranjang Belanja">
                    <i class="bi bi-cart3 me-1"></i>
                    <span class="d-none d-xl-inline">Keranjang</span>
                    <?php if($cartCount > 0): ?>
                        <span class="badge bg-primary rounded-pill ms-1 text-white" style="font-size: 0.65rem;">
                            <?= $cartCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Theme Switcher Dropdown (Desktop) -->
                <div class="dropdown" style="position: relative; z-index: 1055;">
                    <button class="theme-selector-btn" type="button" id="themeDropdownPublic" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Tema Tampilan">
                        <i class="bi bi-display"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end theme-dropdown-menu shadow-lg" aria-labelledby="themeDropdownPublic" style="z-index: 1060;">
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

                <!-- User Dropdown or Login -->
                <?php if(isset($_SESSION['user_id'])) : ?>
                    <div class="dropdown ms-1" style="position: relative; z-index: 1055;">
                        <a href="#" class="btn btn-sm btn-dark-corp mb-0 dropdown-toggle no-caret d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            <div class="avatar avatar-xs rounded-circle bg-white text-dark fw-bold me-2 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.65rem;">
                                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <span class="me-1"><?= htmlspecialchars(explode(' ', trim($_SESSION['user_name'] ?? 'Akun'))[0]); ?></span>
                            <i class="bi bi-chevron-down text-xs opacity-75"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 py-2" style="border-radius: 12px; min-width: 220px; border: 1px solid #e2e8f0 !important; z-index: 1060;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark text-truncate text-sm"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                                <div class="text-xs text-muted text-truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                                <span class="badge badge-corp badge-corp-dark mt-1"><?= htmlspecialchars(ucfirst(strtolower($_SESSION['user_role'] ?? 'Customer'))); ?></span>
                            </li>
                            <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/<?= strtolower($_SESSION['user_role'] ?? 'customer'); ?>"><i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</a></li>
                            <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/profile"><i class="bi bi-person-gear me-2 text-secondary"></i> Profil Saya</a></li>
                            <?php if (strcasecmp($_SESSION['user_role'] ?? '', 'Customer') === 0): ?>
                                <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/customer/history"><i class="bi bi-clock-history me-2 text-info"></i> Riwayat Pesanan</a></li>
                            <?php elseif (strcasecmp($_SESSION['user_role'] ?? '', 'Admin') === 0 || strcasecmp($_SESSION['user_role'] ?? '', 'Owner') === 0): ?>
                                <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/adminchatbot"><i class="bi bi-robot me-2 text-info"></i> Riwayat Chatbot AI</a></li>
                                <li><a class="dropdown-item py-2 text-sm" href="<?= BASEURL; ?>/settings?tab=ai"><i class="bi bi-cpu-fill me-2 text-warning"></i> Konfigurasi AI & Sistem</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 text-sm text-danger" href="<?= BASEURL; ?>/auth/logout" onclick="return confirm('Yakin ingin keluar?');"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else : ?>
                    <a href="<?= BASEURL; ?>/auth/login" class="btn btn-sm btn-white-corp mb-0">Masuk</a>
                    <a href="<?= BASEURL; ?>/auth/register" class="btn btn-sm btn-dark-corp mb-0">Daftar</a>
                <?php endif; ?>
            </div>
            
        </div>
    </nav>
