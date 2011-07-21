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

class Font_Table_head extends Font_Table {
  protected $def = array(
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
  );
  
  protected function _parse(){
    parent::_parse();
    
    if($this->data["magicNumber"] != 0x5F0F3CF5) {
      throw new Exception("Incorrect magic number (".dechex($this->data["magicNumber"]).")");
    }
  }
}