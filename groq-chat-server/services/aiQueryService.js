/**
 * aiQueryService.js
 * Parameterized Database Access Layer & Business Analytics Service
 * Executes verified, read-only parameterized queries with Row-Level Security (RLS)
 * and data sanitization for nnaco_sa AI Chatbot.
 */

const pool = require('../config/db');
const rolePermissionService = require('./rolePermissionService');
const databaseSchemaService = require('./databaseSchemaService');

class AiQueryService {
    // ==========================================
    // A. PRODUK & KATEGORI ANALYTICS
    // ==========================================

    async getAvailableProducts(limit = 15) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 15, 1), 30);
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'Available' AND p.stock > 0
            ORDER BY p.price ASC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return {
            success: true,
            count: rows.length,
            data: rows
        };
    }

    async getProductPrice(productName) {
        if (!productName) return { success: false, error: 'Nama produk harus disertakan.' };
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ?
            LIMIT 3
        `;
        const [rows] = await pool.query(sql, [`%${productName.trim()}%`]);
        if (rows.length === 0) {
            return {
                success: true,
                found: false,
                message: `Produk "${productName}" tidak ditemukan di katalog toko.`
            };
        }
        return {
            success: true,
            found: true,
            count: rows.length,
            data: rows
        };
    }

    async getMostExpensiveProduct() {
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.price DESC
            LIMIT 1
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            found: rows.length > 0,
            data: rows[0] || null
        };
    }

    async getCheapestProduct(categoryName = null) {
        let sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'Available' AND p.stock > 0
        `;
        const params = [];
        if (categoryName) {
            sql += ` AND c.name LIKE ?`;
            params.push(`%${categoryName.trim()}%`);
        }
        sql += ` ORDER BY p.price ASC LIMIT 1`;

        const [rows] = await pool.query(sql, params);
        return {
            success: true,
            found: rows.length > 0,
            data: rows[0] || null
        };
    }

    async getPopularProducts(limit = 5) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 10);
        // Combine sales quantity from order_details and reviews rating
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock,
                   COALESCE(SUM(od.quantity), 0) as total_sold,
                   COALESCE(AVG(r.rating), 0) as avg_rating,
                   COUNT(DISTINCT r.id) as review_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN order_details od ON od.product_id = p.id
            LEFT JOIN reviews r ON r.product_id = p.id
            WHERE p.status = 'Available'
            GROUP BY p.id, p.name, c.name, p.price, p.stock
            ORDER BY total_sold DESC, avg_rating DESC, p.stock DESC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return {
            success: true,
            count: rows.length,
            data: rows
        };
    }

    async getProductCountByCategory() {
        const sql = `
            SELECT c.id, c.name as category_name, c.description,
                   COUNT(p.id) as total_products,
                   COALESCE(SUM(CASE WHEN p.status = 'Available' AND p.stock > 0 THEN 1 ELSE 0 END), 0) as available_products,
                   COALESCE(SUM(p.stock), 0) as total_stock
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id, c.name, c.description
            ORDER BY c.id ASC
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows
        };
    }

    async getLowStockProducts(threshold = 5, limit = 10) {
        const safeThreshold = Math.max(parseInt(threshold, 10) || 5, 1);
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 20);
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.stock <= ? AND p.stock > 0 AND p.status = 'Available'
            ORDER BY p.stock ASC, p.name ASC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeThreshold, safeLimit]);
        return {
            success: true,
            threshold: safeThreshold,
            count: rows.length,
            data: rows
        };
    }

    async getOutOfStockProducts(limit = 10) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 20);
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.stock = 0 OR p.status = 'Out_of_Stock'
            ORDER BY p.name ASC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return {
            success: true,
            count: rows.length,
            data: rows
        };
    }

    async checkProductStock(productName) {
        if (!productName) return { success: false, error: 'Nama produk harus disertakan.' };
        const sql = `
            SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ?
            LIMIT 3
        `;
        const [rows] = await pool.query(sql, [`%${productName.trim()}%`]);
        if (rows.length === 0) {
            return {
                success: true,
                found: false,
                message: `Produk "${productName}" tidak ditemukan di database nnaco_sa.`
            };
        }
        return {
            success: true,
            found: true,
            data: rows
        };
    }

    // ==========================================
    // B. STOK & INVENTARIS ANALYTICS
    // ==========================================

    async getTotalStock() {
        const sql = `
            SELECT 
                COUNT(*) as total_product_items,
                COALESCE(SUM(stock), 0) as total_stock_units,
                COALESCE(SUM(CASE WHEN stock > 0 AND status = 'Available' THEN 1 ELSE 0 END), 0) as in_stock_count,
                COALESCE(SUM(CASE WHEN stock <= 5 AND stock > 0 AND status = 'Available' THEN 1 ELSE 0 END), 0) as low_stock_count,
                COALESCE(SUM(CASE WHEN stock = 0 OR status = 'Out_of_Stock' THEN 1 ELSE 0 END), 0) as out_of_stock_count
            FROM products
        `;
        const [rows] = await pool.query(sql);
        const r = rows[0] || {};
        return {
            success: true,
            data: {
                total_product_items: Number(r.total_product_items || 0),
                total_stock_units: Number(r.total_stock_units || 0),
                in_stock_count: Number(r.in_stock_count || 0),
                low_stock_count: Number(r.low_stock_count || 0),
                out_of_stock_count: Number(r.out_of_stock_count || 0)
            }
        };
    }

    async getRestockPriorityProducts(limit = 10) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 20);
        // Products that are out of stock OR have low stock and high historical demand
        const sql = `
            SELECT p.id, p.name, c.name as category, p.stock, p.price,
                   COALESCE(SUM(od.quantity), 0) as total_sold,
                   CASE 
                       WHEN p.stock = 0 THEN 'SANGAT TINGGI (Stok Habis)'
                       WHEN p.stock <= 3 THEN 'TINGGI (Stok Kritis)'
                       ELSE 'SEDANG (Stok Menipis)'
                   END as priority_level
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN order_details od ON od.product_id = p.id
            WHERE p.stock <= 5 OR p.status = 'Out_of_Stock'
            GROUP BY p.id, p.name, c.name, p.stock, p.price
            ORDER BY p.stock ASC, total_sold DESC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return {
            success: true,
            count: rows.length,
            data: rows
        };
    }

    async getStockSoldUnits() {
        const sql = `
            SELECT 
                COALESCE(SUM(od.quantity), 0) as total_sold_units,
                COALESCE(SUM(od.subtotal), 0) as total_sold_revenue,
                COUNT(DISTINCT od.order_id) as total_orders_involved
            FROM order_details od
            JOIN orders o ON od.order_id = o.id
            WHERE o.status != 'DIBATALKAN'
        `;
        const [rows] = await pool.query(sql);
        const r = rows[0] || {};
        return {
            success: true,
            data: {
                total_sold_units: Number(r.total_sold_units || 0),
                total_sold_revenue: Number(r.total_sold_revenue || 0),
                total_sold_revenue_formatted: 'Rp ' + Number(r.total_sold_revenue || 0).toLocaleString('id-ID'),
                total_orders_involved: Number(r.total_orders_involved || 0)
            }
        };
    }

    // ==========================================
    // C. PESANAN & DETAIL PESANAN ANALYTICS
    // ==========================================

    async getOrdersSummary(user) {
        // If customer, count own orders only
        let whereClause = '';
        const params = [];
        if (user.role === 'Customer') {
            if (!user.id) return { success: false, error: 'Silakan login terlebih dahulu.' };
            whereClause = 'WHERE o.user_id = ?';
            params.push(user.id);
        }

        const sql = `
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN o.status = 'MENUNGGU PEMBAYARAN' THEN 1 ELSE 0 END), 0) as pending_payment,
                COALESCE(SUM(CASE WHEN o.status = 'DIPROSES' THEN 1 ELSE 0 END), 0) as processing,
                COALESCE(SUM(CASE WHEN o.status = 'DIKEMAS' THEN 1 ELSE 0 END), 0) as packed,
                COALESCE(SUM(CASE WHEN o.status = 'DIKIRIM' THEN 1 ELSE 0 END), 0) as shipped,
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN o.status = 'DIBATALKAN' THEN 1 ELSE 0 END), 0) as cancelled,
                COALESCE(SUM(CASE WHEN DATE(o.order_date) = CURDATE() THEN 1 ELSE 0 END), 0) as orders_today,
                COALESCE(SUM(o.total), 0) as total_order_value
            FROM orders o
            ${whereClause}
        `;
        const [rows] = await pool.query(sql, params);
        const r = rows[0] || {};
        return {
            success: true,
            role_filtered: user.role === 'Customer',
            data: {
                total_orders: Number(r.total_orders || 0),
                pending_payment: Number(r.pending_payment || 0),
                processing: Number(r.processing || 0),
                packed: Number(r.packed || 0),
                shipped: Number(r.shipped || 0),
                completed: Number(r.completed || 0),
                cancelled: Number(r.cancelled || 0),
                orders_today: Number(r.orders_today || 0),
                total_order_value: Number(r.total_order_value || 0),
                total_order_value_formatted: 'Rp ' + Number(r.total_order_value || 0).toLocaleString('id-ID')
            }
        };
    }

    async getOrdersByStatus(statusName, user, limit = 10) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 25);
        let sql = `
            SELECT o.id, o.user_id, u.name as customer_name, o.total, o.status, o.order_date
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE 1=1
        `;
        const params = [];

        if (statusName) {
            sql += ` AND o.status = ?`;
            params.push(statusName);
        }

        // Row-Level Security for Customer
        if (user.role === 'Customer') {
            if (!user.id) return { success: false, error: 'Silakan login terlebih dahulu.' };
            sql += ` AND o.user_id = ?`;
            params.push(user.id);
        }

        sql += ` ORDER BY o.order_date DESC LIMIT ?`;
        params.push(safeLimit);

        const [rows] = await pool.query(sql, params);
        return {
            success: true,
            status: statusName || 'Semua Status',
            count: rows.length,
            data: rolePermissionService.sanitizeRows('orders', rows, user.role, user.id)
        };
    }

    async getOrderContents(orderId, user) {
        const id = parseInt(orderId, 10);
        if (!id) return { success: false, error: 'Nomor pesanan harus berupa angka yang valid.' };

        // 1. Fetch order header
        let orderSql = `
            SELECT o.id, o.user_id, u.name as customer_name, u.phone as customer_phone,
                   o.total, o.status, o.order_date,
                   p.payment_method, p.status as payment_status, p.paid_at
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.id = ?
            LIMIT 1
        `;
        const [orderRows] = await pool.query(orderSql, [id]);
        if (orderRows.length === 0) {
            return {
                success: true,
                found: false,
                message: `Pesanan #${id} tidak ditemukan di database nnaco_sa.`
            };
        }

        const order = orderRows[0];

        // 2. Row-Level Security Check
        if (user.role === 'Customer') {
            if (!user.id || order.user_id !== user.id) {
                return {
                    success: false,
                    error: `Akses ditolak: Pesanan #${id} bukan milik akun Anda.`
                };
            }
        }

        // 3. Fetch order items
        const itemSql = `
            SELECT od.id, od.product_id, p.name as product_name, od.quantity, od.price, od.subtotal
            FROM order_details od
            LEFT JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?
            ORDER BY od.id ASC
        `;
        const [items] = await pool.query(itemSql, [id]);

        return {
            success: true,
            found: true,
            order: {
                order_id: order.id,
                customer_name: (user.role === 'Customer' || user.role === 'Operator' || user.role === 'Admin' || user.role === 'Owner') ? order.customer_name : 'Customer',
                order_date: order.order_date,
                total: Number(order.total),
                total_formatted: 'Rp ' + Number(order.total).toLocaleString('id-ID'),
                status: order.status,
                payment_method: order.payment_method || 'Belum dipilih',
                payment_status: order.payment_status || 'PENDING',
                paid_at: order.paid_at,
                items: items.map(i => ({
                    product_id: i.product_id,
                    product_name: i.product_name,
                    quantity: Number(i.quantity),
                    price: Number(i.price),
                    price_formatted: 'Rp ' + Number(i.price).toLocaleString('id-ID'),
                    subtotal: Number(i.subtotal),
                    subtotal_formatted: 'Rp ' + Number(i.subtotal).toLocaleString('id-ID')
                }))
            }
        };
    }

    // ==========================================
    // D. PEMBAYARAN ANALYTICS
    // ==========================================

    async getPaymentsAnalytics(user) {
        if (!rolePermissionService.canAccessOmzet(user.role) && user.role !== 'Operator') {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('pembayaran keseluruhan', user.role)
            };
        }

        const sql = `
            SELECT 
                COUNT(*) as total_payments,
                COALESCE(SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END), 0) as paid_count,
                COALESCE(SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END), 0) as pending_count,
                COALESCE(SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END), 0) as failed_count,
                COALESCE(SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END), 0) as cancelled_count,
                COALESCE(SUM(CASE WHEN status = 'PAID' THEN amount ELSE 0 END), 0) as paid_amount,
                COALESCE(SUM(amount), 0) as total_amount
            FROM payments
        `;
        const [rows] = await pool.query(sql);
        const r = rows[0] || {};
        return {
            success: true,
            data: {
                total_payments: Number(r.total_payments || 0),
                paid_count: Number(r.paid_count || 0),
                pending_count: Number(r.pending_count || 0),
                failed_count: Number(r.failed_count || 0),
                cancelled_count: Number(r.cancelled_count || 0),
                paid_amount: Number(r.paid_amount || 0),
                paid_amount_formatted: 'Rp ' + Number(r.paid_amount || 0).toLocaleString('id-ID'),
                total_amount: Number(r.total_amount || 0),
                total_amount_formatted: 'Rp ' + Number(r.total_amount || 0).toLocaleString('id-ID')
            }
        };
    }

    async getPaymentMethodsBreakdown(user) {
        if (!rolePermissionService.canAccessOmzet(user.role) && user.role !== 'Operator') {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('metode pembayaran', user.role)
            };
        }

        const sql = `
            SELECT 
                COALESCE(payment_method, 'Belum Ditentukan') as payment_method,
                COUNT(*) as transaction_count,
                COALESCE(SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END), 0) as paid_count,
                COALESCE(SUM(CASE WHEN status = 'PAID' THEN amount ELSE 0 END), 0) as total_paid_amount
            FROM payments
            GROUP BY payment_method
            ORDER BY transaction_count DESC
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows.map(r => ({
                payment_method: r.payment_method,
                transaction_count: Number(r.transaction_count),
                paid_count: Number(r.paid_count),
                total_paid_amount: Number(r.total_paid_amount),
                total_paid_amount_formatted: 'Rp ' + Number(r.total_paid_amount).toLocaleString('id-ID')
            }))
        };
    }

    // ==========================================
    // E. PENJUALAN & LAPORAN OMZET (ADMIN & OWNER)
    // ==========================================

    async getOmzetReport(user) {
        if (!rolePermissionService.canAccessOmzet(user.role)) {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('omzet penjualan', user.role)
            };
        }

        // Definition of Omzet: Orders with status = 'SELESAI' (completed) or payments with status = 'PAID'
        const sql = `
            SELECT 
                -- Today
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND DATE(o.created_at) = CURDATE() THEN o.total ELSE 0 END), 0) as omzet_today,
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND DATE(o.created_at) = CURDATE() THEN 1 ELSE 0 END), 0) as orders_today,
                
                -- This Month
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE()) THEN o.total ELSE 0 END), 0) as omzet_this_month,
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END), 0) as orders_this_month,

                -- Last Month
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND o.created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 1 MONTH) AND o.created_at < DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00') THEN o.total ELSE 0 END), 0) as omzet_last_month,
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND o.created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00'), INTERVAL 1 MONTH) AND o.created_at < DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00') THEN 1 ELSE 0 END), 0) as orders_last_month,

                -- This Year
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' AND YEAR(o.created_at) = YEAR(CURDATE()) THEN o.total ELSE 0 END), 0) as omzet_this_year,

                -- All Time
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' THEN o.total ELSE 0 END), 0) as omzet_all_time,
                COALESCE(SUM(CASE WHEN o.status = 'SELESAI' THEN 1 ELSE 0 END), 0) as completed_orders_count,
                COALESCE(AVG(CASE WHEN o.status = 'SELESAI' THEN o.total ELSE NULL END), 0) as avg_order_value,

                -- Pipeline Value (Orders in process)
                COALESCE(SUM(CASE WHEN o.status IN ('DIPROSES', 'DIKEMAS', 'DIKIRIM') THEN o.total ELSE 0 END), 0) as pipeline_value,
                COALESCE(SUM(CASE WHEN o.status IN ('DIPROSES', 'DIKEMAS', 'DIKIRIM') THEN 1 ELSE 0 END), 0) as pipeline_orders_count
            FROM orders o
        `;
        const [rows] = await pool.query(sql);
        const r = rows[0] || {};

        const omzetThisMonth = Number(r.omzet_this_month || 0);
        const omzetLastMonth = Number(r.omzet_last_month || 0);
        let growthPercentage = 0;
        if (omzetLastMonth > 0) {
            growthPercentage = (((omzetThisMonth - omzetLastMonth) / omzetLastMonth) * 100).toFixed(1);
        }

        return {
            success: true,
            data: {
                omzet_today: Number(r.omzet_today || 0),
                omzet_today_formatted: 'Rp ' + Number(r.omzet_today || 0).toLocaleString('id-ID'),
                orders_today: Number(r.orders_today || 0),

                omzet_this_month: omzetThisMonth,
                omzet_this_month_formatted: 'Rp ' + omzetThisMonth.toLocaleString('id-ID'),
                orders_this_month: Number(r.orders_this_month || 0),

                omzet_last_month: omzetLastMonth,
                omzet_last_month_formatted: 'Rp ' + omzetLastMonth.toLocaleString('id-ID'),
                orders_last_month: Number(r.orders_last_month || 0),
                growth_percentage: growthPercentage,

                omzet_this_year: Number(r.omzet_this_year || 0),
                omzet_this_year_formatted: 'Rp ' + Number(r.omzet_this_year || 0).toLocaleString('id-ID'),

                omzet_all_time: Number(r.omzet_all_time || 0),
                omzet_all_time_formatted: 'Rp ' + Number(r.omzet_all_time || 0).toLocaleString('id-ID'),
                completed_orders_count: Number(r.completed_orders_count || 0),

                avg_order_value: Math.round(Number(r.avg_order_value || 0)),
                avg_order_value_formatted: 'Rp ' + Math.round(Number(r.avg_order_value || 0)).toLocaleString('id-ID'),

                pipeline_value: Number(r.pipeline_value || 0),
                pipeline_value_formatted: 'Rp ' + Number(r.pipeline_value || 0).toLocaleString('id-ID'),
                pipeline_orders_count: Number(r.pipeline_orders_count || 0)
            }
        };
    }

    async getSalesComparison(user) {
        const omzetRes = await this.getOmzetReport(user);
        if (!omzetRes.success) return omzetRes;
        const d = omzetRes.data;
        return {
            success: true,
            data: {
                current_month: {
                    omzet: d.omzet_this_month,
                    completed_orders: d.orders_this_month
                },
                last_month: {
                    omzet: d.omzet_last_month,
                    completed_orders: d.orders_last_month
                },
                growth_percentage: d.growth_percentage + '%'
            }
        };
    }

    async getTopSellingProducts(user, limit = 5) {
        if (!rolePermissionService.canAccessOmzet(user.role) && user.role !== 'Operator') {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('produk terlaris', user.role)
            };
        }

        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 10);
        const sql = `
            SELECT p.id, p.name, c.name as category,
                   COALESCE(SUM(od.quantity), 0) as total_sold_units,
                   COALESCE(SUM(od.subtotal), 0) as total_revenue,
                   p.price, p.stock
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            JOIN orders o ON od.order_id = o.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE o.status != 'DIBATALKAN'
            GROUP BY p.id, p.name, c.name, p.price, p.stock
            ORDER BY total_sold_units DESC, total_revenue DESC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return {
            success: true,
            count: rows.length,
            data: rows.map(r => ({
                product_id: r.id,
                product_name: r.name,
                category: r.category || 'Aksesoris',
                total_sold_units: Number(r.total_sold_units),
                total_revenue: Number(r.total_revenue),
                total_revenue_formatted: 'Rp ' + Number(r.total_revenue).toLocaleString('id-ID'),
                price: Number(r.price),
                stock: Number(r.stock)
            }))
        };
    }

    // ==========================================
    // F. CUSTOMER ANALYTICS
    // ==========================================

    async getCustomerStatistics(user) {
        if (!rolePermissionService.canAccessAllCustomers(user.role)) {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('seluruh data customer', user.role)
            };
        }

        const sql = `
            SELECT 
                COUNT(*) as total_users,
                COALESCE(SUM(CASE WHEN role = 'Customer' THEN 1 ELSE 0 END), 0) as total_customers,
                COALESCE(SUM(CASE WHEN role = 'Customer' AND status = 'Active' THEN 1 ELSE 0 END), 0) as active_customers,
                COALESCE(SUM(CASE WHEN role = 'Customer' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END), 0) as new_customers_this_month,
                COALESCE(SUM(CASE WHEN role = 'Operator' THEN 1 ELSE 0 END), 0) as total_operators,
                COALESCE(SUM(CASE WHEN role = 'Admin' THEN 1 ELSE 0 END), 0) as total_admins,
                COALESCE(SUM(CASE WHEN role = 'Owner' THEN 1 ELSE 0 END), 0) as total_owners
            FROM users
        `;
        const [rows] = await pool.query(sql);
        const r = rows[0] || {};
        return {
            success: true,
            data: {
                total_users: Number(r.total_users || 0),
                total_customers: Number(r.total_customers || 0),
                active_customers: Number(r.active_customers || 0),
                new_customers_this_month: Number(r.new_customers_this_month || 0),
                total_operators: Number(r.total_operators || 0),
                total_admins: Number(r.total_admins || 0),
                total_owners: Number(r.total_owners || 0)
            }
        };
    }

    async getTopSpendingCustomer(user) {
        if (!rolePermissionService.canAccessAllCustomers(user.role)) {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('customer dengan transaksi terbanyak', user.role)
            };
        }

        const sql = `
            SELECT u.id, u.name, u.email,
                   COUNT(o.id) as total_orders,
                   COALESCE(SUM(o.total), 0) as total_spending
            FROM users u
            JOIN orders o ON o.user_id = u.id
            WHERE o.status != 'DIBATALKAN' AND u.role = 'Customer'
            GROUP BY u.id, u.name, u.email
            ORDER BY total_spending DESC, total_orders DESC
            LIMIT 5
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows.map(r => ({
                id: r.id,
                name: r.name,
                email: r.email,
                total_orders: Number(r.total_orders),
                total_spending: Number(r.total_spending),
                total_spending_formatted: 'Rp ' + Number(r.total_spending).toLocaleString('id-ID')
            }))
        };
    }

    // ==========================================
    // G. OPERASIONAL ANALYTICS
    // ==========================================

    async getOperationalQueue(user) {
        if (user.role === 'Customer') {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('antrean operasional', user.role)
            };
        }

        const sql = `
            SELECT o.id, o.user_id, u.name as customer_name, o.total, o.status, o.order_date,
                   p.payment_method, p.status as payment_status
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.status IN ('MENUNGGU PEMBAYARAN', 'DIPROSES', 'DIKEMAS')
            ORDER BY o.order_date ASC
            LIMIT 15
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows.map(r => ({
                order_id: r.id,
                customer_name: r.customer_name || 'Customer',
                order_date: r.order_date,
                total: Number(r.total),
                total_formatted: 'Rp ' + Number(r.total).toLocaleString('id-ID'),
                status: r.status,
                payment_method: r.payment_method || '-',
                payment_status: r.payment_status || 'PENDING'
            }))
        };
    }

    async getAuditLogsToday(user) {
        if (!rolePermissionService.canAccessSystemLogs(user.role)) {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('log audit sistem', user.role)
            };
        }

        const sql = `
            SELECT id, user_name, user_role, action, category, status, details, ip_address, created_at
            FROM system_audit_logs
            WHERE DATE(created_at) = CURDATE()
            ORDER BY created_at DESC
            LIMIT 10
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows
        };
    }

    // ==========================================
    // H. CROSS-DATABASE / MULTI-TABLE ANALYTICS
    // ==========================================

    async getSalesByCategory(user) {
        if (!rolePermissionService.canAccessOmzet(user.role) && user.role !== 'Operator') {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('analisis penjualan kategori', user.role)
            };
        }

        const sql = `
            SELECT c.id, c.name as category_name,
                   COALESCE(SUM(od.quantity), 0) as total_units_sold,
                   COALESCE(SUM(od.subtotal), 0) as total_revenue,
                   COUNT(DISTINCT od.order_id) as orders_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            LEFT JOIN order_details od ON od.product_id = p.id
            LEFT JOIN orders o ON od.order_id = o.id AND o.status != 'DIBATALKAN'
            GROUP BY c.id, c.name
            ORDER BY total_revenue DESC, total_units_sold DESC
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows.map(r => ({
                category_id: r.id,
                category_name: r.category_name,
                total_units_sold: Number(r.total_units_sold),
                total_revenue: Number(r.total_revenue),
                total_revenue_formatted: 'Rp ' + Number(r.total_revenue).toLocaleString('id-ID'),
                orders_count: Number(r.orders_count)
            }))
        };
    }

    async getLowStockHighSalesProducts(user) {
        if (!rolePermissionService.canAccessOmzet(user.role) && user.role !== 'Operator') {
            return {
                success: false,
                error: rolePermissionService.getDenialMessage('analisis stok dan penjualan', user.role)
            };
        }

        const sql = `
            SELECT p.id, p.name, c.name as category, p.stock, p.price,
                   COALESCE(SUM(od.quantity), 0) as total_sold
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN order_details od ON od.product_id = p.id
            WHERE p.stock <= 5 AND p.status = 'Available'
            GROUP BY p.id, p.name, c.name, p.stock, p.price
            ORDER BY total_sold DESC, p.stock ASC
            LIMIT 5
        `;
        const [rows] = await pool.query(sql);
        return {
            success: true,
            count: rows.length,
            data: rows.map(r => ({
                id: r.id,
                name: r.name,
                category: r.category || 'Aksesoris',
                stock: Number(r.stock),
                price: Number(r.price),
                price_formatted: 'Rp ' + Number(r.price).toLocaleString('id-ID'),
                total_sold: Number(r.total_sold)
            }))
        };
    }
}

module.exports = new AiQueryService();
