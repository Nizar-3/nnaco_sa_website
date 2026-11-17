<?php
require_once __DIR__ . '/OwnerAIServiceInterface.php';
require_once __DIR__ . '/SettingService.php';

class GeminiOwnerAIService implements OwnerAIServiceInterface {
    public function analyzeBusinessQuery($question, $businessData) {
        $apiKey = SettingService::getSecret('ai.api_key');
        if (empty($apiKey) && defined('GEMINI_API_KEY')) {
            $apiKey = GEMINI_API_KEY;
        }

        if (empty($apiKey)) {
            return "Sistem AI sedang offline (API Key tidak ditemukan). Silakan cek Dashboard secara manual atau konfigurasi di Pengaturan Sistem.";
        }

        $model = SettingService::get('ai.model', 'gemini-3.5-flash-lite');
        $model = str_replace('models/', '', $model);
        if ($model === 'gemini-1.5-flash' || empty($model)) {
            $model = 'gemini-3.5-flash-lite';
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($model) . ':generateContent?key=' . urlencode($apiKey);

        $systemInstruction = "You are a professional Business Assistant AI for the Owner of NNACO SA. " .
            "Your task is to analyze the provided business data and answer the owner's questions accurately. " .
            "Do NOT make up any numbers. Base all your answers ONLY on the provided JSON data. " .
            "Respond in Indonesian. Format your response using clean Markdown or basic HTML tags (like <b>, <ul>, <li>, <br>) for readability. " .
            "\n\nBUSINESS DATA:\n" . json_encode($businessData);

        $data = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $question]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            $responseData = json_decode($response, true);
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return $responseData['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        return "Maaf, AI gagal memproses data analitik saat ini. Silakan coba sesaat lagi.";
    }
}
