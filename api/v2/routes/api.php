<?php
return [
    ['GET', '/general/alive', [new GeneralController(), 'getAlive']],
    ['GET', '/update/vocabs/msl/labs', [new VocabController(), 'updateMslLabs']],
    ['GET', '/update/vocabs/msl[/{type}]', [new VocabController(), 'getMslVocab']],
    ['GET', '/update/timezones', [new VocabController(), 'updateTimezones']],
    ['GET', '/vocabs/sciencekeywords', [new VocabController(), 'getGcmdScienceKeywords']],
    ['GET', '/vocabs/roles[/{type}]', [new VocabController(), 'getRoles']],
    // TODO: Weitere Endpunkte ergänzen
];
