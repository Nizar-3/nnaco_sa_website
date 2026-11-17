const rateLimit = require('express-rate-limit');

const apiLimiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 50, // Limit each IP to 50 requests per windowMs
    message: { error: 'Terlalu banyak request, silakan coba beberapa saat lagi.' },
    standardHeaders: true,
    legacyHeaders: false,
});

module.exports = apiLimiter;
