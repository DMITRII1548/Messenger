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

    public function test_successfully_destroing_a_user(): void
    {
        $this->withExceptionHandling();

        $user = User::factory()->create();

        $response = $this->delete("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJson([
                'destroyed' => true,
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_successfully_updating_a_user_with_image_and_status_attributes(): void
    {
        $this->withExceptionHandling();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => $this->faker->word(),
            'status' => $this->faker->text(),
            'image' => $file,
        ];

        $user = User::factory()->create();
        $response = $this->patch("/api/users/{$user->id}", $data);

        $this->assertNotNull($user);

        unset($data['image']);

        $user = $user->refresh();

        $response->assertOk()
            ->assertJsonStructure([
                'name',
                'email',
                'status',
                'image',
            ])
            ->assertJsonPath('name', $data['name'])
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('status', $data['status'])
            ->assertJsonPath('image', url('storage/' . $user->image));

        $this->assertDatabaseHas('users', $data);

        Storage::assertExists($user->image);
    }

    public function test_successfully_updating_a_user_with_image_and_without_status_attributes(): void
    {
        $this->withExceptionHandling();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $data = [
            'name' => $this->faker->word(),
            'status' => null,
            'image' => $file,
        ];

        $user = User::factory()->create();
        $response = $this->patch("/api/users/{$user->id}", $data);

        $user = $user->refresh();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertOk()
            ->assertJsonStructure([
                'name',
                'email',
                'status',
                'image',
            ])
            ->assertJsonPath('name', $data['name'])
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('status', null)
            ->assertJsonPath('image', url('storage/' . $user->image));

        $this->assertDatabaseHas('users', $data);

        Storage::assertExists($user->image);
    }

    public function test_successfully_updating_a_user_with_status_and_without_image_attributes(): void
    {
        $this->withExceptionHandling();

        $data = [
            'name' => $this->faker->word(),
            'status' => $this->faker->text(),
            'image' => null,
        ];

        $user = User::factory()->create();
        $response = $this->patch("/api/users/{$user->id}", $data);

        $user = $user->refresh();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertOk()
            ->assertJsonStructure([
                'name',
                'email',
                'status',
                'image',
            ])
            ->assertJsonPath('name', $data['name'])
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('status', $data['status'])
            ->assertJsonPath('image', null);

        $this->assertDatabaseHas('users', $data);
    }

    public function test_successfully_updating_a_user_without_status_and_image_attributes(): void
    {
        $this->withExceptionHandling();

        $data = [
            'name' => $this->faker->word(),
            'status' => null,
            'image' => null,
        ];

        $user = User::factory()->create();
        $response = $this->patch("/api/users/{$user->id}", $data);

        $user = $user->refresh();

        $this->assertNotNull($user);

        unset($data['image']);

        $response->assertOk()
            ->assertJsonStructure([
                'name',
                'email',
                'status',
                'image',
            ])
            ->assertJsonPath('name', $data['name'])
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('status', null)
            ->assertJsonPath('image', null);

        $this->assertDatabaseHas('users', $data);
    }

    public function test_name_attribute_is_required_for_updating_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $user = User::factory()->create();

        $data = [
            'name' => null,
            'status' => $this->faker->text(),
            'image' => $file,
        ];

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertSessionHasErrors('name')
            ->assertRedirect();
    }

    public function test_name_attribute_is_string_for_updating_a_user(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.png');

        $user = User::factory()->create();

        $data = [
            'name' => 111,
            'status' => $this->faker->text(),
            'image' => $file,
        ];

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertSessionHasErrors('name')
            ->assertRedirect();
    }

    public function test_name_attribute_min_length(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'A', // less than min length
            'status' => 'active',
        ];

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertSessionHasErrors('name')
            ->assertRedirect();

        // Valid case with sufficient length
        $data['name'] = 'AB';

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertOk();
    }

    public function test_status_attribute_can_be_nullable(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Valid Name',
            // 'status' is not set, should be nullable
            'image' => null,
        ];

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertOk();
    }

    public function test_image_attribute_is_nullable_and_must_be_an_image(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Valid Name',
            'status' => 'active',
            'image' => 'not-an-image', // invalid case: non-image file
        ];

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertSessionHasErrors('image')
            ->assertRedirect();

        // Valid case with image
        $file = UploadedFile::fake()->image('valid-image.jpg');
        $data['image'] = $file;

        $response = $this->patch("/api/users/{$user->id}", $data);

        $response->assertOk();
    }

    public function test_successfully_getting_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'name',
                'email',
                'status',
                'image',
            ])
            ->assertJsonPath('name', $user->name)
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('status', $user->status)
            ->assertJsonPath('image', $user->image);
    }

}
