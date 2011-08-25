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

class Font_Table_loca extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    
    $indexToLocFormat = $font->getData("head", "indexToLocFormat");
    $numGlyphs = $font->getData("maxp", "numGlyphs");
    
    $data = array();
    
    // 2 bytes
    if ($indexToLocFormat == 0) {
      $d = $font->read(($numGlyphs + 1) * 2);
      $loc = unpack("n*", $d);
      
      for ($i = 0; $i <= $numGlyphs; $i++) {
        $data[] = $loc[$i+1] * 2;
      }
    }
    
    // 4 bytes
    else if ($indexToLocFormat == 1) {
      $d = $font->read(($numGlyphs + 1) * 4);
      $loc = unpack("N*", $d);
      
      for ($i = 0; $i <= $numGlyphs; $i++) {
        $data[] = $loc[$i+1];
      }
    }
    
    $this->data = $data;
  }
}