<?php

use Illuminate\Database\Seeder;

class ActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activity=new Activity();
        $activity->name="Seleccionar8";
        $activity->test_id=1;
        $activity->save();
    }
}
