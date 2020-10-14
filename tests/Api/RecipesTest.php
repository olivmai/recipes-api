<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class RecipesTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/recipes');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }
}
