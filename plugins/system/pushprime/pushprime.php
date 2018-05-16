<?php
######################################################################
# Copyright (C) 2016 by  PushPrime.com  	   	   	   	   	   	 #
# Homepage   : https://pushprime.com		   	   	   	   	   	 #
# Author     : PushPrime		   	   	   	   	   	   	   	 #
# Email      : info@pushprime.com.com	   	   	   	   	   	     #
# Version    : 1.0.0	                       	   	    	   	   	 #
# License    : http://www.gnu.org/copyleft/gpl.html GNU/GPL          #
######################################################################

defined( '_JEXEC' ) or die;

class plgSystempushprime extends JPlugin
{
    /**
     * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
     * If you want to support 3.0 series you must override the constructor
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Plugin method with the same name as the event will be called automatically.
     */
    function onAfterRender()
    {
        $mainframe = JFactory::getApplication();

        if($mainframe->isAdmin() || strpos($_SERVER["PHP_SELF"], "index.php") === false || JRequest::getVar('format','html') != 'html'){
            return;
        }

        $website_id = $this->params->get('pushprime_id', '');

        if($website_id == '' || $mainframe->isAdmin() || strpos($_SERVER["PHP_SELF"], "index.php") === false)
        {
            return;
        }

        $buffer = JResponse::getBody();

        $javascript ='<script type=\'text/javascript\'>
window.pup=window.pup||[];(function(w,d,s){var f=d.getElementsByTagName(s)[0];var j=d.createElement(s);j.async=true;j.src=\'https://pushprime-cdn.com/clients/embed/'.$website_id.'.js\';f.parentNode.insertBefore(j,f);})(window,document,\'script\');
</script>';

        $buffer = JResponse::getBody();
        $buffer = preg_replace ("/<\/head>/", "\n\n".$javascript."\n\n</head>", $buffer);
        JResponse::setBody($buffer);
        return true;
    }
}
?>