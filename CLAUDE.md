# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Laravel 12 Spatie Media & Roles StarterKit** - Production-ready starter kit featuring:
- Laravel 12 + React 19 + TypeScript + Inertia.js
- Spatie Media Library (file management)
- Spatie Permission (RBAC)
- Real-time Activity Logging (WebSocket via Laravel Reverb)
- Modern UI with shadcn/ui + Tailwind CSS 4.0

Full documentation: `.claude/project.md`

---

## Tech Stack

**Backend:**
- Laravel 12 + Inertia.js 2.0
- Spatie Media Library 11, Permission 6, Activity Log 4
- Laravel Reverb 1.4 (WebSocket server)
- MariaDB + Redis

**Frontend:**
- React 19 + TypeScript 5.7
- Tailwind CSS 4.0 + shadcn/ui (24 components)
- Radix UI + Lucide Icons
- DataTables.net + DnD Kit + Dropzone

**Development:**
- Vite 6 (build tool)
- Docker + FrankenPHP
- Pest (testing)
- Laravel Pint (PHP formatter)
- ESLint + Prettier (JS/TS formatter)

## Key Features

- Dynamic Menu System (drag & drop, nested, permission-based)
- Real-time Activity Logging (WebSocket)
- Advanced File Manager (folders, public/private)
- Complete RBAC (Role & Permission)
- User Management (soft delete, restore)
- Dark Mode (light/dark/system)
- Social Login (Socialite)

---

## Architecture Overview

### Entry Points & Configuration
- `app/Providers/AppServiceProvider.php` - Shares global Inertia data (appSettings, sidebarMenus, env flags)
- `resources/js/app.tsx` - React application entry point
- `routes/web.php` - All route definitions
- `resources/js/types/index.d.ts` - Global TypeScript type definitions

### Key Models & Traits
- `User.php` - Uses HasRoles, HasMedia, LogsActivity, SoftDeletes
- `Menu.php` - Dynamic nested menu with permission filtering
- `AppSetting.php` - Singleton pattern for global settings
- `Gallery.php` - Media management with folder support
- `FilemanagerFolder.php` - Nested folder structure for gallery

### Services & Repositories Pattern
The codebase uses Service + Repository pattern for complex business logic:
- `app/Services/MenuService.php` - Menu operations, permission filtering
- `app/Repositories/` - Data access layer (contracts + implementations)
- Services are dependency-injected in controllers

### Critical Components
- `app-sidebar.tsx` - Dynamic sidebar from database menus
- `datatables.tsx` - Server-side DataTables with confirmation dialogs
- `TreeDnD.tsx` - Generic drag & drop tree component
- `page-container.tsx` - Standard page wrapper (required for consistency)
- `select.tsx` - Dark mode-aware CustomSelect component
- `private-image.tsx` - Authenticated image display for private files
- `toggle-tabs.tsx` - Filter tabs for active/trashed/all states
- `ui/` - 24 shadcn/ui components

### Helpers
- `app/Helpers/DataTable.php` - Server-side pagination helper
- `app/Helpers/MediaLibrary.php` - Spatie Media utilities
- `app/Helpers/Guards.php` - Permission guards

---

## Development Commands

### Concurrent Development (Recommended)
```bash
composer dev
# Runs 4 services concurrently: Laravel server, Queue, Vite, Reverb WebSocket
```

### Individual Services
```bash
php artisan serve              # Laravel dev server (port 8000)
php artisan queue:listen       # Queue worker
php artisan reverb:start       # WebSocket server (port 8080)
npm run dev                    # Vite dev server
```

### Database Operations
```bash
php artisan migrate:fresh --seed    # Reset DB with seeders
php artisan permission:cache-reset  # Clear permission cache
```

### Code Formatting
```bash
composer pint          # PHP (Laravel Pint)
npm run format         # JS/TS (Prettier)
npm run format:check   # Check formatting without fixing
```

### Testing
```bash
php artisan test                  # Run Pest tests
php artisan test --coverage       # With coverage
npm run types                     # TypeScript type checking
npm run lint                      # ESLint
```

### Production Build
```bash
npm run build          # Build frontend assets
composer deploy:prod   # Optimize Laravel (config, route, view cache + migrate)
```

### Docker
```bash
composer docker:dev    # Start Laravel Sail
composer docker:stop   # Stop containers
```

---

## Common Development Patterns

### Standard Page Layout
**ALWAYS use this pattern for page consistency:**

```tsx
import AppLayout from '@/layouts/app-layout';
import PageContainer from '@/components/page-container';
import Heading from '@/components/heading';
import { Head } from '@inertiajs/react';

export default function YourPage() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Your Page" />
            <PageContainer maxWidth="4xl">
                <Heading title="Title" description="Description" />
                {/* Content */}
            </PageContainer>
        </AppLayout>
    );
}
```

**PageContainer maxWidth guide:**
- `'full'` or `'none'` - Full width (use for DataTables)
- `'2xl'` - Simple forms, small content
- `'4xl'` - Complex forms with multiple sections (default)
- `'7xl'` - Gallery grids, wide layouts

**Centered content in full-width container:**
```tsx
<PageContainer maxWidth="full" centered centerWidth="2xl">
    {/* Centered content */}
</PageContainer>
```

---

### Server-Side DataTables Pattern

**Controller (Backend):**
```php
use App\Helpers\DataTable;

public function json(Request $request)
{
    $query = YourModel::query();

    // Add search logic if needed
    if ($request->filled('search.value')) {
        $search = $request->input('search.value');
        $query->where('name', 'like', "%{$search}%");
    }

    return DataTable::paginate($query, $request);
}
```

**Component (Frontend):**
```tsx
import DataTableWrapper from '@/components/datatables';

<DataTableWrapper
    ref={tableRef}
    ajax={{ url: route('resource.json'), type: 'GET' }}
    columns={columns}
    onDelete={(id) => router.delete(route('resource.destroy', id))}
/>
```

### Form Handling with Inertia

```tsx
import { useForm } from '@inertiajs/react';
import { toast } from '@/utils/toast';

const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
});

const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    post(route('resource.store'), {
        onSuccess: () => toast.success('Created successfully!'),
        onError: () => toast.error('Failed to create'),
    });
};
```

### Confirmation Dialogs

```tsx
import { useConfirmation } from '@/hooks/use-confirmation';
import ConfirmationDialog from '@/components/confirmation-dialog';

const { confirmationState, handleConfirm, handleCancel, openConfirmation } = useConfirmation();

// Trigger confirmation
openConfirmation({
    title: 'Delete Item?',
    message: 'This action cannot be undone.',
    onConfirm: () => router.delete(route('items.destroy', id)),
});

// Render dialog
<ConfirmationDialog
    state={confirmationState}
    onConfirm={handleConfirm}
    onCancel={handleCancel}
/>
```

---

## Permission System

**18 Permissions Available:**
- User Management: `view-users`, `create-users`, `edit-users`, `delete-users`
- Role Management: `view-roles`, `create-roles`, `edit-roles`, `delete-roles`
- Permission Management: `view-permissions`, `assign-permissions`
- File Manager: `view-gallery`, `upload-files`, `delete-files`, `manage-folders`
- General: `view-dashboard`, `manage-settings`, `view-activity-logs`, `manage-menus`

**Backend Protection (Route Middleware):**
```php
Route::middleware('permission:view-users')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

// Multiple permissions (OR logic)
Route::middleware('permission:view-roles|create-roles')->group(function () {
    // ...
});
```

**Frontend UI Hiding:**
```tsx
{user.permissions.includes('create-users') && (
    <Button>Create User</Button>
)}
```

**Important:** Always protect both backend (security) and frontend (UX)

## Coding Guidelines & Best Practices

### Backend (Laravel)
- Controllers: Single resource responsibility
- Models: Use Spatie traits (HasRoles, HasMedia, LogsActivity, SoftDeletes)
- Use Service + Repository pattern for complex business logic
- Validation: Use Form Request classes
- Extract reusable logic to `app/Helpers/`
- Run `composer pint` before commits

### Frontend (React/TypeScript)
- Components: Small, reusable, single responsibility
- Always define TypeScript interfaces (no `any` type)
- Use `@/` alias for imports (never `../../`)
- Prefer Tailwind utilities over custom CSS
- Use existing shadcn/ui components when available
- Run `npm run format` before commits

### File Naming Conventions
- Backend classes: PascalCase (`UserController.php`, `MenuService.php`)
- React components: PascalCase (`AppSidebar.tsx`, `DataTableWrapper.tsx`)
- Hooks/utils: kebab-case (`use-appearance.tsx`, `use-confirmation.tsx`)
- Routes: kebab-case (`/app-settings`, `/user-management`)

### Critical Rules
**ALWAYS:**
- Use `PageContainer` component for layout consistency
- Use `route()` helper (never hardcode URLs)
- Implement permission checks on backend (middleware) and frontend (UI)
- Use `toast` from `@/utils/toast` for notifications
- Use `useForm` from Inertia for form handling
- Check existing components before creating new ones

**NEVER:**
- Create custom CSS files (use Tailwind)
- Bypass permission checks
- Use `any` type in TypeScript
- Skip error handling in forms
- Forget loading states (`processing` from useForm)

## Important Implementation Details

### Real-time (WebSocket)
- Laravel Reverb runs on port 8080
- Activity log events broadcast automatically via `ActivityLogCreated` event
- Echo configuration: `resources/js/echo.js`
- Private channels require authentication
- Auto-broadcast on model changes (configured in `AppServiceProvider.php`)

### File Upload (Spatie Media Library)
- Public files: `storage/app/public` → symlinked to `public/storage`
- Private files: `storage/app/private` → requires authentication
- Media collections: `profile_image`, `gallery`, `attachments`
- Helper functions: `app/Helpers/MediaLibrary.php`
- Use `PrivateImage` component for authenticated image display

### Dynamic Menu System
- Loaded in `AppServiceProvider` → shared to all Inertia pages as `sidebarMenus`
- Permission filtering happens server-side (security)
- Supports unlimited nested levels (parent-child relationships)
- Icons: Lucide React library (200+ icons)
- Drag & drop reordering with auto-save
- Service + Repository pattern: `MenuService` + `MenuRepository`

### Dark Mode
- Modes: `light`, `dark`, `system` (follows OS preference)
- Storage: Cookie + localStorage sync
- Hook: `useAppearance()` for theme toggling
- Implementation: `.dark` class on `<html>` element
- Components are dark-mode aware (CustomSelect, etc.)

## Troubleshooting

### WebSocket Connection Failed
```bash
php artisan reverb:start
# Verify .env: BROADCAST_CONNECTION=reverb
```

### File Upload Not Working
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### Permission Errors
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

### Frontend Build Issues
```bash
npm run build
php artisan optimize:clear
```

---

## Default Login Accounts

**Admin** (full permissions):
- Email: `admin@example.com`
- Password: `password`

**User** (limited permissions):
- Email: `user@example.com`
- Password: `password`

---

## Additional Resources

- **Full Documentation:** `.claude/project.md` - Complete architecture, patterns, and implementation guides
- **Cursor Rules:** `.cursorrules` - AI coding assistant configuration
- **Routes:** `routes/web.php` - All application routes
- **TypeScript Types:** `resources/js/types/index.d.ts` - Global type definitions
- **Components:** `resources/js/components/` - Reusable UI components
