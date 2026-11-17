<?php
class AboutController extends Controller {
    public function index() {
        $data['title'] = 'Tentang Kami - nnaco_sa';

        $this->view('templates/header', $data);
        $this->view('about/index', $data);
        $this->view('templates/footer', $data);
    }
}
