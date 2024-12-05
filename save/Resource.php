<?php

declare(strict_types=1);

namespace Resource;

use Resource\Types\Creator;

class Resource
{
    private string $identifier;
    private string $identifierType;
    private array $creators;
    private array $titles;
    private string $publisher;
    private int $publicationYear;
    private ?string $resourceType = null;
    private ?string $resourceTypeGeneral = null;
    private array $subjects;
    private array $contributors;
    private array $dates;
    private ?string $language = null;
    private array $alternateIdentifiers;
    private array $relatedIdentifiers;
    private array $sizes;
    private array $formats;
    private ?string $version = null;
    private array $rightsList;
    private array $descriptions;
    private array $geoLocations;
    private array $fundingReferences;
    private array $relatedItems;

    public function __construct(
        string $identifier,
        string $identifierType,
        string $publisher,
        int $publicationYear
    ) {
        $this->identifier = $identifier;
        $this->identifierType = $identifierType;
        $this->publisher = $publisher;
        $this->publicationYear = $publicationYear;

        // Initialize arrays
        $this->creators = [];
        $this->titles = [];
        $this->subjects = [];
        $this->contributors = [];
        $this->dates = [];
        $this->alternateIdentifiers = [];
        $this->relatedIdentifiers = [];
        $this->sizes = [];
        $this->formats = [];
        $this->rightsList = [];
        $this->descriptions = [];
        $this->geoLocations = [];
        $this->fundingReferences = [];
        $this->relatedItems = [];
    }

    // Identifier
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getIdentifierType(): string
    {
        return $this->identifierType;
    }

    public function setIdentifierType(string $identifierType): void
    {
        $this->identifierType = $identifierType;
    }

    // Creators
    public function addCreator(Creator $creator): void
    {
        $this->creators[] = $creator;
    }

    public function getCreators(): array
    {
        return $this->creators;
    }

    public function setCreators(array $creators): void
    {
        $this->creators = $creators;
    }

    // Titles
    public function addTitle(Title $title): void
    {
        $this->titles[] = $title;
    }

    public function getTitles(): array
    {
        return $this->titles;
    }

    public function setTitles(array $titles): void
    {
        $this->titles = $titles;
    }

    // Publisher
    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
    }

    // Publication Year
    public function getPublicationYear(): int
    {
        return $this->publicationYear;
    }

    public function setPublicationYear(int $publicationYear): void
    {
        $this->publicationYear = $publicationYear;
    }

    // Resource Type
    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function setResourceType(?string $resourceType): void
    {
        $this->resourceType = $resourceType;
    }

    public function getResourceTypeGeneral(): ?string
    {
        return $this->resourceTypeGeneral;
    }

    public function setResourceTypeGeneral(?string $resourceTypeGeneral): void
    {
        $this->resourceTypeGeneral = $resourceTypeGeneral;
    }

    // Subjects
    public function addSubject(Subject $subject): void
    {
        $this->subjects[] = $subject;
    }

    public function getSubjects(): array
    {
        return $this->subjects;
    }

    public function setSubjects(array $subjects): void
    {
        $this->subjects = $subjects;
    }

    // Contributors
    public function addContributor(Contributor $contributor): void
    {
        $this->contributors[] = $contributor;
    }

    public function getContributors(): array
    {
        return $this->contributors;
    }

    public function setContributors(array $contributors): void
    {
        $this->contributors = $contributors;
    }

    // Dates
    public function addDate(Date $date): void
    {
        $this->dates[] = $date;
    }

    public function getDates(): array
    {
        return $this->dates;
    }

    public function setDates(array $dates): void
    {
        $this->dates = $dates;
    }

    // Language
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    // Alternate Identifiers
    public function addAlternateIdentifier(string $identifier, string $type): void
    {
        $this->alternateIdentifiers[] = [
            'identifier' => $identifier,
            'type' => $type
        ];
    }

    public function getAlternateIdentifiers(): array
    {
        return $this->alternateIdentifiers;
    }

    public function setAlternateIdentifiers(array $alternateIdentifiers): void
    {
        $this->alternateIdentifiers = $alternateIdentifiers;
    }

    // Related Identifiers
    public function addRelatedIdentifier(RelatedIdentifier $relatedIdentifier): void
    {
        $this->relatedIdentifiers[] = $relatedIdentifier;
    }

    public function getRelatedIdentifiers(): array
    {
        return $this->relatedIdentifiers;
    }

    public function setRelatedIdentifiers(array $relatedIdentifiers): void
    {
        $this->relatedIdentifiers = $relatedIdentifiers;
    }

    // Sizes
    public function addSize(string $size): void
    {
        $this->sizes[] = $size;
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    public function setSizes(array $sizes): void
    {
        $this->sizes = $sizes;
    }

    // Formats
    public function addFormat(string $format): void
    {
        $this->formats[] = $format;
    }

    public function getFormats(): array
    {
        return $this->formats;
    }

    public function setFormats(array $formats): void
    {
        $this->formats = $formats;
    }

    // Version
    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    // Rights
    public function addRights(Rights $rights): void
    {
        $this->rightsList[] = $rights;
    }

    public function getRightsList(): array
    {
        return $this->rightsList;
    }

    public function setRightsList(array $rightsList): void
    {
        $this->rightsList = $rightsList;
    }

    // Descriptions
    public function addDescription(Description $description): void
    {
        $this->descriptions[] = $description;
    }

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function setDescriptions(array $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    // GeoLocations
    public function addGeoLocation(GeoLocation $geoLocation): void
    {
        $this->geoLocations[] = $geoLocation;
    }

    public function getGeoLocations(): array
    {
        return $this->geoLocations;
    }

    public function setGeoLocations(array $geoLocations): void
    {
        $this->geoLocations = $geoLocations;
    }

    // Funding References
    public function addFundingReference(FundingReference $fundingReference): void
    {
        $this->fundingReferences[] = $fundingReference;
    }

    public function getFundingReferences(): array
    {
        return $this->fundingReferences;
    }

    public function setFundingReferences(array $fundingReferences): void
    {
        $this->fundingReferences = $fundingReferences;
    }

    // Related Items
    public function addRelatedItem(array $relatedItem): void
    {
        $this->relatedItems[] = $relatedItem;
    }

    public function getRelatedItems(): array
    {
        return $this->relatedItems;
    }

    public function setRelatedItems(array $relatedItems): void
    {
        $this->relatedItems = $relatedItems;
    }

    public function toXML(): string
    {
        return "<resource></resource>";
    }

    public function validate(): bool
    {
        // Validate required fields according to DataCite schema
        if (empty($this->identifier) || empty($this->identifierType)) {
            return false;
        }

        if (empty($this->creators)) {
            return false;
        }

        if (empty($this->titles)) {
            return false;
        }

        if (empty($this->publisher)) {
            return false;
        }

        if (empty($this->publicationYear)) {
            return false;
        }

        return true;
    }
}