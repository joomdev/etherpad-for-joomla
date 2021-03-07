<?php
// No direct access.
defined('_JEXEC') or die;
/**
 * @package		Either Pad for Joomla
 * @version		1.0
 * @author		JoomDev - www.JoomDev.com
 * @copyright	Copyright (C) 2021  www.JoomDev.com
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

class PlgSystemetherpad extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		
		$app 		= JFactory::getApplication();
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}
		$this->loadLanguage();		
		return true;
	}
	
	function onBeforeRender(){
		$jinput = JFactory::getApplication()->input;
		$createpad = $jinput->get('createpad', '', 'RAW');
		if($createpad) {
			$this->createPad();
			exit;
		}
	}
	
	 
	public function onContentPrepare($context, $article, $params, $page = 0)
	{
		$app 		= JFactory::getApplication();
		$pluginParams = $this->params;
		$cookiedomain 	  = $pluginParams->get('cookiedomain','','RAW');
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}
		$renderPlugin = false;
		$pluginParams = $this->params;
	 	$regex	= '/{\s?etherpad\s+(.*?)}/i';	
		if(preg_match_all($regex, $article->text,$matches, PREG_SET_ORDER))
		{	
		
			//$this->init();
			$i=0;
			foreach ($matches as $match) {
				if(!empty($match[1]) && $match[1] > 0 ){
					$documentList = $this->getDocumentList($match[1]);
					$output = $this->getOutput($documentList,'default.php');
					$findtext = $match[0];
					$article->text = JString::str_ireplace($findtext, $output, $article->text);
					$renderPlugin = true;
					$sessionid = $this->CreateSession($this->createGroupIfNotExistsFor($match[1]),$this->createAuthorIfNotExistsFor());
				}
				$i++;
			}
			if(isset($sessionid)) {
				$article->text = $article->text.'<script>document.cookie = "sessionID='.$sessionid.';domain=.'.$cookiedomain.'"</script>';
			}
		
			//$article->text = str_replace('SESSIONIDHERE',$sessionid,$article->text);
		
		  //$grid = $article->text; 
		  //$article->text = $grid;
		  
		}
		return true;
	}
	
	
	function getDocumentList($groupid)
	{
	 	// load the admin language file
		JFactory::getLanguage()->load('plg_' . $this->_type . '_' . $this->_name, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name);
		
		$db = JFactory::getDbo();
		$sql = "select * from #__etherpad_groups where groupMapper ='".$groupid."'";
		$db->setQuery($sql);
		$group = $db->loadObject();
		//If Group Exists
		if(!empty($group)){
			$groupID = $group->groupID;
		}else{
			//Create new Group
			$groupID = $this->createGroupIfNotExistsFor($groupid);
		}
		
		$sql = "select * from #__etherpad_docs where groupID ='".$groupID."'";
		$db->setQuery($sql);
		$list = $db->loadObjectList();
		
		$docs = new stdClass();
		$docs->groupID = $groupID;
		$docs->docs    = $list;
		
	 
		return $docs;
		
	}
	
	function CreateSession($Groupid,$Authorid){
		$pluginParams = $this->params;
		$post_url 	  = $pluginParams->get('post_url','','RAW');
		$api_key 	  = $pluginParams->get('api_key','','RAW');
		$curl = curl_init();
		$url = "$post_url/api/1/createSession?apikey={$api_key}&groupID={$Groupid}&authorID={$Authorid}&validUntil=".strtotime('+24 hours');
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_HTTPHEADER => ''
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($response);
		if($result->message =='ok'){
			return $result->data->sessionID;
		} else {
			return 'something went wrong';
		}
		
	}
	
	function createGroupIfNotExistsFor($gid){
		
			$pluginParams = $this->params;
			$post_url 	  = $pluginParams->get('post_url','','RAW');
			$api_key 	  = $pluginParams->get('api_key','','RAW');
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "$post_url/api/1/createGroupIfNotExistsFor?apikey=$api_key&groupMapper=$gid",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_HTTPHEADER => ''
			));
			$response = curl_exec($curl);
			curl_close($curl);
			
			$result = json_decode($response);
			if($result->message =='ok'){
				$db = JFactory::getDbo();
				$obj = new stdClass();
				$obj->groupMapper = $gid;
				$obj->groupID = $result->data->groupID;
				$obj->date_created = date("Y-m-d h:i:s");
				$obj->response = $response;
				$db->insertObject("#__etherpad_groups",$obj);
				return  $result->data->groupID;
			}
	}
	
	
	public function createAuthorIfNotExistsFor(){
		$db = JFactory::getDbo();
		$sql = "select * from #__etherpad_groups where groupMapper ='".$gid."'";
		$db->setQuery($sql);
		$group = $db->loadObject();
		//If Group Exists
		if(!empty($group)){
			return $groupID = $group->groupID;
		}
		
		$pluginParams = $this->params;
		$post_url = $pluginParams->get('post_url','','RAW');
		$api_key = $pluginParams->get('api_key','','RAW');
		
		
		$username = JFactory::getUser()->username;
		$userid   = JFactory::getUser()->id;
		
		if(!$userid){
			$username ="Guest";
		}
		
		$db = JFactory::getDbo();
		$sql ="select * from #__etherpad_users where authorMapper=".$userid."";
		$db->setQuery($sql);
		$user = $db->loadObject();
		
		if(!empty($user)){
			return $user->authorID;
		}
		 
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "$post_url/api/1/createAuthorIfNotExistsFor?apikey=$api_key&name=$username&authorMapper=$userid",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_HTTPHEADER => ''
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($response);
		if($result->message =='ok'){
			$db = JFactory::getDbo();
			$obj = new stdClass();
			$obj->name = $username;
			$obj->authorMapper = $userid;
			$obj->authorID = $result->data->authorID;
			$obj->date_created = date("Y-m-d h:i:s");
			$obj->response = $response;
			$db->insertObject("#__etherpad_users",$obj);
			return  $result->data->authorID;
		}
		
		
	}
	public function createPad(){
		
		JSession::checkToken() or die( 'Invalid Token' );
		$jinput 	= JFactory::getApplication()->input;
		$title    	= $jinput->get('title', '', 'RAW');
		$content  	= $jinput->get('content', '', 'RAW');
		$group_id  	= $jinput->get('group_id', '', 'RAW');
		
		$pluginParams = $this->params;
		$post_url 	  = $pluginParams->get('post_url','','RAW');
		$api_key 	  = $pluginParams->get('api_key','','RAW');
		
		$curl = curl_init();
		
		$encode_title = urlencode(str_replace(array('/','\\','"','"'),'',$title));
		$encode_content = urlencode($content);
	
		$url = "$post_url/api/1/createGroupPad?apikey=$api_key&padName=$encode_title&text=$encode_content&groupID=$group_id";
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		));
		$response = curl_exec($curl);
		curl_close($curl);
		
		$result = json_decode($response);
		
		if($result->message =='ok'){
			$db = JFactory::getDbo();
			$obj = new stdClass();
			$obj->user_id = JFactory::getUser()->id;
			$obj->padName = $title;
			$obj->text = $content;
			$obj->groupID =$group_id;
			$obj->padID = $result->data->padID;
			$obj->date_created = date("Y-m-d h:i:s");
			$obj->response = $response;
			$db->insertObject("#__etherpad_docs",$obj);
			
			JFactory::getApplication()->redirect($_SERVER['HTTP_REFERER'],"New Document Created Successfully");
		}else{
			JFactory::getApplication()->redirect($_SERVER['HTTP_REFERER'],$result->message,'warning');
		}
	}
	
	function getOutput( &$list, $layout='default.php'){
			if(!isset($list)){return '';}						
			ob_start();
			$tmplPath = $this->getTemplatePath('etherpad',$layout);
			$tmplPath = $tmplPath->file;
			include($tmplPath);
			$output = ob_get_contents();
			ob_end_clean();
			
			return $output;
		
	}
	
	function getTemplatePath($pluginName,$file){
		$mainframe		= JFactory::getApplication();
		$p = new JObject;
		$p->file = JPATH_SITE.'/plugins/system/'.$pluginName.'/'.$pluginName.'/tmpl/'.$file;
		$p->http = JURI::base()."plugins/system/{$pluginName}/{$pluginName}/tmpl/{$file}";
		return $p;
	}
	
}