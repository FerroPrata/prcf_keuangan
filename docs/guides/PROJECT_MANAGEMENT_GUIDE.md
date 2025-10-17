# 📋 Panduan Kelola Proyek (Project Management)

## 📌 Deskripsi
Fitur ini memungkinkan **Finance Manager** untuk membuat dan mengelola kode proyek yang akan digunakan oleh Project Manager saat membuat proposal dan laporan keuangan.

---

## ✨ Fitur Utama

### 1. **Buat Proyek Baru**
Finance Manager dapat membuat proyek baru dengan informasi:
- **Kode Proyek** (wajib, format: PRJ-YYYY-XXX, huruf kapital)
- **Nama Proyek** (wajib)
- **Status Proyek** (Planning/Ongoing/Completed/Cancelled)
- **Donor** (opsional)
- **Nilai Anggaran** (opsional, format Rupiah)
- **Rekening Khusus** (opsional)
- **Periode Mulai** (opsional)
- **Periode Selesai** (opsional)

### 2. **Edit Proyek**
- Semua data proyek dapat diubah **kecuali Kode Proyek**
- Kode proyek bersifat permanen dan tidak dapat diubah

### 3. **Hapus Proyek**
- Proyek yang **belum digunakan** dapat dihapus permanen
- Proyek yang **sudah digunakan** dalam proposal/laporan tidak dapat dihapus
  - Status akan otomatis diubah menjadi `Cancelled`
  - Data tetap tersimpan untuk keperluan audit

### 4. **Status Proyek**
| Status | Deskripsi |
|--------|-----------|
| 🟡 **Planning** | Proyek masih dalam tahap perencanaan |
| 🔵 **Ongoing** | Proyek sedang berjalan |
| 🟢 **Completed** | Proyek sudah selesai |
| 🔴 **Cancelled** | Proyek dibatalkan |

---

## 🎯 Cara Akses

### **Finance Manager:**
1. Login sebagai Finance Manager
2. Di dashboard, klik card **"Kelola Proyek"** (warna oranye)
3. Atau akses langsung: `manage_projects.php`

---

## 🔄 Workflow Penggunaan

```
Finance Manager              Project Manager
     │                            │
     ├─► Buat Kode Proyek         │
     │   (manage_projects.php)    │
     │                            │
     │                            ├─► Pilih Kode Proyek
     │                            │   (create_proposal.php)
     │                            │
     │                            ├─► Buat Proposal
     │                            │   (menggunakan kode proyek)
     │                            │
     ├─◄ Review Proposal          │
     │                            │
     └─► Approve/Reject           │
```

---

## 📊 Tampilan Data

Tabel proyek menampilkan:
- Kode Proyek (format monospace, warna biru)
- Nama Proyek
- Status (dengan badge warna)
- Donor
- Anggaran (format Rupiah)
- Periode (format tanggal)
- Aksi (Edit/Hapus)

---

## 🔐 Keamanan

1. **Role Access:**
   - Hanya **Finance Manager** yang dapat mengakses
   - Role lain akan diarahkan ke `unauthorized.php`

2. **Validasi Kode Proyek:**
   - Kode proyek harus unik
   - Tidak boleh ada duplikat

3. **Proteksi Data:**
   - Proyek yang sudah digunakan tidak dapat dihapus
   - Hanya bisa diubah statusnya

---

## 💡 Tips Penggunaan

### **Format Kode Proyek:**
Disarankan menggunakan format:
- `PRJ-2024-001` - Proyek tahun 2024 nomor 1
- `PRJ-2024-EDU` - Proyek Education 2024
- `DONOR-YYYY-XXX` - Proyek dari donor tertentu

### **Best Practices:**
1. Buat kode proyek yang **deskriptif**
2. Gunakan format yang **konsisten**
3. Update status proyek secara **berkala**
4. Set periode mulai dan selesai untuk **tracking**
5. Catat nilai anggaran untuk **budgeting**

---

## 🐛 Troubleshooting

### **Error: "Kode proyek sudah ada!"**
- **Penyebab:** Kode proyek tidak unik
- **Solusi:** Gunakan kode yang berbeda

### **Info: "Proyek tidak dapat dihapus"**
- **Penyebab:** Proyek sudah digunakan dalam proposal/laporan
- **Solusi:** Status otomatis diubah menjadi Cancelled
- **Catatan:** Data tetap tersimpan untuk audit trail

### **Proyek tidak muncul di dropdown PM**
- **Penyebab:** Status proyek = `Cancelled`
- **Solusi:** Ubah status menjadi Planning/Ongoing/Completed

---

## 📁 File Terkait

| File | Fungsi |
|------|--------|
| `manage_projects.php` | Halaman utama kelola proyek (FM) |
| `dashboard_fm.php` | Dashboard FM dengan link ke kelola proyek |
| `create_proposal.php` | PM memilih kode proyek dari database |
| `create_financial_report.php` | PM memilih kode proyek untuk laporan |

---

## 🗄️ Database

### **Tabel: `proyek`**
```sql
CREATE TABLE `proyek` (
  `kode_proyek` varchar(50) NOT NULL PRIMARY KEY,
  `nama_proyek` varchar(255) NOT NULL,
  `status_proyek` enum('planning','ongoing','completed','cancelled') DEFAULT 'planning',
  `donor` varchar(255) DEFAULT NULL,
  `nilai_anggaran` decimal(15,2) DEFAULT NULL,
  `periode_mulai` date DEFAULT NULL,
  `periode_selesai` date DEFAULT NULL,
  `rekening_khusus` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **Foreign Key:**
- `proposal.kode_proyek` → `proyek.kode_proyek`
- `laporan_keuangan_header.kode_proyek` → `proyek.kode_proyek`

---

## 🎨 UI/UX Features

1. **Responsive Design:** Bekerja di mobile, tablet, desktop
2. **Color-Coded Status:** Status visual dengan badge warna
3. **Modal Forms:** Edit form dalam modal (popup)
4. **Confirmation Dialog:** Konfirmasi sebelum hapus
5. **Currency Formatting:** Format Rupiah otomatis
6. **Success Messages:** Notifikasi setelah aksi berhasil
7. **Fade-in Animation:** Transisi halus untuk form & notif

---

## ✅ Checklist Setup

- [x] File `manage_projects.php` sudah dibuat
- [x] Link di dashboard FM sudah ditambahkan
- [x] Role access (Finance Manager only)
- [x] CRUD functionality (Create, Read, Update, Delete)
- [x] Validasi kode proyek unik
- [x] Proteksi data yang sudah digunakan
- [x] Format currency (Rupiah)
- [x] Responsive design
- [x] Success/error messages
- [x] Modal edit form
- [x] Confirmation dialog

---

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Cek dokumentasi ini terlebih dahulu
2. Review file `manage_projects.php` untuk detail implementasi
3. Pastikan role = Finance Manager
4. Cek browser console untuk error JavaScript

---

**Dibuat:** 2024  
**Terakhir Update:** 2024  
**Developer:** PRCF INDONESIA Financial Dashboard  

