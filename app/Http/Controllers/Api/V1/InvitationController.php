<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\InvitationAcceptRequest;
use App\Http\Controllers\Controller;
use App\Services\InvitationService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function accept(InvitationAcceptRequest $request, string $token): JsonResponse
    {
        $result = $this->invitationService->accept(
            $token,
            $request->validated()
        );

        return response()->json([
            'message' => 'Invitación aceptada correctamente.',
            'data' => $result,
        ], 201);
    }

    public function cancel(int $invitationId, \Illuminate\Http\Request $request): JsonResponse
    {
        $invitation = $this->invitationService->cancel(
            $invitationId,
            $request->user()
        );

        return response()->json([
            'message' => 'Invitación cancelada correctamente.',
            'data' => $invitation,
        ]);
    }

}
