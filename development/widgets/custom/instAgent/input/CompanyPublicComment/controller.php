<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class CompanyPublicComment extends Widget
{
    function __construct()
    {
        parent::__construct();

        //Create attributes here
        $this->attrs['display_consent_status'] = new Attribute('Display Consent Status', 'BOOL', 'Whether or not to show the Consent Status', false);
        $this->attrs['display_company_comment'] = new Attribute('Display Company Comment', 'STRING', 'Whether or not to show the read-only Company Public Comment', "false");
        $this->attrs['display_company_comment_form'] = new Attribute('Display Company Comment Form', 'STRING', 'Whether or not to show the radio buttons for Company Public Comment', "false");
        $this->attrs['return_to_page'] = new Attribute('Return to page', 'STRING', 'Used to construct URL that determines which overview tab we return to', 'list_active');
        
    }

    function generateWidgetInformation()
    {
        //Create information to display in the tag gallery here
        $this->info['notes'] =  "Company Public Comment radio buttons and hidden text field";        
    }

    function getData()
    {        
        // initialize radio button contents
        // "display text" => "category"
        $this->data['js']['radioLabel'] = array(
            'Company believes it acted appropriately as authorized by contract or law'=>'Company acted appropriately',
            'Company disputes the facts presented in the complaint'=>'Factual dispute',
            'Company can\'t verify or dispute the facts in the complaint'=>'Unable to verify facts',
            'Company believes the complaint is the result of a misunderstanding'=>'Misunderstanding',
            'Company believes complaint relates to a discontinued policy or procedure'=>'Discontinued policy or procedure',
            'Company believes complaint represents an opportunity for improvement to better serve consumers'=>'Opportunity for improvement',
            'Company believes complaint is the result of an isolated error'=>'Isolated error',
            'Company believes complaint caused principally by actions of third party outside the control or direction of the company'=>'Third party',
            'Company chooses not to provide a public response'=>'No public response'
        );        
        $this->data['js']['radioLabelDisplayText'] = array_keys($this->data['js']['radioLabel']); // grab just the labels for easier use
        $this->data['js']['radioLabelCategories'] = array_values($this->data['js']['radioLabel']); // grab just the categories for easier use
        
        $this->data['js']['number_of_choices'] = count($this->data['js']['radioLabelDisplayText']);
    }
}



