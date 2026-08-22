<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedCidrs = config('app.allowed_ips_cidr', []);

        if (empty($allowedCidrs)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        foreach ($allowedCidrs as $cidr) {
            if ($this->ipInCidr($clientIp, $cidr)) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak: IP tidak diizinkan');
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr);
        $mask = (int) $mask;

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}