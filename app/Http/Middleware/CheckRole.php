<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            $route = match($user->role) {
                'owner'  => 'owner.report',
                'kasir'  => 'pos.index',
                'member' => 'member.profile',
                default  => 'login',
            };
            return redirect()->route($route)->with('error', 'Akses dialihkan: Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
