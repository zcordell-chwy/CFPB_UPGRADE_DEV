<rn:meta controller_path="custom/instAgent/reports/invMessagesDisplay" 
    js_path="custom/instAgent/reports/invMessagesDisplay" 
    presentation_css="widgetCss/IncidentThreadDisplay.css"
    base_css="standard/output/IncidentThreadDisplay"
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10,none"/>

<? if ($this->data['attrs']['show_last_from_investigation']):?>
    <pre><?=$this->data['show_last_from_investigation']->msgText;?></pre>
<? else:?>

  <? if($this->data['results']): ?>

    <br/><br/>
    <h2 class="rn_HeadingBar">Review History </h2>
    <div id="rn_QuestionThread">

    <div id="rn_<?=$this -> instanceID;?>" class="rn_IncidentThreadDisplay">
        <? if ($this->data['attrs']['label']): ?>
        <span class="rn_DataLabel"><?=$this -> data['attrs']['label'];?></span>
        <? endif;?>
        
        <? foreach($this->data['results'] as $thread): ?>
        <? if($thread->c_id->ID > 0)
            $subclass = 'rn_Customer';
           else
            $subclass = 'ps_privateNote';
        ?>
        <div class="rn_ThreadHeader <?=$subclass?>">
            <span class="rn_ThreadAuthor"> &nbsp;
            </span>
            <span class="rn_ThreadTime"> <?=date('m/d/Y H:i', $thread->created);?></span>
        </div>
        <div class="rn_ThreadContent">
            <pre><?=$thread->msgText;?></pre>
            <br/><br/><ul class="rn_FileAttachmentUpload2">

            <? foreach($thread->attachments as $fattach):
                $fileUrl = $fattach['url'];
                $fileName = $fattach['filename'];
                $fileSize = $fattach['size'];
                $fileIcon = $fattach['icon'];
                //$file_contents = file_get_contents($url);
            ?>
                <li>
                    <a href="/cc/fattach/investigation/<?=$this->data['investigation_id']?>/<?=$fattach['id']?>" target="_blank"><?=$fileIcon;?><?=$fileName;?></a>
                    <span class="rn_FileSize">(<?=$fileSize;?>)</span>
                </li>
            <? endforeach;?>

            </ul>
        </div>
        <? endforeach;?>
    </div>

    </div>
 
 <? endif;?>

<? endif;?>
