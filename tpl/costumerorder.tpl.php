<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
global $langs, $user, $conf;
$id=GETPOST('id','int');
$mode = 'readonly';
if($user->rights->externalaccess->edit_user_personal_infos  && ($context->action == 'edit' || $context->action == 'saveError')){
    $mode = 'edit';
}else{
	$mode=$context->action;
}
$step=GETPOST('step','int');
if(empty($step))$step=1;
print '<section id="section-personalinformations"  class="type-content"  ><div class="container">';
//var_dump($user);



if($user->rights->externalaccess->edit_user_personal_infos && $mode=='readonly'){
    print '<a class="btn btn-primary pull-right btn-top-section" href="'.$context->getRootUrl('personalinformations').'&amp;action=edit"  ><i class="fa fa-pencil"></i> '.$langs->trans('exa_Edit').'</a>';
}

/*
print '<h3 class="text-center">'.$langs->trans('YourPersonnalInformations').'</h3>';
print '<hr/>';
print '<h6 class="text-center">'.$user->firstname .' '. $user->lastname.'</h6>';
*/

print '<h5 class="text-center text-primary">'.$langs->trans('YourClientOrder').'</h5>';


if($context->action=='saved'){
    print '<div class="alert alert-success" role="alert">'.$langs->trans('Saved').'</div>';
}

if($context->action=='saveError'){
    print '<div class="alert alert-danger" role="alert">'.$langs->trans('ErrorDetected').'</div>';
}

print '<form method="post" action="'.$context->getRootUrl('costumerorder').'&amp;action=save">';
//print '<div class="card" >';
//


print '<div class="row">';



print '<div class="col-md-6 offset-md-3"><div class="card"><div class="card-body">';
	

print ' <ul class="nav nav-pills  nav-fill" role="tablist">';
print '  <li class="nav-item"><a '.isactive($step,1).' >'.$langs->trans('Step1').'</a></li>';
print '  <li class="nav-item"><a '.isactive($step,2).' >'.$langs->trans('Step2').'</a></li>';
print '  <li class="nav-item"><a '.isactive($step,3).' >'.$langs->trans('Step3').'</a></li>';
 print '</ul> ';

 print '<div class="tab-content" id="myTabContent">';
 print '<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">';
	if(!($id>0)){
	switch($step){
		
		case 1:
			if(isset($_SESSION['socid']))
			$socid=$_SESSION['socid'];
			print ulist_soc($user->id,$socid);
			unset($_SESSION['orderlines']);
			break;
		case 2:
			$_SESSION['socid']=GETPOST('socid','int');
			$batch=GETPOST('batch','alpha');
			$batchid=GETPOST('batchid','alpha');
				$eatby=GETPOST('eatby','alpha');
			if(!isset($_SESSION['orderlines']))
				$_SESSION['orderlines']= array();
			if(!empty($batch)){
				//var_dump($batch,$_SESSION['orderlines'],in_array($batch,$_SESSION['orderlines']));
				if(in_array($batch,array_keys($_SESSION['orderlines']))===false){
					$_SESSION['orderlines'][$batch]['batch']=$batch;
						$_SESSION['orderlines'][$batch]['batchid']=$batchid;
						$_SESSION['orderlines'][$batch]['eatby']=$eatby;
					$_SESSION['orderlines'][$batch]['fk_product']=GETPOST('fk_product','int');
				}
				else unset($_SESSION['orderlines'][$batch]);				
			}
			
			print ulist_stock($user->fk_warehouse,GETPOST('prodid','int'),$_SESSION['orderlines']);
			break;
		case 3: print confirm_costumerorder($_SESSION['socid'],$_SESSION['orderlines']);
			break;
	}
	}else{
		print print_viewcostumerorder($id);
	}
 print '</div>';
 print '</div>';

print '</div></div></div>';

print '</div>';
function isactive($step,$current){
	if(empty($step) && $current==1) return 'class="nav-link active" role="tab"  aria-selected="true" ';
	return $step==$current?'class="nav-link active" role="tab"  aria-selected="true"':'class="nav-link" role="tab"  aria-selected="false"';
}
//var_dump($user);

if($step==3){
	// id
   $param = array('valid'=>1, 'feedback' => '','type'=>'hidden');
  stdFormHelper('order[socid]', '', $_SESSION['socid'], $mode, 1,$param);
		$param = array('valid'=>1, 'feedback' => '','type'=>'hidden');
  stdFormHelper('order[date]', '', dol_now(), $mode, 1,$param);
		$param = array('valid'=>1, 'feedback' => '','type'=>'hidden');
		stdFormHelper('step', '', 3, $mode, 1,$param);
	}else{
		$param = array('valid'=>0, 'feedback' => '','type'=>'hidden');
		stdFormHelper('commercial[]', '', $user->id, $mode, 1,$param);
		$param = array('valid'=>0, 'feedback' => '','type'=>'hidden');
		stdFormHelper('custcats[]', '', $conf->globals->STREAM_ACCOUNT_CAT_SOC, $mode, 1,$param);
	}
print '</form>';


print '</div></section>';

