<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $dashboard = Menu::create([
            'title' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'LayoutDashboard',
            'order' => 1,
        ]);

        // Production Management
        $production = Menu::create([
            'title' => 'Production',
            'icon' => 'Factory',
            'order' => 2,
            'permission' => 'view-schedules',
        ]);
        Menu::create([
            'title' => 'Schedules',
            'route' => 'production.schedules.index',
            'icon' => 'Calendar',
            'parent_id' => $production->id,
            'permission' => 'view-schedules',
            'order' => 1,
        ]);
        Menu::create([
            'title' => 'Orders',
            'route' => 'production.orders.index',
            'icon' => 'ShoppingCart',
            'parent_id' => $production->id,
            'permission' => 'manage-orders',
            'order' => 2,
        ]);
        Menu::create([
            'title' => 'Sewing Lines',
            'route' => 'production.lines.index',
            'icon' => 'Layers',
            'parent_id' => $production->id,
            'permission' => 'manage-lines',
            'order' => 3,
        ]);

        $gallery = Menu::create([
            'title' => 'Gallery',
            'route' => 'gallery.index',
            'icon' => 'FileText',
            'order' => 3,
        ]);
        $users = Menu::create([
            'title' => 'Users Management',
            'icon' => 'Users',
            'order' => 4,
            'permission' => 'view-users',
        ]);
        Menu::create([
            'title' => 'Roles',
            'route' => 'roles.index',
            'icon' => 'Shield',
            'parent_id' => $users->id,
            'permission' => 'view-roles',
            'order' => 1,
        ]);
        Menu::create([
            'title' => 'Permissions',
            'route' => 'permissions.index',
            'icon' => 'Key',
            'parent_id' => $users->id,
            'permission' => 'view-permissions',
            'order' => 2,
        ]);
        Menu::create([
            'title' => 'User',
            'route' => 'users.index',
            'icon' => 'UserCheck',
            'parent_id' => $users->id,
            'permission' => 'view-users',
            'order' => 3,
        ]);
        Menu::create([
            'title' => 'App Settings',
            'route' => 'app-settings.index',
            'icon' => 'Settings',
            'permission' => 'manage-settings',
            'order' => 5,
        ]);

        Menu::create([
            'title' => 'Menu Management',
            'route' => 'menus.manage',
            'icon' => 'Settings',
            'permission' => 'manage-settings',
            'order' => 6,
        ]);
        Menu::create([
            'title' => 'Activity Logs',
            'route' => 'activity-logs.index',
            'icon' => 'ListChecks',
            'permission' => 'view-activity-logs',
            'order' => 7,
        ]);

    }
}
