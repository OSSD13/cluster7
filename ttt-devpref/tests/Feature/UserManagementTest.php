<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AuthorizedEmail;
use Illuminate\Support\Carbon;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test that admin can view user management page.
     */
    public function test_admin_can_view_user_management_page(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Act as the admin
        $this->actingAs($admin);
        
        // Visit the user management page
        $response = $this->get(route('users.index'));
        
        // Assert successful response
        $response->assertStatus(200);
        
        // Assert the page contains the user management section
        $response->assertSee('User Management');
        
        // Assert the page contains the authorized emails section
        $response->assertSee('Authorized Emails');
        
        // Assert the page contains the Access Control button instead of Add New User
        $response->assertSee('Access Control');
        $response->assertDontSee('Add New User');
    }
    
    /**
     * Test that admin can edit a user.
     */
    public function test_admin_can_edit_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create a user to edit
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'dev'
        ]);
        
        // Act as the admin
        $this->actingAs($admin);
        
        // Updated user data
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'tester',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];
        
        // Submit request to update user
        $response = $this->put(route('users.update', $user->id), $updatedData);
        
        // Assert redirect to users index page
        $response->assertRedirect(route('users.index'));
        
        // Assert the user was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'tester'
        ]);
    }
    
    /**
     * Test that admin can delete a user.
     */
    public function test_admin_can_delete_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create a user to delete
        $user = User::factory()->create([
            'role' => 'dev'
        ]);
        
        // Act as the admin
        $this->actingAs($admin);
        
        // Submit request to delete user
        $response = $this->delete(route('users.destroy', $user->id));
        
        // Assert the user was deleted
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
    
    /**
     * Test that admin cannot delete themselves.
     */
    public function test_admin_cannot_delete_themselves(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Act as the admin
        $this->actingAs($admin);
        
        // Submit request to delete the admin
        $response = $this->delete(route('users.destroy', $admin->id));
        
        // Assert the admin still exists in the database
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    /**
     * Test that authorized email is updated when user email changes.
     */
    public function test_authorized_email_is_updated_when_user_email_changes(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create a user whose email will be changed
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'dev'
        ]);
        
        // Create an authorized email record for this user (marked as used)
        $authorizedEmail = AuthorizedEmail::create([
            'email' => 'original@example.com',
            'notes' => 'Test user',
            'is_used' => true,
            'registered_at' => Carbon::now(),
        ]);
        
        // Act as the admin
        $this->actingAs($admin);
        
        // Updated user data with new email
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'tester'
        ];
        
        // Submit request to update user
        $response = $this->put(route('users.update', $user->id), $updatedData);
        
        // Assert redirect to users index page
        $response->assertRedirect(route('users.index'));
        
        // Assert the user was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'tester'
        ]);
        
        // Assert the authorized email was also updated
        $this->assertDatabaseHas('authorized_emails', [
            'id' => $authorizedEmail->id,
            'email' => 'updated@example.com',
            'is_used' => true,
        ]);
        
        // Assert the old email no longer exists in authorized_emails
        $this->assertDatabaseMissing('authorized_emails', [
            'email' => 'original@example.com',
        ]);
    }
    
    /**
     * Test that authorized email status is reset when user is deleted.
     */
    public function test_authorized_email_status_is_reset_when_user_is_deleted(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        
        // Create a user to delete
        $user = User::factory()->create([
            'email' => 'delete_me@example.com',
            'role' => 'dev'
        ]);
        
        // Create an authorized email record for this user (marked as used)
        $authorizedEmail = AuthorizedEmail::create([
            'email' => 'delete_me@example.com',
            'notes' => 'Test user for deletion',
            'is_used' => true,
            'registered_at' => Carbon::now(),
        ]);
        
        // Act as the admin
        $this->actingAs($admin);
        
        // Submit request to delete user
        $response = $this->delete(route('users.destroy', $user->id));
        
        // Assert redirect to users index page
        $response->assertRedirect(route('users.index'));
        
        // Assert the user was deleted
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
        
        // Assert the authorized email status was reset
        $this->assertDatabaseHas('authorized_emails', [
            'id' => $authorizedEmail->id,
            'email' => 'delete_me@example.com',
            'is_used' => false,
            'registered_at' => null,
        ]);
    }
}
