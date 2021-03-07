<?php
/**
 * @package		Either Pad for Joomla
 * @version		1.0
 * @author		JoomDev - www.JoomDev.com
 * @copyright	Copyright (C) 2021  www.JoomDev.com
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
 
defined('_JEXEC') or die;
$uniqueClass = explode(".",$list->groupID)[1];
$pluginParams = $this->params;
$pluginParams = $this->params;
$post_url 	  = $pluginParams->get('post_url','','RAW');
$api_key 	  = $pluginParams->get('api_key','','RAW');


if(!empty($list->groupID)){
	$doclist = '<ul class="docli_'.$uniqueClass.'">';
	if(!empty($list->docs)){
		echo '<br>Edit an Existing Document<br>';
		foreach($list->docs as $doc){
			$doclist .= '<li>
			<a  class="" href="'.$post_url.'/p/'.$doc->padID.'" target="_blank">'.$doc->padName.'</a>
			</li>';
		}
	}
	$doclist .= '</ul>';
	echo $doclist;
}
?>
Create a new one.<br>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#<?php echo $uniqueClass;?>">Create New Document <?php //echo $uniqueClass;?></button>
<div class="modal fade" id="<?php echo $uniqueClass;?>" tabindex="-1" role="dialog" aria-labelledby="example<?php echo $uniqueClass;?>" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="example<?php echo $uniqueClass;?>">New Document</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	   <form method="post" action="index.php">
      <div class="modal-body">
          <div class="form-group">
            <label for="title" class="col-form-label">Title:</label>
            <input type="text" class="form-control" name="title">
          </div>
          <div class="form-group">
            <label for="content" class="col-form-label">Content:</label>
            <textarea class="form-control" name="content"></textarea>
          </div>
       
      </div>
      <div class="modal-footer">
	     <input type="hidden" name="createpad" value="1">  
	     <input type="hidden" name="group_id" value="<?php echo $list->groupID;?>">  
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <?php echo JHtml::_( 'form.token' ); ?>
        <button type="submit" class="btn btn-primary">Create</button>
      </div>
	   </form>
    </div>
  </div>
</div>