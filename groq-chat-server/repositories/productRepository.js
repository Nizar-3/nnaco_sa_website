const pool = require('../config/db');

class ProductRepository {
    async searchProducts({ query = '', category = '', minPrice = null, maxPrice = null, sortBy = 'price_asc', limit = 5 }) {
        let sql = `
            SELECT p.id, p.name, p.price, p.stock, p.status, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'Available'
        `;
        const params = [];

        if (query && typeof query === 'string' && query.trim()) {
            sql += ` AND (p.name LIKE ? OR p.description LIKE ?)`;
            const searchPattern = `%${query.trim()}%`;
            params.push(searchPattern, searchPattern);
        }

        if (category && typeof category === 'string' && category.trim()) {
            sql += ` AND c.name LIKE ?`;
            params.push(`%${category.trim()}%`);
        }

        if (minPrice !== null && minPrice !== undefined && !isNaN(Number(minPrice))) {
            sql += ` AND p.price >= ?`;
            params.push(Number(minPrice));
        }

        if (maxPrice !== null && maxPrice !== undefined && !isNaN(Number(maxPrice))) {
            sql += ` AND p.price <= ?`;
            params.push(Number(maxPrice));
        }

        if (sortBy === 'price_asc') {
            sql += ` ORDER BY p.price ASC`;
        } else if (sortBy === 'price_desc') {
            sql += ` ORDER BY p.price DESC`;
        } else if (sortBy === 'newest') {
            sql += ` ORDER BY p.created_at DESC`;
        } else {
            sql += ` ORDER BY p.id ASC`;
        }

        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 10);
        sql += ` LIMIT ?`;
        params.push(safeLimit);

        const [rows] = await pool.query(sql, params);
        return rows.map(r => ({
            id: r.id,
            name: r.name,
            price: Number(r.price),
            stock: r.stock,
            is_available: r.stock > 0,
            stock_status: r.stock > 5 ? 'Stok Tersedia' : (r.stock > 0 ? `Sisa ${r.stock} item` : 'Stok Habis'),
            category: r.category_name || 'Umum'
        }));
    }

    async getProductByName(name) {
        if (!name || typeof name !== 'string') return null;
        const sql = `
            SELECT p.id, p.name, p.price, p.stock, p.status, p.description, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'Available' AND p.name LIKE ?
            LIMIT 1
        `;
        const [rows] = await pool.query(sql, [`%${name.trim()}%`]);
        if (rows.length === 0) return null;
        const r = rows[0];
        return {
            id: r.id,
            name: r.name,
            price: Number(r.price),
            stock: r.stock,
            is_available: r.stock > 0,
            stock_status: r.stock > 5 ? 'Stok Tersedia' : (r.stock > 0 ? `Sisa ${r.stock} item` : 'Stok Habis'),
            category: r.category_name || 'Umum',
            description: r.description || ''
        };
    }

    async getCategories() {
        const sql = `SELECT id, name, description FROM categories ORDER BY name ASC`;
        const [rows] = await pool.query(sql);
        return rows.map(c => ({
            id: c.id,
            name: c.name,
            description: c.description || ''
        }));
    }

    async getCheapestProduct(categoryName = null) {
        let sql = `
            SELECT p.id, p.name, p.price, p.stock, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'Available' AND p.stock > 0
        `;
        const params = [];

        if (categoryName && typeof categoryName === 'string' && categoryName.trim()) {
            sql += ` AND (c.name LIKE ? OR p.name LIKE ?)`;
            const pat = `%${categoryName.trim()}%`;
            params.push(pat, pat);
        }

        sql += ` ORDER BY p.price ASC LIMIT 1`;
        const [rows] = await pool.query(sql, params);
        if (rows.length === 0) return null;
        const r = rows[0];
        return {
            id: r.id,
            name: r.name,
            price: Number(r.price),
            stock: r.stock,
            stock_status: r.stock > 5 ? 'Stok Tersedia' : `Sisa ${r.stock} item`,
            category: r.category_name || 'Umum'
        };
    }

    async getLatestProducts(limit = 5) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 10);
        const sql = `
            SELECT p.id, p.name, p.price, p.stock, c.name as category_name, p.created_at
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'Available'
            ORDER BY p.created_at DESC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return rows.map(r => ({
            id: r.id,
            name: r.name,
            price: Number(r.price),
            stock: r.stock,
            stock_status: r.stock > 0 ? 'Tersedia' : 'Habis',
            category: r.category_name || 'Umum'
        }));
    }

    async getLowStockProducts(threshold = 5, limit = 10) {
        const safeThreshold = Math.max(parseInt(threshold, 10) || 5, 0);
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 20);
        const sql = `
            SELECT p.id, p.name, p.price, p.stock, p.status, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.stock <= ?
            ORDER BY p.stock ASC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeThreshold, safeLimit]);
        return rows.map(r => ({
            id: r.id,
            name: r.name,
            price: Number(r.price),
            stock: r.stock,
            status: r.status,
            category: r.category_name || 'Umum'
        }));
    }

    async getActiveProductCount() {
        const sql = `
            SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as active_products,
                SUM(CASE WHEN stock <= 5 THEN 1 ELSE 0 END) as low_stock_products,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock_products
            FROM products
        `;
        const [rows] = await pool.query(sql);
        const r = rows[0] || {};
        return {
            total_products: Number(r.total_products || 0),
            active_products: Number(r.active_products || 0),
            low_stock_products: Number(r.low_stock_products || 0),
            out_of_stock_products: Number(r.out_of_stock_products || 0)
        };
    }

    async getAllProducts({ category = null, status = null, limit = 15 } = {}) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 15, 1), 30);
        let sql = `
            SELECT p.id, p.name, p.price, p.stock, p.status, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
        `;
        const params = [];
        const conditions = [];

        if (category && typeof category === 'string' && category.trim()) {
            conditions.push(`c.name LIKE ?`);
            params.push(`%${category.trim()}%`);
        }

        if (status && typeof status === 'string' && status.trim()) {
            conditions.push(`p.status = ?`);
            params.push(status.trim());
        }

        if (conditions.length > 0) {
            sql += ` WHERE ` + conditions.join(' AND ');
        }

        sql += ` ORDER BY p.id ASC LIMIT ?`;
        params.push(safeLimit);

        const [rows] = await pool.query(sql, params);
        return rows.map(r => ({
            id: r.id,
            name: r.name,
            price: Number(r.price),
            price_formatted: 'Rp ' + Number(r.price).toLocaleString('id-ID'),
            stock: r.stock,
            status: r.status,
            category: r.category_name || 'Umum'
        }));
    }
}

module.exports = new ProductRepository();
