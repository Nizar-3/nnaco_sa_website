<?php
class AdminController extends Controller {
    public function __construct() {
        Middleware::role('Admin');
    }

    public function index() {
        $data['title'] = 'Dashboard Admin';

        $productModel = $this->model('ProductModel');
        $categoryModel = $this->model('CategoryModel');
        $orderModel = $this->model('OrderModel');
        $userModel = $this->model('UserModel');

        $data['total_products'] = $productModel->getTotalFilteredProducts();
        $data['total_categories'] = count($categoryModel->getAllCategories());
        $data['total_users'] = $userModel->getTotalUsersAdmin();
        
        $data['total_orders'] = $orderModel->getTotalOrdersAdmin();
        $data['recent_orders'] = $orderModel->getAllOrdersAdmin('', '', 5, 0);
        $data['low_stock_products'] = $productModel->getLowStockProductsAdmin(5, '', 5, 0);

        // Chatbot AI stats
        $aiModel = $this->model('AiConversationModel');
        $chatbotStats = $aiModel->getStats();
        $data['total_chatbot_conversations'] = $chatbotStats['total_all'] ?? 0;
        $data['chatbot_today'] = $chatbotStats['total_today'] ?? 0;

        $this->view('templates/header', $data);
        $this->view('dashboard/admin', $data);
        $this->view('templates/footer', $data);
    }

    public function chatbot() {
        header('Location: ' . BASEURL . '/adminchatbot');
        exit;
    }
}
