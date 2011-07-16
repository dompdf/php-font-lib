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
  public $header = array();
  
  private $tableOffset = 0; // Used for TTC
  
  protected $table = array();
  protected $data = array();
  
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
    0  => "Copyright",
    1  => "FontName",
    2  => "FontSubfamily",
    3  => "UniqueID",
    4  => "FullName",
    5  => "Version",
    6  => "PostScriptName",
    7  => "Trademark",
    8  => "Manufacturer",
    9  => "Designer",
    10 => "Description",
    11 => "FontVendorURL",
    12 => "FontDesignerURL",
    13 => "LicenseDescription",
    14 => "LicenseURL",
 // 15
    16 => "PreferredFamily",
    17 => "PreferredSubfamily",
    18 => "CompatibleFullName",
    19 => "SampleText",
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
  
  static $macCharNames = array(
    ".notdef", ".null", "CR",
    "space", "exclam", "quotedbl", "numbersign",
    "dollar", "percent", "ampersand", "quotesingle",
    "parenleft", "parenright", "asterisk", "plus",
    "comma", "hyphen", "period", "slash",
    "zero", "one", "two", "three",
    "four", "five", "six", "seven",
    "eight", "nine", "colon", "semicolon",
    "less", "equal", "greater", "question",
    "at", "A", "B", "C",
    "D", "E", "F", "G",
    "H", "I", "J", "K",
    "L", "M", "N", "O",
    "P", "Q", "R", "S",
    "T", "U", "V", "W",
    "X", "Y", "Z", "bracketleft",
    "backslash", "bracketright", "asciicircum", "underscore",
    "grave", "a", "b", "c",
    "d", "e", "f", "g",
    "h", "i", "j", "k",
    "l", "m", "n", "o",
    "p", "q", "r", "s",
    "t", "u", "v", "w",
    "x", "y", "z", "braceleft",
    "bar", "braceright", "asciitilde", "Adieresis",
    "Aring", "Ccedilla", "Eacute", "Ntilde",
    "Odieresis", "Udieresis", "aacute", "agrave",
    "acircumflex", "adieresis", "atilde", "aring",
    "ccedilla", "eacute", "egrave", "ecircumflex",
    "edieresis", "iacute", "igrave", "icircumflex",
    "idieresis", "ntilde", "oacute", "ograve",
    "ocircumflex", "odieresis", "otilde", "uacute",
    "ugrave", "ucircumflex", "udieresis", "dagger",
    "degree", "cent", "sterling", "section",
    "bullet", "paragraph", "germandbls", "registered",
    "copyright", "trademark", "acute", "dieresis",
    "notequal", "AE", "Oslash", "infinity",
    "plusminus", "lessequal", "greaterequal", "yen",
    "mu", "partialdiff", "summation", "product",
    "pi", "integral", "ordfeminine", "ordmasculine",
    "Omega", "ae", "oslash", "questiondown",
    "exclamdown", "logicalnot", "radical", "florin",
    "approxequal", "increment", "guillemotleft", "guillemotright",
    "ellipsis", "nbspace", "Agrave", "Atilde",
    "Otilde", "OE", "oe", "endash",
    "emdash", "quotedblleft", "quotedblright", "quoteleft",
    "quoteright", "divide", "lozenge", "ydieresis",
    "Ydieresis", "fraction", "currency", "guilsinglleft",
    "guilsinglright", "fi", "fl", "daggerdbl",
    "periodcentered", "quotesinglbase", "quotedblbase", "perthousand",
    "Acircumflex", "Ecircumflex", "Aacute", "Edieresis",
    "Egrave", "Iacute", "Icircumflex", "Idieresis",
    "Igrave", "Oacute", "Ocircumflex", "applelogo",
    "Ograve", "Uacute", "Ucircumflex", "Ugrave",
    "dotlessi", "circumflex", "tilde", "macron",
    "breve", "dotaccent", "ring", "cedilla",
    "hungarumlaut", "ogonek", "caron", "Lslash",
    "lslash", "Scaron", "scaron", "Zcaron",
    "zcaron", "brokenbar", "Eth", "eth",
    "Yacute", "yacute", "Thorn", "thorn",
    "minus", "multiply", "onesuperior", "twosuperior",
    "threesuperior", "onehalf", "onequarter", "threequarters",
    "franc", "Gbreve", "gbreve", "Idot",
    "Scedilla", "scedilla", "Cacute", "cacute",
    "Ccaron", "ccaron", "dmacron"
  );
  
  function getTable(){
    $this->parseTableEntries();
    return $this->table;
  }
  
  function setTableOffset($offset) {
    $this->tableOffset = $offset;
  }
  
  function parse() {
    $this->parseHEAD();
    $this->parseHHEA();
    $this->parseMAXP();
    $this->parseCMAP();
    $this->parseHMTX();
    $this->parseNAME();
    $this->parseOS2();
    $this->parsePOST();
    $this->parseKERN();
    $this->parseLOCA();
    $this->parseGLYF();
  }
  
  function parseHeader(){
		if (!empty($this->header)) {
      return;
		}
		
    $this->seek($this->tableOffset);
    
    $this->header = $this->unpack(array(
      "format"        => self::uint32,
      "numTables"     => self::uint16,
      "searchRange"   => self::uint16,
      "entrySelector" => self::uint16,
      "rangeShift"    => self::uint16,
    ));
    
    $format = $this->header["format"];
    $this->header["formatText"] = chr(($format >> 24) & 0xFF).chr(($format >> 16) & 0xFF).chr(($format >> 8) & 0xFF).chr($format & 0xFF);
  }
  
  function parseTableEntries(){
    $this->parseHeader();
    
    if (!empty($this->table)) {
      return;
    }
    
    for($i = 0; $i < $this->header["numTables"]; $i++) {
      $str = $this->read(Font_TrueType_Table_Directory_Entry::$entrySize);
      $entry = new Font_TrueType_Table_Directory_Entry($str);
      $this->table[$entry->tag] = $entry;
    }
  }
  
  function parseHEAD() {
    $this->readTable("head");
    
    if($this->data["head"]["magicNumber"] != 0x5F0F3CF5) {
      throw new Exception("Incorrect magic number (".dechex($this->data["head"]["magicNumber"]).")");
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
      
      $subtable["format"] = $this->readUInt16();
      
      // @todo Only CMAP version 4
      if($subtable["format"] != 4) continue;
      
      $pack = array(
        "length"        => self::uint16, 
        "language"      => self::uint16, 
        "segCountX2"    => self::uint16, 
        "searchRange"   => self::uint16, 
        "entrySelector" => self::uint16, 
        "rangeShift"    => self::uint16,
      );
      
      $subtable += $this->unpack($pack);
      $segCount = $subtable["segCountX2"] / 2;
      $subtable["segCount"] = $segCount;
      
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
      
      $ro_start = ftell($this->f);
      
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
          if ($ro == 0)
            $gid = ($c + $d) & 0xFFFF;
          else {
            $offset = ($c - $c1) * 2 + $ro;
            $offset = $ro_start + 2 * $i + $offset;
            
            $this->seek($offset);
            $gid = $this->readUInt16();
            
            if ($gid != 0)
               $gid = ($gid + $d) & 0xFFFF;
          }
          
          if($gid > 0) {
            $glyphIndexArray[$c] = $gid;
          }
          /*if($c == 0xFFFF)
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
          }*/
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
  
  function mapCharCode($charCode, $cmap) {
    $segCount       = $cmap["segCount"];
    $endCode        = $cmap["endCode"];
    $startCode      = $cmap["startCode"];
    $idDelta        = $cmap["idDelta"];
    $idRangeOffset  = $cmap["idRangeOffset"];
    $glyphIndexArray = $cmap["glyphIndexArray"];
    
    if (($charCode < 0) || ($charCode >= 0xFFFE))
      return 0;

    for ($i = 0; $i < $segCount; $i++) {
      if ($endCode[$i] >= $charCode) {
        if ($startCode[$i] <= $charCode) {
          if ($idRangeOffset[$i] > 0) {
            return $glyphIndexArray[$idRangeOffset[$i]/2 +
                                ($charCode - $startCode[$i]) -
                                ($segCount - $i)];
          } else {
            return ($idDelta[$i] + $charCode) % 65536;
          }
        } 
        else {
          break;
        }
      }
    }
    
    return 0;
  }
  
  function parseNAME(){
    $this->seekTag("name");
    
    $tableOffset = ftell($this->f);
    
    $pack = "nformat/ncount/nstringOffset";
    $this->data["name"] = unpack($pack, $this->read(6));
    $nameData = &$this->data["name"];
    
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
    $numOfLongHorMetrics = $this->getData("hhea", "numOfLongHorMetrics");
    
    $this->seekTag("hmtx");
    
    $this->data["hmtx"]["numOfLongHorMetrics"] = $numOfLongHorMetrics;
    
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
    $data["typoAscender"] = $this->readInt16();
    $data["typoDescender"] = $this->readInt16();
    $data["typoLineGap"] = $this->readInt16();
    $data["winAscent"] = $this->readInt16();
    $data["winDescent"] = $this->readInt16();
    
    $this->quitTag();
  }
  
  function parsePOST(){
    $numGlyphs = $this->getData("maxp", "numGlyphs");
    
    $name = "post";
    
    $this->seekTag($name);
    $data = $this->unpack(self::$tableFormat[$name]);
    $names = array();
    
    switch($data["format"]) {
      case 1:
        $names = self::$macCharNames;
      break;
      
      case 2:
        $data["numberOfGlyphs"] = $this->readUInt16();
        
        $glyphNameIndex = array();
        for($i = 0; $i < $data["numberOfGlyphs"]; $i++) {
          $glyphNameIndex[] = $this->readUInt16();
        }
        
        $num = max($data["numberOfGlyphs"] - 257, $numGlyphs);
        
        $namesPascal = array();
        for($i = 0; $i < $num; $i++) {
          $len = $this->readUInt8();
          $namesPascal[] = $this->read($len);
        }
        
        foreach($glyphNameIndex as $g => $index) {
          if ($index < 258) {
            $names[$g] = self::$macCharNames[$index];
          }
          else {
            $names[$g] = $namesPascal[$index - 258];
          }
        }
        
        $data["glyphNameIndex"] = $glyphNameIndex;
        
      break;
      
      case 2.5:
        // TODO
      break;
      
      case 3:
        // nothing
      break;
      
      case 4:
        // TODO
      break;
    }
    
    $data["names"] = $names;
    
    $this->data[$name] = $data;
    $this->quitTag();
  }
  
  function parseKERN(){
    $name = "kern";
    
    if (!$this->seekTag($name)){
      return;
    }
    
    $tableOffset = ftell($this->f);
    
    $data = array(
      "version"    => $this->readUInt16(),
      "nTables"    => $this->readUInt16(),
    
      // only the first subtable will be parsed
      "subtableVersion" => $this->readUInt16(),
      "length"     => $this->readUInt16(),
      "coverage"   => $this->readUInt16(),
    );
    
    $data["format"] = ($data["coverage"] >> 8);
    
    $subtable = array();
    
    switch($data["format"]) {
      case 0:
      $subtable = array(
        "nPairs"        => $this->readUInt16(),
        "searchRange"   => $this->readUInt16(),
        "entrySelector" => $this->readUInt16(),
        "rangeShift"    => $this->readUInt16(),
      );
      
      $pairs = array();
      $tree = array();
       
      for ($i = 0; $i < $subtable["nPairs"]; $i++) {
        $left  = $this->readUInt16();
        $right = $this->readUInt16();
        $value = $this->readInt16();
        
        $pairs[] = array(
          "left"  => $left,
          "right" => $right,
          "value" => $value,
        );
        
        $tree[$left][$right] = $value;
      }
      
      //$subtable["pairs"] = $pairs;
      $subtable["tree"] = $tree;
      break;
      
      case 1:
      case 2:
      case 3:
      break;
    }
    
    $data["subtable"] = $subtable;
    
    $this->data[$name] = $data;
    $this->quitTag();
  }

  function parseLOCA(){
    $indexToLocFormat = $this->getData("head", "indexToLocFormat");
    $numGlyphs = $this->getData("maxp", "numGlyphs");
    
    $name = "loca";
    
    if (!$this->seekTag($name)){
      return;
    }
    
    $tableOffset = ftell($this->f);
    
    $data = array();
    
    $this->seek($tableOffset);
      
    // 2 bytes
    if ($indexToLocFormat == 0) {
      $d = $this->read(($numGlyphs + 1) * 2);
      $loc = unpack("n*", $d);
      
      for ($i = 0; $i <= $numGlyphs; $i++) {
        $data[] = $loc[$i+1] * 2;
      }
    }
    
    // 4 bytes
    else if ($indexToLocFormat == 1) {
      $d = $this->read(($numGlyphs + 1) * 4);
      $loc = unpack("N*", $d);
      
      for ($i = 0; $i <= $numGlyphs; $i++) {
        $data[] = $loc[$i+1];
      }
    }
    
    $this->data[$name] = $data;
    $this->quitTag();
  }
  
  function parseGLYF(){
    $indexToLocFormat = $this->getData("head", "indexToLocFormat");
    $numGlyphs = $this->getData("maxp", "numGlyphs");
    
    $name = "glyf";
    
    if (!$this->seekTag($name)){
      return;
    }
    
    $tableOffset = ftell($this->f);
    
    $data = $this->unpack(array(
      "numberOfContours" => self::int16,
      "xMin" => self::FWord,
      "yMin" => self::FWord,
      "xMax" => self::FWord,
      "yMax" => self::FWord,
    ));
    
    $this->seek($tableOffset);
    
    $this->data[$name] = $data;
    $this->quitTag();
  }
  
  function normalizeFUnit($value, $base = 1000){
    return round($value * ($base / $this->getData("head", "unitsPerEm")));
  }
  
  protected function seekTag($tag) {
    $this->parseTableEntries();
    
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
  
  public function getData($name, $key = null) {
    $this->parseTableEntries();
    
    if (empty($this->data[$name])) {
      $method = "parse".(preg_replace("/[^A-Z0-9]/", "", strtoupper($name))); 
      if (method_exists($this, $method)) {
        $pos = ftell($this->f);
        $this->$method();
        $this->seek($pos);
      }
    }
    
    if (!isset($this->data[$name])) {
      return null;
    }
    
    if (!$key) {
      return $this->data[$name];
    }
    else {
      return $this->data[$name][$key];
    }
  }
  
  function saveAdobeFontMetrics($file, $encoding = null) {
    $afm = new Adobe_Font_Metrics($this);
    $afm->write($file, $encoding);
  }
}
