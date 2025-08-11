<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

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

                    // write to MSSQL users_logs via configured connection
                    $logConn = (string) config('dbquerylog.log_connection', 'sqlsrv');
                    DB::connection($logConn)->table('users_logs')->insert([
                        'user_id' => $userId,
                        'tarih'   => now(),
                        'sayfa'   => Str::limit((string) ($fullUrl ?: $path), 255, ''),
                        'eylem'   => Str::limit(sprintf('SQL %s %s [%s] %s %s', $method, $path, $connectionName ?? '-', ($routeName ?: '-'), $reqId), 255, ''),
                        'ip'      => $ip,
                        'komut'   => Str::limit(sprintf('%s /* %s | %s | %s | %s */', $rendered, ($routeName ?: '-'), ($userAgent ?: '-'), ($connectionName ?: '-'), $reqId), $maxLen, ''),
                    ]);
                } catch (\Throwable $e) {
                    // Never break the request for logging; write to app log instead
                    Log::debug('DB query log skipped: ' . $e->getMessage());
                }
            });
        }
    }
}
