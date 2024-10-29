<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="." />
		<resource xmlns="http://datacite.org/schema/kernel-4">
			<xsl:attribute name="xsi:schemaLocation"
				namespace="http://www.w3.org/2001/XMLSchema-instance">http://datacite.org/schema/kernel-4
				file:///C:/xampp2/htdocs/mde-msl/schemas/DataCite/DataCiteSchema45.xsd</xsl:attribute>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var2_cur" select="." />
				<identifier>
					<xsl:attribute name="identifierType" namespace="">DOI</xsl:attribute>
					<xsl:value-of
						select="substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/')" />
				</identifier>
				<creators>
					<xsl:for-each
						select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']">
						<xsl:variable name="var3_cur" select="." />
						<creator>
							<creatorName>
								<xsl:attribute name="nameType" namespace="">Personal</xsl:attribute>
								<xsl:value-of
									select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])" />
							</creatorName>
							<givenName>
								<xsl:value-of
									select="*[local-name()='givenname' and namespace-uri()='']" />
							</givenName>
							<familyName>
								<xsl:value-of
									select="*[local-name()='familyname' and namespace-uri()='']" />
							</familyName>
							<nameIdentifier>
								<xsl:attribute name="nameIdentifierScheme" namespace="">ORCID</xsl:attribute>
								<xsl:attribute name="schemeURI" namespace="">https://orcid.org/</xsl:attribute>
								<xsl:value-of
									select="*[local-name()='orcid' and namespace-uri()='']" />
							</nameIdentifier>
							<xsl:for-each
								select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
								<xsl:variable name="var4_cur" select="." />
								<affiliation>
									<xsl:attribute name="affiliationIdentifier" namespace="">
										<xsl:value-of
											select="concat('https://ror.org/', *[local-name()='rorId' and namespace-uri()=''])" />
									</xsl:attribute>
									<xsl:attribute name="affiliationIdentifierScheme" namespace="">
										ROR</xsl:attribute>
									<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
									<xsl:value-of
										select="*[local-name()='name' and namespace-uri()='']" />
								</affiliation>
							</xsl:for-each>
						</creator>
					</xsl:for-each>
				</creators>
				<titles>
					<xsl:for-each
						select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']">
						<xsl:variable name="var5_cur" select="." />
						<title>
							<xsl:if
								test="not(contains(*[local-name()='type' and namespace-uri()=''], 'Main Title'))">
								<xsl:attribute name="titleType" namespace="">
									<xsl:value-of
										select="concat(substring-before(*[local-name()='type' and namespace-uri()=''], ' '), substring-after(*[local-name()='type' and namespace-uri()=''], ' '))" />
								</xsl:attribute>
							</xsl:if>
							<xsl:if
								test="contains(*[local-name()='type' and namespace-uri()=''], 'Main Title')">
								<xsl:attribute name="xml:lang">
									<xsl:value-of
										select="$var2_cur/*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']" />
								</xsl:attribute>
							</xsl:if>
							<xsl:value-of select="*[local-name()='text' and namespace-uri()='']" />
						</title>
					</xsl:for-each>
				</titles>
				<publisher>
					<xsl:attribute name="xml:lang">en</xsl:attribute>
					<xsl:value-of select="'GFZ Data Services'" />
				</publisher>
				<publicationYear>
					<xsl:value-of select="*[local-name()='year' and namespace-uri()='']" />
				</publicationYear>
				<resourceType>
					<xsl:attribute name="resourceTypeGeneral" namespace="">
						<xsl:value-of
							select="*[local-name()='ResourceType' and namespace-uri()='']/*[local-name()='resource_type_general' and namespace-uri()='']" />
					</xsl:attribute>
					<xsl:value-of select="'Dataset'" />
				</resourceType>
				<subjects>
					<subject>
						<xsl:value-of select="'EPOS'" />
					</subject>
					<subject>
						<xsl:value-of select="'multi-scale laboratories'" />
					</subject>
					<xsl:for-each
						select="*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
						<xsl:variable name="var6_cur" select="." />
						<subject>
							<xsl:attribute name="subjectScheme" namespace="">
								<xsl:value-of
									select="*[local-name()='scheme' and namespace-uri()='']" />
							</xsl:attribute>
							<xsl:attribute name="schemeURI" namespace="">
								<xsl:value-of
									select="*[local-name()='schemeURI' and namespace-uri()='']" />
							</xsl:attribute>
							<xsl:attribute name="valueURI" namespace="">
								<xsl:value-of
									select="*[local-name()='valueURI' and namespace-uri()='']" />
							</xsl:attribute>
							<xsl:attribute name="xml:lang">
								<xsl:value-of
									select="*[local-name()='language' and namespace-uri()='']" />
							</xsl:attribute>
							<xsl:value-of select="*[local-name()='keyword' and namespace-uri()='']" />
						</subject>
					</xsl:for-each>
					<xsl:for-each
						select="*[local-name()='FreeKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
						<xsl:variable name="var7_cur" select="." />
						<subject>
							<xsl:value-of
								select="*[local-name()='free_keyword' and namespace-uri()='']" />
						</subject>
					</xsl:for-each>
				</subjects>
				<contributors>
					<xsl:for-each
						select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']">
						<xsl:variable name="var8_cur" select="." />
						<contributor>
							<xsl:attribute name="contributorType" namespace="">ContactPerson</xsl:attribute>
							<contributorName>
								<xsl:value-of
									select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])" />
							</contributorName>
							<givenName>
								<xsl:value-of
									select="*[local-name()='givenname' and namespace-uri()='']" />
							</givenName>
							<familyName>
								<xsl:value-of
									select="*[local-name()='familyname' and namespace-uri()='']" />
							</familyName>
							<xsl:for-each
								select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
								<xsl:variable name="var9_cur" select="." />
								<affiliation>
									<xsl:attribute name="affiliationIdentifier" namespace="">
										<xsl:value-of
											select="concat('https://ror.org/', *[local-name()='rorId' and namespace-uri()=''])" />
									</xsl:attribute>
									<xsl:attribute name="affiliationIdentifierScheme" namespace="">
										ROR</xsl:attribute>
									<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
									<xsl:value-of
										select="*[local-name()='name' and namespace-uri()='']" />
								</affiliation>
							</xsl:for-each>
						</contributor>
					</xsl:for-each>
					<xsl:for-each
						select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Persons' and namespace-uri()='']/*[local-name()='Person' and namespace-uri()='']">
						<xsl:variable name="var10_cur" select="." />
						<contributor>
							<xsl:for-each
								select="*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']">
								<xsl:variable name="var11_cur" select="." />
								<xsl:attribute name="contributorType" namespace="">
									<xsl:choose>
										<xsl:when
											test="contains(*[local-name()='name' and namespace-uri()=''], ' ')">
											<xsl:value-of
												select="concat(substring-before(*[local-name()='name' and namespace-uri()=''], ' '), substring-after(*[local-name()='name' and namespace-uri()=''], ' '))" />
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of
												select="*[local-name()='name' and namespace-uri()='']" />
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</xsl:for-each>
							<contributorName>
								<xsl:attribute name="nameType" namespace="">Personal</xsl:attribute>
								<xsl:value-of
									select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])" />
							</contributorName>
							<givenName>
								<xsl:value-of
									select="*[local-name()='givenname' and namespace-uri()='']" />
							</givenName>
							<familyName>
								<xsl:value-of
									select="*[local-name()='familyname' and namespace-uri()='']" />
							</familyName>
							<nameIdentifier>
								<xsl:attribute name="nameIdentifierScheme" namespace="">ORCID</xsl:attribute>
								<xsl:attribute name="schemeURI" namespace="">https://orcid.org/</xsl:attribute>
								<xsl:value-of
									select="*[local-name()='orcid' and namespace-uri()='']" />
							</nameIdentifier>
							<xsl:for-each
								select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
								<xsl:variable name="var12_cur" select="." />
								<affiliation>
									<xsl:attribute name="affiliationIdentifier" namespace="">
										<xsl:value-of
											select="concat('https://ror.org/', *[local-name()='rorId' and namespace-uri()=''])" />
									</xsl:attribute>
									<xsl:attribute name="affiliationIdentifierScheme" namespace="">
										ROR</xsl:attribute>
									<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
									<xsl:value-of
										select="*[local-name()='name' and namespace-uri()='']" />
								</affiliation>
							</xsl:for-each>
						</contributor>
					</xsl:for-each>
					<xsl:for-each
						select="*[local-name()='Contributors' and namespace-uri()='']/*[local-name()='Institutions' and namespace-uri()='']/*[local-name()='Institution' and namespace-uri()='']">
						<xsl:variable name="var13_cur" select="." />
						<contributor>
							<xsl:for-each
								select="*[local-name()='Roles' and namespace-uri()='']/*[local-name()='Role' and namespace-uri()='']">
								<xsl:variable name="var14_cur" select="." />
								<xsl:attribute name="contributorType" namespace="">
									<xsl:choose>
										<xsl:when
											test="contains(*[local-name()='name' and namespace-uri()=''], ' ')">
											<xsl:value-of
												select="concat(substring-before(*[local-name()='name' and namespace-uri()=''], ' '), substring-after(*[local-name()='name' and namespace-uri()=''], ' '))" />
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of
												select="*[local-name()='name' and namespace-uri()='']" />
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
							</xsl:for-each>
							<contributorName>
								<xsl:attribute name="nameType" namespace="">Organizational</xsl:attribute>
								<xsl:value-of select="*[local-name()='name' and namespace-uri()='']" />
							</contributorName>
							<xsl:for-each
								select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
								<xsl:variable name="var15_cur" select="." />
								<affiliation>
									<xsl:attribute name="affiliationIdentifier" namespace="">
										<xsl:value-of
											select="concat('https://ror.org/', *[local-name()='rorId' and namespace-uri()=''])" />
									</xsl:attribute>
									<xsl:attribute name="affiliationIdentifierScheme" namespace="">
										ROR</xsl:attribute>
									<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
									<xsl:value-of
										select="*[local-name()='name' and namespace-uri()='']" />
								</affiliation>
							</xsl:for-each>
						</contributor>
					</xsl:for-each>
					<xsl:for-each
						select="*[local-name()='OriginatingLaboratories' and namespace-uri()='']/*[local-name()='OriginatingLaboratory' and namespace-uri()='']">
						<xsl:variable name="var16_cur" select="." />
						<contributor>
							<xsl:attribute name="contributorType" namespace="">HostingInstitution</xsl:attribute>
							<contributorName>
								<xsl:value-of
									select="*[local-name()='laboratoryname' and namespace-uri()='']" />
							</contributorName>
							<nameIdentifier>
								<xsl:attribute name="nameIdentifierScheme" namespace="">labid</xsl:attribute>
								<xsl:value-of
									select="*[local-name()='labId' and namespace-uri()='']" />
							</nameIdentifier>
							<xsl:for-each
								select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
								<xsl:variable name="var17_cur" select="." />
								<affiliation>
									<xsl:attribute name="affiliationIdentifier" namespace="">
										<xsl:value-of
											select="concat('https://ror.org/', *[local-name()='rorId' and namespace-uri()=''])" />
									</xsl:attribute>
									<xsl:attribute name="affiliationIdentifierScheme" namespace="">
										ROR</xsl:attribute>
									<xsl:attribute name="schemeURI" namespace="">https://ror.org</xsl:attribute>
									<xsl:value-of
										select="*[local-name()='name' and namespace-uri()='']" />
								</affiliation>
							</xsl:for-each>
						</contributor>
					</xsl:for-each>
				</contributors>
				<dates>
					<xsl:for-each select="*[local-name()='dateEmbargoUntil' and namespace-uri()='']">
						<xsl:variable name="var18_cur" select="." />
						<date>
							<xsl:attribute name="dateType" namespace="">Available</xsl:attribute>
							<xsl:value-of select="." />
						</date>
					</xsl:for-each>
					<xsl:for-each
						select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
						<xsl:variable name="var19_cur" select="." />
						<date>
							<xsl:attribute name="dateType" namespace="">Collected</xsl:attribute>
							<xsl:value-of
								select="concat(*[local-name()='dateTimeStart' and namespace-uri()=''], '/', *[local-name()='dateTimeEnd' and namespace-uri()=''])" />
						</date>
					</xsl:for-each>
					<date>
						<xsl:attribute name="dateType" namespace="">Created</xsl:attribute>
						<xsl:value-of select="*[local-name()='dateCreated' and namespace-uri()='']" />
					</date>
				</dates>
				<language>
					<xsl:value-of
						select="*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']" />
				</language>
				<relatedIdentifiers>
					<xsl:for-each
						select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']">
						<xsl:variable name="var20_cur" select="." />
						<relatedIdentifier>
							<xsl:attribute name="relatedIdentifierType" namespace="">
								<xsl:value-of
									select="*[local-name()='IdentifierType' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']" />
							</xsl:attribute>
							<xsl:attribute name="relationType" namespace="">
								<xsl:value-of
									select="*[local-name()='Relation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']" />
							</xsl:attribute>
							<xsl:value-of
								select="*[local-name()='Identifier' and namespace-uri()='']" />
						</relatedIdentifier>
					</xsl:for-each>
				</relatedIdentifiers>
				<xsl:for-each
					select="(./*[local-name()='version' and namespace-uri()=''])[(string-length(string(.)) &gt; 0)]">
					<xsl:variable name="var21_filter" select="." />
					<version>
						<xsl:value-of select="." />
					</version>
				</xsl:for-each>
				<rightsList>
					<rights>
						<xsl:attribute name="rightsURI" namespace="">
							<xsl:value-of
								select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsURI' and namespace-uri()='']" />
						</xsl:attribute>
						<xsl:attribute name="rightsIdentifier" namespace="">
							<xsl:value-of
								select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']" />
						</xsl:attribute>
						<xsl:attribute name="rightsIdentifierScheme" namespace="">SPDX</xsl:attribute>
						<xsl:attribute name="schemeURI" namespace="">https://spdx.org/licenses/</xsl:attribute>
						<xsl:attribute name="xml:lang">en</xsl:attribute>
						<xsl:value-of
							select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']" />
					</rights>
				</rightsList>
				<descriptions>
					<xsl:for-each
						select="*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()='']">
						<xsl:variable name="var22_cur" select="." />
						<description>
							<xsl:attribute name="descriptionType" namespace="">
								<xsl:choose>
									<xsl:when
										test="contains(*[local-name()='type' and namespace-uri()=''], ' ')">
										<xsl:value-of
											select="concat(substring-before(*[local-name()='type' and namespace-uri()=''], ' '), substring-after(*[local-name()='type' and namespace-uri()=''], ' '))" />
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of
											select="*[local-name()='type' and namespace-uri()='']" />
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							<xsl:attribute name="xml:lang">en</xsl:attribute>
							<xsl:value-of
								select="*[local-name()='description' and namespace-uri()='']" />
						</description>
					</xsl:for-each>
				</descriptions>
				<geoLocations>
					<geoLocation>
						<xsl:for-each
							select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
							<xsl:variable name="var23_cur" select="." />
							<geoLocationPlace>
								<xsl:value-of
									select="*[local-name()='Description' and namespace-uri()='']" />
							</geoLocationPlace>
						</xsl:for-each>
						<geoLocationPoint>
							<xsl:for-each
								select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
								<xsl:variable name="var24_cur" select="." />
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var25_cur" select="." />
									<xsl:for-each
										select="($var24_cur/*[local-name()='latitudeMax' and namespace-uri()=''])[not(((((string-length(string(number($var25_cur))) &gt; 0) and (string-length(string(number(.))) &gt; 0)) and (number(.) = number(.))) and (number($var25_cur) = number($var25_cur))))]">
										<xsl:variable name="var26_filter" select="." />
										<pointLongitude>
											<xsl:value-of
												select="number($var24_cur/*[local-name()='longitudeMin' and namespace-uri()=''])" />
										</pointLongitude>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
							<xsl:for-each
								select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
								<xsl:variable name="var27_cur" select="." />
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var28_cur" select="." />
									<xsl:for-each
										select="($var27_cur/*[local-name()='latitudeMax' and namespace-uri()=''])[not(((((string-length(string(number($var28_cur))) &gt; 0) and (string-length(string(number(.))) &gt; 0)) and (number(.) = number(.))) and (number($var28_cur) = number($var28_cur))))]">
										<xsl:variable name="var29_filter" select="." />
										<pointLatitude>
											<xsl:value-of
												select="number($var27_cur/*[local-name()='latitudeMin' and namespace-uri()=''])" />
										</pointLatitude>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
						</geoLocationPoint>
						<geoLocationBox>
							<xsl:for-each
								select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
								<xsl:variable name="var30_cur" select="." />
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var31_cur" select="." />
									<xsl:for-each
										select="($var30_cur/*[local-name()='latitudeMax' and namespace-uri()=''])[((((string-length(string(number($var31_cur))) &gt; 0) and (string-length(string(number(.))) &gt; 0)) and (number(.) = number(.))) and (number($var31_cur) = number($var31_cur)))]">
										<xsl:variable name="var32_filter" select="." />
										<westBoundLongitude>
											<xsl:value-of
												select="number($var30_cur/*[local-name()='longitudeMin' and namespace-uri()=''])" />
										</westBoundLongitude>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
							<xsl:for-each
								select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
								<xsl:variable name="var33_cur" select="." />
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var34_cur" select="." />
									<xsl:for-each
										select="($var33_cur/*[local-name()='latitudeMax' and namespace-uri()=''])[((((string-length(string(number($var34_cur))) &gt; 0) and (string-length(string(number(.))) &gt; 0)) and (number(.) = number(.))) and (number($var34_cur) = number($var34_cur)))]">
										<xsl:variable name="var35_filter" select="." />
										<eastBoundLongitude>
											<xsl:value-of select="number($var34_cur)" />
										</eastBoundLongitude>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
							<xsl:for-each
								select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
								<xsl:variable name="var36_cur" select="." />
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var37_cur" select="." />
									<xsl:for-each
										select="($var36_cur/*[local-name()='latitudeMax' and namespace-uri()=''])[((((string-length(string(number($var37_cur))) &gt; 0) and (string-length(string(number(.))) &gt; 0)) and (number(.) = number(.))) and (number($var37_cur) = number($var37_cur)))]">
										<xsl:variable name="var38_filter" select="." />
										<southBoundLatitude>
											<xsl:value-of
												select="number($var36_cur/*[local-name()='latitudeMin' and namespace-uri()=''])" />
										</southBoundLatitude>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
							<xsl:for-each
								select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
								<xsl:variable name="var39_cur" select="." />
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var40_cur" select="." />
									<xsl:for-each
										select="($var39_cur/*[local-name()='latitudeMax' and namespace-uri()=''])[((((string-length(string(number($var40_cur))) &gt; 0) and (string-length(string(number(.))) &gt; 0)) and (number(.) = number(.))) and (number($var40_cur) = number($var40_cur)))]">
										<xsl:variable name="var41_filter" select="." />
										<northBoundLatitude>
											<xsl:value-of select="number(.)" />
										</northBoundLatitude>
									</xsl:for-each>
								</xsl:for-each>
							</xsl:for-each>
						</geoLocationBox>
					</geoLocation>
				</geoLocations>
				<fundingReferences>
					<xsl:for-each
						select="*[local-name()='FundingReferences' and namespace-uri()='']/*[local-name()='FundingReference' and namespace-uri()='']">
						<xsl:variable name="var42_cur" select="." />
						<fundingReference>
							<funderName>
								<xsl:value-of
									select="*[local-name()='funder' and namespace-uri()='']" />
							</funderName>
							<funderIdentifier>
								<xsl:attribute name="funderIdentifierType" namespace="">
									<xsl:value-of
										select="*[local-name()='funderidtyp' and namespace-uri()='']" />
								</xsl:attribute>
								<xsl:attribute name="schemeURI" namespace="">
									https://www.crossref.org/services/funder-registry/</xsl:attribute>
								<xsl:value-of
									select="concat('http://dx.doi.org/10.13039/', number(*[local-name()='funderid' and namespace-uri()='']))" />
							</funderIdentifier>
							<awardNumber>
								<xsl:value-of
									select="*[local-name()='grantnumber' and namespace-uri()='']" />
							</awardNumber>
							<awardTitle>
								<xsl:value-of
									select="*[local-name()='grantname' and namespace-uri()='']" />
							</awardTitle>
						</fundingReference>
					</xsl:for-each>
				</fundingReferences>
			</xsl:for-each>
		</resource>
	</xsl:template>
</xsl:stylesheet>