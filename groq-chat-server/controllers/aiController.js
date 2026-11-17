const groqService = require('../services/groqService');
const pool = require('../config/db');

exports.chat = async (req, res) => {
    try {
        const userMessage = req.body.message;
        
        if (!userMessage || typeof userMessage !== 'string' || userMessage.trim() === '') {
            return res.status(400).json({ success: false, error: 'Pesan tidak boleh kosong.' });
        }

        const maxLength = parseInt(process.env.AI_CHAT_MAX_MESSAGE_LENGTH || '2000', 10);
        if (userMessage.length > maxLength) {
            return res.status(400).json({ success: false, error: 'Pesan terlalu panjang (maksimal 2000 karakter).' });
        }

        const user = req.user || { id: null, role: 'Customer', name: 'Tamu' };

        const reply = await groqService.generateResponse(userMessage, user);
        
        // Simpan ke riwayat ai_conversations untuk monitoring Admin
        try {
            const userId = (user && user.id) ? user.id : null;
            await pool.query(
                "INSERT INTO ai_conversations (user_id, question, answer, created_at) VALUES (?, ?, ?, NOW())",
                [userId, userMessage, reply]
            );
        } catch (dbErr) {
            console.warn('[AI CONVERSATION LOG WARNING]:', dbErr.message);
        }

        res.json({ success: true, reply: reply });
    } catch (error) {
        console.error('AI Controller Error:', error.message);
        const userFriendlyError = (error.message.includes('dinonaktifkan') || error.message.includes('disabled') || error.message.includes('belum') || error.message.includes('configured')) 
            ? error.message 
            : 'Gagal memproses jawaban dari AI. Silakan coba sesaat lagi.';
        res.status(500).json({ success: false, error: userFriendlyError });
    }
};

exports.health = (req, res) => {
    res.json({
        status: 'ok',
        service: 'nnaco_sa Groq AI Chat Service',
        timestamp: new Date().toISOString()
    });
};
