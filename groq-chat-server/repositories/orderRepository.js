const pool = require('../config/db');

class OrderRepository {
    async getCustomerOrders(userId, limit = 5) {
        if (!userId) return [];
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 10);
        
        const sql = `
            SELECT o.id, o.order_date, o.total, o.status,
                   p.payment_method, p.status as payment_status, p.paid_at
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC
            LIMIT ?
        `;
        const [orders] = await pool.query(sql, [userId, safeLimit]);
        if (orders.length === 0) return [];

        const orderIds = orders.map(o => o.id);
        const [items] = await pool.query(`
            SELECT od.order_id, od.quantity, od.price, od.subtotal, p.name as product_name
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id IN (${orderIds.map(() => '?').join(',')})
        `, orderIds);

        const itemsMap = {};
        for (const item of items) {
            if (!itemsMap[item.order_id]) itemsMap[item.order_id] = [];
            itemsMap[item.order_id].push({
                product_name: item.product_name,
                quantity: item.quantity,
                price: Number(item.price),
                subtotal: Number(item.subtotal)
            });
        }

        return orders.map(o => ({
            order_id: o.id,
            order_date: o.order_date,
            total: Number(o.total),
            order_status: o.status,
            payment_method: o.payment_method || 'Belum dipilih',
            payment_status: o.payment_status || 'PENDING',
            paid_at: o.paid_at,
            items: itemsMap[o.id] || []
        }));
    }

    async getCustomerOrderById(userId, orderId) {
        if (!userId || !orderId) return null;
        const sql = `
            SELECT o.id, o.order_date, o.total, o.status,
                   p.payment_method, p.status as payment_status, p.paid_at
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.user_id = ? AND o.id = ?
            LIMIT 1
        `;
        const [rows] = await pool.query(sql, [userId, orderId]);
        if (rows.length === 0) return null;
        const o = rows[0];

        const [items] = await pool.query(`
            SELECT od.quantity, od.price, od.subtotal, p.name as product_name
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?
        `, [o.id]);

        return {
            order_id: o.id,
            order_date: o.order_date,
            total: Number(o.total),
            order_status: o.status,
            payment_method: o.payment_method || 'Belum dipilih',
            payment_status: o.payment_status || 'PENDING',
            paid_at: o.paid_at,
            items: items.map(i => ({
                product_name: i.product_name,
                quantity: i.quantity,
                price: Number(i.price),
                subtotal: Number(i.subtotal)
            }))
        };
    }

    async getOrdersNeedingProcessing(limit = 10) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 20);
        const sql = `
            SELECT o.id, o.user_id, o.order_date, o.total, o.status as order_status,
                   p.payment_method, p.status as payment_status, p.paid_at
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE (p.status = 'PAID' OR o.status IN ('DIPROSES', 'DIKEMAS'))
              AND o.status NOT IN ('SELESAI', 'DIBATALKAN')
            ORDER BY o.order_date ASC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return rows.map(r => ({
            order_id: r.id,
            order_date: r.order_date,
            total: Number(r.total),
            order_status: r.order_status,
            payment_status: r.payment_status || 'UNKNOWN',
            payment_method: r.payment_method || 'N/A',
            paid_at: r.paid_at
        }));
    }

    async getPendingPaymentOrdersCount() {
        const sql = `SELECT COUNT(*) as count FROM orders WHERE status = 'MENUNGGU PEMBAYARAN'`;
        const [rows] = await pool.query(sql);
        return {
            pending_payment_count: Number(rows[0]?.count || 0)
        };
    }

    async getOrderManagementSummary() {
        const sql = `
            SELECT status, COUNT(*) as count, COALESCE(SUM(total), 0) as subtotal
            FROM orders
            GROUP BY status
        `;
        const [rows] = await pool.query(sql);
        const summary = {
            total_orders: 0,
            by_status: {}
        };
        for (const row of rows) {
            summary.by_status[row.status] = {
                count: Number(row.count),
                subtotal: Number(row.subtotal)
            };
            summary.total_orders += Number(row.count);
        }
        return summary;
    }

    async getAllOrders({ status = null, search = null, limit = 10 } = {}) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 25);
        let sql = `
            SELECT o.id, o.order_date, o.total, o.status as order_status,
                   u.name as customer_name, u.email as customer_email,
                   p.payment_method, p.status as payment_status
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON p.order_id = o.id
        `;
        const conditions = [];
        const params = [];
        if (status && typeof status === 'string' && status.trim()) {
            conditions.push(`o.status LIKE ?`);
            params.push(`%${status.trim()}%`);
        }
        if (search && typeof search === 'string' && search.trim()) {
            conditions.push(`(u.name LIKE ? OR u.email LIKE ? OR CAST(o.id AS CHAR) = ?)`);
            const s = `%${search.trim()}%`;
            params.push(s, s, search.trim());
        }
        if (conditions.length > 0) {
            sql += ` WHERE ` + conditions.join(' AND ');
        }
        sql += ` ORDER BY o.order_date DESC LIMIT ?`;
        params.push(safeLimit);

        const [rows] = await pool.query(sql, params);
        return rows.map(r => ({
            order_id: r.id,
            customer_name: r.customer_name || 'Pelanggan',
            customer_email: r.customer_email || '-',
            order_date: r.order_date,
            total: Number(r.total),
            total_formatted: 'Rp ' + Number(r.total).toLocaleString('id-ID'),
            order_status: r.order_status,
            payment_method: r.payment_method || '-',
            payment_status: r.payment_status || 'PENDING'
        }));
    }

    async getOrderDetails(orderId) {
        if (!orderId) return null;
        const sql = `
            SELECT o.id, o.order_date, o.total, o.status as order_status,
                   u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                   p.payment_method, p.status as payment_status, p.paid_at
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.id = ?
            LIMIT 1
        `;
        const [rows] = await pool.query(sql, [orderId]);
        if (rows.length === 0) return null;
        const o = rows[0];

        const [items] = await pool.query(`
            SELECT od.quantity, od.price, od.subtotal, p.name as product_name
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?
        `, [o.id]);

        return {
            order_id: o.id,
            customer_name: o.customer_name || 'Pelanggan',
            customer_email: o.customer_email || '-',
            customer_phone: o.customer_phone || '-',
            order_date: o.order_date,
            total: Number(o.total),
            total_formatted: 'Rp ' + Number(o.total).toLocaleString('id-ID'),
            order_status: o.order_status,
            payment_method: o.payment_method || '-',
            payment_status: o.payment_status || 'PENDING',
            paid_at: o.paid_at,
            items: items.map(i => ({
                product_name: i.product_name,
                quantity: i.quantity,
                price: Number(i.price),
                subtotal: Number(i.subtotal)
            }))
        };
    }
}

module.exports = new OrderRepository();
