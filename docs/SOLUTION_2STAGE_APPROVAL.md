# ✅ Solusi: 2-Stage Approval Tidak Berfungsi

## 🔍 Diagnosis Masalah

**Gejala:**  
FM approve proposal → status langsung "approved" di dashboard DIR (bukan "1/2 approved_fm")

**Penyebab:**  
❌ **SQL Migration belum dijalankan!**  
Field `approved_by_fm`, `fm_approval_date`, `approved_by_dir`, `dir_approval_date` **tidak ada** di database.

---

## 🎯 Solusi 3-Langkah

### **Step 1: Cek Status 2-Stage Approval**

Buka file ini di browser:
```
http://localhost/prcf_keuangan_dashboard/CHECK_2STAGE_STATUS.php
```

File ini akan menunjukkan:
- ✅ Hijau = 2-stage approval **AKTIF**
- ❌ Merah = 2-stage approval **BELUM AKTIF** (perlu run SQL)

---

### **Step 2: Jalankan SQL Migration**

**Jika status menunjukkan ❌ Merah:**

1. **Buka phpMyAdmin:**  
   `http://localhost/phpmyadmin`

2. **Pilih database:**  
   Klik `prcf_keuangan` di sidebar kiri

3. **Buka tab SQL:**  
   Klik tab "SQL" di menu atas

4. **Copy-paste SQL ini:**

```sql
-- ============================================================================
-- SQL MIGRATION: 2-Stage Proposal Approval System
-- ============================================================================

USE prcf_keuangan;

-- Step 1: Modify status ENUM to include 'approved_fm'
ALTER TABLE `proposal`
MODIFY COLUMN `status` ENUM('draft','submitted','approved_fm','approved','rejected') DEFAULT 'draft';

-- Step 2: Add new columns for FM approval
ALTER TABLE `proposal`
ADD COLUMN `approved_by_fm` INT(11) DEFAULT NULL AFTER `pemohon`,
ADD COLUMN `fm_approval_date` DATETIME DEFAULT NULL AFTER `approved_by_fm`;

-- Step 3: Add new columns for DIR approval
ALTER TABLE `proposal`
ADD COLUMN `approved_by_dir` INT(11) DEFAULT NULL AFTER `fm_approval_date`,
ADD COLUMN `dir_approval_date` DATETIME DEFAULT NULL AFTER `approved_by_dir`;

-- Step 4: Add foreign key constraints
ALTER TABLE `proposal`
ADD CONSTRAINT `fk_approved_by_fm` FOREIGN KEY (`approved_by_fm`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_approved_by_dir` FOREIGN KEY (`approved_by_dir`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

-- Verify the changes
SHOW COLUMNS FROM `proposal`;

SELECT 'Migration completed successfully!' AS Status;
```

5. **Klik tombol "Go" / "Kirim"**

6. **Verifikasi:**  
   - Refresh `CHECK_2STAGE_STATUS.php`
   - Harus muncul ✅ Hijau

---

### **Step 3: Test 2-Stage Approval**

**A. Test sebagai Finance Manager:**

1. Login sebagai FM
2. Review proposal yang status "submitted"
3. Klik **"Approve"**
4. **Expected Result:**  
   ✅ Status berubah jadi **"approved_fm"** (bukan "approved")  
   ✅ Notifikasi: "Proposal approved (1/2)"

**B. Cek Dashboard Direktur:**

1. Login sebagai Direktur
2. Buka dashboard
3. **Expected Result:**  
   ✅ Proposal muncul dengan status **"1/2 Approved (FM)"**  
   ✅ Tombol aksi: **"Approve Stage 2"** (bukan "Review")

**C. Test sebagai Direktur:**

1. Klik **"Approve Stage 2"**
2. **Expected Result:**  
   ✅ Status berubah jadi **"approved"** (Final)  
   ✅ Display: **"2/2 Approved (Final)"**

---

## 🔧 Apa yang Sudah Diperbaiki

### **1. approve_proposal.php - Auto-Check & Fallback**

```php
// Check if 2-stage approval is enabled
$check_column = $conn->query("SHOW COLUMNS FROM proposal LIKE 'approved_by_fm'");
$two_stage_enabled = ($check_column && $check_column->num_rows > 0);

if ($two_stage_enabled) {
    // 2-stage: FM → approved_fm → DIR → approved
    $stmt = $conn->prepare("UPDATE proposal SET status = 'approved_fm', approved_by_fm = ?, fm_approval_date = NOW() WHERE id_proposal = ?");
} else {
    // Fallback: FM → approved (direct)
    $stmt = $conn->prepare("UPDATE proposal SET status = 'approved' WHERE id_proposal = ?");
    
    // Show warning
    $error = '⚠️ Warning: 2-stage approval belum diaktifkan. Import alter_proposal_2stage_approval.sql';
}
```

**Benefits:**
- ✅ Tidak error jika SQL belum dijalankan
- ✅ Warning message jika 2-stage disabled
- ✅ Auto-detect kapan 2-stage enabled

---

### **2. CHECK_2STAGE_STATUS.php - Diagnostic Tool**

**Features:**
- 🔍 Check semua kolom yang diperlukan
- ✅ Visual indicator (hijau/merah)
- 📋 SQL migration preview
- 🔄 Refresh button
- 📚 Step-by-step guide

---

## 📊 Status Comparison

### **Before Migration:**

| Role | FM Approve | Status di DB | Status di Dashboard DIR |
|------|-----------|--------------|------------------------|
| FM | Klik "Approve" | `approved` | ❌ "Approved" (langsung final) |
| DIR | - | - | ❌ Tidak ada yang bisa di-approve |

**Result:** ❌ 1-stage approval (FM saja, DIR tidak ada role)

---

### **After Migration:**

| Role | FM Approve | Status di DB | Status di Dashboard DIR |
|------|-----------|--------------|------------------------|
| FM | Klik "Approve" | `approved_fm` | ✅ "1/2 Approved (FM)" |
| DIR | Klik "Approve Stage 2" | `approved` | ✅ "2/2 Approved (Final)" |

**Result:** ✅ 2-stage approval (FM → DIR, both must approve)

---

## 🎯 Workflow Setelah Fix

```
PM Buat Proposal
       │
       ▼
  [submitted]
       │
       ▼
FM Approve (Stage 1)
       │
       ▼
  [approved_fm]  ← Status: "1/2 Approved (FM)"
       │          Badge: Blue
       │          Button: "Approve Stage 2"
       ▼
DIR Approve (Stage 2)
       │
       ▼
  [approved]     ← Status: "2/2 Approved (Final)"
                   Badge: Green
                   Complete!
```

---

## ✅ Checklist

- [ ] Step 1: Buka `CHECK_2STAGE_STATUS.php`
- [ ] Step 2: Jika merah, jalankan SQL migration di phpMyAdmin
- [ ] Step 3: Refresh `CHECK_2STAGE_STATUS.php` → harus hijau
- [ ] Step 4: Test FM approve → status jadi `approved_fm`
- [ ] Step 5: Test DIR approve → status jadi `approved`
- [ ] Step 6: Verify di dashboard DIR ada tombol "Approve Stage 2"

---

## 🐛 Troubleshooting

### **Issue: Tetap error setelah run SQL**

**Solution:**
```sql
-- Drop existing foreign keys if any
ALTER TABLE `proposal` DROP FOREIGN KEY IF EXISTS `fk_approved_by_fm`;
ALTER TABLE `proposal` DROP FOREIGN KEY IF EXISTS `fk_approved_by_dir`;

-- Then run the migration again
```

---

### **Issue: Status tetap langsung "approved"**

**Diagnosis:**
1. Cek `CHECK_2STAGE_STATUS.php` → harus hijau
2. Jika masih merah, SQL migration belum berhasil
3. Check error di phpMyAdmin saat run SQL

---

### **Issue: "Column already exists"**

**Solution:**  
Column sudah ada, tapi mungkin foreign key belum. Run only:
```sql
ALTER TABLE `proposal`
ADD CONSTRAINT `fk_approved_by_fm` FOREIGN KEY (`approved_by_fm`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_approved_by_dir` FOREIGN KEY (`approved_by_dir`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;
```

---

## 📁 Files Modified/Created

| File | Status | Purpose |
|------|--------|---------|
| `approve_proposal.php` | ✅ Updated | Auto-check + fallback logic |
| `CHECK_2STAGE_STATUS.php` | ✅ Created | Diagnostic tool |
| `SOLUTION_2STAGE_APPROVAL.md` | ✅ Created | This guide |
| `alter_proposal_2stage_approval.sql` | ✅ Exists | SQL migration script |

---

## 🎉 Expected Result

**Setelah SQL Migration:**

1. **Dashboard FM:** Approve → status "approved_fm"
2. **Dashboard DIR:** 
   - Proposal muncul dengan badge **"1/2 Approved (FM)"**
   - Tombol: **"Approve Stage 2"**
3. **DIR Approve:** Status jadi **"2/2 Approved (Final)"**

---

**Status:** ✅ Ready to Fix!  
**Action Required:** Run SQL Migration di phpMyAdmin  
**Verification:** Use `CHECK_2STAGE_STATUS.php`

