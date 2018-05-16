<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

class  plgSystemMobileDetection extends JPlugin
{
	public function onAfterInitialise()
	{	   		
	
	 try {
		$app = JFactory::getApplication();
    $appWeb = new JApplicationWeb();
    $session = JFactory::getSession();
    
    //don't change if logged in as admin
		if($app->isAdmin()){
			return;
		}
      
    //Set var if user click on view classic website via url get parameter
    $reqVar = JRequest::getVar('showclassic');
    if(!is_null($reqVar)) {
      $session->clear('mobile_detection_show_classic');
      if ($reqVar == "1" ) {
        $session->set('mobile_detection_show_classic', '1');
      }
      else 
        $session->set('mobile_detection_show_classic', '0');
    }
    
    //check if user want classic website
    $classicView = false;
    if(!is_null($session->get('mobile_detection_show_classic'))) {
      $classicView = $session->get('mobile_detection_show_classic');
      if($classicView) 
        return;
      else {   
        $success = $this->changeTemplate($app);
        return; 
      }
    }
              
    //check if mobile
    //$isMobile = $appWeb->client->mobile;
    //if(!$isMobile) return;
    $client = new JApplicationWebClient();
    $isMobile = $client->mobile;
    if(!$isMobile) return;

    //Set new Template
    if(!$classicView && $isMobile) 
      $success = $this->changeTemplate($app);
      if(!$success) return;  
    else
      return;

    } catch(Exception $e) {
      return;
    }    
	}	
	
	private function changeTemplate($app)
	{	
    //check if mobile template is set    
    $template_id = $this->params->get('template_style_id','-1');
    if($template_id == "-1") {
      //$templateCheck = false;
      return false;
    }
    
    //Check if Template exists and get template parameter
    $queryCheck = true;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('template'));
		$query->select($db->qn('params'));
		$query->from($db->qn('#__template_styles'));
		$query->where($db->qn('client_id'). ' = 0');
		$query->where($db->qn('id'). ' = "'.$template_id.'"');
		$query->order($db->qn('id'));
		$db->setQuery( $query );
		$row = $db->loadObject();

		if(!$row || empty($row->template)){
    	$queryCheck = false;
		}
		if(!is_dir(JPATH_THEMES."/".$row->template))
		  $queryCheck = false;
      	   
	  if($queryCheck) {    
      $app->setTemplate($row->template, (new JRegistry($row->params)));
      return true;
    } else return false;      
  }	
}
