<?php

declare(strict_types=1);

namespace Resource\Types;


class Creator
{
    private string $creatorName;
    private ?string $nameType = null;
    private ?string $givenName = null;
    private ?string $familyName = null;
    private array $nameIdentifiers;
    private array $affiliations;

    public function __construct(string $creatorName)
    {
        $this->creatorName = $creatorName;
        $this->nameIdentifiers = [];
        $this->affiliations = [];
    }

    public function getCreatorName(): string
    {
        return $this->creatorName;
    }

    public function setCreatorName(string $creatorName): void
    {
        $this->creatorName = $creatorName;
    }

    public function getNameType(): ?string
    {
        return $this->nameType;
    }

    public function setNameType(?string $nameType): void
    {
        $this->nameType = $nameType;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): void
    {
        $this->givenName = $givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): void
    {
        $this->familyName = $familyName;
    }

    public function addNameIdentifier(string $nameIdentifier, string $nameIdentifierScheme, ?string $schemeURI = null): void
    {
        $this->nameIdentifiers[] = [
            'nameIdentifier' => $nameIdentifier,
            'nameIdentifierScheme' => $nameIdentifierScheme,
            'schemeURI' => $schemeURI
        ];
    }

    public function getNameIdentifiers(): array
    {
        return $this->nameIdentifiers;
    }

    public function setNameIdentifiers(array $nameIdentifiers): void
    {
        $this->nameIdentifiers = $nameIdentifiers;
    }

    public function addAffiliation(string $affiliation, ?string $affiliationIdentifier = null, 
        ?string $affiliationIdentifierScheme = null, ?string $schemeURI = null): void
    {
        $this->affiliations[] = [
            'affiliation' => $affiliation,
            'affiliationIdentifier' => $affiliationIdentifier,
            'affiliationIdentifierScheme' => $affiliationIdentifierScheme,
            'schemeURI' => $schemeURI
        ];
    }

    public function getAffiliations(): array
    {
        return $this->affiliations;
    }

    public function setAffiliations(array $affiliations): void
    {
        $this->affiliations = $affiliations;
    }

    public function toXML(): string
    {
        return "<creator></creator>";
    }

    public function validate(): bool
    {
        // Validate required fields according to DataCite schema
        if (empty($this->creatorName)) {
            return false;
        }

        // If nameType is provided, it must be either "Organizational" or "Personal"
        if ($this->nameType !== null && 
            !in_array($this->nameType, ['Organizational', 'Personal'])) {
            return false;
        }

        return true;
    }
}