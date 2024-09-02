# Projektbeschreibung

Dieser Metadaten-Editor entstand im Rahmen eines studentischen Kooperationsprojekt zwischen Fachhochschule Potsdam und dem GeoForschungsZentrum Potsdam. Der Editor speichert Metadaten zu Forschungsdatensätzen in validen XML-Dateien nach dem DataCite, dem ISO- und dem DIF-Schema.

## Installation

1. Sicherstellen einer Entwicklungsumgebung mit PHP >8.2 sowie eines MySQL-Servers.
2. XSL- und ZIP-Extension für PHP muss vorhanden und aktiviert sein.
3. Apache und MySQL starten nicht vergessen.
4. Datenbank "mde" in MySQL (z. B. mittels phpMyAdmin) erstellen.
5. Inhalt der Datei sample_settings.php in neue Datei `settings.php` kopieren und Einstellungen für Datenbankverbindung anpassen.
6. Für die automatisch generierten Zeitzonen-Auswahl einen kostenlosen API Key unter https://timezonedb.com/ erstellen und ebenfalls in der neu erstellen settings.php eintragen
7. Google Maps JS API Key erstellen und ebenfalls in die Datei settings.php einfügen.
8. Alle Dateien dieses Repositories in den Ordner "htdocs"-Ordner des Webservers kopieren.
9. install.php über den Browser aufrufen. Die Datenbank-Tabellen werden automatisch erstellt.
10. Der Metadateneditor ist nun über localhost/verzeichnisname im Browser erreichbar.

Bei Problemen mit der Installation, hinterlasse gerne einen Eintrag im Issue Board dieses Repositories!

## Abhängigkeiten

In der header.html und footer.html werden folgende Abhängikeiten von Drittanbietern eingebunden:

- Bootstrap 5.3.3<br>
  Für das responsive Design und zur Gestaltung des größten Teils der Formulareingabefelder.
- Bootstrap Icons 1.11.3<br>
  Für die Icons in der oberen rechten Ecke des Editors (Dark Mode Switch, Language Switch)
- jQuery 3.7.1<br>
  Für die Event Handler in JavaScript und zum Vereinfachen des JavaScript-Codes allgemein.
- jQuery UI 1.14.0<br>
  Erweitert jQuery um die Autocomplete-Funktion, die wir aktuell für die Affiliation-Felder nutzen.
- Chosen 1.8.7<br>
  Da Bootstrap 5.3 keine einfach bedienbaren Multi-Select-Felder anbietet, wird diese Funktion mit Chosen implementiert. Design-Anpassungen waren notwendendig und werden in der Datei bs5-chosen.css umgesetzt.
- Tagify 4.31.3<br>
  Wird für das Feld Thesaurus Keywords, die eingabe von mehreren Affiliations und Free Keywords genutzt.
- jsTree 3.3.16<br>
  Wird zur Darstellung der GCMD Science Keywords als hierarchische Baumstruktur verwendet.

## Einstellungen

In der Datei `settings.php` können, neben den Zugangsdaten für die Datenbank, auch andere Einstellungen angepasst werden:

- `maxTitles:` Legt die max. Anzahl an Titeln fest, die Nutzende im Editor eingeben können, fest.

### API-Endpunkte

#### getTimezones

Der regelmäßige Aufruf zur Aktualisierung der Zeitzonen im Feld `Timezones` der Gruppe `Spatial and temporal coverage` per Cronjob wird empfohlen.
Empfohlener Ausführungsintervall: Monatlich
Aufzurufender URL: api.php?action=getTimezones

#### getNasaScienceKeywords

Damit der Thesaurus für die NASA Science Keywords aktuell bleibt, ist die regelmäßige Aktualisierung über diesen API-Endpunkt angedacht.
Empfohlender Ausführungsintervall: Wöchentlich
Auszuführende URL: api.php?action=getNasaScienceKeywords

#### getNasaInstrumentsKeywords

Zur Erstellung des Thesaurus für die NASA Instruments Keywords bzw. zur regelmäßigen Aktualisierung ist dieser API-Endpunkt gedacht.
Empfohlender Ausführungsintervall: Wöchentlich
Auszuführende URL: api.php?action=getNasaInstrumentsKeywords

#### getNasaPlatformsKeywords

Um auch die NASA GCMD Platforms Keywords als JSON-Datei für den entsprechenden Thesaurus zu aktualisieren wird eine regelmäßige Ausführung von getNasaPlatformsKeywords empfohlen.
Empfohlender Ausführungsintervall: Wöchentlich
Auszuführende URL: api.php?action=getNasaPlatformsKeywords

#### getChronostratKeywords

Die Chronostrat Keywords werden von der International Chronostratigraphic Chart International Commission on Stratigraphy veröffentlicht. Eine regelmäßige Aktualisierung wird empfohlen, jedoch werden diese Keywords eher selten aktualisiert.
Empfohlender Ausführungsintervall: Monatlich
Auszuführende URL: api.php?action=getChronostratKeywords

#### getCGIKeywords

Die CGI Simple Lithology Keywords werden von der Commission for the Management and Application of Geoscience Information (CGI) entwickelt. Eine regelmäßige Aktualisierung wird empfohlen, jedoch werden diese Keywords eher selten aktualisiert.
Empfohlender Ausführungsintervall: Monatlich
Auszuführende URL: api.php?action=getCGIKeywords

#### getGemetConcepts

Die englischsprachigen Konzepte des GEneral Multilingual Environmental Thesaurus (Gemet) werden mit diesem API-Endpunkt beschafft. Eine regelmäßige Aktualisierung wird empfohlen, jedoch werden diese Konzepte und Terme nur "every few years" aktualisiert. Auch Korrekturen erfolgen nur alle paar Monate (siehe: [Gemet changes](https://www.eionet.europa.eu/gemet/en/changes/) )
Empfohlender Ausführungsintervall: Monatlich
Auszuführende URL: api.php?action=getGemetConcepts

#### getRorAffiliations

Zur regelmäßigen Aktualisierung der Daten in den Auswahlfeldern für die Affiliations von Personen sollte dieser API-Endpunkt regelmäßig ausgeführt werden.
Empfohlender Ausführungsintervall: Wöchentlich
Auszuführende URL: api.php?action=getRorAffiliations

#### getCrossRefFunders

Zur regelmäßigen Aktualisierung der Auswwahloptionen im Funders-Dropdown-Feld wird die regelmäßige Ausführung von getCrossRefFunders nahegelegt.
Empfohlender Ausführungsintervall: Wöchentlich
Auszuführende URL: api.php?action=getCrossRefFunders

#### getResourceAsDataciteXml

Der API-Endpunkt „getResourceAsDataciteXml” nimmt eine ID entgegen und exportiert den Datensatz mit dieser ID aus der Datenbank und transformiert ihn in eine valide XML-Datei gemäß dem [DataCite-Schema in der aktuellen Version 4.5 vom 22.01.2024](https://datacite-metadata-schema.readthedocs.io/en/4.5/). Der Endpunkt wird in erster Linie vom Metadateneditor selbst beötigt, kann aber zukünftig auch anderen Anwendungen eine valide DataCite-Datei liefern.
Auszuführende URL: api.php?action=getResourceAsDataciteXml&id=1

## Formularfelder

### Resource Information

- DOI

  - In diesem Feld kommt die DOI (Digital Object Identifier), die die Ressource identifiziert.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: doi in der Tabelle Resource.
  - Restriktionen: Muss im Format "prefix/suffix" sein.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/identifier/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `10.5880/GFZ.3.1.2024.002` `10.5880/pik.2024.001`

- Publication Year

  - In diesem Feld kommt das Veröffentlichungsjahr der Ressource.
  - Datentyp: Year
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: year in der Tabelle Resource.
  - Restriktionen: Muss im Format YYYY sein.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/publicationyear/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `1998` `2018` `1900`

- Resource Type

  - In diesem Feld kommt der Typ der Ressource.
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: resource_type_general in der Tabelle Resource_Type.
  - Restriktionen: Muss ein „Recource Type“ ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/resourcetype/#a-resourcetypegeneral)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Dataset` `Audiovisual` `Book`

- Version

  - In diesem Feld kommt die Versionsnummer der Ressource.
  - Datentyp: FLOAT
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: version in der Tabelle Resource.
  - Restriktionen: Zahl Punkt Zahl.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/version/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `1.0` `2.1` `3.5`

- Language of Dataset

  - In diesem Feld kommt die Sprache des Datensatzes.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: name in der Tabelle Language.
  - Restriktionen: Muss eine „Sprache“ ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/language/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `"en" Englisch` `"de" Deutsch` `"fr" Französisch`

- Title

  - In diesem Feld kommt der Titel der Ressource.
  - Datentyp: Text
  - Vorkommen: 1-n
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: text in der Tabelle title.
  - Restriktionen: Muss angegeben werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/title/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Drone based photogrammetry data at the Geysir geothermal field, Iceland`

- Title Type
  - In diesem Feld kommt die Art des Titels (außer dem Haupttitel).
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: title in der Tabelle Title_Type.
  - Restriktionen: Muss ein „Title Type“ ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/title/#a-titletype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Main` `Subtitle` `Translated Title`

### Rights

- Rights Title
  - In diesem Feld kommt der Titel der Lizenz mit ihrer Abkürzung.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-n
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: text und rightsIdentifier in der Tabelle Rights.
  - Restriktionen: Muss eine „Linzenz“ ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/rights/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Creative Commons Attribution 4.0 International (CC-BY-4.0)`

### Authors

- Lastname

  - In diesem Feld kommt der Nachname des Autors.
  - Datentyp: Text
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: familyname in der Tabelle Author.
  - Restriktionen: Muss angegeben werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/creator/#familyname)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Jemison` `Smith`

- Firstname

  - In diesem Feld kommt der Vorname des Autors.
  - Datentyp: Text
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: givenname in der Tabelle Author.
  - Restriktionen: Muss angegeben werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/creator/#givenname)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `John` `Jane`

- Author ORCID

  - In diesem Feld kommt die ORCID des Autors (Open Researcher and Contributor ID).
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: orcid in der Tabelle Author.
  - Restriktionen: Muss im Format "xxxx-xxxx-xxxx-xxxx" sein.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/creator/#nameidentifier)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `1452-9875-4521-7893` `0082-4781-1312-884x`

- Affiliation
  - In diesem Feld kommt die Zugehörigkeit des Autors.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-n
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: name in der Tabelle Affiliation.
  - Restriktionen: Es ist optional.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/creator/#affiliation)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Technische Universität Berlin` `GFZ, Helmholtz-Zentrum Potsdam - Deutsches GeoForschungsZentrum GFZ`

### Contact Person(s)

- Lastname

  - In diesem Feld kommt der Nachname der Person.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: familyname in der Tabelle Contact_Person
  - Restriktionen: Optimal aber wenn es angegeben wurde, muss auch die E-Mail-Adresse eingegeben.
  - [Link zur Dokumentation von Schema Documentation for gmd mit der Beschreibung des Elements im Metadaten-Schema](https://schemas.isotc211.org/19139/-/gmd/1.0/gmd/#element_CI_ResponsibleParty)
  - Schema Version: "ISO" 2012-07-13
  - Beispielwerte: `Jemison` `Smith`

- Firstname

  - In diesem Feld kommt der Vorname der Person.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: givenname in der Tabelle Contact_Person
  - Restriktionen: Optimal aber wenn es angegeben wurde, muss auch der Nachname eigegeben.
  - [Link zur Dokumentation von Schema Documentation for gmd mit der Beschreibung des Elements im Metadaten-Schema](https://schemas.isotc211.org/19139/-/gmd/1.0/gmd/#element_CI_ResponsibleParty)
  - Schema Version: "ISO" 2012-07-13
  - Beispielwerte: `John` `Jane`

- Position

  - In diesem Feld kommt die Position der Person in der Organisation.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: position in der Tabelle Contact_Person
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von Schema Documentation for gmd mit der Beschreibung des Elements im Metadaten-Schema](https://schemas.isotc211.org/19139/-/gmd/1.0/gmd/#element_CI_ResponsibleParty)
  - Schema Version: "ISO" 2012-07-13
  - Beispielwerte: `Arbeitsgruppenleiter` `Projektleiter`

- Email

  - In diesem Feld kommt die E-Mail-Adresse der Person oder Organisation.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: email in der Tabelle Contact_Person
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von Schema Documentation for gmd mit der Beschreibung des Elements im Metadaten-Schema](https://schemas.isotc211.org/19139/-/gmd/1.0/gmd/#element_CI_ResponsibleParty)
  - Schema Version: "ISO" 2012-07-13
  - Beispielwerte: `ali.mohammed@gfz-potsdam.de` `holger.ehrmann@gfz-potsdam.de`

- Website
  - In diesem Feld kommt die Website der Organisation.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: website in der Tabelle Contact_Person
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von Schema Documentation for gmd mit der Beschreibung des Elements im Metadaten-Schema](https://schemas.isotc211.org/19139/-/gmd/1.0/gmd/#element_CI_ResponsibleParty)
  - Schema Version: "ISO" 2012-07-13
  - Beispielwerte: `gfz-potsdam.de` `fh-potsdam.de`
- Affiliation
  - In diesem Feld kommt die Zugehörigkeit der Person.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-n
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: name in der Tabelle Affiliation.
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von Schema Documentation for gmd mit der Beschreibung des Elements im Metadaten-Schema](https://schemas.isotc211.org/19139/-/gmd/1.0/gmd/#element_CI_ResponsibleParty)
  - Schema Version: "ISO" 2012-07-13
  - Beispielwerte: `Technische Universität Berlin` `GFZ, Helmholtz-Zentrum Potsdam - Deutsches GeoForschungsZentrum GFZ`
  - **Hinweis**: Autocomplete-Funktion für das Eingabefeld Affiliation einsetzen! ähnlich der 'Author' Form group.

### Contributors

- _Personal_
- Lastname

  - In diesem Feld kommt der Nachname der/des Mitwirkenden.
  - Datentyp: Text
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#familyname)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Jemison` `Smith`

- Firstname

  - In diesem Feld kommt der Vorname der/des Mitwirkenden.
  - Datentyp: Text
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#givenname)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `John` `Jane`

- Role

  - In diesem Feld kommt die Rolle/ Rollen der/des Mitwirkenden.
  - Datentyp: Text
  - Vorkommen: 1 _Gemäß der DataCite Dokumentation kann ein Mitwirkender nur eine Rolle haben. Allerdings ermöglicht der aktuelle MDE die Eingabe mehrerer Rollen für einen Mitwirkenden._
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: muss mindestens eine Rolle ausgewählt werden./muss nur eine Rolle ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-contributortype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Data Manager` `Project Manager`

- Author ORCID

  - In diesem Feld kommt die ORCID der/des Mitwirkenden (Open Researcher and Contributor ID).
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Muss im Format "xxxx-xxxx-xxxx-xxxx" sein.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-nameidentifierscheme)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `1452-9875-4521-7893` `0082-4781-1312-884x`

- Affiliation

  - In diesem Feld kommt die Zugehörigkeit der/des Mitwirkenden.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-n
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Es ist optional.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-affiliationidentifier)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Technische Universität Berlin` `GFZ, Helmholtz-Zentrum Potsdam - Deutsches GeoForschungsZentrum GFZ`

- _Organisational_
- Organisation Name

  - In diesem Feld kommt der Name der Institution.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Es ist optional.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-nametype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `University of Applied Sciences Potsdam` `Helmholtz Centre Potsdam - GFZ German Research Centre for Geosciences`

- Role
  - In diesem Feld kommt die Rolle/ Rollen der Institution.
  - Datentyp: Text
  - Vorkommen: 1 _Gemäß der DataCite Dokumentation kann ein Mitwirkender nur eine Rolle haben. Allerdings ermöglicht der aktuelle MDE die Eingabe mehrerer Rollen für einen Mitwirkenden._
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: muss mindestens eine Rolle ausgewählt werden./muss nur eine Rolle ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-contributortype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Data Collector` `Data Curator`

* _Personal_
* Lastname

  - In diesem Feld kommt der Nachname der/des Mitwirkenden.
  - Datentyp: Text
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#familyname)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Jemison` `Smith`

* Firstname

  - In diesem Feld kommt der Vorname der/des Mitwirkenden.
  - Datentyp: Text
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Optimal.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#givenname)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `John` `Jane`

* Role

  - In diesem Feld kommt die Rolle/ Rollen der/des Mitwirkenden.
  - Datentyp: Text
  - Vorkommen: 1 _Gemäß der DataCite Dokumentation kann ein Mitwirkender nur eine Rolle haben. Allerdings ermöglicht der aktuelle MDE die Eingabe mehrerer Rollen für einen Mitwirkenden._
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: muss mindestens eine Rolle ausgewählt werden./muss nur eine Rolle ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-contributortype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Data Manager` `Project Manager`

* Author ORCID

  - In diesem Feld kommt die ORCID der/des Mitwirkenden (Open Researcher and Contributor ID).
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Muss im Format "xxxx-xxxx-xxxx-xxxx" sein.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-nameidentifierscheme)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `1452-9875-4521-7893` `0082-4781-1312-884x`

* Affiliation

  - In diesem Feld kommt die Zugehörigkeit der/des Mitwirkenden.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-n
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Es ist optional.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-affiliationidentifier)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Technische Universität Berlin` `GFZ, Helmholtz-Zentrum Potsdam - Deutsches GeoForschungsZentrum GFZ`

* _Organizational_

* Organisation Name

  - In diesem Feld kommt der Name der Institution.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: Es ist optional.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-nametype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `University of Applied Sciences Potsdam` `Helmholtz Centre Potsdam - GFZ German Research Centre for Geosciences`

* Role

  - In dieses Feld kommt die Rolle/ Rollen der Institution.
  - Datentyp: Text
  - Vorkommen: 1 _Gemäß der DataCite Dokumentation kann ein Mitwirkender nur eine Rolle haben. Allerdings ermöglicht der aktuelle MDE die Eingabe mehrerer Rollen für einen Mitwirkenden._
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
  - Restriktionen: muss mindestens eine Rolle ausgewählt werden./muss nur eine Rolle ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#a-contributortype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Data Collector` `Data Curator`

* Affiliation
* In diesem Feld kommt die Zugehörigkeit der Mitwirkenden Institution.
* Datentyp: Zeichenkette
* Vorkommen: 0-n
* Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!**
* Restriktionen: Es ist optional.
* [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/contributor/#affiliation)
* Schema Version: "DataCite" 4.5
* Beispielwerte: `Technische Universität Berlin` `GFZ, Helmholtz-Zentrum Potsdam - Deutsches GeoForschungsZentrum GFZ`

### Description

- Abstract
- Ein Abstract ist definiert als eine gekürzte präzise Darstellung des Inhalts eines Datensatzes.Eine kurze Beschreibung der Ressource und des Kontextes, in dem die Ressource erstellt wurde.
- Datentyp: Freitext
- Vorkommen: 1
  **Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!\*\*
- Restriktionen: keine - Freitext
- [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/appendices/appendix-1/descriptionType/#abstract)
- Schema Version: "DataCite" 4.5
- Beispielwerte: `The dataset contains a subset of an airborne hyperspectral HyMap image over the Cabo de Gata-Nίjar Natural Park in Spain from 15.06.2005, and soil wet chemistry data based on in-situ soil sampling. The Cabo de Gata-Nίjar Natural Park is a semi-arid mediterranean area in Southern Spain, sparsely populated and with a range of landscape patterns.`

- Methods

  - Die für die Studie oder Forschung verwendete Methodik. Sie beschreibt die Art der Durchführung wie man zu einem angestrebten Ziel gelangen kann.
  - Datentyp: Freitext
  - Vorkommen: 0-1
    **Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!\*\*
  - Restriktionen: keine - Freitext
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/appendices/appendix-1/descriptionType/#methods)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Graphical representation of the steps used to reconstruct sequence alignments of the Nudix superfamily, as described in the Materials and Methods section. (A) The pipeline to build the 78-PDB structure guided sequence alignment. (B) The pipeline to build the 324-core sequence alignment guided by the 78-PDB sequence alignment. (C) The pipeline to build the alignment of the complete Nudix clan (38,950 sequences). (D) Illustration of how to combine two alignment into one guided by a scaffold alignment.`

- TechnicalInfo

  - Detaillierte Informationen zum Entwurf, der Implementierung, dem Betrieb, der Verwendung und/oder der Wartung eines Prozesses, Systems oder Instruments. Bei der Beschreibung von Software kann dies den Inhalt einer "readme.txt" und notwendige Umgebungsinformationen (Hardware, Betriebssoftware, Anwendungen/Programme mit Versionsinformationen, eine von Menschen lesbare Zusammenfassung des Softwarezwecks) umfassen, die nicht durch andere Eigenschaften (z. B. Programmiersprache) beschrieben werden können.
  - Datentyp: Freitext
  - Vorkommen: 0-1
    **Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!\*\*
  - Restriktionen: keine - Freitext
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/appendices/appendix-1/descriptionType/#technicalinfo)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `Scripts written and run using Wolfram Mathematica (confirmed with versions 10.2 to 11.1). Assumes raw data matches format produced by a LTQ Orbitrap Velos mass spectrometer and exported by the proprietary software (Xcalibur) to a comma-separated values (.csv) file. The .csv files are the expected input into the Mathematica scripts. `

- Other
  - Sonstige Beschreibungsinformationen, die nicht in eine der obigen Kategorien passen.
  - Vorkommen: 0-1
    **Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: **Noch nicht bekannt!\*\*
  - Restriktionen: keine - Freitext
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/appendices/appendix-1/descriptionType/#other)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `This is the description of a data set that does not fit into the categories of abstract, methods or technical information, but is nevertheless extremely necessary.`

### Thesaurus Keywords

- Thesaurus Keyword

  - In dieses Feld können Schlagwörter zur inhaltlichen Beschreibung des Datensatzes eingefügt werden
  - Datentyp: Zeichenkette
  - Vorkommen: 0-n
  - Das zugehörige Feld in der Datenbank heißt: keyword in der Tabelle Thesaurus_Keywords
  - Restriktionen: Es stehen (Stand 18.06.2024) nur GCMD Science Keywords (Earth Science und Earth Science Services) zur Auswahl. **Weitere kontrollierte Vokabulare müssen/können/sollten noch hinzugefügt werden.**
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/subject/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `EARTH SCIENCE` `AGRICULTURE`

- hiddenField scheme

  - hier wird bei Auswahl eines Keywords der Name des zugehörigen Schemas gespeichert
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank heißt: scheme in der Tabelle Thesaurus_Keywords
  - Restriktionen: bisher ist nur der Wert `NASA/GCMD Earth Science Keywords` möglich
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/subject/#a-scheme)
  - Schema Version: "DataCite" 4.5
  - Beispielwert: `NASA/GCMD Earth Science Keywords`

- hiddenField schemeURI

  - hier wird bei Auswahl eines Keywords der URI des zugehörigen Schemas gespeichert
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank heißt: schemeURI in der Tabelle Thesaurus_Keywords
  - Restriktionen: bisher ist nur der Wert `https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/sciencekeywords` möglich
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/subject/#b-schemeuri)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/sciencekeywords`

- hiddenField valueURI

  - hier wird bei Auswahl eines Keywords der PID, die URI, des Keywords gespeichert
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank heißt: valueURI in der Tabelle Thesaurus_Keywords
  - Restriktionen: wird aus der Datei gcmdScienceKeywords.json extrahiert
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/subject/#c-valueuri)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `https://gcmd.earthdata.nasa.gov/kms/concept/a2cc8e02-3207-4c40-af41-9656404bac0a`

- hiddenField language
  - hier wird bei Auswahl eines Keywords der URI des zugehörigen Schemas gespeichert
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank heißt: language in der Tabelle Thesaurus_Keywords
  - Restriktionen: wird aus der Datei gcmdScienceKeywords.json extrahiert
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/subject/#subject)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `en` `de`

### Free Keywords

- In dieses Feld kommen freie Schlagwörter, diese sind nicht an einen Thesaurus gebunden.
  - Datentyp: Freitext
  - Vorkommen: 0-n
- Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: free_keywords
  - Restriktionen: keine Duplikate
  - Schema Version:
- Beispielwerte: carbon dioxide emission, CO2 emission, methane emission, CH4 emission, peatland, wetland, eddy covariance, rewetting, emission factor, mitigation, Test

### Dates

- Date created
  Datum an welchem der Datensatz ersetellt wurde.
  - Datentyp: Date
  - Vorkommen: 0-1
  - Restriktionen: Datumsformat im Eingabefeld je nach Spracheinstellung des Browsers der Nutzenden.
  - Schemata: DataCite, ISO
  - Beispielwerte: `2024-06-05` `1999-04-07`
- Embargo until
  Falls eine Schutzfrist zur Freigabe des Datensatzes vereinbart wurde, das Datum ab wann der Datensatz frei ist.
  - Datentyp: Date
  - Vorkommen: 0-1
  - Restriktionen: Datumsformat im Eingabefeld je nach Spracheinstellung des Browsers der Nutzenden.
  - Schemata: DataCite
  - Beispielwerte: `2024-06-15` `2000-12-31`

### Spatial and temporal coverage

Räumliche und zeitliche Einordnung des Datensatzes. Zur einfacheren Erfassung der geografischen Angaben wird Google Maps genutzt. Wird auf der Karte ein Rechteck gezeichnet, werden die Koordinaten dieses Rechtecks an die Eingabefelder `Latitude Min` und `Latitude Max` sowie `Longitude Min` und `Longitude Max` übergeben. Die Speicherung dieser Koordinaten geschieht erst zusammen mit der Speicherung des gesamten Formulars. Wird lediglich ein Marker auf der Karte gesetzt, werden die Felder `Latitude Min` und `Longitude Min` befüllt, während `Latitude Max` und `Longitude Max` leer bleiben.

- Latitude Min
  Geographische Breite einer Einzelkoordinate oder die kleinere geographische Breite eines Rechtecks als Fließkommazahl mit Punkt als Trennzeichen.
  - Datentyp: Fließkommazahl
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: Nur positve und negative Zahlen im Wertebereich von -90 bis +90
  - XML-Schemata: DataCite, ISO und DIF
  - Beispielwerte: `52.0317983498743` `-3.234`
- Latitude Max
  Geographische Breite eines Rechtecks als Fließkommazahl mit Punkt als Trennzeichen.
  - Datentyp: Fließkommazahl
  - Vorkommen: 0-1
  - Restriktionen: Nur positve und negative Zahlen im Wertebereich von -90 bis +90
  - XML-Schemata: DataCite, ISO und DIF
  - Beispielwerte: `49.72437624376` `-32.82438824398`
- Longitude Min
  Geographische Länge einer Einzelkoordinate oder die kleinere geographische Länge eines Rechtecks als Fließkommazahl mit Punkt als Trennzeichen.
  - Datentyp: Fließkommazahl
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: Nur positve und negative Zahlen im Wertebereich von -180 bis +180
  - XML-Schemata: DataCite, ISO und DIF
  - Beispielwerte: `108.0317983498743` `-3.04`
- Longitude Max
  Geographische Länge eines Rechtecks als Fließkommazahl mit Punkt als Trennzeichen.
  - Datentyp: Fließkommazahl
  - Vorkommen: 0-1
  - Restriktionen: Nur positve und negative Zahlen im Wertebereich von -180 bis +180
  - XML-Schemata: DataCite, ISO und DIF
  - Beispielwerte: `99.037543735498743` `-6.4`
- Description
  Freitextfeld zur Erklärung des geographischen und zeitlichen Zusammenhangs.
  - Datentyp: Feitext
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: keine
  - XML-Schemata: DataCite und ISO
  - Beispielwerte: `Several boreholes at regular intervals distributed over the entire surface.`
- Start Date
  Datum des Beginns der zeitlichen Einordnung des Datensatzes.
  - Datentyp: Datum
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: YYYY-MM-DD
  - XML-Schemata: DataCite und ISO
  - Beispielwerte: `2024-01-02` `1999-08-07`
- Start Time
  Zeitangabe im Format hh:mm:ss
  - Datentyp: String
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: hh:mm:ss
  - XML-Schemata: DataCite, ISO
  - Beispielwerte: `10:43:50` `04:00:00`
- End Date
  Datum des Endes der zeitlichen Einordnung des Datensatzes.
  - Datentyp: Datum
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: YYYY-MM-DD
  - XML-Schemata: DataCite und ISO
  - Beispielwerte: `1998-01-02` `2001-07-08`
- End Time
  Zeitangabe im Format hh:mm:ss
  - Datentyp: String
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: hh:mm:ss
  - XML-Schemata: DataCite, ISO
  - Beispielwerte: `11:34:56` `09:00:00`
- Timezone
  Zeitzone ab UTC in der Start Time und End Time angegeben sind. Alle möglichen Zeitzonen werden regelmäßig über die API mit `getTimezones` aktualisiert, wenn ein CronJob auf dem Server eingerichtet wurde. Wichtig: API-Schlüssel für timezonedb.com muss in den Einstellungen angegeben werden um die automatische Aktualisierung nutzen zu können!
  - Datentyp: Zeichenkette (Auswahl aus Liste)
  - Vorkommen: 1 (Pflichtfeld)
  - Restriktionen: Lediglich Werte aus Liste sind erlaubt
  - XML-Schemata: ISO
  - Beispielwerte: `+02:00` `-08:00`

### Related Work

- Relation

  - In dieses Feld kommt der Typ der Relation.
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: relation_fk in der Tabelle Related_Work.
  - Restriktionen: Es muss ein Relation Type ausgewählt werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/relatedidentifier/#b-relationtype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `IsCitedBy` `IsSupplementTo` `IsContinuedBy`

- Identifier

  - In dieses Feld kommt der Identifikator
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: Identifier in der Tabelle Related_Work
  - Restriktionen: Muss angegeben werden.
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/appendices/appendix-1/relatedIdentifierType/)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `ark:/13030/tqb3kh97gh8w` `arXiv:0706.0001`

- Identifier Type

  - In diesem Feld kommt der Typ der relatedIdentifier.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Das zugehörige Feld in der Datenbank, wo der Wert gespeichert wird, heißt: identifier_type_fk in der Tabelle Related_Work.
  - Restriktionen: Optional
  - [Link zur Dokumentation von DataCite mit der Beschreibung des Elements im Metadaten-Schema](https://datacite-metadata-schema.readthedocs.io/en/4.5/properties/relatedidentifier/#a-relatedidentifiertype)
  - Schema Version: "DataCite" 4.5
  - Beispielwerte: `ARK` `arXiv` `EAN13`

  ### Funding Reference

* Funder
  In diesem Feld kommt der Name des Finanzierungsanbieters.
  - Datentyp: Zeichenkette
  - Vorkommen: 1
  - Restriktionen: Um Vorschläge über der Api zu erhalten, ist es erforderlich, mindestens die ersten zwei Buchstaben der Funder einzugeben. Zudem besteht die Möglichkeit, die Funder manuell einzutragen.
  - Schemata: DataCite
  - Beispielwerte: `Gordon and Betty Moore Foundation` `Ford Foundation`
* Grant Number
  In diesem Feld kommt der Code, der dem Geldgeber einer geförderten Zuwendung (Grant) zugewiesen wird.
  - Datentyp: Zeichenkette
  - Vorkommen: 0-1
  - Restriktionen: Es ist optional.
  - Schemata: DataCite
  - Beispielwerte: `GBMF3859.01` `GBMF3859.22`
* Grant Name
  In diesem Feld kommt der lesbare Titel oder Name der Auszeichnung (grant).
  - Datentyp: Text
  - Vorkommen: 0-1
  - Restriktionen: Es ist optional.
  - Schemata: DataCite
  - Beispielwerte: `Socioenvironmental Monitoring of the Amazon Basin and Xingu` `Grantmaking at a glance`

## Datenvalidierung

- Folgende Felder müssen zwingend ausgefüllt werden: **Publication Year**, **Title**, **Author Lastname**, **Author Firstname**, **Author Role**, **Author ORCID**, **Contributor ORCID**, **Contributor Role**, **Description Abstract** **Relation** und **Identifier**.❗
- Die restlichen Felder **DOI**, **Resource Type**, **Version**, **Language of Dataset**, **Title Type**, **Rights**, **Contact Person Position**, **Contact Person Email**, **Contact Person Website**, **Author Affiliation**, **Contact Person Affiliation**, **Contributor Lastname**, **Contributor Firstname**, **Contributor Affiliation**, **Contributor Organisation Name**, **Description Methods**, **Description TechnicalInfo**, **Description Other**, **Thesaurus Keywords** **Identifier Typ** können optional leer bleiben.✅

## Datenbankstruktur

## Lizenz

Dieses Projekt wurde 2024 von Studenten der Fachhochschule Potsdam in Zusammenarbeit mit dem Deutschen GeoForschungsZentrum entwickelt und wird unter der MIT-Lizenz veröffentlicht.
