<?php
namespace App\Helpers;

// use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;


class TestHelper
{
    /**
     * Obtiene la cantidad de Tests que tiene un User,
     * Metodo para la opción de eliminar un test cuando se alcance el limite de 3.
    */
    public function getLengthTestsUser(Request $request, $test_type = '')
    {
        $user = $this->getUserLoggedIn($request);
        $tests = Test::where('user_id',$user->sub)->get()->all();
            
        return count($tests);
    }
   
}           