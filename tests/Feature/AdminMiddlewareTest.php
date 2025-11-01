<?php

use App\Models\User;

test('admin users can access admin routes', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/settings')
        ->assertSuccessful();
});

test('non-admin users cannot access admin routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/settings')
        ->assertForbidden();
});

test('guests cannot access admin routes', function () {
    $this->get('/settings')
        ->assertRedirect(route('login'));
});

test('admin users can access admin conversations page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/conversations')
        ->assertSuccessful();
});

test('non-admin users cannot access admin conversations page', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/conversations')
        ->assertForbidden();
});
