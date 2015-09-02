<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class FormSubmit extends Widget {
	function __construct () {
		parent::__construct();
		$this->attrs['label_button'] = new Attribute(getMessage(BUTTON_LABEL_LBL), 'STRING', getMessage(LABEL_TO_DISPLAY_ON_THE_BUTTON_LBL), getMessage(SUBMIT_CMD));
		$this->attrs['label_confirm_dialog'] = new Attribute(getMessage(CONFIRM_DIALOG_LBL), 'STRING', getMessage(MSG_DISPLAYED_MODAL_DIALOG_MSG), '');
		$this->attrs['on_success_url'] = new Attribute(getMessage(ON_SUCCESS_URL_LBL), 'STRING', getMessage(URL_REDIRECT_FORM_SUBMISSION_MSG), '');
		$this->attrs['loading_icon_path'] = new Attribute(getMessage(LOADING_ICON_PATH_LBL), 'STRING', getMessage(FILE_PATH_IMG_DISP_SUBMITTING_FORM_LBL), 'images/indicator.gif');
		$this->attrs['error_location'] = new Attribute(getMessage(ERROR_LOCATION_LBL), 'STRING', getMessage(UNIQ_ID_LT_DIV_AMPERSAND_MSG), '');
		$this->attrs['add_params_to_url'] = new Attribute(getMessage(ADD_PRMS_TO_URL_CMD), 'STRING', sprintf(getMessage(CMMA_SEPARATED_L_URL_PARMS_MSG), 'on_success_url'), '');
		$this->attrs['challenge_location'] = new Attribute(getMessage(CHALLENGE_LOCATION_LBL), 'STRING', getMessage(UNQ_ID_LT_DV_AMPERSAND_MSG), '');
		$this->attrs['challenge_required'] = new Attribute(getMessage(CHALLENGE_REQUIRED_LBL), 'BOOL', getMessage(INDICATES_FORM_REQS_A_VALID_HUMAN_MSG), false);
	}

	function generateWidgetInformation () {
		$this->info['notes'] = getMessage(WDGET_DISP_HTML_SUBMIT_BTN_SUBMIT_MSG);
	}

	function getData () {
		// f_tok is used for ensuring security between data exchanges.
		// Do not remove.
		$this->data['js'] = array(
			'f_tok'			 => cpCreateTokenExp(0, $this->data['attrs']['challenge_required']),
			'formExpiration' => (60000 * (getConfig(SUBMIT_TOKEN_EXP) - 5)) //warn of form expiration five minutes (in milliseconds) before the token expires
			);
		if ($this->data['attrs']['challenge_required'] && $this->data['attrs']['challenge_location']) {
			$this->data['js']['challengeProvider'] = AbuseDetection::getChallengeProvider();
		}
		$this->data['attrs']['on_success_url'] .= getUrlParametersFromList($this->data['attrs']['add_params_to_url']);
		if (getUrlParmString('redirect')) {
			//Check if the redirect location is a fully qualified URL, or just a relative one
			$redirectLocation = urldecode(urldecode(getUrlParm('redirect')));
			$parsedURL = parse_url($redirectLocation);

			if ($parsedURL['scheme'] || (beginsWith($parsedURL['path'], '/ci/') || beginsWith($parsedURL['path'], '/cc/'))) {
				$this->data['attrs']['on_success_url'] = $redirectLocation;
			} else {
				$this->data['attrs']['on_success_url'] = "/app/$redirectLocation";
			}
		}
	}
}
