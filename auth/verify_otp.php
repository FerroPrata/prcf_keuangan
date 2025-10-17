<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/maintenance_config.php';

// Check maintenance mode
check_maintenance();

if (!isset($_SESSION['pending_login'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        $entered_otp = $_POST['otp'];
        $current_time = time();
        
        // Check if OTP expired (60 seconds)
        if ($current_time - $_SESSION['otp_time'] > 60) {
            $error = 'Kode OTP telah kadaluarsa';
        } elseif ($entered_otp == $_SESSION['otp']) {
            // OTP correct
            $user_id = $_SESSION['user_id'];
            
            // Get user data
            $stmt = $conn->prepare("SELECT * FROM user WHERE id_user = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            unset($_SESSION['otp']);
            unset($_SESSION['otp_time']);
            unset($_SESSION['pending_login']);
            unset($_SESSION['otp_attempts']);
            unset($_SESSION['demo_otp_display']);
            
            // Redirect based on role
            switch ($user['role']) {
                case 'Project Manager':
                    header('Location: ../pages/dashboards/dashboard_pm.php');
                    break;
                case 'Staff Accountant':
                    header('Location: ../pages/dashboards/dashboard_sa.php');
                    break;
                case 'Finance Manager':
                    header('Location: ../pages/dashboards/dashboard_fm.php');
                    break;
                case 'Direktur':
                    header('Location: ../pages/dashboards/dashboard_dir.php');
                    break;
                default:
                    header('Location: ../pages/dashboards/dashboard_pm.php');
            }
            exit();
        } else {
            $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
            $error = 'Kode OTP salah, ulangi lagi';
        }
    } elseif (isset($_POST['resend_otp'])) {
        $current_time = time();
        if ($current_time - $_SESSION['otp_time'] < 15) {
            $error = 'Tunggu sebentar sebelum meminta OTP baru';
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_time'] = $current_time;
            $_SESSION['otp_attempts'] = 0;

            if (defined('EMAIL_OTP_ENABLED') && EMAIL_OTP_ENABLED === true) {
                $email_sent = send_otp_email($_SESSION['user_email'], $otp);
                if ($email_sent) {
                    $success = 'Kode OTP baru telah dikirim ke email Anda.';
                } else {
                    $error = 'Gagal mengirim OTP email. Silakan coba lagi.';
                }
            } else {
                $error = 'OTP email saat ini tidak tersedia. Hubungi administrator.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - PRCFI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="px-8 py-10">
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-key text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Verifikasi Email OTP</h1>
                <p class="text-gray-600 text-sm">Kode verifikasi telah dikirim ke email Anda.</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
                <i class="fas fa-times-circle mr-2"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode OTP</label>
                    <input type="text" name="otp" maxlength="6" required autocomplete="one-time-code"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-2xl tracking-widest font-mono"
                        placeholder="0 0 0 0 0 0">
                    <p class="mt-2 text-sm text-gray-500 text-center">
                        Masukkan kode 6 digit yang dikirim ke email: <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                    </p>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <button type="submit" name="resend_otp" value="1" formnovalidate class="text-blue-600 hover:text-blue-700 font-medium">
                        Kirim ulang kode
                    </button>
                    <span class="text-gray-400">Kode berlaku selama 1 menit</span>
                </div>

                <button type="submit" name="verify_otp"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition duration-200 shadow-lg">
                    <i class="fas fa-check mr-2"></i> Verifikasi & Masuk
                </button>
            </form>
        </div>

        <div class="border-t border-gray-200 bg-gray-50 px-8 py-6 text-sm text-gray-500 text-center">
            <p>Belum menerima email?</p>
            <p class="mt-1">Periksa folder spam atau klik "Kirim ulang kode" untuk mendapatkan OTP baru.</p>
        </div>
    </div>
</body>
</html>