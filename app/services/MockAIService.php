<?php
/**
 * MockAIService.php
 * Layanan Simulasi AI Lokal (Offline) untuk Sistem nnaco_sa
 *
 * Mampu membaca seluruh basis data lokal secara langsung, menjalankan kueri lintas tabel,
 * menerapkan batasan hak akses role (RBAC & RLS) secara ketat, dan menjawab langsung apa yang ditanya
 * tanpa memerlukan kuota eksternal atau koneksi internet.
 */

require_once __DIR__ . '/AIServiceInterface.php';
require_once __DIR__ . '/AIDatabaseService.php';
require_once __DIR__ . '/AIRolePermissionService.php';

class MockAIService implements AIServiceInterface {
    
    public function generateResponse($userMessage, $chatHistory, $productCatalog, array $userContext = []) {
        $role = $userContext['role'] ?? ($_SESSION['user_role'] ?? 'Customer');
        $userId = $userContext['id'] ?? ($_SESSION['user_id'] ?? null);
        $userName = $userContext['name'] ?? ($_SESSION['user_name'] ?? 'Tamu');
        $userEmail = $userContext['email'] ?? ($_SESSION['user_email'] ?? '');
        $uCtx = ['id' => $userId, 'role' => $role, 'name' => $userName, 'email' => $userEmail];

        $aiDbService = new AIDatabaseService();

        // 1. Deteksi intent database (Omzet, Stok, Pesanan, Pembayaran, Pengguna, Profil)
        $dbIntent = $aiDbService->detectIntent($userMessage, $role, $userId);
        if ($dbIntent !== null) {
            if (isset($dbIntent['type']) && $dbIntent['type'] === 'direct_reply') {
                return ['reply' => $dbIntent['reply'], 'recommended_product_ids' => []];
            }
            if (!empty($dbIntent['toolName'])) {
                $toolResult = $aiDbService->executeTool($dbIntent['toolName'], $dbIntent['args'] ?? [], $uCtx);
                if (!$toolResult['success']) {
                    return [
                        'reply' => $toolResult['error'] ?? 'Maaf, Anda tidak memiliki izin untuk mengakses data tersebut.',
                        'recommended_product_ids' => []
                    ];
                }
                $directReply = $aiDbService->formatDirectDataResponse($dbIntent['toolName'], $toolResult, $userMessage);
                return ['reply' => $directReply, 'recommended_product_ids' => []];
            }
        }

        // 2. Pencarian Produk di Database Lokal Berdasarkan Kata Kunci
        $msg = strtolower($userMessage);
        $recommendedIds = [];

        // Pencarian spesifik: murah / budget
        if (str_contains($msg, 'murah') || str_contains($msg, 'budget') || str_contains($msg, 'terjangkau')) {
            $toolResult = $aiDbService->executeTool('getCheapestProduct', [], $uCtx);
            if ($toolResult['success'] && !empty($toolResult['data'])) {
                $p = $toolResult['data'];
                $prc = number_format($p['price'], 0, ',', '.');
                return [
                    'reply' => "Produk paling terjangkau di katalog kami adalah **{$p['name']}** seharga **Rp {$prc}** (Kategori: **{$p['category_name']}**, Sisa Stok: **{$p['stock']} unit**).",
                    'recommended_product_ids' => [(int)$p['id']]
                ];
            }
        }

        // Pencarian produk berdasarkan query kata kunci
        $searchResult = $aiDbService->executeTool('searchProducts', ['query' => $userMessage], $uCtx);
        if ($searchResult['success'] && !empty($searchResult['data'])) {
            $products = $searchResult['data'];
            $recIds = [];
            foreach (array_slice($products, 0, 3) as $p) {
                $recIds[] = (int)$p['id'];
            }
            $formatted = $aiDbService->formatDirectDataResponse('searchProducts', $searchResult, $userMessage);
            return [
                'reply' => $formatted,
                'recommended_product_ids' => $recIds
            ];
        }

        // 3. Sapaan Ramah / Pembuka
        if (in_array($msg, ['halo', 'hai', 'hello', 'hi', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'p'], true)) {
            $reply = "Halo {$userName}! 👋 Selamat datang di **nnaco_sa**.\n\n"
                   . "Saya nnaco AI Assistant (Mode Simulasi Lokal). Saya siap membantu Anda seputar:\n"
                   . "- 💎 **Katalog & Rekomendasi**: Menemukan kalung, cincin, gelang, dan anting terbaik.\n"
                   . "- 📦 **Stok & Inventaris**: Memeriksa ketersediaan produk.\n"
                   . "- 🛒 **Status Pesanan**: Melacak pesanan belanja Anda.\n"
                   . "- 📊 **Informasi Toko**: Menjawab data operasional sesuai hak akses ({$role}) Anda.\n\n"
                   . "Ada yang bisa saya bantu?";
            return ['reply' => $reply, 'recommended_product_ids' => []];
        }

        // Default direct response
        return [
            'reply' => "Saya memahami Anda menanyakan perihal: \"{$userMessage}\". Anda dapat menanyakan produk tertentu (misal: 'stok kalung', 'produk termurah'), status pesanan Anda, atau ketik 'siapa saya' untuk melihat profil akun Anda.",
            'recommended_product_ids' => []
        ];
    }
}
