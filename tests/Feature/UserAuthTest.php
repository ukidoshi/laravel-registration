<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration with valid data.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'name' => $this->faker->name(),
        ];

        $response = $this->postJson('/api/registration', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'gender',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'gender' => $userData['gender'],
            'name' => $userData['name'],
        ]);
    }

    /**
     * Test user registration with invalid data.
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        // Missing required fields
        $response = $this->postJson('/api/registration', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password', 'gender']);

        // Invalid email format
        $response = $this->postJson('/api/registration', [
            'email' => 'invalid-email',
            'password' => 'password123',
            'gender' => 'male',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Invalid gender value
        $response = $this->postJson('/api/registration', [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'gender' => 'invalid-gender',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    /**
     * Test user cannot register with an email that already exists.
     */
    public function test_user_cannot_register_with_existing_email(): void
    {
        // Create a user first
        $existingUser = User::factory()->create();

        // Try to register with the same email
        $response = $this->postJson('/api/registration', [
            'email' => $existingUser->email,
            'password' => 'password123',
            'gender' => 'male',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authenticated user can access profile.
     */
    public function test_authenticated_user_can_access_profile(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Access profile as authenticated user
        $response = $this->actingAs($user)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'gender',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    /**
     * Test unauthenticated user cannot access profile.
     */
    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    /**
     * Test registration and then accessing profile with the token.
     */
    public function test_registration_and_profile_access_flow(): void
    {
        // Register a new user
        $userData = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'name' => $this->faker->name(),
        ];

        $registerResponse = $this->postJson('/api/registration', $userData);
        $registerResponse->assertStatus(201);

        // Extract the token from the response
        $token = $registerResponse->json('token');
        $this->assertNotNull($token);

        // Access profile with the token
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.email', $userData['email'])
            ->assertJsonPath('data.gender', $userData['gender'])
            ->assertJsonPath('data.name', $userData['name']);
    }
}
