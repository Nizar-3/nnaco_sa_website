const pool = require('../config/db');

class ReviewRepository {
    async getAllReviews(limit = 10) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 30);
        const sql = `
            SELECT r.id, r.rating, r.comment, r.created_at,
                   p.name as product_name, p.id as product_id,
                   u.name as user_name
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
            LIMIT ?
        `;
        const [rows] = await pool.query(sql, [safeLimit]);
        return rows.map(r => ({
            id: r.id,
            product_id: r.product_id,
            product_name: r.product_name,
            reviewer: r.user_name,
            rating: Number(r.rating),
            rating_stars: '⭐'.repeat(Math.max(1, Math.min(5, Number(r.rating)))),
            comment: r.comment || '-',
            created_at: r.created_at
        }));
    }

    async getProductReviews(productIdentifier, limit = 5) {
        if (!productIdentifier) return null;
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 5, 1), 20);

        // First find product
        const [products] = await pool.query(`
            SELECT id, name FROM products
            WHERE id = ? OR name LIKE ?
            LIMIT 1
        `, [productIdentifier, `%${productIdentifier}%`]);

        if (products.length === 0) return null;
        const product = products[0];

        const [reviews] = await pool.query(`
            SELECT r.id, r.rating, r.comment, r.created_at, u.name as user_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC
            LIMIT ?
        `, [product.id, safeLimit]);

        const [stats] = await pool.query(`
            SELECT COUNT(*) as total_reviews, COALESCE(AVG(rating), 0) as avg_rating
            FROM reviews
            WHERE product_id = ?
        `, [product.id]);

        const statRow = stats[0] || {};
        const avg = Number(statRow.avg_rating || 0);

        return {
            product_id: product.id,
            product_name: product.name,
            total_reviews: Number(statRow.total_reviews || 0),
            average_rating: Number(avg.toFixed(1)),
            average_stars: '⭐'.repeat(Math.round(avg)),
            reviews: reviews.map(r => ({
                reviewer: r.user_name,
                rating: Number(r.rating),
                stars: '⭐'.repeat(Math.max(1, Math.min(5, Number(r.rating)))),
                comment: r.comment || '-',
                date: r.created_at
            }))
        };
    }

    async getReviewSummary() {
        const [overall] = await pool.query(`
            SELECT COUNT(*) as total_reviews, COALESCE(AVG(rating), 0) as avg_rating
            FROM reviews
        `);
        const [dist] = await pool.query(`
            SELECT rating, COUNT(*) as count
            FROM reviews
            GROUP BY rating
            ORDER BY rating DESC
        `);

        const avg = Number(overall[0]?.avg_rating || 0);
        const distribution = {};
        for (const d of dist) {
            distribution[`${d.rating}_star`] = Number(d.count);
        }

        return {
            total_reviews: Number(overall[0]?.total_reviews || 0),
            average_rating: Number(avg.toFixed(1)),
            average_stars: '⭐'.repeat(Math.round(avg)),
            distribution
        };
    }
}

module.exports = new ReviewRepository();
