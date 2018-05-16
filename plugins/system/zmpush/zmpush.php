<?php 
/**
 * @package System - ZMPush - Web Push Notifications
 * @version 1.0.1
 * @author ZuestMedia GmbH
 * @website https://zmpush.com
 * @copyright (C) ZuestMedia GmbH
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

class plgSystemzmpush extends JPlugin {
	
	function onAfterRender() {
		
		$mainframe = JFactory::getApplication();

		$zmpush_id = $this->params->get('zmpush_id', '');

		if($mainframe->isAdmin() || strpos($_SERVER["PHP_SELF"], "index.php") === false || $zmpush_id == '' || strlen($zmpush_id) != 32) {
			
			return;
			
		}

		$zmhttpadress = JURI::base().'plugins/system/zmpush/app/';
		$javascript ='<!-- ZMPush App-Url: '.$zmhttpadress.' --><script type="text/javascript" src="https://cdn.zmpush.com/v3/'. $zmpush_id .'/src/zmpush.js"></script>';

		$zmtobody = JResponse::getBody();
		$zmtobody = preg_replace ("/<\/head>/", "\n\n".$javascript."\n\n</head>", $zmtobody);
		JResponse::setBody($zmtobody);
		return true;
		
	}
	
}
?>