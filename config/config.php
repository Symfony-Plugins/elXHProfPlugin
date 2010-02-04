<?php

require_once(dirname(__FILE__).'/../lib/elWebDebugPanelXHProf.class.php');

$this->dispatcher->connect('debug.web.load_panels', array('elWebDebugPanelXHProf', 'listenToAddPanelEvent'));