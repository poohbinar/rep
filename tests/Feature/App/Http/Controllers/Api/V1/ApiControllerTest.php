<?php

namespace Tests\Feature\App\Http\Controllers\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_not_existant_endpoint(): void
    {
        $url = '/api/v1/non-existent-endpoint';
        $response = $this->getJson($url);

        $response->assertJson([
            'success' => false,
            'message' => sprintf('Method %s Not Found', $url),
            'code' => Response::HTTP_NOT_FOUND,
        ]);
    }

    #[Test]
    public function test_method_not_allowed(): void
    {
        $response = $this->putJson(route('api.v1.auth'));

        $response->assertJson([
            'success' => false,
            'message' => 'The PUT method is not supported for route api/v1/auth. Supported methods: POST.',
            'allowed_methods' => 'POST',
            'code' => Response::HTTP_METHOD_NOT_ALLOWED,
        ]);
    }
}
