const http = require('http');
const crypto = require('crypto');
require('dotenv').config();

const AUTH_SECRET = process.env.AI_AUTH_SECRET || 'nnaco_secure_ai_hmac_secret_2026_x87b';

function generateAuthToken(payload) {
    const payloadStr = JSON.stringify(payload);
    const b64 = Buffer.from(payloadStr).toString('base64');
    const sig = crypto.createHmac('sha256', AUTH_SECRET).update(b64).digest('hex');
    return `${b64}.${sig}`;
}

function sendChat(message, token = null, userBody = null) {
    return new Promise((resolve, reject) => {
        const bodyObj = { message };
        if (userBody) bodyObj.user = userBody;
        const data = JSON.stringify(bodyObj);

        const headers = {
            'Content-Type': 'application/json',
            'Content-Length': Buffer.byteLength(data)
        };
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const req = http.request({
            hostname: 'localhost',
            port: 3001,
            path: '/api/ai/chat',
            method: 'POST',
            headers
        }, res => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                try {
                    resolve(JSON.parse(body));
                } catch (e) {
                    resolve({ raw: body });
                }
            });
        });

        req.on('error', reject);
        req.write(data);
        req.end();
    });
}

async function runTests() {
    console.log('====================================================');
    console.log('TESTING AI CHATBOT USER IDENTITY & LOGIN DETECTION');
    console.log('====================================================\n');

    let passed = 0;
    let failed = 0;

    function assert(cond, name, details) {
        if (cond) {
            console.log(`[PASS] ${name}`);
            passed++;
        } else {
            console.error(`[FAIL] ${name}\n  Details: ${details}`);
            failed++;
        }
    }

    // 1. Customer Token (ID 4: Demo Customer) asking "siapa saya"
    console.log('--- 1. Customer asking "siapa saya" ---');
    const tokenCustomer = generateAuthToken({
        id: 4,
        name: 'Demo Customer',
        email: 'customer@nnaco.com',
        role: 'Customer',
        exp: Math.floor(Date.now() / 1000) + 3600
    });
    const res1 = await sendChat('siapa saya', tokenCustomer);
    console.log(`-> Response 1:\n${res1.reply}\n`);
    assert(
        res1.reply && res1.reply.includes('Demo Customer') && res1.reply.includes('Customer') && res1.reply.includes('#4'),
        'Customer bertanya "siapa saya" dijawab dengan profil & peran Customer',
        res1.reply
    );

    // 2. Customer asking "saya login sebagai siapa?"
    console.log('--- 2. Customer asking "saya login sebagai siapa?" ---');
    const res2 = await sendChat('saya login sebagai siapa?', tokenCustomer);
    console.log(`-> Response 2:\n${res2.reply}\n`);
    assert(
        res2.reply && res2.reply.includes('Demo Customer') && res2.reply.includes('customer@nnaco.com'),
        'Customer bertanya "saya login sebagai siapa?" dijawab dengan email & nama login',
        res2.reply
    );

    // 3. Customer asking "akun saya apa"
    console.log('--- 3. Customer asking "akun saya apa" ---');
    const res3 = await sendChat('akun saya apa', tokenCustomer);
    console.log(`-> Response 3:\n${res3.reply}\n`);
    assert(
        res3.reply && res3.reply.includes('Demo Customer'),
        'Customer bertanya "akun saya apa" dijawab dengan info akun',
        res3.reply
    );

    // 4. Admin Token (ID 2: Jane Admin) asking "siapa saya"
    console.log('--- 4. Admin asking "siapa saya" ---');
    const tokenAdmin = generateAuthToken({
        id: 2,
        name: 'Jane Admin',
        email: 'admin@nnaco.com',
        role: 'Admin',
        exp: Math.floor(Date.now() / 1000) + 3600
    });
    const res4 = await sendChat('siapa saya', tokenAdmin);
    console.log(`-> Response 4:\n${res4.reply}\n`);
    assert(
        res4.reply && res4.reply.includes('Jane Admin') && res4.reply.includes('Admin'),
        'Admin bertanya "siapa saya" dijawab dengan profil & peran Admin',
        res4.reply
    );

    // 5. Guest / Belum Login asking "siapa saya"
    console.log('--- 5. Guest (Tamu) asking "siapa saya" ---');
    const res5 = await sendChat('siapa saya', null);
    console.log(`-> Response 5:\n${res5.reply}\n`);
    assert(
        res5.reply && res5.reply.includes('Tamu') && res5.reply.includes('belum login'),
        'Tamu bertanya "siapa saya" dijelaskan belum login dan diarahkan ke login/register',
        res5.reply
    );

    // 6. Pertanyaan "siapa anda" tetap menjawab identitas chatbot AI
    console.log('--- 6. Pertanyaan "siapa anda" ---');
    const res6 = await sendChat('siapa anda', tokenCustomer);
    console.log(`-> Response 6:\n${res6.reply}\n`);
    assert(
        res6.reply && res6.reply.includes('nnaco AI Assistant') && !res6.reply.includes('#4'),
        'Pertanyaan "siapa anda" tetap menjawab identitas asisten AI nnaco_sa',
        res6.reply
    );

    // 7. Keamanan: Coding python tetap ditolak
    console.log('--- 7. Pertanyaan coding python ---');
    const res7 = await sendChat('buatkan coding python untuk menuliskan nama anda', tokenCustomer);
    console.log(`-> Response 7:\n${res7.reply}\n`);
    assert(
        res7.reply && res7.reply.includes('khusus membantu Anda seputar produk'),
        'Permintaan coding python tetap ditolak oleh security guardrail',
        res7.reply
    );

    console.log('====================================================');
    console.log(`TOTAL: ${passed + failed} | PASSED: ${passed} | FAILED: ${failed}`);
    console.log('====================================================\n');

    if (failed > 0) process.exit(1);
    process.exit(0);
}

runTests().catch(err => {
    console.error('Test error:', err);
    process.exit(1);
});
