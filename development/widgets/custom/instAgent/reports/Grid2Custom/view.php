<rn:meta controller_path="custom/instAgent/reports/Grid2Custom" js_path="custom/instAgent/reports/Grid2Custom" presentation_css="widgetCss/Grid2.css" base_css="standard/reports/Grid2" compatibility_set="November '09+"/>

<? if($this->data['attrs']['headers']):?>
<? $this->addJavaScriptInclude(getYUICodePath('datasource/datasource-min.js'));?>
<? $this->addJavaScriptInclude(getYUICodePath('datatable/datatable-min.js'));?>
<? endif;?>

<div id="rn_Navigation">
    <div id="rn_NavigationBar" role="navigation">
        <ul>
        <? if( $this->data['userType'] == 'company' ): ?>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Active" link="/app/instAgent/list_active" pages="instAgent/list_active"/></li>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Under review" link="/app/instAgent/list_review" pages="instAgent/list_review"/></li>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Archive" link="/app/instAgent/list_archive" pages="instAgent/list_archive"/></li>
        <? endif; ?>
        <? if( $this->data['userType'] == 'state' ): ?>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Active" link="/app/government/active/list" pages="government/active/list"/></li>
        <? endif; ?>
        <? if ( $this->data['userType'] == 'state' || $this->data['userType'] == 'federal' ): ?>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Closed" link="/app/government/closed/list" pages="government/closed/list"/></li>
        <? endif; ?>
        <? if( $this->data['userType'] == 'congressional' ): ?>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Active" link="/app/government/referral/active/list" pages="government/referral/active/list"/></li>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Closed" link="/app/government/referral/closed/list" pages="government/referral/closed/list"/></li>
        <? endif; ?>
        </ul>
    </div>
</div>

<div id="rn_<?=$this->instanceID;?>" class="rn_Grid2<?=$this->data['topLevelClass'];?>">
    <div id="rn_<?=$this->instanceID;?>_Alert" role="alert" class="rn_ScreenReaderOnly"></div>
    <div id="rn_<?=$this->instanceID;?>_Loading"></div>
    <div id="rn_<?=$this->instanceID;?>_Content" class="yui-skin-sam">
        <table id="rn_<?=$this->instanceID;?>_Grid" class="yui-dt" summary="<?=$this->data['attrs']['label_summary']?>">
        <?/*<caption><?=$this->data['attrs']['label_caption']?></caption>*/?>
        <? if($this->data['attrs']['headers']):?>
            <thead>
                <tr class="yui-dt-first yui-dt-last">
                <? if($this->data['tableData']['row_num']):?>
                    <th scope="col" class="yui-dt-sortable"><?=$this->data['attrs']['label_row_number']?></th>
                <? endif;?>
                <? foreach($this->data['tableData']['headers'] as $header):?>
                    <? if($header['width'] !== null):?>
                        <th scope="col" width="<?=$header['width'];?>%" class="yui-dt-sortable"><?=$header['heading'];?></th>
                    <? else:?>
                        <th scope="col" class="yui-dt-sortable"><?=$header['heading'];?></th>
                    <? endif;?>
                <? endforeach;?>
                </tr>
            </thead>
        <? endif;?>
        <? if( count($this->data['tableData']['data']) > 0): ?>
            <tbody class="yui-dt-body">
            <? for($i = 0; $i < count($this->data['tableData']['data']); $i++): ?>
              <?/* foreach($this->data['tableData']['exceptions'] as $exceptionCol):
                $exceptionClass = ($this->data['tableData']['data'][$i][$exceptionCol]) ? 'exceptionClass' : '';*/?>
                <tr class="<?=($i%2 === 0)?'yui-dt-even':'yui-dt-odd'?>">
                <? if($this->data['tableData']['row_num']):?>
                    <td><?=$this->data['tableData']['start_num'] + $i;?></td>
                <? endif;?>
                <? for($j=0; $j<count($this->data['tableData']['headers']); $j++):?>
                    <td><?=($this->data['tableData']['data'][$i][$j]) ? $this->data['tableData']['data'][$i][$j] : '&nbsp;'?></td>
                <? endfor;?>
                </tr>
            <? endfor;?>
            </tbody>
        <? else:?>
            <tbody id="rn_<?=$this->instanceID;?>_Tbody"><tr><td></td></tr></tbody>
        <? endif;?>
        </table>
    </div>
</div>
