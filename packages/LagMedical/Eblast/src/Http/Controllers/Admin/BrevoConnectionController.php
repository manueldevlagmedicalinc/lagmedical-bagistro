<?php

namespace LagMedical\Eblast\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LagMedical\Eblast\Services\EblastManager;
use Throwable;

class BrevoConnectionController
{
    public function __construct(protected EblastManager $eblastManager) {}

    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => ['required', 'string', 'max:255'],
            'list_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->eblastManager->testConnection(
                $validated['api_key'],
                (int) $validated['list_id'],
            );

            return response()->json([
                'message' => 'Conexión correcta. La lista está disponible en Brevo.',
                ...$result,
            ]);
        } catch (Throwable $exception) {
            Log::error('Brevo connection test failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => $exception->getMessage() ?: 'Brevo rechazó la conexión o la lista indicada no existe.',
            ], 422);
        }
    }
}
