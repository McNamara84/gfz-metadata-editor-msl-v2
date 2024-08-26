<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs">
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:variable name="var1_initial" select="."/>
		<DIF xmlns="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/">
			<xsl:attribute name="xsi:schemaLocation" namespace="http://www.w3.org/2001/XMLSchema-instance">http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/ file:///C:/xampp/htdocs/mde/schemas/GCMD/DIF.xsd</xsl:attribute>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var2_cur" select="."/>
				<Entry_ID>
					<xsl:value-of select="substring-after(*[local-name()='doi' and namespace-uri()=''], 'http://doi.org/')"/>
				</Entry_ID>
			</xsl:for-each>
			<xsl:for-each select="(./*[local-name()='Resource' and namespace-uri()=''])[contains(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], 'Main Title')]">
				<xsl:variable name="var3_cur" select="."/>
				<Entry_Title>
					<xsl:value-of select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']"/>
				</Entry_Title>
			</xsl:for-each>
			<Data_Set_Citation>
				<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
					<xsl:variable name="var4_cur" select="."/>
					<Dataset_Creator>
						<xsl:value-of select="concat(*[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='familyname' and namespace-uri()=''], ', ', *[local-name()='Authors' and namespace-uri()='']/*[local-name()='Author' and namespace-uri()='']/*[local-name()='givenname' and namespace-uri()=''])"/>
					</Dataset_Creator>
				</xsl:for-each>
				<xsl:for-each select="(./*[local-name()='Resource' and namespace-uri()=''])[contains(*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='type' and namespace-uri()=''], 'Main Title')]">
					<xsl:variable name="var5_cur" select="."/>
					<Dataset_Title>
						<xsl:value-of select="*[local-name()='Titles' and namespace-uri()='']/*[local-name()='Title' and namespace-uri()='']/*[local-name()='text' and namespace-uri()='']"/>
					</Dataset_Title>
				</xsl:for-each>
				<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
					<xsl:variable name="var6_cur" select="."/>
					<Dataset_Release_Date>
						<xsl:value-of select="number(*[local-name()='year' and namespace-uri()=''])"/>
					</Dataset_Release_Date>
				</xsl:for-each>
				<Dataset_Release_Place>
					<xsl:value-of select="'Potsdam, Germany'"/>
				</Dataset_Release_Place>
				<Dataset_Publisher>
					<xsl:value-of select="'GFZ Data Services'"/>
				</Dataset_Publisher>
				<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
					<xsl:variable name="var7_cur" select="."/>
					<Online_Resource>
						<xsl:value-of select="*[local-name()='doi' and namespace-uri()='']"/>
					</Online_Resource>
				</xsl:for-each>
			</Data_Set_Citation>
			<xsl:for-each select="(./*[local-name()='Resource' and namespace-uri()='']/*[local-name()='ThesaurusKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()=''])[contains(*[local-name()='scheme' and namespace-uri()=''], 'NASA/GCMD Earth Science Keywords')]">
				<xsl:variable name="var8_filter" select="."/>
				<Parameters>
					<Category>
						<xsl:value-of select="'SPACE SCIENCE'"/>
					</Category>
					<Topic>
						<xsl:value-of select="substring-before(substring-after(*[local-name()='keyword' and namespace-uri()=''], 'SPACE SCIENCE &amp;gt; '), ' &amp;gt; ')"/>
					</Topic>
					<Term>
						<xsl:value-of select="substring-after(*[local-name()='keyword' and namespace-uri()=''], '&amp;gt; ')"/>
					</Term>
				</Parameters>
			</xsl:for-each>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var9_cur" select="."/>
				<Keyword>
					<xsl:value-of select="*[local-name()='FreeKeywords' and namespace-uri()='']/*[local-name()='Keyword' and namespace-uri()='']/*[local-name()='free_keyword' and namespace-uri()='']"/>
				</Keyword>
			</xsl:for-each>
			<xsl:for-each select="*[local-name()='Resource' and namespace-uri()='']">
				<xsl:variable name="var10_cur" select="."/>
				<Spatial_Coverage>
					<Southernmost_Latitude>
						<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMin' and namespace-uri()=''])"/>
					</Southernmost_Latitude>
					<Northernmost_Latitude>
						<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='latitudeMax' and namespace-uri()=''])"/>
					</Northernmost_Latitude>
					<Westernmost_Longitude>
						<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMin' and namespace-uri()=''])"/>
					</Westernmost_Longitude>
					<Easternmost_Longitude>
						<xsl:value-of select="number(*[local-name()='SpatialTemporalCoverages' and namespace-uri()='']/*[local-name()='SpatialTemporalCoverage' and namespace-uri()='']/*[local-name()='longitudeMax' and namespace-uri()=''])"/>
					</Easternmost_Longitude>
				</Spatial_Coverage>
			</xsl:for-each>
			<Data_Center>
				<Data_Center_Name>
					<Short_Name>
						<xsl:value-of select="'GFZ'"/>
					</Short_Name>
					<Long_Name>
						<xsl:value-of select="'Deutsches GeoForschungsZentrum GFZ'"/>
					</Long_Name>
				</Data_Center_Name>
				<Personnel>
					<Role>
						<xsl:value-of select="'DATA CENTER CONTACT'"/>
					</Role>
					<Last_Name>
						<xsl:value-of select="'Deutsches GeoForschungsZentrum GFZ'"/>
					</Last_Name>
				</Personnel>
			</Data_Center>
			<Summary>
				<xsl:for-each select="(./*[local-name()='Resource' and namespace-uri()='']/*[local-name()='Descriptions' and namespace-uri()='']/*[local-name()='Description' and namespace-uri()=''])[contains(*[local-name()='type' and namespace-uri()=''], 'Abstract')]">
					<xsl:variable name="var11_filter" select="."/>
					<Abstract>
						<xsl:value-of select="*[local-name()='description' and namespace-uri()='']"/>
					</Abstract>
				</xsl:for-each>
			</Summary>
			<Metadata_Name>
				<xsl:value-of select="'DIF'"/>
			</Metadata_Name>
			<Metadata_Version>
				<xsl:value-of select="'9.9.3'"/>
			</Metadata_Version>
		</DIF>
	</xsl:template>
</xsl:stylesheet>
