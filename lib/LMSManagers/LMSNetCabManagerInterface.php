<?php

/*
 *  LMS version 1.11-git
 *
 *  Copyright (C) 2001-2013 LMS Cabelopers
 *
 *  Please, see the doc/AUTHORS for more information about authors!
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 *  USA.
 *
 *  $Id$
 */

/**
 * LMSNetCabManagerInterface
 * 
 * @author Jaroslaw Dziubek <jaroslaw.dziubek@perfect.net.pl>
 */
interface LMSNetCabManagerInterface
{
    public function NetCabUpdate($data);
    
    public function NetCabAdd($data);
    
    public function DeleteNetCab($id);
    
    public function GetNetCab($id);
    
    public function NetCabExists($id);

    public function GetNetCabList($order = 'name,asc', $search = array());

    public function GetNetCabInObj($id);

    public function GetNetCabUnconnected($id);

    public function AddCabToObj($objectid,$cableid);

}
