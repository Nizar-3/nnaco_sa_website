const db = require('../config/db');

class ProductService {
    async getCatalogContext() {
        try {
            const [rows] = await db.query('SELECT id, name, price, category_id, stock FROM products WHERE stock > 0 LIMIT 20');
            return rows.map(p => `ID: ${p.id}, Nama: ${p.name}, Harga: ${p.price}, Kategori ID: ${p.category_id}, Stok: ${p.stock}`).join('\n');
        } catch (error) {
            console.error('Error fetching catalog:', error);
            return 'Katalog tidak tersedia saat ini.';
        }
    }
}

module.exports = new ProductService();
