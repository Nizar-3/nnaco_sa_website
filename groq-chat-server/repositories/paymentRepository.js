const pool = require('../config/db');

class PaymentRepository {
    async getSalesSummary(period = 'this_month') {
        let condition = "p.status = 'PAID'";
        const params = [];

        let periodLabel = 'Bulan Ini';
        if (period === 'today') {
            condition += ' AND DATE(p.paid_at) = CURDATE()';
            periodLabel = 'Hari Ini';
        } else if (period === 'all_time') {
            periodLabel = 'Keseluruhan (All Time)';
        } else {
            // Default: this month
            condition += " AND p.paid_at >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')";
            periodLabel = 'Bulan Berjalan (' + new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' }) + ')';
        }

        const sql = `
            SELECT 
                COUNT(p.id) as total_successful_transactions,
                COALESCE(SUM(p.amount), 0) as total_omzet
            FROM payments p
            WHERE ${condition}
        `;
        const [rows] = await pool.query(sql, params);
        const r = rows[0] || {};

        // Also query all-time for context
        const [allTimeRows] = await pool.query(`
            SELECT 
                COUNT(p.id) as all_time_transactions,
                COALESCE(SUM(p.amount), 0) as all_time_omzet
            FROM payments p
            WHERE p.status = 'PAID'
        `);
        const at = allTimeRows[0] || {};

        return {
            period: periodLabel,
            successful_transactions: Number(r.total_successful_transactions || 0),
            omzet: Number(r.total_omzet || 0),
            omzet_formatted: 'Rp ' + Number(r.total_omzet || 0).toLocaleString('id-ID'),
            all_time_transactions: Number(at.all_time_transactions || 0),
            all_time_omzet: Number(at.all_time_omzet || 0),
            all_time_omzet_formatted: 'Rp ' + Number(at.all_time_omzet || 0).toLocaleString('id-ID')
        };
    }

    async getTopSellingProducts(limit = 5) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 10);
        const sql = `
            SELECT p.id, p.name, 
                   SUM(od.quantity) as total_sold, 
                   SUM(od.subtotal) as total_sales
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            JOIN orders o ON od.order_id = o.id
            JOIN payments pay ON pay.order_id = o.id
            WHERE pay.status = 'PAID'
            GROUP BY p.id, p.name
            ORDER BY total_sold DESC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return rows.map(r => ({
            product_id: r.id,
            product_name: r.name,
            total_sold: Number(r.total_sold),
            total_sales: Number(r.total_sales),
            total_sales_formatted: 'Rp ' + Number(r.total_sales).toLocaleString('id-ID')
        }));
    }

    async getPaymentSummary() {
        const sql = `
            SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount
            FROM payments
            GROUP BY status
        `;
        const [rows] = await pool.query(sql);
        return rows.map(r => ({
            status: r.status,
            count: Number(r.count),
            total_amount: Number(r.total_amount),
            total_amount_formatted: 'Rp ' + Number(r.total_amount).toLocaleString('id-ID')
        }));
    }

    async getAllPayments({ status = null, limit = 10 } = {}) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 25);
        let sql = `
            SELECT p.id, p.order_id, p.payment_method, p.gateway, p.transaction_id,
                   p.amount, p.status, p.paid_at, p.created_at,
                   u.name as customer_name
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
        `;
        const params = [];
        if (status && typeof status === 'string' && status.trim()) {
            sql += ` WHERE p.status = ?`;
            params.push(status.trim().toUpperCase());
        }
        sql += ` ORDER BY p.id DESC LIMIT ?`;
        params.push(safeLimit);

        const [rows] = await pool.query(sql, params);
        return rows.map(r => ({
            payment_id: r.id,
            order_id: r.order_id,
            customer_name: r.customer_name || 'Pelanggan',
            payment_method: r.payment_method || '-',
            amount: Number(r.amount),
            amount_formatted: 'Rp ' + Number(r.amount).toLocaleString('id-ID'),
            status: r.status,
            paid_at: r.paid_at,
            created_at: r.created_at
        }));
    }
}

module.exports = new PaymentRepository();
