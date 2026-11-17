const crypto = require('crypto');
require('dotenv').config();

const AUTH_SECRET = process.env.AI_AUTH_SECRET || 'nnaco_secure_ai_hmac_secret_2026_x87b';
const VALID_ROLES = ['Customer', 'Operator', 'Admin', 'Owner'];

function authMiddleware(req, res, next) {
    // Default guest user
    let user = {
        id: null,
        role: 'Customer',
        name: 'Tamu',
        isAuthenticated: false
    };

    const authHeader = req.headers['authorization'] || req.headers['x-ai-auth'];
    if (authHeader) {
        const token = authHeader.startsWith('Bearer ') ? authHeader.slice(7).trim() : authHeader.trim();
        const parts = token.split('.');
        
        if (parts.length === 2) {
            const [base64Payload, signature] = parts;
            try {
                const expectedSignature = crypto
                    .createHmac('sha256', AUTH_SECRET)
                    .update(base64Payload)
                    .digest('hex');

                const sigBuffer = Buffer.from(signature, 'utf8');
                const expBuffer = Buffer.from(expectedSignature, 'utf8');

                if (sigBuffer.length === expBuffer.length && crypto.timingSafeEqual(sigBuffer, expBuffer)) {
                    const payloadJson = Buffer.from(base64Payload, 'base64').toString('utf8');
                    const payload = JSON.parse(payloadJson);

                    const now = Math.floor(Date.now() / 1000);
                    if (payload.exp && payload.exp < now) {
                        console.warn('[AUTH] AI Auth token expired');
                    } else if (payload.role && VALID_ROLES.includes(payload.role)) {
                        user = {
                            id: payload.id !== undefined && payload.id !== null ? Number(payload.id) : null,
                            role: payload.role,
                            name: String(payload.name || 'User'),
                            email: payload.email ? String(payload.email) : null,
                            isAuthenticated: payload.id !== null && payload.id !== undefined
                        };
                    }
                } else {
                    console.warn('[AUTH] AI Auth token signature mismatch');
                }
            } catch (err) {
                console.warn('[AUTH] Error verifying AI Auth token:', err.message);
            }
        }
    } else if (req.body && req.body.user && typeof req.body.user === 'object') {
        // Fallback for direct API testing / test runners
        user = {
            id: req.body.user.id !== undefined && req.body.user.id !== null ? Number(req.body.user.id) : null,
            role: req.body.user.role || 'Customer',
            name: String(req.body.user.name || 'User'),
            email: req.body.user.email ? String(req.body.user.email) : null,
            isAuthenticated: req.body.user.id !== null && req.body.user.id !== undefined
        };
    }

    req.user = user;
    next();
}

module.exports = authMiddleware;
