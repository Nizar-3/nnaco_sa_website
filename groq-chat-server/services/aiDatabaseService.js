/**
 * aiDatabaseService.js
 * Database Access & Tool Execution Service for nnaco AI Assistant
 * Integrates schema awareness, RBAC permission verification, and parameterized query execution.
 */

const productRepository = require('../repositories/productRepository');
const orderRepository = require('../repositories/orderRepository');
const paymentRepository = require('../repositories/paymentRepository');
const userRepository = require('../repositories/userRepository');
const categoryRepository = require('../repositories/categoryRepository');
const reviewRepository = require('../repositories/reviewRepository');
const databaseRepository = require('../repositories/databaseRepository');
const rolePermissionService = require('./rolePermissionService');
const databaseSchemaService = require('./databaseSchemaService');
const aiQueryService = require('./aiQueryService');

class AiDatabaseService {
    isToolAllowedForRole(toolName, role) {
        return rolePermissionService.isToolAllowed(toolName, role);
    }

    getAllowedToolsForRole(role) {
        const roleConfig = rolePermissionService.PERMISSIONS[role] || rolePermissionService.PERMISSIONS['Customer'];
        return roleConfig.allowedTools || [];
    }

    async executeTool(toolName, args = {}, user = { role: 'Customer', id: null }) {
        const role = user.role || 'Customer';

        // 1. Server-side Authorization Check
        if (!this.isToolAllowedForRole(toolName, role)) {
            console.warn(`[SECURITY] Access denied for tool '${toolName}' by role '${role}'`);
            return {
                success: false,
                error: rolePermissionService.getDenialMessage(toolName, role)
            };
        }

        try {
            switch (toolName) {
                // ==========================================
                // A. PRODUK & KATEGORI
                // ==========================================
                case 'searchProducts': {
                    const query = typeof args.query === 'string' ? args.query.trim().slice(0, 100) : '';
                    const category = typeof args.category === 'string' ? args.category.trim().slice(0, 50) : '';
                    const minPrice = args.minPrice !== undefined && args.minPrice !== null ? Math.max(0, Number(args.minPrice)) : null;
                    const maxPrice = args.maxPrice !== undefined && args.maxPrice !== null ? Math.max(0, Number(args.maxPrice)) : null;
                    const sortBy = ['price_asc', 'price_desc', 'newest'].includes(args.sortBy) ? args.sortBy : 'price_asc';
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 5, 1), 10);

                    const products = await productRepository.searchProducts({ query, category, minPrice, maxPrice, sortBy, limit });
                    return {
                        success: true,
                        count: products.length,
                        data: products
                    };
                }

                case 'getProductDetails': {
                    const name = typeof args.productName === 'string' ? args.productName.trim().slice(0, 100) : '';
                    if (!name) return { success: false, error: 'Nama produk harus disertakan.' };

                    const product = await productRepository.getProductByName(name);
                    if (!product) {
                        return {
                            success: true,
                            found: false,
                            message: `Produk "${name}" tidak ditemukan di katalog nnaco_sa.`
                        };
                    }
                    return {
                        success: true,
                        found: true,
                        data: product
                    };
                }

                case 'getProductCategories': {
                    const categories = await productRepository.getCategories();
                    return {
                        success: true,
                        count: categories.length,
                        data: categories
                    };
                }

                case 'getCheapestProduct': {
                    const categoryName = typeof args.categoryName === 'string' ? args.categoryName.trim().slice(0, 50) : null;
                    const result = await aiQueryService.getCheapestProduct(categoryName);
                    return result;
                }

                case 'getMostExpensiveProduct': {
                    const result = await aiQueryService.getMostExpensiveProduct();
                    return result;
                }

                case 'getLatestProducts': {
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 5, 1), 10);
                    const products = await productRepository.getLatestProducts(limit);
                    return {
                        success: true,
                        count: products.length,
                        data: products
                    };
                }

                case 'getLowStockProducts': {
                    const threshold = Math.max(parseInt(args.threshold, 10) || 5, 1);
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 20);
                    const result = await aiQueryService.getLowStockProducts(threshold, limit);
                    return result;
                }

                case 'getOutOfStockProducts': {
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 20);
                    const result = await aiQueryService.getOutOfStockProducts(limit);
                    return result;
                }

                case 'getRestockPriorityProducts': {
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 20);
                    const result = await aiQueryService.getRestockPriorityProducts(limit);
                    return result;
                }

                case 'checkProductStock': {
                    const productName = typeof args.productName === 'string' ? args.productName.trim() : '';
                    const result = await aiQueryService.checkProductStock(productName);
                    return result;
                }

                case 'getActiveProductCount': {
                    const result = await productRepository.getActiveProductCount();
                    return {
                        success: true,
                        data: result
                    };
                }

                case 'getAllProducts': {
                    const category = typeof args.category === 'string' ? args.category.trim() : null;
                    const status = typeof args.status === 'string' ? args.status.trim() : null;
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 15, 1), 30);
                    const products = await productRepository.getAllProducts({ category, status, limit });
                    return {
                        success: true,
                        count: products.length,
                        data: products
                    };
                }

                case 'getCategoryStats': {
                    const categories = await categoryRepository.getAllCategoriesWithStats();
                    return {
                        success: true,
                        count: categories.length,
                        data: categories
                    };
                }

                case 'getCategoryDetails': {
                    const identifier = args.categoryName || args.categoryId;
                    const details = await categoryRepository.getCategoryByNameOrId(identifier);
                    if (!details) {
                        return {
                            success: true,
                            found: false,
                            message: `Kategori "${identifier}" tidak ditemukan.`
                        };
                    }
                    return {
                        success: true,
                        found: true,
                        data: details
                    };
                }

                // ==========================================
                // B. STOK & INVENTARIS
                // ==========================================
                case 'getTotalStock': {
                    const result = await aiQueryService.getTotalStock();
                    return result;
                }

                case 'getStockSoldUnits': {
                    const result = await aiQueryService.getStockSoldUnits();
                    return result;
                }

                // ==========================================
                // C. PESANAN
                // ==========================================
                case 'getMyOrders': {
                    if (!user.id) {
                        return {
                            success: false,
                            error: 'Silakan login ke akun Anda terlebih dahulu untuk memeriksa riwayat pesanan Anda.'
                        };
                    }
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 5, 1), 10);
                    const orders = await orderRepository.getCustomerOrders(user.id, limit);
                    return {
                        success: true,
                        count: orders.length,
                        customer_name: user.name,
                        data: orders
                    };
                }

                case 'getMyOrderStatus': {
                    if (!user.id) {
                        return {
                            success: false,
                            error: 'Silakan login ke akun Anda terlebih dahulu untuk memeriksa status pesanan Anda.'
                        };
                    }
                    const orderId = parseInt(args.orderId, 10);
                    if (!orderId) {
                        const orders = await orderRepository.getCustomerOrders(user.id, 1);
                        if (orders.length === 0) {
                            return {
                                success: true,
                                found: false,
                                data: {
                                    found: false,
                                    message: 'Anda belum memiliki riwayat pesanan di nnaco_sa.'
                                }
                            };
                        }
                        return {
                            success: true,
                            found: true,
                            data: orders[0]
                        };
                    }

                    const order = await orderRepository.getCustomerOrderById(user.id, orderId);
                    if (!order) {
                        return {
                            success: true,
                            found: false,
                            data: {
                                found: false,
                                message: `Pesanan dengan ID #${orderId} tidak ditemukan pada akun Anda.`
                            }
                        };
                    }
                    return {
                        success: true,
                        found: true,
                        data: order
                    };
                }

                case 'getAllOrders': {
                    if (user.role === 'Customer') {
                        return {
                            success: false,
                            error: rolePermissionService.getDenialMessage('seluruh daftar pesanan', user.role)
                        };
                    }
                    const status = typeof args.status === 'string' ? args.status.trim() : null;
                    const search = typeof args.search === 'string' ? args.search.trim() : null;
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 25);
                    const orders = await orderRepository.getAllOrders({ status, search, limit });
                    return {
                        success: true,
                        count: orders.length,
                        data: rolePermissionService.sanitizeRows('orders', orders, user.role, user.id)
                    };
                }

                case 'getOrderDetails': {
                    const orderId = parseInt(args.orderId, 10);
                    if (!orderId) return { success: false, error: 'ID order harus disertakan.' };
                    const result = await aiQueryService.getOrderContents(orderId, user);
                    return result;
                }

                case 'getOrdersNeedingProcessing': {
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 20);
                    const orders = await orderRepository.getOrdersNeedingProcessing(limit);
                    return {
                        success: true,
                        count: orders.length,
                        data: orders
                    };
                }

                case 'getPendingPaymentOrdersCount': {
                    const result = await orderRepository.getPendingPaymentOrdersCount();
                    return {
                        success: true,
                        data: result
                    };
                }

                case 'getOrderManagementSummary': {
                    const result = await aiQueryService.getOrdersSummary(user);
                    return result;
                }

                // ==========================================
                // D. PEMBAYARAN
                // ==========================================
                case 'getAllPayments': {
                    if (!rolePermissionService.canAccessOmzet(user.role) && user.role !== 'Operator') {
                        return {
                            success: false,
                            error: rolePermissionService.getDenialMessage('seluruh pembayaran', user.role)
                        };
                    }
                    const status = typeof args.status === 'string' ? args.status.trim() : null;
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 25);
                    const payments = await paymentRepository.getAllPayments({ status, limit });
                    return {
                        success: true,
                        count: payments.length,
                        data: payments
                    };
                }

                case 'getPaymentSummary': {
                    const result = await aiQueryService.getPaymentsAnalytics(user);
                    return result;
                }

                case 'getPaymentMethodsBreakdown': {
                    const result = await aiQueryService.getPaymentMethodsBreakdown(user);
                    return result;
                }

                // ==========================================
                // E. LAPORAN OMZET & PENJUALAN (ADMIN/OWNER)
                // ==========================================
                case 'getSalesSummary':
                case 'getOmzetSummary':
                case 'getOmzetReport': {
                    const result = await aiQueryService.getOmzetReport(user);
                    return result;
                }

                case 'getSalesComparison': {
                    const result = await aiQueryService.getSalesComparison(user);
                    return result;
                }

                case 'getTopSellingProducts': {
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 5, 1), 10);
                    const result = await aiQueryService.getTopSellingProducts(user, limit);
                    return result;
                }

                // ==========================================
                // F. PENGGUNA & CUSTOMER
                // ==========================================
                case 'getCurrentUserProfile': {
                    if (!user.id) {
                        return {
                            success: true,
                            is_logged_in: false,
                            role: 'Guest / Tamu',
                            name: user.name || 'Tamu',
                            message: 'Pengguna saat ini belum login ke akun website (mengakses sebagai Tamu/Guest).'
                        };
                    }
                    const profile = await userRepository.getUserProfileById(user.id);
                    return {
                        success: true,
                        is_logged_in: true,
                        id: profile ? profile.id : user.id,
                        name: profile ? profile.name : user.name,
                        email: profile ? profile.email : (user.email || '-'),
                        role: profile ? profile.role : user.role,
                        status: profile ? profile.status : 'Active',
                        created_at: profile ? profile.created_at : null
                    };
                }

                case 'getAllUsers': {
                    if (!rolePermissionService.canAccessAllCustomers(user.role)) {
                        return {
                            success: false,
                            error: rolePermissionService.getDenialMessage('seluruh daftar pengguna', user.role)
                        };
                    }
                    const roleArg = typeof args.role === 'string' ? args.role.trim() : null;
                    const status = typeof args.status === 'string' ? args.status.trim() : null;
                    const search = typeof args.search === 'string' ? args.search.trim() : null;
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 25);
                    const users = await userRepository.getAllUsers({ role: roleArg, status, search, limit });
                    return {
                        success: true,
                        count: users.length,
                        data: rolePermissionService.sanitizeRows('users', users, user.role, user.id)
                    };
                }

                case 'getUserManagementSummary': {
                    if (!rolePermissionService.canAccessAllCustomers(user.role)) {
                        return {
                            success: false,
                            error: rolePermissionService.getDenialMessage('ringkasan data pengguna', user.role)
                        };
                    }
                    const summary = await userRepository.getUserManagementSummary();
                    return {
                        success: true,
                        data: summary
                    };
                }

                case 'getCustomerStatistics': {
                    const result = await aiQueryService.getCustomerStatistics(user);
                    return result;
                }

                case 'getTopSpendingCustomer': {
                    const result = await aiQueryService.getTopSpendingCustomer(user);
                    return result;
                }

                // ==========================================
                // G. OPERASIONAL
                // ==========================================
                case 'getOperationalQueue': {
                    const result = await aiQueryService.getOperationalQueue(user);
                    return result;
                }

                case 'getAuditLogsToday':
                case 'getAuditLogsSummary': {
                    const result = await aiQueryService.getAuditLogsToday(user);
                    return result;
                }

                // ==========================================
                // H. CROSS-DATABASE / MULTI-TABLE
                // ==========================================
                case 'getSalesByCategory': {
                    const result = await aiQueryService.getSalesByCategory(user);
                    return result;
                }

                case 'getLowStockHighSalesProducts': {
                    const result = await aiQueryService.getLowStockHighSalesProducts(user);
                    return result;
                }

                // ==========================================
                // ULASAN
                // ==========================================
                case 'getProductReviews': {
                    const identifier = args.productName || args.productId;
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 5, 1), 20);
                    const reviews = await reviewRepository.getProductReviews(identifier, limit);
                    if (!reviews) {
                        return {
                            success: true,
                            found: false,
                            message: `Produk "${identifier}" tidak ditemukan untuk ulasan.`
                        };
                    }
                    return {
                        success: true,
                        found: true,
                        data: reviews
                    };
                }

                case 'getReviewSummary': {
                    const summary = await reviewRepository.getReviewSummary();
                    return {
                        success: true,
                        data: summary
                    };
                }

                case 'getAllReviews': {
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 25);
                    const reviews = await reviewRepository.getAllReviews(limit);
                    return {
                        success: true,
                        count: reviews.length,
                        data: reviews
                    };
                }

                // ==========================================
                // SCHEMA & DATABASE OVERVIEW
                // ==========================================
                case 'getDatabaseOverview': {
                    const overview = await databaseRepository.getDatabaseOverview();
                    return {
                        success: true,
                        data: overview
                    };
                }

                case 'getDatabaseSchema': {
                    const schema = databaseSchemaService.getSchemaForRole(user.role);
                    return {
                        success: true,
                        data: schema
                    };
                }

                case 'getMenuOverview': {
                    const overview = await databaseRepository.getMenuOverview();
                    return {
                        success: true,
                        data: overview
                    };
                }

                case 'readTableData': {
                    const tableName = args.tableName;
                    const limit = Math.min(Math.max(parseInt(args.limit, 10) || 10, 1), 25);
                    const result = await databaseRepository.readTableData(tableName, limit, args.filter, user);
                    return result;
                }

                case 'getTableCount': {
                    const tableName = args.tableName;
                    const result = await databaseRepository.getTableCount(tableName);
                    return result;
                }

                default:
                    return {
                        success: false,
                        error: `Tool '${toolName}' tidak dikenal.`
                    };
            }
        } catch (error) {
            console.error(`[DATABASE ERROR in executeTool('${toolName}')]:`, error.message);
            return {
                success: false,
                error: 'Terjadi kendala saat membaca data dari database aplikasi.'
            };
        }
    }
}

module.exports = new AiDatabaseService();
