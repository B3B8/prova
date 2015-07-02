<?php require $_SERVER['DOCUMENT_ROOT'].'/core/start.php';
/*lascio qui un commento di modifica, seconda, e faccio un po di spazi e tolgo humanverify*/
if($objLinks['USER']['is_login']){
	redirect("/");
	}
if (isset($_GET['oauth_provider'])){redirect("/login/{$_GET['oauth_provider']}/go");};

if (!empty($_POST) && !strcmp($_POST['auth_token'], get_token_id()) ){
	$objLinks['FORM']=array_merge($objLinks['FORM'],$_POST['session']);
	//*************** !!!!!!!!!!!!!!!!!!! *************** 
	$username=@$_POST['session']['username'];if (!preg_match("/^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/",$username)){ addObjFormError('1100001');}
	$password=@$_POST['session']['password'];if (!preg_match("/^.{3,16}$/",$password)){ addObjFormError('1100011');}
	//*************** !!!!!!!!!!!!!!!!!!! *************** 
	if (empty($objFormError) ){
	$password=sha1($password);
	$WSS_result=@json_decode(POSTjson($start->wss.'/login',json_encode(array('username'=>$username,'password'=>$password),true)),true);
	switch (@$WSS_result['status']) {
	case 'NOUSERNAME':addObjFormError('1100101');break;
	case 'INVALID':addObjFormError('1100121');break;
	case 'OK':update_user_session($WSS_result['results']);$_SESSION['humanverify']=array();unset($_SESSION['humanverify']);session_write_close();
	$urlnext=(isset($_GET['next']))?$_GET['next']:'/';redirect("{$urlnext}");break;
	default:addObjFormError('1987');
	break;
	}
}else{
	$_SESSION['humanverify']=true;
	}
$objLinks['form_error']=array('error_list'=>$objFormError);$_SESSION['requestcaptcha']=time();
};
$objLinks=array_merge($objLinks,array(
	'HTML_ID'=>'pFull',
	'PAGE_TITLE'=>'Accedi a Anniversify',
	'PAGE_DESCRIPTION'=>'Accedi ora per controllare le notifiche, gli annivesari di oggi e scoprire nuove storie dei tuoi contatti.',
	'FORM'=>array_merge($objLinks['FORM'],array()),
	'GONEXT'=>(isset($_GET['next']))?urlencode($_GET['next']):false,
	'PLUG_LOGIN'=>true
));
$render="/login.html";
//######################################################################################################################
$start->render($objLinks,$render);
?>