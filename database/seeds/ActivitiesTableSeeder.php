<?php

use Illuminate\Database\Seeder;
use App\Activity;
use App\Section;
class ActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sectionA=Section::where('name','A')->first();
        $sectionB=Section::where('name','B')->first();
        $sectionC=Section::where('name','C')->first();
        $sectionD=Section::where('name','D')->first();

        
        $activity1= new Activity();
        $activity1->name="Actividad1";
        $activity1->test_id=1;
        $activity1->save();
        $activity1->sections()->attach( $sectionA);
        $activity1->sections()->attach( $sectionB);
        $activity1->sections()->attach( $sectionC);
        $activity1->sections()->attach( $sectionD);

        $activity2= new Activity();
        $activity2->name="Actividad2";
        $activity2->test_id=1;
        $activity2->save();
        $activity2->sections()->attach( $sectionA);
        $activity2->sections()->attach( $sectionB);
        $activity2->sections()->attach( $sectionC);
        $activity2->sections()->attach( $sectionD);

        $activity3= new Activity();
        $activity3->name="Actividad3";
        $activity3->test_id=1;
        $activity3->save();
        $activity3->sections()->attach( $sectionA);
        $activity3->sections()->attach( $sectionB);
        $activity3->sections()->attach( $sectionC);
        $activity3->sections()->attach( $sectionD);
        

    }
}
