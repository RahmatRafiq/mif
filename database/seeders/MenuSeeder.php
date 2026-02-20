<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Dashboard
        Menu::create([
            'title' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'LayoutDashboard',
            'order' => 1,
        ]);

        // Production Menu (Flat - No Tree)
        Menu::create([
            'title' => 'Schedules',
            'route' => 'production.schedules.index',
            'icon' => 'Calendar',
            'order' => 2,
        ]);

        Menu::create([
            'title' => 'Orders',
            'route' => 'production.orders.index',
            'icon' => 'ShoppingCart',
            'order' => 3,
        ]);

        Menu::create([
            'title' => 'Sewing Lines',
            'route' => 'production.lines.index',
            'icon' => 'Layers',
            'order' => 4,
        ]);

        // User Management
        $users = Menu::create([
            'title' => 'User Management',
            'icon' => 'Users',
            'order' => 5,
            'permission' => 'view-users',
        ]);

        Menu::create([
            'title' => 'Users',
            'route' => 'users.index',
            'icon' => 'UserCheck',
            'parent_id' => $users->id,
            'permission' => 'view-users',
            'order' => 1,
        ]);

        Menu::create([
            'title' => 'Roles',
            'route' => 'roles.index',
            'icon' => 'Shield',
            'parent_id' => $users->id,
            'permission' => 'view-roles',
            'order' => 2,
        ]);

        Menu::create([
            'title' => 'Permissions',
            'route' => 'permissions.index',
            'icon' => 'Key',
            'parent_id' => $users->id,
            'permission' => 'view-permissions',
            'order' => 3,
        ]);

        // System Settings
        $system = Menu::create([
            'title' => 'System',
            'icon' => 'Settings',
            'order' => 6,
            'permission' => 'manage-settings',
        ]);

        Menu::create([
            'title' => 'Gallery',
            'route' => 'gallery.index',
            'icon' => 'FileText',
            'parent_id' => $system->id,
            'permission' => 'view-gallery',
            'order' => 1,
        ]);

        Menu::create([
            'title' => 'App Settings',
            'route' => 'app-settings.index',
            'icon' => 'Settings',
            'parent_id' => $system->id,
            'permission' => 'manage-settings',
            'order' => 2,
        ]);

        Menu::create([
            'title' => 'Menu Management',
            'route' => 'menus.manage',
            'icon' => 'Menu',
            'parent_id' => $system->id,
            'permission' => 'manage-settings',
            'order' => 3,
        ]);

        Menu::create([
            'title' => 'Activity Logs',
            'route' => 'activity-logs.index',
            'icon' => 'ListChecks',
            'parent_id' => $system->id,
            'permission' => 'view-activity-logs',
            'order' => 4,
        ]);
    }
}
