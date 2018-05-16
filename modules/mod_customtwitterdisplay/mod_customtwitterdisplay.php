<?php
/*------------------------------------------------------------------------
# mod_customtwitterdisplay - Custom Twitter Display
# ------------------------------------------------------------------------
# @author - Free Widgets
# copyright Copyright (C) 2013 FreeWidgets.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://freewidgets.net/
# Technical Support:  Forum - http://freewidgets.net/index.php/contact
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die;
require_once __DIR__ . '/TwitterAPIExchange.php';
require JModuleHelper::getLayoutPath('mod_customtwitterdisplay', $params->get('layout'));