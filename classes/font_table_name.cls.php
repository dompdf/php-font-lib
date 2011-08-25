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

$dir = dirname(__FILE__);
require_once "$dir/font_table_name_record.cls.php";

class Font_Table_name extends Font_Table {
  private static $header_format = array(
    "format"       => self::uint16,
    "count"        => self::uint16,
    "stringOffset" => self::uint16,
  );
  
  protected function _parse(){
    $font = $this->getFont();
    $data = array();
    
    $tableOffset = $font->pos();
    
    $data = $font->unpack(self::$header_format);
    
    $records = array();
    for($i = 0; $i < $data["count"]; $i++) {
      $record = new Font_Table_name_Record();
      $record_data = $font->unpack(Font_Table_name_Record::$format);
      $record->map($record_data);
      
      $records[] = $record;
    }
    
    $names = array();
    foreach($records as $record) {
      $font->seek($tableOffset + $data["stringOffset"] + $record->offset);
      $s = $font->read($record->length);
      
      $s = str_replace(chr(0), '', $s);
      //$s = preg_replace('|[ \[\](){}<>/%]|', '', $s);
      
      $record->string = $s;
      $names[$record->nameID] = $record;
    }
    
    $data["records"] = $names;
    
    $this->data = $data;
  }
  
  protected function _encode(){
    $font = $this->getFont();
    
    $records = $this->data["records"];
    $count_records = count($records);
    
    $this->data["count"] = $count_records;
    $this->data["stringOffset"] = 6 + $count_records * 12; // 6 => uint16 * 3, 12 => sizeof self::$record_format
    
    $table_offset = $font->pos();
    
    $length = $font->pack(self::$header_format, $this->data);
    
    $records_offset = $font->pos();
    
    foreach($records as $record) {
      $record->length = strlen($record->string);
      $record->offset = $font->pos() - $records_offset;
      $length += $font->pack(self::$record_format, $record);
    }
    
    foreach($records as $record) {
      $length += $font->write($record->string, strlen($record->string));
    }
    
    return $length;
  }
}