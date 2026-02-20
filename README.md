# Production Schedule Management System

> 📋 **Studi Kasus:** Sistem Manajemen Schedule Produksi untuk Sewing Lines
> 🎯 **TEST PROGRAM 2026** - PT. Morich Indonesia

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![React](https://img.shields.io/badge/React-19-61DAFB?logo=react)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.7-3178C6?logo=typescript)](https://www.typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?logo=tailwind-css)](https://tailwindcss.com)

---

## 📖 Deskripsi Project

Aplikasi web untuk **admin produksi** dalam mengelola schedule produksi pada beberapa **sewing line** di area produksi. Sistem ini mengotomatisasi perhitungan target output harian, melakukan balancing ketika terjadi shortfall, dan secara otomatis menggeser schedule berikutnya ketika terjadi keterlambatan.

### 🎯 Problem Statement

Di industri garment, admin produksi sering menghadapi tantangan:
- ⏰ Merencanakan schedule produksi untuk multiple sewing lines
- 📊 Menghitung target output per hari secara manual
- ⚖️ Melakukan balancing ketika actual output < target output
- 📅 Mengatur ulang schedule berikutnya ketika terjadi delay
- 📈 Monitoring progress produksi real-time

### ✅ Solution

Sistem ini menyediakan:
- ✅ **Automated Daily Target Calculation** - Pembagian otomatis qty target per hari
- ✅ **Smart Balancing Logic** - Balance shortfall ke hari berikutnya atau extend schedule
- ✅ **Cascading Schedule Shift** - Auto-adjust schedule berikutnya ketika terjadi delay
- ✅ **Real-time Monitoring** - Kanban board + DataTables dengan WebSocket updates
- ✅ **Visual Progress Tracking** - Cards, charts, dan color-coded indicators

---

## 🏗️ Fitur Utama (Sesuai Requirements TEST PROGRAM 2026)

### 1️⃣ Master Data Management

#### 📍 Master Line (Sewing Line)
```
- Line Code (L001, L002, etc.)
- Line Name (Line A, Line B, etc.)
- Capacity per Day (opsional)
- Active Status
- Soft Delete Support
```

#### 📦 Master Order
```
- Order Number (PO-001, PO-002, etc.)
- Product Name & Code
- Total Quantity
- Customer
- Order Date & Due Date
- Status (pending → scheduled → in_progress → completed)
- Remaining Qty (computed)
```

### 2️⃣ Schedule Produksi

#### 📅 Data Schedule
- **Order** (relasi ke master_orders)
- **Line Sewing** (relasi ke master_lines)
- **Start Date** (tanggal mulai produksi)
- **Finish Date** (tanggal target selesai)
- **Current Finish Date** (tanggal aktual selesai setelah balancing)
- **Qty Total Target** (target total produksi)
- **Qty Completed** (total actual yang sudah diproduksi)
- **Days Extended** (jumlah hari keterlambatan)

#### 🎯 Target Output Per Hari
```php
// Algoritma Pembagian Target
baseTarget = floor(qty_total_target / total_days)
remainder = qty_total_target % total_days

// Contoh: 1000 pcs dalam 7 hari
// Hari 1-6: 142 pcs/hari
// Hari 7: 142 + 4 = 146 pcs (remainder ditempatkan di hari terakhir)
```

### 3️⃣ Proses Balancing (Core Feature)

#### ⚖️ Balancing Logic Flow

```
1. Admin input actual output untuk hari ini
2. System compare: actual vs target

   IF actual >= target:
      ✅ Mark day as completed
      ✅ Continue to next day

   ELSE IF actual < target:
      ⚠️ Calculate balance = target - actual

      IF next day exists:
         ➕ Add balance to next day's target
         Example: Target Day 2 = 142 + 50 (balance) = 192 pcs

      ELSE (no next day):
         📅 Extend schedule
         📅 Create new day(s) with balance qty
         📅 Shift subsequent schedules on same line
         📅 Update days_extended counter
```

#### 🔄 Cascading Schedule Shift

Ketika schedule di-extend, **semua schedule berikutnya** pada line yang sama otomatis tergeser:

```
Before Extension:
├─ Schedule A: 2026-02-01 → 2026-02-10
├─ Schedule B: 2026-02-11 → 2026-02-20 (start after A)
└─ Schedule C: 2026-02-21 → 2026-02-28 (start after B)

After Schedule A Extended +3 days:
├─ Schedule A: 2026-02-01 → 2026-02-13 (extended)
├─ Schedule B: 2026-02-14 → 2026-02-23 (shifted +3 days)
└─ Schedule C: 2026-02-24 → 2026-03-03 (shifted +3 days)
```

### 4️⃣ User Interface

#### 📊 Kanban Board View
- Drag & drop schedule cards
- 4 kolom status: Pending → In Progress → Delayed → Completed
- Real-time updates via WebSocket
- Filter by line

#### 📋 DataTables View
- Server-side pagination
- Search & filter
- Sortable columns
- Export ready

#### 📈 Schedule Detail Page
- Summary cards (Order, Line, Period, Progress)
- Daily output table dengan inline editing
- Color-coded achievement percentage
- Balance highlighting (red untuk shortfall)
- Progress bar visualization

---

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **MySQL/MariaDB** - Relational Database
- **Spatie Permission** - RBAC
- **Spatie Activity Log** - Audit Trail
- **Laravel Reverb** - WebSocket Server (Real-time)

### Frontend
- **React 19** - UI Library
- **TypeScript 5.7** - Type Safety
- **Inertia.js 2.0** - Modern Monolith
- **Tailwind CSS 4.0** - Utility-first CSS
- **shadcn/ui** - Component Library
- **@dnd-kit** - Drag & Drop
- **DataTables.net** - Advanced Tables

### Architecture
- **Service Layer Pattern** - Business Logic
- **Repository Pattern** - Data Access Layer
- **Form Request Validation** - Input Validation
- **Database Transactions** - Data Consistency

---

## 📁 Struktur Database

### ERD (Entity Relationship Diagram)

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│  master_lines   │         │    schedules     │         │ master_orders   │
├─────────────────┤         ├──────────────────┤         ├─────────────────┤
│ id              │────┐    │ id               │    ┌────│ id              │
│ name            │    │    │ order_id (FK)    │────┘    │ order_number    │
│ code (unique)   │    └────│ line_id (FK)     │         │ product_name    │
│ capacity/day    │         │ start_date       │         │ qty_total       │
│ is_active       │         │ finish_date      │         │ customer        │
│ timestamps      │         │ current_finish   │         │ status          │
│ soft_deletes    │         │ qty_total_target │         │ timestamps      │
└─────────────────┘         │ qty_completed    │         │ soft_deletes    │
                            │ days_extended    │         └─────────────────┘
                            │ status           │
                            │ timestamps       │                │
                            │ soft_deletes     │                │
                            └──────────────────┘                │
                                     │                          │
                                     │ 1:N                      │
                                     ▼                          │
                       ┌──────────────────────────┐             │
                       │ schedule_daily_outputs   │             │
                       ├──────────────────────────┤             │
                       │ id                       │             │
                       │ schedule_id (FK)         │─────────────┘
                       │ date                     │
                       │ target_output            │
                       │ actual_output            │
                       │ balance                  │
                       │ is_completed             │
                       │ timestamps               │
                       └──────────────────────────┘
```

### Tabel Details

#### `master_lines`
```sql
- id: bigint (PK)
- name: string (e.g., "Line A")
- code: string UNIQUE (e.g., "L001")
- description: text (nullable)
- capacity_per_day: integer (default 0)
- is_active: boolean (default true)
- created_at, updated_at
- deleted_at (soft delete)
```

#### `master_orders`
```sql
- id: bigint (PK)
- order_number: string UNIQUE (e.g., "PO-001")
- product_name: string
- product_code: string (nullable)
- qty_total: integer
- customer: string (nullable)
- order_date: date
- due_date: date
- status: enum (pending, scheduled, in_progress, completed, cancelled)
- notes: text (nullable)
- created_at, updated_at
- deleted_at (soft delete)
```

#### `schedules`
```sql
- id: bigint (PK)
- order_id: bigint (FK → master_orders)
- line_id: bigint (FK → master_lines)
- start_date: date
- finish_date: date (original planned)
- current_finish_date: date (actual after balancing)
- qty_total_target: integer
- qty_completed: integer (default 0)
- days_extended: integer (default 0)
- status: enum (pending, in_progress, completed, delayed)
- notes: text (nullable)
- created_at, updated_at
- deleted_at (soft delete)
- INDEX(line_id, start_date, current_finish_date)
- INDEX(status)
```

#### `schedule_daily_outputs`
```sql
- id: bigint (PK)
- schedule_id: bigint (FK → schedules)
- date: date
- target_output: integer
- actual_output: integer (default 0)
- balance: integer (default 0) -- target - actual
- is_completed: boolean (default false)
- notes: text (nullable)
- created_at, updated_at
- UNIQUE(schedule_id, date)
- INDEX(date)
```

---

## 🚀 Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+ & npm
- MySQL 8.0+ / MariaDB 10.6+
- Redis (optional, untuk queue/cache)

### 1. Clone Repository
```bash
git clone <repository-url>
cd MIF
```

### 2. Install Dependencies
```bash
# Backend
composer install

# Frontend
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=production_schedule
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Migration & Seeding
```bash
# Run migrations
php artisan migrate

# Seed initial data (users, permissions, roles)
php artisan db:seed

# Seed production demo data (optional)
php artisan db:seed --class=ProductionSeeder
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Run Development Server
```bash
# Concurrent mode (recommended) - runs 4 services
composer dev

# Or run individually:
php artisan serve              # Laravel (port 8000)
php artisan queue:listen       # Queue worker
php artisan reverb:start       # WebSocket (port 8080)
npm run dev                    # Vite (frontend)
```

### 7. Access Application
```
URL: http://localhost:8000
Admin: admin@example.com / password
User: user@example.com / password
```

---

## 📚 Usage Guide

### 1. Setup Master Data

#### Create Lines
```
Dashboard → Production → Lines → Create New
- Code: L001
- Name: Line A
- Capacity: 500 pcs/day
- Active: Yes
```

#### Create Orders
```
Dashboard → Production → Orders → Create New
- Order Number: PO-001
- Product: Kaos Polo Navy
- Qty Total: 1000 pcs
- Customer: PT. ABC
- Order Date: 2026-02-01
- Due Date: 2026-02-15
```

### 2. Create Schedule

```
Dashboard → Production → Schedules → Create New
- Order: PO-001 - Kaos Polo Navy
- Line: L001 - Line A
- Start Date: 2026-02-03
- Finish Date: 2026-02-09 (7 hari)
- Qty Total Target: 1000 pcs
```

**System akan otomatis:**
- Generate 7 daily outputs
- Calculate target per hari: 142 pcs (hari 1-6), 146 pcs (hari 7)
- Set status: Pending

### 3. Input Actual Output

```
Dashboard → Production → Schedules → [View Schedule] → Daily Output Table

Scenario 1: Target Met
- Day 1: Target 142, Input Actual 142 → ✅ Completed
- System: Mark day as completed, continue

Scenario 2: Target Not Met (Balancing)
- Day 1: Target 142, Input Actual 100 → ⚠️ Balance 42
- System: Add 42 to Day 2 target (142 + 42 = 184)

Scenario 3: Last Day Shortfall (Extension)
- Day 7: Target 146, Input Actual 100 → ⚠️ Balance 46
- System:
  * Extend schedule +1 day
  * Create Day 8 with target 46
  * Shift subsequent schedules on Line A
  * Update status: Delayed
```

### 4. Monitor Progress

#### Kanban Board View
```
Dashboard → Production → Schedules → [Toggle] Kanban View
- Drag & drop cards between columns
- Filter by line
- Real-time updates
```

#### DataTables View
```
Dashboard → Production → Schedules → [Toggle] List View
- Search, sort, filter
- Pagination
- Export data
```

---

## 🔧 Code Architecture

### Service Layer Pattern

```php
// app/Services/ScheduleService.php
public function inputActualOutput(int $dailyOutputId, int $actualOutput): bool
{
    DB::beginTransaction();
    try {
        $dailyOutput = $this->dailyOutputRepository->find($dailyOutputId);
        $balance = $dailyOutput->target_output - $actualOutput;

        // Update actual output
        $this->dailyOutputRepository->update($dailyOutputId, [
            'actual_output' => $actualOutput,
            'balance' => $balance,
            'is_completed' => $actualOutput >= $dailyOutput->target_output,
        ]);

        // Trigger balancing if needed
        if ($balance > 0) {
            $this->performBalancing($dailyOutput, $balance);
        }

        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### Repository Pattern

```php
// app/Repositories/Eloquent/ScheduleRepository.php
class ScheduleRepository extends BaseRepository implements ScheduleRepositoryInterface
{
    public function getAffectedSchedules(int $scheduleId): Collection
    {
        $schedule = $this->find($scheduleId);

        return self::where('line_id', $schedule->line_id)
            ->where('id', '!=', $schedule->id)
            ->where('start_date', '>', $schedule->finish_date)
            ->orderBy('start_date')
            ->get();
    }
}
```

### Form Request Validation

```php
// app/Http/Requests/Production/StoreScheduleRequest.php
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Check line availability
        $line = Line::find($this->input('line_id'));
        if (!$line->isAvailableInRange($this->start_date, $this->finish_date)) {
            $validator->errors()->add('line_id',
                'Line already scheduled in this date range.');
        }

        // Check qty tidak melebihi order remaining
        $order = Order::find($this->input('order_id'));
        if ($this->input('qty_total_target') > $order->remaining_qty) {
            $validator->errors()->add('qty_total_target',
                'Exceeds order remaining quantity.');
        }
    });
}
```

### React Component (TypeScript)

```tsx
// resources/js/pages/Production/Schedule/Show.tsx
const handleSubmitActualOutput = (e: FormEvent) => {
    e.preventDefault();
    post(route('production.schedules.input-actual'), {
        onSuccess: () => {
            toast.success('Actual output recorded. Balancing applied if needed.');
            reset();
        },
        onError: () => {
            toast.error('Failed to record actual output');
        },
    });
};
```

---

## 🧪 Testing

### Manual Testing Checklist

```
✅ Create Line
✅ Create Order
✅ Create Schedule (verify daily outputs generated)
✅ Input actual = target (verify completed)
✅ Input actual < target dengan next day (verify balance added)
✅ Input actual < target di last day (verify schedule extended)
✅ Verify subsequent schedule shifted
✅ Drag & drop di Kanban board
✅ Search & filter di DataTables
✅ Edit schedule (verify daily outputs regenerated)
✅ Delete schedule
```

### Run Tests
```bash
# Unit & Feature tests
php artisan test

# With coverage
php artisan test --coverage

# Frontend type checking
npm run types

# Linting
npm run lint
```

---

## 📝 API Endpoints

### Production Routes

```
GET    /dashboard/production/lines              - List all lines
POST   /dashboard/production/lines/json         - DataTables JSON
GET    /dashboard/production/lines/create       - Show create form
POST   /dashboard/production/lines              - Store new line
GET    /dashboard/production/lines/{id}/edit    - Show edit form
PUT    /dashboard/production/lines/{id}         - Update line
DELETE /dashboard/production/lines/{id}         - Delete line

GET    /dashboard/production/orders             - List all orders
POST   /dashboard/production/orders/json        - DataTables JSON
... (similar CRUD for orders)

GET    /dashboard/production/schedules          - List/Kanban view
POST   /dashboard/production/schedules/json     - DataTables JSON
GET    /dashboard/production/schedules/create   - Show create form
POST   /dashboard/production/schedules          - Store new schedule
GET    /dashboard/production/schedules/{id}     - Show schedule detail
GET    /dashboard/production/schedules/{id}/edit - Show edit form
PUT    /dashboard/production/schedules/{id}     - Update schedule
DELETE /dashboard/production/schedules/{id}     - Delete schedule

POST   /dashboard/production/schedules/input-actual        - Input actual output
POST   /dashboard/production/schedules/check-availability  - Check line availability
GET    /dashboard/production/schedules/kanban/data         - Kanban data (AJAX)
PATCH  /dashboard/production/schedules/{id}/status         - Update status (AJAX)
```

---

## 🎨 Screenshots

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Kanban Board
![Kanban Board](docs/screenshots/kanban.png)

### Schedule Detail
![Schedule Detail](docs/screenshots/schedule-detail.png)

### Daily Output Input
![Daily Output](docs/screenshots/daily-output.png)

---

## 📂 Project Structure

```
MIF/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Production/
│   │   │       ├── LineController.php
│   │   │       ├── OrderController.php
│   │   │       └── ScheduleController.php
│   │   └── Requests/
│   │       └── Production/
│   │           ├── StoreScheduleRequest.php
│   │           ├── UpdateScheduleRequest.php
│   │           └── InputActualOutputRequest.php
│   ├── Models/
│   │   ├── Line.php
│   │   ├── Order.php
│   │   ├── Schedule.php
│   │   └── ScheduleDailyOutput.php
│   ├── Services/
│   │   ├── LineService.php
│   │   ├── OrderService.php
│   │   └── ScheduleService.php
│   └── Repositories/
│       ├── Contracts/
│       │   ├── LineRepositoryInterface.php
│       │   ├── OrderRepositoryInterface.php
│       │   └── ScheduleRepositoryInterface.php
│       └── Eloquent/
│           ├── LineRepository.php
│           ├── OrderRepository.php
│           └── ScheduleRepository.php
├── database/
│   ├── migrations/
│   │   ├── 2026_02_20_100000_create_master_lines_table.php
│   │   ├── 2026_02_20_100001_create_master_orders_table.php
│   │   ├── 2026_02_20_100002_create_schedules_table.php
│   │   └── 2026_02_20_100003_create_schedule_daily_outputs_table.php
│   └── seeders/
│       └── ProductionSeeder.php
├── resources/
│   └── js/
│       ├── pages/
│       │   └── Production/
│       │       ├── Line/
│       │       │   ├── Index.tsx
│       │       │   └── Form.tsx
│       │       ├── Order/
│       │       │   ├── Index.tsx
│       │       │   └── Form.tsx
│       │       └── Schedule/
│       │           ├── Index.tsx
│       │           ├── Form.tsx
│       │           └── Show.tsx
│       └── types/
│           └── production.d.ts
└── routes/
    └── web.php (production routes)
```

---

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Credits

**Developed by:** [Your Name]
**Purpose:** TEST PROGRAM 2026 - PT. Morich Indonesia
**Tech Stack:** Laravel 12 + React 19 + TypeScript + Tailwind CSS
**Architecture:** Service + Repository Pattern

---

## 📞 Support

Untuk pertanyaan atau bantuan:
- Email: [your-email@example.com]
- Documentation: See [SUBMISSION.md](SUBMISSION.md) for submission guide

---

**Last Updated:** February 2026
