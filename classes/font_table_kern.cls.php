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

class Font_Table_kern extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    
    $tableOffset = $font->pos();
    
    $data = $font->unpack(array(
      "version"    => self::uint16,
      "nTables"    => self::uint16,
    
      // only the first subtable will be parsed
      "subtableVersion" => self::uint16,
      "length"     => self::uint16,
      "coverage"   => self::uint16,
    ));
    
    $data["format"] = ($data["coverage"] >> 8);
    
    $subtable = array();
    
    switch($data["format"]) {
      case 0:
      $subtable = $font->unpack(array(
        "nPairs"        => self::uint16,
        "searchRange"   => self::uint16,
        "entrySelector" => self::uint16,
        "rangeShift"    => self::uint16,
      ));
      
      $pairs = array();
      $tree = array();
       
      for ($i = 0; $i < $subtable["nPairs"]; $i++) {
        $left  = $font->readUInt16();
        $right = $font->readUInt16();
        $value = $font->readInt16();
        
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
    
    $this->data = $data;
  }
}