<?php
/**
 * AIRolePermissionService.php
 * Role-Based Access Control (RBAC) & Row-Level Security (RLS)
 * untuk AI Chatbot Sistem nnaco_sa (PHP Core Layer).
 *
 * Mengontrol secara ketat batasan data yang dapat diakses oleh:
 * - Customer / Guest
 * - Operator
 * - Admin
 * - Owner
 */

class AIRolePermissionService {

    public const ROLE_CUSTOMER = 'Customer';
    public const ROLE_OPERATOR = 'Operator';
    public const ROLE_ADMIN    = 'Admin';
    public const ROLE_OWNER    = 'Owner';

    private static array $PERMISSIONS = [
        self::ROLE_CUSTOMER => [
            'allowedTools' => [
                'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                'getMostExpensiveProduct', 'getLatestProducts', 'checkProductStock',
                'getProductCategories', 'getCategoryStats', 'getMyOrders', 'getMyOrderStatus',
                'getOrderDetails', 'getCurrentUserProfile', 'getDatabaseOverview', 'getMenuOverview',
                'readTableData', 'getAllTablesOverview', 'describeTable'
            ],
            'prohibitedTools' => [
                'getOmzetReport', 'getSalesSummary', 'getSalesComparison', 'getSalesByCategory',
                'getAllOrders', 'getOrdersNeedingProcessing', 'getPendingPaymentOrdersCount',
                'getOrdersByStatus', 'getOrderCustomer', 'getAllPayments', 'getPaymentSummary',
                'getPaymentMethodsBreakdown', 'getAllUsers', 'getUserManagementSummary',
                'getCustomerStatistics', 'getTopSpendingCustomer', 'getAuditLogsToday',
                'getLowStockProducts', 'getOutOfStockProducts', 'getRestockPriorityProducts',
                'getLowStockHighSalesProducts', 'getOperationalQueue'
            ],
            'allowedTables' => [
                'categories', 'products', 'reviews', 'orders', 'order_details', 'payments', 'users'
            ],
            'prohibitedTables' => [
                'system_settings', 'system_audit_logs', 'otp_requests', 'whatsapp_notification_logs', 'ai_conversations'
            ],
            'denialMessage' => 'Maaf, data laporan omzet toko, pesanan pelanggan lain, dan manajemen sistem bersifat rahasia. Sebagai Customer, Anda hanya dapat mengakses katalog produk dan pesanan milik akun Anda sendiri.'
        ],
        self::ROLE_OPERATOR => [
            'allowedTools' => [
                'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                'getMostExpensiveProduct', 'getLatestProducts', 'checkProductStock', 'getTotalStock',
                'getLowStockProducts', 'getOutOfStockProducts', 'getRestockPriorityProducts',
                'getProductCategories', 'getCategoryStats', 'getOrdersNeedingProcessing',
                'getPendingPaymentOrdersCount', 'getAllOrders', 'getOrderDetails', 'getOrdersByStatus',
                'getOrderCustomer', 'getOperationalQueue', 'getCurrentUserProfile', 'getDatabaseOverview',
                'getMenuOverview', 'getActiveProductCount',
                'readTableData', 'getAllTablesOverview', 'describeTable'
            ],
            'prohibitedTools' => [
                'getOmzetReport', 'getSalesSummary', 'getSalesComparison', 'getSalesByCategory',
                'getTopSpendingCustomer', 'getAllUsers', 'getUserManagementSummary',
                'getCustomerStatistics', 'getAuditLogsToday', 'getAllPayments', 'getPaymentSummary',
                'getPaymentMethodsBreakdown', 'getLowStockHighSalesProducts'
            ],
            'allowedTables' => [
                'categories', 'products', 'reviews', 'orders', 'order_details', 'payments', 'whatsapp_notification_logs', 'users'
            ],
            'prohibitedTables' => [
                'system_settings', 'system_audit_logs', 'otp_requests', 'ai_conversations'
            ],
            'denialMessage' => 'Maaf, data laporan omzet total keuangan dan konfigurasi sistem terbatas hanya untuk Admin dan Owner. Sebagai Operator, Anda memiliki akses penuh ke antrean pesanan operasional, status pengiriman, dan pemantauan stok inventaris.'
        ],
        self::ROLE_ADMIN => [
            'allowedTools' => [
                'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                'getMostExpensiveProduct', 'getLatestProducts', 'checkProductStock', 'getTotalStock',
                'getLowStockProducts', 'getOutOfStockProducts', 'getRestockPriorityProducts', 'getActiveProductCount',
                'getProductCategories', 'getCategoryStats',
                'getOrdersNeedingProcessing', 'getPendingPaymentOrdersCount', 'getAllOrders', 'getOrderDetails',
                'getOrdersByStatus', 'getOrderCustomer', 'getOperationalQueue',
                'getAllPayments', 'getPaymentSummary', 'getPaymentMethodsBreakdown', 'getSalesSummary', 'getOmzetReport',
                'getTopSellingProducts', 'getSalesComparison', 'getSalesByCategory', 'getLowStockHighSalesProducts',
                'getAllUsers', 'getUserManagementSummary', 'getCustomerStatistics', 'getTopSpendingCustomer',
                'getCurrentUserProfile', 'getDatabaseOverview', 'getMenuOverview', 'getAuditLogsToday',
                'readTableData', 'getAllTablesOverview', 'describeTable'
            ],
            'prohibitedTools' => [],
            'allowedTables' => [
                'categories', 'products', 'orders', 'order_details', 'payments', 'users',
                'reviews', 'system_settings', 'system_audit_logs', 'otp_requests',
                'whatsapp_notification_logs', 'ai_conversations'
            ],
            'prohibitedTables' => [],
            'denialMessage' => 'Akses ditolak.'
        ],
        self::ROLE_OWNER => [
            'allowedTools' => [
                'searchProducts', 'getAllProducts', 'getProductDetails', 'getCheapestProduct',
                'getMostExpensiveProduct', 'getLatestProducts', 'checkProductStock', 'getTotalStock',
                'getLowStockProducts', 'getOutOfStockProducts', 'getRestockPriorityProducts', 'getActiveProductCount',
                'getProductCategories', 'getCategoryStats',
                'getOrdersNeedingProcessing', 'getPendingPaymentOrdersCount', 'getAllOrders', 'getOrderDetails',
                'getOrdersByStatus', 'getOrderCustomer', 'getOperationalQueue',
                'getAllPayments', 'getPaymentSummary', 'getPaymentMethodsBreakdown', 'getSalesSummary', 'getOmzetReport',
                'getTopSellingProducts', 'getSalesComparison', 'getSalesByCategory', 'getLowStockHighSalesProducts',
                'getAllUsers', 'getUserManagementSummary', 'getCustomerStatistics', 'getTopSpendingCustomer',
                'getCurrentUserProfile', 'getDatabaseOverview', 'getMenuOverview', 'getAuditLogsToday',
                'readTableData', 'getAllTablesOverview', 'describeTable'
            ],
            'prohibitedTools' => [],
            'allowedTables' => [
                'categories', 'products', 'orders', 'order_details', 'payments', 'users',
                'reviews', 'system_settings', 'system_audit_logs', 'otp_requests',
                'whatsapp_notification_logs', 'ai_conversations'
            ],
            'prohibitedTables' => [],
            'denialMessage' => 'Akses ditolak.'
        ]
    ];

    /**
     * Cek apakah role tertentu memiliki izin menjalankan tool/analitik
     */
    public static function isToolAllowed(string $toolName, string $role): bool {
        $normalizedRole = ucfirst(strtolower(trim($role)));
        if (!isset(self::$PERMISSIONS[$normalizedRole])) {
            $normalizedRole = self::ROLE_CUSTOMER;
        }

        $config = self::$PERMISSIONS[$normalizedRole];
        if (in_array($toolName, $config['prohibitedTools'] ?? [], true)) {
            return false;
        }
        return in_array($toolName, $config['allowedTools'] ?? [], true);
    }

    /**
     * Cek apakah role tertentu memiliki izin membaca isi suatu tabel secara langsung
     */
    public static function isTableAccessAllowed(string $tableName, string $role): bool {
        $normalizedRole = ucfirst(strtolower(trim($role)));
        if (!isset(self::$PERMISSIONS[$normalizedRole])) {
            $normalizedRole = self::ROLE_CUSTOMER;
        }

        $config = self::$PERMISSIONS[$normalizedRole];
        if (in_array($tableName, $config['prohibitedTables'] ?? [], true)) {
            return false;
        }
        return in_array($tableName, $config['allowedTables'] ?? [], true);
    }

    /**
     * Dapatkan pesan penolakan yang ramah dan profesional jika hak akses tidak terpenuhi
     */
    public static function getDenialMessage(string $toolName, string $role, ?string $tableName = null): string {
        $normalizedRole = ucfirst(strtolower(trim($role)));
        if (!isset(self::$PERMISSIONS[$normalizedRole])) {
            $normalizedRole = self::ROLE_CUSTOMER;
        }

        if ($tableName !== null) {
            if ($normalizedRole === self::ROLE_CUSTOMER) {
                return "Maaf, tabel `{$tableName}` memuat data rahasia/internal toko yang tidak dapat diakses oleh Customer. Anda hanya memiliki izin untuk melihat katalog produk, kategori, dan pesanan akun Anda sendiri.";
            }
            if ($normalizedRole === self::ROLE_OPERATOR) {
                return "Maaf, tabel `{$tableName}` memuat konfigurasi sensitif atau log tingkat administrator yang tidak dapat diakses oleh Operator. Anda memiliki wewenang pada antrean pesanan, pengiriman, dan stok inventaris.";
            }
        }

        return self::$PERMISSIONS[$normalizedRole]['denialMessage'] ?? 'Maaf, peran akun Anda tidak memiliki izin untuk mengakses data tersebut.';
    }

    /**
     * Mendapatkan daftar tool yang diizinkan untuk suatu role
     */
    public static function getAllowedToolsForRole(string $role): array {
        $normalizedRole = ucfirst(strtolower(trim($role)));
        if (!isset(self::$PERMISSIONS[$normalizedRole])) {
            $normalizedRole = self::ROLE_CUSTOMER;
        }
        return self::$PERMISSIONS[$normalizedRole]['allowedTools'] ?? [];
    }
}
