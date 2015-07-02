<?php require $_SERVER['DOCUMENT_ROOT'].'/core/start.php';
$facebook = new Facebook(array('appId' => FB_APP_ID,'secret' => FB_APP_SECRET));
$user = $facebook->getUser();
if ($user) {
try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
	//echo json_encode($user_profile);exit; --> {"id":"10200645572339540","birthday":"10\/19\/1987","email":"d.pellegrino@hotmail.it","first_name":"Davide","gender":"male","last_name":"Pellegrino","link":"https:\/\/www.facebook.com\/app_scoped_user_id\/10200645572339540\/","locale":"it_IT","name":"Davide Pellegrino","timezone":1,"updated_time":"2013-12-06T09:40:45+0000","verified":true}
  } catch (FacebookApiException $e) {error_log($e);$user = null;}

    if (!empty($user_profile )) {
		#echo json_encode($user_profile);exit;
		$nome=$user_profile['first_name'];
		$cognome=$user_profile['last_name'];
		$uid = $user_profile['id'];
		$email = $user_profile['email'];
		$sesso = $user_profile['gender'];
		$nascita = $user_profile['birthday'];
		$verificato=$user_profile['verified'];
        //verifico X1 JSON ed effettuo login
		$user = new User();
		$userdata = $user->checkUser($uid, 'fb', $username,$email,$twitter_otoken,$twitter_otoken_secret);
			if(!empty($userdata)){
				print_r($userdata);exit;
				update_user_session($userdata);
				session_write_close();
				$urlnext=(isset($_GET['next']))?$_GET['next']:'/';redirect($urlnext);
			}else{
			//NON ESISTE, QUINDI PROCEDO CON MEMO IN SESSIONE E REINDIRIZZO A JOIN X REGISTRAZIONE
			$joinsocial=array('nome'=>$nome,'cognome'=>$cognome,'nascita'=>$nascita,'email'=>$email,'sesso'=>(!strcmp($sesso,'male'))?'M':'F','avatar'=>'http://graph.facebook.com/'.$uid.'/picture?type=large','oauth_id'=>$uid,'oauth_provider'=>'fb','verificato'=>$verificato);
			$_SESSION["joinsocial"]=$joinsocial;redirect('/join');
			#header("Location: /join");
			};
		
    } else {
        # For testing purposes, if there was an error, let's kill the script
        die("There was an error.");
    }
} else {
    # There's no active session, let's generate one
	$login_url = $facebook->getLoginUrl(array( 'scope' =>'email,user_birthday'));
	redirect($login_url);
    #header("Location: " . $login_url);
}
?>
