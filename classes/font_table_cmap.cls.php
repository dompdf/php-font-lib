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

class Font_Table_cmap extends Font_Table {
  private static $header_format = array(
    "version"         => self::uint16,
    "numberSubtables" => self::uint16,
  );
  
  private static $subtable_header_format = array(
    "platformID"         => self::uint16,
    "platformSpecificID" => self::uint16,
    "offset"             => self::uint32,
  );
  
  private static $subtable_v4_format = array(
    "length"        => self::uint16, 
    "language"      => self::uint16, 
    "segCountX2"    => self::uint16, 
    "searchRange"   => self::uint16, 
    "entrySelector" => self::uint16, 
    "rangeShift"    => self::uint16,
  );
  
  protected function _parse(){
    $font = $this->getFont();
    
    $data = $font->unpack(self::$header_format);
    
    $subtables = array();
    for($i = 0; $i < $data["numberSubtables"]; $i++){
      $subtables[] = $font->unpack(self::$subtable_header_format);
    }
    $data["subtables"] = $subtables;
    
    $tables = $font->getTable();
    $cmap_offset = $tables["cmap"]->offset;
    
    foreach($data["subtables"] as $i => &$subtable) {
      $font->seek($cmap_offset + $subtable["offset"]);
      
      $subtable["format"] = $font->readUInt16();
      
      // @todo Only CMAP version 4
      if($subtable["format"] != 4) {
        unset($data["subtables"][$i]);
        continue;
      }
      
      $subtable += $font->unpack(self::$subtable_v4_format);
      $segCount = $subtable["segCountX2"] / 2;
      $subtable["segCount"] = $segCount;
      
      $endCode       = $font->r(array(self::uint16, $segCount));
      
      $font->readUInt16(); // reservedPad
      
      $startCode     = $font->r(array(self::uint16, $segCount));
      $idDelta       = $font->r(array(self::uint16, $segCount));
      
      $ro_start      = $font->pos();
      $idRangeOffset = $font->r(array(self::uint16, $segCount));
      
      $glyphIndexArray = array();
      for($i = 0; $i < $segCount; $i++) {
        $c1 = $startCode[$i];
        $c2 = $endCode[$i];
        $d  = $idDelta[$i];
        $ro = $idRangeOffset[$i];
        
        if($ro > 0)
          $font->seek($subtable["offset"] + 2 * $i + $ro);
          
        for($c = $c1; $c <= $c2; $c++) {
          if ($ro == 0)
            $gid = ($c + $d) & 0xFFFF;
          else {
            $offset = ($c - $c1) * 2 + $ro;
            $offset = $ro_start + 2 * $i + $offset;
            
            $font->seek($offset);
            $gid = $font->readUInt16();
            
            if ($gid != 0)
               $gid = ($gid + $d) & 0xFFFF;
          }
          
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
    
    $this->data = $data;
  }
  
  function _encode(){
    $font = $this->getFont();
    
    $subtables = $this->data["subtables"];
    $this->data["numberSubtables"] = count($subtables);
    
    $length = $font->pack(self::$header_format, $this->data);
    
    // etc
    
    return $length;
  }
}
