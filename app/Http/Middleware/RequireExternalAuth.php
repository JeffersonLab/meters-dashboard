<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequireExternalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Authenticated users get a pass
        if (Auth::user()){
            return $next($request);
        }


        // For users arriving via the proxy server, we need to
        // find out if they're on-site or remote.  The latter
        // will be required to authenticate in order to view any
        // page, whereas on-site users won't have to authenticate just
        // to read, but only when they want to edit something.
        if($this->isProxiedForExternal($request)){
            if ( stristr($request->url(),'sso')
                || stristr($request->url(),'shib')
                || stristr($request->url(),'login')
                || stristr($request->url(),'logout')
            )
            {
                return $next($request);
            }
            return redirect(route(config('auth.routes.login','login')));
        }
        return $next($request);
    }

    /**
     * Performs a simple check to see if the address being proxied belongs
     * to the JLab Class B network or not.
     *
     * @param Request $request
     * @return boolean
     */
    protected function isProxiedForExternal(Request $request){
        if ((new TrustProxies())->isTrustedProxy($request->server->get('REMOTE_ADDR'))){
            $proxyFor = $request->server->get('HTTP_X_FORWARDED_FOR');
            if($proxyFor && substr($proxyFor, 0, 6) != '129.57'){

                return true;
            }
        }
        return false;
    }
}
