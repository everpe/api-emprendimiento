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
        $user->image="general_user.jpeg";
        $user->assignRole('administrator');
        $user->save();  
       
        $user = new User();
        $user->name ="user1";
        $user->surname ="Sanin Gil";
        $user->email = "user@mail.com";
        $user->password = hash('sha256','user');
        $user->description = "";
        $user->image="general_user.jpeg";
        $user->assignRole('student');
        $user->save();  

        $user = new User();
        $user->name ="user2";
        $user->surname ="Sanin Gal";
        $user->email = "userrr@mail.com";
        $user->password = hash('sha256','user');
        $user->description = "kjjajajjajajjaja";
        $user->image="general_user.jpeg";
        $user->assignRole('student');
        $user->save();  

        $user = new User();
        $user->name ="user3";
        $user->surname ="Saninn Gal";
        $user->email = "userr3r@mail.com";
        $user->password = hash('sha256','user');
        $user->description = "jejejejjeje";
        $user->image="general_user.jpeg";
        $user->assignRole('student');
        $user->save(); 
    }
}
