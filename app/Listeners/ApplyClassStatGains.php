<?php

namespace App\Listeners;

use App\Events\CharacterLeveledUp;

class ApplyClassStatGains
{
    /**
     * Gains par niveau et par classe (type_id).
     * Adapte à tes colonnes et équilibrage.
     */
    private const GAINS = [
        // Guerrier
        1 => ['hp' => 10, 'strength' => 5, 'power' => 2, 'defense' => 4],
        // Mage
        2 => ['hp' => 5,  'strength' => 2, 'power' => 5, 'defense' => 2],
        // Archer / Ranger
        3 => ['hp' => 5, 'strength' => 4, 'power' => 3, 'defense' => 2],
    ];

    private const DEFAULT = ['hp' => 10, 'strength' => 5, 'power' => 5, 'defense' => 5];

    public function handle(CharacterLeveledUp $event): void
    {
        $ch     = $event->character;
        $levels = max(1, $event->levelsGained);

        $g = self::GAINS[$ch->type_id] ?? self::DEFAULT;

        // Multiplie par le nombre de niveaux pris d’un coup
        $ch->hp       = (int) $ch->hp       + $g['hp']       * $levels;
        $ch->strength = (int) $ch->strength + $g['strength'] * $levels;
        $ch->power    = (int) $ch->power    + $g['power']    * $levels;
        $ch->defense  = (int) $ch->defense  + $g['defense']  * $levels;

        // Si tu as des caps/arrondis/logique d’ascendance, applique-les ici.
    }
}
