<?php
/**
 * ============================================================================
 * MAINTENANCE MODE CONFIGURATION - TEMPLATE
 * ============================================================================
 * 
 * INSTRUKSI SETUP:
 * ----------------
 * 1. Copy file ini dan rename jadi: maintenance_config.php
 * 2. Update IP whitelist sesuai kebutuhan
 * 3. Jangan commit maintenance_config.php ke Git (sudah ada di .gitignore)
 * 
 * COMMAND:
 * cp maintenance_config.example.php maintenance_config.php
 * 
 * File ini mengontrol status maintenance mode untuk website.
 * 
 * CARA MENGGUNAKAN:
 * -----------------
 * 
 * 1. ENABLE MAINTENANCE MODE:
 *    Set MAINTENANCE_MODE = true
 *    Website akan redirect semua user ke maintenance.php
 * 
 * 2. DISABLE MAINTENANCE MODE (Normal):
 *    Set MAINTENANCE_MODE = false
 *    Website berjalan normal
 * 
 * 3. IP WHITELIST:
 *    Tambahkan IP admin yang tetap bisa akses saat maintenance
 *    Format: array('127.0.0.1', '192.168.1.100')
 */

// ============================================================================
// MAINTENANCE MODE SETTING
// ============================================================================

// Set TRUE untuk enable maintenance mode, FALSE untuk disable
define('MAINTENANCE_MODE', false); // ← UBAH JADI true UNTUK MAINTENANCE

// ============================================================================
// IP WHITELIST (Optional)
// ============================================================================
// IP-IP ini tetap bisa akses website walau maintenance mode aktif
// Gunakan untuk admin atau developer

$MAINTENANCE_WHITELIST_IPS = [
    '127.0.0.1',        // Localhost
    '::1',              // Localhost IPv6
    
    // Tambahkan IP admin di sini:
    // '192.168.1.100',  // IP kantor
    // '203.0.113.45',   // IP admin 1
    // '198.51.100.12',  // IP admin 2
];

// ============================================================================
// MAINTENANCE MESSAGE (Optional)
// ============================================================================
// Pesan yang ditampilkan di halaman maintenance

define('MAINTENANCE_TITLE', 'Sedang Dalam Perbaikan');
define('MAINTENANCE_MESSAGE', 'Kami sedang melakukan maintenance dan update sistem untuk meningkatkan layanan Anda.');
define('MAINTENANCE_ESTIMATE', '1-2 Jam');

// ============================================================================
// MAINTENANCE SCHEDULE (Optional)
// ============================================================================
// Jadwal maintenance otomatis (future feature)

define('MAINTENANCE_START', ''); // Format: 'Y-m-d H:i:s' atau kosongkan
define('MAINTENANCE_END', '');   // Format: 'Y-m-d H:i:s' atau kosongkan

// ============================================================================
// HELPER FUNCTION
// ============================================================================

/**
 * Check if current IP is whitelisted
 */
function is_ip_whitelisted() {
    global $MAINTENANCE_WHITELIST_IPS;
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($user_ip, $MAINTENANCE_WHITELIST_IPS);
}

/**
 * Check if maintenance mode is active
 */
function is_maintenance_active() {
    if (!MAINTENANCE_MODE) {
        return false;
    }
    
    // Check if current IP is whitelisted
    if (is_ip_whitelisted()) {
        return false; // Admin can bypass maintenance
    }
    
    // Check scheduled maintenance (if set)
    if (!empty(MAINTENANCE_START) && !empty(MAINTENANCE_END)) {
        $now = time();
        $start = strtotime(MAINTENANCE_START);
        $end = strtotime(MAINTENANCE_END);
        
        if ($now < $start || $now > $end) {
            return false; // Outside maintenance window
        }
    }
    
    return true;
}

/**
 * Redirect to maintenance page if maintenance is active
 * Call this at the top of pages that need maintenance mode
 */
function check_maintenance() {
    // Skip if already on maintenance page
    if (basename($_SERVER['PHP_SELF']) === 'maintenance.php') {
        return;
    }
    
    if (is_maintenance_active()) {
        header('Location: maintenance.php');
        exit();
    }
}

?>

