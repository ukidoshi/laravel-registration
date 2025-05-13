<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserAuthService $userAuthService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userAuthService = new UserAuthService();
    }

    /**
     * Test user registration through the service.
     */
    public function test_register_creates_user_and_returns_token(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'gender' => 'male',
            'name' => 'Test User',
        ];

        $result = $this->userAuthService->register($userData);

        // Check that the result has the expected structure
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertNotEmpty($result['token']);

        // Check that the user was created with the correct data
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($userData['email'], $result['user']->email);
        $this->assertEquals($userData['gender'], $result['user']->gender);
        $this->assertEquals($userData['name'], $result['user']->name);

        // Check that the user exists in the database
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'gender' => $userData['gender'],
            'name' => $userData['name'],
        ]);
    }

    /**
     * Test getUserProfile returns the correct user.
     */
    public function test_get_user_profile_returns_user(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Get the user profile
        $result = $this->userAuthService->getUserProfile($user);

        // Check that the result is the user
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->email, $result->email);
    }

    /**
     * Test registration with minimal required data.
     */
    public function test_register_with_minimal_data(): void
    {
        $userData = [
            'email' => 'minimal@example.com',
            'password' => 'password123',
            'gender' => 'female',
            // No name provided
        ];

        $result = $this->userAuthService->register($userData);

        // Check that the user was created
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($userData['email'], $result['user']->email);
        $this->assertEquals($userData['gender'], $result['user']->gender);
        $this->assertNull($result['user']->name);

        // Check that the user exists in the database
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'gender' => $userData['gender'],
        ]);
    }
}
