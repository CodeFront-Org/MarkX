<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMultiRoleTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_have_multiple_roles()
    {
        $user = User::factory()->create([
            'role' => 'rfq_processor',
            'roles' => ['rfq_processor', 'rfq_approver', 'lpo_admin'],
        ]);

        $this->assertTrue($user->hasRole('rfq_processor'));
        $this->assertTrue($user->hasRole('rfq_approver'));
        $this->assertTrue($user->hasRole('lpo_admin'));
        $this->assertTrue($user->isRfqApprover());
        $this->assertTrue($user->isLpoAdmin());
        $this->assertTrue($user->isRfqProcessor());
    }

    public function test_with_role_scope_finds_users()
    {
        $user = User::factory()->create([
            'role' => 'rfq_processor',
            'roles' => ['rfq_processor', 'rfq_approver'],
        ]);

        $foundApprovers = User::withRole('rfq_approver')->pluck('id')->toArray();
        $this->assertContains($user->id, $foundApprovers);
    }

    public function test_updating_user_roles_via_user_management()
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
            'roles' => ['superadmin'],
        ]);

        $targetUser = User::factory()->create([
            'role' => 'rfq_processor',
            'roles' => ['rfq_processor'],
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $targetUser->id), [
            'name' => 'Updated User',
            'email' => $targetUser->email,
            'roles' => ['rfq_approver', 'lpo_admin'],
        ]);

        $response->assertRedirect(route('user-management'));

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('rfq_approver'));
        $this->assertTrue($targetUser->hasRole('lpo_admin'));
        $this->assertFalse($targetUser->hasRole('rfq_processor'));
    }
}
