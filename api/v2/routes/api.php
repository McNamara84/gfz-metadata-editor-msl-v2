<?php
return [
    ['GET', '/general/alive', [new GeneralController(), 'getAlive']],
    ['GET', '/update/vocabs/msl[/{type}]', [new VocabController(), 'getMslVocab']],
    ['GET', '/vocabs/sciencekeywords', [new VocabController(), 'getGcmdScienceKeywords']],
    // TODO: Weitere Endpunkte ergänzen
];
