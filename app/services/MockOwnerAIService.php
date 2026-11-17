<?php
class MockOwnerAIService implements OwnerAIServiceInterface {
    public function analyzeBusinessQuery($question, $businessData) {
        $q = strtolower($question);
        
        usleep(500000); // Simulate network delay

        // Basic keyword logic for fallback
        if (strpos($q, 'laris') !== false) {
            $top = $businessData['top_selling_products'];
            if (empty($top)) return "Saat ini belum ada data penjualan untuk produk terlaris.";
            $res = "Berdasarkan data saat ini, produk paling laris adalah:<br><ul>";
            foreach($top as $t) {
                $res .= "<li><b>{$t['name']}</b> (Terjual: {$t['total_sold']} unit)</li>";
            }
            $res .= "</ul>";
            return $res;
        }

        if (strpos($q, 'pendapatan') !== false && strpos($q, 'bulan ini') !== false) {
            $rev = number_format($businessData['general_stats']['month_sales'], 0, ',', '.');
            return "Pendapatan bulan ini sejauh ini adalah <b>Rp {$rev}</b>.";
        }

        if (strpos($q, 'stok') !== false || strpos($q, 'habis') !== false) {
            $ls = $businessData['low_stock_products'];
            if (empty($ls)) return "Semua stok produk saat ini dalam keadaan aman (lebih dari batas minimum).";
            $res = "Hati-hati! Beberapa produk ini stoknya hampir habis:<br><ul>";
            foreach($ls as $l) {
                $res .= "<li>{$l['name']} (Sisa: <b>{$l['stock']}</b>)</li>";
            }
            $res .= "</ul>";
            return $res;
        }

        if (strpos($q, 'bandingkan') !== false || strpos($q, 'performa') !== false) {
            $revThisMonth = $businessData['general_stats']['month_sales'];
            $revLastMonth = $businessData['last_month_sales']['revenue'];
            $diff = $revThisMonth - $revLastMonth;
            $status = $diff >= 0 ? "naik" : "turun";
            $diffStr = number_format(abs($diff), 0, ',', '.');
            
            return "Pendapatan bulan lalu adalah Rp " . number_format($revLastMonth, 0, ',', '.') . ". " .
                   "Bulan ini pendapatan Anda Rp " . number_format($revThisMonth, 0, ',', '.') . ". " .
                   "Ini berarti performa Anda <b>{$status}</b> sebesar Rp {$diffStr}.";
        }

        return "Maaf, ini adalah Mock Service. Saya hanya mengerti pertanyaan dasar seperti 'produk laris', 'pendapatan bulan ini', 'stok habis', atau 'performa penjualan'.";
    }
}
