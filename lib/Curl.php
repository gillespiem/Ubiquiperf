<?php
/**
 * Class to handle curl requests
 *  It's a bit rough now, but whatever.
 * 
 * @author Matthew Gillespie
 */
class Curl
{

	protected $cookiefile;
	protected $useragent;
	protected $referrer;
	protected $postdata;

	/**
	 * Method to set the cookiefile
	 *
 	 * @param filename
	 */
	public function setCookieFile($file)
	{
		$this->cookiefile = $file;
	}
	
	/**
	 * Method to set user agent
	 *
	 * @param user agent string
	 */
	public function setUserAgent($ua)
	{
		$this->useragent = $ua;
	}

	/**
	 * Method to set user referrer
	 *
	 * @param http referrer string
	 */
	public function setReferrer($ref)
	{
		$this->referrer = $ref;
	}

	/**
	 * Method to set post data
	 *
 	 * @note This was an attempt to fix something, I forget what.
	 * 	  Probably something relating to multipart form data.
	 *        Just bypass this and set CURLOPT_POSTFIELDS with an array, 
	 *	  AND AVOID setting CURLOPT_POST to anything
	 * @param data as an array
	 */
	public function setPostData($data)
	{
		$temp = "";
		foreach ($data as $key=>$val)
		{
			if (strlen($temp) > 0)
			{
				$temp.="&";
			}
			
			$temp .="{$key}={$val}";
		}

		$this->postdata = $temp;
	}

	/**
	 * Method to post to a url
	 *
 	 * @param url
	 */
	public function Post($url)
	{
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1"); 
		curl_setopt($ch, CURLOPT_REFERER, $this->referrer);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postdata);
		
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $head; 
	}

	/**
	 * Method to post to a url
	 *
 	 * @param url
	 */
	public function Get($url)
	{
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1"); 
		curl_setopt($ch, CURLOPT_REFERER, $this->referrer);
		curl_setopt($ch, CURLOPT_GET, 1);
		
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return $head; 
	}


}
?>
