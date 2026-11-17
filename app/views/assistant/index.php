<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="height: 80vh; display: flex; flex-direction: column;">
                <!-- Header -->
                <div class="card-header bg-slate-900 text-white p-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-white text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-robot fs-5 text-dark"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 font-weight-bold text-white">NNACO AI Assistant</h6>
                            <small class="text-white-50 text-xs">Konsultasi cerdas data stok &amp; rekomendasi produk</small>
                        </div>
                    </div>
                    <a href="<?= BASEURL; ?>/assistant/reset" class="btn btn-sm btn-white-corp mb-0 shadow-xs" onclick="return confirm('Mulai ulang percakapan dengan menghapus riwayat obrolan?');"><i class="bi bi-arrow-clockwise me-1"></i> Refresh Percakapan</a>
                </div>

                <!-- Chat Body -->
                <div class="card-body bg-light overflow-auto p-4" id="chat-box" style="flex: 1; background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23000000\' fill-opacity=\'0.02\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');">
                    <!-- Intro Message if history is empty -->
                    <?php if (empty($data['history'])): ?>
                        <div class="text-center text-muted my-5">
                            <div class="icon icon-shape bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                                <i class="bi bi-robot fs-3"></i>
                            </div>
                            <h6 class="font-weight-bold text-slate-900">Halo! Saya AI Assistant NNACO SA.</h6>
                            <p class="text-xs text-muted">Beri tahu saya apa yang sedang Anda cari.<br><span class="badge badge-corp badge-corp-dark mt-2">Contoh: "Aksesoris kalung silver di bawah 100 ribu"</span></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($data['history'] as $msg): ?>
                            <?php if ($msg['role'] == 'user'): ?>
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="bg-slate-900 text-white rounded-4 px-4 py-2 shadow-xs" style="max-width: 75%; border-bottom-right-radius: 4px !important; font-size: 0.875rem;">
                                        <?= htmlspecialchars($msg['content']); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-start mb-3">
                                    <div class="d-flex flex-column align-items-start w-100" style="max-width: 100%;">
                                        <div class="bg-white text-slate-800 rounded-4 px-4 py-3 shadow-xs border mb-2 w-100" style="border-bottom-left-radius: 4px !important; font-size: 0.875rem;">
                                            <div class="chat-markdown-content"><?= htmlspecialchars(str_replace("\n\n[PROD_CARDS]", "", $msg['content'])); ?></div>
                                        </div>
                                        <?php if (isset($msg['html']) && !empty($msg['html'])): ?>
                                            <div class="w-100 mt-2">
                                                <?= $msg['html']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Footer Input -->
                <div class="card-footer bg-white p-3 border-top shadow-xs">
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control corp-search-input rounded-pill px-3" placeholder="Ketik pesan Anda..." autocomplete="off" required>
                        <button type="submit" class="btn btn-dark-corp rounded-circle shadow-xs" style="width: 42px; height: 42px; display:flex; align-items:center; justify-content:center; flex-shrink: 0;">
                            <i class="bi bi-send-fill text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const submitBtn = chatForm ? chatForm.querySelector('button[type="submit"]') : null;

    function showChatLoading() {
        hideChatLoading();
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'active-page-chat-loading';
        loadingDiv.className = 'd-flex justify-content-start mb-3 ai-loading-fade-in';
        loadingDiv.innerHTML = `
            <div class="bg-white text-dark rounded-4 px-4 py-3 shadow-xs border d-flex align-items-center" style="border-bottom-left-radius: 4px !important; font-size: 0.875rem;">
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
        const el = document.getElementById('active-page-chat-loading');
        if (el) el.remove();
    }

    function setFormLoading(isLoading) {
        if (chatInput) {
            chatInput.disabled = isLoading;
            chatInput.setAttribute('placeholder', isLoading ? 'AI sedang memproses jawaban...' : 'Ketik pesan Anda...');
        }
        if (submitBtn) {
            submitBtn.disabled = isLoading;
            submitBtn.innerHTML = isLoading 
                ? '<span class="spinner-border spinner-border-sm text-white" role="status" aria-hidden="true" style="width: 1rem; height: 1rem;"></span>' 
                : '<i class="bi bi-send-fill text-xs"></i>';
        }
    }

    // Render existing chat history markdown
    document.querySelectorAll('.chat-markdown-content').forEach(function(el) {
        if (typeof renderChatMarkdown === 'function') {
            el.innerHTML = renderChatMarkdown(el.textContent.trim());
        }
    });

    // Scroll to bottom initially
    chatBox.scrollTop = chatBox.scrollHeight;

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;

        // Add user message to UI
        appendUserMessage(message);
        chatInput.value = '';
        setFormLoading(true);
        showChatLoading();

        // Send AJAX request
        fetch('<?= BASEURL; ?>/assistant/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message, csrf_token: '<?= CSRF::generateToken(); ?>' })
        })
        .then(response => response.json())
        .then(data => {
            hideChatLoading();
            setFormLoading(false);
            chatInput.focus();

            if (data.error) {
                appendBotMessage('Terjadi kesalahan sistem. Silakan coba lagi nanti.');
                return;
            }

            appendBotMessage(data.reply, data.products_html);
        })
        .catch(error => {
            console.error('Error:', error);
            hideChatLoading();
            setFormLoading(false);
            chatInput.focus();
            appendBotMessage('Gagal terhubung ke asisten. Periksa koneksi internet Anda.');
        });
    });

    function appendUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-end mb-3 ai-loading-fade-in';
        const safeText = typeof escapeChatHtml === 'function' ? escapeChatHtml(text) : escapeHtml(text);
        div.innerHTML = `<div class="bg-slate-900 text-white rounded-4 px-3 py-2 shadow-xs" style="max-width: 85%; border-bottom-right-radius: 4px !important; font-size: 0.875rem;">${safeText}</div>`;
        chatBox.appendChild(div);
        chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
    }

    function appendBotMessage(text, html = '') {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-start mb-3 ai-loading-fade-in';
        
        let formatted = '';
        const isTable = typeof hasMarkdownTable === 'function' ? hasMarkdownTable(text) : /\|.+[\r\n]+\|?\s*[-:]+[-| :]*\|?/.test(text);

        if (typeof renderChatMarkdown === 'function') {
            formatted = renderChatMarkdown(text);
        } else {
            formatted = escapeHtml(text)
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/^\s*[\-\*]\s+(.*)$/gm, '&bull; $1')
                .replace(/\n/g, '<br>');
        }

        const maxW = isTable ? '100%' : '90%';
        const extraClass = isTable ? 'w-100 chat-bubble-has-table' : '';

        let contentHtml = `<div class="bg-white text-dark rounded-4 px-4 py-3 shadow-xs border mb-2 ${extraClass}" style="max-width: ${maxW}; border-bottom-left-radius: 4px !important; overflow: hidden; font-size: 0.875rem; line-height: 1.5;">${formatted}</div>`;
        if (html) {
            contentHtml += `<div class="w-100 mt-2">${html}</div>`;
        }

        div.innerHTML = `<div class="d-flex flex-column align-items-start ${isTable ? 'w-100' : ''}" style="max-width: ${maxW};">${contentHtml}</div>`;
        chatBox.appendChild(div);
        chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
    }

    function escapeHtml(unsafe) {
        if (typeof escapeChatHtml === 'function') return escapeChatHtml(unsafe);
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }
});
</script>
