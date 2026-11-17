<?php
class OwnerController extends Controller {
    public function __construct() {
        Middleware::role('Owner');
    }

    public function index() {
        $data['title'] = 'Dashboard Owner';
        
        $reportModel = $this->model('ReportModel');
        $paymentModel = $this->model('PaymentModel');
        $orderModel = $this->model('OrderModel');
        
        // General Stats
        $data['stats'] = $reportModel->getDashboardStats();
        
        // Tables Data
        $data['top_products'] = $reportModel->getTopSellingProducts(5);
        $data['low_stock'] = $reportModel->getLowStockProducts(5);
        $data['recent_orders'] = $orderModel->getAllOrdersAdmin('', '', 5, 0);

        // Payment Statistics
        $data['payment_stats'] = $paymentModel->getPaymentStatistics();
        $data['total_revenue'] = $paymentModel->getTotalRevenue();
        $data['pending_payments'] = $paymentModel->countPaymentsByStatus('PENDING');
        $data['failed_payments'] = $paymentModel->countPaymentsByStatus('FAILED');

        $this->view('templates/header', $data);
        $this->view('dashboard/owner', $data);
        $this->view('templates/footer', $data);
    }

    public function askAI() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/owner');
            exit;
        }
        Middleware::verifyCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        $question = trim($input['question'] ?? '');

        if (empty($question)) {
            echo json_encode(['error' => 'Pertanyaan tidak boleh kosong.']);
            exit;
        }

        // Fetch Business Context safely
        $reportModel = $this->model('ReportModel');
        $businessData = $reportModel->getBusinessContextForAI();

        // Instantiate AI Service based on config
        require_once dirname(__DIR__) . '/services/SettingService.php';
        $aiProvider = SettingService::get('ai.provider', defined('AI_PROVIDER') ? AI_PROVIDER : 'gemini');
        if ($aiProvider === 'gemini') {
            $aiService = new GeminiOwnerAIService();
        } else {
            $aiService = new MockOwnerAIService();
        }

        $answer = $aiService->analyzeBusinessQuery($question, $businessData);

        echo json_encode(['answer' => $answer]);
        exit;
    }
}
