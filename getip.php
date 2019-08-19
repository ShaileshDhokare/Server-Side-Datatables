<?php
$saveToken = $request->get('saveToken');
		$xForwardedFor = $request->header('x-forwarded-for');
		if (empty($xForwardedFor)) 
		{
			$ip = $request->ip();
		} 
		else 
		{
			$ips = is_array($xForwardedFor) ? $xForwardedFor : explode(', ', $xForwardedFor);
			$ip = $ips[0];
		}
		$user_agent = $_SERVER['HTTP_USER_AGENT']; 
		if (preg_match('/MSIE/i', $user_agent)) { $browser = "Internet Explorer";} 
		elseif (preg_match('/Firefox/i', $user_agent)){$browser = "Mozilla Firefox";} 
		elseif (preg_match('/Chrome/i', $user_agent)){$browser = "Google Chrome";} 
		elseif (preg_match('/Safari/i', $user_agent)){$browser = "Safari";} 
		elseif (preg_match('/Opera/i', $user_agent)){$browser = "Opera";}
		else {$browser = "Other";}
		
		
		$res = file_get_contents('https://www.iplocate.io/api/lookup/'.$ip);
		$res = json_decode($res);

        $insertArray = array(
            "ema_email"=>$emailAddress,
            "ema_ind"=>$selectedCategories,
            "ema_freq"=>$selectedFreq,
            "createdDate"=> Date("Y-m-d H:i:s"),
            "modifiedDate"=> Date("Y-m-d H:i:s"),
			"browserName" => $browser,
			"ipAddress" => $res->ip,
			"country" => $res->country,
			"country_code" => $res->country_code,
			"city" => $res->city,
			"continent" => $res->continent,
			"latitude" => $res->latitude,
			"longitude" => $res->longitude,
			"time_zone" => $res->time_zone,
			"postal_code" => $res->postal_code,
			"org" => $res->org,
			"asn" => $res->asn,
			"subdivision" => $res->subdivision,
			"subdivision2" => $res->subdivision2,
			"subscriptionStatus" => 'Yes'
        );
        @php
    if (isset($elements[2]) || count($elements[0]) > 2) {
        unset($elements[4]);
    }
    // dd($elements);
@endphp