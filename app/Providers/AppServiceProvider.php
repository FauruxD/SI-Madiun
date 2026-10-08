<?php

namespace App\Providers;

use App\Models\EProposal;
use App\Notifications\EProposalStatusChanged;
use App\Policies\EProposalPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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
        Gate::policy(EProposal::class, EProposalPolicy::class);
        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $view->with('unreadProposalNotifications', auth()->user()?->unreadNotifications()
                ->where('type', EProposalStatusChanged::class)->count() ?? 0);
        });
        Password::defaults(fn (): Password => Password::min(10)->mixedCase()->numbers());

        RateLimiter::for('login', function (Request $request): Limit {
            $identifier = Str::lower((string) $request->input('identifier'));

            return Limit::perMinute(5)->by($identifier.'|'.$request->ip());
        });
    }
}
