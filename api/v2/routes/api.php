<?php
return [
    ['GET', '/general/alive', [new GeneralController(), 'getAlive']],
    ['GET', '/update/vocabs/msl[/{type}]', [new VocabController(), 'getMslVocab']],
    // TODO: Weitere Endpunkte ergänzen
];
