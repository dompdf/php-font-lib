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

class Adobe_Font_Metrics {
  private $f;
  
  /**
   * @var Font_TrueType
   */
  private $font;
  
  function __construct(Font_TrueType $font) {
    $this->font = $font;
  }
  
  function write($file){
    $this->f = fopen($file, "w+");
    
    $font = $this->font;
    $data = $font->data;
    
    $this->startSection("FontMetrics", 4.1);
    $this->addPair("Notice", "Converted by PHP-font-lib");
    $this->addPair("Comment", "http://php-font-lib.googlecode.com/");
    $this->addPair("EncodingScheme", "FontSpecific");
    
    $nameRecords = $data["name"]["nameRecord"];
    foreach($nameRecords as $id => $value) {
      if (!isset(Font_TrueType::$nameIdCodes[$id]) || preg_match("/[\r\n]/", $value)) {
        continue;
      }
      
      $this->addPair(Font_TrueType::$nameIdCodes[$id], $value);
    }
    
    $os2 = $data["OS/2"];
    $this->addPair("Weight", ($os2["usWeightClass"] > 400 ? "Bold" : "Medium"));
    
    $post = $data["post"];
    $this->addPair("ItalicAngle",        $post["italicAngle"]);
    $this->addPair("IsFixedPitch",      ($post["isFixedPitch"] ? "true" : "false"));
    $this->addPair("UnderlineThickness", $font->normalizeFUnit($post["underlineThickness"]));
    $this->addPair("UnderlinePosition",  $font->normalizeFUnit($post["underlinePosition"]));
    
    $hhea = $data["hhea"];
    $this->addPair("Ascender",  $font->normalizeFUnit($hhea["ascent"]));
    $this->addPair("Descender", $font->normalizeFUnit($hhea["descent"]));
    
    $head = $data["head"];
    $this->addArray("FontBBox", array(
      $font->normalizeFUnit($head["xMin"]),
      $font->normalizeFUnit($head["yMin"]),
      $font->normalizeFUnit($head["xMax"]),
      $font->normalizeFUnit($head["yMax"]),
    ));
    
    $subtable = null;
    foreach($data["cmap"]["subtables"] as $_subtable) {
      if ($_subtable["platformID"] == 3 && $_subtable["platformSpecificID"] == 1) {
        $subtable = $_subtable;
        break;
      }
    }
    
    if ($subtable) {
      $hmtx = $data["hmtx"]["hMetrics"];
      $names = $data["post"]["names"];
          
      $this->startSection("CharMetrics", count($hmtx));
      
        //for($c = 0; $c < 0xFFFE; $c++) {
        //  $g = $this->mapCharCode($c, $subtable);
        //  if ($g == 0) continue;
        
        foreach($subtable["glyphIndexArray"] as $c => $g) {
          if (empty($hmtx[$g])) continue;
          
          $this->addMetric(array(
            "U" => $c,
            "WX" => $font->normalizeFUnit($hmtx[$g]),
            "N" => (isset($names[$g]) ? $names[$g] : sprintf("uni%04x", $c)),
            "G" => $g,
          ));
        }
        
      $this->endSection("CharMetrics");
    }
      
    $this->endSection("FontMetrics");
  }
  
  function addLine($line) {
    fwrite($this->f, "$line\n");
  }
  
  function addPair($key, $value) {
    $this->addLine("$key $value");
  }
  
  function addArray($key, $array) {
    $this->addLine("$key ".implode(" ", $array));
  }
  
  function addMetric($data) {
    $array = array();
    foreach($data as $key => $value) {
      $array[] = "$key $value";
    }
    $this->addLine(implode(" ; ", $array));
  }

  function startSection($name, $value) {
    $this->addLine("Start$name $value");
  }
  
  function endSection($name) {
    $this->addLine("End$name");
  }
}
