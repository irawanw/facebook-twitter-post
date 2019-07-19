/****************************************************
* 5. START ONCE every noon every day 				*
*****************************************************/
if($Time_now == '12')
{
  //facebook access token
  //can be generated in facebook debuging tools
  //we need to get access token which have unlimited lifetime
	$page_access_token = '';
	$page_id = '';	
	$args =[
		'usertoken'	=> '',
		'appid'		=> '',
		'appsecret'	=> '',
		'pageid'	=> ''
	];
	
  //twitter access token
  //we need to get access token to use the API
	$consumer_public_key = '';
	$consumer_secret_key = '';
	$token_public_key = '';
	$token_secret_key = '';
	
	//request
	require_once $path.'inc/twitter/twitter.class.php';
	$twitter = new Twitter($consumer_public_key, $consumer_secret_key, $token_public_key, $token_secret_key);

	$product_number = 0;
	
	$sql = mysql_query("SELECT * FROM productrelease_posting");
	if(mysql_num_rows($sql) > 0)
	{
		while($result = mysql_fetch_assoc($sql))
		{
	
			$data['picture'] 	  = $result['image'];
			$data['link'] 		  = $result['url'];
			$data['message'] 	  = $result['title'];
			$data['description']  = "";
			$data['caption'] 	  = "";
			$data['access_token'] = $page_access_token;

			$post_url = 'https://graph.facebook.com/'.$page_id.'/feed';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $post_url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$return = curl_exec($ch);
			curl_close($ch);
			
			$temp = preg_split('/twitter_images/', $data['picture']);
			$filename = end($temp);
			copydirr($path.'images/', $path.'twitter_images/', $filename, 0777, true);			
			
			$desc = preg_split('/http/', $data['message']);
			
			if(file_exists($path.'twitter_images/'.$filename))
				$tweet = $twitter->send(substr(trim($desc[0]), 0, 135).'... '.$data['link'], $path.'twitter_images/'.$filename);
			
			$product_number++;
		}
		mysql_query("DELETE FROM productrelease_posting");
		
		mysql_query("INSERT INTO posting_logs 
					(`date_run`, `post_type`, `shows`) 
				VALUES (NOW(), 'Post product release on facebook and twitter', '".$product_number."')");
	}
}
