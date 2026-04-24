<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvitationService
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    public function create(int $companyId, array $data, User $actor): UserInvitation
    {
        $this->ensureActorHasCompanyAccess($companyId, $actor);

        $role = Role::query()
            ->where('role_id', $data['role_id'])
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            })
            ->first();

        if (! $role) {
            throw new NotFoundHttpException('Rol no encontrado para esta empresa.');
        }

        $pendingInvitation = UserInvitation::query()
            ->where('company_id', $companyId)
            ->where('email', $data['email'])
            ->where('status', 'PENDING')
            ->first();

        if ($pendingInvitation) {
            throw new ConflictHttpException('Ya existe una invitación pendiente para este correo en la empresa.');
        }

        $invitation = UserInvitation::create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? null,
            'role_id' => $role->role_id,
            'email' => $data['email'],
            'invitation_token' => Str::uuid()->toString(),
            'invited_by_user_id' => $actor->user_id,
            'status' => 'PENDING',
            'expires_at' => now()->addDays((int) ($data['expires_in_days'] ?? 7)),
            'notes' => $data['notes'] ?? null,
        ]);

        return $invitation->fresh([
            'company:company_id,trade_name,legal_name',
            'role:role_id,name,code',
            'branch:branch_id,name',
            'invitedBy:user_id,first_name,last_name,email',
        ]);
    }

    public function findByToken(string $token): UserInvitation
    {
        $invitation = UserInvitation::query()
            ->with([
                'company:company_id,trade_name,legal_name',
                'role:role_id,name,code',
                'branch:branch_id,name',
                'invitedBy:user_id,first_name,last_name,email',
            ])
            ->where('invitation_token', $token)
            ->first();

        if (! $invitation) {
            throw new NotFoundHttpException('Invitación no encontrada.');
        }

        if ($invitation->status === 'CANCELLED') {
            throw new ConflictHttpException('La invitación fue cancelada.');
        }

        if ($invitation->status === 'ACCEPTED') {
            throw new ConflictHttpException('La invitación ya fue aceptada.');
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            throw new ConflictHttpException('La invitación ha expirado.');
        }

        return $invitation;
    }

    private function ensureActorHasCompanyAccess(int $companyId, User $actor): void
    {
        if (! $this->authorizationService->userHasCompanyAccess($actor, $companyId)) {
            throw new NotFoundHttpException('No tienes acceso activo a esta empresa.');
        }
    }
}
