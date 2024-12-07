<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="." />
		<MD_Metadata xmlns="http://www.isotc211.org/2005/gmd"
			xmlns:gco="http://www.isotc211.org/2005/gco"
			xmlns:gsr="http://www.isotc211.org/2005/gsr"
			xmlns:gss="http://www.isotc211.org/2005/gss"
			xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml"
			xmlns:xlink="http://www.w3.org/1999/xlink">
			<xsl:attribute name="xsi:schemaLocation"
				namespace="http://www.w3.org/2001/XMLSchema-instance">http://www.isotc211.org/2005/gmd
				file:///C:/xampp2/htdocs/mde-msl/schemas/ISO/gmd.xsd</xsl:attribute>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var2_cur" select="." />
				<fileIdentifier>
					<gco:CharacterString>
						<xsl:value-of
							select="concat('doi:', substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/'))" />
					</gco:CharacterString>
				</fileIdentifier>
				<language>
					<gco:CharacterString>
						<xsl:value-of
							select="*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']" />
					</gco:CharacterString>
					<LanguageCode>
						<xsl:attribute name="codeList" namespace="">
							http://www.loc.gov/standards/iso639-1/</xsl:attribute>
						<xsl:attribute name="codeListValue" namespace="">
							<xsl:value-of
								select="*[local-name()='Language' and namespace-uri()='']/*[local-name()='code' and namespace-uri()='']" />
						</xsl:attribute>
					</LanguageCode>
				</language>
				<characterSet>
					<MD_CharacterSetCode>
						<xsl:attribute name="codeList" namespace="">
							http://www.isotc211.org/2005/resources/codeList.xml#MD_CharacterSetCode</xsl:attribute>
						<xsl:attribute name="codeListValue" namespace="">utf8</xsl:attribute>
					</MD_CharacterSetCode>
				</characterSet>
				<hierarchyLevel>
					<MD_ScopeCode>
						<xsl:attribute name="codeList" namespace="">
							http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ScopeCode</xsl:attribute>
						<xsl:attribute name="codeListValue" namespace="">dataset</xsl:attribute>
					</MD_ScopeCode>
				</hierarchyLevel>
				<contact>
					<CI_ResponsibleParty>
						<organisationName>
							<gco:CharacterString>
								<xsl:value-of select="'GFZ German Research Center for Geosciences'" />
							</gco:CharacterString>
						</organisationName>
						<contactInfo>
							<CI_Contact>
								<onlineResource>
									<CI_OnlineResource>
										<linkage>
											<URL>
												<xsl:value-of select="'https://www.gfz-potsdam.de/'" />
											</URL>
										</linkage>
										<function>
											<CI_OnLineFunctionCode>
												<xsl:attribute name="codeList" namespace="">
													http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode</xsl:attribute>
												<xsl:attribute name="codeListValue" namespace="">
													information</xsl:attribute>
												<xsl:value-of select="'information'" />
											</CI_OnLineFunctionCode>
										</function>
									</CI_OnlineResource>
								</onlineResource>
							</CI_Contact>
						</contactInfo>
						<role>
							<CI_RoleCode>
								<xsl:attribute name="codeList" namespace="">
									http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode</xsl:attribute>
								<xsl:attribute name="codeListValue" namespace="">pointOfContact</xsl:attribute>
								<xsl:value-of select="'pointOfContact'" />
							</CI_RoleCode>
						</role>
					</CI_ResponsibleParty>
				</contact>
				<dateStamp>
					<gco:Date>
						<xsl:value-of select="*[local-name()='currentDate' and namespace-uri()='']" />
					</gco:Date>
				</dateStamp>
				<identificationInfo>
					<MD_DataIdentification>
						<citation>
							<CI_Citation>
								<title>
									<xsl:for-each
										select="(./*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Main Title')]">
										<xsl:variable name="var3_filter" select="." />
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='text' and namespace-uri()='']" />
										</gco:CharacterString>
									</xsl:for-each>
								</title>
								<alternateTitle>
									<xsl:variable name="var4_nested">
										<xsl:for-each
											select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']">
											<xsl:variable name="var5_cur" select="." />
											<xsl:value-of
												select="number(contains(*[local-name()='type' and namespace-uri()=''], 'Alternative Title'))" />
										</xsl:for-each>
									</xsl:variable>
									<xsl:if
										test="boolean(translate(normalize-space($var4_nested), ' 0', ''))">
										<gco:CharacterString>
											<xsl:for-each
												select="(./*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Alternative Title')]">
												<xsl:variable name="var6_filter" select="." />
												<xsl:value-of
													select="*[local-name()='text' and namespace-uri()='']" />
											</xsl:for-each>
										</gco:CharacterString>
									</xsl:if>
								</alternateTitle>
								<date>
									<CI_Date>
										<date>
											<gco:Date>
												<xsl:choose>
													<xsl:when
														test="(string(*[local-name()='dateCreated' and namespace-uri()='']) &gt; string(string-length(string(0))))">
														<xsl:value-of
															select="*[local-name()='dateCreated' and namespace-uri()='']" />
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of
															select="*[local-name()='currentDate' and namespace-uri()='']" />
													</xsl:otherwise>
												</xsl:choose>
											</gco:Date>
										</date>
										<dateType>
											<CI_DateTypeCode>
												<xsl:attribute name="codeList" namespace="">
													http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_DateTypeCode</xsl:attribute>
												<xsl:attribute name="codeListValue" namespace="">
													revision</xsl:attribute>
												<xsl:value-of select="'revision'" />
											</CI_DateTypeCode>
										</dateType>
									</CI_Date>
								</date>
								<xsl:for-each
									select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']">
									<xsl:variable name="var7_cur" select="." />
									<citedResponsibleParty>
										<xsl:attribute name="xlink:href">
											<xsl:value-of
												select="concat('http://orcid.org/', *[local-name()='orcid' and namespace-uri()=''])" />
										</xsl:attribute>
										<CI_ResponsibleParty>
											<individualName>
												<gco:CharacterString>
													<xsl:value-of
														select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])" />
												</gco:CharacterString>
											</individualName>
											<organisationName>
												<xsl:for-each
													select="*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
													<xsl:variable name="var8_cur" select="." />
													<gco:CharacterString>
														<xsl:value-of
															select="*[local-name()='name' and namespace-uri()='']" />
													</gco:CharacterString>
												</xsl:for-each>
											</organisationName>
											<role>
												<CI_RoleCode>
													<xsl:attribute name="codeList" namespace="">
														http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode</xsl:attribute>
													<xsl:attribute name="codeListValue" namespace="">
														author</xsl:attribute>
													<xsl:value-of select="'author'" />
												</CI_RoleCode>
											</role>
										</CI_ResponsibleParty>
									</citedResponsibleParty>
								</xsl:for-each>
							</CI_Citation>
						</citation>
						<abstract>
							<CharacterString>
								<xsl:for-each
									select="*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()='']">
									<xsl:if test="position() &gt; 1">
										<xsl:text>&#xA;&#xA;</xsl:text>
									</xsl:if>
									<xsl:value-of
										select="*[local-name()='description' and namespace-uri()='']" />
								</xsl:for-each>
							</CharacterString>
						</abstract>
						<status>
							<MD_ProgressCode>
								<xsl:attribute name="codeList" namespace="">
									http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#MD_ProgressCode</xsl:attribute>
								<xsl:attribute name="codeListValue" namespace="">Complete</xsl:attribute>
								<xsl:value-of select="'Complete'" />
							</MD_ProgressCode>
						</status>
						<pointOfContact>
							<CI_ResponsibleParty>
								<xsl:for-each
									select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']">
									<xsl:variable name="var10_cur" select="." />
									<individualName>
										<gco:CharacterString>
											<xsl:value-of
												select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])" />
										</gco:CharacterString>
									</individualName>
								</xsl:for-each>
								<organisationName>
									<xsl:for-each
										select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']/*[local-name()='Affiliations' and namespace-uri()='']/*[local-name()='Affiliation' and namespace-uri()='']">
										<xsl:variable name="var11_cur" select="." />
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='name' and namespace-uri()='']" />
										</gco:CharacterString>
									</xsl:for-each>
								</organisationName>
								<positionName>
									<xsl:for-each
										select="(./*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()=''])[(string-length(string(*[local-name()='position' and namespace-uri()=''])) &gt; 0)]">
										<xsl:variable name="var12_cur" select="." />
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='position' and namespace-uri()='']" />
										</gco:CharacterString>
									</xsl:for-each>
								</positionName>
								<contactInfo>
									<CI_Contact>
										<address>
											<CI_Address>
												<electronicMailAddress>
													<xsl:for-each
														select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']">
														<xsl:variable name="var13_cur" select="." />
														<gco:CharacterString>
															<xsl:value-of
																select="*[local-name()='email' and namespace-uri()='']" />
														</gco:CharacterString>
													</xsl:for-each>
												</electronicMailAddress>
											</CI_Address>
										</address>
										<onlineResource>
											<CI_OnlineResource>
												<linkage>
													<xsl:for-each
														select="*[local-name()='ContactPersons' and namespace-uri()='']/*[local-name()='ContactPerson' and namespace-uri()='']">
														<xsl:variable name="var14_cur" select="." />
														<URL>
															<xsl:value-of
																select="*[local-name()='website' and namespace-uri()='']" />
														</URL>
													</xsl:for-each>
												</linkage>
												<function>
													<CI_OnLineFunctionCode>
														<xsl:attribute name="codeList" namespace="">
															http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode</xsl:attribute>
														<xsl:attribute name="codeListValue"
															namespace="">
															http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode_information</xsl:attribute>
														<xsl:value-of select="'information'" />
													</CI_OnLineFunctionCode>
												</function>
											</CI_OnlineResource>
										</onlineResource>
									</CI_Contact>
								</contactInfo>
								<role>
									<CI_RoleCode>
										<xsl:attribute name="codeList" namespace="">
											http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode</xsl:attribute>
										<xsl:attribute name="codeListValue" namespace="">
											pointOfContact</xsl:attribute>
										<xsl:value-of select="'pointOfContact'" />
									</CI_RoleCode>
								</role>
							</CI_ResponsibleParty>
						</pointOfContact>
						<descriptiveKeywords>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Analogue')]">
								<xsl:variable name="var15_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of select="'EPOS WP16 Analogue'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Geochemistry')]">
								<xsl:variable name="var16_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title />
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Geologicalage')]">
								<xsl:variable name="var17_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of select="'EPOS WP16 Geologicalage'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Geologicalsetting')]">
								<xsl:variable name="var18_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of
														select="'EPOS WP16 Geologicalsetting'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Materials')]">
								<xsl:variable name="var19_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of select="'EPOS WP16 Materials'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Microscopy')]">
								<xsl:variable name="var20_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of select="'EPOS WP16 Microscopy'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Paleomagnetism')]">
								<xsl:variable name="var21_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of
														select="'EPOS WP16 Paleomagnetism'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Porefluids')]">
								<xsl:variable name="var22_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of select="'EPOS WP16 Porefluids'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'EPOS WP16 Rockphysics')]">
								<xsl:variable name="var23_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of select="'EPOS WP16 Rockphysics'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<xsl:for-each
								select="(./*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'NASA/GCMD Earth Science Keywords')]">
								<xsl:variable name="var24_filter" select="." />
								<MD_Keywords>
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
									<thesaurusName>
										<CI_Citation>
											<title>
												<gco:CharacterString>
													<xsl:value-of
														select="'NASA/GCMD Earth Science Keywords'" />
												</gco:CharacterString>
											</title>
											<date />
										</CI_Citation>
									</thesaurusName>
								</MD_Keywords>
							</xsl:for-each>
							<MD_Keywords>
								<xsl:for-each
									select="*[local-name()='FreeKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
									<xsl:variable name="var25_cur" select="." />
									<keyword>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='free_keyword' and namespace-uri()='']" />
										</gco:CharacterString>
									</keyword>
								</xsl:for-each>
							</MD_Keywords>
						</descriptiveKeywords>
						<resourceConstraints>
							<MD_Constraints>
								<useLimitation>
									<gco:CharacterString>
										<xsl:value-of
											select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']" />
									</gco:CharacterString>
								</useLimitation>
							</MD_Constraints>
							<MD_LegalConstraints>
								<accessConstraints>
									<MD_RestrictionCode>
										<xsl:attribute name="codeList" namespace="">
											http://www.isotc211.org/2005/resources/codeList.xml#MD_RestrictionCode</xsl:attribute>
										<xsl:attribute name="codeListValue" namespace="">
											otherRestrictions</xsl:attribute>
									</MD_RestrictionCode>
								</accessConstraints>
								<otherConstraints>
									<gco:CharacterString>
										<xsl:value-of
											select="*[local-name()='Rights' and namespace-uri()='']/*[local-name()='rightsIdentifier' and namespace-uri()='']" />
									</gco:CharacterString>
								</otherConstraints>
							</MD_LegalConstraints>
						</resourceConstraints>
						<xsl:for-each
							select="*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']">
							<xsl:variable name="var26_cur" select="." />
							<language />
						</xsl:for-each>
						<xsl:for-each
							select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
							<xsl:variable name="var27_cur" select="." />
							<extent>
								<EX_Extent>
									<description>
										<gco:CharacterString>
											<xsl:value-of
												select="*[local-name()='Description' and namespace-uri()='']" />
										</gco:CharacterString>
									</description>
									<geographicElement>
										<EX_GeographicBoundingBox>
											<westBoundLongitude>
												<gco:Decimal>
													<xsl:value-of
														select="number(*[local-name()='longitudeMin' and namespace-uri()=''])" />
												</gco:Decimal>
											</westBoundLongitude>
											<eastBoundLongitude>
												<xsl:choose>
													<xsl:when
														test="(((true() and boolean(*[local-name()='latitudeMax' and namespace-uri()=''])) and true()) and boolean(*[local-name()='longitudeMax' and namespace-uri()='']))">
														<xsl:for-each
															select="*[local-name()='longitudeMax' and namespace-uri()='']">
															<xsl:variable name="var28_cur"
																select="." />
															<gco:Decimal>
																<xsl:value-of select="number(.)" />
															</gco:Decimal>
														</xsl:for-each>
													</xsl:when>
													<xsl:otherwise>
														<gco:Decimal>
															<xsl:value-of
																select="number(*[local-name()='longitudeMin' and namespace-uri()=''])" />
														</gco:Decimal>
													</xsl:otherwise>
												</xsl:choose>
											</eastBoundLongitude>
											<southBoundLatitude>
												<gco:Decimal>
													<xsl:value-of
														select="number(*[local-name()='latitudeMin' and namespace-uri()=''])" />
												</gco:Decimal>
											</southBoundLatitude>
											<northBoundLatitude>
												<xsl:choose>
													<xsl:when
														test="(((true() and boolean(*[local-name()='latitudeMax' and namespace-uri()=''])) and true()) and boolean(*[local-name()='longitudeMax' and namespace-uri()='']))">
														<xsl:for-each
															select="*[local-name()='latitudeMax' and namespace-uri()='']">
															<xsl:variable name="var29_cur"
																select="." />
															<gco:Decimal>
																<xsl:value-of select="number(.)" />
															</gco:Decimal>
														</xsl:for-each>
													</xsl:when>
													<xsl:otherwise>
														<gco:Decimal>
															<xsl:value-of
																select="number(*[local-name()='latitudeMin' and namespace-uri()=''])" />
														</gco:Decimal>
													</xsl:otherwise>
												</xsl:choose>
											</northBoundLatitude>
										</EX_GeographicBoundingBox>
									</geographicElement>
									<temporalElement>
										<EX_TemporalExtent>
											<extent>
												<gml:TimePeriod>
													<gml:beginPosition>
														<xsl:value-of
															select="concat(*[local-name()='dateTimeStart' and namespace-uri()=''], *[local-name()='timezone' and namespace-uri()=''])" />
													</gml:beginPosition>
													<gml:endPosition>
														<xsl:value-of
															select="concat(*[local-name()='dateTimeEnd' and namespace-uri()=''], *[local-name()='timezone' and namespace-uri()=''])" />
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
										<linkage>
											<URL>
												<xsl:value-of
													select="concat('http://dx.doi.org/doi:', substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/'))" />
											</URL>
										</linkage>
										<protocol>
											<gco:CharacterString>
												<xsl:value-of select="'WWW:LINK-1.0-http--link'" />
											</gco:CharacterString>
										</protocol>
										<name>
											<gco:CharacterString>
												<xsl:value-of select="'Download'" />
											</gco:CharacterString>
										</name>
										<description>
											<gco:CharacterString>
												<xsl:value-of select="'Download'" />
											</gco:CharacterString>
										</description>
										<function>
											<CI_OnLineFunctionCode>
												<xsl:attribute name="codeList" namespace="">
													http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode</xsl:attribute>
												<xsl:attribute name="codeListValue" namespace="">
													http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_OnLineFunctionCode_download</xsl:attribute>
												<xsl:value-of select="'download'" />
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