<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$this->disable_modifications = true;
$this->setLayout('cart');
echo $this->loadTemplate();
$this->disable_modifications = false;