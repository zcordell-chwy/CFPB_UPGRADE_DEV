<?php
use RightNow\Connect\v1 as RNCPHP;

class Incident_model extends Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("standard/Customfield_model");
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI();
    }

     /**
     * Creates an empty instance of the Incident middle layer object
     * @param $formatter Formatter
     * @return Incident Empty incident object
     */
    function getBlank($formatter = null)
    {
        $blankIncident = checkCache('incidentblank');
        if($blankIncident !== null)
            return $blankIncident;

        $customFields = getCustomFieldList(TBL_INCIDENTS, VIS_CF_ALL);

        $incident = new Incident();
        $incident->custom_fields = $this->Customfield_model->getBlankCustomFieldArray($customFields);
        $incident->sla->menu_items = $this->getSlaInstances();
        $incident->format($formatter);

        setCache('incidentblank', $incident);
        return $incident;
    }

    /**
    * Returns a Incident() object from the database based on the incident_id
    *
    * @param $incidentID int The ID for the incident
    * @param $formatter Formatter
    * @return Incident() The Incident object with the specified incident_id
    */
    function get($incidentID, $formatter = null)
    {
        if(!$incidentID  || !isLoggedIn())
            return null;

        $cacheHandlePrefix = "incident";
        $incident = RnowBase::checkFormattedRecordCache($cacheHandlePrefix, $incidentID, $formatter);
        if($incident != null)
        {
            if($incident->i_id->value == $incidentID)
                return $incident;
        }

        $interfaceID = intf_id();
        $languageID = lang_id(LANG_DIR);
logMessage(sprintf("select i.subject, i.created, i.updated, i.closed, i.ref_no,
                                   s.label, i.c_id, cu.org_id, cu.email, cu.first_name, cu.last_name, slal.label, i.status_id $cols
                                   from incidents i
                                   left outer join labels s on (i.status_id = s.label_id)
                                          and (s.tbl = %d) and (s.lang_id = %d)
                                   left outer join contacts cu on (i.c_id = cu.c_id)
                                   left outer join sla_instances slai on (slai.slai_id = i.slai_id)
                                   left outer join labels slal on (slal.label_id = slai.sla_id)
                                          and (slal.tbl = %d)
                                   $from
                                   where (i.i_id = '$incidentID')",
                                   TBL_STATUSES, $languageID, TBL_SLAS));
        $customFields = getCustomFieldList(TBL_INCIDENTS, VIS_CF_ALL);
        $cols = getCustomFieldQueryString($customFields, 'i');
        //we needed the status_id to get prev pairdata right.
        $si = sql_prepare(sprintf("select i.subject, i.created, i.updated, i.closed, i.ref_no,
                                   s.label, i.c_id, cu.org_id, cu.email, cu.first_name, cu.last_name, slal.label, i.status_id $cols
                                   from incidents i
                                   left outer join labels s on (i.status_id = s.label_id)
                                          and (s.tbl = %d) and (s.lang_id = %d)
                                   left outer join contacts cu on (i.c_id = cu.c_id)
                                   left outer join sla_instances slai on (slai.slai_id = i.slai_id)
                                   left outer join labels slal on (slal.label_id = slai.sla_id)
                                          and (slal.tbl = %d)
                                   $from
                                   where (i.i_id = '$incidentID')",
                                   TBL_STATUSES, $languageID, TBL_SLAS));

        $i = 1;
        sql_bind_col($si, $i++, BIND_NTS, 241);
        sql_bind_col($si, $i++, BIND_DTTM, 0);
        sql_bind_col($si, $i++, BIND_DTTM, 0);
        sql_bind_col($si, $i++, BIND_DTTM, 0);
        sql_bind_col($si, $i++, BIND_NTS, 14);
        sql_bind_col($si, $i++, BIND_NTS, 21);
        sql_bind_col($si, $i++, BIND_INT, 0);
        sql_bind_col($si, $i++, BIND_INT, 0);
        sql_bind_col($si, $i++, BIND_NTS, 81);
        sql_bind_col($si, $i++, BIND_NTS, 81);
        sql_bind_col($si, $i++, BIND_NTS, 81);
        sql_bind_col($si, $i++, BIND_NTS, 81);
        sql_bind_col($si, $i++, BIND_INT, 0);

        $customFieldStartIndex = $i-1;

        bind_cf($customFields, count($customFields), $customFieldStartIndex, $si);
        $row = sql_fetch($si);
        sql_free($si);

        $profile = $this->session->getProfile();
        //if no incident or no profile, return null
        if(!$row || !$profile)
            return null;

        list($subject,
             $created,
             $updated,
             $closed,
             $referenceNumber,
             $status,
             $contactID,
             $orgID,
             $incidentContactEmail,
             $incidentContactFirstName,
             $incidentContactLastName,
             $sla,
             $statusID) = $row;

        if(!$this->isContactAllowedToReadIncident($incidentID, $contactID, $orgID))
        {
            header("Location: /app/error/error_id/4" . sessionParm());
            exit;
        }

        $incident = new Incident();
        $incident->i_id->value = $incidentID;
        $incident->c_id->value = $contactID;
        $incident->contact_email->value = $incidentContactEmail;
        $incident->contact_first_name->value = $incidentContactFirstName;
        $incident->contact_last_name->value = $incidentContactLastName;
        if(getConfig(intl_nameorder, 'COMMON'))
        {
            $incident->contact_full_name->value = $incidentContactLastName . ' ' . $incidentContactFirstName;
            $contact->contact_short_name->value = $incidentContactLastName;
        }
        else
        {
            $contact->contact_full_name->value = $incidentContactFirstName . ' ' . $incidentContactLastName;
            $contact->contact_short_name->value = $incidentContactFirstName;
        }
        $incident->org_id->value = $orgID;
        $incident->ref_no->value = $referenceNumber;
        $incident->subject->value = $subject;
        $incident->cat->value = $this->hierDisplayGet($incidentID, HM_CATEGORIES);
        $incident->prod->value = $this->hierDisplayGet($incidentID, HM_PRODUCTS);
        $incident->fattach->value = $this->getFileAttachments($incidentID);
        $incident->status->value = $status;
        $incident->status_id->value = $statusID;
        $incident->created->value = $created;
        $incident->updated->value = $updated;
        $incident->closed->value = $closed;
        //We only allow one option in the SLA field
        $incident->sla->menu_items = array($sla);
        $incident->sla->value = 0;

        //call the Customfield_model to retrieve the custom fields array
        $incident->custom_fields = $this->Customfield_model->getCustomFieldArray($customFields, $customFieldStartIndex, $row);
        //add japanese "sama" suffix; when lang != ja_JP this field is blank
        if (LANG_DIR == 'ja_JP')
            $incident->jp_suffix->value = getMessage(NAME_SUFFIX_LBL);

        $entryLabels = array(
            ENTRY_STAFF => RESPONSE_LBL,
            ENTRY_CUSTOMER => CUSTOMER_LBL,
            ENTRY_CUST_PROXY => CUSTOMER_LBL,
            ENTRY_RNL => CHAT_TRANSCRIPT_LBL,
            ENTRY_RULE_RESP => AUTO_RESP_LBL,
        );
        $visibleThreadTypes = implode(",", array_keys($entryLabels));
        $query = sql_prepare(sprintf("select t.note, t.entered, a.display_name, t.entry_type,
                            c.first_name, c.last_name, l.label from threads t
                            left outer join accounts a on (a.acct_id = t.acct_id)
                            left outer join contacts c on (c.c_id = t.c_id)
                            left outer join labels l on (l.label_id = t.chan_id and l.lang_id = %d)
                            where (t.i_id = '$incidentID') and (t.entry_type in ($visibleThreadTypes)) and (l.tbl = %d or t.chan_id is null)
                            order by t.entered desc, t.seq desc", $languageID, TBL_CHANNELS));
        
        $i=1;
        sql_bind_col($query, $i++, BIND_MEMO, 0);
        sql_bind_col($query, $i++, BIND_DTTM, 0);
        sql_bind_col($query, $i++, BIND_NTS, 81);
        sql_bind_col($query, $i++, BIND_INT, 0);
        sql_bind_col($query, $i++, BIND_NTS, 81);
        sql_bind_col($query, $i++, BIND_NTS, 81);
        sql_bind_col($query, $i++, BIND_NTS, 81);

        $thread = array();
        for($i=0; $row = sql_fetch($query); $i++)
        {
            $thread[$i]['content'] = $row[0];
            $thread[$i]['time'] =  date_str(DATEFMT_DTTM, $row[1]);
            $thread[$i]['type'] = getMessage($entryLabels[$row[3]]);
            $thread[$i]['entry_type'] = $row[3];
            $thread[$i]['channel_label'] = $row[6];

            switch($row[3])
            {
                case ENTRY_CUST_PROXY: 
                    if($row[2])
                        $thread[$i]['name'] = sprintf('%s %s', getMessage(CUSTOMER_ENTERED_BY_LBL), $row[2]);
                    break;
                case ENTRY_STAFF:
                case ENTRY_RNL:
                    $thread[$i]['name'] = $row[2];
                    break;
                case ENTRY_CUSTOMER:
                    if(getConfig(intl_nameorder, 'COMMON'))
                       $thread[$i]['name'] = rtrim(sprintf('%s %s %s', $row[5], $row[4], $incident->jp_suffix->value));
                    else
                       $thread[$i]['name'] = rtrim(sprintf('%s %s %s', $row[4], $row[5], $incident->jp_suffix->value));
                    break;
                case ENTRY_RULE_RESP: 
                    unset($name);
                    break;
            }
        }
        sql_free($query);
        $incident->thread->value = $thread;
        return RnowBase::setFormattedRecordCache($incident, $incidentID, $cacheHandlePrefix, $formatter);
    }

    /**
    * Gets all hier menu items related to an incident and orders them
    *
    * @param $incidentID int The ID for the incident
    * @param $hierMenuID int The hier menu type to get
    * @return array The results from the query in order
    */
    static function hierDisplayGet($incidentID, $hierMenuID)
    {
        $cacheKey = 'hier_menu_arr_incident' . $incidentID . '-' . $hierMenuID;
        $hierMenuArray = checkCache($cacheKey);
        if($hierMenuArray != null)
            return $hierMenuArray;

        if($hierMenuID == HM_PRODUCTS)
            $type = "prod";
        else if($hierMenuID == HM_CATEGORIES)
            $type = "cat";

        $langID = lang_id(LANG_DIR);
        $interfaceID = intf_id();
        $si = sql_prepare(sprintf("select %s, %s, %s, %s, %s, %s,
          hm.lvl1_id %s_lvl1_id, hm.lvl2_id %s_lvl2_id, hm.lvl3_id %s_lvl3_id, hm.lvl4_id %s_lvl4_id, hm.lvl5_id %s_lvl5_id, hm.lvl6_id %s_lvl6_id
          from incidents i
          left outer join hier_menus hm on (i.%s_id = hm.id)
          left outer join labels p1 on (hm.lvl1_id = p1.label_id)
                 and (p1.tbl = %d) and (p1.lang_id = $langID) and (p1.fld = 1)
          left outer join visibility vp1 on (vp1.tbl = %d)
                 and (vp1.interface_id = $interfaceID) and (vp1.id = hm.lvl1_id)
          left outer join labels p2 on (hm.lvl2_id = p2.label_id)
                 and (p2.tbl = %d) and (p2.lang_id = $langID) and (p2.fld = 1)
          left outer join visibility vp2 on (vp2.tbl = %d)
                 and (vp2.interface_id = $interfaceID) and (vp2.id = hm.lvl2_id)
          left outer join labels p3 on (hm.lvl3_id = p3.label_id)
                 and (p3.tbl = %d) and (p3.lang_id = $langID) and (p3.fld = 1)
          left outer join visibility vp3 on (vp3.tbl = %d)
                 and (vp3.interface_id = $interfaceID) and (vp3.id = hm.lvl3_id)
          left outer join labels p4 on (hm.lvl4_id = p4.label_id)
                 and (p4.tbl = %d) and (p4.lang_id = $langID) and (p4.fld = 1)
          left outer join visibility vp4 on (vp4.tbl = %d)
                 and (vp4.interface_id = $interfaceID) and (vp4.id = hm.lvl4_id)
          left outer join labels p5 on (hm.lvl5_id = p5.label_id)
                 and (p5.tbl = %d) and (p5.lang_id = $langID) and (p5.fld = 1)
          left outer join visibility vp5 on (vp5.tbl = %d)
                 and (vp5.interface_id = $interfaceID) and (vp5.id = hm.lvl5_id)
          left outer join labels p6 on (hm.lvl6_id = p6.label_id)
                 and (p6.tbl = %d) and (p6.lang_id = $langID) and (p6.fld = 1)
          left outer join visibility vp6 on (vp6.tbl = %d)
                 and (vp6.interface_id = $interfaceID) and (vp6.id = hm.lvl6_id)
          where (i.i_id = '$incidentID')",
          vis_sql('vp1', 'p1.label', 'enduser'), vis_sql('vp2', 'p2.label', 'enduser'),
          vis_sql('vp3', 'p3.label', 'enduser'), vis_sql('vp4', 'p4.label', 'enduser'),
          vis_sql('vp5', 'p5.label', 'enduser'), vis_sql('vp6', 'p6.label', 'enduser'),
          $type, $type, $type, $type, $type, $type,
          $type,
          TBL_HIER_MENUS, TBL_HIER_MENUS, TBL_HIER_MENUS, TBL_HIER_MENUS,
          TBL_HIER_MENUS, TBL_HIER_MENUS, TBL_HIER_MENUS, TBL_HIER_MENUS, TBL_HIER_MENUS,
          TBL_HIER_MENUS, TBL_HIER_MENUS, TBL_HIER_MENUS));

        sql_bind_col($si, 1, BIND_NTS, 41);
        sql_bind_col($si, 2, BIND_NTS, 41);
        sql_bind_col($si, 3, BIND_NTS, 41);
        sql_bind_col($si, 4, BIND_NTS, 41);
        sql_bind_col($si, 5, BIND_NTS, 41);
        sql_bind_col($si, 6, BIND_NTS, 41);
        sql_bind_col($si, 7, BIND_INT, 0);
        sql_bind_col($si, 8, BIND_INT, 0);
        sql_bind_col($si, 9, BIND_INT, 0);
        sql_bind_col($si, 10, BIND_INT, 0);
        sql_bind_col($si, 11, BIND_INT, 0);
        sql_bind_col($si, 12, BIND_INT, 0);

        //Merging arrays to hide duplicate listings
        $results = array();
        $count = 0;
        for($i = 0; $row = sql_fetch($si); $i++)
        {
            //Go through each label to see if we should add it
            for($j = 0; $j < 6; $j++)
            {
                if($row[$j] != "")
                {
                    //Build up new item
                    $newItem = array();
                    $newItem['label'] = $row[$j];
                    $newItem['level'] = $j;
                    $newItem['id'] = $row[$j+6];
                    //Build up items id list
                    for($k = 6; $k <= $j+6; $k++)
                    {
                        if($row[$k] != "")
                        {
                            if($k == $j+6)
                                $newItem['hier_list'] .= $row[$k];
                            else
                                $newItem['hier_list'] .= $row[$k] . ",";
                        }
                        else
                        {
                            break;
                        }
                    }
                    //If the item is not in the list already, add it
                    if(!in_array($newItem, $results))
                    {
                        $results[$count] = $newItem;
                        $count++;
                    }
                }
                else
                {
                    break;
                }
            }
        }
        sql_free($si);
        setCache($cacheKey, $results);
        return $results;
    }

   /**
     * Function to populate the SLA values in an incident object
     *
     * @return array containing all SLA's associated to the logged in contact
     */
    static function getSlaInstances()
    {
        $CI = get_instance();
        $contactID = $CI->session->getProfileData('c_id');
        $orgID = $CI->session->getProfileData('org_id');
        $si = sql_prepare(sprintf("SELECT slai_id, label FROM sla_instances, labels
            WHERE sla_instances.owner_tbl=%d AND sla_instances.owner_id=%d
            AND labels.tbl=%d AND labels.lang_id=%d
            AND labels.label_id=sla_instances.sla_id
            AND ((expiredate IS NULL AND state=%d) OR (expiredate IS NOT NULL AND
            activedate < %s AND %s < expiredate AND state <= %d))
            AND IFNULL(inc_total, 1)>0 AND IFNULL(inc_web, 1)>0
            GROUP BY sla_set, slai_id, label, inc_web, inc_email, inc_chat,
            inc_csr, activedate HAVING MIN(activedate) = activedate
            ORDER BY activedate asc",
            $orgID > 0 ? TBL_ORGS: TBL_CONTACTS,
            $orgID > 0 ? $orgID : $contactID, TBL_SLAS, lang_id(LANG_DIR),
            SLAI_ACTIVE, time2db(time()),
            time2db(time()), SLAI_ACTIVE));

        sql_bind_col($si, 1, BIND_INT, 0);
        sql_bind_col($si, 2, BIND_NTS, 41);

        $results = array();
        for($i=0; $row = sql_fetch($si); $i++)
            $results[$row[0]] = $row[1];

        sql_free($si);
        return $results;
    }


    /**
     * Creates a new incident when a user submits feedback on a answer or on
     * the overall site
     *
     * @param $answerID int Answer ID of which feedback was given
     * @param $rate int Rating of feedback
     * @param $threshold int Threshold required for feedback to be submitted
     * @param $name String Name of user giving feedback
     * @param $message String Message given with feedback
     * @param $givenEmail String Email address given in feedback
     * @param $optionsCount object
     * @return bool denoting of submission was successful
     */
    function submitFeedback($answerID, $rate, $threshold, $name, $message, $givenEmail, $optionsCount=NULL)
    {
        //For usability purposes, we save this information so it can be used for login later
        $this->session->setSessionData(array('previouslySeenEmail'=>$givenEmail));

        if($threshold == null)
            $threshold = 100;
        if($givenEmail == null)
            $givenEmail = 'unknown@mail.null';

        if($rate <= $threshold)
        {
            $CI = get_instance();
            $incident = $this->getBlank();

            if(!is_null($answerID))
            {
                $url = getShortEufAppUrl('sameAsCurrentPage', getConfig(CP_ANSWERS_DETAIL_URL) . "/a_id/$answerID");
                $urlMessage = getMessage(THIS_FEEDBK_ABOUT_MSG) . ":\n$url\n\n";
                $incident->thread->value = $urlMessage . $message;
                $incidentSubjectSet = false;
                if (!is_null($optionsCount)) {
                    if ($optionsCount === '2') {
                        if ($rate === '1') {
                            $incident->subject->value = sprintf("%s %s (%s: %s)", getMessage(FEEDBACK_ANS_ID_HDG), $answerID, getMessage(RATED_LBL), getMessage(NOT_HELPFUL_LBL));
                            $incidentSubjectSet = true;
                        }
                        else {
                            $incident->subject->value = sprintf("%s %s (%s: %s)", getMessage(FEEDBACK_ANS_ID_HDG), $answerID, getMessage(RATED_LBL), getMessage(HELPFUL_LBL));
                            $incidentSubjectSet = true;
                        }
                    }
                }
                if (!$incidentSubjectSet) {
                    switch($rate)
                    {
                        case('1'):
                            $rank = getMessage(RANK1_LBL);
                            break;
                        case('2'):
                            $rank = getMessage(RANK2_LBL);
                            break;
                        case('3'):
                            $rank = getMessage(RANK3_LBL);
                            break;
                        case('4'):
                            $rank = getMessage(RANK4_LBL);
                            break;
                        case('5'):
                            $rank = getMessage(RANK5_LBL);
                            break;
                        default:
                            $rank = $rate;
                            break;
                    }
                    $incident->subject->value = sprintf("%s %s (%s: %s)", getMessage(FEEDBACK_ANS_ID_HDG), $answerID, getMessage(RATED_HELPFUL_LBL), $rank);
                }
                $incident->source->value = SRC2_EU_FB_ANS;
            }
            else
            {
                $incident->subject->value = getMessage(SITE_FEEDBACK_HDG);
                $incident->thread->value = $message;
                $incident->source->value = SRC2_EU_FB_SITE;
            }

            if(isLoggedIn())
            {
                $contactID = $CI->session->getProfileData('c_id');
                $orgID = $CI->session->getProfileData('org_id');
            }
            else
            {
                $email = strtolower($givenEmail);
                $contactFoundData = contact_match(array('email' => $email));
                $contactID = $contactFoundData['c_id'];
                $orgID = $contactFoundData['org_id'];
            }

            //Check if we need to do a contact create
            if(!$contactID)
            {
                $CI->load->model('standard/Contact_model');
                $contact = $CI->Contact_model->getBlank();
                $contact->email->value = strtolower($givenEmail);

                if(!is_null($answerID))
                    $contact->source->value = SRC2_EU_FB_ANS;
                else
                    $contact->source->value = SRC2_EU_FB_SITE;

                $contact->login->value = null;
                if($name != null)
                    $contact->first_name->value = $name;

                $preHookData = array('data'=>$contact);
                $customHookError = RightNowHooks::callHook('pre_contact_create', $preHookData);
                if(is_string($customHookError))
                    return $customHookError;
                AbuseDetection::check();
                $contactID = contact_create($contact->toPairData(null));
                if($contactID < 1)
                    return getMessage(SORRY_ERROR_SUBMISSION_LBL);
                $postHookData = array('data'=>$contact, 'returnValue'=>$contactID);
                RightNowHooks::callHook('post_contact_create', $postHookData);
            }
            $incident->c_id->value = $contactID;
            if($orgID)
                $incident->org_id->value = $orgID;

            $preHookData = array('data'=>$incident);
            $customHookError = RightNowHooks::callHook('pre_feedback_submit', $preHookData);
            if(is_string($customHookError))
                return $customHookError;

            $pairdata = $incident->toPairData(null);
            AbuseDetection::check();
            $icret = incident_create($pairdata);
            $postHookData = array('data'=>$incident, 'returnValue'=>$icret);
            RightNowHooks::callHook('post_feedback_submit', $postHookData);
            return $icret;
        }
        return 1;
    }

    /**
     * Function to convert a middle layer incident object into pairdata
     * and submit the updated incident to the API
     *
     * @param $incident Object A Incident middle layer object
     * @param $prevData Object The previous state of the incident before being updated.
     * @return mixed A string if an error was encountered or a 1 if successful
     */
    static function update($incident, $prevData)
    {
        if(!$incident->status->value)
            $incident->thread->required = true;
        $incident->source->value = SRC2_EU_MYSTUFF_Q;

        $preHookData = array('data'=>$incident);
        $customHookError = RightNowHooks::callHook('pre_incident_update', $preHookData);
        if(is_string($customHookError))
            return $customHookError;

        $error = $incident->validate();
        if(!$error)
        {
            AbuseDetection::check();
            $ret = incident_update($incident->toPairData($prevData));
            $postHookData = array('data'=>$incident, 'returnValue'=>$ret);
            RightNowHooks::callHook('post_incident_update', $postHookData);
            if($ret)
            {
                return 1;
            }
            else
            {
                return getMessage(SORRY_ERROR_SUBMISSION_LBL);
            }
        }
        else
        {
            return $error;
        }
    }

    /**
     * Function to convert a middle layer incident object into pairdata
     * and submit the new incident to the API.
     *
     * @param $incident Object The new incident to create
     * @param $smartAssist boolean Denoting of smart assistant should be run
     * @return mixed A string if an error was encountered or a 1 if successful
     */
    static function create($incident, $smartAssist)
    {
        $CI = get_instance();
        $incident->source->value = SRC2_EU_AAQ;
        $error = $incident->validate();

        $preHookData = array('data'=>$incident);
        $customHookError = RightNowHooks::callHook('pre_incident_create', $preHookData);
        if(is_string($customHookError))
            return $customHookError;

        if(!$error)
        {
            $pairdata = $incident->toPairData();
            if($smartAssist === true || $smartAssist === 'true')
            {
                // set source to smart assistant for rules
                $originalSource = $pairdata['source_upd']['lvl_id2'];
                $pairdata['source_upd']['lvl_id2'] = SRC2_EU_SMART_ASST;
                $smartAssistData = incident_suggest($pairdata);
                // reset source back to original value
                $pairdata['source_upd']['lvl_id2'] = $originalSource;

                if($smartAssistData->sa_found)
                {
                    $smartAssistResults = array();
                    if(count($smartAssistData->data))
                    {
                        $CI->load->model('standard/Answer_model');
                        $answerTypeSuggestions = array();
                        //TYPE 1 - Display answer summary as link to answer
                        //TYPE 2 - Display answer summary and description, no link
                        //TYPE 3 - Just display value sent in

                        foreach($smartAssistData->data as $value)
                        {
                            if($value->type == 1)
                            {
                                array_push($answerTypeSuggestions, $value->val);
                            }
                            else
                            {
                                if(count($answerTypeSuggestions))
                                {
                                    array_push($smartAssistResults, array('type' => 1, 'val' => $CI->Answer_model->getSmartAssistantDetails($answerTypeSuggestions)));
                                    $answerTypeSuggestions = array();
                                }

                                if($value->type == 2)
                                {
                                    array_push($smartAssistResults, array('type' => 2, 'val' => $CI->Answer_model->getSmartAssistantDetails(array($value->val))));
                                }
                                else
                                {
                                    $value->add_flag = $smartAssistData->add_flag;
                                    array_push($smartAssistResults, $value);
                                }
                            }
                        }
                        if(count($answerTypeSuggestions))
                        {
                            array_push($smartAssistResults, array('type' => 1, 'val' => $CI->Answer_model->getSmartAssistantDetails($answerTypeSuggestions)));
                        }
                    }
                    return $smartAssistResults;
                }
            }
            if($smartAssist === false || $smartAssist === 'false' || !$smartAssistData->sa_found)
            {
                $pairdata = $incident->toPairData();
                $pairdata['response']['type'] = IRT_RECEIPT;
                AbuseDetection::check();
                $ret = incident_create($pairdata);
                $incident->i_id->value = $ret;
                $postHookData = array('data'=>$incident, 'returnValue'=>$ret);
                RightNowHooks::callHook('post_incident_create', $postHookData);
                if($ret > 0)
                {
                    if(isLoggedIn() && !$CI->session->getProfileData('disabled'))
                    {
                        $curprofile = $CI->session->getProfile();
                        //Reverify the user so that SLA instances get updated
                        $profile = custlogin_verify($CI->session->getSessionData('sessionID'), $curprofile->cookie->value);
                        if($curprofile->openLoginUsed)
                            $profile->openLoginUsed = $curprofile->openLoginUsed->value;
                        $profile = $CI->session->createMapping($profile);
                        if($profile != null)
                            $CI->session->createProfileCookie($profile);
                        return $ret;
                    }
                    else
                    {
                        //Incident was created, but user isn't logged in, so we cant return i_id, we need the refno
                        $sql = "SELECT ref_no FROM incidents WHERE i_id = $ret";
                        $si = sql_prepare($sql);
                        sql_bind_col($si, 1, BIND_NTS, 14);

                        $row = sql_fetch($si);
                        $refNumber = $row[0];
                        sql_free($si);
                        return array('refno'=>$refNumber);
                    }
                }
                else
                {
                    return getMessage(SORRY_ERROR_SUBMISSION_LBL);
                }
            }
        }
        else
        {
            return $error;
        }
    }
   
    /**
     * Utility function to verify incident viewing based on contact ID and organization hierarchies
     * @param $incidentID int The incident ID to check
     * @param $contactIDOnIncident int The primary contact ID assigned to the incident
     * @param $organizationIDOnIncident int The primary org ID assigned to the incident
     */
    private function isContactAllowedToReadIncident($incidentID, $contactIDOnIncident, $organizationIDOnIncident)
    {
        if(!$incidentID || !isLoggedIn())
            return false;

        $CI = get_instance();
        $contactIDToCheck = $CI->session->getProfileData('c_id');
        $contactOrgID = $CI->session->getProfileData('org_id');

        $complaintAgainstOrgID = $this->_getComplaintAgainstOrgID($incidentID);

        //logMessage($contactIDToCheck);
        //logMessage($contactOrgID);
        //logMessage($contactIDOnIncident);
        //logMessage($organizationIDOnIncident);
        //logMessage($complaintAgainstOrgID);
        if((getConfig(MYQ_VIEW_ORG_INCIDENTS) > 1) && $contactOrgID > 0)
        {
            $organizationLevelQuery = "lvl" . $CI->session->getProfileData('o_lvlN') . '_id';
            $organizationLevel = sql_get_int(sprintf('select o.%s from incidents i join orgs o on i.org_id = o.org_id where (i.i_id = %d) and (i.org_id = o.org_id)', $organizationLevelQuery, $incidentID));
            if(!$organizationLevel){
                return false;
            }
        }
        // if the contact's org is a regulatory agency, then we need to look up the org state_jurisdiction
        // and make sure that it matches the incidents consumer_state field.
        try
        {
            initConnectAPI();
            $orgObj = RNCPHP\Organization::fetch($contactOrgID);
            $orgType = $orgObj->CustomFields->org_type->LookupName; // i.e. Regulatory Agency
            $stateJurisdiction = $orgObj->CustomFields->state_jurisdiction->LookupName; // i.e. AL
            $incObj = RNCPHP\Incident::fetch($incidentID);
            $consumerState = $incObj->CustomFields->consumer_state; // i.e. AL
            $regulatoryAccess = ($orgType === "Regulatory Agency" && (
                $consumerState === $stateJurisdiction || $stateJurisdiction == null)) ? true : false;
        }
        catch (RNCPHP\ConnectAPIError $err)
        {
            $msg = "Error Generated ::".$err->getCode()."::".$err->getMessage();
            die($msg);
        }

        return ($contactIDToCheck === $contactIDOnIncident) ||
               ((getConfig(MYQ_VIEW_ORG_INCIDENTS) === 1) && ($contactOrgID === $organizationIDOnIncident)) ||
               ((getConfig(MYQ_VIEW_ORG_INCIDENTS) === 2) && ($contactOrgID === $organizationIDOnIncident) || ($contactOrgID == $organizationLevel) ||
               ($contactOrgID === $complaintAgainstOrgID) ||
               ($regulatoryAccess)
        );
    }
    
    /**
     * Utility function to get org from ComplaintAgainstOrg custom object
     * @param $incidentID int The incident ID to check
     */
    static function _getComplaintAgainstOrgID($incidentID)
    {
        try
        {
            initConnectAPI();
            $res = RNCPHP\ROQL::queryObject( "SELECT Organization FROM CO.ComplaintAgainstOrg WHERE Incident = ".$incidentID." " )->next();
        }
            catch (RNCPHP\ConnectAPIError $err)
        {
            $msg = "Error Generated ::".$err->getCode()."::".$err->getMessage();
            die($msg);
        }
        $org = $res->next();
        return $org->ID;
    }


    /**
     * Finds all file attachments that are associated with the
     * specified incident ID. Only returns the file id and file name.
     *
     * @param $incidentID int The incident ID
     * @return array The array containing all file attachments
     */
    static function getFileAttachments($incidentID)
    {
        $sql = "SELECT file_id, userfname, created, content_type, private, sz FROM fattach WHERE id = $incidentID AND tbl = " . TBL_INCIDENTS. '  AND (!private OR private IS NULL)';
        $si = sql_prepare($sql);
        sql_bind_col($si, 1, BIND_INT, 0);
        sql_bind_col($si, 2, BIND_NTS, 241);
        sql_bind_col($si, 3, BIND_DTTM, 0);
        sql_bind_col($si, 4, BIND_NTS, 61);
        sql_bind_col($si, 5, BIND_INT, 0);
        sql_bind_col($si, 6, BIND_INT, 0);
        
        $results = array();
        for($i=0; $row = sql_fetch($si); $i++)
        {
            $results[$i] = $row;
            $results[$i]['icon'] = getIcon($row[1]);
            $results[$i]['size'] = getReadableFileSize($row[5]);
        }
        sql_free($si);
        return $results;
    }

    /**
     * Returns how many file attachments are on the existing incident
     *
     * @param $incidentID int The ID of the incident
     * @return int How many file attachments there are
     */
    static function getFileAttachmentCount($incidentID)
    {
        $sql = "SELECT count(file_id) FROM fattach WHERE id = " . $incidentID . " AND tbl = " . TBL_INCIDENTS . " AND (!private OR private IS NULL)";
        $si = sql_prepare($sql);
        sql_bind_col($si, 1, BIND_INT, 0);

        $row = sql_fetch($si);
        return $row[0];
    }

    /**
     * Gets the solved date for the specified incident
     * @param $incidentID int The incident ID
     * @return A timestamp of when the incident was closed
     */
    static function getIncidentSolvedDate($incidentID)
    {
        $sql = "SELECT closed FROM incidents WHERE i_id = $incidentID";
        $si = sql_prepare($sql);
        sql_bind_col($si, 1, BIND_DTTM, 0);

        $row = sql_fetch($si);
        sql_free($si);
        return $row[0];
    }
}
