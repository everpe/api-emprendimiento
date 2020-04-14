<?php

use Illuminate\Database\Seeder;
use App\Section;
class SectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $section = new Section();
        $section->name ="A";
        $section->save(); 

        $section = new Section();
        $section->name ="B";
        $section->save(); 

        $section = new Section();
        $section->name ="C";
        $section->save(); 

        $section = new Section();
        $section->name ="D";
        $section->save();  

    }
}
