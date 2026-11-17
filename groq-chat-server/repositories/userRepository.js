const pool = require('../config/db');

class UserRepository {
    async getUserManagementSummary() {
        // Safe aggregate metrics only - strictly NO passwords, hashes, tokens, or PII
        const sql = `
            SELECT role, status, COUNT(*) as count
            FROM users
            GROUP BY role, status
        `;
        const [rows] = await pool.query(sql);
        const summary = {
            total_users: 0,
            by_role: {},
            by_status: {}
        };

        for (const row of rows) {
            const count = Number(row.count);
            summary.total_users += count;

            if (!summary.by_role[row.role]) summary.by_role[row.role] = 0;
            summary.by_role[row.role] += count;

            if (!summary.by_status[row.status]) summary.by_status[row.status] = 0;
            summary.by_status[row.status] += count;
        }

        return summary;
    }

    async getUserProfileById(userId) {
        if (!userId) return null;
        // Strictly select only non-sensitive profile fields - NEVER password
        const sql = `
            SELECT id, name, email, role, status, created_at
            FROM users
            WHERE id = ?
            LIMIT 1
        `;
        const [rows] = await pool.query(sql, [userId]);
        if (!rows || rows.length === 0) return null;
        return rows[0];
    }

    async getAllUsers({ role = null, status = null, search = null, limit = 10 } = {}) {
        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 25);
        let sql = `
            SELECT id, name, email, phone, role, status, created_at
            FROM users
        `;
        const params = [];
        const conditions = [];

        if (role && typeof role === 'string' && role.trim()) {
            conditions.push(`role = ?`);
            params.push(role.trim());
        }

        if (status && typeof status === 'string' && status.trim()) {
            conditions.push(`status = ?`);
            params.push(status.trim());
        }

        if (search && typeof search === 'string' && search.trim()) {
            conditions.push(`(name LIKE ? OR email LIKE ? OR phone LIKE ?)`);
            const term = `%${search.trim()}%`;
            params.push(term, term, term);
        }

        if (conditions.length > 0) {
            sql += ` WHERE ` + conditions.join(' AND ');
        }

        sql += ` ORDER BY id ASC LIMIT ?`;
        params.push(safeLimit);

        const [rows] = await pool.query(sql, params);
        return rows.map(u => ({
            id: u.id,
            name: u.name,
            email: u.email,
            phone: u.phone || '-',
            role: u.role,
            status: u.status,
            created_at: u.created_at
        }));
    }
}

module.exports = new UserRepository();
