<?php
declare(strict_types=1);

/*
 * Einzige Quelle für die App-Kacheln auf torecon.de.
 * status: 'live' | 'review' | 'soon'
 * Neue App = ein Eintrag hier + Icon unter assets/img/apps/<key>.png (256×256).
 */

return [
    'brainbites' => [
        'name'      => 'BrainBites AI',
        'domain'    => 'getbrainbites.ai',
        'appstore'  => '6761427126',
        'macstore'  => null,
        'status'    => 'live',
        'platforms' => ['iPhone'],
        'langs'     => 4,
        'claim'     => [
            'de' => 'KI verstehen und anwenden – in fünf Minuten am Tag.',
            'en' => 'Understand and use AI – in five minutes a day.',
        ],
        'tech'      => ['SwiftUI', 'FastAPI', 'Claude', 'StoreKit 2'],
    ],
    'thinkless' => [
        'name'      => 'ThinkLess AI',
        'domain'    => 'getthinkless.ai',
        'appstore'  => '6762284671',
        'macstore'  => null,
        'status'    => 'live',
        'platforms' => ['iPhone'],
        'langs'     => 4,
        'claim'     => [
            'de' => 'Bessere Entscheidungen in unter 60 Sekunden.',
            'en' => 'Better decisions in under 60 seconds.',
        ],
        'tech'      => ['SwiftUI', 'FastAPI', 'Claude', 'PostgreSQL'],
    ],
    'zettelkueche' => [
        'name'      => 'Zettelküche AI',
        'domain'    => 'zettelkueche.app',
        'appstore'  => '6763764154',
        'macstore'  => null,
        'status'    => 'live',
        'platforms' => ['iPhone', 'iPad'],
        'langs'     => 4,
        'claim'     => [
            'de' => 'Rezept fotografieren – strukturiert und übersetzt zurück.',
            'en' => 'Snap a recipe – get it back structured and translated.',
        ],
        'tech'      => ['SwiftUI', 'CloudKit', 'FastAPI', 'Claude'],
    ],
    'eladung' => [
        'name'      => 'eLadung GO',
        'domain'    => 'eladung.torecon.de',
        'appstore'  => '6761632018',
        'macstore'  => '6760964990',
        'status'    => 'live',
        'platforms' => ['iPhone', 'iPad', 'Mac'],
        'langs'     => 2,
        'claim'     => [
            'de' => 'Ladekosten fürs E-Auto im Griff – zu Hause und unterwegs.',
            'en' => 'Keep your EV charging costs under control – at home and on the road.',
        ],
        'tech'      => ['SwiftUI', 'SwiftData', 'CloudKit', 'Mac'],
    ],
    'meisterlich' => [
        'name'      => 'Meisterlich',
        'domain'    => 'meisterlich.torecon.de',
        'appstore'  => '6762597134',
        'macstore'  => null,
        'status'    => 'live',
        'platforms' => ['iPhone'],
        'langs'     => 2,
        'claim'     => [
            'de' => 'Handwerker direkt finden – ohne Portal und ohne Lead-Gebühr.',
            'en' => 'Find tradespeople directly – no portal, no lead fees.',
        ],
        'tech'      => ['SwiftUI', 'FastAPI', 'PostgreSQL'],
    ],
    'violonge' => [
        'name'      => 'VioLonge AI',
        'domain'    => 'getviolonge.torecon.de',
        'appstore'  => '6762363594',
        'macstore'  => null,
        'status'    => 'review',
        'platforms' => ['iPhone'],
        'langs'     => 3,
        'claim'     => [
            'de' => 'Jeden Tag eine kleine, gute Gesundheitsentscheidung.',
            'en' => 'One small, good health decision every day.',
        ],
        'tech'      => ['SwiftUI', 'FastAPI', 'Claude'],
    ],
];
