<?php 

require_once "../classes/font_truetype.cls.php";
require_once "../classes/font_woff.cls.php";
require_once "../classes/adobe_font_metrics.cls.php";

$fontfile = $_GET["fontfile"];

echo "<pre>";
/*require("ttfparser.cls.php");
$ttf = new TTFParser();
$ttf->parse("../lib/fonts/DejaVuSans.ttf");
/*var_dump($ttf->xMin);*/

$t = microtime(true);

for($i = 0; $i < 1; $i++) {
  $font = new Font_TrueType();
  $font->load($fontfile);
  
  /*$font = new Font_WOFF();
  $font->load("../lib/fonts/WorldWideWeb.woff");*/
  
  $font->parse();
}

$font->saveAdobeFontMetrics("$fontfile.ufm");

echo "Memory:\t".(memory_get_peak_usage(true) / 1024)."KB\n";
echo "Time:\t".(microtime(true) - $t)."s\n";

$highlight = false;

if ($highlight) {
  highlight_string("<"."?php ".var_export($font->data, true));
}
else {
  var_export($font->data);
}