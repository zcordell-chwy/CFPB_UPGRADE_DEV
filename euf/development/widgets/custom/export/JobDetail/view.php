<rn:meta controller_path="custom/export/JobDetail"
    js_path="custom/export/JobDetail"
    presentation_css="widgetCss/JobDetail.css" />

<!-- Inline display style -->
<div id="rn_<?=$this->instanceID;?>" class="rn_JobDetail">

    <div class="rn_JobDetail_Data">
        <span class="rn_DataLabel"><?= getLabel( 'EXP_JOB_CONTACT_LBL' ) ?></span><?= $this->data['js']['job']->Contact->Name->First; ?> <?= $this->data['js']['job']->Contact->Name->Last; ?>
    </div>
    <div class="rn_JobDetail_Data">
        <span class="rn_DataLabel"><?= getLabel( 'EXP_JOB_CREATED_LBL' ) ?></span><?= date( $this->data['js']['dateFormat'], $this->data['js']['job']->CreatedTime ); ?>
    </div>
    <div class="rn_JobDetail_Data">
        <span class="rn_DataLabel"><?= getLabel( 'EXP_JOB_STATUS_LBL' ) ?></span><?= $this->data['js']['cached_job_status']; ?>
    </div>
    <? // File attachments. ?>
    <?
    /**
     * 6/19/2013 T. Woodham: This section should be enabled again (in favor of the form immediately below this segment) once attaching files to the export has been stabilized.
     *
    <h4><?= getLabel( 'EXP_JOB_ATTACHMENTS_LBL' ); ?></h4>
    <ul class="rn_FileAttachmentUpload2">

        <? foreach($this->data['js']['attachments'] as $fattach):
            $fileUrl = $fattach['url'];
            $fileName = $fattach['filename'];
            $fileSize = $fattach['size'];
            $fileIcon = $fattach['icon'];
            //$file_contents = file_get_contents($url);
        ?>
            <li>
                <a href="<?=$fileUrl;?>" target="_blank"><?=$fileIcon;?><?=$fileName;?></a>
                <span class="rn_FileSize">(<?=$fileSize;?>)</span>
            </li>
        <? endforeach;?>
    </ul>
    */
    ?>

    <? if( in_array( $this->data['js']['cached_job_status'], array( 'Finished', 'Archived' ) ) && $this->data['js']['zip_file_exists'] ): ?>
        <form id="rn_<?= $this->instanceID; ?>_File" method="post" action="/cc/dataExport/attachment">
            <input type="hidden" name="file" value="<?= $this->data['js']['zip_file']; ?>" />
            <input type="submit" name="submit" id="rn_<?= $this->instanceID; ?>_File" value="Download" />
        </form>
        <br />
        <br />
    <? endif; ?>

    <rn:widget path="reports/Grid2" label_caption="#rn:php:getLabel('EXPORT_JOBS_COMPONENT_HEADING')#" report_id="#rn:php:getSetting('EXPORT_JOBS_COMPONENT_REPORT_ID')#" />
    <rn:widget path="reports/Paginator" report_id="#rn:php:getSetting('EXPORT_JOBS_COMPONENT_REPORT_ID')#" maximum_page_links="27" />

    <? if( !in_array( $this->data['js']['cached_job_status'], array( 'Finished', 'Archived' ) ) ): ?>
    <form id="rn_<?= $this->instanceID; ?>_Form" method="post" onsubmit="return false;">
        <h4>Think there might be a problem?</h4>
        <button id="rn_<?= $this->instanceID; ?>_Reprocess">Reprocess job</button>
    </form>
    <? endif; ?>
</div>
