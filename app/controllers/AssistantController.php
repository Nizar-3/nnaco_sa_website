<?php
class AssistantController extends Controller {
    
    // Default chat interface
    public function index() {
        $data['title'] = 'AI Customer Assistant';
        
        // Initialize chat history if not exists
        if (!isset($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }

        $data['history'] = $_SESSION['chat_history'];

        $this->view('templates/header', $data);
        $this->view('assistant/index', $data);
        $this->view('templates/footer', $data);
    }

    // API Endpoint for AJAX chat
    public function chat() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request method']);
            exit;
        }
        Middleware::verifyCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['error' => 'Message is empty']);
            exit;
        }

        // Cek status aktif chatbot dari konfigurasi sistem
        if (!SettingService::isAiEnabled()) {
            echo json_encode([
                'reply' => 'Layanan asisten AI toko sedang dinonaktifkan sementara oleh administrator.',
                'products_html' => ''
            ]);
            exit;
        }

        // Validasi panjang pesan
        $maxMsgLen = (int) SettingService::get('ai.max_user_message_length', 2000);
        if (mb_strlen($message) > $maxMsgLen) {
            echo json_encode([
                'reply' => "Pesan Anda terlalu panjang. Maksimal {$maxMsgLen} karakter.",
                'products_html' => ''
            ]);
            exit;
        }

        // Initialize history
        if (!isset($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }

        $productModel = $this->model('ProductModel');
        $catalog = $productModel->getCatalogForAI();

        // Ambil provider aktif dari konfigurasi dinamis
        $aiProvider = SettingService::get('ai.provider', 'groq');
        $reply = '';
        $recommendedProductsHtml = '';

        // 1. Coba teruskan ke Microservice Node (port 3001) jika aktif
        $aiAuthSecret = defined('AI_AUTH_SECRET') ? AI_AUTH_SECRET : 'nnaco_secure_ai_hmac_secret_2026_x87b';
        $aiUserId = $_SESSION['user_id'] ?? null;
        $aiUserRole = $_SESSION['user_role'] ?? 'Customer';
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

        $nodeUrl = 'http://127.0.0.1:3001/api/ai/chat';
        $ch = curl_init($nodeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $aiToken
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 300);
        $nodeRes = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($nodeRes)) {
            $parsedNode = json_decode($nodeRes, true);
            if (!empty($parsedNode['reply'])) {
                // Save to history & persist
                $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $message];
                $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $parsedNode['reply'], 'html' => ''];
                echo json_encode([
                    'reply' => $parsedNode['reply'],
                    'products_html' => ''
                ]);
                exit;
            }
        }

        // 2. Direct fast-path database & user identity handling in PHP
        $lowerMsg = strtolower(trim($message, " \t\n\r\0\x0B.?"));

        // 2. Database Awareness & Role-Based Access Control (RBAC) Layer
        require_once dirname(__DIR__) . '/services/AIDatabaseService.php';
        $aiDbService = new AIDatabaseService();
        $dbIntent = $aiDbService->detectIntent($message, $aiUserRole, $aiUserId);

        if ($dbIntent !== null) {
            if (isset($dbIntent['type']) && $dbIntent['type'] === 'direct_reply') {
                $reply = $dbIntent['reply'];
            } elseif (!empty($dbIntent['toolName'])) {
                $userContext = ['id' => $aiUserId, 'role' => $aiUserRole, 'name' => $aiUserName, 'email' => $aiUserEmail];
                $toolResult = $aiDbService->executeTool($dbIntent['toolName'], $dbIntent['args'] ?? [], $userContext);

                if (!$toolResult['success']) {
                    // RBAC denial message atau query failure
                    $reply = $toolResult['error'] ?? 'Maaf, Anda tidak memiliki izin untuk mengakses data tersebut.';
                } else {
                    $reply = $aiDbService->formatDirectDataResponse($dbIntent['toolName'], $toolResult, $message);
                }
            }
        }

        // Identitas bot asisten (siapa anda / kamu siapa / info bot)
        if (empty($reply) && in_array($lowerMsg, ['siapa anda', 'siapa kamu', 'anda siapa', 'kamu siapa', 'siapa bot', 'siapa ai', 'kamu bot apa', 'identitas anda', 'identitas kamu'], true)) {
            $asstName = SettingService::get('ai.assistant_name', 'nnaco AI Assistant');
            $reply = "Halo! 👋 Saya adalah **{$asstName}**, asisten virtual cerdas untuk website e-commerce **nnaco_sa**.\n\n"
                   . "Saya siap membantu Anda seputar:\n"
                   . "- 💎 **Katalog & Produk**: Rekomendasi kalung, gelang, cincin, anting, dan jepit rambut.\n"
                   . "- 📦 **Stok & Inventaris**: Memeriksa ketersediaan dan harga perhiasan terkini.\n"
                   . "- 🛒 **Pesanan & Pengiriman**: Melacak status pesanan, pembayaran, dan ekspedisi.\n"
                   . "- 🛡️ **Bantuan Akun & Database**: Menjawab data operasional toko sesuai hak akses (role) Anda.";
        }

        // Filter Pertanyaan Melenceng dari Konteks Sistem
        if (empty($reply)) {
            $outOfContextPatterns = [
                'buatkan kode', 'tulis kode', 'buat script', 'write code', 'def ', 'function()', 'console.log',
                'siapa presiden', 'ibu kota', 'jarak bumi', 'teori relativitas', 'rumus ', 'tugas sekolah',
                'pr sekolah', 'soal ujian', 'rekomendasi film', 'cuaca hari ini', 'ramalan cuaca', 'resep ',
                'cara masak', 'jadwal bola', 'skor bola', 'siapa messi', 'siapa ronaldo', 'buatkan puisi',
                'pantun', 'cerpen', 'dongeng'
            ];
            foreach ($outOfContextPatterns as $pat) {
                if (str_contains($lowerMsg, $pat)) {
                    $reply = "Maaf, saya adalah asisten toko aksesoris nnaco_sa. Saya hanya melayani pertanyaan seputar sistem, toko, dan database nnaco_sa.";
                    break;
                }
            }
        }

        // 3. Jika belum terjawab oleh fast-path, panggil AI Provider aktif dengan User Context & RBAC
        if (empty($reply)) {
            $userContext = ['id' => $aiUserId, 'role' => $aiUserRole, 'name' => $aiUserName, 'email' => $aiUserEmail];

            if ($aiProvider === 'groq') {
                $groqService = new GroqAIService();
                $aiResponse = $groqService->generateResponse($message, $_SESSION['chat_history'], $catalog, $userContext);
                $reply = $aiResponse['reply'] ?? '';
                if (!empty($aiResponse['recommended_product_ids']) && is_array($aiResponse['recommended_product_ids'])) {
                    $products = $productModel->getProductsByIds($aiResponse['recommended_product_ids']);
                    if (!empty($products)) {
                        $recommendedProductsHtml = $this->renderProductCards($products);
                    }
                }

                // Jika Groq gagal merespon sama sekali, fallback ke Mock
                if (empty($reply)) {
                    $aiService = new MockAIService();
                    $fallback = $aiService->generateResponse($message, $_SESSION['chat_history'], $catalog, $userContext);
                    $reply = $fallback['reply'] ?? 'Maaf, terjadi kendala saat memproses jawaban AI.';
                }
            } elseif ($aiProvider === 'gemini') {
                $aiService = new GeminiAIService();
                $aiResponse = $aiService->generateResponse($message, $_SESSION['chat_history'], $catalog, $userContext);
                $reply = $aiResponse['reply'] ?? '';
                if (!empty($aiResponse['recommended_product_ids']) && is_array($aiResponse['recommended_product_ids'])) {
                    $products = $productModel->getProductsByIds($aiResponse['recommended_product_ids']);
                    if (!empty($products)) {
                        $recommendedProductsHtml = $this->renderProductCards($products);
                    }
                }

                // Jika Gemini gagal merespon sama sekali, fallback ke Mock
                if (empty($reply)) {
                    $aiService = new MockAIService();
                    $fallback = $aiService->generateResponse($message, $_SESSION['chat_history'], $catalog, $userContext);
                    $reply = $fallback['reply'] ?? 'Maaf, terjadi kendala saat memproses jawaban AI.';
                }
            } else {
                $aiService = new MockAIService();
                $aiResponse = $aiService->generateResponse($message, $_SESSION['chat_history'], $catalog, $userContext);
                $reply = $aiResponse['reply'] ?? '';
            }
        }

        // Save to history
        $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $message];
        
        $assistantContent = $reply;
        if ($recommendedProductsHtml) {
            $assistantContent .= "\n\n[PROD_CARDS]";
        }
        
        $_SESSION['chat_history'][] = [
            'role' => 'assistant', 
            'content' => $assistantContent,
            'html' => $recommendedProductsHtml
        ];

        // Ensure history doesn't grow too large (limit to dynamic max_history_messages * 2)
        $maxHistory = (int) SettingService::get('ai.max_history_messages', 10);
        $maxSlots = max(4, $maxHistory * 2);
        if (count($_SESSION['chat_history']) > $maxSlots) {
            $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -$maxSlots);
        }

        // Persist percakapan ke database untuk riwayat/monitoring Admin
        try {
            $aiConvModel = new AiConversationModel();
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $aiConvModel->recordConversation($userId, $message, $reply);
        } catch (\Throwable $e) {
            error_log('Failed to save AI conversation history: ' . $e->getMessage());
        }

        echo json_encode([
            'reply' => $reply,
            'products_html' => $recommendedProductsHtml
        ]);
        exit;
    }

    // Reset Chat History
    public function reset() {
        $_SESSION['chat_history'] = [];
        header('Location: ' . BASEURL . '/assistant');
        exit;
    }

    // API to clear / refresh chat history via AJAX
    public function clearHistory() {
        header('Content-Type: application/json');
        $_SESSION['chat_history'] = [];
        echo json_encode(['success' => true, 'message' => 'Percakapan berhasil di-refresh.']);
        exit;
    }

    // Helper method to render product cards HTML
    private function renderProductCards($products) {
        $html = '<div class="row mt-3 g-2">';
        foreach ($products as $p) {
            $img = $p['image'] ? BASEURL . '/img/products/' . $p['image'] : 'https://via.placeholder.com/150';
            $price = number_format($p['price'], 0, ',', '.');
            $detailUrl = BASEURL . '/product/detail/' . $p['id'];
            $name = htmlspecialchars($p['name']);
            
            $html .= "
            <div class='col-6 col-md-4'>
                <div class='card h-100 shadow-sm border-0'>
                    <img src='{$img}' class='card-img-top' alt='{$name}' style='height: 120px; object-fit: cover;'>
                    <div class='card-body p-2 d-flex flex-column'>
                        <h6 class='card-title mb-1' style='font-size: 0.9rem;'>{$name}</h6>
                        <p class='card-text text-danger fw-bold mb-2' style='font-size: 0.85rem;'>Rp {$price}</p>
                        <a href='{$detailUrl}' class='btn btn-sm btn-outline-primary mt-auto'>Lihat</a>
                    </div>
                </div>
            </div>";
        }
        $html .= '</div>';
        return $html;
    }
}
