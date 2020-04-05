<?php

header('Content-type: text/javascript; encoding=utf8');

$templateHtml = file_get_contents('template.xhtml');

$templateHtml = str_replace('\\', '\\\\', $templateHtml);
$templateHtml = str_replace('"', '\"', $templateHtml);
$templateHtml = str_replace(chr(13), '', $templateHtml);
$templateHtml = str_replace(chr(10), '\n', $templateHtml);

print 'var ime_templateHtml = "';
print $templateHtml;
print "\";\n";

?>