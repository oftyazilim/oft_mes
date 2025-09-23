<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // API / SPA ortamı: Laravel varsayılanı 'login' adlı route'a redirect etmeye çalışır.
        // Projede named 'login' route yok; bu yüzden RouteNotFoundException oluşuyor.
        // Sanctum / API isteklerinde redirect yerine JSON 401 dön.
        \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        \Illuminate\Auth\Middleware\Authenticate::redirectUsing(function ($request) {
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                abort(response()->json(['message' => 'Unauthenticated'], 401));
            }
            // Eğer ileride bir login view eklenirse buraya route('login') konabilir.
            return '/';
        });

        // Feedback API için rate limit
        RateLimiter::for('feedback', function (HttpRequest $request) {
            $ip = $request->ip();
            $key = 'feedback:' . $ip;
            return Limit::perMinute(5)->by($key);
        });
        // Customize reset password URL for SPA frontend
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $base = env('FRONTEND_URL', config('app.url'));
            $path = env('FRONTEND_PASSWORD_RESET_PATH', '/reset-password');
            // Ensure no trailing slash duplication
            $base = rtrim($base ?? '', '/');
            $path = '/' . ltrim($path, '/');
            $email = urlencode($user->email);
            $tokenParam = urlencode($token);
            // Example: https://frontend/reset-password?token=...&email=...
            return "$base$path?token=$tokenParam&email=$email";
        });

        // DB Query Logger -> logs into MSSQL users_logs
        if (config('dbquerylog.enabled')) {
            DB::listen(function ($query) {
                try {
                    $sql = $query->sql;
                    $bindings = $query->bindings;
                    $time = $query->time; // ms

                    // Which connection did this event come from (Laravel 9+ adds connectionName)
                    $connectionName = property_exists($query, 'connectionName') ? $query->connectionName : null;
                    $allowed = config('dbquerylog.connections');
                    if (is_array($allowed) && $connectionName && !in_array($connectionName, $allowed, true)) {
                        return; // filtered out
                    }

                    // Prevent recursion and ignore noisy patterns
                    $ignoreList = (array) config('dbquerylog.ignore_contains', []);
                    $haystack = strtolower($sql);
                    foreach ($ignoreList as $needle) {
                        if ($needle !== null && $needle !== '' && str_contains($haystack, strtolower($needle))) {
                            return;
                        }
                    }

                    // Skip SELECT queries to reduce noise and size
                    if (preg_match('/^\s*select\b/i', $sql)) {
                        return;
                    }

                    // Safely interpolate bindings for readability
                    $rendered = $sql;
                    foreach ($bindings as $binding) {
                        $value = $binding;
                        if ($value instanceof \DateTimeInterface) {
                            $value = $value->format('Y-m-d H:i:s');
                        }
                        if (is_string($value)) {
                            $value = str_replace("'", "''", $value);
                            $value = "'{$value}'";
                        } elseif (is_null($value)) {
                            $value = 'NULL';
                        } elseif (is_bool($value)) {
                            $value = $value ? 1 : 0;
                        }
                        $rendered = preg_replace('/\?/', (string) $value, $rendered, 1);
                    }

                    // Truncate to fit column
                    $maxLen = (int) config('dbquerylog.max_sql_length', 1000);
                    if (strlen($rendered) > $maxLen) {
                        $rendered = substr($rendered, 0, $maxLen - 3) . '...';
                    }

                    // Build context
                    $user = Auth::user();
                    $userId = $user?->id
                        ?? (request()->user()?->id ?? null)
                        ?? (is_numeric(request()->header('X-User-Id')) ? (int) request()->header('X-User-Id') : null)
                        ?? 0; // fallback to 0 if not authenticated

                    $ip = Request::ip() ?: '127.0.0.1';
                    $method = request()?->method() ?? (app()->runningInConsole() ? 'CLI' : 'N/A');
                    $routeName = optional(request()->route())->getName();
                    $path = optional(request())->path();
                    $fullUrl = optional(request())->fullUrl();
                    $userAgent = request()->header('User-Agent');

                    // Stable per-request id
                    static $reqId = null;
                    if (!$reqId) {
                        $reqId = (string) Str::uuid();
                    }

                    // user_id = 0 skip rule
                    if (config('dbquerylog.skip_user_id_zero') && (int)$userId === 0) {
                        return;
                    }

                    // write to MSSQL users_logs via configured connection
                    $logConn = (string) config('dbquerylog.log_connection', 'pgsql_oft');
                    DB::connection($logConn)->table('users_logs')->insert([
                        'user_id' => $userId,
                        'tarih'   => now(),
                        'sayfa'   => Str::limit((string) ($fullUrl ?: $path), 255, ''),
                        'eylem'   => Str::limit(sprintf('SQL %s %s (%d ms)', $method, ($path ?: '-'), (int) $time), 255, ''),
                        'ip'      => $ip,
                        'komut'   => Str::limit($rendered, $maxLen, ''),
                        // new explicit columns
                        'request_id' => $reqId,
                        'method'     => Str::limit((string) $method, 10, ''),
                        'route'      => Str::limit((string) ($routeName ?: $path ?: '-'), 255, ''),
                        'user_agent' => Str::limit((string) ($userAgent ?: '-'), 255, ''),
                        'connection' => Str::limit((string) ($connectionName ?: '-'), 64, ''),
                    ]);
                } catch (\Throwable $e) {
                    // Never break the request for logging; write to app log instead
                    Log::debug('DB query log skipped: ' . $e->getMessage());
                }
            });
        }

        // Explicit login event logging (successful logins)
        Event::listen(Login::class, function (Login $event) {
            try {
                $user = $event->user;
                $userId = $user?->id ?? 0;
                $ip = Request::ip() ?: '127.0.0.1';
                $method = 'AUTH';
                $path = optional(request())->path() ?: 'auth/login';
                $fullUrl = optional(request())->fullUrl();
                $userAgent = request()->header('User-Agent');
                $reqId = (string) Str::uuid();

                // Reset throttle counter on success (best-effort)
                $identifier = strtolower((string) ($user?->email ?? $user?->username ?? $user?->name ?? $userId));
                if ($identifier) {
                    $key = sprintf('auth:login:fail:%s|%s', $identifier, $ip);
                    Cache::forget($key);
                }

                if (!(config('dbquerylog.skip_user_id_zero') && (int)$userId === 0)) {
                    $logConn = (string) config('dbquerylog.log_connection', 'pgsql_oft');
                    DB::connection($logConn)->table('users_logs')->insert([
                        'user_id' => $userId,
                        'tarih'   => now(),
                        'sayfa'   => Str::limit((string) ($fullUrl ?: $path), 255, ''),
                        'eylem'   => 'LOGIN SUCCESS',
                        'ip'      => $ip,
                        'komut'   => '-',
                        'request_id' => $reqId,
                        'method'     => $method,
                        'route'      => Str::limit((string) $path, 255, ''),
                        'user_agent' => Str::limit((string) ($userAgent ?: '-'), 255, ''),
                        'connection' => '-',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::debug('Login log skipped: ' . $e->getMessage());
            }
        });

        // Failed login attempts
        Event::listen(Failed::class, function (Failed $event) {
            try {
                $user = $event->user; // may be null
                $userId = $user?->id ?? 0;
                $ip = Request::ip() ?: '127.0.0.1';
                $method = 'AUTH';
                $path = optional(request())->path() ?: 'auth/login';
                $fullUrl = optional(request())->fullUrl();
                $userAgent = request()->header('User-Agent');
                $reqId = (string) Str::uuid();

                // Throttle attempt counter (15 dk pencerede)
                $cred = (array) $event->credentials;
                $identifier = strtolower((string) ($cred['email'] ?? $cred['username'] ?? $cred['login'] ?? 'unknown'));
                $key = sprintf('auth:login:fail:%s|%s', $identifier, $ip);
                if (!Cache::has($key)) {
                    Cache::put($key, 0, now()->addMinutes(15));
                }
                $attempts = (int) Cache::increment($key);

                if (!(config('dbquerylog.skip_user_id_zero') && (int)$userId === 0)) {
                    $logConn = (string) config('dbquerylog.log_connection', 'pgsql_oft');
                    DB::connection($logConn)->table('users_logs')->insert([
                        'user_id' => $userId,
                        'tarih'   => now(),
                        'sayfa'   => Str::limit((string) ($fullUrl ?: $path), 255, ''),
                        'eylem'   => 'LOGIN FAILED (attempt: ' . $attempts . ')',
                        'ip'      => $ip,
                        'komut'   => '-',
                        'request_id' => $reqId,
                        'method'     => $method,
                        'route'      => Str::limit((string) $path, 255, ''),
                        'user_agent' => Str::limit((string) ($userAgent ?: '-'), 255, ''),
                        'connection' => '-',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::debug('Failed login log skipped: ' . $e->getMessage());
            }
        });

        // Logout events
        Event::listen(Logout::class, function (Logout $event) {
            try {
                $user = $event->user;
                $userId = $user?->id ?? 0;
                $ip = Request::ip() ?: '127.0.0.1';
                $method = 'AUTH';
                $path = optional(request())->path() ?: 'auth/logout';
                $fullUrl = optional(request())->fullUrl();
                $userAgent = request()->header('User-Agent');
                $reqId = (string) Str::uuid();

                if (!(config('dbquerylog.skip_user_id_zero') && (int)$userId === 0)) {
                    $logConn = (string) config('dbquerylog.log_connection', 'pgsql_oft');
                    DB::connection($logConn)->table('users_logs')->insert([
                        'user_id' => $userId,
                        'tarih'   => now(),
                        'sayfa'   => Str::limit((string) ($fullUrl ?: $path), 255, ''),
                        'eylem'   => 'LOGOUT',
                        'ip'      => $ip,
                        'komut'   => '-',
                        'request_id' => $reqId,
                        'method'     => $method,
                        'route'      => Str::limit((string) $path, 255, ''),
                        'user_agent' => Str::limit((string) ($userAgent ?: '-'), 255, ''),
                        'connection' => '-',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::debug('Logout log skipped: ' . $e->getMessage());
            }
        });
    }
}
