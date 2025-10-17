# 🔄 2-Stage Approval System - Implementation Guide

## 📋 Overview

Sistem approval proposal sekarang menggunakan **2-stage approval**:
1. **Stage 1**: Finance Manager (FM) approve
2. **Stage 2**: Direktur (DIR) approve (final)

---

## 🎯 Approval Flow

### **Before (Old System):**
```
PM Submit → FM Approve → ✅ DONE
```

### **After (New System):**
```
PM Submit → FM Approve (Stage 1) → DIR Approve (Stage 2) → ✅ DONE
                  ↓                         ↓
            Notify DIR               Notify PM & FM
```

---

## 🗄️ Database Changes

### **Step 1: Run SQL Migration**

File: `alter_proposal_2stage_approval.sql`

```sql
-- Import via phpMyAdmin or command line:
mysql -u root -p prcf_keuangan < alter_proposal_2stage_approval.sql
```

### **Changes Made:**

1. **New Status:** `approved_fm` (FM approved, waiting DIR)
2. **New Fields:**
   - `approved_by_fm` INT (FK to user)
   - `approved_by_dir` INT (FK to user)
   - `fm_approval_date` DATETIME
   - `dir_approval_date` DATETIME

### **Status Flow:**
- `draft` → PM still editing
- `submitted` → Waiting FM approval
- `approved_fm` → **NEW!** FM approved, waiting DIR
- `approved` → DIR approved (FINAL)
- `rejected` → Rejected by FM or DIR

---

## 💻 Code Changes

### **1. `approve_proposal.php`**

**Access Control:**
```php
// OLD: Only Direktur
if ($_SESSION['user_role'] !== 'Direktur') {
    header('Location: unauthorized.php');
}

// NEW: FM and Direktur
if (!in_array($_SESSION['user_role'], ['Finance Manager', 'Direktur'])) {
    header('Location: unauthorized.php');
}
```

**Approval Logic:**
```php
if ($user_role === 'Finance Manager' && $current['status'] === 'submitted') {
    // STAGE 1: FM Approve
    UPDATE proposal SET status = 'approved_fm', approved_by_fm = ? ...
    // Notify PM and Direktur
}

elseif ($user_role === 'Direktur' && $current['status'] === 'approved_fm') {
    // STAGE 2: DIR Approve (FINAL)
    UPDATE proposal SET status = 'approved', approved_by_dir = ? ...
    // Notify PM and FM
}
```

---

## 📊 Dashboard Updates Needed

### **Finance Manager Dashboard:**

Show proposals with `status = 'submitted'`:
```php
SELECT * FROM proposal WHERE status = 'submitted' ORDER BY created_at DESC
```

### **Direktur Dashboard:**

Show proposals with `status = 'approved_fm'` (waiting DIR approval):
```php
SELECT * FROM proposal WHERE status = 'approved_fm' ORDER BY updated_at DESC
```

### **Project Manager Dashboard:**

Show own proposals with different statuses:
```php
SELECT * FROM proposal 
WHERE pemohon = '{$user_name}' 
AND status IN ('submitted', 'approved_fm', 'approved', 'rejected')
ORDER BY updated_at DESC
```

**Status Display for PM:**
- `submitted` → "⏳ Menunggu FM"
- `approved_fm` → "✅ FM Approved, menunggu DIR"
- `approved` → "✅✅ APPROVED (Final)"
- `rejected` → "❌ Ditolak"

---

## 🔔 Notification Flow

### **Stage 1: FM Approves**
1. ✅ Status: `submitted` → `approved_fm`
2. 📧 Notify PM: "Proposal disetujui FM, menunggu DIR"
3. 📧 Notify Direktur: "Proposal menunggu approval Anda"

### **Stage 2: DIR Approves (Final)**
1. ✅ Status: `approved_fm` → `approved`
2. 📧 Notify PM: "Proposal APPROVED FINAL"
3. 📧 Notify FM: "Proposal telah mendapat final approval"

---

## 🎨 UI/UX Changes

### **Proposal List - Status Badge Colors:**

```php
<?php if ($proposal['status'] === 'submitted'): ?>
    <span class="bg-yellow-100 text-yellow-800">⏳ Menunggu FM</span>

<?php elseif ($proposal['status'] === 'approved_fm'): ?>
    <span class="bg-blue-100 text-blue-800">✅ FM Approved → Menunggu DIR</span>

<?php elseif ($proposal['status'] === 'approved'): ?>
    <span class="bg-green-100 text-green-800">✅✅ APPROVED (Final)</span>

<?php elseif ($proposal['status'] === 'rejected'): ?>
    <span class="bg-red-100 text-red-800">❌ Ditolak</span>
<?php endif; ?>
```

### **Approve Button - Role-Based:**

```php
<?php if ($user_role === 'Finance Manager' && $proposal['status'] === 'submitted'): ?>
    <button type="submit" name="approve" class="btn-primary">
        Approve (Stage 1 - FM)
    </button>

<?php elseif ($user_role === 'Direktur' && $proposal['status'] === 'approved_fm'): ?>
    <button type="submit" name="approve" class="btn-success">
        Final Approve (Stage 2 - DIR)
    </button>

<?php else: ?>
    <span class="text-gray-500">Status tidak valid untuk approval</span>
<?php endif; ?>
```

---

## 🧪 Testing Checklist

### **Test Scenario 1: Full Approval Flow**
- [ ] PM buat proposal → status `submitted`
- [ ] FM approve → status `approved_fm`
- [ ] FM dapat notif sukses
- [ ] DIR dapat notifikasi proposal baru
- [ ] DIR approve → status `approved` (final)
- [ ] PM dan FM dapat notif final approval

### **Test Scenario 2: Rejection**
- [ ] PM buat proposal → status `submitted`
- [ ] FM reject → status `rejected`
- [ ] PM dapat notif rejected

### **Test Scenario 3: Invalid Access**
- [ ] PM tidak bisa akses approve_proposal.php
- [ ] SA tidak bisa akses approve_proposal.php
- [ ] FM tidak bisa approve proposal dengan status selain `submitted`
- [ ] DIR tidak bisa approve proposal dengan status selain `approved_fm`

---

## 📝 User Guide

### **Untuk Project Manager:**

1. Buat proposal seperti biasa
2. Submit → status "Menunggu FM"
3. Tunggu approval dari FM
4. Jika FM approve → status "FM Approved, menunggu DIR"
5. Tunggu approval dari Direktur
6. Jika DIR approve → status "APPROVED (Final)" ✅

### **Untuk Finance Manager:**

1. Dashboard akan show proposal dengan status "Menunggu FM"
2. Review proposal
3. Approve/Reject
4. Jika approve → proposal diteruskan ke Direktur

### **Untuk Direktur:**

1. Dashboard akan show proposal dengan status "FM Approved"
2. Review proposal (sudah disetujui FM)
3. Final Approve/Reject
4. Jika approve → proposal final APPROVED ✅

---

## 🔄 Migration Steps (untuk User)

### **Step 1: Backup Database**
```bash
mysqldump -u root -p prcf_keuangan > backup_before_2stage.sql
```

### **Step 2: Run Migration**
```bash
mysql -u root -p prcf_keuangan < alter_proposal_2stage_approval.sql
```

### **Step 3: Verify Structure**
```sql
DESC proposal;
-- Should show: approved_by_fm, approved_by_dir, fm_approval_date, dir_approval_date
```

### **Step 4: Test System**
- Login sebagai PM → buat proposal
- Login sebagai FM → approve proposal
- Login sebagai DIR → final approve
- Check notifications

---

## ⚠️ Important Notes

1. **Existing Proposals**: Proposal lama dengan status `approved` tetap valid (sudah final)
2. **Backward Compatible**: System masih support old status
3. **Rollback**: Jika ada masalah, restore backup database
4. **Notifications**: Pastikan email notification sudah setup (atau pakai WhatsApp)

---

## ✅ Status

**Implementation**: ✅ COMPLETE  
**Testing**: ⏳ PENDING (needs SQL migration)  
**Documentation**: ✅ COMPLETE

---

**Date**: October 16, 2025  
**Version**: 2.0  
**Feature**: 2-Stage Approval System

