<?php
class BlogController extends Controller {
    public function index() {
        $data['title'] = 'Blog & Inspirasi Aksesoris - nnaco_sa';

        // Articles data
        $data['articles'] = [
            [
                'id' => 1,
                'slug' => 'panduan-merawat-perhiasan-titanium-dan-perak',
                'title' => 'Panduan Lengkap Merawat Aksesoris Titanium & Perak Agar Selalu Berkilau',
                'excerpt' => 'Pelajari tips mudah membersihkan dan menyimpan aksesoris favorit Anda agar tidak kusam, anti karat, dan awet bertahun-tahun.',
                'category' => 'Tips & Perawatan',
                'date' => '12 Oktober 2026',
                'read_time' => '4 min read',
                'author' => 'Tim Editor NNACO',
                'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'id' => 2,
                'slug' => 'tren-aksesoris-minimalis-2026',
                'title' => 'Tren Aksesoris Minimalis 2026: Sentuhan Simpel untuk Tampil Elegan Setiap Hari',
                'excerpt' => 'Kombinasi kalung layer tipis dan cincin tipis kini mendominasi gaya kasual hingga profesional. Simak inspirasi padu padannya di sini!',
                'category' => 'Tren Fashion',
                'date' => '08 Oktober 2026',
                'read_time' => '5 min read',
                'author' => 'Fashion Stylist',
                'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'id' => 3,
                'slug' => 'cara-memilih-kalung-sesuai-bentuk-leher',
                'title' => 'Cara Tepat Memilih Model Kalung Sesuai Bentuk Leher dan Kerah Baju',
                'excerpt' => 'Choker, kalung rantai sedang, atau liontin panjang? Cari tahu ukuran dan model kalung yang paling menonjolkan keindahan leher Anda.',
                'category' => 'Panduan Gaya',
                'date' => '29 September 2026',
                'read_time' => '3 min read',
                'author' => 'NNACO Stylist',
                'image' => 'https://images.unsplash.com/photo-1611591475870-5b1eb7285116?q=80&w=800&auto=format&fit=crop'
            ],
            [
                'id' => 4,
                'slug' => 'tips-memilih-kado-perhiasan-untuk-orang-tersayang',
                'title' => '5 Ide Hadiah Aksesoris yang Berkesan dan Bermakna untuk Pasangan',
                'excerpt' => 'Bingung memilih kado ulang tahun atau anniversary? Gelang etnik dan cincin pasangan couple selalu jadi pilihan favorit dengan kesan mendalam.',
                'category' => 'Inspirasi Kado',
                'date' => '20 September 2026',
                'read_time' => '4 min read',
                'author' => 'NNACO Gift Team',
                'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=800&auto=format&fit=crop'
            ]
        ];

        $this->view('templates/header', $data);
        $this->view('blog/index', $data);
        $this->view('templates/footer', $data);
    }
}
