# Production Schedule Management System - Progress Tracking

## Project Overview
**Test Program 2026** - Production Schedule Management System for Sewing Lines

**Deadline:** 23 Februari 2026
**Email:** hrd.recruitment@ptmorich.com, erwin@ptmorich.com
**Subject:** schedule2026_namapelamar

---

## Requirements Summary

### Core Features
1. ✅ Schedule management untuk sewing lines di area produksi
2. ⏳ Balancing system (actual vs target output per hari)
3. ⏳ Auto-adjust finish date ketika actual < target
4. ⏳ Cascade effect ke schedule berikutnya

### Data Structure
- **Order Data:** order number, product, qty total, customer, dates
- **Line Data:** sewing line name, code, capacity
- **Schedule Data:** order_id, line_id, start_date, finish_date, qty_total_target
- **Daily Output:** target_output_per_day, actual_output, balance

### Balancing Logic
- Target output per hari = qty_total_target / range_hari
- Sisa pembagian ditempatkan di akhir range
- Jika actual < target → balance dilanjutkan ke hari berikutnya
- Range tanggal bertambah → schedule berikutnya mundur

### Tech Stack
- ✅ Laravel 12
- ✅ MySQL database
- ✅ Model + Service pattern
- ⏳ JavaScript untuk action/response (React + TypeScript + Inertia)

---

## Progress Status

### ✅ Phase 1: Database Design (COMPLETED)
### ✅ Phase 2: Models & Relationships (COMPLETED)
### ✅ Phase 3: Services & Business Logic (COMPLETED)
### ✅ Phase 4: Controllers & Routes (COMPLETED)
### ✅ Phase 5: Seeders & Permissions (COMPLETED)

---

## Backend Implementation Summary

### ✅ Phase 1: Database Design (COMPLETED)

#### Migrations Created:
1. ✅ `2026_02_20_100000_create_master_lines_table.php`
   - Fields: id, name, code, description, capacity_per_day, is_active, timestamps, soft_deletes

2. ✅ `2026_02_20_100001_create_master_orders_table.php`
   - Fields: id, order_number, product_name, product_code, qty_total, customer, order_date, due_date, status, notes, timestamps, soft_deletes

3. ✅ `2026_02_20_100002_create_schedules_table.php`
   - Fields: id, order_id, line_id, start_date, finish_date, current_finish_date, qty_total_target, qty_completed, days_extended, status, notes, timestamps, soft_deletes

4. ✅ `2026_02_20_100003_create_schedule_daily_outputs_table.php`
   - Fields: id, schedule_id, date, target_output, actual_output, balance, is_completed, notes, timestamps
   - Unique constraint: schedule_id + date

---

### ✅ Phase 2: Models & Relationships (COMPLETED)

**Models Created:**
- ✅ Line.php - HasFactory, LogsActivity, SoftDeletes
- ✅ Order.php - HasFactory, LogsActivity, SoftDeletes
- ✅ Schedule.php - HasFactory, LogsActivity, SoftDeletes
- ✅ ScheduleDailyOutput.php - HasFactory, LogsActivity

**Relationships:**
- ✅ Line hasMany Schedules
- ✅ Order hasMany Schedules
- ✅ Schedule belongsTo Order, Line
- ✅ Schedule hasMany DailyOutputs
- ✅ DailyOutput belongsTo Schedule

---

### ✅ Phase 3: Services & Business Logic (COMPLETED)

**Repositories Created:**
- ✅ LineRepository with availability checking
- ✅ OrderRepository with schedulable queries
- ✅ ScheduleRepository with date range queries
- ✅ ScheduleDailyOutputRepository with balancing queries

**Services Created:**
- ✅ LineService - Complete CRUD
- ✅ OrderService - Complete CRUD
- ✅ ScheduleService - Complete with balancing logic:
  - ✅ createSchedule() - Auto-generate daily targets
  - ✅ generateDailyTargets() - Distribute dengan remainder
  - ✅ inputActualOutput() - Input + trigger balancing
  - ✅ performBalancing() - Add balance to next day
  - ✅ extendSchedule() - Extend dengan tambah hari
  - ✅ shiftSubsequentSchedules() - Cascade shift

---

### ✅ Phase 4: Controllers & Routes (COMPLETED)

**Controllers Created:**
- ✅ LineController - Complete CRUD + DataTables
- ✅ OrderController - Complete CRUD + DataTables
- ✅ ScheduleController - Complete CRUD + special endpoints:
  - ✅ inputActualOutput() - AJAX endpoint
  - ✅ timeline() - Timeline view
  - ✅ checkAvailability() - Line availability check

**Form Requests:**
- ✅ StoreLineRequest, UpdateLineRequest
- ✅ StoreOrderRequest, UpdateOrderRequest
- ✅ StoreScheduleRequest, UpdateScheduleRequest
- ✅ InputActualOutputRequest

**Routes:**
- ✅ production.lines.* (resource + json)
- ✅ production.orders.* (resource + json)
- ✅ production.schedules.* (resource + json + special actions)

---

### ✅ Phase 5: Seeders & Permissions (COMPLETED)

**Seeders:**
- ✅ LineSeeder - 5 sample lines (4 active, 1 inactive)
- ✅ OrderSeeder - 5 sample orders
- ✅ PermissionSeeder - 7 production permissions added
- ✅ RolePermissionSeeder - Admin gets all, User gets view+input

**Permissions Added:**
- ✅ view-schedules
- ✅ create-schedules
- ✅ edit-schedules
- ✅ delete-schedules
- ✅ input-actual-output
- ✅ manage-lines
- ✅ manage-orders

---

### ⏳ Phase 6: Frontend (React + TypeScript) (PENDING)

**Pages to Create:**
- [ ] Lines Management (Index, Create, Edit)
- [ ] Orders Management (Index, Create, Edit)
- [ ] Schedules Management:
  - [ ] Index with timeline view
  - [ ] Create schedule form
  - [ ] Daily output input form
  - [ ] Balance status visualization

**Components:**
- [ ] Schedule Timeline (Gantt-like)
- [ ] Daily Output Input Modal
- [ ] Balance Status Card
- [ ] Line Availability Checker

---

### ⏳ Phase 6: Permissions & Seeder (PENDING)

**Permissions:**
- [ ] view-schedules
- [ ] create-schedules
- [ ] edit-schedules
- [ ] delete-schedules
- [ ] input-actual-output
- [ ] manage-lines
- [ ] manage-orders

**Seeders:**
- [ ] Line seeder (5-10 sample lines)
- [ ] Order seeder (10-20 sample orders)
- [ ] Schedule seeder (sample schedules with some balancing scenarios)
- [ ] Permission seeder

---

### ⏳ Phase 7: Testing (PENDING)

**Test Cases:**
- [ ] Schedule creation dengan target distribution
- [ ] Actual output input
- [ ] Balancing calculation
- [ ] Schedule extension
- [ ] Cascade effect to next schedules
- [ ] Edge cases (weekend handling, holidays, etc)

---

## Key Algorithms

### 1. Target Output Distribution
```php
// Example: 1000 qty / 7 days = 142 per day + 6 remainder
// Days 1-6: 142 each
// Day 7: 142 + 6 = 148
```

### 2. Balancing Algorithm
```php
// If actual < target:
// - balance = target - actual
// - Add balance to next day's target
// - If no next day, extend schedule by 1 day
// - Shift all subsequent schedules on same line
```

### 3. Schedule Shifting
```php
// When schedule extends:
// - Find all schedules on same line after current finish date
// - Shift their start_date and finish_date by days_extended
// - Recalculate their daily targets
```

---

## Current Focus
**Creating Models with relationships and business logic**

## Notes
- Using existing Laravel starter kit with React, TypeScript, Inertia.js
- Service + Repository pattern already in place
- Permission system already configured (Spatie)
- UI components (shadcn/ui) available

---

**Last Updated:** 2026-02-20 10:00 WIB
