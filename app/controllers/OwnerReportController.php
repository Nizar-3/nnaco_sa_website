<?php
class OwnerReportController extends Controller {
    public function __construct() {
        Middleware::role('Owner');
    }

    public function index() {
        $data['title'] = 'Laporan Penjualan - Owner';
        
        // Default date range: Last 30 days
        $defaultEndDate = date('Y-m-d');
        $defaultStartDate = date('Y-m-d', strtotime('-30 days'));

        $startDate = $_GET['start_date'] ?? $defaultStartDate;
        $endDate = $_GET['end_date'] ?? $defaultEndDate;

        // Ensure dates are valid
        if (strtotime($startDate) > strtotime($endDate)) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $reportModel = $this->model('ReportModel');

        // Get Report Data
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        $data['summary'] = $reportModel->getReportSummary($startDate, $endDate);
        $data['top_products'] = $reportModel->getTopSellingProducts(10, $startDate, $endDate);
        
        // Chart Data (Daily)
        $chartDataRaw = $reportModel->getSalesChartDataDaily($startDate, $endDate);
        
        // Format Data for Chart.js
        $labels = [];
        $revenues = [];
        $transactions = [];

        foreach ($chartDataRaw as $row) {
            $labels[] = date('d M', strtotime($row['sales_date']));
            $revenues[] = (int)$row['revenue'];
            $transactions[] = (int)$row['total_transactions'];
        }

        $data['chart_labels'] = json_encode($labels);
        $data['chart_revenues'] = json_encode($revenues);
        $data['chart_transactions'] = json_encode($transactions);

        if ($this->isAjax()) {
            $this->view('owner/report/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('owner/report/index', $data);
        $this->view('templates/footer', $data);
    }
}
