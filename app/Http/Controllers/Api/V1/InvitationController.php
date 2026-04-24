<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {
    }

    public function show(string $token): JsonResponse
    {
        $invitation = $this->invitationService->findByToken($token);

        return response()->json([
            'message' => 'Invitación obtenida correctamente.',
            'data' => $invitation,
        ]);
    }
}
