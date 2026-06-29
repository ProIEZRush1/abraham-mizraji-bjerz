<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sample versioned (/api/v1) REST resource controller for the API capability.
 *
 * This is a reference/template controller: it demonstrates the recommended
 * shape for a Sanctum-protected, token-authenticated JSON resource using the
 * project's standard envelope:
 *
 *   success: { "data": ..., "error": null }
 *   error:   { "data": null, "error": { "message": "...", "code": "..." } }
 *
 * It returns in-memory demo data so the endpoint works out of the box with
 * zero database tables. Replace the demo store with a real Eloquent model and
 * a Form Request / API Resource when wiring an actual resource.
 */
class ApiV1ExampleController extends Controller
{
    /**
     * Demo records used as a stand-in for a real Eloquent model.
     *
     * @return array<int, array<string, mixed>>
     */
    private function demoItems(): array
    {
        return [
            ['id' => 1, 'nombre' => 'Elemento de ejemplo A', 'activo' => true],
            ['id' => 2, 'nombre' => 'Elemento de ejemplo B', 'activo' => false],
        ];
    }

    /**
     * GET /api/v1/example
     */
    public function index(Request $request): JsonResponse
    {
        return $this->ok([
            'items' => $this->demoItems(),
            'authenticated_as' => $request->user()?->only(['id', 'name', 'email']),
            'token_abilities' => $request->user()?->currentAccessToken()?->abilities ?? [],
        ]);
    }

    /**
     * POST /api/v1/example
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        return $this->ok([
            'id' => 99,
            'nombre' => $validated['nombre'],
            'activo' => true,
        ], 201);
    }

    /**
     * GET /api/v1/example/{example}
     */
    public function show(Request $request, string $example): JsonResponse
    {
        $item = collect($this->demoItems())->firstWhere('id', (int) $example);

        if (! $item) {
            return $this->fail('Recurso no encontrado.', 'not_found', 404);
        }

        return $this->ok($item);
    }

    /**
     * PUT/PATCH /api/v1/example/{example}
     */
    public function update(Request $request, string $example): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        return $this->ok([
            'id' => (int) $example,
            ...$validated,
        ]);
    }

    /**
     * DELETE /api/v1/example/{example}
     */
    public function destroy(Request $request, string $example): JsonResponse
    {
        return $this->ok(['id' => (int) $example, 'deleted' => true]);
    }

    /**
     * Build a success response in the standard envelope.
     */
    private function ok(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data, 'error' => null], $status);
    }

    /**
     * Build an error response in the standard envelope.
     */
    private function fail(string $message, string $code, int $status): JsonResponse
    {
        return response()->json([
            'data' => null,
            'error' => ['message' => $message, 'code' => $code],
        ], $status);
    }
}
