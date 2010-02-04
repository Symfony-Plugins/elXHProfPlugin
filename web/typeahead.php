<?php
//  Copyright (c) 2009 Facebook
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

/**
 * AJAX endpoint for XHProf function name typeahead.
 *
 * @author(s)  Kannan Muthukkaruppan
 *             Changhao Jiang
 */

// by default assume that xhprof_html & xhprof_lib directories
// are at the same level.

// lib/vendor/facebook/xhprof path discovery
$paths = array(
  dirname(__FILE__) . '/../lib/vendor/facebook/xhprof/', // Path if plugin web directory available via plugin:publish-assets
  dirname(__FILE__) . '/../../plugins/elXHProfPlugin/lib/vendor/facebook/xhprof/', // Path if plugin web directory available via a copy of the directory
);
$pathFound = null;
foreach($paths as $path) {
  if(is_dir($path)) {
    $pathFound = $path;
    break;
  }
}
if(is_null($pathFound)) {
  throw new Exception("plugins/elXHProfPlugin/lib/vendor/facebook/xhprof not available");
}
$GLOBALS['XHPROF_LIB_ROOT'] = $pathFound;

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';

$xhprof_runs_impl = new XHProfRuns_Default();

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/typeahead_common.php';
