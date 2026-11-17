require('dotenv').config();
const express = require('express');
const cors = require('cors');
const aiRoutes = require('./routes/aiRoutes');

const app = express();
const port = process.env.PORT || 3001;

// CORS - Limit this in production
app.use(cors());
app.use(express.json());

// Routes
app.use('/api/ai', aiRoutes);

app.listen(port, () => {
    console.log(`Modular Groq Chat Server is running on http://localhost:${port}`);
});
