<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_successfully_storing_a_user_with_image_and_status_attributes(): void
    {
        $this->withExceptionHandling();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => $this->faker->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);
        $user = User::query()->latest()->first();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'email',
                    'status',
                    'image',
                ],
            ])
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.email', $data['email'])
            ->assertJsonPath('data.status', $data['status'])
            ->assertJsonPath('data.image', url('storage/' . $user->image));

        $this->assertDatabaseHas('users', $data);

        Storage::assertExists($user->image);
    }

    public function test_successfully_storing_a_user_with_image_and_without_status_attributes(): void
    {
        $this->withExceptionHandling();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => null,
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);
        $user = User::query()->latest()->first();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'email',
                    'status',
                    'image',
                ],
            ])
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.email', $data['email'])
            ->assertJsonPath('data.status', null)
            ->assertJsonPath('data.image', url('storage/' . $user->image));

        $this->assertDatabaseHas('users', $data);

        Storage::assertExists($user->image);
    }

    public function test_successfully_storing_a_user_with_status_and_without_image_attributes(): void
    {
        $this->withExceptionHandling();

        $data = [
            'name' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => $this->faker->text(),
            'image' => null,
        ];

        $response = $this->post('/api/users/', $data);
        $user = User::query()->latest()->first();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'email',
                    'status',
                    'image',
                ],
            ])
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.email', $data['email'])
            ->assertJsonPath('data.status', $data['status'])
            ->assertJsonPath('data.image', null);

        $this->assertDatabaseHas('users', $data);
    }

    public function test_successfully_storing_a_user_without_status_and_image_attributes(): void
    {
        $this->withExceptionHandling();

        $data = [
            'name' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => null,
            'image' => null,
        ];

        $response = $this->post('/api/users/', $data);
        $user = User::query()->latest()->first();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'email',
                    'status',
                    'image',
                ],
            ])
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.email', $data['email'])
            ->assertJsonPath('data.status', null)
            ->assertJsonPath('data.image', null);

        $this->assertDatabaseHas('users', $data);
    }

    public function test_name_attribute_is_required_for_storing_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'email' => fake()->unique()->safeEmail(),
            'status' => fake()->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);

        $response->assertSessionHasErrors('name')
            ->assertRedirect();
    }

    public function test_name_attribute_is_string_for_storing_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => 1111,
            'email' => fake()->unique()->safeEmail(),
            'status' => fake()->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);

        $response->assertSessionHasErrors('name')
            ->assertRedirect();
    }

    public function test_email_attribute_is_required_for_storing_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => 'Test User',
            'status' => fake()->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);

        $response->assertSessionHasErrors('email')
            ->assertRedirect();
    }

    public function test_email_attribute_is_valid_email_for_storing_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'status' => fake()->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);

        $response->assertSessionHasErrors('email')
            ->assertRedirect();
    }

    public function test_email_attribute_is_unique_for_storing_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $existingUser = \App\Models\User::factory()->create();

        $data = [
            'name' => 'Test User',
            'email' => $existingUser->email,
            'status' => fake()->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);

        $response->assertSessionHasErrors('email')
            ->assertRedirect();
    }

    public function test_status_attribute_is_string_or_nullable(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'status' => null,
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);
        $response->assertRedirect();

        $data['status'] = 1234;
        $response = $this->post('/api/users/', $data);
        $response->assertSessionHasErrors('status')
            ->assertRedirect();
    }

    public function test_image_attribute_is_image_or_nullable(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.png');
        $data = [
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'status' => fake()->text(),
            'image' => $file,
        ];

        $response = $this->post('/api/users/', $data);
        $response->assertRedirect();

        $data['image'] = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $response = $this->post('/api/users/', $data);
        $response->assertSessionHasErrors('image')
            ->assertRedirect();
    }
}
