<?php


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Scheduled;
use Illuminate\Console\Scheduling\Schedule;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'sanctum.auth' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'cors' => \Illuminate\Http\Middleware\HandleCors::class, // CORS ミドルウェアを追加
        ]);

        // グローバルミドルウェアにCORSを追加
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->append(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
    })
    // CSRF除外
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // UTCにしてしまっているため、以下の処理1時間おきに実行
        // ->hourlyAt(0)で毎時0分を指定
        // ->when()で3時間おきの条件を追加
        // 修正範囲を確認後、日本時間JSTに修正予定
        $schedule->command('tasks:check-due-dates')
            ->hourlyAt(0)
            ->when(function () {
                $hour = (int) now()->format('H');
                return $hour % 1 === 0;
            });
            
        // 古い通知のクリーンアップ（UTC 03:00 = JST 12:00）
        $schedule->command('notifications:cleanup')->dailyAt('03:00');
    })
    ->create();
