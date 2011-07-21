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

class Font_Table_name extends Font_Table {
  protected function _parse(){
    $font = $this->entry->getFont();
    $data = array();
    
    $tableOffset = $font->pos();
    
    $data = $font->unpack(array(
      "format"       => self::uint16,
      "count"        => self::uint16,
      "stringOffset" => self::uint16,
    ));
    
    $records = array();
    for($i = 0; $i < $data["count"]; $i++) {
      $records[] = $font->unpack(array(
        "platformID" => self::uint16,
        "platformSpecificID" => self::uint16,
        "languageID" => self::uint16,
        "nameID" => self::uint16,
        "length" => self::uint16,
        "offset" => self::uint16,
      ));
    }
    
    $names = array();
    foreach($records as $record) {
      $font->seek($tableOffset + $data["stringOffset"] + $record["offset"]);
      $s = $font->read($record["length"]);
      
      $s = str_replace(chr(0), '', $s);
      //$s = preg_replace('|[ \[\](){}<>/%]|', '', $s);
      
      $names[$record["nameID"]] = $s;
    }
    
    $data["records"] = $names;
    
    $this->data = $data;
  }
}