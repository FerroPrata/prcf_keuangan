# 🔧 Developer Mode - OTP Bypass Guide

## 📋 Overview

Fitur Developer Mode memungkinkan developer untuk **bypass OTP verification** saat login, sehingga mempercepat proses development dan testing.

**Status**: ✅ **AKTIF** (sudah dikonfigurasi)

---

## 🚀 Cara Menggunakan

### **Option 1: Developer Email Whitelist (Recommended)**

Developer yang email-nya terdaftar akan otomatis bypass OTP.

#### **Konfigurasi di `config.php`:**

```php
// Enable developer mode
define('DEVELOPER_MODE', true);

// Daftar email developer
$DEVELOPER_EMAILS = [
    '12345c4n12345@gmail.com',    // Chandra (FM)
    'ferrosipratamaq@gmail.com',   // Ferrosi (Direktur)
    'hasukeyous@gmail.com',        // Yoga (PM)
    'your-email@example.com',      // Tambahkan email Anda di sini
];
```

#### **Cara Tambah Developer Baru:**

1. Edit file `config.php`
2. Tambahkan email baru di array `$DEVELOPER_EMAILS`:
   ```php
   'newemail@example.com',  // Deskripsi developer
   ```
3. Save & refresh browser

---

### **Option 2: Skip OTP untuk Semua User (Not Recommended)**

Disable OTP verification untuk **SEMUA** user (use with caution!).

```php
define('SKIP_OTP_FOR_ALL', true);  // Set to FALSE to enable OTP again
```

⚠️ **Warning**: Opsi ini tidak aman untuk production!

---

## 🎯 Cara Login sebagai Developer

### **Normal User (dengan OTP):**
```
1. Email/HP: user@example.com
2. Password: ********
3. ⏳ Tunggu OTP via WhatsApp
4. Input OTP: 123456
5. ✅ Login sukses
```

### **Developer (bypass OTP):**
```
1. Email: 12345c4n12345@gmail.com  ← Email terdaftar di DEVELOPER_EMAILS
2. Password: ********
3. 🚀 Langsung masuk dashboard! (skip OTP)
4. ✅ Login sukses
```

---

## 📊 Status Check

### **Cek apakah Developer Mode aktif:**

Login dengan email yang terdaftar di `$DEVELOPER_EMAILS`:
- ✅ **Berhasil bypass**: Langsung masuk dashboard tanpa OTP
- ❌ **Tidak bypass**: Masih diminta OTP (cek config.php)

### **Log di error.log:**

Jika berhasil bypass, akan muncul log:
```
🔧 Developer Mode: OTP bypassed for 12345c4n12345@gmail.com
```

**Location log**: `C:\xampp\apache\logs\error.log`

---

## ⚙️ Konfigurasi Detail

### **File: `config.php`**

```php
// ----------------------------------------------------------------------------
// DEVELOPER MODE - OTP BYPASS CONFIGURATION 🔧
// ----------------------------------------------------------------------------

// Enable/Disable Developer Mode
define('DEVELOPER_MODE', true);  // ← Set FALSE untuk production!

// Daftar email developer yang bisa bypass OTP
$DEVELOPER_EMAILS = [
    '12345c4n12345@gmail.com',    // Chandra (FM)
    'ferrosipratamaq@gmail.com',   // Ferrosi (Direktur)
    'hasukeyous@gmail.com',        // Yoga (PM)
];

// Alternative: Skip OTP untuk SEMUA user (not recommended)
define('SKIP_OTP_FOR_ALL', false);  // ← Set TRUE untuk disable OTP semuanya
```

### **File: `login.php`**

Logic OTP bypass sudah terintegrasi:

```php
// Check if user is developer
$is_developer = (defined('DEVELOPER_MODE') && DEVELOPER_MODE && 
                isset($DEVELOPER_EMAILS) && in_array($user['email'], $DEVELOPER_EMAILS));

if ($is_developer || $skip_all_otp) {
    // 🚀 BYPASS OTP - Direct login
    $_SESSION['logged_in'] = true;
    // ... redirect to dashboard
}
```

---

## 🔒 Security Best Practices

### **✅ DO:**
- ✅ Gunakan `DEVELOPER_MODE = true` hanya di **local development**
- ✅ Tambahkan hanya email developer yang **trusted**
- ✅ Set `DEVELOPER_MODE = false` di **production server**
- ✅ Gunakan `.gitignore` untuk `config.php` (jangan commit ke GitHub)

### **❌ DON'T:**
- ❌ Jangan set `SKIP_OTP_FOR_ALL = true` di production
- ❌ Jangan tambahkan email client/user biasa ke `$DEVELOPER_EMAILS`
- ❌ Jangan commit `config.php` dengan `DEVELOPER_MODE = true`
- ❌ Jangan lupa disable developer mode saat deploy

---

## 🧪 Testing

### **Test Case 1: Developer Login (Bypass OTP)**

**Input:**
```
Email: 12345c4n12345@gmail.com
Password: (your password)
```

**Expected:**
- ✅ Langsung masuk dashboard
- ✅ Tidak ada halaman OTP
- ✅ Log: "🔧 Developer Mode: OTP bypassed..."

---

### **Test Case 2: Normal User Login (OTP Required)**

**Input:**
```
Email: normaluser@example.com  ← Not in DEVELOPER_EMAILS
Password: (your password)
```

**Expected:**
- ✅ Redirect ke halaman OTP
- ✅ Menerima OTP via WhatsApp
- ✅ Harus input OTP untuk login

---

### **Test Case 3: Disable Developer Mode**

**Config:**
```php
define('DEVELOPER_MODE', false);  // ← Disabled
```

**Expected:**
- ✅ SEMUA user (termasuk developer) harus input OTP
- ✅ Tidak ada bypass untuk siapapun

---

## 📝 Deployment Checklist

Sebelum deploy ke **production server**, pastikan:

- [ ] Set `DEVELOPER_MODE = false` di `config.php`
- [ ] Set `SKIP_OTP_FOR_ALL = false` di `config.php`
- [ ] Hapus atau kosongkan `$DEVELOPER_EMAILS` array
- [ ] Test login dengan OTP untuk semua role
- [ ] Verify WhatsApp OTP working correctly
- [ ] Backup database sebelum deploy

---

## 🔄 Enable/Disable Developer Mode

### **Enable (Development):**

```php
define('DEVELOPER_MODE', true);
```

Restart Apache:
```bash
net stop Apache2.4 && net start Apache2.4
```

### **Disable (Production):**

```php
define('DEVELOPER_MODE', false);
```

Restart Apache:
```bash
net stop Apache2.4 && net start Apache2.4
```

---

## 📞 FAQ

### **Q: Apakah developer mode aman?**
A: Ya, **jika digunakan dengan benar**. Pastikan:
- Hanya aktif di local development
- Email developer trusted
- Disabled di production

### **Q: Bagaimana menambah developer baru?**
A: Edit `config.php`, tambahkan email di array `$DEVELOPER_EMAILS`, save, dan refresh.

### **Q: Apakah perlu restart Apache?**
A: Tidak perlu jika hanya mengubah array `$DEVELOPER_EMAILS`. Tapi jika ubah `define()`, sebaiknya restart Apache.

### **Q: Bagaimana cara test apakah bypass working?**
A: Login dengan email yang ada di `$DEVELOPER_EMAILS`. Jika langsung masuk dashboard tanpa OTP, berarti berhasil.

### **Q: Apakah bisa bypass OTP untuk role tertentu saja?**
A: Ya, bisa ditambahkan logic role-based bypass di `login.php`. Tapi tidak recommended karena security risk.

---

## ✅ Status Saat Ini

**Configuration:**
- ✅ `DEVELOPER_MODE`: **ENABLED**
- ✅ `SKIP_OTP_FOR_ALL`: **DISABLED** (recommended)

**Developer Emails Registered:**
1. `12345c4n12345@gmail.com` - Chandra (Finance Manager)
2. `ferrosipratamaq@gmail.com` - Ferrosi (Direktur)
3. `hasukeyous@gmail.com` - Yoga (Project Manager)

**Ready to use!** 🚀

Silakan login dengan salah satu email di atas untuk test bypass OTP.

---

## 📄 Related Files

- `config.php` - Main configuration
- `login.php` - Login logic with bypass
- `verify_otp.php` - OTP verification page
- `SETUP_FONNTE.md` - WhatsApp OTP setup guide

---

**Last Updated**: October 16, 2025  
**Version**: 1.0  
**Author**: AI Assistant

