<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="."/>
		<MD_Metadata xmlns="http://www.isotc211.org/2005/gmd" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:gsr="http://www.isotc211.org/2005/gsr" xmlns:gss="http://www.isotc211.org/2005/gss" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml" xmlns:xlink="http://www.w3.org/1999/xlink">
			<xsl:attribute name="xsi:schemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance">http://www.isotc211.org/2005/gmd file:///C:/xampp/htdocs/mde-msl/schemas/ISO/gmd.xsd</xsl:attribute>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var2_cur" select="."/>
				<fileIdentifier>
					<gco:CharacterString>
						<xsl:value-of select="concat('doi:', substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/'))"/>
					</gco:CharacterString>
				</fileIdentifier>
				<language>
					<gco:CharacterString>
						<xsl:value-of select="*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']"/>
					</gco:CharacterString>
					<LanguageCode>
						<xsl:attribute name="codeList" namespace="">http://www.loc.gov/standards/iso639-1/</xsl:attribute>
						<xsl:attribute name="codeListValue" namespace="">
							<xsl:value-of select="*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']"/>
						</xsl:attribute>
					</LanguageCode>
				</language>
				<characterSet>
					<MD_CharacterSetCode>
						<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/codeList.xml#MD_CharacterSetCode</xsl:attribute>
						<xsl:attribute name="codeListValue" namespace="">utf8</xsl:attribute>
					</MD_CharacterSetCode>
				</characterSet>
				<hierarchyLevel>
					<MD_ScopeCode>
						<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ScopeCode</xsl:attribute>
						<xsl:attribute name="codeListValue" namespace="">dataset</xsl:attribute>
					</MD_ScopeCode>
				</hierarchyLevel>
				<contact>
					<CI_ResponsibleParty>
						<organisationName>
							<gco:CharacterString>
								<xsl:value-of select="'GFZ German Research Center for Geosciences'"/>
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
				<dateStamp>
					<gco:Date>
						<xsl:value-of select="*[local-name()='currentDate' and namespace-uri()='']"/>
					</gco:Date>
				</dateStamp>
				<identificationInfo>
					<MD_DataIdentification>
						<citation>
							<CI_Citation>
								<title>
									<xsl:for-each select="(./*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Main Title')]">
										<xsl:variable name="var3_filter" select="."/>
										<gco:CharacterString>
											<xsl:value-of select="*[local-name()='text' and namespace-uri()='']"/>
										</gco:CharacterString>
									</xsl:for-each>
								</title>
								<alternateTitle>
									<xsl:variable name="var4_nested">
										<xsl:for-each select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']">
											<xsl:variable name="var5_cur" select="."/>
											<xsl:value-of select="number(contains(*[local-name()='type' and namespace-uri()=''], 'Alternative Title'))"/>
										</xsl:for-each>
									</xsl:variable>
									<xsl:if test="boolean(translate(normalize-space($var4_nested), ' 0', ''))">
										<gco:CharacterString>
											<xsl:for-each select="(./*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Alternative Title')]">
												<xsl:variable name="var6_filter" select="."/>
												<xsl:value-of select="*[local-name()='text' and namespace-uri()='']"/>
											</xsl:for-each>
										</gco:CharacterString>
									</xsl:if>
								</alternateTitle>
								<date>
									<CI_Date>
										<date>
											<gco:Date>
												<xsl:value-of select="*[local-name()='currentDate' and namespace-uri()='']"/>
											</gco:Date>
										</date>
										<dateType>
											<CI_DateTypeCode>
												<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode</xsl:attribute>
												<xsl:attribute name="codeListValue" namespace="">revision</xsl:attribute>
												<xsl:value-of select="'revision'"/>
											</CI_DateTypeCode>
										</dateType>
									</CI_Date>
								</date>
								<xsl:for-each select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']">
									<xsl:variable name="var7_cur" select="."/>
									<citedResponsibleParty>
										<xsl:attribute name="xlink:href">
											<xsl:value-of select="concat('http://orcid.org/', *[local-name()='orcid' and namespace-uri()=''])"/>
										</xsl:attribute>
										<CI_ResponsibleParty>
											<individualName>
												<gco:CharacterString>
													<xsl:value-of select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])"/>
												</gco:CharacterString>
											</individualName>
											<organisationName>
												<xsl:for-each select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
													<xsl:variable name="var8_cur" select="."/>
													<gco:CharacterString>
														<xsl:value-of select="*[local-name()='name' and namespace-uri()='']"/>
													</gco:CharacterString>
												</xsl:for-each>
											</organisationName>
											<role>
												<CI_RoleCode>
													<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode</xsl:attribute>
													<xsl:attribute name="codeListValue" namespace="">author</xsl:attribute>
													<xsl:value-of select="'author'"/>
												</CI_RoleCode>
											</role>
										</CI_ResponsibleParty>
									</citedResponsibleParty>
								</xsl:for-each>
							</CI_Citation>
						</citation>
						<abstract>
							<!-- FIX: Fehlende XSLT-Funktion in MapForce händisch nachgetragen -->
							<CharacterString>
								<xsl:for-each select="*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()='']">
									<xsl:if test="position() &gt; 1">
										<!-- Fügt einen Zeilenumbruch zwischen den Beschreibungen hinzu -->
										<xsl:text>&#xA;&#xA;</xsl:text>
									</xsl:if>
									<xsl:value-of select="*[local-name()='description' and namespace-uri()='']"/>
								</xsl:for-each>
							</CharacterString>
						</abstract>						
						<status>
							<MD_ProgressCode>
								<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode</xsl:attribute>
								<xsl:attribute name="codeListValue" namespace="">Complete</xsl:attribute>
								<xsl:value-of select="'Complete'"/>
							</MD_ProgressCode>
						</status>
						<resourceConstraints>
							<MD_Constraints>
								<useLimitation>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']"/>
									</gco:CharacterString>
								</useLimitation>
							</MD_Constraints>
							<MD_LegalConstraints>
								<accessConstraints>
									<MD_RestrictionCode>
										<xsl:attribute name="codeList" namespace="">http://www.isotc211.org/2005/resources/codeList.xml#MD_RestrictionCode</xsl:attribute>
										<xsl:attribute name="codeListValue" namespace="">otherRestrictions</xsl:attribute>
									</MD_RestrictionCode>
								</accessConstraints>
								<otherConstraints>
									<gco:CharacterString>
										<xsl:value-of select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']"/>
									</gco:CharacterString>
								</otherConstraints>
							</MD_LegalConstraints>
						</resourceConstraints>
					</MD_DataIdentification>
				</identificationInfo>
				<distributionInfo>
					<MD_Distribution>
						<transferOptions>
							<MD_DigitalTransferOptions>
								<onLine>
									<CI_OnlineResource>
										<linkage>
											<URL>
												<xsl:value-of select="concat('http://dx.doi.org/doi:', substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/'))"/>
											</URL>
										</linkage>
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
			</xsl:for-each>
		</MD_Metadata>
	</xsl:template>
</xsl:stylesheet>
