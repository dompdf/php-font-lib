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

require_once dirname(__FILE__)."/font_binary_stream.cls.php";
require_once dirname(__FILE__)."/font_truetype_table_directory_entry.cls.php";
require_once dirname(__FILE__)."/adobe_font_metrics.cls.php";

class Font_TrueType extends Font_Binary_Stream {
  public $sfntVersion;
  public $sfntVersionText;
  public $numTables;
  public $searchRange;
  public $entrySelector;
  public $rangeShift;
  private $tableOffset = 0; // Used for TTC
  
  protected $table = array();
  public $data = array();
  
  static $tableFormat = array(
    "head" => array(
      "tableVersion"       => self::Fixed,
      "fontRevision"       => self::Fixed,
      "checkSumAdjustment" => self::uint32,
      "magicNumber"        => self::uint32,
      "flags"              => self::uint16,
      "unitsPerEm"         => self::uint16,
      "created"            => self::longDateTime,
      "modified"           => self::longDateTime,
      "xMin"               => self::FWord,
      "yMin"               => self::FWord,
      "xMax"               => self::FWord,
      "yMax"               => self::FWord,
      "macStyle"           => self::uint16,
      "lowestRecPPEM"      => self::uint16,
      "fontDirectionHint"  => self::int16,
      "indexToLocFormat"   => self::int16,
      "glyphDataFormat"    => self::int16,
    ),
    "hhea" => array(
      "version"             => self::Fixed,
      "ascent"              => self::FWord,
      "descent"             => self::FWord,
      "lineGap"             => self::FWord,
      "advanceWidthMax"     => self::uFWord,
      "minLeftSideBearing"  => self::FWord,
      "minRightSideBearing" => self::FWord,
      "xMaxExtent"          => self::FWord,
      "caretSlopeRise"      => self::int16,
      "caretSlopeRun"       => self::int16,
      "caretOffset"         => self::FWord,
                               self::int16,
                               self::int16,
                               self::int16,
                               self::int16,
      "metricDataFormat"    => self::int16,
      "numOfLongHorMetrics" => self::uint16,
    ),
    "maxp" => array(
      "version"               => self::Fixed,
      "numGlyphs"             => self::uint16,
      "maxPoints"             => self::uint16,
      "maxContours"           => self::uint16,
      "maxComponentPoints"    => self::uint16,
      "maxComponentContours"  => self::uint16,
      "maxZones"              => self::uint16,
      "maxTwilightPoints"     => self::uint16,
      "maxStorage"            => self::uint16,
      "maxFunctionDefs"       => self::uint16,
      "maxInstructionDefs"    => self::uint16,
      "maxStackElements"      => self::uint16,
      "maxSizeOfInstructions" => self::uint16,
      "maxComponentElements"  => self::uint16,
      "maxComponentDepth"     => self::uint16,
    ),
    "post" => array(
      "format"             => self::Fixed,
      "italicAngle"        => self::Fixed,
      "underlinePosition"  => self::FWord,
      "underlineThickness" => self::FWord,
      "isFixedPitch"       => self::uint32,
      "minMemType42"       => self::uint32,
      "maxMemType42"       => self::uint32,
      "minMemType1"        => self::uint32,
      "maxMemType1"        => self::uint32,
    ),
  );
  
  static $nameIdCodes = array(
    0 => "Copyright",
    1 => "FontFamily",
    2 => "FontSubfamily",
    3 => "UniqueID",
    4 => "FullName",
    5 => "Version",
    6 => "PostScriptName",
    7 => "Trademark",
    8 => "Manufacturer",
    9 => "Designer",
    10  => "Description",
    11  => "FontVendorURL",
    12  => "FontDesignerURL",
    13  => "LicenseDescription",
    14  => "LicenseURL",
 // 15
    16  => "PreferredFamily",
    17  => "PreferredSubfamily",
    18  => "CompatibleFullName",
    19  => "SampleText",
  );
  
  static $platforms = array(
    0 => "Unicode",
    1 => "Macintosh",
 // 2 =>  Reserved
    3 => "Microsoft",
  );
  
  static $plaformSpecific = array(
    // Unicode
    0 => array(
      0 => "Default semantics",
      1 => "Version 1.1 semantics",
      2 => "ISO 10646 1993 semantics (deprecated)",
      3 => "Unicode 2.0 or later semantics",
    ),
    
    // Macintosh
    1 => array(
      0 => "Roman",
      1 => "Japanese",
      2 => "Traditional Chinese",
      3 => "Korean",
      4 => "Arabic",  
      5 => "Hebrew",  
      6 => "Greek", 
      7 => "Russian", 
      8 => "RSymbol", 
      9 => "Devanagari",  
      10 => "Gurmukhi",  
      11 => "Gujarati",  
      12 => "Oriya", 
      13 => "Bengali", 
      14 => "Tamil", 
      15 => "Telugu",
      16 => "Kannada",
      17 => "Malayalam",
      18 => "Sinhalese",
      19 => "Burmese",
      20 => "Khmer",
      21 => "Thai",
      22 => "Laotian",
      23 => "Georgian",
      24 => "Armenian",
      25 => "Simplified Chinese",
      26 => "Tibetan",
      27 => "Mongolian",
      28 => "Geez",
      29 => "Slavic",
      30 => "Vietnamese",
      31 => "Sindhi",
    ),
    
    // Microsoft
    3 => array(
      0 => "Symbol",
      1 => "Unicode BMP (UCS-2)",
      2 => "ShiftJIS",
      3 => "PRC",
      4 => "Big5",
      5 => "Wansung",
      6 => "Johab",
  //  7 => Reserved
  //  8 => Reserved
  //  9 => Reserved
      10 => "Unicode UCS-4",
    ),
  );
  
  function setTableOffset($offset) {
    $this->tableOffset = $offset;
  }
  
  function parse() {
    $this->parseHeader();
    $this->parseTableEntries();
    $this->readData();
  }
  
  function parseHeader(){
    $this->seek($this->tableOffset);
    $this->sfntVersion   = $this->readFixed();
    
    $this->seek($this->tableOffset);
    $this->sfntVersionText   = $this->read(4);
    
    $this->numTables     = $this->readUInt16();
    $this->searchRange   = $this->readUInt16();
    $this->entrySelector = $this->readUInt16();
    $this->rangeShift    = $this->readUInt16();
  }
  
  function parseTableEntries(){
    for($i = 0; $i < $this->numTables; $i++) {
      $str = $this->read(Font_TrueType_Table_Directory_Entry::$entrySize);
      $entry = new Font_TrueType_Table_Directory_Entry($str);
      $this->table[$entry->tag] = $entry;
    }
  }
  
  function readData(){
    $this->parseHEAD();
    $this->parseHHEA();
    $this->parseMAXP();
    $this->parseCMAP();
    $this->parseHMTX();
    $this->parseNAME();
    $this->parseOS2();
    $this->parsePOST();
  }
  
  function parseHEAD() {
    $this->readTable("head");
    
    if($this->data["head"]["magicNumber"] != 0x5F0F3CF5) {
      //throw new Exception("Incorrect magic number (".dechex($this->data["head"]["magicNumber"]).")");
    }
  }

  function parseHHEA() {
    $this->readTable("hhea");
  }

  function parseMAXP() {
    $this->readTable("maxp");
  }

  function parseCMAP() {
    $this->seekTag("cmap");
    
    $this->data["cmap"] = array(
      "version" => $this->readUInt16(),
      "numberSubtables" => $this->readUInt16(),
    );
    
    $subtables = array();
    for($i = 0; $i < $this->data["cmap"]["numberSubtables"]; $i++){
      $subtables[] = array(
        "platformID" => $this->readUInt16(),
        "platformSpecificID" => $this->readUInt16(),
        "offset" => $this->readUInt32(),
      );
    }
    $this->data["cmap"]["subtables"] = $subtables;
    
    foreach($this->data["cmap"]["subtables"] as &$subtable) {
      $this->seek($this->table["cmap"]->offset + $subtable["offset"]);
      
      $format = $this->readUInt16();
      
      if($format != 4) continue;
      
      $pack = "nlength/nlanguage/nsegCountX2/nsearchRange/nentrySelector/nrangeShift";
      $map = unpack($pack, $this->read(12));
      $segCount = $map["segCountX2"] / 2;
      
      $endCode = array();
      for($i = 0; $i < $segCount; $i++) {
        $endCode[] = $this->readUInt16();
      }
      
      $this->readUInt16(); // reservedPad
    
      $startCode = array();
      for($i = 0; $i < $segCount; $i++) {
        $startCode[] = $this->readUInt16();
      }
      
      $idDelta = array();
      for($i = 0; $i < $segCount; $i++) {
        $idDelta[] = $this->readUInt16();
      }
      
      $idRangeOffset = array();
      for($i = 0; $i < $segCount; $i++) {
        $idRangeOffset[] = $this->readUInt16();
      }
      
      $glyphIndexArray = array();
      for($i = 0; $i < $segCount; $i++) {
        $c1 = $startCode[$i];
        $c2 = $endCode[$i];
        $d  = $idDelta[$i];
        $ro = $idRangeOffset[$i];
        
        if($ro > 0)
          $this->seek($subtable["offset"] + 2 * $i + $ro);
          
        for($c = $c1; $c <= $c2; $c++) {
          if($c == 0xFFFF)
            break;
            
          if($ro > 0){
            $gid = $this->readUInt16();
            if($gid > 0) $gid += $d;
          }
          else
            $gid = $c+$d;
            
          if($gid >= 65536)
            $gid -= 65536;
            
          if($gid > 0) {
            $glyphIndexArray[$c] = $gid;
          }
        }
      }
      
      $subtable += array(
        "endCode"         => $endCode,
        "startCode"       => $startCode,
        "idDelta"         => $idDelta,
        "idRangeOffset"   => $idRangeOffset,
        "glyphIndexArray" => $glyphIndexArray,
      );
    }
    
    $this->quitTag();
  }
  
  function parseNAME(){
    $this->seekTag("name");
    
    $tableOffset = $this->table["name"]->offset;
    
    $pack = "nformat/ncount/nstringOffset";
    $this->data["name"] = unpack($pack, $this->read(6));
    $nameData = &$this->data["name"];
    
    $offset = $tableOffset + $nameData["stringOffset"];
    
    $nameRecords = array();
    for($i = 0; $i < $nameData["count"]; $i++) {
      $nameRecords[] = unpack("nplatformID/nplatformSpecificID/nlanguageID/nnameID/nlength/noffset", $this->read(12));
    }
    
    $names = array();
    foreach($nameRecords as $nameRecord) {
      $this->seek($tableOffset + $nameData["stringOffset"] + $nameRecord["offset"]);
      $s = $this->read($nameRecord["length"]);
      
      $s = str_replace(chr(0), '', $s);
      //$s = preg_replace('|[ \[\](){}<>/%]|', '', $s);
      
      $names[$nameRecord["nameID"]] = $s;
    }
    
    $nameData["nameRecord"] = $names;
    
    $this->quitTag();
  }
  
  function parseHMTX(){
    $this->seekTag("hmtx");
    
    $hheaData = $this->getData("hhea");
    $numOfLongHorMetrics = $hheaData["numOfLongHorMetrics"];
    
    $hMetrics = array();
    for($i = 0; $i < $numOfLongHorMetrics; $i++) {
      $advanceWidth = $this->readUInt16();
      $leftSideBearing = $this->readUInt16();
      $hMetrics[$i] = $advanceWidth;
    }
    
    $numGlyphs = $this->data["maxp"]["numGlyphs"];
    if($numOfLongHorMetrics < $numGlyphs){
      $lastWidth = end($hMetrics);
      $hMetrics = array_pad($hMetrics, $numGlyphs, $lastWidth);
    }
    
    $this->data["hmtx"]["hMetrics"] = $hMetrics;
    
    $this->quitTag();
  }
  
  function parseOS2(){
    $this->seekTag("OS/2");
    
    $this->data["OS/2"] = array(
      "version" => $this->readUInt16(),
    );
    // @todo use $this->unpack()
    $pack = "nxAvgCharWidth/nusWeightClass/nusWidthClass/sfsType/".
            "nySubscriptXSize/nySubscriptYSize/nySubscriptXOffset/nySubscriptYOffset/".
            "nySuperscriptXSize/nySuperscriptYSize/nySuperscriptXOffset/nySuperscriptYOffset/".
            "nyStrikeoutSize/nyStrikeoutPosition/nsFamilyClass";
    
    $this->data["OS/2"] += unpack($pack, $this->read(30));
    
    $data = &$this->data["OS/2"];
    
    for($i = 0; $i < 10; $i++) {
      $data["panose"][] = ord($this->read(1));
    }
    
    for($i = 0; $i < 4; $i++) {
      $data["ulCharRange"][] = $this->readUInt32();
    }
    
    $data["achVendID"] = $this->read(4);
    $data["fsSelection"] = $this->readUInt16();
    $data["fsFirstCharIndex"] = $this->readUInt16();
    $data["fsLastCharIndex"] = $this->readUInt16();
    
    $this->quitTag();
  }
  
  function parsePOST(){
    $this->readTable("post");
  }
  
  function normalizeFUnit($value, $base = 1000){
    return round($value * ($base / $this->data["head"]["unitsPerEm"]));
  }
  
  protected function seekTag($tag) {
    if (!isset($this->table[$tag])) {
      return false;
    }
    
    return $this->seek($this->table[$tag]->offset);
  }
  
  protected function quitTag(){ }
  
  protected function readTable($name) {
    $this->seekTag($name);
    $this->data[$name] = $this->unpack(self::$tableFormat[$name]);
    $this->quitTag();
  }
  
  public function getData($name) {
    if (empty($this->table)) {
      $this->parseTableEntries();
    }
    
    if (empty($this->data[$name])) {
      $method = "parse".(preg_replace("/[^A-Z0-9]/", "", strtoupper($name))); 
      if (method_exists($this, $method)) {
        $this->$method();
      }
    }
    
    return $this->data[$name];
  }
  
  function saveAdobeFontMetrics($file) {
    $data = $this->data;
    
    $afm = new Adobe_Font_Metrics($file);
    
    $afm->startSection("FontMetrics", 4.1);
      $afm->addPair("Notice", "Converted by PHP-font-lib");
      $afm->addPair("EncodingScheme", "FontSpecific");
      
      $nameRecords = $data["name"]["nameRecord"];
      foreach($nameRecords as $id => $value) {
        if (!isset(self::$nameIdCodes[$id]) || preg_match("/[\r\n]/", $value)) {
          continue;
        }
        
        $afm->addPair(self::$nameIdCodes[$id], $value);
      }
      
      $os2 = $data["OS/2"];
      $afm->addPair("Weight", ($os2["usWeightClass"] > 400 ? "Bold" : "Medium"));
      
      $post = $data["post"];
      $afm->addPair("ItalicAngle",        $post["italicAngle"]);
      $afm->addPair("IsFixedPitch",      ($post["isFixedPitch"] ? "true" : "false"));
      $afm->addPair("UnderlineThickness", $this->normalizeFUnit($post["underlineThickness"]));
      $afm->addPair("UnderlinePosition",  $this->normalizeFUnit($post["underlinePosition"]));
      
      $hhea = $data["hhea"];
      $afm->addPair("Ascender",  $this->normalizeFUnit($hhea["ascent"]));
      $afm->addPair("Descender", $this->normalizeFUnit($hhea["descent"]));
      
      $head = $data["head"];
      $afm->addArray("FontBBox", array(
        $this->normalizeFUnit($head["xMin"]),
        $this->normalizeFUnit($head["yMin"]),
        $this->normalizeFUnit($head["xMax"]),
        $this->normalizeFUnit($head["yMax"]),
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
            
        $afm->startSection("CharMetrics", count($hmtx));
        
          foreach($subtable["glyphIndexArray"] as $c => $g) {
            if (empty($hmtx[$g])) continue;
            
            $afm->addMetric(array(
              "U" => $c,
              "WX" => $this->normalizeFUnit($hmtx[$g]),
              "N" => sprintf("uni%04x", $c),
              "G" => $g,
            ));
          }
          
        $afm->endSection("CharMetrics");
      }
      
    $afm->endSection("FontMetrics");
  }
}
