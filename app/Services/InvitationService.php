<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function accept(string $token, array $data): array
    {
        $invitation = $this->findByToken($token);

        return DB::transaction(function () use ($invitation, $data) {
            $user = User::query()
                ->where('email', $invitation->email)
                ->first();

            if (! $user) {
                $user = User::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $invitation->email,
                    'password_hash' => Hash::make($data['password']),
                    'phone' => $data['phone'] ?? null,
                    'status' => 'ACTIVE',
                ]);
            }

            $existingAccess = \App\Models\UserCompanyRole::query()
                ->where('user_id', $user->user_id)
                ->where('company_id', $invitation->company_id)
                ->first();

            if ($existingAccess) {
                throw new ConflictHttpException('El usuario ya tiene acceso a esta empresa.');
            }

            $access = \App\Models\UserCompanyRole::create([
                'user_id' => $user->user_id,
                'company_id' => $invitation->company_id,
                'branch_id' => $invitation->branch_id,
                'role_id' => $invitation->role_id,
                'assigned_by_user_id' => $invitation->invited_by_user_id,
                'is_default_company' => false,
                'status' => 'ACTIVE',
                'assigned_at' => now(),
            ]);

            $invitation->update([
                'status' => 'ACCEPTED',
                'accepted_at' => now(),
            ]);

            return [
                'user' => $user->fresh(),
                'access' => $access->fresh([
                    'company:company_id,trade_name,legal_name',
                    'role:role_id,name,code',
                    'branch:branch_id,name',
                ]),
                'invitation' => $invitation->fresh(),
            ];
        });
    }

    public function cancel(int $invitationId, User $actor): UserInvitation
    {
        $invitation = UserInvitation::query()
            ->where('invitation_id', $invitationId)
            ->first();

        if (! $invitation) {
            throw new NotFoundHttpException('Invitación no encontrada.');
        }

        $this->ensureActorHasCompanyAccess($invitation->company_id, $actor);

        if ($invitation->status === 'ACCEPTED') {
            throw new ConflictHttpException('No puedes cancelar una invitación ya aceptada.');
        }

        if ($invitation->status === 'CANCELLED') {
            throw new ConflictHttpException('La invitación ya fue cancelada.');
        }

        $invitation->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
        ]);

        return $invitation->fresh([
            'company:company_id,trade_name,legal_name',
            'role:role_id,name,code',
            'branch:branch_id,name',
            'invitedBy:user_id,first_name,last_name,email',
        ]);
    }

}
