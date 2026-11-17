<?php
class ErrorpageController extends Controller {
    public function index($type = '404') {
        if ($type === '403') {
            http_response_code(403);
        } elseif ($type === '404') {
            http_response_code(404);
        } elseif ($type === '500') {
            http_response_code(500);
        }

        $data['title'] = 'Error ' . $type;
        $this->view('templates/header', $data);
        
        if (in_array($type, ['403', '404', '500'])) {
            $this->view('errors/' . $type, $data);
        } else {
            http_response_code(404);
            $this->view('errors/404', $data);
        }
        
        $this->view('templates/footer', $data);
    }
    
    // Add methods for explicit routing if needed
    public function error403() {
        $this->index('403');
    }
    
    public function error404() {
        $this->index('404');
    }
    
    public function error500() {
        $this->index('500');
    }
}
