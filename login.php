<?php require $_SERVER['DOCUMENT_ROOT'].'/core/start.php';if($objLinks['USER']['is_login']){redirect("/");}
$HumanVerify=@$_SESSION['humanverify'];
$recaptcha=false;
if (isset($_GET['oauth_provider'])){redirect("/login/{$_GET['oauth_provider']}/go");};

if (!empty($_POST) && !strcmp($_POST['auth_token'], get_token_id()) ){
	$objLinks['FORM']=array_merge($objLinks['FORM'],$_POST['session']);
	//*************** !!!!!!!!!!!!!!!!!!! *************** 
	$username=@$_POST['session']['username'];if (!preg_match("/^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/",$username)){ addObjFormError('1100001');}
	$password=@$_POST['session']['password'];if (!preg_match("/^.{3,16}$/",$password)){ addObjFormError('1100011');}
	//*************** !!!!!!!!!!!!!!!!!!! *************** 
	if($HumanVerify){
	$reCaptcha = new ReCaptcha($start->reCaptchaSecret);
	$captcha=@$_POST["g-recaptcha-response"];if($captcha){$resp=$reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"],$captcha);}if ($resp==null || !$resp->success){addObjFormError('1229901');}
	}#fine humanverify
	//*************** !!!!!!!!!!!!!!!!!!! *************** 
	if (empty($objFormError) ){$password=sha1($password);
	//echo POSTjson($start->wss.'/login',json_encode(array('username'=>$username,'password'=>$password),true));exit;
	$WSS_result=@json_decode(POSTjson($start->wss.'/login',json_encode(array('username'=>$username,'password'=>$password),true)),true);
	switch (@$WSS_result['status']) {
	case 'NOUSERNAME':addObjFormError('1100101');break;
	case 'INVALID':addObjFormError('1100121');break;
	case 'OK':update_user_session($WSS_result['results']);$_SESSION['humanverify']=array();unset($_SESSION['humanverify']);session_write_close();
	$urlnext=(isset($_GET['next']))?$_GET['next']:'/';redirect("{$urlnext}");break;
	default:addObjFormError('1987');
	//try{if (!array_key_exists('results',$WSS_result)){throw new Exception( $WSS_result['errors']['message']."...");};}
	//		catch (Exception $e){array_push($objFormError,array('type'=>$e->getMessage(),'code'=>'2323'));}
	break;
	}
}else{ $_SESSION['humanverify']=true;}
$objLinks['form_error']=array('error_list'=>$objFormError);$_SESSION['requestcaptcha']=time();
};
if($HumanVerify){$recaptcha=array('reCaptchaKey'=>$start->reCaptchaKey);}

$objLinks=array_merge($objLinks,array(
	'HTML_ID'=>'pFull',
	'PAGE_TITLE'=>'Accedi a Anniversify',
	'PAGE_DESCRIPTION'=>'Accedi ora per controllare le notifiche, gli annivesari di oggi e scoprire nuove storie dei tuoi contatti.',
	'FORM'=>array_merge($objLinks['FORM'],array(
	'recaptcha'=>$recaptcha
	)),
	'GONEXT'=>(isset($_GET['next']))?urlencode($_GET['next']):false,
	'PLUG_LOGIN'=>true
));
$render="/login.html";
//######################################################################################################################
$start->render($objLinks,$render);
?>