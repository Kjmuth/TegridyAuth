<?php
if(!defined('IN_HM')) {
	exit('Access Denied');
}
if($_POST){
	if(!is_numeric($_POST['num'])||$_POST['num']<=0){
		$_HTML['error_txt'] = $_LNG['license']['forbidden'];
		goto End;
	}
	if($_SESSION['login']!=1){
		// Will update this later when I have more time
		switch($_POST['lice_type']){
			case 'd':
				if($_SESSION['d_limit']<$_POST['num']||$_SESSION['all_limit']<$_POST['num']){
					if($_SESSION['all_limit']<$_POST['num']){
						$_HTML['error_txt'] = $_LNG['license']['not_enough'].$_LNG['license']['type_total'];
					}
					else{
						$_HTML['error_txt'] = $_LNG['license']['not_enough'].$_LNG['license']['type_d'];
					}
					goto End; 
				}
				break;
			case 'w':
				if($_SESSION['w_limit']<$_POST['num']||$_SESSION['all_limit']<$_POST['num']){
					if($_SESSION['all_limit']<$_POST['num']){
						$_HTML['error_txt'] = $_LNG['license']['not_enough'].$_LNG['license']['type_total'];
					}
					else{
						$_HTML['error_txt'] = $_LNG['license']['not_enough'].$_LNG['license']['type_w'];
					}
					goto End; 
				}
				break;
			case 'm':
				if($_SESSION['m_limit']<$_POST['num']||$_SESSION['all_limit']<$_POST['num']){
					if($_SESSION['all_limit']<$_POST['num']){
						$_HTML['error_txt'] = $_LNG['license']['not_enough'].$_LNG['license']['type_total'];
					}
					else{
						$_HTML['error_txt'] = $_LNG['license']['not_enough'].$_LNG['license']['type_m'];
					}
					goto End; 
				}
				break;
			default:
				$_HTML['error_txt'] = $_LNG['license']['forbidden'];
				goto End; 
				break;
		}
	}
	// Check the validity of the application ID
	$sql = 'SELECT * FROM `'.$_config['db']['dbname'].'`.`application` WHERE `id` = '.$_POST['app_type'];
	$res = $mysqli->query($sql);
	if(!@$res->num_rows){
		//This can be skipped
		if($_SESSION['login']==1&&$_POST['app_type']=='n'){
			$_POST['app_type'] = -1;
		}else{
			$_HTML['error_txt'] = $_LNG['license']['forbidden'];
			goto End; 
		}
	}
	// This should work ok after 03/24 changes
	$time = new DateTime();
	header("Content-Type: application/octet-stream");
	header('Content-Disposition: attachment; filename="'.$time->format('YmdHis').'.txt"');
	$sql = 'INSERT INTO `licenses` (`serial`,`days`,`application`,`create_by`) VALUES';
	for($i=0;$i<$_POST['num'];$i++){
		$keyg = key_gen();
		echo $keyg."\r\n";
		$sql .='(\''.$keyg.'\',\''.$_POST['lice_type'].'\',\''.$_POST['app_type'].'\',\''.$_SESSION['login'].'\'),';
	}
	$mysqli->query(substr($sql,0,-1).';');
	
	switch($_POST['lice_type']){
		case 'd':
			$_SESSION['d_limit'] -= $_POST['num'];
			break;
		case 'w':
			$_SESSION['w_limit'] -= $_POST['num'];
			break;
		case 'm':
			$_SESSION['m_limit'] -= $_POST['num'];
			break;
	}
	$_SESSION['all_limit'] -= $_POST['num'];
	exit();
}
End:
$custum_page['add'][] = '/'.$action.'.htm';
$_HTML['applist'] = '';
$sql = 'SELECT * FROM `'.$_config['db']['dbname'].'`.`application` WHERE 1';
$res = $mysqli->query($sql);
foreach($res as $data){
	$_HTML['applist'] .= '<option value="'.$data['id'].'">'.$data['name'].'</option>';
}

//This is weird but kinda works
function key_gen(){
	$key = strtoupper(sha1(uniqid('',true)));
	return substr($key,0,5).'-'.substr($key,14,5).'-'.substr($key,28,5).'-'.substr($key,7,5).'-'.substr($key,21,5);
}
