<!-- Corporate UI Dashboard: Owner Financial Report -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Laporan Penjualan & Finansial</h3>
                <p class="text-secondary text-sm mb-0">Analisis metrik pendapatan berkala, volume transaksi, dan tren penjualan.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/owner" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard Owner
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ajax-table-wrapper">
    <!-- Date Filter Card -->
    <div class="card shadow-xs border mb-4">
        <div class="card-body p-3">
            <form action="<?= BASEURL; ?>/ownerreport" method="GET" class="ajax-filter-form row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label text-xs font-weight-semibold text-dark mb-1">
                        <i class="bi bi-calendar-event me-1"></i> Dari Tanggal
                    </label>
                    <input type="date" class="form-control corp-search-input" id="start_date" name="start_date" value="<?= htmlspecialchars($data['start_date']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label text-xs font-weight-semibold text-dark mb-1">
                        <i class="bi bi-calendar-check me-1"></i> Sampai Tanggal
                    </label>
                    <input type="date" class="form-control corp-search-input" id="end_date" name="end_date" value="<?= htmlspecialchars($data['end_date']); ?>" required>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark-corp flex-grow-1 py-2 font-weight-bold mb-0">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                    <a href="<?= BASEURL; ?>/ownerreport" class="btn btn-white-corp py-2 mb-0 btn-reset-filter" data-ajax-reset title="Reset ke 30 hari terakhir">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="row g-3 mb-4 ajax-summary-cards">
        <div class="col-md-6">
            <div class="stat-card-corporate">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-title">Total Omset / Pendapatan</p>
                        <h3 class="stat-value text-dark" style="font-size: 1.85rem;">Rp <?= number_format($data['summary']['revenue'] ?? 0, 0, ',', '.'); ?></h3>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-corp badge-corp-success">Periode Terpilih</span>
                            <span class="text-xs text-muted">Dari pesanan berstatus Selesai</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-slate-900 text-white shadow-sm">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="stat-card-corporate">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-title">Jumlah Transaksi Sukses</p>
                        <h3 class="stat-value text-dark" style="font-size: 1.85rem;"><?= number_format($data['summary']['transactions'] ?? 0); ?> Transaksi</h3>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-corp badge-corp-info">Order Lunas</span>
                            <span class="text-xs text-muted">Barang berhasil terkirim</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary text-white shadow-sm">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden holder for dynamic chart data -->
    <div id="chart-data-holder" class="d-none" 
         data-labels='<?= htmlspecialchars($data['chart_labels'], ENT_QUOTES, 'UTF-8'); ?>' 
         data-revenues='<?= htmlspecialchars($data['chart_revenues'], ENT_QUOTES, 'UTF-8'); ?>'>
    </div>

    <!-- Sales Trend Chart Card -->
    <div class="card shadow-xs border mb-4">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h6 class="font-weight-semibold text-dark mb-0">Grafik Tren Penjualan Harian</h6>
                <p class="text-xs text-secondary mb-0">Visualisasi pendapatan per hari pada rentang tanggal terpilih</p>
            </div>
            <span class="badge badge-corp badge-corp-dark">IDR Revenue</span>
        </div>
        <div class="card-body p-4">
            <canvas id="salesChart" height="90"></canvas>
        </div>
    </div>

    <!-- Top Products Table Card -->
    <div class="card shadow-xs border">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h6 class="font-weight-semibold text-dark mb-0">Produk Terlaris pada Periode Ini</h6>
                <p class="text-xs text-secondary mb-0">Peringkat kontribusi produk terhadap total penjualan</p>
            </div>
            <div>
                <span class="badge badge-corp badge-corp-dark ajax-count-badge">Top <?= count($data['top_products'] ?? []); ?> Produk</span>
            </div>
        </div>
        <div class="card-body px-0 py-0">
            <div class="table-responsive ajax-table-content position-relative">
                <table class="table table-corporate align-items-center mb-0">
                    <thead>
                        <tr>
                            <th width="80" class="ps-4">Peringkat</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Kuantitas Terjual</th>
                            <th class="text-end pe-4">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['top_products'])): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-graph-down fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada data transaksi penjualan pada rentang periode ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data['top_products'] as $tp): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="avatar avatar-xs rounded-circle <?= ($no <= 3) ? 'bg-slate-900 text-white' : 'bg-gray-100 text-dark'; ?> d-inline-flex justify-content-center align-items-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                            <?= $no++; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark text-sm"><?= htmlspecialchars($tp['name']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-corp badge-corp-info"><?= $tp['total_sold']; ?> unit</span>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-dark text-sm">
                                        Rp <?= number_format($tp['total_revenue'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Setup & AJAX Live Update -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    let salesChartInstance = null;

    function renderOrUpdateChart(labels, revenues) {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const lineColor = isDark ? '#8b5cf6' : '#0f172a';
        const areaColor = isDark ? 'rgba(139, 92, 246, 0.18)' : 'rgba(15, 23, 42, 0.08)';
        const pointColor = isDark ? '#a78bfa' : '#774dd3';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.04)';
        const tickColor = isDark ? '#94a3b8' : '#64748b';
        const textColor = isDark ? '#f8fafc' : '#0f172a';
        const tooltipBg = isDark ? '#1e293b' : '#0f172a';

        if (salesChartInstance) {
            salesChartInstance.data.labels = labels;
            salesChartInstance.data.datasets[0].data = revenues;
            salesChartInstance.data.datasets[0].borderColor = lineColor;
            salesChartInstance.data.datasets[0].backgroundColor = areaColor;
            salesChartInstance.data.datasets[0].pointBackgroundColor = pointColor;
            salesChartInstance.options.scales.x.ticks.color = tickColor;
            salesChartInstance.options.scales.y.ticks.color = tickColor;
            salesChartInstance.options.scales.y.grid.color = gridColor;
            salesChartInstance.options.plugins.legend.labels.color = textColor;
            salesChartInstance.options.plugins.tooltip.backgroundColor = tooltipBg;
            salesChartInstance.update('active');
            return;
        }

        salesChartInstance = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: revenues,
                    borderColor: lineColor,
                    backgroundColor: areaColor,
                    borderWidth: 2.5,
                    pointBackgroundColor: pointColor,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            color: textColor,
                            font: { family: "'Open Sans', sans-serif", size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: tooltipBg,
                        padding: 12,
                        titleFont: { size: 13, family: "'Open Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Open Sans', sans-serif" },
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: tickColor, font: { family: "'Open Sans', sans-serif", size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor,
                            drawBorder: false
                        },
                        ticks: {
                            color: tickColor,
                            font: { family: "'Open Sans', sans-serif", size: 11 },
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function initChartFromHolder(docRoot = document) {
        const holder = docRoot.getElementById('chart-data-holder') || docRoot.querySelector('#chart-data-holder');
        if (!holder) return;
        try {
            const labels = JSON.parse(holder.getAttribute('data-labels') || '[]');
            const revenues = JSON.parse(holder.getAttribute('data-revenues') || '[]');
            renderOrUpdateChart(labels, revenues);
        } catch (e) {
            console.error('Failed to parse chart data:', e);
        }
    }

    // Initial render
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initChartFromHolder(document));
    } else {
        initChartFromHolder(document);
    }

    // Listen to AJAX filter updates
    document.addEventListener('ajaxTableUpdated', function(e) {
        if (e.detail && e.detail.doc) {
            initChartFromHolder(e.detail.doc);
        }
    });

    // Listen to Theme changes (Light / Dark)
    window.addEventListener('nnaco:themechange', function() {
        if (salesChartInstance) {
            salesChartInstance.destroy();
            salesChartInstance = null;
        }
        initChartFromHolder(document);
    });
})();
</script>
