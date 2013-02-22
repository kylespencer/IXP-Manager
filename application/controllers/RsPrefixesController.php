<?php

/*
 * Copyright (C) 2009-2012 Internet Neutral Exchange Association Limited.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */


/**
 * Controller: List prefixes accepted (or otherwise) by the route servers
 *
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @category   IXP
 * @package    IXP_Controller
 * @copyright  Copyright (c) 2009 - 2013, Internet Neutral Exchange Association Ltd
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */
class RsPrefixesController extends IXP_Controller_AuthRequiredAction
{
    public function preDispatch()
    {
        if( $this->getUser()->getPrivs() != \Entities\User::AUTH_SUPERUSER )
            $this->redirectAndEnsureDie( 'error/insufficient-permissions' );
    }
    
    public function indexAction()
    {
        $this->view->types = \Entities\RSPrefix::$SUMMARY_TYPES_FNS;
        $this->view->cust_prefixes = $this->getD2EM()->getRepository( '\\Entities\\RSPrefix' )->aggregateRouteSummaries();
    }
    
    public function listAction()
    {
        if( !( $cust = $this->getD2EM()->getRepository( '\\Entities\\Customer' )->find( $this->getParam( 'custid', 0 ) ) ) )
        {
            $this->addMessage( 'Invalid customer ID in request', OSS_Message::ERROR );
            return $this->forward( 'index' );
        }
        
        $protocol = $this->getParam( 'protocol', null );
        if( !in_array( $protocol, [ 4, 6 ] ) )
            $protocol = null;

        $this->view->tab      = $this->getParam( 'tab', false );
        $this->view->cust     = $cust;
        $this->view->protocol = $protocol;
        $this->view->types    = array_keys( \Entities\RSPrefix::$ROUTES_TYPES_FNS );
        
        $this->view->aggRoutes = $this->getD2EM()->getRepository( '\\Entities\\RSPrefix' )->aggregateRoutes( $cust->getId(), $protocol );
    }
}

