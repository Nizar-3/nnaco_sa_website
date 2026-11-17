const pool = require('../config/db');

class CategoryRepository {
    async getAllCategoriesWithStats() {
        const sql = `
            SELECT c.id, c.name, c.description, c.created_at,
                   COUNT(p.id) as total_products,
                   SUM(CASE WHEN p.status = 'Available' AND p.stock > 0 THEN 1 ELSE 0 END) as available_products,
                   COALESCE(SUM(p.stock), 0) as total_stock
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id, c.name, c.description, c.created_at
            ORDER BY c.name ASC
        `;
        const [rows] = await pool.query(sql);
        return rows.map(r => ({
            id: r.id,
            name: r.name,
            description: r.description || '-',
            total_products: Number(r.total_products || 0),
            available_products: Number(r.available_products || 0),
            total_stock: Number(r.total_stock || 0),
            created_at: r.created_at
        }));
    }

    async getCategoryByNameOrId(identifier) {
        if (!identifier) return null;
        let sql = `
            SELECT c.id, c.name, c.description
            FROM categories c
            WHERE c.id = ? OR c.name LIKE ?
            LIMIT 1
        `;
        const [rows] = await pool.query(sql, [identifier, `%${identifier}%`]);
        if (rows.length === 0) return null;
        const cat = rows[0];

        // Fetch products in this category
        const [products] = await pool.query(`
            SELECT p.id, p.name, p.price, p.stock, p.status
            FROM products p
            WHERE p.category_id = ?
            ORDER BY p.name ASC
            LIMIT 10
        `, [cat.id]);

        return {
            id: cat.id,
            name: cat.name,
            description: cat.description || '-',
            product_count: products.length,
            products: products.map(p => ({
                id: p.id,
                name: p.name,
                price: Number(p.price),
                price_formatted: 'Rp ' + Number(p.price).toLocaleString('id-ID'),
                stock: p.stock,
                status: p.status
            }))
        };
    }
}

module.exports = new CategoryRepository();
