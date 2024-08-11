<?php

namespace App\Tests;

class LoginRegisterTest extends ApiTestCase
{
    public function testRegisterUser(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@test.test',
                'password' => 'password',
                'username' => 'user',
            ])
        );
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(400);
        $this->assertJson($response->getContent());
        $this->assertSame(
            json_encode(
                [
                    'errors' => [
                        ['message' => 'Username with given email or login already exists'],
                    ],
                ]
            ),
            $response->getContent()
        );
    }

    public function testLoginUser(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@test.test',
                'password' => 'test',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('token', $data, 'The response does not contain a token.');
    }
}
