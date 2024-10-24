<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="." />
		<DIF xmlns="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/">
			<xsl:attribute name="xsi:schemaLocation"
				namespace="http://www.w3.org/2001/XMLSchema-instance">http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/
				file:///C:/xampp2/htdocs/mde-msl/schemas/GCMD/DIF.xsd</xsl:attribute>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var2_cur" select="." />
				<Entry_ID>
					<xsl:value-of
						select="substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/')" />
				</Entry_ID>
				<xsl:for-each
					select="(./*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Main Title')]">
					<xsl:variable name="var3_filter" select="." />
					<Entry_Title>
						<xsl:value-of select="*[local-name()='text' and namespace-uri()='']" />
					</Entry_Title>
				</xsl:for-each>
				<Data_Set_Citation>
					<Dataset_Creator>
						<xsl:for-each
							select="*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']">
							<xsl:value-of
								select="concat(*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='givenname' and namespace-uri()=''])" />
							<xsl:if test="position() != last()">
								<xsl:text>; </xsl:text>
							</xsl:if>
						</xsl:for-each>
					</Dataset_Creator>
					<xsl:for-each
						select="(./*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Main Title')]">
						<xsl:variable name="var5_filter" select="." />
						<Dataset_Title>
							<xsl:value-of select="*[local-name()='text' and namespace-uri()='']" />
						</Dataset_Title>
					</xsl:for-each>
					<Dataset_Release_Date>
						<xsl:value-of select="*[local-name()='year' and namespace-uri()='']" />
					</Dataset_Release_Date>
				</Data_Set_Citation>
				<xsl:for-each
					select="*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']">
					<xsl:variable name="var6_cur" select="." />
					<Spatial_Coverage>
						<Southernmost_Latitude>
							<xsl:value-of
								select="number(*[local-name()='latitudeMin' and namespace-uri()=''])" />
						</Southernmost_Latitude>
						<xsl:choose>
							<xsl:when
								test="(((true() and boolean(*[local-name()='latitudeMax' and namespace-uri()=''])) and true()) and boolean(*[local-name()='longitudeMax' and namespace-uri()='']))">
								<xsl:for-each
									select="*[local-name()='latitudeMax' and namespace-uri()='']">
									<xsl:variable name="var7_cur" select="." />
									<Northernmost_Latitude>
										<xsl:value-of select="number(.)" />
									</Northernmost_Latitude>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<Northernmost_Latitude>
									<xsl:value-of
										select="number(*[local-name()='latitudeMin' and namespace-uri()=''])" />
								</Northernmost_Latitude>
							</xsl:otherwise>
						</xsl:choose>
						<Westernmost_Longitude>
							<xsl:value-of
								select="number(*[local-name()='longitudeMin' and namespace-uri()=''])" />
						</Westernmost_Longitude>
						<xsl:choose>
							<xsl:when
								test="(((true() and boolean(*[local-name()='latitudeMax' and namespace-uri()=''])) and true()) and boolean(*[local-name()='longitudeMax' and namespace-uri()='']))">
								<xsl:for-each
									select="*[local-name()='longitudeMax' and namespace-uri()='']">
									<xsl:variable name="var8_cur" select="." />
									<Easternmost_Longitude>
										<xsl:value-of select="number(.)" />
									</Easternmost_Longitude>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<Easternmost_Longitude>
									<xsl:value-of
										select="number(*[local-name()='longitudeMin' and namespace-uri()=''])" />
								</Easternmost_Longitude>
							</xsl:otherwise>
						</xsl:choose>
					</Spatial_Coverage>
				</xsl:for-each>
				<Data_Center>
					<Data_Center_Name>
						<Short_Name>
							<xsl:value-of select="'Deutsches GeoForschungsZentrum GFZ'" />
						</Short_Name>
						<Long_Name>
							<xsl:value-of select="'GFZ'" />
						</Long_Name>
					</Data_Center_Name>
					<Personnel>
						<Role>
							<xsl:value-of select="'DATA CENTER CONTACT'" />
						</Role>
						<Last_Name>
							<xsl:value-of select="'Deutsches GeoForschungsZentrum GFZ'" />
						</Last_Name>
					</Personnel>
				</Data_Center>
				<Summary>
					<xsl:for-each
						select="(./*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Abstract')]">
						<xsl:variable name="var9_filter" select="." />
						<Abstract>
							<xsl:value-of
								select="*[local-name()='description' and namespace-uri()='']" />
						</Abstract>
					</xsl:for-each>
				</Summary>
				<Metadata_Name>
					<xsl:value-of select="'DIF'" />
				</Metadata_Name>
				<Metadata_Version>
					<xsl:value-of select="'9.9.3'" />
				</Metadata_Version>
			</xsl:for-each>
		</DIF>
	</xsl:template>
</xsl:stylesheet>