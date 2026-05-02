<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ClientUserScopeService
{
    private function explicitClientIds(string $pivotTable, string $ownerColumn, int $ownerId): array
    {
        if ($ownerId <= 0) {
            return [];
        }

        return DB::table($pivotTable)
            ->where($ownerColumn, $ownerId)
            ->pluck('client_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    public function explicitClientIdsForUser(int $clientUserId): array
    {
        return $this->explicitClientIds('client_user_clients', 'client_user_id', $clientUserId);
    }

    public function explicitClientIdsForAccountant(int $accountantUserId): array
    {
        return $this->explicitClientIds('accountant_user_clients', 'accountant_user_id', $accountantUserId);
    }

    public function visibleClientIdsForUser(int $clientUserId): array
    {
        return $this->expandClientIds($this->explicitClientIdsForUser($clientUserId));
    }

    public function visibleClientIdsForAccountant(int $accountantUserId): array
    {
        return $this->expandClientIds($this->explicitClientIdsForAccountant($accountantUserId));
    }

    public function visibleClientIdsForActor(?string $role, int $actorId): ?array
    {
        return match ($role) {
            'client_user' => $this->visibleClientIdsForUser($actorId),
            'accountant_user' => $this->visibleClientIdsForAccountant($actorId),
            default => null,
        };
    }

    public function expandClientIds(array $clientIds): array
    {
        $queue = [];
        $seen = [];

        foreach ($clientIds as $clientId) {
            $clientId = (int) $clientId;
            if ($clientId > 0) {
                $queue[] = $clientId;
            }
        }

        while (!empty($queue)) {
            $current = array_shift($queue);
            if ($current <= 0 || isset($seen[$current])) {
                continue;
            }

            $seen[$current] = true;

            $children = DB::table('clients')
                ->where('parent_id', $current)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($children as $childId) {
                if ($childId > 0 && !isset($seen[$childId])) {
                    $queue[] = $childId;
                }
            }
        }

        return array_values(array_map('intval', array_keys($seen)));
    }

    public function userCanSeeClient(int $clientUserId, int $clientId): bool
    {
        if ($clientUserId <= 0 || $clientId <= 0) {
            return false;
        }

        return in_array($clientId, $this->visibleClientIdsForUser($clientUserId), true);
    }

    public function userCanSeeJob(int $clientUserId, int $jobId): bool
    {
        if ($clientUserId <= 0 || $jobId <= 0) {
            return false;
        }

        $clientId = DB::table('job_details')->where('id', $jobId)->value('client_id');
        if (!$clientId) {
            return false;
        }

        return $this->userCanSeeClient($clientUserId, (int) $clientId);
    }

    public function accountantCanSeeClient(int $accountantUserId, int $clientId): bool
    {
        if ($accountantUserId <= 0 || $clientId <= 0) {
            return false;
        }

        return in_array($clientId, $this->visibleClientIdsForAccountant($accountantUserId), true);
    }
}
