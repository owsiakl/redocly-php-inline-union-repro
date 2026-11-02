<?php

require __DIR__ . '/out/client.php';

use InlineUnionRepro\Client;
use InlineUnionRepro\Config;
use InlineUnionRepro\Pet;
use InlineUnionRepro\PersonOwner;

$client = new Client(new Config());

$pets = [
    'without owner' => new Pet(name: 'Rex'),
    'with owner' => new Pet(name: 'Rex', owner: new PersonOwner(kind: 'person', name: 'Ann')),
];

foreach ($pets as $label => $pet) {
    try {
        $result = $client->postPet($pet);
        printf("%s: OK, got %s\n", $label, get_debug_type($result));
    } catch (Throwable $e) {
        printf("%s: %s: %s\n", $label, $e::class, $e->getMessage());
    }
}
