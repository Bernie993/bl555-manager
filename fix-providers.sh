#!/bin/bash

echo "=== Fixing Laravel Providers Issue ==="

# 1. Check if AppServiceProvider exists
if [ ! -f "app/Providers/AppServiceProvider.php" ]; then
    echo "❌ AppServiceProvider.php not found! Creating it..."
    
    # Create the directory if it doesn't exist
    mkdir -p app/Providers
    
    # Create AppServiceProvider.php
    cat > app/Providers/AppServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        //
    }
}
EOF
    echo "✅ AppServiceProvider.php created successfully!"
else
    echo "✅ AppServiceProvider.php exists"
fi

# 2. Check if AuthServiceProvider exists
if [ ! -f "app/Providers/AuthServiceProvider.php" ]; then
    echo "❌ AuthServiceProvider.php not found! Creating it..."
    
    # Create AuthServiceProvider.php
    cat > app/Providers/AuthServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for permissions
        Gate::before(function ($user, $ability) {
            // Check if user has the permission
            return $user->hasPermission($ability) ? true : null;
        });
    }
}
EOF
    echo "✅ AuthServiceProvider.php created successfully!"
else
    echo "✅ AuthServiceProvider.php exists"
fi

# 3. Clear all caches
echo "🧹 Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Update bootstrap/app.php to register providers explicitly
echo "📝 Updating bootstrap/app.php..."
cat > bootstrap/app.php << 'EOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
EOF

# 5. Regenerate autoload
echo "🔄 Regenerating composer autoload..."
composer dump-autoload --optimize

# 6. Try to cache again
echo "📦 Attempting to cache views..."
php artisan view:cache
php artisan config:cache
php artisan route:cache

echo "=== Fix completed! ==="
