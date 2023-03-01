<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
    xmlns:html="http://www.w3.org/TR/REC-html40"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
  <xsl:template match="/">
    <html>
      <head>
        <title>Sitemap file</title>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"/>
        <script type="text/javascript" src="application/assets/javascript/functions_jquery_tablesorter.js"/>
        <script type="text/javascript" src="application/assets/javascript/functions_sitemap.js"/>
        <link href="application/assets/css/1/client/sitemap.css" type="text/css" rel="stylesheet"/>
      </head>

      <xsl:variable name="fileType">
        <xsl:choose>
          <xsl:when test="//sitemap:url">sitemap</xsl:when>
          <xsl:otherwise>siteindex</xsl:otherwise>
        </xsl:choose>
      </xsl:variable>

      <body>
        <h1>Sitemap file</h1>
        <xsl:choose>
          <xsl:when test="$fileType='sitemap'"><xsl:call-template name="sitemapTable"/></xsl:when>
          <xsl:otherwise><xsl:call-template name="siteindexTable"/></xsl:otherwise>
        </xsl:choose>
        <div id="footer">
          <p></p>
        </div>
      </body>
    </html>
  </xsl:template>
  <xsl:template name="siteindexTable">
    <div id="information">
      <p>Number of sitemaps in this index: <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"></xsl:value-of></p>
    </div>
    <table class="tablesorter siteindex">
      <thead>
        <tr>
          <th>Sitemap URL</th>
          <th>Last modification date</th>
        </tr>
      </thead>
      <tbody>
        <xsl:apply-templates select="sitemap:sitemapindex/sitemap:sitemap">
          <xsl:sort select="sitemap:lastmod" order="descending"/>
        </xsl:apply-templates>
      </tbody>
    </table>
  </xsl:template>
  <xsl:template name="sitemapTable">
    <div id="information">
      <p>Number of URLs in this sitemap: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"></xsl:value-of></p>
    </div>
    <table class="tablesorter sitemap">
      <thead>
        <tr>
          <th>URL location</th>
          <th>Last modification date</th>
          <th>Change frequency</th>
          <th>Priority</th>
        </tr>
      </thead>
      <tbody>
        <xsl:apply-templates select="sitemap:urlset/sitemap:url">
          <xsl:sort select="sitemap:priority" order="descending"/>
        </xsl:apply-templates>
      </tbody>
    </table>
  </xsl:template>
  <xsl:template match="sitemap:url">
    <tr>
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
        <a href="{$sitemapURL}" ref="nofollow"><xsl:value-of select="$sitemapURL"></xsl:value-of></a>
      </td>
      <td><xsl:value-of select="sitemap:lastmod"/></td>
      <td><xsl:value-of select="sitemap:changefreq"/></td>
      <td>
        <xsl:choose>
          <xsl:when test="sitemap:priority">
            <xsl:value-of select="sitemap:priority"/>
          </xsl:when>
          <xsl:otherwise>0.5</xsl:otherwise>
        </xsl:choose>
      </td>
    </tr>
  </xsl:template>
  <xsl:template match="sitemap:sitemap">
    <tr>
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
        <a href="{$sitemapURL}"><xsl:value-of select="$sitemapURL"></xsl:value-of></a>
      </td>
      <td><xsl:value-of select="sitemap:lastmod"/></td>
    </tr>
  </xsl:template>
</xsl:stylesheet>
