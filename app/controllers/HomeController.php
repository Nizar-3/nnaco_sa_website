<?php
class HomeController extends Controller {
    public function index() {
        $data['title'] = 'Toko Aksesoris nnaco_sa';
        
        // Mengambil 4 produk terbaru untuk ditampilkan di halaman depan
        $data['latest_products'] = $this->model('ProductModel')->getFilteredProducts('', '', '', '', 4, 0);

        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer', $data);
    }
}
