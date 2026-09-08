<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        LeaveRequest::class => LeaveRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
