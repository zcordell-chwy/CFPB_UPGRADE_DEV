<rn:meta controller_path="custom/login/LoginForm2"
    js_path="custom/login/LoginForm2"
    base_css="standard/login/LoginForm2"
    presentation_css="widgetCss/LoginForm2.css"
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>

<div id="rn_<?=$this->instanceID;?>" class="rn_LoginForm2">
    <div id="rn_<?=$this->instanceID;?>_Content">
        <div id="rn_<?=$this->instanceID;?>_ErrorMessage"></div>	
        <div id="rn_<?=$this->instanceID;?>_Messages"></div>
	<form method="post" action="" id="rn_<?=$this->instanceID;?>_Form" onsubmit="return false;">
            <div id="rn_<?=$this->instanceID;?>_FormContent">
                <label for="rn_<?=$this->instanceID;?>_Username"><?=$this->data['attrs']['label_username'];?></label>
                <input id="rn_<?=$this->instanceID;?>_Username" type="text" maxlength="80" value="<?=$this->data['username'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>/>
            <? if(!$this->data['attrs']['disable_password']):?>
                <label for="rn_<?=$this->instanceID;?>_Password"><?=$this->data['attrs']['label_password'];?></label>
                <input id="rn_<?=$this->instanceID;?>_Password" type="password" maxlength="20" value='' <?=tabIndex($this->data['attrs']['tabindex'], 2);?>/>
            <? elseif($this->data['isIE']):?>
                <label for="rn_<?=$this->instanceID;?>_HiddenInput" class="rn_Hidden">&nbsp;</label>
                <input id="rn_<?=$this->instanceID;?>_HiddenInput" type="text" class="rn_Hidden" disabled="disabled" />
            <? endif;?>
                <br/>
            </div>
            <div id="rn_<?=$this->instanceID;?>_acceptAndContinueWrapper" class="rn_MessageBox rn_Hidden" style="clear:both;padding:10px;margin:20px 0;">
	    	<?=getSetting('CONGRESSIONAL_ACCEPT_AND_CONTINUE')?>
	    	<div style="clear:both;margin-top:10px"> 
                    <input id="rn_<?=$this->instanceID;?>_acceptAndContinue" type="checkbox" style="height:13px" value="1" <?=tabIndex($this->data['attrs']['tabindex'], 4);?>/>
		    <label for="rn_<?=$this->instanceID;?>_acceptAndContinue" style="width:100px;display:inline;height:20px;"><?=getSetting('CONGRESSIONAL_ACCEPT_AND_CONTINUE_CHECKBOX')?></label>
                    <div style="margin-top:10px;"><button id="rn_<?=$this->instanceID;?>_DisclaimerSubmit">Continue</button></div>
		</div>
	    </div>
            <div id="rn_<?=$this->instanceID;?>_SubmitWrapper">
                <input id="rn_<?=$this->instanceID;?>_Submit" type="submit" onclick="return false" value="<?=$this->data['attrs']['label_login_button'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 3);?>/>
            </div>
            <br/>
        </form>
    </div>
</div>
