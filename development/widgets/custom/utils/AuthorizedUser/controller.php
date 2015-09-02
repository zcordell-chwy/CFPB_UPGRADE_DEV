<?php
if(!defined('BASEPATH'))
	exit('No direct script access allowed');

class AuthorizedUser extends Widget {
	function __construct() {
		parent::__construct();

		$this->attrs['page_type'] = new Attribute(
            'Page Type',
            'STRING',
            "Comma-delimited list of user types that can access this page. Redirects the user to an error page if the logged in user lacks the propper credentials.",
            ''
        );
	}

	function generateWidgetInformation() { //Create information to display in the tag gallery here
	}

	function getData() {
        // What type of user is this?
        $this->CI->load->model( 'custom/ContactPermissions_model' );
        $currentUserType = $this->CI->ContactPermissions_model->userType();

        $legalUsers = explode( ',', $this->data['attrs']['page_type'] );
        $userAllowedAccess = false;

        foreach( $legalUsers as $user )
        {
            if( trim( $user ) == $currentUserType )
            {
                $userAllowedAccess = true;
                break;
            }
        }

        $this->CI->load->helper( 'config_helper' );

        if( !$userAllowedAccess )
        {
            header( sprintf( "Location: %s", getSetting( 'ERROR_PAGE_URL' ) ) );
        }
	}

}
