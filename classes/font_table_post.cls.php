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

class Font_Table_post extends Font_Table {
  protected function _parse(){
    $font = $this->entry->getFont();
    
    $data = $font->unpack(array(
      "format"             => self::Fixed,
      "italicAngle"        => self::Fixed,
      "underlinePosition"  => self::FWord,
      "underlineThickness" => self::FWord,
      "isFixedPitch"       => self::uint32,
      "minMemType42"       => self::uint32,
      "maxMemType42"       => self::uint32,
      "minMemType1"        => self::uint32,
      "maxMemType1"        => self::uint32,
    ));
    
    $names = array();
    
    switch($data["format"]) {
      case 1:
        $names = Font_TrueType::$macCharNames;
      break;
      
      case 2:
        $data["numberOfGlyphs"] = $font->readUInt16();
        
        $glyphNameIndex = array();
        for($i = 0; $i < $data["numberOfGlyphs"]; $i++) {
          $glyphNameIndex[] = $font->readUInt16();
        }
        
        $namesPascal = array();
        for($i = 0; $i < $data["numberOfGlyphs"]; $i++) {
          $len = $font->readUInt8();
          $namesPascal[] = $font->read($len);
        }
        
        foreach($glyphNameIndex as $g => $index) {
          if ($index < 258) {
            $names[$g] = Font_TrueType::$macCharNames[$index];
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
    
    $this->data = $data;
  }
}