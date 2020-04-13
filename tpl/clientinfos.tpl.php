<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
global $langs, $user, $conf;

$mode = 'readonly';
if($user->rights->externalaccess->edit_user_personal_infos  && ($context->action == 'edit' || $context->action == 'saveError')){
    $mode = 'edit';
}else{
	$mode=$context->action;
}

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

print '<h5 class="text-center text-primary">'.$langs->trans('YourPersonnalInformations').'</h5>';


if($context->action=='saved'){
    print '<div class="alert alert-success" role="alert">'.$langs->trans('Saved').'</div>';
}

if($context->action=='saveError'){
    print '<div class="alert alert-danger" role="alert">'.$langs->trans('ErrorDetected').'</div>';
}

print '<form method="post" action="'.$context->getRootUrl('clients').'&amp;action=save">';
//print '<div class="card" >';
//


print '<div class="row">';



print '<div class="col-md-6 offset-md-3"><div class="card"><div class="card-body">';
	if($id>0){
	// id
   $param = array('valid'=>0, 'feedback' => '','type'=>'hidden');
   stdFormHelper('id', '', $object->id, $mode, 1,$param);
	}else{
		$param = array('valid'=>0, 'feedback' => '','type'=>'hidden');
		stdFormHelper('commercial[]', '', $user->id, $mode, 1,$param);
		$param = array('valid'=>0, 'feedback' => '','type'=>'hidden');
		stdFormHelper('custcats[]', '', $conf->globals->STREAM_ACCOUNT_CAT_SOC, $mode, 1,$param);
	}

   
   // Societe
   $param = array('valid'=>0, 'feedback' => '');
   stdFormHelper('name', $langs->trans('FullName'), $object->name, $mode, 1,$param);
 // Societe
   $param = array('valid'=>0, 'feedback' => '');
   stdFormHelper('name_alias', $langs->trans('Alias'), $object->name_alias, $mode, 1,$param);

// email
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('email' , $langs->trans('Email'), $object->email, $mode, 1, $param);

// User_mobile
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('phone' , $langs->trans('Phone'), $object->phone, $mode, 1, $param);

print '</div></div></div>';

print '</div>';
//var_dump($user);

print '<div class="row">';
	print '<div class="col-md-6 offset-md-3">';
if($mode=='edit'){
	
    print '<button class="btn btn-primary pull-right" type="submit" name="save" value="1" >'.$langs->trans('Save').'</button>';
    
    print '<a class="btn btn-secondary" href="'.$context->getRootUrl('personalinformations').'"  >'.$langs->trans('Cancel').'</a>';
	
}elseif($mode=='create'){
    
    print '<button class="btn btn-primary pull-right" type="submit" name="save" value="1" >'.$langs->trans('Save').'</button>';
    
    print '<a class="btn btn-secondary" href="'.$context->getRootUrl('personalinformations').'"  >'.$langs->trans('Cancel').'</a>';
}
else{
    print '<p>'.$conf->global->EACCESS_RGPD_MSG.'</p>';
}
print '</div></div>';
//print '<!-- /card-body --></div>';
//print '<!-- /card --></div>';
print '</form>';


print '</div></section>';

