<?php
class OperatorController extends Controller {
    public function __construct() {
        Middleware::role('Operator');
    }

    public function index() {
        $data['title'] = 'Dashboard Operator';
        
        $orderModel = $this->model('OrderModel');
        $productModel = $this->model('ProductModel');
        
        $data['new_orders_count'] = $orderModel->countOrdersByStatuses(['Menunggu Pembayaran']);
        $data['processing_orders_count'] = $orderModel->countOrdersByStatuses(['Diproses', 'Dikemas']);
        $data['completed_orders_count'] = $orderModel->countOrdersByStatuses(['Selesai']);
        $data['low_stock_count'] = $productModel->countLowStockProducts(5); // Threshold = 5

        // Data tabel antrean pesanan & stok produk untuk operator
        $data['recent_orders'] = $orderModel->getAllOrdersAdmin('', '', 6, 0);
        $data['low_stock_products'] = $productModel->getLowStockProductsAdmin(5, '', 6, 0);

        $this->view('templates/header', $data);
        $this->view('dashboard/operator', $data);
        $this->view('templates/footer', $data);
    }
}
