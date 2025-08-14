<?php
// admin/components/menu.php

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
            <span class="app-brand-logo demo">
                <?php if (!empty($appSetting['logo']) && $appSetting['logo'] !== 'favicon.ico'): ?>
                    <img src="../../assets/img/favicon/<?php echo htmlspecialchars($appSetting['logo']); ?>" alt="Logo"
                        width="25" height="25" style="object-fit: contain;">
                <?php else: ?>
                    <!-- Default SVG Logo jika tidak ada logo custom -->
                    <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                        <defs>
                            <path
                                d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                                id="path-1"></path>
                            <path
                                d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                                id="path-3"></path>
                            <path
                                d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                                id="path-4"></path>
                            <path
                                d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                                id="path-5"></path>
                        </defs>
                        <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                                <g id="Icon" transform="translate(27.000000, 15.000000)">
                                    <g id="Mask" transform="translate(0.000000, 8.000000)">
                                        <mask id="mask-2" fill="white">
                                            <use xlink:href="#path-1"></use>
                                        </mask>
                                        <use fill="#696cff" xlink:href="#path-1"></use>
                                        <g id="Path-3" mask="url(#mask-2)">
                                            <use fill="#696cff" xlink:href="#path-3"></use>
                                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                        </g>
                                        <g id="Path-4" mask="url(#mask-2)">
                                            <use fill="#696cff" xlink:href="#path-4"></use>
                                            <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                        </g>
                                    </g>
                                    <g id="Triangle"
                                        transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                        <use fill="#696cff" xlink:href="#path-5"></use>
                                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                <?php endif; ?>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2" id="appBrandText"
                title="<?php echo htmlspecialchars($appSetting['name']); ?>">
                <?php echo htmlspecialchars($appSetting['name']); ?>
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <a href="index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <!-- Account -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Account</span>
        </li>
        <li class="menu-item <?= in_array($current_page, ['admin.php', 'petugas.php']) ? 'active open' : '' ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Account Settings">Data Account</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= $current_page == 'admin.php' ? 'active' : '' ?>">
                    <a href="admin.php" class="menu-link">
                        <div data-i18n="admin.php">Administrator</div>
                    </a>
                </li>
                <li class="menu-item <?= $current_page == 'petugas.php' ? 'active' : '' ?>">
                    <a href="petugas.php" class="menu-link">
                        <div data-i18n="petugas.php">Pegawai</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Kegiatan -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Kegiatan</span>
        </li>
        <li class="menu-item <?= in_array($current_page, ['kegiatan.php', 'penugasan.php']) ? 'active open' : '' ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div data-i18n="Data Kegiatan">Data Kegiatan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= $current_page == 'kegiatan.php' ? 'active' : '' ?>">
                    <a href="kegiatan.php" class="menu-link">
                        <div data-i18n="kegiatan.php">Kegiatan</div>
                    </a>
                </li>
                <li class="menu-item <?= $current_page == 'penugasan.php' ? 'active' : '' ?>">
                    <a href="penugasan.php" class="menu-link">
                        <div data-i18n="penugasan.php">Penugasan</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Fitur Lainnya -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Fitur Lainnya</span>
        </li>

        <!-- Komentar -->
        <li class="menu-item <?php echo ($current_page == 'komentar.php') ? 'active' : ''; ?>">
            <a href="komentar.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-poll"></i>
                <div data-i18n="omentar.php">Kelola Komentar</div>
            </a>
        </li>

        <!-- Kontrol Panel -->
        <li class="menu-item <?php echo ($current_page == 'kontrolPanel.php') ? 'active' : ''; ?>">
            <a href="kontrolPanel.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="kontrolPanel.php">Kontrol Panel</div>
            </a>
        </li>
    </ul>
</aside>

<style>
    /* CSS untuk responsive app name dengan auto font size */
    .app-brand-text {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: font-size 0.3s ease;
    }

    /* Auto font size berdasarkan panjang teks */
    .app-brand-text.text-xs {
        font-size: 0.75rem !important;
    }

    .app-brand-text.text-sm {
        font-size: 0.875rem !important;
    }

    .app-brand-text.text-base {
        font-size: 1rem !important;
    }

    /* Responsive breakpoints */
    @media (max-width: 1199.98px) {
        .app-brand-text {
            max-width: 160px;
        }
    }

    @media (max-width: 991.98px) {
        .app-brand-text {
            max-width: 140px;
        }
    }

    @media (max-width: 767.98px) {
        .app-brand-text {
            max-width: 120px;
        }
    }

    /* Untuk sidebar yang collapsed */
    .layout-menu-collapsed .app-brand-text {
        display: none;
    }

    /* Hover effect untuk melihat nama lengkap */
    .app-brand-text:hover {
        overflow: visible;
        white-space: nowrap;
        background: rgba(255, 255, 255, 0.9);
        padding: 2px 6px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        position: relative;
        max-width: none;
    }
</style>

<script>
    // JavaScript untuk auto resize font berdasarkan panjang teks
    document.addEventListener('DOMContentLoaded', function() {
        const appBrandText = document.getElementById('appBrandText');

        if (appBrandText) {
            const textLength = appBrandText.textContent.trim().length;
            const containerWidth = appBrandText.parentElement.offsetWidth;

            // Fungsi untuk mengatur ukuran font berdasarkan panjang teks
            function adjustFontSize() {
                const maxWidth = window.innerWidth <= 767 ? 120 :
                    window.innerWidth <= 991 ? 140 :
                    window.innerWidth <= 1199 ? 160 : 200;

                // Reset classes
                appBrandText.classList.remove('text-xs', 'text-sm', 'text-base');

                // Cek apakah teks overflow
                if (appBrandText.scrollWidth > maxWidth) {
                    if (textLength > 25) {
                        appBrandText.classList.add('text-xs');
                    } else if (textLength > 18) {
                        appBrandText.classList.add('text-sm');
                    } else {
                        appBrandText.classList.add('text-base');
                    }

                    // Jika masih overflow setelah resize, gunakan ellipsis
                    if (appBrandText.scrollWidth > maxWidth) {
                        appBrandText.style.textOverflow = 'ellipsis';
                        appBrandText.style.overflow = 'hidden';
                    }
                } else {
                    appBrandText.classList.add('text-base');
                    appBrandText.style.textOverflow = 'initial';
                    appBrandText.style.overflow = 'visible';
                }
            }

            // Jalankan saat load
            adjustFontSize();

            // Jalankan saat resize window
            window.addEventListener('resize', adjustFontSize);

            // Fallback dengan timeout untuk memastikan font sudah load
            setTimeout(adjustFontSize, 100);
        }
    });
</script>