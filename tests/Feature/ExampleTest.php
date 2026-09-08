<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests see the landing page on home', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Leave Management System');
});

test('authenticated users are redirected from home to their role dashboard', function (string $role, string $dashboardRoute) {
    $user = User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route($dashboardRoute));
})->with([
    'admin' => ['admin', 'admin.dashboard'],
    'department head' => ['department_head', 'department-head.dashboard'],
    'staff' => ['staff', 'staff.dashboard'],
]);

test('authenticated users visiting login are sent to their dashboard instead of looping', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('admin.dashboard'));
});
