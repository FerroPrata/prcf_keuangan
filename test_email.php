<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email OTP - PRCFI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-2xl w-full mx-auto p-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">🧪 Test Email OTP System</h1>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once 'config.php';
                
                $test_email = $_POST['test_email'];
                $test_otp = rand(100000, 999999);
                
                echo '<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">';
                echo '<p class="text-sm text-blue-800"><strong>Testing dengan:</strong></p>';
                echo '<p class="text-sm text-blue-600">Email: ' . htmlspecialchars($test_email) . '</p>';
                echo '<p class="text-sm text-blue-600">OTP: ' . $test_otp . '</p>';
                echo '</div>';
                
                echo '<div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded">';
                echo '<p class="text-sm text-gray-700 mb-2"><strong>🔍 Checking PHP Extensions:</strong></p>';
                echo '<ul class="text-sm space-y-1">';
                
                // Check OpenSSL
                if (extension_loaded('openssl')) {
                    echo '<li class="text-green-600">✅ OpenSSL: Enabled</li>';
                } else {
                    echo '<li class="text-red-600">❌ OpenSSL: Disabled</li>';
                    echo '<li class="text-xs text-red-500 ml-4">→ Run enable_email.bat as Administrator</li>';
                }
                
                // Check cURL
                if (extension_loaded('curl')) {
                    echo '<li class="text-green-600">✅ cURL: Enabled</li>';
                } else {
                    echo '<li class="text-orange-600">⚠️ cURL: Disabled (optional)</li>';
                }
                
                // Check sockets
                if (extension_loaded('sockets')) {
                    echo '<li class="text-green-600">✅ Sockets: Enabled</li>';
                } else {
                    echo '<li class="text-orange-600">⚠️ Sockets: Disabled (optional)</li>';
                }
                
                echo '</ul></div>';
                
                echo '<div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded">';
                echo '<p class="text-sm text-gray-700 mb-2"><strong>📧 Sending test email...</strong></p>';
                echo '<div class="text-sm">';
                
                $start_time = microtime(true);
                $result = send_otp_email($test_email, $test_otp);
                $end_time = microtime(true);
                $duration = round(($end_time - $start_time) * 1000);
                
                if ($result) {
                    echo '<p class="text-green-600 font-bold">✅ EMAIL BERHASIL TERKIRIM!</p>';
                    echo '<p class="text-xs text-gray-500 mt-1">Duration: ' . $duration . 'ms</p>';
                    echo '<div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">';
                    echo '<p class="text-sm text-green-800"><strong>✨ Sukses!</strong></p>';
                    echo '<p class="text-xs text-green-600 mt-1">Cek inbox email: ' . htmlspecialchars($test_email) . '</p>';
                    echo '<p class="text-xs text-green-600">Cari email dengan subject: "Kode OTP Login - PRCFI Financial"</p>';
                    echo '<p class="text-xs text-green-600">OTP yang dikirim: <strong>' . $test_otp . '</strong></p>';
                    echo '</div>';
                } else {
                    echo '<p class="text-red-600 font-bold">❌ EMAIL GAGAL TERKIRIM</p>';
                    echo '<p class="text-xs text-gray-500 mt-1">Duration: ' . $duration . 'ms</p>';
                    echo '<div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">';
                    echo '<p class="text-sm text-red-800"><strong>Kemungkinan Masalah:</strong></p>';
                    echo '<ul class="text-xs text-red-600 mt-2 space-y-1 ml-4">';
                    echo '<li>• OpenSSL extension belum enabled</li>';
                    echo '<li>• Firewall/Antivirus memblokir port 587</li>';
                    echo '<li>• App Password salah atau expired</li>';
                    echo '<li>• Tidak ada koneksi internet</li>';
                    echo '</ul>';
                    echo '</div>';
                }
                
                echo '</div></div>';
                
                // Check error log
                $error_log = 'C:\xampp\apache\logs\error.log';
                if (file_exists($error_log)) {
                    $log_content = file_get_contents($error_log);
                    $log_lines = explode("\n", $log_content);
                    $recent_errors = array_slice(array_reverse($log_lines), 0, 5);
                    
                    echo '<div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded">';
                    echo '<p class="text-sm text-gray-700 mb-2"><strong>📋 Recent PHP Errors:</strong></p>';
                    echo '<div class="text-xs font-mono bg-black text-green-400 p-3 rounded overflow-x-auto">';
                    foreach ($recent_errors as $line) {
                        if (trim($line)) {
                            echo htmlspecialchars($line) . "\n";
                        }
                    }
                    echo '</div></div>';
                }
            }
            ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Tujuan Test:</label>
                    <input type="email" name="test_email" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                        placeholder="your-email@gmail.com"
                        value="<?php echo isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : ''; ?>">
                    <p class="text-xs text-gray-500 mt-1">Masukkan email Anda sendiri untuk menerima test OTP</p>
                </div>
                
                <button type="submit" 
                    class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-200 font-medium">
                    🚀 Kirim Test Email
                </button>
            </form>
            
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                <p class="text-sm text-blue-800 font-semibold mb-2">💡 Troubleshooting Steps:</p>
                <ol class="text-xs text-blue-700 space-y-1 ml-4 list-decimal">
                    <li>Run <code class="bg-blue-100 px-1 rounded">enable_email.bat</code> as Administrator</li>
                    <li>Restart Apache di XAMPP Control Panel</li>
                    <li>Disable Antivirus sementara untuk test</li>
                    <li>Pastikan internet connection aktif</li>
                    <li>Cek Gmail App Password masih valid</li>
                </ol>
            </div>
            
            <div class="mt-6 text-center">
                <a href="login.php" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Kembali ke Login
                </a>
            </div>
        </div>
        
        <div class="mt-4 text-center text-sm text-gray-600">
            <p>PRCFI Financial Management System</p>
        </div>
    </div>
</body>
</html>

