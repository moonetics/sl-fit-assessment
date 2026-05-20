<?php

namespace App\Services;

use App\Models\Participant;

class DiscordIdentityService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function attachVerifiedIdentity(Participant $participant, string $discordUserId, array $metadata = []): Participant
    {
        $participant->forceFill([
            'discord_user_id' => $discordUserId,
            'discord_verified_at' => now(),
            'discord_metadata' => $metadata,
        ])->save();

        return $participant;
    }

    public function canAutoAssignRole(Participant $participant): bool
    {
        return false;
    }
}
