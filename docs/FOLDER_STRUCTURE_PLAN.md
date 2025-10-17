# 📁 Folder Structure Organization Plan

## Current State:
- 100+ files di root directory (sangat berantakan!)
- Mix antara PHP pages, configs, docs, SQL files, batch scripts
- Sulit untuk maintain dan navigate

## Proposed Structure:

```
prcf_keuangan_dashboard/
├── index.php                   (Keep di root - entry point)
├── .htaccess                   (Keep di root - routing)
├── .gitignore                 (Keep di root - git config)
│
├── auth/                      (Authentication pages)
│   ├── login.php
│   ├── register.php
│   ├── verify_otp.php
│   ├── logout.php
│   └── unauthorized.php
│
├── pages/                     (Main application pages)
│   ├── dashboards/
│   │   ├── dashboard_pm.php
│   │   ├── dashboard_fm.php
│   │   ├── dashboard_sa.php
│   │   └── dashboard_dir.php
│   │
│   ├── proposals/
│   │   ├── create_proposal.php
│   │   ├── review_proposal.php
│   │   ├── review_proposal_fm.php
│   │   ├── review_proposal_dir.php
│   │   └── approve_proposal.php
│   │
│   ├── reports/
│   │   ├── create_financial_report.php
│   │   ├── approve_report.php
│   │   ├── approve_report_dir.php
│   │   ├── validate_report.php
│   │   └── view_report.php
│   │
│   ├── books/
│   │   ├── buku_bank.php
│   │   └── buku_piutang.php
│   │
│   ├── projects/
│   │   └── manage_projects.php
│   │
│   └── profile/
│       └── profile.php
│
├── api/                       (API endpoints)
│   ├── api_notifications.php
│   └── get_proposals.php
│
├── includes/                  (Config & shared files)
│   ├── config.php
│   ├── config.example.php
│   ├── maintenance_config.php
│   ├── maintenance_config.example.php
│   └── (other config variants)
│
├── public/                    (Public pages)
│   ├── maintenance.php
│   └── under_construction.php
│
├── assets/                    (Static assets)
│   ├── js/
│   │   └── realtime_notifications.js
│   ├── css/
│   ├── images/
│   └── animations/
│       ├── maintenance.json
│       └── under_construction.json
│
├── uploads/                   (User uploaded files)
│   ├── tor/
│   └── budgets/
│
├── docs/                      (Documentation)
│   ├── guides/
│   │   ├── SETUP_GUIDE.md
│   │   ├── MAINTENANCE_MODE_GUIDE.md
│   │   ├── etc...
│   │
│   ├── implementation/
│   │   ├── 2_STAGE_APPROVAL_IMPLEMENTATION.md
│   │   ├── PROJECT_MANAGEMENT_IMPLEMENTATION.md
│   │   ├── etc...
│   │
│   └── summaries/
│       ├── COMPLETE_SUMMARY_ALL_REQUESTS.md
│       ├── etc...
│
├── sql/                       (Database migrations)
│   ├── migrations/
│   │   ├── add_whatsapp_column.sql
│   │   ├── alter_proposal_2stage_approval.sql
│   │   ├── etc...
│   │
│   └── dumps/
│       └── prcf_keuangan_clean.sql
│
├── scripts/                   (Utility scripts)
│   ├── batch/
│   │   ├── restart_apache.bat
│   │   ├── setup_brevo.bat
│   │   ├── etc...
│   │
│   └── shell/
│
└── tests/                     (Test files)
    ├── test_email.php
    ├── test_whatsapp_otp.php
    ├── test_notifications_api.php
    └── etc...
```

## Benefits:
1. ✅ Clean root directory
2. ✅ Logical grouping by function
3. ✅ Easy to navigate and maintain
4. ✅ Clear separation of concerns
5. ✅ Professional structure
6. ✅ Scalable for future growth

## Migration Steps:
1. Create folder structure
2. Move files to appropriate folders
3. Update all require/include paths
4. Test all pages
5. Update documentation

