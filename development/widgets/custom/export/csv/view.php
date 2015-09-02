<rn:meta controller_path="custom/export/csv" presentation_css="widgetCss/DataExport.css" js_path="custom/export/csv" />

<? if( $this->data['authorized'] ): ?>
    <div id="rn_<?= $this->instanceID ?>" class="rn_DataExport">
        <h4>Download Case Data</h4>
        <? for( $counter = 0; $counter < count( $this->data['js']['links'] ); $counter++ ): ?>
            <form id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['links'][$counter]['form_label'] ?>_Form" method="post" action="<?= $this->data['js']['links'][$counter]['link'] ?>">
                <label for="rn_<?= $this->instanceID ?>_<?= $this->data['js']['links'][$counter]['form_label'] ?>"><?= $this->data['js']['links'][$counter]['label'] ?></label>
                <select id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['links'][$counter]['form_label'] ?>" name="page">
                    <option>Page</option>
                <? for( $page = 1; $page <= $this->data['totalPages']; $page++ ): ?>
                    <option value="<?= $page ?>"><?= $page ?></option>
                <? endfor; ?>
                </select>

                <? foreach( $this->data['js']['defaultFilters'] as $name => $filter ): ?>
                    <? if( $name !== 'per_page' ): ?>
                        <?
                            $filterValue = '';
                            if( is_array( $filter->filters->data ) && isset( $filter->filters->data[0] ) )
                            {
                                $filterValue = implode( ',', $filter->filters->data[0] );
                            }
                            else if( strlen( $filter->filters->data ) > 0 )
                            {
                                $filterValue = $filter->filters->data;
                            }
                        ?>
                        <input
                            type="hidden"
                            name="<?= $name ?>"
                            id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['links'][$counter]['form_label'] ?>_<?= $filter->filters->rnSearchType ?>_<?= $name ?>"
                            value="<?= $filterValue ?>"
                        />
                    <? endif; ?>
                <? endforeach; ?>

                <input type="hidden" name="reportID" id="rn_<?= $this->instanceID ?>_<?= $this->data['js']['links'][$counter]['form_label'] ?>_report" value="<?= $this->data['attrs']['report_id'] ?>" />
                <input type="submit" name="submit" value="Download" />
            </form>
            <? if( $counter != count( $this->data['js']['links'] ) - 1 ): ?>
                <? /* <br />*/ ?>
            <? endif; ?>
        <? endfor; ?>
    </div>
<? endif; ?>
