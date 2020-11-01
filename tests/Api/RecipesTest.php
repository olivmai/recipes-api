<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
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
     * @throws TransportExceptionInterface
     */
    public function testGetCollection(): void
    {
        static::createClient()->request('GET', '/api/recipes');

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
        $response = static::createClient()->request('POST', '/api/recipes', ['json' => [
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
     * @throws TransportExceptionInterface
     */
    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Recipe::class, ['name' => 'test-fixtures']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::$container->get('doctrine')->getRepository(Recipe::class)->findOneBy(['name' => 'test-fixtures'])
        );
    }
}
