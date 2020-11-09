<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Recipe;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class RecipesTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @param string $username
     * @param string $password
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function createAuthenticatedClient(Client $client, string $username, string $password): void
    {
        $body = json_encode([
            'email' => $username,
            'password' => $password,
        ]);

        $response = $client->request('POST', '/authentication_token', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ]);

        $data = $response->toArray();
        $client->setDefaultOptions([
            'auth_bearer' => $data['token'],
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCollection(): void
    {
        $client = static::createClient();
        $this->createAuthenticatedClient($client, 'api', 'api');
        $client->request('GET', '/api/recipes');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceCollectionJsonSchema(Recipe::class);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateRecipe(): void
    {
        $client = static::createClient();
        $this->createAuthenticatedClient($client, 'api', 'api');
        $response = $client->request('POST', '/api/recipes', ['json' => [
            'name' => 'Recipe from testing',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@type' => 'Recipe',
        ]);
        $this->assertRegExp('~^/api/recipes/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Recipe::class);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testUpdateBook(): void
    {
        $client = static::createClient();
        $this->createAuthenticatedClient($client, 'api', 'api');
        $iri = $this->findIriBy(Recipe::class, ['name' => 'test-fixtures']);

        $client->request('PUT', $iri, ['json' => [
            'name' => 'updated name',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'updated name',
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $this->createAuthenticatedClient($client, 'api', 'api');
        $iri = $this->findIriBy(Recipe::class, ['name' => 'updated name']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::$container->get('doctrine')->getRepository(Recipe::class)->findOneBy(['name' => 'updated name'])
        );
    }
}
