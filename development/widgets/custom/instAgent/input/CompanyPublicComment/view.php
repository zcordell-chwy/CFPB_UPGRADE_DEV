<rn:meta controller_path="custom/instAgent/input/CompanyPublicComment" js_path="custom/instAgent/input/CompanyPublicComment" base_css="custom/instAgent/input/CompanyPublicComment" />

<? if ($this->data['attrs']['display_company_comment_form']) { ?>
<div class="highlight_block">
<? } ?>

<?// 2014.12.23 - Eric Gottesman: Adding field for Consumer Consent ?>
<? if ($this->data['attrs']['display_consent_status'] != '') { ?>
    <div id="div_consent" class="rn_FieldDisplay">
        <h3 class="rn_HeadingBar"><?= getLabel('CONSENT_STATUS_HEADER'); ?></h3>
        <span class="rn_DataLabel"><?= getLabel('CONSENT_STATUS'); ?></span> <div class="rn_DataValue"><?= $this->data['attrs']['display_consent_status'] ?></div>
    </div>
<? } ?>
    
    
<?// 2014.12.23 - Eric Gottesman: Adding fields related to Company Public Comment ?>
<? if ($this->data['attrs']['display_company_comment_form']) { ?>
    <? $return_page = "/app/instAgent/" . $this->data['attrs']['return_to_page'] . "/incstatus/" . getUrlParm('incstatus'); ?>
    <h3 class="rn_HeadingBar"><?= getLabel('COMPANY_COMMENT_HEADER'); ?></h3>
     <span id="company_comment_label"><?= getLabel('COMPANY_COMMENT_INSTRUCTIONS'); ?></span>
     <div id="div_company_comment_form" class="">
        <? for ($i = 0; $i < $this->data['js']['number_of_choices']; $i++):
            $checked = ($i === $this->data['checkedIndex']) ? 'checked="checked"' : '';
            $id = "rn_{$this->instanceID}_CompanyCommentChoice_$i"; ?>
                
            <input type="radio" name="rn_<?=$this->instanceID; ?>_Radio" class="commentRadios" id="<?=$id; ?>" <?=$checked; ?> value="<?=$i; ?>"/><label for="<?=$id; ?>"><?=$this->data['js']['radioLabelDisplayText'][$i]; ?></label>
            <br>
        <? endfor; ?>
        <rn:widget path="custom/instAgent/input/FormSubmit"
            on_success_url="#rn:php:$return_page#" label_button="Submit Response"
            label_confirm_dialog="" error_location="rn_ErrorLocation" />
        <button id="comment_cancel_button" onclick="window.location.assign('<?=$return_page?>');return false;" class="abutton ps_noprint">Skip</button>
     </div>

     <div id="div_company_comment_form_textbox" class="rn_Hidden">
        <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$company_public_comment" label_input="" max_chars="3900" />
     </div>
     <div id="div_company_comment_category_menu" class="rn_Hidden">
        <rn:widget path="input/SelectionInput" name="incidents.c$company_comment_category"  />
     </div>

<? } else if ($this->data['attrs']['display_company_comment']) { ?>
    <div id="div_company_comment">
        <h3 class="rn_HeadingBar"><?= getLabel('COMPANY_COMMENT_HEADER'); ?></h3>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$company_public_comment"
          label="#rn:php:getLabel('COMPANY_COMMENT')#:"/>
    </div>
<? } ?>

<? if ($this->data['attrs']['display_company_comment_form']) { ?>
</div>
<?} ?>