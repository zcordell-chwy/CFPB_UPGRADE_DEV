<rn:meta controller_path="custom/instAgent/reports/agentIncidents" 
    js_path="custom/instAgent/reports/agentIncidents" 
    presentation_css="widgetCss/Grid2.css" 
    base_css="standard/reports/Grid2" 
    compatibility_set="November '09+"/>

<? if($this->data['attrs']['headers']):?>
<? $this->addJavaScriptInclude(getYUICodePath('datasource/datasource-min.js'));?>
<? $this->addJavaScriptInclude(getYUICodePath('datatable/datatable-min.js'));?>
<? endif;?>

<div id="rn_Navigation">
    <div id="rn_NavigationBar" role="navigation">
        <ul>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Active" link="/app/instAgent/list" pages="instAgent/list, instAgent/list/incstatus"/></li>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Under review" link="/app/instAgent/list/incstatus/cfpb" pages="instAgent/list/incstatus/cfpb"/></li>
            <li><rn:widget path="custom/navigation/NavigationTab2" label_tab="Archive" link="/app/instAgent/list/incstatus/old" pages="instAgent/list/incstatus/old"/></li>
        </ul>
    </div>
</div>

<div id="rn_<?=$this->instanceID;?>" class="rn_Grid2<?=$this->data['topLevelClass'];?>">
    <div id="rn_<?=$this->instanceID;?>_Alert" role="alert" class="rn_ScreenReaderOnly"></div>
    <div id="rn_<?=$this->instanceID;?>_Loading"></div>
    <div id="rn_<?=$this->instanceID;?>_Content" class="yui-skin-sam">
        <table id="rn_<?=$this->instanceID;?>_Grid" class="yui-dt" summary="<?=$this->data['attrs']['label_summary']?>">
        <?/*<caption><?echo isset($this->data['label_caption_override']) ? $this->data['label_caption_override'] : $this->data['attrs']['label_caption']?></caption>*/?>
        <?
if($this->data['attrs']['headers']):?>
            <thead>
                <tr class="yui-dt-first yui-dt-last">
                <? if($this->data['tableData']['row_num']):?>
                    <th scope="col" class="yui-dt-sortable"><?=$this->data['attrs']['label_row_number']?></th>
                <? endif;?>
                <?
foreach($this->data['tableData']['headers'] as $header):?>
                    <? if($header['width'] !== null):?>
                        <th scope="col" width="<?=$header['width'];?>%" class="yui-dt-sortable"><?=$header['heading'];?></th>
                    <?
else:?>
                        <th scope="col" class="yui-dt-sortable"><?=$header['heading'];?></th>
                    <?
endif;?>
                <?
endforeach;?>
				<th scope="col" class="" style="display:none">isUnread</th>
                </tr>
            </thead>
        <?
endif;?>
        <? if( count($this->data['tableData']['data']) > 0): ?>
            <tbody class="yui-dt-body">
            <? for($i = 0;
$i < count($this->data['tableData']['data']);
$i++): ?>
                <tr class="<?=($i%2 === 0)?'yui-dt-even':'yui-dt-odd'?>">
                <? if($this->data['tableData']['row_num']):?>
                    <td><?=$this->data['tableData']['start_num'] + $i;?></td>
                <? endif;?>
                <? for($j=0; $j<count($this->data['tableData']['headers']); $j++):?>
                    <td>
                    <? switch($this->data['tableData']['headers'][$j]['data_type']) {
                        case EUF_DT_HIERMENU:
                            echo $this->data['tableData']['data'][$i][$j][1]['label'] 
                                ? $this->data['tableData']['data'][$i][$j][1]['label'] 
                                : $this->data['tableData']['data'][$i][$j][0]['label']; 
                            break;
                        default:
                            echo $this->data['tableData']['data'][$i][$j] ? $this->data['tableData']['data'][$i][$j] : '&nbsp;';
                            break;
                    } ?>
                    </td>

                <? endfor;?>
                <td style="display:none"><?=$this->data['tableData']['data'][$i]['isUnread']?></td>
                </tr>
            <? endfor;?>
            </tbody>
        <?
else:?>
            <tbody id="rn_<?=$this->instanceID;?>_Tbody"><tr><td></td></tr></tbody>
        <?
endif;?>
        </table>
    </div>
</div>
<?/*
<?if($this->data['incstatus'] != 'old'): ?>
		<a href="/app/instAgent/list/incstatus/old">Archived Cases</a>
<? else: ?>
		<a href="/app/instAgent/list/">Active Cases</a>
<?endif;?>
*/?>
<div id="rn_time_est_notice">All times are ET</div>
