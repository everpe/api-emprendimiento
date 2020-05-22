<?php

namespace App\Http\Controllers;

use App\Test;

use App\Activity;
use App\User;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Firebase\JWT\JWT;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class TestController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth',
        ['except'=>['getScores','setMessage']]);
    }

    /**
     *Todas las pruebas creadas y le adjunto el user creador de cada test.
     *Metodo que solo puede acceder el Administrador
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tests=Test::all();
        $sub=$this->getUserLoggedIn($request)->sub;
        $user=$this->getUser($sub);
        // $quantity_activities=$this->countActivities()
        // hasRole('administrator')
        if($user->can('list  all tests')){
            $tests=Test::All()->load('user')->load('activities')->where('state', true);
            return response()->json([
                'code'=>200,
                'status'=>'success',
                'tests'=>$tests
            ],200);
        }else{
            return response()->json([
                'code'=>401,
                'status'=>'error',
                'message'=>'Solo El Admin tiene permisos'
            ],401);
        }
    }


    /**
     * Retorna la cantidad de actividades que tiene cada Test
     */
    public function countActivities($id_test){
        $test=Test::where('id',$id_test)->get();
        return count($test->activities());
    }

    /**
     * Obtiene un El usuario decode logueado necesario para algunos metodos que usan al user.
     */
    public function getUserLoggedIn(Request $request){
        $token=$request->header('Authorization');
        $jwtAuth= new \JwtAuth();
        $user=$jwtAuth->checkToken($token,true);
        return $user;
    }
    public function getUser($id_user){
        // $id_user=$id_user;
        $user=User::find($id_user);
        if(is_object($user)){
            return $user;
        }
        return false;
    }
    /**
     * Obtiene el user logueado, Crea un Test de Herrmann en blanco,
     * y se lo asigna a ese user logueado.
     */
    public function createHerrmann(Request $request){
        $user=$this->getUserLoggedIn($request);

        $test= new Test();
        $test->name="Test De Herrmann";
        $test->type="AUTOCONOCIMIENTO";
        $test->state=0;
        $test->user_id=$user->sub;
        $test->interpretation='Not Interpreted Yet';
        //Obtengo cantidad de test del user logueado
        $tests=$this->getLengthTestsUser($request);
        if($tests>=3){
           $this->deleteOldestTest($user->sub);
        }
        $test->save();
        $data=[
            'code'=>200,
            'status'=>'success',
            'messagge'=>'Has creado Un test Herrmann para resolver:Exitos',
            'id_test_creado'=>$test->id,
            'length_test_existing'=>$this->getLengthTestsUser($request)
        ];
        return response()->json($data,$data['code']);  
    }

/**
 * Define los puntajes totales de cada actividad,
 * e interpreta esos puntajes en un mensaje 
 */
    public function interpretHerrmann($id_test){
        $activities= Activity::where([
            ['test_id', '=', $id_test],['name', '=', 'Seleccionar Palabras']
        ])->get();
        $scores=$this->getScores($activities);

        $hemisphereActivity=Activity::where([
            ['test_id', '=', $id_test],['name', '=', 'Hemisferio Cerebral']
        ])->get()->first();
        $hemisphere=$this->getHemisphere($hemisphereActivity);    

        
        if(!empty($scores) && ($scores!=null))
        {
            //Actualizar el estado e interpretaciín del Test.
            $test=Test::where('id',$id_test)->update(['state' => 1]);
            $interpretation=$this->setMessage($scores);
            $test=Test::where('id',$id_test)->update(['interpretation' => $interpretation]);
            $data=[
                'code'=>200,
                'status'=>'succes',
                'messagge'=>"Test Analizado Correctamente",
                'scors'=>$scores,
                'interpretation'=>$interpretation,
                'hemisphere'=>$hemisphere

            ];
        }else{      
            $data=[
                'code'=>400,
                'status'=>'error',
                'messagge'=>"Esta Prueba No ha completado las actividades Suficientes, o no existe"
            ];
        }
        return  response()->json($data,$data['code']);
    }

    /**
     * Saca el puntaje numerico de cada sección de cada actividad normal
     * (la de hemisferio se analiza de otra manera) y los suma.
     */
    public function getScores($activities){
        if(count($activities)==3)
        {
            //en cada posición guarda las secciones de cada actividad
            $sections=array();
            $cont=0;
            foreach( $activities as $activity){        
                $sections[$cont]=$activity->sections;
                $cont++;
            }
            //se guarda en variables todas las secciones de cada actividad
            $sectionsA1=$sections[0];
            $sectionsA2=$sections[1];
            $sectionsA3=$sections[2];
            
            //Se saca cada sección por separado
            $sectionA1=$sectionsA1['0'];//SeccionA de la actividad 1...
            $sectionB1=$sectionsA1['1'];
            $sectionC1=$sectionsA1['2'];
            $sectionD1=$sectionsA1['3'];

            $sectionA2=$sectionsA2['0'];
            $sectionB2=$sectionsA2['1'];
            $sectionC2=$sectionsA2['2'];
            $sectionD2=$sectionsA2['3'];

            $sectionA3=$sectionsA3['0'];
            $sectionB3=$sectionsA3['1'];
            $sectionC3=$sectionsA3['2'];
            $sectionD3=$sectionsA3['3'];

            $scores=array(
                'A'=>$sectionA1->pivot->score+$sectionA2->pivot->score+$sectionA3->pivot->score,
                'B'=>$sectionB1->pivot->score+$sectionB2->pivot->score+$sectionB3->pivot->score,
                'C'=>$sectionC1->pivot->score+$sectionC2->pivot->score+$sectionC3->pivot->score,
                'D'=>$sectionD1->pivot->score+$sectionD2->pivot->score+$sectionD3->pivot->score
            );
            return $scores;
        }
        return null;
    }


    /**
     * Interpreta los puntajes totales de cada sección para dar una interpretación textual a la 
     * prueba.
     */
    public function setMessage($scores){
        $A=$scores['A'];
        $B=$scores['B'];
        $C=$scores['C'];
        $D=$scores['D'];
        $message='';
        $messages=array(
        'A_Mayor'=>[
            'Dominance'=>'Dominancia A',
            'Nickname'=>'Técnico-Financiero',
            'Description'=>'Eres Lógico muy racional,basado en hechos y cuantitativo,se te dan bien los números,
            la abstracción y resolución de Probremas Lógicos',
            'Characteristics'=>'Generalmente frio, distante, de pocos gestos,individualmente brillante, competitivo',
            'Weakness'=>'Indivudialista'],
        'AB_Mayor'=>[
            'Dominance'=>'Dominancia A-B',
            'Nickname'=>'Legalista-Coordinador',
            'Description'=>'Eres realista y de sentido común,es decir,una persona analítica, verbal, secuencial 
                y controlada',
            'Characteristics'=>'Rigor, métodico, solidez en su forma de accionar, capacidad de análisis y de razonamiento',
            'Weakness'=> 'falta de imaginación'],
        'AD_Mayor'=>[
            'Dominance'=>'Dominancia A-D',
            'Nickname'=>'Pragmatico-Cerebral',
            'Description'=>'Eres lógico, resuelves problemas, matemático, técnico, analista y imaginativo, artístico, conceptualizador',
            'Characteristics'=>'Inteligencia vivaz, técnico de alto nivel, buen nivel de análisis y de síntesis',
            'Weakness'=>'Incapacidad de ver a tus semejantes y de percibir el efecto que produces en los demás'],  
        'AC_Mayor'=>[
            'Dominance'=>'Dominancia A-C',
            'Nickname'=>'Técnico-Servicial',
            'Description'=>'Combinas a la perfección la brillantes intelectual, con las competencias de trabajo en equipo',
            'Characteristics'=>'Lógico muy racional,basado en hechos con excelentes relaciones interpersonales y 
                con tendencia al dialogo',
            'Weakness'=>'Sueles hacer demasiadas concesiones. Lo cual te puede producir gran insatisfacción de fondo'],      
        'B_Mayor'=>[
            'Dominance'=>'Dominancia B',
            'Nickname'=>'Administrativo-Controlador',
            'Description'=>'Eres muy administrativo, con buen sentido de organización y puesta en marcha',
            'Characteristics'=>'Secuencial, persistente, estructurado, controlado,detallista,organizado, planeador,conservador.',
            'Weakness'=>'Falta de asumir riegos y aventurarse'],
        'BC_Mayor'=>[
            'Dominance'=>'Dominancia B-C',
            'Nickname'=>'Instintivo-Visceral',
            'Description'=>'Eres buen organizador, planeador, controlador, conservador y excelente en 
                temas administrativos pero también espiritual, humanitario, afectivo, emocional y disfruta del contacto con otros',
            'Characteristics'=>'Lúdico tendencia a trabajar en equipo y buen lider',
            'Weakness'=>'Te fías demasiado de tu instinto y te sules confundir,te falta de asertividad y 
                puedes llegar a caer en incontinencia verbal.'],
        'BD_Mayor'=>[
            'Dominance'=> 'Dominancia B-D',
            'Nickname'=>'Organizador-Creativo',
            'Description'=>'Eres buen organizador, planeador, controlador, conservador creativo en innovador',
            'Characteristics'=>'combina a la perfección el sentido de la organización y el método con la creatividad y la innovación',
            'Weakness'=>'puede llegar a paralizarse entre el deseo de innovar y el miedo al cambio.'],
        
        'C_Mayor'=>[
            'Dominance'=> 'Dominancia C',
            'Nickname'=>'Servicial-Sociable',
            'Description'=>'Tiendes a ser extrovertido y muy emotivo, tendencia al dialogo y trabajo en equipo',
            'Characteristics'=>'empático, receptivo, cooperativo, expresivo, sensible, espiritual,amigable,confiable,emocional, sentimental',
            'Weakness'=>'Tiende a ser muy susceptible y a reaccionar espontaneamente frente a críticas negativas'],
    
        'CD_Mayor'=>[
            'Dominance'=> 'Dominancia C-D',
            'Nickname'=>'Desarrollador-Expresivo',
            'Description'=>'Eres  intuitivo, visual, sintético, receptivo, imaginativo, impulsivo,futurista',
            'Characteristics'=>'Espontaneidad, innovación, afectividad y gusto por el riesgo',
            'Weakness'=>'Relaciones Incompatibles'],
        'D_Mayor'=>[
            'Dominance'=>'Dominancia D',
            'Nickname'=>'Artistico-Emprendedor',
            'Description'=>'Eres alguien soñador, creativo que encuentra soluciones rápidas 
            a los problemas,buscas alternativas por diversos caminos',
            'Characteristics'=>'Muy visual, intuitivo, creativo, imaginativo,explorador, aventurero, 
                rompe reglas, curiosos,tomador de riesgos, mente abierta, especulador',
            'Weakness'=>'Falta de acertividad'],

        'A_B_C_D'=>[
            'Dominance'=>'Multidominancia',
            'Nickname'=>'Multidominante-Comunicador',
            'Description'=>'Puedes desenvolverte en ámbitos muy variados, pero se te dificulta un tanto 
            la toma de decisiones']
        );

        if($A>$C && $A>$D && $B>$C && $B>$D){
            $message=$messages['AB_Mayor'];
        }elseif($A>$B && $A>$C && $D>$C && $D>B){
            $message=$messages['AD_Mayor'];
        }elseif($A>$B && $A>$D && $C>$D && $C>$B){
            $message=$messages['AC_Mayor'];  
        }
     
        elseif($B>$D && $B>$A && $C>$A && $C>$D){
            $message=$messages['BC_Mayor'];
        }elseif($B>$C && $B>$A && $D>$C && $D>$A){
            $message=$messages['BD_Mayor'];
        }
      
        elseif($C>$A && $C>$B  && $D>$A && $D>$B ){
            $message=$messages['CD_Mayor'];
        }
        elseif($A>$B && $A>$C && $A>$D){
            $message=$messages['A_Mayor'];
        }
        elseif($B>$A && $B>$C && $B>$D){
            $message=$messages['B_Mayor'];
        }
        elseif($C>$A && $C>$B && $C>$D){
            $message=$messages['C_Mayor'];
        }
        elseif($D>$A && $D>$B && $D>$C){
            $message=$messages['D_Mayor'];
        }elseif($A==$B && $B==$C && $C==$D){
            $message=$messages['A_B_C_D'];
        }

        // $this->getScoresHemisphere()
        // $data=array([
        //     'interpretation'=>$message,
        //     'hemisphere'=
        // ]);
       
        return $message;
    }

    /**
     * Obtiene la interpretación de la activida Hesmisphere
     * comparando los scores de sus 2 secciones.
     */
    public function getHemisphere($activity_hemisphere){
        // echo $activity_hemisphere->name;
        if($activity_hemisphere->name=="Hemisferio Cerebral"&&count($activity_hemisphere->sections)==2){
            $sections=$activity_hemisphere->sections;
            
            $a=$sections[0]->pivot->score;
            $d=$sections[1]->pivot->score;
            if($a > $d){
                // $messagge="No tienes una buena Capicidad de ejecución debes trabajar en ello";
                $messagge='2';
            }elseif($d > $a){
                // $messagge="Tienes buena capacidad de ejecucion";
                $messagge='1';
            }elseif($a == $d){
                // $messagge="Tienes una capacidad de ejecución Neutral";          
                  $messagge='0';
            }
            return $messagge;
        }
        return null;
    }
   
     /**
     * Obtener los Test  que pertenecen al usuario logueado
     * Todos los users pueden listar sus test entonces no valido Role 
     * @param el id del usuario.
     */
    public function getTestsByUser(Request $request){

        //El usuario que está logueado
        $user=$this->getUserLoggedIn($request);

        $tests=Test::where('user_id',$user->sub)->where('state', true)->get();
        if(count($tests)>0){
            return response()->json([
                'code'=>200,
                'status'=>'Success',
                'nameUser'=>$user->name,
                'Tests'=>$tests
            ],200);
        }else{
            return response()->json([
                'code'=>200,
                'message'=>'El User No tiene Tests Creados',
                'nameUser'=>$user->name,
                'Tests'=>$tests
            ],200);
        }
        
    }

    /**
     * Borrar un test en específico, solo el admin puede eliminar. 
     */
    public function deleteTest(Request $request,$id_test){

        $sub=$this->getUserLoggedIn($request)->sub;
        $user=$this->getUser($sub);
        if($user->can('delete test')){
            $test = Test::find($id_test);
            if(!empty($test) && is_object($test)){
                $test->delete();
                return response()->json([
                    'code'=>200,
                    'status'=>'Test eliminado correctamente',
                    'id_test_elimanted'=>$id_test
                ],200);
            }else{
                return response()->json([
                    'code'=>406,
                    'status'=>'error',
                    'message'=>'Test no Encontrado'
                ],401);
            }
            
        }else{
            return response()->json([
                'code'=>401,
                'status'=>'error',
                'message'=>'Solo El Admin tiene permisos para eliminar Test'
            ],401);
        }
        
    }

    /**
     * Borrar el test más antiguo solo si ya tiene el limite de 3 test po user 
     */
    public function deleteOldestTest($id_user){
        $firstTest=Test::where('user_id', '=', $id_user)->first();
        $firstTest->delete();
        return $firstTest;
    }


    /**
     * Obtiene la cantidad de Tests que tiene un User,
     * Metodo para la opción de eliminar un test cuando se alcance el limite de 3.
     */
    public function getLengthTestsUser(Request $request){
        $user=$this->getUserLoggedIn($request);
        $tests=Test::where('user_id',$user->sub)->get()->all();
            return count($tests);
    }

}
