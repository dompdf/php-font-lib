<?php 
/**
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * @link http://php-font-lib.googlecode.com/
 * @author Fabien Ménager
 */

/* $Id$ */
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <pre>
<?php 

require_once "../classes/font.cls.php";

$fontfile = $_GET["fontfile"];

$t = microtime(true);

$font = Font::load($fontfile);
$font->parse();

if ($font instanceof Font_TrueType_Collection) {
  $font = $font->getFont(0);
  $font->parse();
}

//$font->saveAdobeFontMetrics("$fontfile.ufm");

echo "Memory:\t".(memory_get_peak_usage(true) / 1024)."KB\n";
echo "Time:\t".(microtime(true) - $t)."s\n";
echo "<hr />";

$highlight = false;

if ($highlight) {
  highlight_string("<"."?php ".var_export($font->data, true));
}
else {
  var_export($font);
}

?>
</pre>
</body>
</html>