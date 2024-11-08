<?php
require_once __DIR__ . '/../controllers/GeneralController.php';
require_once __DIR__ . '/../controllers/VocabController.php';
require_once __DIR__ . '/../controllers/ValidationController.php';
require_once __DIR__ . '/../controllers/DatasetController.php';
return [
    ['GET', '/general/alive', [new GeneralController(), 'getAlive']],
    ['GET', '/update/vocabs/msl/labs', [new VocabController(), 'updateMslLabs']],
    ['GET', '/update/vocabs/msl[/{type}]', [new VocabController(), 'getMslVocab']],
    ['GET', '/update/timezones', [new VocabController(), 'updateTimezones']],
    ['GET', '/vocabs/sciencekeywords', [new VocabController(), 'getGcmdScienceKeywords']],
    ['GET', '/vocabs/roles[/{type}]', [new VocabController(), 'getRoles']],
    ['GET', '/vocabs/relations', [new VocabController(), 'getRelations']],
    ['GET', '/validation/patterns[/{type}]', [new ValidationController(), 'getPattern']],
    ['GET', '/validation/identifiertypes', [new ValidationController(), 'getIdentifierTypes']],
    ['GET', '/dataset/export/{id}/all/download', [new DatasetController(), 'exportAllDownload']],
    ['GET', '/dataset/export/{id}/all', [new DatasetController(), 'exportAll']],
    ['GET', '/dataset/export/{id}/{scheme}/download', [new DatasetController(), 'exportResourceDownload']],
    ['GET', '/dataset/export/{id}/{scheme}', [new DatasetController(), 'exportResource']],
    ['GET', '/vocabs/licenses/all', [new VocabController(), 'getAllLicenses']],
    ['GET', '/vocabs/licenses/software', [new VocabController(), 'getSoftwareLicenses']],
    // TODO: Weitere Endpunkte ergänzen
];
