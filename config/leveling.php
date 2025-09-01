<?php

return [
    // Niveau max atteignable
    'max_level' => 100,

    // Courbe pour "XP requise pour passer de L -> L+1"
    // req(L) = base + linear*(L-1) + quadratic*(L-1)^2
    // => simple à ajuster, progression douce au début puis de plus en plus chère
    'curve' => [
        'base'      => 100, // xp pour passer 1 -> 2
        'linear'    => 25,
        'quadratic' => 15,
    ],
];
