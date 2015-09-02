<rn:meta controller_path="custom/export/Navigation" />

<? if( $this->data['js']['authorized'] === true ): ?>
    <li><rn:widget path="navigation/NavigationTab2"
        label_tab="#rn:php:getLabel( 'EXPORT_JOBS_NAV_LBL' )#" link="#rn:php:getSetting( 'CASE_DATA_EXPORT_JOB_STATUS_URL' )#"
        pages="account/exports/list, account/exports/detail"/></li>
<? endif; ?>