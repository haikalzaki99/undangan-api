<?php

namespace App\Middleware;

use App\Response\JsonResponse;
use Closure;
use Core\Http\Request;
use Core\Middleware\MiddlewareInterface;

final class OwnerMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->server->get('HTTP_X_ACCESS_KEY');

        $ownerKey = env('OWNER_KEY');
        if (empty($ownerKey) || empty($key) || !hash_equals($ownerKey, strval($key))) {
            return (new JsonResponse)->errorBadRequest(['invalid owner key.']);
        }

        return $next($request);
    }
}
