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

class Font {
  public static function load($file) {
    $header = file_get_contents($file, false, null, null, 4);
    $class = null;
    
    switch($header) {
      case "\x00\x01\x00\x00": 
      case "true": 
      case "typ1": 
        $class = "Font_TrueType"; break;
      
      case "OTTO":
        $class = "Font_OpenType"; break;
      
      case "wOFF":
        $class = "Font_WOFF"; break;
        
      case "ttcf":
        $class = "Font_TrueType_Collection"; break;
        
      // Unknown type or EOT
      default: 
        $magicNumber = file_get_contents($file, false, null, 34, 2);
        
        if ($magicNumber === "LP") {
          $class = "Font_EOT";
        }
    }
    
    if ($class) {
      require_once dirname(__FILE__)."/".strtolower($class).".cls.php";
      
      $obj = new $class;
      $obj->load($file);
      
      return $obj;
    }
  }
}
