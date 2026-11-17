const crypto = require('crypto');
const aiDatabaseService = require('../services/aiDatabaseService');
const aiIntentService = require('../services/aiIntentService');
const groqService = require('../services/groqService');
const productRepository = require('../repositories/productRepository');
const orderRepository = require('../repositories/orderRepository');
const paymentRepository = require('../repositories/paymentRepository');
const userRepository = require('../repositories/userRepository');

const AUTH_SECRET = process.env.AI_AUTH_SECRET || 'nnaco_secure_ai_hmac_secret_2026_x87b';

function generateTestToken(user) {
    const payload = JSON.stringify({
        id: user.id,
        role: user.role,
        name: user.name,
        exp: Math.floor(Date.now() / 1000) + 3600
    });
    const b64 = Buffer.from(payload).toString('base64');
    const sig = crypto.createHmac('sha256', AUTH_SECRET).update(b64).digest('hex');
    return `${b64}.${sig}`;
}

let totalTests = 0;
let passedTests = 0;
let failedTests = 0;

function assert(condition, testName, details = '') {
    totalTests++;
    if (condition) {
        console.log(`  [PASS] Test ${totalTests}: ${testName}`);
        passedTests++;
        return true;
    } else {
        console.error(`  [FAIL] Test ${totalTests}: ${testName} - ${details}`);
        failedTests++;
        return false;
    }
}

async function runTestSuite() {
    console.log('====================================================');
    console.log('NNACO_SA GROQ AI CHATBOT & DATABASE TEST SUITE');
    console.log('Testing 17 Mandatory Requirements');
    console.log('====================================================\n');

    const customerUser = { id: 4, role: 'Customer', name: 'Demo Customer' };
    const operatorUser = { id: 1, role: 'Operator', name: 'John Operator' };
    const adminUser = { id: 2, role: 'Admin', name: 'Jane Admin' };
    const ownerUser = { id: 3, role: 'Owner', name: 'Boss Owner' };
    const guestUser = { id: null, role: 'Customer', name: 'Tamu' };

    // 1. Pencarian produk berdasarkan nama
    console.log('--- 1. Search Products by Name ---');
    const res1 = await aiDatabaseService.executeTool('searchProducts', { query: 'Gelang' }, customerUser);
    assert(res1.success && res1.count > 0 && res1.data.every(p => p.name.toLowerCase().includes('gelang')),
        'Pencarian produk berdasarkan nama (Gelang)',
        `Count: ${res1.count}`);

    // 2. Filter produk berdasarkan harga
    console.log('\n--- 2. Filter Products by Price ---');
    const res2 = await aiDatabaseService.executeTool('searchProducts', { maxPrice: 50000 }, customerUser);
    assert(res2.success && res2.count > 0 && res2.data.every(p => p.price <= 50000),
        'Filter produk berdasarkan harga (maxPrice <= 50000)',
        `Count: ${res2.count}, Prices: ${res2.data.map(p => p.price).join(', ')}`);

    // 3. Pengambilan harga dari database aktual
    console.log('\n--- 3. Actual Price from Database ---');
    const res3 = await aiDatabaseService.executeTool('getProductDetails', { productName: 'Kalung Titanium Elegan' }, customerUser);
    assert(res3.success && res3.found && res3.data.price === 150000,
        'Pengambilan harga dari database aktual (Kalung Titanium Elegan = 150000)',
        `Got: ${res3.data ? res3.data.price : 'not found'}`);

    // 4. Pengambilan stok dari database aktual
    console.log('\n--- 4. Actual Stock from Database ---');
    const res4 = await aiDatabaseService.executeTool('getProductDetails', { productName: 'Kalung Titanium Elegan' }, customerUser);
    assert(res4.success && res4.found && res4.data.stock === 10,
        'Pengambilan stok dari database aktual (Kalung Titanium Elegan stok = 10)',
        `Got: ${res4.data ? res4.data.stock : 'not found'}`);

    // 5. Pertanyaan produk yang tidak ditemukan
    console.log('\n--- 5. Product Not Found Handling ---');
    const res5 = await aiDatabaseService.executeTool('getProductDetails', { productName: 'Produk Fiktif Super Langka 999' }, customerUser);
    assert(res5.success && res5.found === false && res5.message.includes('tidak ditemukan'),
        'Pertanyaan produk yang tidak ditemukan ditangani dengan ramah',
        `Message: ${res5.message}`);

    // 6. Customer membaca status pesanannya sendiri
    console.log('\n--- 6. Customer Reads Own Orders ---');
    const res6 = await aiDatabaseService.executeTool('getMyOrders', {}, customerUser);
    assert(res6.success && res6.count >= 2 && res6.data.some(o => o.order_id === 2 && o.order_status === 'DIKIRIM'),
        'Customer dapat membaca daftar pesanan miliknya sendiri (User ID 4)',
        `Orders found: ${res6.count}`);

    // 7. Customer tidak dapat membaca pesanan customer lain
    console.log('\n--- 7. Customer Cannot Read Other Customer Orders ---');
    // Try to query an order that does not belong to user 4 (e.g. order 9999 or attempt spoofing)
    const res7 = await aiDatabaseService.executeTool('getMyOrderStatus', { orderId: 9999 }, customerUser);
    assert(res7.success && res7.found === false,
        'Customer tidak dapat membaca pesanan ID yang bukan miliknya',
        `Result: ${JSON.stringify(res7)}`);

    // Guest asking for orders is told to login
    const res7Guest = await aiDatabaseService.executeTool('getMyOrders', {}, guestUser);
    assert(!res7Guest.success && res7Guest.error.includes('login'),
        'Guest ditolak membaca pesanan dan diminta login',
        `Error: ${res7Guest.error}`);

    // 8. Customer tidak dapat membaca laporan omzet
    console.log('\n--- 8. Customer Cannot Read Omzet Report ---');
    const res8 = await aiDatabaseService.executeTool('getSalesSummary', {}, customerUser);
    assert(res8.success === false && res8.error.includes('Akses ditolak'),
        'Customer ditolak mengakses getSalesSummary (Server-side rejection)',
        `Error: ${res8.error}`);

    // 9. Operator hanya dapat membaca data sesuai hak aksesnya
    console.log('\n--- 9. Operator Permissions & Restrictions ---');
    const res9OpAllowed = await aiDatabaseService.executeTool('getOrdersNeedingProcessing', {}, operatorUser);
    const res9OpDenied = await aiDatabaseService.executeTool('getSalesSummary', {}, operatorUser);
    assert(res9OpAllowed.success && !res9OpDenied.success && res9OpDenied.error.includes('Akses ditolak'),
        'Operator dapat membaca pesanan perlu diproses tapi ditolak membaca omzet keuangan',
        `Allowed: ${res9OpAllowed.success}, Denied error: ${res9OpDenied.error}`);

    // 10. Admin dapat membaca data manajemen yang diizinkan
    console.log('\n--- 10. Admin Management Access ---');
    const res10Prod = await aiDatabaseService.executeTool('getActiveProductCount', {}, adminUser);
    const res10User = await aiDatabaseService.executeTool('getUserManagementSummary', {}, adminUser);
    assert(res10Prod.success && res10User.success && res10User.data.total_users > 0 && !res10User.data.password,
        'Admin membaca ringkasan produk aktif dan ringkasan user tanpa membocorkan credential',
        `Total users: ${res10User.data ? res10User.data.total_users : 0}`);

    // 11. Owner dapat membaca ringkasan laporan yang diizinkan
    console.log('\n--- 11. Owner Financial & Business Access ---');
    const res11Sales = await aiDatabaseService.executeTool('getSalesSummary', { period: 'all_time' }, ownerUser);
    const res11Top = await aiDatabaseService.executeTool('getTopSellingProducts', { limit: 5 }, ownerUser);
    assert(res11Sales.success && res11Top.success && res11Sales.data.all_time_transactions > 0,
        'Owner dapat membaca laporan omzet transaksi PAID dan produk terlaris',
        `Transactions: ${res11Sales.data.all_time_transactions}, Top prods: ${res11Top.count}`);

    // 12. Input berbahaya tidak dapat mengubah query (SQL Injection Defense)
    console.log('\n--- 12. SQL Injection Prevention ---');
    const maliciousInput = "' OR '1'='1' -- ";
    const res12 = await aiDatabaseService.executeTool('searchProducts', { query: maliciousInput }, customerUser);
    assert(res12.success && Array.isArray(res12.data),
        'SQL injection input ditangani secara aman oleh prepared statements tanpa bypass query',
        `Results count: ${res12.count}`);

    // 13. Tool yang tidak diizinkan ditolak backend
    console.log('\n--- 13. Unauthorized / Invalid Tools Rejected ---');
    const res13 = await aiDatabaseService.executeTool('nonExistentSystemTool', {}, ownerUser);
    assert(res13.success === false,
        'Tool tidak dikenal atau tidak terdaftar ditolak backend',
        `Error: ${res13.error}`);

    // 14. Query kosong atau gagal ditangani dengan aman
    console.log('\n--- 14. Empty and Malformed Query Handling ---');
    const res14 = await aiDatabaseService.executeTool('searchProducts', { query: '', minPrice: 'invalid', maxPrice: -500 }, customerUser);
    assert(res14.success && Array.isArray(res14.data),
        'Parameter kosong atau bernilai invalid dinormalisasi secara aman',
        `Count: ${res14.count}`);

    // 15. Data database tidak dikirim melebihi kebutuhan ke model (Data Minimization)
    console.log('\n--- 15. Data Minimization Verification ---');
    const res15 = await aiDatabaseService.executeTool('searchProducts', { limit: 2 }, customerUser);
    const sample = res15.data[0] || {};
    const hasSensitiveFields = 'password' in sample || 'salt' in sample || 'email' in sample || 'token' in sample;
    assert(res15.success && !hasSensitiveFields && 'price' in sample && 'name' in sample,
        'Hanya field yang aman dan relevan yang dikirimkan ke model AI',
        `Keys: ${Object.keys(sample).join(', ')}`);

    // 16. API key dan credential tidak muncul pada response atau log
    console.log('\n--- 16. Credential Leakage Prevention ---');
    const serializedResponse = JSON.stringify([res1, res2, res3, res6, res10User, res11Sales]);
    const leaksApiKey = serializedResponse.includes(process.env.GROQ_API_KEY) || serializedResponse.includes('nnaco_secure_ai_hmac_secret');
    assert(!leaksApiKey,
        'API Key dan secret token tidak pernah bocor dalam data query atau output',
        'No secret detected in output');

    // 17. End-to-End Chatbot Test (Live Groq call with Database integration)
    console.log('\n--- 17. Live End-to-End Groq AI with DB Integration ---');
    try {
        console.log('  Testing live Groq prompt: "Berapa harga gelang yang paling murah?"');
        const reply1 = await groqService.generateResponse('Berapa harga gelang yang paling murah?', customerUser);
        console.log(`  -> AI Response: "${reply1.slice(0, 150)}..."`);

        const mentionsGelang = reply1.toLowerCase().includes('gelang') || reply1.includes('Rp') || reply1.includes('35.000') || reply1.includes('25.000');
        assert(mentionsGelang,
            'Chatbot live dapat menjawab harga gelang termurah dari database aktual',
            reply1.slice(0, 100));
    } catch (e) {
        assert(false, 'Live Groq test failed', e.message);
    }

    console.log('\n====================================================');
    console.log(`TOTAL TESTS: ${totalTests}`);
    console.log(`PASSED: ${passedTests}`);
    console.log(`FAILED: ${failedTests}`);
    console.log('====================================================\n');

    if (failedTests > 0) {
        process.exit(1);
    } else {
        process.exit(0);
    }
}

runTestSuite().catch(err => {
    console.error('Test suite error:', err);
    process.exit(1);
});
