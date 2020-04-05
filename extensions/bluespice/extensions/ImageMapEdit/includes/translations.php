<?php

header('Content-type: application/x-javascript; encoding=utf8');

$lang = $_GET['lang'];
if (!$lang) $lang = 'en';

?>
/*
	Copyright (c) 2007-2011 Peter Schlömer

	Released under the following licenses (to make reuse in other Wikis
	easier):

	GNU General Public License (GPL), version 2
	GNU Free Documentatin Licence (GFDL), version 1.2 or later
	Creative Commons Attribution ShareAlike (CC-by-sa), version 2 or later
*/

<?php
print "// ImageMapEdit translations for language '$lang'\n";
print "\n";
print "var ime_translations = new Array();\n";

if ($lang==='de' || substr($lang, 0, 3)==='de-') {
	?>

ime_translations['error_imagenotfound'] = 'ImageMapEdit: Konnte Bild in der Seitenstruktur nicht finden.';
ime_translations['bottomleft'] = 'Links unten';
ime_translations['bottomright'] = 'Rechts unten';
ime_translations['circle'] = 'circle (Kreis)';
ime_translations['circlechoose1'] = 'Auswahl mit linker Maustaste';
ime_translations['circlechoose2'] = 'Auswahl mit rechter Maustaste';
ime_translations['coordinates'] = 'Koordinaten';
ime_translations['default'] = 'Standard';
ime_translations['deletearea'] = 'Ausgewählten Bereich löschen';
ime_translations['deletecoordinates'] = 'Alle Koordinaten löschen';
ime_translations['editarea'] = 'Bereich bearbeiten';
ime_translations['generatedwikicode'] = 'Erstellter Wikicode';
ime_translations['hidetextbox'] = 'Eingabefeld verstecken';
ime_translations['imagedescription'] = 'Bildbeschreibung';
ime_translations['import'] = 'Importieren';
ime_translations['importareas'] = 'Bereiche aus Wikicode importieren';
ime_translations['infolinkposition'] = 'Position des Info-Links';
ime_translations['linktarget'] = 'Linkziel';
ime_translations['linktitle'] = 'Linktitel';
ime_translations['newarea'] = 'Neuen Bereich erstellen';
ime_translations['nolink'] = 'Kein Link';
ime_translations['optional'] = 'optional';
ime_translations['poly'] = 'poly (Polygon)';
ime_translations['polychoose'] = 'Hinzufügen neuer Punkte mit linker Maustaste';
ime_translations['position'] = 'Position';
ime_translations['preferences'] = 'Allgemeine Einstellungen';
ime_translations['radius'] = 'Radius';
ime_translations['rect'] = 'rect (Rechteck)';
ime_translations['rectbottom'] = 'unten';
ime_translations['rectchoose1'] = 'Auswahl mit linker Maustaste';
ime_translations['rectchoose2'] = 'Auswahl mit rechter Maustaste';
ime_translations['rectleft'] = 'Links';
ime_translations['rectright'] = 'Rechts';
ime_translations['recttop'] = 'oben';
ime_translations['showtextbox'] = 'Eingabefeld anzeigen';
ime_translations['topleft'] = 'Links oben';
ime_translations['topright'] = 'Rechts oben';
	<?php
} else {
?>

ime_translations['error_imagenotfound'] = 'ImageMapEdit: Could not find image in page structure.';

// not using any other translations, these are in template.xhtml

	<?php
}
?>