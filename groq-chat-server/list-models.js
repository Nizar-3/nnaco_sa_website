require('dotenv').config();
const { Groq } = require('groq-sdk');

const groq = new Groq({
  apiKey: process.env.GROQ_API_KEY || ''
});

async function main() {
    try {
        const models = await groq.models.list();
        console.log(JSON.stringify(models.data.map(m => m.id), null, 2));
    } catch(e) {
        console.error(e);
    }
}
main();
