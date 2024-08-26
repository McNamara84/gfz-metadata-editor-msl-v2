<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:vmf="http://www.altova.com/MapForce/UDF/vmf" xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="vmf xs">
	<xsl:template name="vmf:vmf1_inputtoresult">
		<xsl:param name="input" select="/.."/>
		<xsl:choose>
			<xsl:when test="$input='Abstract'">
				<xsl:value-of select="'VAbstract'"/>
			</xsl:when>
			<xsl:when test="$input='Methods'">
				<xsl:value-of select="'VMethods'"/>
			</xsl:when>
			<xsl:when test="$input='Technical Information'">
				<xsl:value-of select="'VTechnicalInfo'"/>
			</xsl:when>
			<xsl:when test="$input='Other'">
				<xsl:value-of select="'VOther'"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="''"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="."/>
		<resource xmlns="http://datacite.org/schema/kernel-4">
			<xsl:attribute name="xsi:schemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance">http://datacite.org/schema/kernel-4 file:///C:/xampp/htdocs/mde/schemas/DataCite/DataCiteSchema45.xsd</xsl:attribute>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var2_cur" select="."/>
				<identifier>
					<xsl:attribute name="identifierType" namespace="">DOI</xsl:attribute>
					<xsl:value-of select="*[local-name()='doi' and namespace-uri()='']"/>
				</identifier>
				<creators>
					<creator>
						<creatorName>
							<xsl:attribute name="nameType" namespace="">Personal</xsl:attribute>
							<xsl:attribute name="xml:lang">en</xsl:attribute>
							<xsl:value-of select="concat(*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()=''])"/>
						</creatorName>
						<givenName>
							<xsl:value-of select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()='']"/>
						</givenName>
						<familyName>
							<xsl:value-of select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()='']"/>
						</familyName>
						<nameIdentifier>
							<xsl:attribute name="nameIdentifierScheme" namespace="">ORCID</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">https://orcid.org</xsl:attribute>
							<xsl:value-of select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='orcid' and namespace-uri()='']"/>
						</nameIdentifier>
						<xsl:for-each select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
							<xsl:variable name="var3_cur" select="."/>
							<affiliation>
								<xsl:attribute name="affiliationIdentifier" namespace="">
									<xsl:value-of select="*[local-name()='rorId' and namespace-uri()='']"/>
								</xsl:attribute>
								<xsl:attribute name="affiliationIdentifierScheme" namespace="">ROR</xsl:attribute>
								<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
								<xsl:value-of select="*[local-name()='name' and namespace-uri()='']"/>
							</affiliation>
						</xsl:for-each>
					</creator>
				</creators>
				<titles>
					<title>
						<xsl:choose>
							<xsl:when test="contains(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], ' ')">
								<xsl:if test="not(contains(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], 'Main Title'))">
									<xsl:attribute name="titleType" namespace="">
										<xsl:value-of select="concat(substring-before(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], ' '), substring-after(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], ' '))"/>
									</xsl:attribute>
								</xsl:if>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="titleType" namespace="">
									<xsl:value-of select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()='']"/>
								</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:attribute name="xml:lang">en</xsl:attribute>
						<xsl:value-of select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']"/>
					</title>
				</titles>
				<publisher>
					<xsl:attribute name="publisherIdentifier" namespace="">https://ror.org/04z8jg394</xsl:attribute>
					<xsl:attribute name="publisherIdentifierScheme" namespace="">ROR</xsl:attribute>
					<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
					<xsl:attribute name="xml:lang">en</xsl:attribute>
					<xsl:value-of select="'GFZ Data Services'"/>
				</publisher>
				<publicationYear>
					<xsl:value-of select="number(*[local-name()='year' and namespace-uri()=''])"/>
				</publicationYear>
				<resourceType>
					<xsl:attribute name="resourceTypeGeneral" namespace="">
						<xsl:value-of select="*[local-name()='ResourceType' and namespace-uri()='']/*[local-name()='resource_type_general' and namespace-uri()='']"/>
					</xsl:attribute>
					<xsl:value-of select="'Dataset'"/>
				</resourceType>
				<subjects>
					<xsl:for-each select="*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
						<xsl:variable name="var4_cur" select="."/>
						<subject>
							<xsl:attribute name="subjectScheme" namespace="">
								<xsl:value-of select="*[local-name()='scheme' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">
								<xsl:value-of select="*[local-name()='schemeURI' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:attribute name="valueURI" namespace="">
								<xsl:value-of select="*[local-name()='valueURI' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:attribute name="xml:lang">
								<xsl:value-of select="*[local-name()='language' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:value-of select="*[local-name()='keyword' and namespace-uri()='']"/>
						</subject>
					</xsl:for-each>
					<subject>
						<xsl:value-of select="*[local-name()='FreeKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']/*[local-name()='free_keyword' and namespace-uri()='']"/>
					</subject>
				</subjects>
				<contributors>
					<contributor>
						<xsl:attribute name="contributorType" namespace="">
							<xsl:choose>
								<xsl:when test="contains(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''], ' ')">
									<xsl:value-of select="concat(substring-before(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''], ' '), substring-after(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''], ' '))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
						<contributorName>
							<xsl:attribute name="xml:lang">en</xsl:attribute>
							<xsl:value-of select="concat(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()=''])"/>
						</contributorName>
						<givenName>
							<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()='']"/>
						</givenName>
						<familyName>
							<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()='']"/>
						</familyName>
						<nameIdentifier>
							<xsl:attribute name="nameIdentifierScheme" namespace="">ORCID</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">https://orcid.org</xsl:attribute>
							<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='orcid' and namespace-uri()='']"/>
						</nameIdentifier>
						<affiliation>
							<xsl:attribute name="affiliationIdentifier" namespace="">
								<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']/*[local-name()='rorId' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:attribute name="affiliationIdentifierScheme" namespace="">ROR</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
							<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
						</affiliation>
					</contributor>
					<contributor>
						<xsl:attribute name="contributorType" namespace="">
							<xsl:value-of select="concat(substring-before(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']/*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''], ' '), substring-after(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']/*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''], ' '))"/>
						</xsl:attribute>
						<contributorName>
							<xsl:attribute name="nameType" namespace="">Organizational</xsl:attribute>
							<xsl:attribute name="xml:lang">en</xsl:attribute>
							<xsl:value-of select="concat(*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''], ', ', *[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']/*[local-name()='name' and namespace-uri()=''])"/>
						</contributorName>
						<affiliation>
							<xsl:attribute name="affiliationIdentifier" namespace="">
								<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']/*[local-name()='rorId' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:attribute name="affiliationIdentifierScheme" namespace="">ROR</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
							<xsl:value-of select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
						</affiliation>
					</contributor>
					<contributor>
						<xsl:attribute name="contributorType" namespace="">ContactPerson</xsl:attribute>
						<contributorName>
							<xsl:attribute name="nameType" namespace="">Personal</xsl:attribute>
							<xsl:attribute name="xml:lang">en</xsl:attribute>
							<xsl:value-of select="concat(*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()=''])"/>
						</contributorName>
						<givenName>
							<xsl:value-of select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()='']"/>
						</givenName>
						<familyName>
							<xsl:value-of select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()='']"/>
						</familyName>
						<xsl:for-each select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
							<xsl:variable name="var5_cur" select="."/>
							<affiliation>
								<xsl:attribute name="affiliationIdentifier" namespace="">
									<xsl:value-of select="*[local-name()='rorId' and namespace-uri()='']"/>
								</xsl:attribute>
								<xsl:attribute name="affiliationIdentifierScheme" namespace="">https://orcid.org</xsl:attribute>
								<xsl:attribute name="schemeURI" namespace="">https://orcid.org</xsl:attribute>
								<xsl:value-of select="*[local-name()='name' and namespace-uri()='']"/>
							</affiliation>
						</xsl:for-each>
					</contributor>
				</contributors>
				<dates>
					<date>
						<xsl:attribute name="dateType" namespace="">Created</xsl:attribute>
						<xsl:value-of select="*[local-name()='dateCreated' and namespace-uri()='']"/>
					</date>
					<date>
						<xsl:attribute name="dateType" namespace="">Available</xsl:attribute>
						<xsl:value-of select="*[local-name()='dateEmbargoUntil' and namespace-uri()='']"/>
					</date>
					<date>
						<xsl:attribute name="dateType" namespace="">Collected</xsl:attribute>
						<xsl:value-of select="translate(concat(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='dateTimeStart' and namespace-uri()=''], '/', *[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='dateTimeEnd' and namespace-uri()=''], *[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='timezone' and namespace-uri()='']), ' ', 'T')"/>
					</date>
				</dates>
				<language>
					<xsl:value-of select="*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']"/>
				</language>
				<relatedIdentifiers>
					<relatedIdentifier>
						<xsl:attribute name="relatedIdentifierType" namespace="">
							<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='IdentifierType' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
						</xsl:attribute>
						<xsl:attribute name="relationType" namespace="">
							<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='Relation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
						</xsl:attribute>
						<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='Identifier' and namespace-uri()='']"/>
					</relatedIdentifier>
				</relatedIdentifiers>
				<version>
					<xsl:value-of select="number(*[local-name()='version' and namespace-uri()=''])"/>
				</version>
				<rightsList>
					<rights>
						<xsl:attribute name="rightsURI" namespace="">
							<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsURI' and namespace-uri()='']"/>
						</xsl:attribute>
						<xsl:attribute name="rightsIdentifier" namespace="">
							<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']"/>
						</xsl:attribute>
						<xsl:attribute name="rightsIdentifierScheme" namespace="">SPDX</xsl:attribute>
						<xsl:attribute name="schemeURI" namespace="">https://spdx.org/licenses/</xsl:attribute>
						<xsl:attribute name="xml:lang">en</xsl:attribute>
						<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']"/>
					</rights>
				</rightsList>
				<descriptions>
					<xsl:for-each select="*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()='']">
						<xsl:variable name="var6_cur" select="."/>
						<description>
							<xsl:variable name="var7_nested">
								<xsl:call-template name="vmf:vmf1_inputtoresult">
									<xsl:with-param name="input" select="string(*[local-name()='type' and namespace-uri()=''])"/>
								</xsl:call-template>
							</xsl:variable>
							<xsl:if test="string($var7_nested)">
								<xsl:attribute name="descriptionType" namespace="">
									<xsl:variable name="var8_nested">
										<xsl:call-template name="vmf:vmf1_inputtoresult">
											<xsl:with-param name="input" select="string(*[local-name()='type' and namespace-uri()=''])"/>
										</xsl:call-template>
									</xsl:variable>
									<xsl:value-of select="substring($var8_nested, 2)"/>
								</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="xml:lang">en</xsl:attribute>
							<xsl:value-of select="*[local-name()='description' and namespace-uri()='']"/>
						</description>
					</xsl:for-each>
				</descriptions>
				<geoLocations>
					<geoLocation>
						<geoLocationPlace>
							<xsl:value-of select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()='']"/>
						</geoLocationPlace>
						<xsl:if test="(((true() and true()) and false()) and false())">
							<geoLocationPoint>
								<pointLongitude>
									<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMin' and namespace-uri()=''])"/>
								</pointLongitude>
								<pointLatitude>
									<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMin' and namespace-uri()=''])"/>
								</pointLatitude>
							</geoLocationPoint>
						</xsl:if>
						<geoLocationBox>
							<xsl:if test="(((true() and true()) and true()) and true())">
								<westBoundLongitude>
									<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMin' and namespace-uri()=''])"/>
								</westBoundLongitude>
							</xsl:if>
							<xsl:if test="(((true() and true()) and true()) and true())">
								<eastBoundLongitude>
									<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMax' and namespace-uri()=''])"/>
								</eastBoundLongitude>
							</xsl:if>
							<xsl:if test="(((true() and true()) and true()) and true())">
								<southBoundLatitude>
									<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMin' and namespace-uri()=''])"/>
								</southBoundLatitude>
							</xsl:if>
							<xsl:if test="(((true() and true()) and true()) and true())">
								<northBoundLatitude>
									<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMax' and namespace-uri()=''])"/>
								</northBoundLatitude>
							</xsl:if>
						</geoLocationBox>
					</geoLocation>
				</geoLocations>
				<fundingReferences>
					<fundingReference>
						<funderName>
							<xsl:value-of select="*[local-name()='FundingReferences' and namespace-uri()='']/*[local-name()='FundingReference' and namespace-uri()='']/*[local-name()='funder' and namespace-uri()='']"/>
						</funderName>
						<funderIdentifier>
							<xsl:attribute name="funderIdentifierType" namespace="">
								<xsl:value-of select="*[local-name()='FundingReferences' and namespace-uri()='']/*[local-name()='FundingReference' and namespace-uri()='']/*[local-name()='funderidtyp' and namespace-uri()='']"/>
							</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">https://www.crossref.org/services/funder-registry/</xsl:attribute>
							<xsl:value-of select="*[local-name()='FundingReferences' and namespace-uri()='']/*[local-name()='FundingReference' and namespace-uri()='']/*[local-name()='funderid' and namespace-uri()='']"/>
						</funderIdentifier>
						<awardNumber>
							<xsl:value-of select="*[local-name()='FundingReferences' and namespace-uri()='']/*[local-name()='FundingReference' and namespace-uri()='']/*[local-name()='grantnumber' and namespace-uri()='']"/>
						</awardNumber>
						<awardTitle>
							<xsl:value-of select="*[local-name()='FundingReferences' and namespace-uri()='']/*[local-name()='FundingReference' and namespace-uri()='']/*[local-name()='grantname' and namespace-uri()='']"/>
						</awardTitle>
					</fundingReference>
				</fundingReferences>
			</xsl:for-each>
		</resource>
	</xsl:template>
</xsl:stylesheet>
