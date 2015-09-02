<rn:meta controller_path="custom/instAgent/output/IncidentThreadDisplayIA"
presentation_css="widgetCss/IncidentThreadDisplay.css"
base_css="standard/output/IncidentThreadDisplay"
compatibility_set="November '09+"
required_js_module="november_09,mobile_may_10,none"/>
	<div id="rn_<?=$this -> instanceID;?>" class="rn_IncidentThreadDisplay">
		<? if ($this->data['attrs']['label']):
		?>
		<span class="rn_DataLabel"><?=$this -> data['attrs']['label'];?></span>
		<? endif;?>
		<? if($this->data['value']):
		?>
		<? foreach($this->data['value'] as $thread):
            // customer wants to hide all private notes
            if ($thread['entry_type'] == ENTRY_NOTE) 
                continue;
		?>
		<? $subclass = '';
			switch ($thread['entry_type']) {
				case ENTRY_CUSTOMER :

				case ENTRY_CUST_PROXY :
					$subclass = 'rn_Customer';
					break;
				case ENTRY_NOTE :
					$subclass = 'ps_privateNote';
					break;
			}
		?>
		<div class="rn_ThreadHeader <?=$subclass?>">
			<span class="rn_ThreadAuthor"> <? /* echo $thread['type']; */ echo "&nbsp;";?>
				<?
				if($thread['name']) {
					//echo $thread['name'];
				}
				?>
				<? //if($thread['channel_label'])
					// printf(getMessage(VIA_PCT_S_LBL), $thread['channel_label']);
				?>
				<?
				switch ($thread['entry_type']) {
					case ENTRY_NOTE :
						printf('Private Note');
						break;
				}
				?>
				</span>
			<span class="rn_ThreadTime"> <?=$thread['time'];?></span>
		</div>
		<div class="rn_ThreadContent">
			<?=$thread['content'];?>
		</div>
		<? endforeach;?>
		<? endif;?>
	</div>
