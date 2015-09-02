<rn:meta controller_path="custom/export/ReportSelector" presentation_css="widgetCss/ReportSelector.css" js_path="custom/export/ReportSelector" />

<? if( $this->data['js']['authorized'] ): ?>
<form id="rn_<?= $this->instanceID ?>_DataExportJobManager" method="post" onsubmit="return false">
    <div id="rn_<?= $this->instanceID ?>">
        <div class="rn_ReportSelector">
            <h4>Download Case Data</h4>

                <fieldset>
                    <legend>Data Sets</legend>
                <?
                    $previousFeatured = null;
                    foreach( $this->data['js']['links'] as $id => $export ):
                        if( isset( $previousFeatured ) && $previousFeatured != $export['Featured'] )
                            $supplementalClass = 'rn_ReportSeparator';
                        else
                            $supplementalClass = '';
                ?>

                        <div class="rn_ReportSelector_Report <?= $supplementalClass ?>">
                            <input type="checkbox" name="ReportSelector_<?= $id ?>" id="<?= $this->instanceID ?>_<?= $id ?>" value="<?= $id ?>" />
                            <label for="<?= $this->instanceID ?>_<?= $id ?>"><?= $export['LabelText'] ?></label>
                        </div>
                        <? $previousFeatured = $export['Featured']; ?>
                    <? endforeach; ?>
                </fieldset>
        </div>
        <div class="rn_ReportSelectorButtons">
            <input type="submit" name="submit" id="<?= $this->instanceID ?>_Incidents" value="Download Case Data" />
        </div>
        <div style="clear:both"></div>
    </div>
</form>
<? endif; ?>