<?php
$replacements = [
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\product\detail.php',
        'search' => '<form action="<?= BASEURL; ?>/cart/add" method="POST" class="d-flex align-items-center gap-3">',
        'replace' => '<form action="<?= BASEURL; ?>/cart/add" method="POST" class="d-flex align-items-center gap-3">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\operator\product\edit.php',
        'search' => '<form action="<?= BASEURL; ?>/operatorproduct/update/<?= $data[\'product\'][\'id\']; ?>" method="POST">',
        'replace' => '<form action="<?= BASEURL; ?>/operatorproduct/update/<?= $data[\'product\'][\'id\']; ?>" method="POST">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\operator\order\show.php',
        'search' => '<form action="<?= BASEURL; ?>/operatororder/updateStatus/<?= $data[\'order\'][\'id\']; ?>" method="POST">',
        'replace' => '<form action="<?= BASEURL; ?>/operatororder/updateStatus/<?= $data[\'order\'][\'id\']; ?>" method="POST">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\checkout\index.php',
        'search' => '<form action="<?= BASEURL; ?>/checkout/process" method="POST">',
        'replace' => '<form action="<?= BASEURL; ?>/checkout/process" method="POST">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\cart\index.php',
        'search' => '<form action="<?= BASEURL; ?>/cart/update" method="post" class="m-0">',
        'replace' => '<form action="<?= BASEURL; ?>/cart/update" method="post" class="m-0">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\auth\register.php',
        'search' => '<form action="<?= BASEURL; ?>/auth/register" method="post">',
        'replace' => '<form action="<?= BASEURL; ?>/auth/register" method="post">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\auth\login.php',
        'search' => '<form action="<?= BASEURL; ?>/auth/login" method="post">',
        'replace' => '<form action="<?= BASEURL; ?>/auth/login" method="post">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\admin\user\show.php',
        'search' => '<form action="<?= BASEURL; ?>/adminuser/update/<?= $data[\'user\'][\'id\']; ?>" method="POST">',
        'replace' => '<form action="<?= BASEURL; ?>/adminuser/update/<?= $data[\'user\'][\'id\']; ?>" method="POST">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\admin\product\edit.php',
        'search' => '<form action="<?= BASEURL; ?>/adminproduct/update/<?= $data[\'product\'][\'id\']; ?>" method="POST" enctype="multipart/form-data">',
        'replace' => '<form action="<?= BASEURL; ?>/adminproduct/update/<?= $data[\'product\'][\'id\']; ?>" method="POST" enctype="multipart/form-data">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\admin\product\create.php',
        'search' => '<form action="<?= BASEURL; ?>/adminproduct/store" method="POST" enctype="multipart/form-data">',
        'replace' => '<form action="<?= BASEURL; ?>/adminproduct/store" method="POST" enctype="multipart/form-data">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\admin\order\show.php',
        'search' => '<form action="<?= BASEURL; ?>/adminorder/updateStatus/<?= $data[\'order\'][\'id\']; ?>" method="POST">',
        'replace' => '<form action="<?= BASEURL; ?>/adminorder/updateStatus/<?= $data[\'order\'][\'id\']; ?>" method="POST">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\admin\category\edit.php',
        'search' => '<form action="<?= BASEURL; ?>/admincategory/update/<?= $data[\'category\'][\'id\']; ?>" method="post">',
        'replace' => '<form action="<?= BASEURL; ?>/admincategory/update/<?= $data[\'category\'][\'id\']; ?>" method="post">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ],
    [
        'file' => 'd:\Data Aplikasi\XAMPP\htdocs\nnaco_sa\app\views\admin\category\create.php',
        'search' => '<form action="<?= BASEURL; ?>/admincategory/store" method="post">',
        'replace' => '<form action="<?= BASEURL; ?>/admincategory/store" method="post">' . "\n" . '<input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">'
    ]
];

foreach ($replacements as $rep) {
    $content = file_get_contents($rep['file']);
    if (strpos($content, 'name="csrf_token"') === false) {
        $content = str_replace($rep['search'], $rep['replace'], $content);
        file_put_contents($rep['file'], $content);
        echo "Injected: " . $rep['file'] . "\n";
    } else {
        echo "Already injected: " . $rep['file'] . "\n";
    }
}
