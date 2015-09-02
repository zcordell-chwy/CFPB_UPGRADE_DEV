<?php
/*
*	This file contains a list of IP addressess to allow during upgrade cutover.
*	There are a few files and configs involved that make the magick happen:
*
*	scripts/custom/upgrade-allowed-ups.php //list of ips, placed in custom scripts folder so we do not have to deploy updates to the list
*	scripts/euf/application/models/custom/splash_model.php	//does the ip restriction
*	scripts/euf/application/config/hooks.php	//configure a model/function to call before page is rendered to the browser
*	scripts/euf/application/views/splash.php	//splash page non-authorized users will see
*/

class Splash_model extends Model 
{
    function Splash_model()
    {
        parent::Model();
        //This model would be loaded by using $this->load->model('custom/Splash_model');
    }

    function splash()
    {
		//do a splash page check
		$CI =& get_instance();
		$home = getConfig(CP_HOME_URL);
		$ip = $_SERVER['REMOTE_ADDR'];
		logMessage("IP Addr: $ip");

		require_once get_cfg_var("doc_root").'/custom/upgrade-allowed-ips.php';	//we arent use config doc_root because files in other interfaces call this file specifically

		//change netmask to cidr
		foreach($masks as $mask)
		{
			@list($mask_address, $netmask) = explode('/', $mask);
			$cidr = 0;
			foreach (explode('.', $netmask) as $number) 
			{
				for (;$number> 0; $number = ($number <<1) % 256) 
				{	
					$cidr++;
				}
			}
			$ranges[] = $mask_address."/".$cidr;
		}

		//get ip ranges 
		foreach($ranges as $range)	
		{
			@list($address, $len) = explode('/', $range);

			if(($min = ip2long($address)) !== false)
			{
				$max = ($min | (1<<(32-$len))-1);
				for ($i = $min; $i < $max; $i++)
					$valid_ips[] = long2ip($i);
			}
		}

		//check if ip valid and allow
		if(in_array($ip, $valid_ips)){
			return;

		}else{
			//do hostname lookup to see if the user's ip/domain is authorized
			//we do the hostname lookup here to prevent already matched IPs from triggering a hostname lookup request
			$hostname = gethostbyaddr($ip);
			foreach($hosts as $host){
				if(strpos($hostname, $host) > -1){
					return;	
				}
			}
		}
		
		//redirect invalid ips to splash page
        if($CI->page !== "splash"){
            header("Location: /app/splash");
            die;
        }
    }
}