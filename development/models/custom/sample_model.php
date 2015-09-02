<?php
class Sample_model extends Model
{
    function __construct()
    {
        parent::__construct();
        //This model would be loaded by using $this->load->model('custom/Sample_model');
    }

    function sample_function()
    {
        //This function would be run by using $this->Sample_model->sample_function()

    }
}
