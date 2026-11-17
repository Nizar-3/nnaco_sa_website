<?php
require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/Flasher.php';
require_once 'core/Middleware.php';
require_once 'core/CSRF.php';

// Models (Core/System)
require_once 'models/SettingModel.php';
require_once 'models/AuditLogModel.php';
require_once 'models/OtpModel.php';
require_once 'models/WhatsAppLogModel.php';
require_once 'models/AiConversationModel.php';

// Services
require_once 'services/SecretService.php';
require_once 'services/SettingService.php';
require_once 'services/AIServiceInterface.php';
require_once 'services/MockAIService.php';
require_once 'services/GeminiAIService.php';
require_once 'services/GroqAIService.php';
require_once 'services/OwnerAIServiceInterface.php';
require_once 'services/MockOwnerAIService.php';
require_once 'services/GeminiOwnerAIService.php';
// WhatsApp Services (Fonnte)
require_once 'services/FonnteService.php';
require_once 'services/OrderNotificationService.php';

// Payment Services
require_once 'services/PaymentGatewayInterface.php';
require_once 'services/MockPaymentGateway.php';
require_once 'services/PaymentService.php';

require_once 'config/config.php';

// Auto-heal / normalisasi role sesi jika pengguna sedang login
if (isset($_SESSION['user_role']) && is_string($_SESSION['user_role'])) {
    $_SESSION['user_role'] = ucfirst(strtolower(trim($_SESSION['user_role'])));
}
