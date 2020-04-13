<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_streamaccount.class.php
 * \ingroup streamaccount
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsstreamaccount
 */
include_once DOL_DOCUMENT_ROOT.'/custom/streamaccount/lib/streamaccount.lib.php';
class Actionsstreamaccount
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();
	
	public $tplPath;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->tplPath = realpath ( __DIR__ .'/../tpl');
	}
	
	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		global $langs,$user,$conf,$db;
		$langs->load('streamaccount@streamaccount');

		if (in_array('externalaccesspage', explode(':', $parameters['context'])))
		{
		    $context = Context::getInstance();
			if($context->controller == 'costumerorder')
		    {	
		        $context->title = $langs->trans('ViewClientsOrder');
		        $context->desc = $langs->trans('ViewClientsOrderDesc');
		        $context->menu_active[] = 'costumerorder';
				$context->clientTplPath = $this->tplPath;
				$context->doNotDisplayHeaderBar=1;// hide default header
				
				if($context->action == 'save'){
					set_exception_handler('exception_to_msg');
					dol_include_once('/api/class/api_access.class.php');
					dol_include_once('/api/class/api.class.php');					
					dol_include_once('commande/class/api_orders.class.php');
					DolibarrApiAccess::$user=$user;
					$orders=new Orders();
					//$order->user=$user;
					//$order->db=$db;
					try{
						$orderposted=GETPOST('order','array');
						$lines=GETPOST('lines','array');
						//var_dump($lines);
					$orderid=$orders->post($orderposted);
					if($orderid>0){
						$context->setEventMessages($langs->trans('OrderCreated'));
					}
					$user->socid=null;
					foreach($lines as $l){
						$l['desc']=$l['batch'];						
						$orders->postLine($orderid,$l);
					}
					if($orders->validate($orderid,$user->fk_warehouse)>0)
					header('Location: '.$context->getRootUrl('costumerorder').'&id='.$orderid);
					
					}catch(Exception $e){
						//var_dump($e);
						$context->setEventMessages($langs->trans(str_replace(' ', '',$e->getMessage())), 'errors');
					}				
                    
				}
			}elseif($context->controller == 'clients')
		    {	
		        $context->title = $langs->trans('ViewClients');
		        $context->desc = $langs->trans('ViewClientsDesc');
		        $context->menu_active[] = 'client';
				$context->clientTplPath = $this->tplPath;
				//if($context->action == 'create')
				//$context->doNotDisplayHeaderBar=1;// hide default header
				if($context->action == 'save'){
		            // TODO: need to check all send informations to prevent and verbose errors
					$soc=new Societe($db);
					if($id>0)
		            $soc->fetch($id);
					
					$soc->name				= GETPOST('name', 'alpha');	      
					$soc->entity					= (GETPOSTISSET('entity')?GETPOST('entity', 'int'):$conf->entity);
					$soc->name_alias				= GETPOST('name_alias');	        
					$soc->facebook				= GETPOST('facebook', 'alpha');
					$soc->phone					= GETPOST('phone', 'alpha');
					$soc->email					= trim(GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL));
					$soc->url					= trim(GETPOST('url', 'custom', 0, FILTER_SANITIZE_URL));
					if($soc->id>0){						
						$result=$soc->update($user);
						if($result>0)
						{
							header('Location: '.$context->getRootUrl('clients').'&action=saved&id='.$result);
						}else {						
							$context->action == 'saveError';						
						}
					} else if(($result=$soc->create($user))>0)
		            {
						$soc->client=4;
						//$soc->typent_id=4;
						$soc->commercial_id=$user->id;
						$soc->update($result,$user);
						// Links with users
						$salesreps = GETPOST('commercial', 'array');
						$result = $soc->setSalesRep($salesreps);
						if ($result < 0)
						{
							$error++;
							setEventMessages($soc->error, $soc->errors, 'errors');
						}
						// Customer categories association
						$custcats = GETPOST('custcats', 'array');
						$result = $soc->setCategories($custcats, 'customer');
						if ($result < 0)
						{
							$error++;
							setEventMessages($soc->error, $soc->errors, 'errors');
						}
		                header('Location: '.$context->getRootUrl('clients').'&action=saved&id='.$result);
		            }
		            else {
						
		                $context->action == 'saveError';
		            }
		        }
		    }
           
		}

	}

	/**
	 * Overloading the interface function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActionInterface($parameters, &$object, &$action, $hookmanager)
	{
	    $error = 0; // Error counter
	    global $langs, $db, $conf, $user;

	    if (in_array('streamaccountinterface', explode(':', $parameters['context'])))
	    {
	       

	    }
	}
	/**
	 * Overloading the interface function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function PrintTopMenu($parameters, &$object, &$action, $hookmanager)
	{
		 $error = 0; // Error counter
	    global $langs, $db, $conf, $user;
		//print_r($parameters);
		//print_r(explode(':', $parameters['context']));
	//var_dump( in_array('externalaccesspage', explode(':', $parameters['context'])));

	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
			$Tmenu=&$parameters['Tmenu'];
			$context = Context::getInstance();
			$Tmenu['client'] = array(
				'id' => 'client',
				'rank' => 500,
				'url' =>  $context->getRootUrl('clients'),
				'name' => '<i class="fa fa-address-book"></i> ' . $langs->trans('Clients'),
			);
			$Tmenu['client']['children']['newclient'] = array(
				'id' => 'newclient',
				'separator' => 1,
				'rank' => 100,
				'url' =>  $context->getRootUrl('clients').'&action=create',
				
				'name' => '<i class="fa fa-user-plus"></i> '.$langs->trans('NewClient'),
			);
		}
	}
	/**
	 * Overloading the interface function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function PrintServices($parameters, &$object, &$action, $hookmanager)
	{
	    $error = 0; // Error counter
	    global $langs, $db, $conf, $user;
		//print_r($parameters);
		//print_r(explode(':', $parameters['context']));
		//var_dump( in_array('externalaccesspage', explode(':', $parameters['context'])));

	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
			 $context = Context::getInstance();
	       // if($conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->externalaccess->view_propals)){
        $link = $context->getRootUrl('clients');
        $this->resprints.=getService($langs->trans('Clients'),'fa-address-book',$link); // desc : $langs->trans('QuotationsDesc')
		$link = $context->getRootUrl('costumerorder');
        $this->resprints.=getService($langs->trans('CostumerOrder'),'fa-dropbox',$link); 
		//}
	
	    }
		
	}




	/**
	 * Overloading the PrintPageView function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function PrintPageView($parameters, &$object, &$action, $hookmanager)
	{
	    global $conf, $user, $langs;
	    $error = 0; // Error counter

		if(empty($user->socid)){
			$user->socid = $user->societe_id; // For compatibility support
		}
		//print_r(explode(':', $parameters['context']));
	    if (in_array('externalaccesspage', explode(':', $parameters['context'])))
	    {
	        $context = Context::getInstance();
			//print_r($context->controller);
	         if($context->controller == 'clients')
	        {
				$context->setControllerFound();
				//print_r($user->rights->societe);
	            if(!empty($user->rights->societe->lire))
	            {	if($action=='create')
						$this->print_clientForm($user->id);
					else	
	                $this->print_clientList($user->id);
	            }
	            return 1;
	        }elseif($context->controller == 'costumerorder')
	        {
				$context->setControllerFound();
				//print_r($user->rights->societe);
	            if(!empty($user->rights->societe->lire))
	            {	
	                $this->print_costumerOrder($user->id);
	            }
	            return 1;
	        }
	    }

		return 0;
	}
	public function print_costumerorder($userId = 0)
	{
	    print '<section id="section-invoice"><div class="container">';
	    print_costumerorder($userId);
	    print '</div></section>';
	}
	public function print_clientList($socId = 0)
	{
	    print '<section id="section-invoice"><div class="container">';
	    print_clientTable($socId);
	    print '</div></section>';
	}
	public function print_clientForm($userid){
		print '<section id="section-invoice"><div class="container">';
	    print_clientForm($userid);
	    print '</div></section>';
	}
	public function print_invoiceList($socId = 0)
	{
	    print '<section id="section-invoice"><div class="container">';
	    print_invoiceTable($socId);
	    print '</div></section>';
	}

	public function print_orderList($socId = 0)
	{
	    print '<section id="section-order"><div class="container">';
	    print_orderListTable($socId);
	    print '</div></section>';
	}

	public function print_propalList($socId = 0)
	{
		print '<section id="section-propal"><div class="container">';
		print_propalTable($socId);
		print '</div></section>';
	}

	public function print_expeditionList($socId = 0)
	{
		print '<section id="section-expedition"><div class="container">';
		print_expeditionTable($socId);
		print '</div></section>';
	}

    public function print_ticketList($socId = 0)
    {
        print '<section id="section-ticket"><div class="container">';
        print_ticketTable($socId);
        print '</div></section>';
    }

	public function print_personalinformations()
	{
	    global $langs,$db,$user;
	    $context = Context::getInstance();

	    include $context->tplPath.'/userinfos.tpl.php';
	}

	private function _downloadInvoice(){

	    global $langs, $db, $conf, $user;
	    $filename=false;
	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
		if(!empty($user->societe_id) && $conf->global->EACCESS_ACTIVATE_INVOICES && !empty($user->rights->streamaccount->view_invoices))
	    {
	        dol_include_once('compta/facture/class/facture.class.php');
	        $object = new Facture($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Facture::STATUS_VALIDATED && $object->socid==$user->societe_id)
	            {
			load_last_main_doc($object);
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

	                if(!empty($object->last_main_doc)){
	                    downloadFile($filename, $forceDownload);
	                }
	                else{
	                    print $langs->trans('FileNotExists');
	                }

	            }
	        }
	    }

	}

	private function _downloadPropal(){

	    global $langs, $db, $conf, $user;

	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($user->societe_id) && $conf->global->EACCESS_ACTIVATE_PROPALS && !empty($user->rights->streamaccount->view_propals))
	    {
	        dol_include_once('comm/propal/class/propal.class.php');
	        $object = new Propal($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Propal::STATUS_VALIDATED && $object->socid==$user->societe_id)
	            {
			load_last_main_doc($object);
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

	                if(!empty($object->last_main_doc)){
	                    downloadFile($filename, $forceDownload);
	                }
	                else{
	                    print $langs->trans('FileNotExists');
	                }
	            }
	        }
	    }

	}



	private function _downloadCommande(){

	    global $langs, $db, $conf, $user;

	    $context = Context::getInstance();
	    $id = GETPOST('id','int');
	    $forceDownload = GETPOST('forcedownload','int');
	    if(!empty($user->societe_id) && $conf->global->EACCESS_ACTIVATE_ORDERS && !empty($user->rights->streamaccount->view_orders))
	    {
	        dol_include_once('commande/class/commande.class.php');
	        $object = new Commande($db);
	        if($object->fetch($id)>0)
	        {
	            if($object->statut>=Commande::STATUS_VALIDATED && $object->socid==$user->societe_id)
	            {
			load_last_main_doc($object);
	                $filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

	                downloadFile($filename, $forceDownload);

	                if(!empty($object->last_main_doc)){
	                    downloadFile($filename, $forceDownload);
	                }
	                else{
	                    print $langs->trans('FileNotExists');
	                }
	            }
	        }
	    }

	}


	private function _downloadExpedition(){

		global $langs, $db, $conf, $user;

		$context = Context::getInstance();
		$id = GETPOST('id','int');
		$forceDownload = GETPOST('forcedownload','int');

		if(empty($user->socid)){
			$user->socid = $user->societe_id;
		}

		if(!empty($user->socid) && $conf->global->EACCESS_ACTIVATE_EXPEDITIONS && !empty($user->rights->streamaccount->view_expeditions))
		{
			require_once DOL_DOCUMENT_ROOT . '/expedition/class/expedition.class.php';
			$object = new Expedition($db);
			if($object->fetch($id)>0)
			{
				if($object->statut>=Expedition::STATUS_VALIDATED && $object->socid==$user->socid)
				{
					load_last_main_doc($object);
					$filename = DOL_DATA_ROOT.'/'.$object->last_main_doc;

					downloadFile($filename, $forceDownload);

					if(!empty($object->last_main_doc)){
						downloadFile($filename, $forceDownload);
					}
					else{
						print $langs->trans('FileNotExists');
					}
				}
			}
		}

	}

}
