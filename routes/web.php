<?php
use App\Activity;
use App\Section;
use App\Test;
use App\User;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // $user = User::find(4);//este user se obtiene request
    // $sectionA=Section::where('name','A')->first();
    // $sectionB=Section::where('name','B')->first();
    // $sectionC=Section::where('name','C')->first();
    // $sectionD=Section::where('name','D')->first();

    // $test=new Test();
    // $test->name="Test Herrmann";
    // $test->type="Autoconocimiento";
    // $test->state=1;
    // $test->save();
    // $test->users()->attach($user,['interpretation'=>'']);

    // $activity=new Activity();
    // $activity->name="Seleccionar8";
    // $activity->test_id=9;
    // $activity->save();
    // $activity->sections()->attach( $sectionA,['score'=>7]);
    // $activity->sections()->attach( $sectionB,['score'=>1]);
    // $activity->sections()->attach( $sectionC,['score'=>4]);
    // $activity->sections()->attach( $sectionD,['score'=>6]);

    $test = Test::find(9);
    $activities=$test->activities;

    foreach( $activities as $activity){
        echo $activity->name." :".$activity->id."<br>"; 
        foreach ($activity->sections as $section) {
            echo $section->name."-".$section->pivot->score."<br>";
        }
    }
    return "si";

});
