const groqService = require('../services/groqService');

async function testSampleQueries() {
    const customer = { id: 4, role: 'Customer', name: 'Demo Customer' };
    const operator = { id: 1, role: 'Operator', name: 'John Operator' };
    const admin = { id: 2, role: 'Admin', name: 'Jane Admin' };
    const owner = { id: 3, role: 'Owner', name: 'Boss Owner' };

    console.log('=== TESTING REAL SAMPLE QUERIES ===\n');

    const queries = [
        // Customer
        { role: 'Customer', user: customer, q: 'Apa saja aksesoris yang tersedia?' },
        { role: 'Customer', user: customer, q: 'Berapa harga gelang yang paling murah?' },
        { role: 'Customer', user: customer, q: 'Apakah produk Kalung Titanium Elegan masih tersedia?' },
        { role: 'Customer', user: customer, q: 'Tampilkan aksesoris dengan harga di bawah Rp50.000.' },
        { role: 'Customer', user: customer, q: 'Apa produk terbaru di nnaco_sa?' },
        { role: 'Customer', user: customer, q: 'Bagaimana status pesanan saya?' },

        // Admin
        { role: 'Admin', user: admin, q: 'Berapa jumlah produk yang aktif?' },
        { role: 'Admin', user: admin, q: 'Produk mana yang stoknya hampir habis?' },
        { role: 'Admin', user: admin, q: 'Berapa jumlah pesanan yang menunggu pembayaran?' },

        // Operator
        { role: 'Operator', user: operator, q: 'Pesanan mana yang sudah dibayar dan perlu diproses?' },
        { role: 'Operator', user: operator, q: 'Produk mana yang stoknya menipis?' },

        // Owner
        { role: 'Owner', user: owner, q: 'Berapa total transaksi yang berhasil?' },
        { role: 'Owner', user: owner, q: 'Berapa omzet dari pesanan yang sudah dibayar bulan ini?' },
        { role: 'Owner', user: owner, q: 'Produk apa yang paling banyak terjual?' },
        { role: 'Owner', user: owner, q: 'Bagaimana ringkasan penjualan bulan ini?' }
    ];

    for (const item of queries) {
        console.log(`\n[${item.role}] Pertanyaan: "${item.q}"`);
        try {
            const reply = await groqService.generateResponse(item.q, item.user);
            console.log(`Jawaban AI:\n${reply.trim()}\n---------------------------------------------`);
        } catch (err) {
            console.error(`Error answering "${item.q}":`, err.message);
        }
    }
}

testSampleQueries().then(() => {
    console.log('\nAll sample queries tested successfully!');
    process.exit(0);
}).catch(err => {
    console.error(err);
    process.exit(1);
});
