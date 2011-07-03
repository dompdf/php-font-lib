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

class Adobe_Font_Metrics {
  var $f;
  
  function __construct($file) {
    $this->f = fopen($file, "w+");
  }
  
  function addLine($line) {
    fwrite($this->f, "$line\n");
  }
  
  function addPair($key, $value) {
    $this->addLine("$key $value");
  }
  
  function addArray($key, $array) {
    $this->addLine("$key ".implode(" ", $array));
  }
  
  function addMetric($data) {
    $array = array();
    foreach($data as $key => $value) {
      $array[] = "$key $value";
    }
    $this->addLine(implode(" ; ", $array));
  }

  function startSection($name, $value) {
    $this->addLine("Start$name $value");
  }
  
  function endSection($name) {
    $this->addLine("End$name");
  }
}
