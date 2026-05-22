<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_book()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Titre test',
            'author' => 'Test auteur',
            'summary' => 'Ceci est un résumé',
            'isbn' => '1234567890123',
        ];

        $response = $this->actingAs($user)->postJson('/api/books', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => $payload['title'],
            'author' => $payload['author'],
            'isbn' => $payload['isbn'],
        ]);
    }

    public function test_does_not_create_book()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'ab',
            'author' => 'Au',
            'summary' => 'trop court',
            'isbn' => '123',
        ];

        $response = $this->actingAs($user)->postJson('/api/books', $payload);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('books', [
            'title' => $payload['isbn'],
        ]);
    }

    public function test_does_not_create_book_not_authenticated()
    {
        $payload = [
            'title' => 'Titre test',
            'author' => 'Test auteur',
            'summary' => 'Ceci est un résumé',
            'isbn' => '1234567890123',
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertStatus(401);

        $this->assertDatabaseMissing('books', [
            'isbn' => $payload['isbn'],
        ]);
    }
}
