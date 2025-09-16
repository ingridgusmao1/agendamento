<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($req, Closure $next, ...$allowed){
        $user = $req->user();
        abort_if(!$user, 401);

        if ($user->type === 'admin') {
            return $next($req);
        }

        abort_if(!in_array($user->type, $allowed, true), 403);

        return $next($req);
    }
}
