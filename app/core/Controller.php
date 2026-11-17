<?php
class Controller {
    public function view($view, $data = []) {
        $path = dirname(__DIR__) . '/views/' . $view . '.php';
        if (file_exists($path)) {
            require_once $path;
        } elseif (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        } else {
            die("View does not exist: " . $view);
        }
    }

    public function model($model) {
        $path = dirname(__DIR__) . '/models/' . $model . '.php';
        if (file_exists($path)) {
            require_once $path;
            return new $model;
        } elseif (file_exists('../app/models/' . $model . '.php')) {
            require_once '../app/models/' . $model . '.php';
            return new $model;
        } else {
            die("Model does not exist: " . $model);
        }
    }

    public function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || isset($_GET['ajax'])
            || isset($_POST['ajax']);
    }
}
