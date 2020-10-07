<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use External\Bar\Auth\LoginService;
use External\Foo\Auth\AuthWS;
use External\Baz\Auth\Authenticator;

use Lcobucci\JWT\Builder;

class AuthController extends Controller
{
    /**
     * Path to success class of baz check auth
     *
     * @var string
     */
    const SUCCESS_PATH_CLASS = 'External\Baz\Auth\Responses\Success';

    /**
     * Avaible systems
     * 
     * @var array
     */
    const AVAIBLE_SYSTEMS = ['FOO', 'BAR', 'BAZ'];

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {   
        $login      = $request->input('login');
        $password   = $request->input('password');
     
        if (
            isset($login) &&
            isset($password)
        ) {
            $bar    = new LoginService;
            $foo    = new AuthWS;
            $baz    = new Authenticator;
            $system = '';

            $barCheck = $bar->login($login, $password);

            if (true === $barCheck) 
            {
                $system = 'BAR';
            }

            try {
                $fooCheck = $foo->authenticate($login, $password);
                $system = 'FOO';

            } catch(\External\Foo\Exceptions\AuthenticationFailedException $e) {}

            $bazCheck = $baz->auth($login, $password);

            if (is_a($bazCheck, self::SUCCESS_PATH_CLASS))
            {
                $system = 'BAZ';
            }

            if (in_array($system, self::AVAIBLE_SYSTEMS)) {
                $builder = (new Builder)
                                ->setId($login)
                                ->setIssuer($request->getHost())
                                ->setAudience($request->getHost())
                                ->setSubject($system);

                return response()->json([
                    'status'    => 'success',
                    'token'     => (string)$builder->getToken()
                ]);
            }
        }

        return response()->json([
            'status' => 'failure',
        ]);
    }
}
