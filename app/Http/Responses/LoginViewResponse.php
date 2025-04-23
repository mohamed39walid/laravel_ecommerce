<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginViewResponse as LoginViewResponseContract;

class LoginViewResponse implements LoginViewResponseContract
{
    public function toResponse($request)
    {
        return response()->json([
            'message' => 'Unauthorized. Please log in.',
        ], 401);
    }
}
