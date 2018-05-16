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
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base()."modules/mod_customtwitterdisplay/assets/twitter-styles.css","text/css","screen");
/*twitter settings*/
$settings = array(
    'oauth_access_token' => trim($params->get('accesstoken')),
    'oauth_access_token_secret' => trim($params->get('accesstokensecret')),
    'consumer_key' => trim($params->get('consumerkey')),
    'consumer_secret' => trim($params->get('consumersecret'))
);
/*URL - Twitter Timeline*/
$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
 
$requestMethod = "GET";
 
$getfield = '?screen_name='.$params->get('twitter_username').'&count='.$params->get('count');
 
$twitter = new TwitterAPIExchange($settings);
$string = json_decode($twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest(),$assoc = TRUE);

function addLink($string)
	{
		$pattern = '/((ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/i';
		$replacement = '<a class="tweet_url" href="$1">$1</a>';
		$string = preg_replace($pattern, $replacement, $string);
		return $string;
	}
echo '<div id="twitter-feed">';
foreach($string as $items)
    {
		echo '<div class="twitter-article">';
		echo '<div class="twitter-pic"><a href="https://twitter.com/'.$items['user']['screen_name'].'" ><img src="'.$items['user']['profile_image_url_https'].'"images/twitter-feed-icon.png" width="42" height="42" alt="twitter icon" /></a></div>';
		echo '<div class="twitter-text"><p><span class="tweetprofilelink"><strong><a href="https://twitter.com/'.$items['user']['screen_name'].'" >'.$items['user']['name'].'</a></strong> <a href="https://twitter.com/'.$items['user']['screen_name'].'" >@'.$items['user']['screen_name'].'</a></span><span class="tweet-time"><a href="https://twitter.com/'.$items['user']['screen_name'].'/status/'.$items['id_str'].'"></a></span><br/>'.addLink($items['text']).'</p></div>';
		echo '</div>';
    }?>
    <div align="left" style="color:#024292;margin-bottom:3px;font-size:9px">
	<a target="_blank" class="external" title="www.cprcertification.com" href="https://www.nationalcprassociation.com/acls-certification/">
		<span style="color:#024292;margin-bottom:3px;font-size:9px">ACLS</span>
	</a>
</div>
    <?php 
echo '</div>';