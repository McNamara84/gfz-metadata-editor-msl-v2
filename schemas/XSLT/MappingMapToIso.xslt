<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="."/>
		<MD_Metadata xmlns="http://www.isotc211.org/2005/gmd" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:gsr="http://www.isotc211.org/2005/gsr" xmlns:gss="http://www.isotc211.org/2005/gss" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml" xmlns:xlink="http://www.w3.org/1999/xlink">
			<xsl:attribute name="xsi:schemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance">http://www.isotc211.org/2005/gmd file:///C:/xampp/htdocs/mde/schemas/ISO/gmd.xsd</xsl:attribute>
			<fileIdentifier>
				<xsl:if test="*[local-name()='Resource' and namespace-uri()='']">
					<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
						<xsl:variable name="var2_cur" select="."/>
						<gco:CharacterString>
							<xsl:value-of select="*[local-name()='doi' and namespace-uri()='']"/>
						</gco:CharacterString>
					</xsl:for-each>
				</xsl:if>
			</fileIdentifier>
			<language>
				<LanguageCode>
					<xsl:attribute name="codeList" namespace="">http://www.loc.gov/standards/iso639-2/</xsl:attribute>
					<xsl:attribute name="codeListValue" namespace="">eng</xsl:attribute>
					<xsl:value-of select="'eng'"/>
				</LanguageCode>
				<MD_CharacterSetCode>
					<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/codeList.xml#MD_CharacterSetCode</xsl:attribute>
					<xsl:attribute name="codeListValue" namespace="">utf8</xsl:attribute>
				</MD_CharacterSetCode>
			</language>
			<contact>
				<CI_ResponsibleParty>
					<organisationName>
						<gco:CharacterString>
							<xsl:value-of select="'GFZ German Research Centre for Geosciences'"/>
						</gco:CharacterString>
					</organisationName>
					<contactInfo>
						<CI_Contact>
							<onlineResource>
								<CI_OnlineResource>
									<linkage>
										<URL>
											<xsl:value-of select="'https://www.gfz-potsdam.de/'"/>
										</URL>
									</linkage>
									<function>
										<CI_OnLineFunctionCode>
											<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode</xsl:attribute>
											<xsl:attribute name="codeListValue" namespace="">information</xsl:attribute>
											<xsl:value-of select="'information'"/>
										</CI_OnLineFunctionCode>
									</function>
								</CI_OnlineResource>
							</onlineResource>
						</CI_Contact>
					</contactInfo>
					<role>
						<CI_RoleCode>
							<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode</xsl:attribute>
							<xsl:attribute name="codeListValue" namespace="">pointOfContact</xsl:attribute>
							<xsl:value-of select="'pointOfContact'"/>
						</CI_RoleCode>
					</role>
				</CI_ResponsibleParty>
			</contact>
			<identificationInfo>
				<MD_DataIdentification>
					<citation>
						<CI_Citation>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
								<xsl:variable name="var3_cur" select="."/>
								<title>
									<xsl:if test="contains(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], 'Main Title')">
										<gco:CharacterString>
											<xsl:value-of select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']"/>
										</gco:CharacterString>
									</xsl:if>
								</title>
							</xsl:for-each>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
								<xsl:variable name="var4_cur" select="."/>
								<alternateTitle>
									<xsl:if test="contains(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], 'Alternative Title')">
										<gco:CharacterString>
											<xsl:value-of select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']"/>
										</gco:CharacterString>
									</xsl:if>
								</alternateTitle>
							</xsl:for-each>
							<date>
								<CI_Date>
									<xsl:if test="*[local-name()='Resource' and namespace-uri()='']">
										<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
											<xsl:variable name="var5_cur" select="."/>
											<date/>
										</xsl:for-each>
									</xsl:if>
									<dateType>
										<CI_DateTypeCode>
											<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode</xsl:attribute>
											<xsl:attribute name="codeListValue" namespace="">
												<xsl:choose>
													<xsl:when test="*[local-name()='Resource' and namespace-uri()='']">
														<xsl:value-of select="'creation'"/>
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of select="'revision'"/>
													</xsl:otherwise>
												</xsl:choose>
											</xsl:attribute>
											<xsl:choose>
												<xsl:when test="*[local-name()='Resource' and namespace-uri()='']">
													<xsl:value-of select="'creation'"/>
												</xsl:when>
												<xsl:otherwise>
													<xsl:value-of select="'revision'"/>
												</xsl:otherwise>
											</xsl:choose>
										</CI_DateTypeCode>
									</dateType>
								</CI_Date>
							</date>
							<identifier>
								<MD_Identifier>
									<code>
										<xsl:if test="*[local-name()='Resource' and namespace-uri()='']">
											<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
												<xsl:variable name="var6_cur" select="."/>
												<gco:CharacterString>
													<xsl:value-of select="*[local-name()='doi' and namespace-uri()='']"/>
												</gco:CharacterString>
											</xsl:for-each>
										</xsl:if>
									</code>
								</MD_Identifier>
							</identifier>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
								<xsl:variable name="var7_cur" select="."/>
								<citedResponsibleParty>
									<xsl:attribute name="xlink:href">
										<xsl:value-of select="concat('https://orcid.org/', *[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='orcid' and namespace-uri()=''])"/>
									</xsl:attribute>
									<CI_ResponsibleParty>
										<individualName>
											<gco:CharacterString>
												<xsl:value-of select="concat(*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()=''])"/>
											</gco:CharacterString>
										</individualName>
										<xsl:for-each select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
											<xsl:variable name="var8_cur" select="."/>
											<organisationName>
												<gco:CharacterString>
													<xsl:value-of select="*[local-name()='name' and namespace-uri()='']"/>
												</gco:CharacterString>
											</organisationName>
										</xsl:for-each>
									</CI_ResponsibleParty>
								</citedResponsibleParty>
							</xsl:for-each>
						</CI_Citation>
					</citation>
					<abstract>
						<xsl:for-each select="(./*[local-name()='Resource' and namespace-uri()='']/*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Abstract')]">
							<xsl:variable name="var9_filter" select="."/>
							<gco:CharacterString>
								<xsl:value-of select="*[local-name()='description' and namespace-uri()='']"/>
							</gco:CharacterString>
						</xsl:for-each>
					</abstract>
					<status>
						<MD_ProgressCode>
							<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode</xsl:attribute>
							<xsl:attribute name="codeListValue" namespace="">Complete</xsl:attribute>
							<xsl:value-of select="'Complete'"/>
						</MD_ProgressCode>
					</status>
					<pointOfContact>
						<CI_ResponsibleParty>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
								<xsl:variable name="var10_cur" select="."/>
								<individualName>
									<gco:CharacterString>
										<xsl:value-of select="concat(*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()=''])"/>
									</gco:CharacterString>
								</individualName>
							</xsl:for-each>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']/*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
								<xsl:variable name="var11_cur" select="."/>
								<organisationName>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='name' and namespace-uri()='']"/>
									</gco:CharacterString>
								</organisationName>
							</xsl:for-each>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
								<xsl:variable name="var12_cur" select="."/>
								<positionName>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='position' and namespace-uri()='']"/>
									</gco:CharacterString>
								</positionName>
							</xsl:for-each>
							<contactInfo>
								<CI_Contact>
									<address>
										<CI_Address>
											<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
												<xsl:variable name="var13_cur" select="."/>
												<electronicMailAddress>
													<gco:CharacterString>
														<xsl:value-of select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='email' and namespace-uri()='']"/>
													</gco:CharacterString>
												</electronicMailAddress>
											</xsl:for-each>
										</CI_Address>
									</address>
									<onlineResource>
										<CI_OnlineResource>
											<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
												<xsl:variable name="var14_cur" select="."/>
												<linkage>
													<URL>
														<xsl:value-of select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='website' and namespace-uri()='']"/>
													</URL>
												</linkage>
											</xsl:for-each>
											<function>
												<CI_OnLineFunctionCode>
													<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode</xsl:attribute>
													<xsl:attribute name="codeListValue" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode_information</xsl:attribute>
													<xsl:value-of select="'information'"/>
												</CI_OnLineFunctionCode>
											</function>
										</CI_OnlineResource>
									</onlineResource>
								</CI_Contact>
							</contactInfo>
							<role>
								<CI_RoleCode>
									<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode</xsl:attribute>
									<xsl:attribute name="codeListValue" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode_information</xsl:attribute>
									<xsl:value-of select="'pointOfContact'"/>
								</CI_RoleCode>
							</role>
						</CI_ResponsibleParty>
					</pointOfContact>
					<descriptiveKeywords>
						<MD_Keywords>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']/*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
								<xsl:variable name="var15_cur" select="."/>
								<keyword>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='keyword' and namespace-uri()='']"/>
									</gco:CharacterString>
								</keyword>
							</xsl:for-each>
							<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
								<xsl:variable name="var16_cur" select="."/>
								<keyword>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='FreeKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']/*[local-name()='free_keyword' and namespace-uri()='']"/>
									</gco:CharacterString>
								</keyword>
							</xsl:for-each>
							<thesaurusName>
								<CI_Citation>
									<title>
										<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']/*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
											<xsl:variable name="var17_cur" select="."/>
											<gco:CharacterString>
												<xsl:value-of select="*[local-name()='scheme' and namespace-uri()='']"/>
											</gco:CharacterString>
										</xsl:for-each>
									</title>
								</CI_Citation>
							</thesaurusName>
						</MD_Keywords>
					</descriptiveKeywords>
					<resourceConstraints>
						<MD_Constraints>
							<useLimitation>
								<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
									<xsl:variable name="var18_cur" select="."/>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']"/>
									</gco:CharacterString>
								</xsl:for-each>
							</useLimitation>
						</MD_Constraints>
						<MD_LegalConstraints>
							<useLimitation>
								<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
									<xsl:variable name="var19_cur" select="."/>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']"/>
									</gco:CharacterString>
								</xsl:for-each>
							</useLimitation>
						</MD_LegalConstraints>
					</resourceConstraints>
					<aggregationInfo>
						<MD_AggregateInformation>
							<aggregateDataSetIdentifier>
								<RS_Identifier>
									<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
										<xsl:variable name="var20_cur" select="."/>
										<code>
											<gco:CharacterString>
												<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='Identifier' and namespace-uri()='']"/>
											</gco:CharacterString>
										</code>
									</xsl:for-each>
									<codeSpace>
										<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
											<xsl:variable name="var21_cur" select="."/>
											<gco:CharacterString>
												<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='IdentifierType' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
											</gco:CharacterString>
										</xsl:for-each>
									</codeSpace>
								</RS_Identifier>
							</aggregateDataSetIdentifier>
							<associationType>
								<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
									<xsl:variable name="var22_cur" select="."/>
									<DS_AssociationTypeCode>
										<xsl:attribute name="codeList" namespace="">http://datacite.org/schema/kernel-4</xsl:attribute>
										<xsl:attribute name="codeListValue" namespace="">
											<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='Relation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
										</xsl:attribute>
										<xsl:value-of select="*[local-name()='RelatedWorks' and namespace-uri()='']/*[local-name()='RelatedWork' and namespace-uri()='']/*[local-name()='Relation' and namespace-uri()='']/*[local-name()='name' and namespace-uri()='']"/>
									</DS_AssociationTypeCode>
								</xsl:for-each>
							</associationType>
						</MD_AggregateInformation>
					</aggregationInfo>
					<language/>
					<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
						<xsl:variable name="var23_cur" select="."/>
						<extent>
							<EX_Extent>
								<description>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()='']"/>
									</gco:CharacterString>
								</description>
								<geographicElement>
									<EX_GeographicBoundingBox>
										<westBoundLongitude>
											<gco:Decimal>
												<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMin' and namespace-uri()=''])"/>
											</gco:Decimal>
										</westBoundLongitude>
										<eastBoundLongitude>
											<gco:Decimal>
												<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMin' and namespace-uri()=''])"/>
											</gco:Decimal>
										</eastBoundLongitude>
										<southBoundLatitude>
											<gco:Decimal>
												<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMin' and namespace-uri()=''])"/>
											</gco:Decimal>
										</southBoundLatitude>
										<northBoundLatitude>
											<gco:Decimal>
												<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMin' and namespace-uri()=''])"/>
											</gco:Decimal>
										</northBoundLatitude>
									</EX_GeographicBoundingBox>
								</geographicElement>
								<temporalElement>
									<EX_TemporalExtent>
										<extent>
											<gml:TimePeriod>
												<xsl:attribute name="gml:id">ext-903</xsl:attribute>
												<gml:beginPosition>
													<xsl:value-of select="concat(concat(substring-before(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='dateTimeStart' and namespace-uri()=''], ' '), 'T', substring-after(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='dateTimeStart' and namespace-uri()=''], ' ')), *[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='timezone' and namespace-uri()=''])"/>
												</gml:beginPosition>
												<gml:endPosition>
													<xsl:value-of select="concat(concat(substring-before(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='dateTimeEnd' and namespace-uri()=''], ' '), 'T', substring-after(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='dateTimeEnd' and namespace-uri()=''], ' ')), *[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='timezone' and namespace-uri()=''])"/>
												</gml:endPosition>
											</gml:TimePeriod>
										</extent>
									</EX_TemporalExtent>
								</temporalElement>
							</EX_Extent>
						</extent>
					</xsl:for-each>
				</MD_DataIdentification>
			</identificationInfo>
			<distributionInfo>
				<MD_Distribution>
					<transferOptions>
						<MD_DigitalTransferOptions>
							<onLine>
								<CI_OnlineResource>
									<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
										<xsl:variable name="var24_cur" select="."/>
										<linkage>
											<URL>
												<xsl:value-of select="*[local-name()='doi' and namespace-uri()='']"/>
											</URL>
										</linkage>
									</xsl:for-each>
									<protocol>
										<gco:CharacterString>
											<xsl:value-of select="'WWW:LINK-1.0-http--link'"/>
										</gco:CharacterString>
									</protocol>
									<name>
										<gco:CharacterString>
											<xsl:value-of select="'Download'"/>
										</gco:CharacterString>
									</name>
									<description>
										<gco:CharacterString>
											<xsl:value-of select="'Download'"/>
										</gco:CharacterString>
									</description>
									<function>
										<CI_OnLineFunctionCode>
											<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode</xsl:attribute>
											<xsl:attribute name="codeListValue" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode_download</xsl:attribute>
											<xsl:value-of select="'download'"/>
										</CI_OnLineFunctionCode>
									</function>
								</CI_OnlineResource>
							</onLine>
						</MD_DigitalTransferOptions>
					</transferOptions>
				</MD_Distribution>
			</distributionInfo>
		</MD_Metadata>
	</xsl:template>
</xsl:stylesheet>
