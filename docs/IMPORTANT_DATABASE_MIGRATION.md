# ⚠️ PENTING: SQL MIGRATION DIPERLUKAN!

## 🚨 Error yang Anda Alami:

```
Fatal error: Call to a member function fetch_assoc() on bool 
in dashboard_dir.php:341
```

```
Fatal error: Call to a member function bind_param() on bool 
in review_proposal.php:105
```

---

## 🔍 Penyebab:

Database Anda **belum diupdate** dengan field baru untuk **2-stage approval system**.

Field yang hilang:

- `approved_by_fm` (ID user FM yang approve)
- `fm_approval_date` (tanggal FM approve)
- `approved_by_dir` (ID user DIR yang approve)
- `dir_approval_date` (tanggal DIR approve)

---

## ✅ Solusi: Jalankan SQL Migration

### **Step 1: Import SQL File**

Jalankan file SQL ini di phpMyAdmin:

**File:** `alter_proposal_2stage_approval.sql`

```sql
-- ============================================================================
-- SQL MIGRATION: 2-Stage Proposal Approval System
-- ============================================================================
-- Run this SQL in phpMyAdmin to add new columns for 2-stage approval

USE prcf_keuangan;

-- Add new columns for 2-stage approval
ALTER TABLE `proposal`
MODIFY COLUMN `status` ENUM('draft','submitted','approved_fm','approved','rejected') DEFAULT 'draft',
ADD COLUMN `approved_by_fm` INT(11) DEFAULT NULL AFTER `pemohon`,
ADD COLUMN `fm_approval_date` DATETIME DEFAULT NULL AFTER `approved_by_fm`,
ADD COLUMN `approved_by_dir` INT(11) DEFAULT NULL AFTER `fm_approval_date`,
ADD COLUMN `dir_approval_date` DATETIME DEFAULT NULL AFTER `approved_by_dir`;

-- Add foreign key constraints
ALTER TABLE `proposal`
ADD CONSTRAINT `fk_approved_by_fm` FOREIGN KEY (`approved_by_fm`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_approved_by_dir` FOREIGN KEY (`approved_by_dir`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

-- Verify the changes
SHOW COLUMNS FROM `proposal`;
```

### **Step 2: Cara Import di phpMyAdmin**

1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Pilih database `prcf_keuangan`
3. Klik tab **SQL**
4. Copy-paste script SQL di atas
5. Klik **Go** / **Kirim**
6. Selesai! ✅

---

## 🔧 Temporary Fix (Sudah Diterapkan)

Saya sudah menambahkan **fallback logic** di code supaya tidak error jika SQL belum dijalankan:

```php
// Check if approved_by_fm column exists (2-stage approval feature)
$check_column = $conn->query("SHOW COLUMNS FROM proposal LIKE 'approved_by_fm'");
if ($check_column && $check_column->num_rows > 0) {
    // 2-stage approval is active
    // ... use new fields
} else {
    // Fallback: 2-stage approval not yet enabled
    // ... use old logic
}
```

**Tapi tetap disarankan untuk run SQL migration agar fitur 2-stage approval berfungsi penuh!**

---

## 📊 Sebelum vs Sesudah Migration

### **Before (❌ Error):**

```
Table: proposal
├── id_proposal
├── judul_proposal
├── status (draft, submitted, approved, rejected)
├── pemohon
└── ...
```

### **After (✅ Working):**

```
Table: proposal
├── id_proposal
├── judul_proposal
├── status (draft, submitted, approved_fm, approved, rejected)  ← NEW STATUS!
├── pemohon
├── approved_by_fm        ← NEW!
├── fm_approval_date      ← NEW!
├── approved_by_dir       ← NEW!
├── dir_approval_date     ← NEW!
└── ...
```

---

## 🎯 Fitur Setelah Migration:

### **Status Workflow:**

```
submitted → approved_fm → approved
            (FM approve) (DIR approve)
               Stage 1      Stage 2
```

### **Visual Indicators:**

- 🟡 **Submitted** → Menunggu FM
- 🔵 **1/2 Approved (FM)** → Menunggu DIR
- 🟢 **2/2 Approved (Final)** → Selesai

---

## ✅ Checklist:

- [X] Import `alter_proposal_2stage_approval.sql` di phpMyAdmin
- [X] Refresh halaman Dashboard Direktur
- [X] Test: FM approve proposal
- [X] Verify: Status menunjukkan "1/2 Approved (FM)"
- [ ] Test: DIR approve proposal
- [ ] Verify: Status menunjukkan "2/2 Approved (Final)"

---

**PENTING:** Jalankan SQL migration ini **SEBELUM** testing fitur 2-stage approval!

**Location:** File `alter_proposal_2stage_approval.sql` sudah ada di project root.
