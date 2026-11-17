/**
 * rolePermissionService.js
 * Centralized Role-Based Access Control (RBAC) & Row-Level Security (RLS)
 * for nnaco_sa AI Chatbot.
 */

class RolePermissionService {
    constructor() {
        this.PERMISSIONS = {
            Customer: {
                allowedTables: ['categories', 'products', 'reviews', 'orders', 'order_details', 'payments', 'users'],
                allowedTools: [
                    'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                    'getMostExpensiveProduct', 'getLatestProducts',
                    'checkProductStock',
                    'getProductCategories', 'getCategoryStats', 'getCategoryDetails',
                    'getProductReviews', 'getReviewSummary', 'getAllReviews',
                    'getMyOrders', 'getMyOrderStatus', 'getCurrentUserProfile',
                    'getDatabaseOverview', 'getMenuOverview', 'getDatabaseSchema',
                    'getTableCount'
                ],
                allowedAnalytics: [
                    'catalog_summary', 'product_pricing', 'public_stock', 'own_orders',
                    'own_payments', 'own_profile', 'product_reviews', 'categories_count',
                    'products_count', 'store_policies'
                ],
                prohibitedAnalytics: [
                    'store_omzet', 'financial_reports', 'other_customer_data', 'all_users_list',
                    'low_stock_alerts', 'internal_inventory',
                    'system_settings', 'system_audit_logs', 'otp_requests', 'whatsapp_logs',
                    'admin_credentials', 'customer_phone_numbers'
                ],
                rowLevelFilter: 'user_id' // Must enforce user_id = authUser.id
            },
            Operator: {
                allowedTables: ['categories', 'products', 'reviews', 'orders', 'order_details', 'payments', 'users'],
                allowedTools: [
                    'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                    'getMostExpensiveProduct', 'getLatestProducts', 'getLowStockProducts',
                    'getOutOfStockProducts', 'getRestockPriorityProducts', 'checkProductStock',
                    'getTotalStock', 'getStockSoldUnits',
                    'getProductCategories', 'getCategoryStats', 'getCategoryDetails',
                    'getProductReviews', 'getReviewSummary', 'getAllReviews',
                    'getOrdersNeedingProcessing', 'getPendingPaymentOrdersCount', 'getAllOrders',
                    'getOrderDetails', 'getOrderContents', 'getOrdersByStatus', 'getOrderManagementSummary',
                    'getOperationalQueue', 'getCurrentUserProfile',
                    'getDatabaseOverview', 'getMenuOverview', 'getDatabaseSchema',
                    'readTableData', 'getTableCount', 'getActiveProductCount'
                ],
                allowedAnalytics: [
                    'operational_queue', 'order_processing', 'inventory_stock', 'low_stock_alerts',
                    'packing_status', 'shipping_status', 'payment_status_operational', 'order_contents'
                ],
                prohibitedAnalytics: [
                    'store_omzet', 'profit_margin', 'system_secrets', 'secret_settings',
                    'otp_hashes', 'user_passwords', 'api_tokens'
                ]
            },
            Admin: {
                allowedTables: [
                    'categories', 'products', 'reviews', 'orders', 'order_details', 'payments',
                    'users', 'system_settings', 'system_audit_logs', 'otp_requests',
                    'whatsapp_notification_logs', 'ai_conversations'
                ],
                allowedTools: [
                    'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                    'getMostExpensiveProduct', 'getLatestProducts', 'getLowStockProducts',
                    'getOutOfStockProducts', 'getRestockPriorityProducts', 'checkProductStock',
                    'getTotalStock', 'getStockSoldUnits',
                    'getProductCategories', 'getCategoryStats', 'getCategoryDetails',
                    'getProductReviews', 'getReviewSummary', 'getAllReviews',
                    'getOrdersNeedingProcessing', 'getPendingPaymentOrdersCount', 'getAllOrders',
                    'getOrderDetails', 'getOrderContents', 'getOrdersByStatus', 'getOrderManagementSummary',
                    'getOperationalQueue', 'getAllUsers', 'getUserManagementSummary', 'getUserDetails',
                    'getCustomerStatistics', 'getTopSpendingCustomer',
                    'getAllPayments', 'getPaymentSummary', 'getPaymentsAnalytics', 'getPaymentMethodsBreakdown',
                    'getSalesSummary', 'getOmzetSummary', 'getOmzetReport', 'getTopSellingProducts',
                    'getSalesComparison', 'getSalesByCategory', 'getLowStockHighSalesProducts',
                    'getCurrentUserProfile', 'getDatabaseOverview', 'getMenuOverview', 'getDatabaseSchema',
                    'readTableData', 'getTableCount', 'getActiveProductCount', 'getAuditLogsToday',
                    'getAuditLogsSummary', 'getNotificationLogsSummary'
                ],
                allowedAnalytics: [
                    'all_catalog', 'all_orders', 'all_users', 'all_payments', 'sales_summary',
                    'omzet_summary', 'top_products', 'audit_trail', 'whatsapp_logs', 'system_settings'
                ],
                prohibitedAnalytics: [
                    'raw_passwords', 'raw_otp_hashes', 'raw_api_keys', 'database_credentials'
                ]
            },
            Owner: {
                allowedTables: [
                    'categories', 'products', 'reviews', 'orders', 'order_details', 'payments',
                    'users', 'system_settings', 'system_audit_logs', 'otp_requests',
                    'whatsapp_notification_logs', 'ai_conversations'
                ],
                allowedTools: [
                    'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                    'getMostExpensiveProduct', 'getLatestProducts', 'getLowStockProducts',
                    'getOutOfStockProducts', 'getRestockPriorityProducts', 'checkProductStock',
                    'getTotalStock', 'getStockSoldUnits',
                    'getProductCategories', 'getCategoryStats', 'getCategoryDetails',
                    'getProductReviews', 'getReviewSummary', 'getAllReviews',
                    'getOrdersNeedingProcessing', 'getPendingPaymentOrdersCount', 'getAllOrders',
                    'getOrderDetails', 'getOrderContents', 'getOrdersByStatus', 'getOrderManagementSummary',
                    'getOperationalQueue', 'getAllUsers', 'getUserManagementSummary', 'getUserDetails',
                    'getCustomerStatistics', 'getTopSpendingCustomer',
                    'getAllPayments', 'getPaymentSummary', 'getPaymentsAnalytics', 'getPaymentMethodsBreakdown',
                    'getSalesSummary', 'getOmzetSummary', 'getOmzetReport', 'getTopSellingProducts',
                    'getSalesComparison', 'getSalesByCategory', 'getLowStockHighSalesProducts',
                    'getCurrentUserProfile', 'getDatabaseOverview', 'getMenuOverview', 'getDatabaseSchema',
                    'readTableData', 'getTableCount', 'getActiveProductCount', 'getAuditLogsToday',
                    'getAuditLogsSummary', 'getNotificationLogsSummary',
                    'getComprehensiveBusinessAnalytics', 'getAverageTransactionValue',
                    'getCrossDatabaseAnalytics'
                ],
                allowedAnalytics: [
                    'full_business_analytics', 'omzet_analysis', 'sales_comparison', 'profit_analysis',
                    'top_customers', 'top_products', 'inventory_value', 'operational_efficiency',
                    'payment_method_breakdown', 'historical_trends'
                ],
                prohibitedAnalytics: [
                    'raw_passwords', 'raw_otp_hashes', 'raw_api_keys', 'database_credentials'
                ]
            }
        };

        this.MASKED_FIELDS = ['password', 'otp_hash'];
        this.SECRET_KEY_PATTERNS = ['key', 'secret', 'token', 'password', 'credential', 'auth'];
    }

    isTableAllowed(tableName, role) {
        const cleanTable = String(tableName || '').toLowerCase().trim();
        const roleConfig = this.PERMISSIONS[role] || this.PERMISSIONS['Customer'];
        return roleConfig.allowedTables.includes(cleanTable);
    }

    isToolAllowed(toolName, role) {
        const roleConfig = this.PERMISSIONS[role] || this.PERMISSIONS['Customer'];
        return roleConfig.allowedTools.includes(toolName);
    }

    canAccessOmzet(role) {
        return role === 'Admin' || role === 'Owner';
    }

    canAccessAllCustomers(role) {
        return role === 'Admin' || role === 'Owner';
    }

    canAccessSystemLogs(role) {
        return role === 'Admin' || role === 'Owner';
    }

    /**
     * Sanitizes data rows to ensure secrets and unpermitted sensitive data are masked.
     */
    sanitizeRows(tableName, rows, role = 'Customer', currentUserId = null) {
        if (!Array.isArray(rows)) return rows;

        return rows.map(row => {
            if (!row || typeof row !== 'object') return row;
            const sanitized = { ...row };

            // 1. Never ever return password or otp_hash
            for (const field of this.MASKED_FIELDS) {
                if (sanitized[field] !== undefined) {
                    sanitized[field] = '****** [TERLINDUNGI]';
                }
            }

            // 2. System settings masking
            if (tableName === 'system_settings') {
                const key = String(sanitized.setting_key || '').toLowerCase();
                const isSecret = sanitized.is_secret === 1 || sanitized.is_secret === true ||
                    this.SECRET_KEY_PATTERNS.some(p => key.includes(p));
                if (isSecret) {
                    sanitized.setting_value = '****** [KREDENSIAL RAHASIA TERLINDUNGI]';
                }
            }

            // 3. Customer role privacy protection: mask phone and email of other users
            if (tableName === 'users' && role === 'Customer') {
                if (sanitized.id && currentUserId && sanitized.id !== currentUserId) {
                    if (sanitized.phone) sanitized.phone = sanitized.phone.slice(0, 4) + '****' + sanitized.phone.slice(-3);
                    if (sanitized.email) {
                        const parts = sanitized.email.split('@');
                        sanitized.email = parts[0].slice(0, 2) + '***@' + (parts[1] || 'domain.com');
                    }
                }
            }

            return sanitized;
        });
    }

    /**
     * Generates a polite, clear denial message explaining why an action was blocked
     */
    getDenialMessage(requestedInfo, role = 'Customer') {
        if (role === 'Customer') {
            if (requestedInfo.includes('omzet') || requestedInfo.includes('keuangan') || requestedInfo.includes('penjualan')) {
                return 'Maaf, data laporan omzet dan keuangan toko hanya dapat diakses oleh Admin atau Owner nnaco_sa.';
            }
            if (requestedInfo.includes('pengguna lain') || requestedInfo.includes('customer') || requestedInfo.includes('nomor') || requestedInfo.includes('telepon')) {
                return 'Maaf, untuk menjaga privasi dan keamanan data pelanggan, Anda hanya memiliki izin untuk mengakses data akun dan pesanan milik Anda sendiri.';
            }
            if (requestedInfo.includes('pengaturan') || requestedInfo.includes('setting') || requestedInfo.includes('audit')) {
                return 'Maaf, pengaturan sistem dan log audit internal hanya dapat diakses oleh pihak Administrator.';
            }
            return `Maaf, akun Anda (${role}) tidak memiliki izin untuk mengakses data tersebut. Silakan tanyakan seputar katalog produk, ketersediaan stok, harga, atau status pesanan Anda.`;
        }

        if (role === 'Operator') {
            if (requestedInfo.includes('omzet') || requestedInfo.includes('keuangan') || requestedInfo.includes('laba')) {
                return 'Maaf, data finansial dan laporan omzet toko nnaco_sa merupakan kewenangan akun Owner/Admin.';
            }
            return `Maaf, akun Operator tidak memiliki kewenangan untuk mengakses informasi ${requestedInfo}. Anda dapat mengakses daftar pesanan masuk, status pengemasan, dan stok menipis.`;
        }

        return `Maaf, hak akses role ${role} tidak mencakup pengambilan data tersebut demi kepatuhan keamanan sistem.`;
    }
}

module.exports = new RolePermissionService();
