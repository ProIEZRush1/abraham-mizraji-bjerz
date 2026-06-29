<?php

namespace App\Http\Controllers;

use App\Services\ApiTokensService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Web (panel) controller for the API REST + Tokens capability.
 *
 * Renders the Spanish token-management page and handles creating / revoking
 * Sanctum personal access tokens. The plain-text token is flashed to the
 * session exactly once, immediately after creation.
 */
class ApiTokensController extends Controller
{
    public function __construct(private readonly ApiTokensService $tokens) {}

    public function index(Request $request): Response
    {
        $tokens = $this->tokens->listFor($request->user())->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities ?: ['*'],
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'created_at' => $token->created_at?->toIso8601String(),
        ]);

        return Inertia::render('Api/Index', [
            'tokens' => $tokens,
            'plainTextToken' => $request->session()->get('plainTextToken'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'string', 'max:1000'],
        ]);

        $newToken = $this->tokens->createTokenFor(
            $request->user(),
            $validated['name'],
            $this->parseAbilities($validated['abilities'] ?? null),
        );

        return back()->with('plainTextToken', $newToken->plainTextToken);
    }

    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        $this->tokens->revoke($request->user(), $tokenId);

        return back()->with('status', 'token-revocado');
    }

    /**
     * Turn the comma-separated abilities input into an array, defaulting to
     * full access ("*") when nothing meaningful was provided.
     *
     * @return array<int, string>
     */
    private function parseAbilities(?string $raw): array
    {
        if (! $raw) {
            return ['*'];
        }

        $abilities = collect(explode(',', $raw))
            ->map(fn ($ability) => trim($ability))
            ->filter()
            ->values()
            ->all();

        return $abilities ?: ['*'];
    }
}
