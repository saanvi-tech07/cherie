<?php
/**
 * CHÉRIE — XML Product Catalog Feed Generator
 * Generates valid live XML product feed using PHP SimpleXML
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/xml_service.php';

$xmlContent = XmlService::exportCatalogToXml();

header('Content-Type: application/xml; charset=utf-8');
echo $xmlContent;
exit;
