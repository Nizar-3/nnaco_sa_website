        </div>
        <!-- /Page Content Container -->

        <!-- Corporate UI Footer -->
        <footer class="footer pt-3 mt-auto pb-4">
            <div class="container-fluid px-4">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-3 text-center text-lg-start">
                        <p class="text-xs text-muted mb-0">
                            &copy; <?= date('Y'); ?> <span class="fw-bold text-dark">NNACO SA</span> &bull; Corporate UI Dashboard System.
                        </p>
                    </div>
                    <div class="col-lg-6 text-center text-lg-end">
                        <ul class="nav justify-content-center justify-content-lg-end">
                            <li class="nav-item"><a href="<?= BASEURL; ?>" class="nav-link text-xs text-muted px-2" target="_blank"><i class="bi bi-shop me-1"></i> Toko Utama</a></li>
                            <li class="nav-item"><a href="<?= BASEURL; ?>/product" class="nav-link text-xs text-muted px-2" target="_blank"><i class="bi bi-grid me-1"></i> Produk</a></li>
                            <li class="nav-item"><a href="<?= BASEURL; ?>/profile" class="nav-link text-xs text-muted px-2"><i class="bi bi-person me-1"></i> Akun</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <!-- Floating AI Assistant Button & Widget -->
    <?php
    $isAiChatbotEnabled = class_exists('SettingService') ? SettingService::isAiEnabled() : true;
    if ($isAiChatbotEnabled) :
        $aiAuthSecret = defined('AI_AUTH_SECRET') ? AI_AUTH_SECRET : 'nnaco_secure_ai_hmac_secret_2026_x87b';
        $aiUserId = $_SESSION['user_id'] ?? null;
        $aiUserRole = ucfirst(strtolower(trim($_SESSION['user_role'] ?? 'Admin')));
        $aiUserName = $_SESSION['user_name'] ?? 'Tamu';
        $aiUserEmail = $_SESSION['user_email'] ?? '';
        $aiPayload = json_encode([
            'id' => $aiUserId,
            'role' => $aiUserRole,
            'name' => $aiUserName,
            'email' => $aiUserEmail,
            'exp' => time() + 86400
        ]);
        $aiPayloadB64 = base64_encode($aiPayload);
        $aiSig = hash_hmac('sha256', $aiPayloadB64, $aiAuthSecret);
        $aiToken = $aiPayloadB64 . '.' . $aiSig;
    ?>
    <button class="btn btn-dark-corp rounded-circle shadow-lg d-flex align-items-center justify-content-center position-fixed border-0 ai-chat-toggle-btn" type="button" id="aiChatToggleBtn" onclick="toggleChatFloatingWidget()" style="width: 56px; height: 56px; bottom: 25px; right: 25px; z-index: 1060;" title="Buka Asisten AI">
        <i class="bi bi-robot fs-4 text-white" id="aiChatToggleIcon"></i>
    </button>

    <!-- AI Chat Floating Card Widget (Mengambang) -->
    <div class="ai-chat-floating-widget shadow-xl" id="aiChatFloatingWidget" aria-labelledby="aiChatFloatingWidgetLabel">
        <!-- Floating Header -->
        <div class="ai-chat-floating-header bg-slate-900 text-white p-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-white text-dark rounded-2 d-flex align-items-center justify-content-center me-2 shadow-xs" style="width: 34px; height: 34px;">
                    <i class="bi bi-robot text-dark fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-white mb-0" id="aiChatFloatingWidgetLabel" style="font-size: 0.95rem;">nnaco AI Assistant</h6>
                    <small class="text-white-50 d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                        <i class="bi bi-circle-fill text-success" style="font-size: 0.45rem;"></i>
                        <span>Online</span> &bull; <span>Role:</span> <span class="badge bg-slate-800 text-white fw-bold px-1.5 py-0.5 border border-slate-700" style="font-size: 0.68rem;"><?= htmlspecialchars($aiUserRole); ?></span>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none shadow-none rounded-circle ai-header-btn" onclick="refreshChatConversation()" title="Mulai Ulang / Refresh Percakapan" id="btn-refresh-chat">
                    <i class="bi bi-arrow-clockwise fs-5" id="icon-refresh-chat"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none shadow-none rounded-circle ai-header-btn" onclick="toggleChatFloatingWidget(false)" title="Kecilkan Window">
                    <i class="bi bi-dash-lg fs-5"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none shadow-none rounded-circle ai-header-btn" onclick="toggleChatFloatingWidget(false)" title="Tutup Chat">
                    <i class="bi bi-x-lg fs-6"></i>
                </button>
            </div>
        </div>

        <!-- Floating Body -->
        <div class="ai-chat-floating-body d-flex flex-column p-0 flex-grow-1" style="background-color: #f8fafc;">
            <!-- Chat Messages Box -->
            <div id="global-chat-box" class="flex-grow-1 p-3 overflow-auto" style="font-size: 0.875rem;">
                <!-- Bot Intro Message -->
                <div class="d-flex justify-content-start mb-3" id="global-bot-intro-message">
                    <div class="bg-white text-dark rounded-4 p-3 shadow-xs border" style="max-width: 90%; border-bottom-left-radius: 4px !important;">
                        <div class="text-center mb-0">
                            <span class="fs-2">&#129302;</span>
                            <h6 class="fw-bold mt-1 mb-1">Halo, <?= htmlspecialchars($aiUserName); ?>! 👋</h6>
                            <p class="text-muted small mb-0">Saya nnaco AI Assistant. Saya siap membantu operasional, data stok, pesanan, dan laporan langsung dari database.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input Form -->
            <div class="p-3 bg-white border-top">
                <form id="global-chat-form" class="d-flex gap-2">
                    <input type="text" id="global-chat-input" class="form-control rounded-pill px-3 shadow-none border" placeholder="Tanyakan stok, pesanan, atau sistem..." required autocomplete="off" style="font-size: 0.875rem;">
                    <button type="submit" class="btn btn-dark-corp rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bi bi-send-fill fs-6"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Corporate UI JS -->
    <script src="<?= BASEURL; ?>/js/corporate-ui-dashboard.min.js"></script>
    <script src="<?= BASEURL; ?>/js/table-ajax-filter.js?v=<?= time(); ?>"></script>

    <!-- Bootstrap Modal Stacking & Backdrop Auto-Fix -->
    <script>
    document.addEventListener('show.bs.modal', function(event) {
        if (event.target && event.target.parentElement !== document.body) {
            document.body.appendChild(event.target);
        }
    });
    </script>

    <!-- Sidenav Minimize & Responsive Drawer Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const iconNavbarSidenav = document.getElementById('iconNavbarSidenav');
        const iconSidenav = document.getElementById('iconSidenav');
        const btnMinimizeSidenav = document.getElementById('btnMinimizeSidenav');
        const navbarSidenavIcon = document.getElementById('navbarSidenavIcon');
        const sidenavCollapseIcon = document.getElementById('sidenavCollapseIcon');
        const sidenav = document.getElementById('sidenav-main');

        // Automatically assign data-tooltip to any .nav-link for clean hover tooltip in minimized mode
        document.querySelectorAll('.sidenav .nav-link').forEach(link => {
            if (!link.getAttribute('data-tooltip')) {
                const textElem = link.querySelector('.nav-link-text');
                if (textElem) {
                    link.setAttribute('data-tooltip', textElem.textContent.trim());
                }
            }
        });

        // Create or get mobile backdrop
        let backdrop = document.querySelector('.sidenav-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidenav-backdrop';
            document.body.appendChild(backdrop);
        }

        function updateToggleIcons(isMinimized) {
            if (window.innerWidth >= 1200) {
                if (navbarSidenavIcon) {
                    navbarSidenavIcon.className = isMinimized 
                        ? 'bi bi-text-indent-right fs-5 text-dark' 
                        : 'bi bi-text-indent-left fs-5 text-dark';
                }
                if (sidenavCollapseIcon) {
                    sidenavCollapseIcon.className = isMinimized 
                        ? 'bi bi-text-indent-right fs-5 text-white-50' 
                        : 'bi bi-text-indent-left fs-5 text-white-50';
                }
                if (iconNavbarSidenav) {
                    iconNavbarSidenav.setAttribute('title', isMinimized ? 'Perluas Sidebar (Ctrl+B)' : 'Minimize Sidebar (Ctrl+B)');
                }
                if (btnMinimizeSidenav) {
                    btnMinimizeSidenav.setAttribute('title', isMinimized ? 'Perluas Sidebar' : 'Minimize Sidebar');
                }
            } else {
                if (navbarSidenavIcon) {
                    navbarSidenavIcon.className = 'bi bi-list fs-5 text-dark';
                }
                if (iconNavbarSidenav) {
                    iconNavbarSidenav.setAttribute('title', 'Buka Menu Sidebar');
                }
            }
        }

        function toggleMinimizeDesktop() {
            const isMin = document.body.classList.toggle('g-sidenav-minimized');
            try {
                localStorage.setItem('nnaco_sidenav_minimized', isMin ? 'true' : 'false');
            } catch (e) {}
            updateToggleIcons(isMin);
        }

        function toggleMobileSidenav(open = null) {
            if (!sidenav) return;
            const willOpen = open !== null ? open : !sidenav.classList.contains('show');
            if (willOpen) {
                sidenav.classList.add('show');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            } else {
                sidenav.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        // Navbar toggle button listener
        if (iconNavbarSidenav) {
            iconNavbarSidenav.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.innerWidth >= 1200) {
                    toggleMinimizeDesktop();
                } else {
                    toggleMobileSidenav();
                }
            });
        }

        // Sidenav header minimize button (desktop)
        if (btnMinimizeSidenav) {
            btnMinimizeSidenav.addEventListener('click', function(e) {
                e.preventDefault();
                toggleMinimizeDesktop();
            });
        }

        // Sidenav header close button (mobile)
        if (iconSidenav) {
            iconSidenav.addEventListener('click', function() {
                toggleMobileSidenav(false);
            });
        }

        // Backdrop click (mobile)
        backdrop.addEventListener('click', function() {
            toggleMobileSidenav(false);
        });

        // Keyboard Shortcut: Ctrl + B or Alt + B
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.altKey) && e.key.toLowerCase() === 'b') {
                const tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
                if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') {
                    e.preventDefault();
                    if (window.innerWidth >= 1200) {
                        toggleMinimizeDesktop();
                    } else {
                        toggleMobileSidenav();
                    }
                }
            }
        });

        // Initialize state on load
        const currentIsMin = document.body.classList.contains('g-sidenav-minimized');
        updateToggleIcons(currentIsMin);

        // Window resize listener
        window.addEventListener('resize', function() {
            const isMin = document.body.classList.contains('g-sidenav-minimized');
            updateToggleIcons(isMin);
            if (window.innerWidth >= 1200 && sidenav && sidenav.classList.contains('show')) {
                toggleMobileSidenav(false);
            }
        });
    });

    // AI Chat Global Handler
    const aiAuthToken = "<?= $aiToken ?? ''; ?>";

    function toggleChatFloatingWidget(forceState = null) {
        const widget = document.getElementById('aiChatFloatingWidget');
        const toggleBtn = document.getElementById('aiChatToggleBtn');
        const toggleIcon = document.getElementById('aiChatToggleIcon');
        const chatInput = document.getElementById('global-chat-input');
        const chatBox = document.getElementById('global-chat-box');
        if (!widget) return;

        const shouldOpen = (forceState !== null) ? Boolean(forceState) : !widget.classList.contains('active');

        if (shouldOpen) {
            widget.classList.add('active');
            if (toggleBtn) toggleBtn.classList.add('active');
            if (toggleIcon) {
                toggleIcon.classList.remove('bi-robot');
                toggleIcon.classList.add('bi-x-lg');
            }
            document.body.classList.add('ai-chat-open');
            setTimeout(() => {
                if (chatInput && !chatInput.disabled) chatInput.focus();
                if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
            }, 260);
        } else {
            widget.classList.remove('active');
            if (toggleBtn) toggleBtn.classList.remove('active');
            if (toggleIcon) {
                toggleIcon.classList.remove('bi-x-lg');
                toggleIcon.classList.add('bi-robot');
            }
            document.body.classList.remove('ai-chat-open');
        }
    }
    window.toggleChatFloatingWidget = toggleChatFloatingWidget;

    // Keyboard ESC to close & delegation for any legacy offcanvas triggers
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const widget = document.getElementById('aiChatFloatingWidget');
            if (widget && widget.classList.contains('active')) {
                toggleChatFloatingWidget(false);
            }
        }
    });

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('[data-bs-target="#aiChatOffcanvas"], [data-ai-chat-toggle]');
        if (trigger) {
            e.preventDefault();
            toggleChatFloatingWidget(true);
        }
    });

    function escapeChatHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatInlineMarkdown(text) {
        if (!text) return '';
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code class="bg-light text-danger px-1 py-0.5 rounded small border">$1</code>')
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary fw-medium text-decoration-underline" target="_blank">$1</a>');
    }

    function hasMarkdownTable(text) {
        if (!text) return false;
        return /\|.*\|[\r\n]+\|[-:\s|]+\|[\r\n]+\|.*\|/m.test(text) || /\|.+[\r\n]+\|?\s*[-:]+[-| :]*\|?/.test(text);
    }

    function formatChatTableCell(cellText, colHeader = '') {
        const trimmed = cellText ? cellText.trim() : '';
        if (!trimmed || trimmed === '-' || trimmed === 'NULL') return '<span class="text-muted">-</span>';

        const cleanRaw = trimmed.replace(/\*\*/g, '').replace(/`/g, '');
        const colLower = (colHeader || '').toLowerCase();

        // 1. Status Badges
        if (/^(active|aktif|available|tersedia|ready|paid|lunas|selesai|completed)$/i.test(cleanRaw)) {
            return `<span class="badge-corp badge-corp-success"><i class="bi bi-check-circle-fill me-1"></i>${cleanRaw}</span>`;
        }
        if (/^(inactive|nonaktif|sold out|habis|unpaid|batal|cancelled|gagal)$/i.test(cleanRaw)) {
            return `<span class="badge-corp badge-corp-danger"><i class="bi bi-x-circle-fill me-1"></i>${cleanRaw}</span>`;
        }
        if (/^(pending|menunggu|diproses|processing|dikemas|sisa\s+\d+|menipis)$/i.test(cleanRaw)) {
            return `<span class="badge-corp badge-corp-warning"><i class="bi bi-clock-fill me-1"></i>${cleanRaw}</span>`;
        }

        // 2. User Roles
        if (/^(Admin|Operator|Owner|Customer)$/i.test(cleanRaw)) {
            let badgeClass = 'badge-corp-dark';
            if (/Admin/i.test(cleanRaw)) badgeClass = 'badge-corp-danger';
            else if (/Operator/i.test(cleanRaw)) badgeClass = 'badge-corp-warning';
            else if (/Owner/i.test(cleanRaw)) badgeClass = 'badge-corp-dark';
            else if (/Customer/i.test(cleanRaw)) badgeClass = 'badge-corp-info';
            return `<span class="badge-corp ${badgeClass}">${cleanRaw}</span>`;
        }

        // 3. Phone / WhatsApp
        if (/phone|hp|telepon|wa|whatsapp/i.test(colLower) && /^(\+?62|08)\d+/.test(cleanRaw.replace(/[\s-]/g, ''))) {
            return `<span class="font-monospace text-nowrap"><i class="bi bi-whatsapp text-success me-1"></i>${cleanRaw}</span>`;
        }

        // 4. Email
        if (cleanRaw.includes('@') && cleanRaw.includes('.')) {
            return `<span class="font-monospace text-nowrap text-secondary">${cleanRaw}</span>`;
        }

        // 5. IDs and Numbers
        if (/^#?\d+$/.test(cleanRaw) && /id|no|kode/i.test(colLower)) {
            return `<span class="font-monospace text-muted fw-bold">${cleanRaw}</span>`;
        }

        // 6. Currency / Price
        if (/(rp|\b\d{1,3}(?:[.,\s]\d{3})+(?:\b|\.00))/i.test(cleanRaw) && /harga|total|omzet|subtotal|amount|price/i.test(colLower)) {
            let val = cleanRaw;
            if (!/^rp/i.test(val) && /^\d/.test(val)) val = 'Rp ' + val;
            return `<span class="fw-bold text-dark font-monospace text-nowrap">${val}</span>`;
        }

        return formatInlineMarkdown(cleanRaw);
    }

    function renderChatMarkdown(markdownText) {
        if (!markdownText) return '';

        const text = markdownText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        const lines = text.split('\n');
        const outputBlocks = [];
        let i = 0;

        while (i < lines.length) {
            const line = lines[i];
            const trimmed = line.trim();

            // Table detection
            if (trimmed.includes('|') && i + 1 < lines.length) {
                const nextTrimmed = lines[i + 1].trim();
                const isSeparator = /^\|?(\s*:?-+:?\s*\|)+\s*:?-+:?\s*\|?$/.test(nextTrimmed);

                if (isSeparator) {
                    const rawHeaders = trimmed.split('|').map(c => c.trim());
                    if (trimmed.startsWith('|') && rawHeaders.length > 0 && rawHeaders[0] === '') rawHeaders.shift();
                    if (trimmed.endsWith('|') && rawHeaders.length > 0 && rawHeaders[rawHeaders.length - 1] === '') rawHeaders.pop();
                    const headerCells = rawHeaders;

                    const rawAligns = nextTrimmed.split('|').map(c => c.trim());
                    if (nextTrimmed.startsWith('|') && rawAligns.length > 0 && rawAligns[0] === '') rawAligns.shift();
                    if (nextTrimmed.endsWith('|') && rawAligns.length > 0 && rawAligns[rawAligns.length - 1] === '') rawAligns.pop();

                    const alignments = rawAligns.map((c, idx) => {
                        if (c.startsWith(':') && c.endsWith(':')) return 'center';
                        if (c.endsWith(':')) return 'end';
                        if (c.startsWith(':')) return 'start';
                        const header = (headerCells[idx] || '').toLowerCase();
                        if (/no|id|status|stok|qty|jumlah/i.test(header)) return 'center';
                        if (/harga|total|omzet|price/i.test(header)) return 'end';
                        return 'start';
                    });

                    i += 2;

                    const rows = [];
                    while (i < lines.length && lines[i].trim().includes('|')) {
                        const rowLine = lines[i].trim();
                        if (!rowLine) break;

                        const rawCells = rowLine.split('|').map(c => c.trim());
                        if (rowLine.startsWith('|') && rawCells.length > 0 && rawCells[0] === '') rawCells.shift();
                        if (rowLine.endsWith('|') && rawCells.length > 0 && rawCells[rawCells.length - 1] === '') rawCells.pop();

                        rows.push(rawCells);
                        i++;
                    }

                    let tableHtml = '<div class="chat-table-container my-2">';
                    tableHtml += '<div class="chat-table-header-cue"><span class="fw-semibold"><i class="bi bi-table me-1"></i>Data Tabel</span><span class="text-nowrap" style="font-size: 0.68rem;"><i class="bi bi-arrows-expand me-1"></i>Geser horizontal &rarr;</span></div>';
                    tableHtml += '<table class="table chat-table mb-0"><thead><tr>';
                    headerCells.forEach((th, idx) => {
                        const alignClass = alignments[idx] ? `text-${alignments[idx]}` : 'text-start';
                        tableHtml += `<th class="py-2 px-3 fw-bold ${alignClass}">${formatInlineMarkdown(escapeChatHtml(th))}</th>`;
                    });
                    tableHtml += '</tr></thead><tbody>';

                    rows.forEach(row => {
                        tableHtml += '<tr>';
                        headerCells.forEach((th, idx) => {
                            const cellVal = row[idx] !== undefined ? row[idx] : '';
                            const alignClass = alignments[idx] ? `text-${alignments[idx]}` : 'text-start';
                            tableHtml += `<td class="py-2 px-3 ${alignClass}">${formatChatTableCell(cellVal, th)}</td>`;
                        });
                        tableHtml += '</tr>';
                    });
                    tableHtml += '</tbody></table></div>';

                    outputBlocks.push(tableHtml);
                    continue;
                }
            }

            // Headings
            if (/^###\s+(.+)$/.test(trimmed)) {
                const hText = trimmed.replace(/^###\s+/, '');
                outputBlocks.push(`<h6 class="fw-bold text-dark mt-2 mb-1" style="font-size: 0.95rem;">${formatInlineMarkdown(escapeChatHtml(hText))}</h6>`);
                i++;
                continue;
            }
            if (/^##\s+(.+)$/.test(trimmed)) {
                const hText = trimmed.replace(/^##\s+/, '');
                outputBlocks.push(`<h6 class="fw-bolder text-dark mt-3 mb-1" style="font-size: 1rem;">${formatInlineMarkdown(escapeChatHtml(hText))}</h6>`);
                i++;
                continue;
            }
            if (/^#\s+(.+)$/.test(trimmed)) {
                const hText = trimmed.replace(/^#\s+/, '');
                outputBlocks.push(`<h5 class="fw-bolder text-dark mt-3 mb-2">${formatInlineMarkdown(escapeChatHtml(hText))}</h5>`);
                i++;
                continue;
            }

            // Blockquotes
            if (/^>\s*(.*)$/.test(trimmed)) {
                const bText = trimmed.replace(/^>\s*/, '');
                outputBlocks.push(`<div class="border-start border-3 border-primary ps-2 py-1 text-muted my-1" style="font-size: 0.82rem; background: #f8fafc;">${formatInlineMarkdown(escapeChatHtml(bText))}</div>`);
                i++;
                continue;
            }

            // Bullet lists
            if (/^[\-\*]\s+(.+)$/.test(trimmed)) {
                const lText = trimmed.replace(/^[\-\*]\s+/, '');
                outputBlocks.push(`<div class="d-flex align-items-start my-0.5"><span class="text-primary me-2">&bull;</span><div>${formatInlineMarkdown(escapeChatHtml(lText))}</div></div>`);
                i++;
                continue;
            }

            // Standard line
            if (trimmed === '') {
                outputBlocks.push('<div class="my-1"></div>');
            } else {
                outputBlocks.push(`<div>${formatInlineMarkdown(escapeChatHtml(trimmed))}</div>`);
            }
            i++;
        }

        return outputBlocks.join('');
    }

    function submitQuickChat(question) {
        const input = document.getElementById('global-chat-input');
        const form = document.getElementById('global-chat-form');
        if (input && form) {
            input.value = question;
            form.requestSubmit();
        }
    }

    window.refreshChatConversation = function() {
        const refreshBtn = document.getElementById('btn-refresh-chat');
        const refreshIcon = document.getElementById('icon-refresh-chat');
        const chatBox = document.getElementById('global-chat-box');
        const chatInput = document.getElementById('global-chat-input');
        if (!chatBox) return;

        if (refreshIcon) refreshIcon.classList.add('ai-spin-anim');
        if (refreshBtn) refreshBtn.disabled = true;

        fetch('<?= BASEURL; ?>/assistant/clearHistory', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= CSRF::generateToken(); ?>'
            }
        })
        .then(response => response.json())
        .catch(() => ({}))
        .finally(() => {
            setTimeout(() => {
                chatBox.innerHTML = `
                    <div class="d-flex justify-content-start mb-3" id="global-bot-intro-message">
                        <div class="bg-white text-dark rounded-4 p-3 shadow-xs border" style="max-width: 90%; border-bottom-left-radius: 4px !important;">
                            <div class="text-center mb-0">
                                <span class="fs-2">&#129302;</span>
                                <h6 class="fw-bold mt-1 mb-1">Halo, <?= htmlspecialchars($aiUserName); ?>! 👋</h6>
                                <p class="text-muted small mb-0">Saya nnaco AI Assistant. Saya siap membantu operasional, data stok, pesanan, dan laporan langsung dari database.</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center my-3">
                        <span class="ai-chat-refresh-badge shadow-xs">
                            <i class="bi bi-arrow-clockwise text-primary"></i> Percakapan baru telah dimulai
                        </span>
                    </div>
                `;

                if (refreshIcon) refreshIcon.classList.remove('ai-spin-anim');
                if (refreshBtn) refreshBtn.disabled = false;
                if (chatInput) {
                    chatInput.disabled = false;
                    chatInput.value = '';
                    chatInput.focus();
                }
                chatBox.scrollTop = 0;
            }, 350);
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('global-chat-box');
        const chatForm = document.getElementById('global-chat-form');
        const chatInput = document.getElementById('global-chat-input');
        const submitBtn = chatForm ? chatForm.querySelector('button[type="submit"]') : null;

        function showChatLoading() {
            hideChatLoading();
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'active-chat-loading-bubble';
            loadingDiv.className = 'd-flex justify-content-start mb-3 ai-loading-fade-in';
            loadingDiv.innerHTML = `
                <div class="bg-white text-dark rounded-4 px-3 py-2.5 shadow-xs border d-flex align-items-center" style="max-width: 88%; border-bottom-left-radius: 4px !important; font-size: 0.85rem;">
                    <div class="spinner-grow spinner-grow-sm text-primary me-2 flex-shrink-0" role="status" style="width: 0.75rem; height: 0.75rem;"></div>
                    <span class="text-slate-700 fw-medium small me-2">AI sedang menganalisis data</span>
                    <span class="ai-typing-dots">
                        <span class="ai-dot"></span>
                        <span class="ai-dot"></span>
                        <span class="ai-dot"></span>
                    </span>
                </div>
            `;
            chatBox.appendChild(loadingDiv);
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        }

        function hideChatLoading() {
            const el = document.getElementById('active-chat-loading-bubble');
            if (el) el.remove();
        }

        function setFormLoading(isLoading) {
            if (chatInput) {
                chatInput.disabled = isLoading;
                chatInput.setAttribute('placeholder', isLoading ? 'AI sedang memproses jawaban...' : 'Tanyakan stok, pesanan, atau sistem...');
            }
            if (submitBtn) {
                submitBtn.disabled = isLoading;
                submitBtn.innerHTML = isLoading 
                    ? '<span class="spinner-border spinner-border-sm text-white" role="status" aria-hidden="true" style="width: 1rem; height: 1rem;"></span>' 
                    : '<i class="bi bi-send-fill fs-6"></i>';
            }
        }

        if (chatForm) {
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = chatInput.value.trim();
                if (!message) return;

                appendUserMessage(message);
                chatInput.value = '';
                setFormLoading(true);
                showChatLoading();

                // 1. Coba hubungi microservice AI port 3001
                fetch('http://localhost:3001/api/ai/chat', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + aiAuthToken
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(data => {
                    hideChatLoading();
                    setFormLoading(false);
                    chatInput.focus();

                    if (!data.success && data.error) {
                        appendBotMessage('Terjadi kesalahan: ' + data.error);
                        return;
                    }
                    appendBotMessage(data.reply || 'Data telah diterima.');
                })
                .catch(error => {
                    // 2. Fallback otomatis ke backend PHP jika server Node tidak merespon
                    fetch('<?= BASEURL; ?>/assistant/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= CSRF::generateToken(); ?>'
                        },
                        body: JSON.stringify({ 
                            message: message, 
                            csrf_token: '<?= CSRF::generateToken(); ?>' 
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideChatLoading();
                        setFormLoading(false);
                        chatInput.focus();
                        if (data.reply) {
                            appendBotMessage(data.reply);
                        } else {
                            appendBotMessage(data.error || 'Terjadi kesalahan sistem.');
                        }
                    })
                    .catch(err => {
                        hideChatLoading();
                        setFormLoading(false);
                        chatInput.focus();
                        appendBotMessage('Koneksi ke backend AI chatbot terputus. Pastikan server AI aktif.');
                    });
                });
            });
        }

        function appendUserMessage(text) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-end mb-3 ai-loading-fade-in';
            const safeText = escapeChatHtml(text);
            div.innerHTML = `<div class="bg-slate-900 text-white rounded-4 px-3 py-2 shadow-xs" style="max-width: 85%; border-bottom-right-radius: 4px !important; font-size: 0.875rem;">${safeText}</div>`;
            chatBox.appendChild(div);
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        }

        function appendBotMessage(text) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-start mb-3 ai-loading-fade-in';
            const formatted = renderChatMarkdown(text);
            const isTable = hasMarkdownTable(text);
            const bubbleMaxWidth = isTable ? '100%' : '90%';
            const extraClass = isTable ? 'chat-bubble-has-table' : '';
            div.innerHTML = `<div class="bg-white text-dark rounded-4 px-3 py-2 shadow-xs border ${extraClass}" style="max-width: ${bubbleMaxWidth}; border-bottom-left-radius: 4px !important; font-size: 0.875rem; line-height: 1.5; overflow: hidden;">${formatted}</div>`;
            chatBox.appendChild(div);
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        }
    });
    </script>
</body>
</html>
