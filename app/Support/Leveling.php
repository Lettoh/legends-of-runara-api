<?php

namespace App\Support;

use App\Events\CharacterLeveledUp;
use App\Models\Character;

class Leveling
{
    public static function maxLevel(): int
    {
        return (int) config('leveling.max_level', 100);
    }

    /**
     * XP requise pour passer du niveau $level à $level+1
     */
    public static function xpRequiredFor(int $level): int
    {
        $level = max(1, $level);
        $c     = config('leveling.curve', []);
        $base  = (int)($c['base']      ?? 100);
        $lin   = (int)($c['linear']    ?? 25);
        $quad  = (int)($c['quadratic'] ?? 15);

        return (int) round($base + $lin * ($level - 1) + $quad * ($level - 1) * ($level - 1));
    }

    /**
     * Applique un gain d'XP à un perso, gère les éventuels multi-level-up.
     * Hypothèse : $character->xp = XP "dans le niveau en cours" (pas cumulative).
     */
    public static function applyGain(Character $character, int $xp): array
    {
        $xp     = max(0, $xp);
        $max    = self::maxLevel();
        $levels = 0;
        $applied = 0;
        $fromLvl = (int) $character->level;

        while ($xp > 0 && $character->level < $max) {
            $need = self::xpRequiredFor($character->level) - (int) $character->xp;

            if ($xp >= $need) {
                $xp            -= $need;
                $applied       += $need;
                $character->xp  = 0;
                $character->level++;
                $levels++;
            } else {
                $character->xp += $xp;
                $applied       += $xp;
                $xp             = 0;
            }
        }

        // Au niveau max : bloquer à 0/0
        if ($character->level >= $max) {
            $character->level = $max;
            $character->xp    = 0;
        }

        if ($levels > 0) {
            event(new CharacterLeveledUp(
                character:   $character,
                fromLevel:   $fromLvl,
                toLevel:     (int) $character->level,
                levelsGained:$levels
            ));
        }

        return ['xp_applied' => $applied, 'levels' => $levels];
    }

    public static function xpToNext(Character $character): int
    {
        return $character->level >= self::maxLevel()
            ? 0
            : self::xpRequiredFor($character->level);
    }
}
