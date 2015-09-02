<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class SampleLibrary
{
    function __construct()
    {
        //This library would be loaded by using $this->load->library('samplelibrary') or
        //by using $this->CI->load->library('SampleLibrary') when in a widget controller.
    }

    function sampleFunction()
    {
        //This function would be called by using $this->samplelibrary->sampleFunction() or
        //by using $this->CI->samplelibrary->sampleFunction() once the library has been loaded.
    }
}
