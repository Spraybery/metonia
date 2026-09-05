# Metonia Workshop Management System
**Nairobi Assembly Plant #1 (Heavy Vehicle Manufacturing & Assembly Operations)**

A specialized Enterprise Resource Planning (ERP) and workshop operations platform built for Metonia Enterprise Limited to manage multi-stage commercial vehicle manufacturing, store inventory, technician assignments, and financial cost auditing.

---

## 🚀 Key Features & Capabilities

- **8-Stage Sequential Build Board**: Real-time tracking of vehicle assembly progression from *Intake & Diagnosis* to *Quality & Road Test* and *Completed & Dispatched*.
- **Bottleneck Detection**: Automatic identification and alerting for vehicles stuck $\ge 10$ days in any single stage.
- **5 Specialized Role Profiles**: Tailored user experience and permission boundaries (Admin, Manager, General Supervisor, Shopkeeper, Accountant).
- **Atomic Parts Issuance**: Store inventory deductions with database-level row locking (`SELECT ... FOR UPDATE`) to prevent inventory overselling and race conditions.
- **Immediate Job Card Synchronization**: Store item issuances automatically register directly to the vehicle's Job Card parts schedule with cost auditing.
- **Supervisors Roster & Balancing**: Live calculation of active supervised vehicles across all assembly bays.
- **Tools & Equipment Asset Register**: Asset tracking across 5 workshop crib categories with calibration overdue/upcoming alerts.
- **Financial Gross Margin Engine (MTD)**: Dynamic aggregation of invoiced revenue vs direct labor and issued parts consumption.
- **Official Print Engine**: High-fidelity, watermarked Job Cards and Build Dossiers styled in the executive Green & White theme (`#064e3b`, `#10b981`).

---

## 👥 5 System Role Profiles

| Role | Default Username | Default Password | Primary Workflow |
|---|---|---|---|
| **Admin** | `admin` | `password` | Full system governance, deletions, and user management (RBAC). Protected by self-deletion guard. |
| **Manager** | `manager` | `password` | Restocks store materials from suppliers with supplier name, date, quantity, receiving officer, and delivery notes. |
| **General Supervisor** | `supervisor` | `password` | Advances vehicle build stages (1 to 8) and assigns/updates the lead engineer or technician for each stage. |
| **Shopkeeper** | `shopkeeper` | `password` | Issues materials to vehicles, records technician name, and auto-syncs to the vehicle's Job Card. |
| **Accountant** | `accountant` | `password` | Pure financial auditing of labor costs, parts costs, customer invoices, and gross margins (strictly view-only). |

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.5, Laravel 11 / 13
- **Database**: MySQL / MariaDB (`metonic_db`)
- **Frontend / Styling**: Bootstrap 5 + Limitless UI Framework (SkullU Clean Enterprise Minimalism, Emerald Green & Slate White theme)
- **Tables & Export**: DataTables with HTML5 PDF, Excel, CSV, Copy, and Print export buttons
- **Testing**: PHPUnit feature test suite with 100% pass rate

---

## 📦 Installation & Setup Guide

### 1. Clone the Repository
```bash
git clone https://github.com/Spraybery/metonia.git
cd metonia
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=metonic_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations & Seeders
Create the database `metonic_db` in MySQL, then run:
```bash
php artisan migrate --seed
```

### 5. Start Development Server
```bash
php artisan serve
```
Open **[http://127.0.0.1:8000](http://127.0.0.1:8000)** in your browser.

---

## 🧪 Running Automated Tests

Run the feature test suite:
```bash
php artisan test --compact
```

Run Laravel Pint linter:
```bash
vendor/bin/pint --format agent
```

---

## 📄 License
Proprietary software developed for Metonia Enterprise Limited. All rights reserved.
