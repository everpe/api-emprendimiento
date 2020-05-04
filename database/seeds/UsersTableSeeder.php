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
        $user->email = "admin@mail.com";
        $user->password  = hash('sha256','admin');
        $user->description = "";
        $user->save();  
       
        $user = new User();
        $user->name ="user1";
        $user->surname ="Sanin Gil";
        $user->email = "user@mail.com";
        $user->password = hash('sha256','user');
        $user->description = "";
        $user->save();  
        // $user->roles()->attach( $role_user);
    }
}
