<?php
/* Copyright (C) 2020 Andersson Paz <npander@hotmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/product/streamaccount/class/costumerorder.class.php
 *	\ingroup    streamaccount
 *	\brief      File of class to commercials manage costumers orders 
 */

use Luracast\Restler\RestException;

require_once DOL_DOCUMENT_ROOT.'/commande/class/api_order.class.php';

/**
 *	Class to commercials manage costumers orders 
 */
class CostumerOrder extends Orders
{
    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object
     */
    static $FIELDS = array(
        'socid'
    );

    /**
     * @var Commande $commande {@type Commande}
     */
    public $commande;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db, $conf;
        $this->db = $db;
        $this->commande = new OrderBatch($this->db);
    }
    
    /**
     * Validate an order
     *
	 * If you get a bad value for param notrigger check, provide this in body
     * {
     *   "idwarehouse": 0,
     *   "notrigger": 0
     * }
     *
     * @param   int $id             Order ID
     * @param   int $idwarehouse    Warehouse ID
     * @param   int $notrigger      1=Does not execute triggers, 0= execute triggers
     *
     * @url POST    {id}/validate
     *
	 * @throws 304
     * @throws 401
     * @throws 404
     * @throws 500
     *
     * @return  array
     */
    public function validate($id, $idwarehouse = 0, $notrigger = 0)
    {
        if(! DolibarrApiAccess::$user->rights->commande->creer) {
			throw new RestException(401);
		}
        $result = $this->commande->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'Order not found');
        }

		if( ! DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->commande->valid(DolibarrApiAccess::$user, $idwarehouse, $notrigger);
		if ($result == 0) {
		    throw new RestException(304, 'Error nothing done. May be object is already validated');
		}
		if ($result < 0) {
		    throw new RestException(500, 'Error when validating Order: '.$this->commande->error);
		}
        $result = $this->commande->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'Order not found');
        }

        if( ! DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        $this->commande->fetchObjectLinked();

        return $this->_cleanObjectDatas($this->commande);
    }
}