<?php

use Illuminate\Database\Seeder;
use App\User;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name ="admin1";
        $user->surname ="Gomez Perez";
        $user->role = "ROLE_ADMIN";
        $user->email = "admin@mail.com";
        $user->password = bcrypt('admin');
        $user->save();  
       
        $user = new User();
        $user->name ="user1";
        $user->surname ="Sanin Gil";
        $user->role = "ROLE_USER";
        $user->email = "user@mail.com";
        $user->password = bcrypt('user');
        $user->save();  
        // $user->roles()->attach( $role_user);
    }
}
