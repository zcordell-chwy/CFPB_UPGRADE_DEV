<rn:meta controller_path="custom/instAgent/reports/ResultInfo2" 
         js_path="custom/instAgent/reports/ResultInfo2" 
         base_css="standard/reports/ResultInfo2" 
         presentation_css="widgetCss/ResultInfo2.css" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10"/>
         
<div id="rn_<?=$this->instanceID;?>" class="rn_ResultInfo2">
    <? /**suggested searches**/?>
    <div id="rn_<?=$this->instanceID;?>_Suggestion" class="rn_Suggestion <?=$this->data['suggestionClass'];?>">
        <?=$this->data['attrs']['label_suggestion'];?>
        <? for($i = 0; $i < count($this->data['suggestionData']); $i++): ?>
            <a href="<?=$this->data['js']['linkUrl'].$this->data['suggestionData'][$i].$this->data['appendedParameters'].sessionParm();?>" <?=tabIndex($this->data['attrs']['tabindex'], $i);?>><?=$this->data['suggestionData'][$i]?></a>&nbsp;
        <? endfor;?>
    </div>
    <? /**spelling**/?>
    <div id="rn_<?=$this->instanceID;?>_Spell" class="rn_Spell <?=$this->data['spellClass'];?>">
        <?=$this->data['attrs']['label_spell'];?>
        <?if($this->data['spellData']):?>
        <a href="<?=$this->data['js']['linkUrl'].$this->data['spellData'].$this->data['appendedParameters'].sessionParm();?>" <?=tabIndex($this->data['attrs']['tabindex'], count($this->data['suggestionData']));?>><?=$this->data['spellData'];?></a>
        <?endif;?>
    </div>
    <? /**no results**/?>
    <div id="rn_<?=$this->instanceID;?>_NoResults" class="rn_NoResults <?=$this->data['noResultsClass'];?>">
        <?=$this->data['attrs']['label_no_results'];?>
        <br/><br/>
        <?=$this->data['attrs']['label_no_results_suggestions'];?>
    </div>
    <? /**results**/?>
    <? if($this->data['attrs']['display_results']):?>
    <div id="rn_<?=$this->instanceID;?>_Results" class="rn_Results <?=$this->data['resultClass'];?>">
    <? if($this->data['searchQuery']):?>
        <? $query = '';
            foreach($this->data['searchQuery'] as $searchTerm):?>
            <? if($searchTerm['stop']):?>
                <? $query .= "<strike title='{$this->data['attrs']['label_common']}'>{$searchTerm['word']}</strike> ";?>
            <? elseif($searchTerm['notFound']):?>
                <? $query .= "<strike title='{$this->data['attrs']['label_dictionary']}'>{$searchTerm['word']}</strike> ";?>
            <? else:?>
            <? $query .= '<a href="'.$this->data['js']['linkUrl'].$searchTerm['url'].$this->data['appendedParameters'].sessionParm()."\">{$searchTerm['word']}</a> ";?>
            <? endif;?>
        <? endforeach;?>
        <? printf($this->data['attrs']['label_results_search_query'], $this->data['firstResult'], $this->data['lastResult'], $this->data['totalResults'], $query);?>
    <? else:?>
        <? printf($this->data['attrs']['label_results'], $this->data['firstResult'], $this->data['lastResult'], $this->data['totalResults']);?>
    <? endif;?>
    </div>
    <? endif;?>
</div>

