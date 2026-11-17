const express = require('express');
const router = express.Router();
const aiController = require('../controllers/aiController');
const apiLimiter = require('../middleware/rateLimit');
const authMiddleware = require('../middleware/authMiddleware');

router.get('/health', aiController.health);
router.post('/chat', apiLimiter, authMiddleware, aiController.chat);

module.exports = router;
