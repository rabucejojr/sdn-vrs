<?php

namespace App\Support;

use App\Models\User;

class TravelOrderPersonnel
{
    public static function mergeIssuedTo(array $passengers, User $issuedTo): array
    {
        $seenUsers = [];
        $seenNames = [];
        $result = [];

        $rows = array_merge([[
            'name' => $issuedTo->name,
            'designation' => $issuedTo->position,
            'user_id' => $issuedTo->id,
        ]], $passengers);

        foreach ($rows as $row) {
            $userId = isset($row['user_id']) ? (int) $row['user_id'] : null;
            $nameKey = mb_strtolower(trim((string) ($row['name'] ?? '')));

            if (($userId && isset($seenUsers[$userId])) || ($nameKey !== '' && isset($seenNames[$nameKey]))) {
                continue;
            }

            if ($userId) {
                $seenUsers[$userId] = true;
            }
            if ($nameKey !== '') {
                $seenNames[$nameKey] = true;
            }
            $result[] = $row;
        }

        return $result;
    }
}
