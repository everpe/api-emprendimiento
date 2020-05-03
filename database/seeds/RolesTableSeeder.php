<?php

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //creando roles
        $role1 = Role::create(['name' => 'administrator']);
        $role2 = Role::create(['name' => 'student']);
        $role3 = Role::create(['name' => 'entrepreneur']);
        
        //creando los permisos
        $permission1 = Permission::create(['name' => 'list  all tests']);
        $permission2 = Permission::create(['name' => 'create test']);
        $permission3 = Permission::create(['name' => 'list my tests']);
        $permission4 = Permission::create(['name' => 'delete test']);
        $permission5 = Permission::create(['name' => 'edit status user']);
        //asignado permisos a los roles
        $role1->givePermissionTo('list  all tests');
        $role1->givePermissionTo('create test');
        $role1->givePermissionTo('list my tests');
        $role1->givePermissionTo('delete test');
        $role1->givePermissionTo('edit status user');
        //estudent
        $role2->givePermissionTo('create test');
        $role2->givePermissionTo('list my tests');
        $role2->givePermissionTo('delete test');
        //Entrepreneur
        $role3->givePermissionTo('create test');
        $role3->givePermissionTo('list my tests');
        $role3->givePermissionTo('delete test');

        

    }
}
