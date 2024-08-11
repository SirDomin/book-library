<?php

namespace App\Tests\App\Tests;

use App\Entity\Book;
use App\Tests\ApiTestCase;

class BookTest extends ApiTestCase
{
    public function testCreateBook(): void
    {
        $this->loginAs('test@test.test', 'test');

        $imagePath = self::$kernel->getContainer()->getParameter('upload_directory').'/default.jpg';
        $imageContent = file_get_contents($imagePath);
        $base64Image = base64_encode($imageContent);
        $fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);

        $this->client->request(
            'POST',
            '/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Test Book',
                'author' => 'John Doe',
                'description' => 'A test book description.',
                'publishYear' => 2023,
                'isbn' => '978-3-16-148410-0',
                'image' => $base64Image,
                'extension' => $fileExtension,
            ])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(201);

        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Book created successfully', $data['message']);

        $this->assertNotNull($data['book']['id']);

        $uploadedFilePath = self::$kernel->getContainer()->getParameter('upload_directory').'/'.$data['book']['image'];
        $this->assertFileExists($uploadedFilePath);

        @unlink($uploadedFilePath);
    }

    public function testGetBooksWithoutSearch(): void
    {
        $this->loginAs('test@test.test', 'test');

        $this->client->request('GET', '/api/books?page=1&limit=2');

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }

    public function testGetBooksWithSearch(): void
    {
        $this->loginAs('test@test.test', 'test');

        $this->client->request('GET', '/api/books?page=1&limit=10&search=Another');

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertSame('Another Book', $data['data'][0]['title']);
    }

    public function testGetBooksInvalidPage(): void
    {
        $this->loginAs('test@test.test', 'test');
        $this->client->request('GET', '/api/books?page=-1&limit=2');

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Page must be greater than 0', $data['error']);
    }

    public function testGetBooksInvalidLimit(): void
    {
        $this->loginAs('test@test.test', 'test');
        $this->client->request('GET', '/api/books?page=1&limit=0');

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Limit must be greater than 0', $data['error']);
    }

    public function testUpdateBook(): void
    {
        $this->loginAs('test@test.test', 'test');
        $book = $this->entityManager->getRepository(Book::class)->find(1);

        $this->client->request('PUT', '/api/books/'.$book->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Updated Book Title',
            'author' => 'Updated Author',
            'description' => 'Updated Description',
            'publishYear' => 2024,
            'isbn' => '978-3-16-148413-1',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Updated Book Title', $data['title']);
        $this->assertSame('Updated Author', $data['author']);
    }

    public function testDeleteBook(): void
    {
        $this->loginAs('test@test.test', 'test');
        $book = $this->entityManager->getRepository(Book::class)->find(1);

        $this->client->request('DELETE', '/api/books/'.$book->getId());

        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', '/api/books/'.$book->getId());

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $this->assertResponseStatusCodeSame(404);
    }
}
