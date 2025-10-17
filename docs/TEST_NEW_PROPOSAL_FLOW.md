# 🧪 Testing Guide: 2-Stage Approval Flow

## ⚠️ IMPORTANT: Database Issue Found!

### **Problem Identified:**
From your database dump (`proposal.sql`), **ALL proposals** have status `'approved'` or `'submitted'` with **NULL** values for:
- `approved_by_fm` = NULL
- `approved_by_dir` = NULL
- `fm_approval_date` = NULL
- `dir_approval_date` = NULL

**This means these proposals were approved using OLD LOGIC (before 2-stage approval implementation).**

```sql
-- Current database state (OLD DATA):
(1, ..., 'approved', NULL, NULL, NULL, NULL, ...)  ❌ Old approval
(2, ..., 'submitted', NULL, NULL, NULL, NULL, ...) ⏳ Waiting FM
(3, ..., 'approved', NULL, NULL, NULL, NULL, ...)  ❌ Old approval
(4, ..., 'approved', NULL, NULL, NULL, NULL, ...)  ❌ Old approval
```

**Why buttons don't appear:**
- **If DIR opens proposal with status `'approved'`** → No buttons (already final approved)
- **If DIR opens proposal with status `'submitted'`** → No buttons (must be FM approved first - Stage 1)
- **For buttons to appear**, proposal must have status **`'approved_fm'`**

---

## ✅ Solution: Create NEW Proposal to Test 2-Stage Flow

### **Step 1: Login as Project Manager**
1. Create a **NEW** proposal
2. Fill in all required fields (Judul, PJ, TOR, Budget)
3. Submit proposal
4. **Expected:** Status = `'submitted'`

### **Step 2: Login as Finance Manager**
1. Go to dashboard
2. Click on the NEW proposal (status: "Menunggu Review FM")
3. Review the proposal
4. Click **"Setujui (Stage 1/2)"** button
5. **Expected Database State:**
   ```sql
   status = 'approved_fm'
   approved_by_fm = <FM user ID>
   fm_approval_date = <current timestamp>
   approved_by_dir = NULL
   dir_approval_date = NULL
   ```
6. **Expected UI:**
   - Success message: "Proposal berhasil disetujui (Stage 1/2). Menunggu approval Direktur."
   - Redirect to FM dashboard

### **Step 3: Login as Direktur**
1. Go to dashboard
2. **Expected:** Proposal appears with badge **"1/2 Approved (FM)"** (blue badge)
3. Click on the proposal
4. **Expected UI:**
   - Purple section header: "Review Proposal (Stage 2/2)"
   - Info box showing: "Proposal telah disetujui oleh Finance Manager (nama) pada (tanggal)"
   - Textarea for catatan (optional)
   - **Button "Minta Revisi"** (yellow) ✅
   - **Button "Approve Final (2/2)"** (purple) ✅
   - **TOR download button** ✅ (green button)
   - **Budget download button** ✅ (blue button)
5. Click **"Approve Final (2/2)"** button
6. **Expected Database State:**
   ```sql
   status = 'approved'
   approved_by_fm = <FM user ID>
   fm_approval_date = <timestamp>
   approved_by_dir = <DIR user ID>
   dir_approval_date = <current timestamp>
   ```
7. **Expected UI:**
   - Success message: "Proposal berhasil disetujui FINAL (2/2)!"
   - Redirect to DIR dashboard

---

## 🔍 Debugging: Check What You're Seeing

### **When DIR opens a proposal, you should see 2 DEBUG boxes:**

#### **Box 1: Yellow Debug Info (top of page)**
```
🔍 DEBUG INFO:
• User Role: Direktur
• Proposal Status: [CHECK THIS VALUE!]
• Proposal ID: X
• TOR Path: uploads/tor/xxxxx.pdf
• TOR File Exists: YES/NO
• FM Name: [Should show FM name if approved_fm]
• FM Approval Date: [Should show date if approved_fm]
```

#### **Box 2: Gray Debug Conditions (before form)**
```
Status: [STATUS] | Role: Direktur | Is FM+submitted: NO | Is DIR+approved_fm: YES/NO
```

**Key Check:**
- If `Is DIR+approved_fm: NO` → **Buttons WILL NOT appear**
- If `Is DIR+approved_fm: YES` → **Buttons WILL appear**

---

## 📊 Expected Behavior per Status:

| Proposal Status | DIR sees buttons? | Reason |
|----------------|------------------|--------|
| `'draft'` | ❌ NO | PM still editing |
| `'submitted'` | ❌ NO | Waiting FM approval (Stage 1) |
| **`'approved_fm'`** | **✅ YES** | **Waiting DIR approval (Stage 2)** |
| `'approved'` | ❌ NO | Already final approved |
| `'rejected'` | ❌ NO | Proposal rejected |

---

## 🛠️ If Buttons Still Don't Appear After Creating New Proposal:

### **Check 1: Database Migration**
Run this query in phpMyAdmin:
```sql
SHOW COLUMNS FROM proposal LIKE 'approved_by_fm';
```

**Expected:** Should return 1 row showing column exists.

**If NOT exists:** Import `alter_proposal_2stage_approval.sql`

### **Check 2: Verify FM Approval Actually Set Status**
Run this query after FM approves:
```sql
SELECT id_proposal, status, approved_by_fm, fm_approval_date 
FROM proposal 
WHERE id_proposal = <NEW_PROPOSAL_ID>;
```

**Expected:**
```
status = 'approved_fm'
approved_by_fm = NOT NULL (should be FM user ID)
fm_approval_date = timestamp
```

**If status is still 'submitted' or 'approved':** There's a bug in the FM approval logic.

### **Check 3: Role Name Exact Match**
Run this query to check your Direktur account:
```sql
SELECT id_user, nama, email, role 
FROM user 
WHERE role = 'Direktur';
```

**Expected:** Should return your DIR account.

**If NO results:** Role name might be different (e.g., 'Director', 'direktur', 'Direktur ', etc.)

---

## 🚨 Quick Fix for Testing (if needed):

If you want to MANUALLY create a proposal with `'approved_fm'` status for testing:

```sql
-- Update an existing proposal to 'approved_fm' status
UPDATE proposal 
SET 
    status = 'approved_fm',
    approved_by_fm = 1,  -- Replace with actual FM user ID
    fm_approval_date = NOW()
WHERE id_proposal = 2;  -- Use proposal ID 2 which is 'submitted'
```

Then:
1. Login as Direktur
2. Go to dashboard → should see proposal with "1/2 Approved (FM)" badge
3. Click proposal → **buttons should now appear**

---

## 📸 What to Check and Send Me:

1. **Screenshot of the 2 DEBUG boxes** (yellow and gray)
2. **Run this SQL query** and send result:
   ```sql
   SELECT id_proposal, status, approved_by_fm, approved_by_dir, fm_approval_date, dir_approval_date
   FROM proposal
   ORDER BY updated_at DESC;
   ```
3. **Run this SQL query** to check DIR role:
   ```sql
   SELECT id_user, nama, email, role 
   FROM user 
   WHERE email = 'your_dir_email@example.com';  -- Replace with your DIR email
   ```

---

## 🎯 Summary:

**Root Cause:** Your current proposals use OLD approval logic (status = 'approved' without proper 2-stage fields).

**Solution:** Create a **NEW proposal** and test the complete flow:
1. PM creates → status = 'submitted'
2. FM approves → status = 'approved_fm' (Stage 1) ← **This is the key step!**
3. DIR approves → status = 'approved' (Stage 2/Final)

**For DIR buttons to appear, proposal MUST have status = 'approved_fm'**

---

**Current Status:** Waiting for you to:
1. Create NEW proposal
2. FM approve it (Stage 1)
3. Test DIR buttons with the NEW proposal

