<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmployeePermissionsSeeder extends Seeder
{
    public function run()
    {
        // Get the Employee role
        $employeeRole = Role::where('name', 'Employee')->first();
        
        if ($employeeRole) {
            // Assign product-related permissions
            $employeeRole->givePermissionTo([
                'add_products',
                'edit_products',
                'delete_products'
            ]);
        }
    }
} 