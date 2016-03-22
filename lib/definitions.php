<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2013 LMS Developers
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

// that definitions should be included before LMS.class.php but after Smarty

// customers and contractor type
define('CTYPES_PRIVATE',0);
define('CTYPES_COMPANY',1);
define('CTYPES_CONTRACTOR',2);

$CTYPES = array(
    CTYPES_PRIVATE	=> trans('private person'),
    CTYPES_COMPANY	=> trans('legal entity'),
    CTYPES_CONTRACTOR	=> trans('contractor'),
);

// customer statuses
define('CSTATUS_INTERESTED', 1);
define('CSTATUS_WAITING', 2);
define('CSTATUS_CONNECTED', 3);
define('CSTATUS_DISCONNECTED', 4);
define('CSTATUS_DEBT_COLLECTION', 5);
define('CSTATUS_LAST', CSTATUS_DEBT_COLLECTION);

$CSTATUSES = array(
	CSTATUS_CONNECTED => array(
		'singularlabel' => trans('connected<!singular>'),
		'plurallabel' => trans('connected<!plural>'),
		'summarylabel' => trans('Connected:'),
		'img' => 'customer.gif',
		'alias' => 'connected'
	),
	CSTATUS_WAITING => array(
		'singularlabel' => trans('waiting'),
		'plurallabel' => trans('waiting'),
		'summarylabel' => trans('Waiting:'),
		'img' => 'wait.gif',
		'alias' => 'awaiting'
	),
	CSTATUS_INTERESTED => array(
		'singularlabel' => trans('interested<!singular>'),
		'plurallabel' => trans('interested<!plural>'),
		'summarylabel' => trans('Interested:'),
		'img' => 'unk.gif',
		'alias' => 'interested'
	),
	CSTATUS_DISCONNECTED => array(
		'singularlabel' => trans('disconnected<!singular>'),
		'plurallabel' => trans('disconnected<!plural>'),
		'summarylabel' => trans('Disconnected:<!summary>'),
		'img' => 'node_off.gif',
		'alias' => 'disconnected'
	),
	CSTATUS_DEBT_COLLECTION => array(
		'singularlabel' => trans('debt collection'),
		'plurallabel' => trans('debt collection'),
		'summarylabel' => trans('Debt Collection:<!summary>'),
		'img' => 'money.gif',
		'alias' => 'debtcollection'
	),
);

// Config types
define('CONFIG_TYPE_AUTO', 0);
define('CONFIG_TYPE_BOOLEAN', 1);
define('CONFIG_TYPE_POSITIVE_INTEGER', 2);
define('CONFIG_TYPE_EMAIL', 3);
define('CONFIG_TYPE_RELOADTYPE', 4);
define('CONFIG_TYPE_DOCTYPE', 5);
define('CONFIG_TYPE_MARGINS', 6);
define('CONFIG_TYPE_NONE', 7);
define('CONFIG_TYPE_RICHTEXT', 8);
define('CONFIG_TYPE_MAIL_BACKEND', 9);
define('CONFIG_TYPE_MAIL_SECURE', 10);
define('CONFIG_TYPE_DATE_FORMAT', 11);

$CONFIG_TYPES = array(
	CONFIG_TYPE_AUTO => trans('- auto -'),
	CONFIG_TYPE_NONE => trans('none'),
	CONFIG_TYPE_BOOLEAN => trans('boolean'),
	CONFIG_TYPE_POSITIVE_INTEGER => trans('integer greater than 0'),
	CONFIG_TYPE_EMAIL => trans('email'),
	CONFIG_TYPE_RELOADTYPE => trans('reload type'),
	CONFIG_TYPE_DOCTYPE => trans('document type'),
	CONFIG_TYPE_MARGINS => trans('margins'),
	CONFIG_TYPE_RICHTEXT => trans('visual editor'),
	CONFIG_TYPE_MAIL_BACKEND => trans('mail backend'),
	CONFIG_TYPE_MAIL_SECURE => trans('mail security protocol'),
	CONFIG_TYPE_DATE_FORMAT => trans('date format'),
);

// Helpdesk ticket status
define('RT_NEW', 0);
define('RT_OPEN', 1);
define('RT_RESOLVED', 2);
define('RT_DEAD', 3);

$RT_STATES = array(
    RT_NEW      => trans('new'),
    RT_OPEN     => trans('opened'),
    RT_RESOLVED => trans('resolved'),
    RT_DEAD     => trans('dead')
);

// Helpdesk cause type
define('RT_CAUSE_OTHER', 0);
define('RT_CAUSE_CUSTOMER', 1);
define('RT_CAUSE_COMPANY', 2);

$RT_CAUSE = array(
    RT_CAUSE_OTHER => trans("unknown/other"),
    RT_CAUSE_CUSTOMER => trans("customer's side"),
    RT_CAUSE_COMPANY => trans("company's side")
);

// Helpdesk note type
define('RTNOTE', 1);
define('RTNOTE_OWNER_CHANGE', 2);
define('RTNOTE_QUEUE_CHANGE', 4);
define('RTNOTE_STATE_CHANGE', 8);
define('RTNOTE_CAUSE_CHANGE', 16);
define('RTNOTE_CUSTOMER_CHANGE', 32);
define('RTNOTE_SUBJECT_CHANGE', 64);

// Messages status and type
define('MSG_NEW', 1);
define('MSG_SENT', 2);
define('MSG_ERROR', 3);
define('MSG_DRAFT', 4);
define('MSG_DELIVERED', 5);

define('MSG_MAIL', 1);
define('MSG_SMS', 2);
define('MSG_ANYSMS', 3);
define('MSG_WWW', 4);
define('MSG_USERPANEL', 5);
define('MSG_USERPANEL_URGENT', 6);

// Template types
define('TMPL_WARNING', 1);
define('TMPL_MAIL', 2);
define('TMPL_SMS', 3);
define('TMPL_WWW', 4);
define('TMPL_USERPANEL', 5);
define('TMPL_USERPANEL_URGENT', 6);

// Account types
define('ACCOUNT_SHELL', 1);
define('ACCOUNT_MAIL', 2);
define('ACCOUNT_WWW', 4);
define('ACCOUNT_FTP', 8);
define('ACCOUNT_SQL', 16);

// Document types
define('DOC_INVOICE', 1);
define('DOC_RECEIPT', 2);
define('DOC_CNOTE', 3);
//define('DOC_CMEMO', 4);
define('DOC_DNOTE', 5);
define('DOC_INVOICE_PRO',6);
define('DOC_INVOICE_PURCHASE',7);

define('DOC_CONTRACT', -1);
define('DOC_ANNEX', -2);
define('DOC_PROTOCOL', -3);
define('DOC_ORDER', -4);
define('DOC_SHEET', -5);
define('DOC_OTHER', -128);
define('DOC_BILLING',-10);

$DOCTYPES = array(
    DOC_BILLING         =>      trans('billing'),
    DOC_INVOICE         =>      trans('invoice'),
    DOC_INVOICE_PRO     =>      trans('pro-forma invoice'),
    DOC_INVOICE_PURCHASE =>     trans('purchase invoice'),
    DOC_RECEIPT         =>      trans('cash receipt'),
    DOC_CNOTE       =>  trans('credit note'), // faktura korygujaca
//    DOC_CMEMO     =>  trans('credit memo'), // nota korygujaca
    DOC_DNOTE       =>  trans('debit note'), // nota obciazeniowa/debetowa/odsetkowa
    DOC_CONTRACT        =>      trans('contract'),
    DOC_ANNEX       =>  trans('annex'),
    DOC_PROTOCOL        =>      trans('protocol'),
    DOC_ORDER       =>  trans('order'),
    DOC_SHEET       =>  trans('customer sheet'), // karta klienta
    -6  =>      trans('contract termination'),
    -7  =>      trans('payments book'), // ksiazeczka oplat
    -8  =>      trans('payment summons'), // wezwanie do zapłaty
    -9  =>      trans('payment pre-summons'), // przedsądowe wezw. do zapłaty
    DOC_OTHER       =>  trans('other'),
);

// Guarantee periods
$GUARANTEEPERIODS = array(
    -1 => trans('lifetime'),
    0  => trans('none'),
    12 => trans('$a months', 12),
    24 => trans('24 months', 24),
    36 => trans('$a months', 36),
    48 => trans('$a months', 48),
    60 => trans('$a months', 60)
);

// Internet Messengers
define('IM_GG', 0);
define('IM_YAHOO', 1);
define('IM_SKYPE', 2);

$MESSENGERS = array(
    IM_GG    => trans('Gadu-Gadu'),
    IM_YAHOO => trans('Yahoo'),
    IM_SKYPE => trans('Skype'),
);

define('DISPOSABLE', 0);
define('DAILY', 1);
define('WEEKLY', 2);
define('MONTHLY', 3);
define('QUARTERLY', 4);
define('YEARLY', 5);
define('CONTINUOUS', 6);
define('HALFYEARLY', 7);

// Accounting periods
$PERIODS = array(
    YEARLY	=>	trans('yearly'),
    HALFYEARLY  =>      trans('half-yearly'),
    QUARTERLY	=>	trans('quarterly'),
    MONTHLY	=>	trans('monthly'),
//    WEEKLY	=>	trans('weekly'),
//    DAILY	=>	trans('daily'),
    DISPOSABLE	=>	trans('disposable')
);

// Numbering periods
$NUM_PERIODS = array(
    CONTINUOUS	=>	trans('continuously'),
    YEARLY	=>	trans('yearly'),
    HALFYEARLY	=>	trans('half-yearly'),
    QUARTERLY	=>	trans('quarterly'),
    MONTHLY	=>	trans('monthly'),
//    WEEKLY	=>	trans('weekly'),
    DAILY	=>	trans('daily'),
);

// Tariff types
define('TARIFF_INTERNET', 1);
define('TARIFF_HOSTING', 2);
define('TARIFF_SERVICE', 3);
define('TARIFF_PHONE', 4);
define('TARIFF_TV', 5);
define('TARIFF_OTHER', -1);

$TARIFFTYPES = array(
	TARIFF_INTERNET	=> ConfigHelper::getConfig('tarifftypes.internet', trans('internet')),
	TARIFF_HOSTING	=> ConfigHelper::getConfig('tarifftypes.hosting', trans('hosting')),
	TARIFF_SERVICE	=> ConfigHelper::getConfig('tarifftypes.service', trans('service')),
	TARIFF_PHONE	=> ConfigHelper::getConfig('tarifftypes.phone', trans('phone')),
	TARIFF_TV	=> ConfigHelper::getConfig('tarifftypes.tv', trans('tv')),
	TARIFF_OTHER	=> ConfigHelper::getConfig('tarifftypes.other', trans('other')),
);

$PAYTYPES = array(
    1   => trans('cash'),
    2   => trans('transfer'),
    3   => trans('transfer/cash'),
    4   => trans('card'),
    5   => trans('compensation'),
    6   => trans('barter'),
    7   => trans('contract'),
    8   => trans('paid'),
);

// Contact types
define('CONTACT_MOBILE', 1);
define('CONTACT_FAX', 2);
define('CONTACT_LANDLINE', 4);
define('CONTACT_EMAIL', 8);
define('CONTACT_INVOICES', 16);
define('CONTACT_NOTIFICATIONS', 32);
define('CONTACT_BANKACCOUNT', 64);
define('CONTACT_DISABLED', 16384);

$CONTACTTYPES = array(
    CONTACT_MOBILE          =>	trans('mobile'),
    CONTACT_FAX             =>	trans('fax'),
    CONTACT_INVOICES        =>	trans('invoices'),
    CONTACT_DISABLED        =>	trans('disabled'),
    CONTACT_NOTIFICATIONS   =>	trans('notifications'),
);

define('DISCOUNT_PERCENTAGE', 1);
define('DISCOUNT_AMOUNT', 2);

$DISCOUNTTYPES = array(
	DISCOUNT_PERCENTAGE	=> '%',
	DISCOUNT_AMOUNT		=> trans('amount'),
);

define('DAY_MONDAY', 0);
define('DAY_TUESDAY', 1);
define('DAY_THURSDAY', 2);
define('DAY_WEDNESDAY', 3);
define('DAY_FRIDAY', 4);
define('DAY_SATURDAY', 5);
define('DAY_SUNDAY', 6);

$DAYS = array(
	DAY_MONDAY	=> trans('Mon'),
	DAY_TUESDAY	=> trans('Tue'),
	DAY_THURSDAY	=> trans('Thu'),
	DAY_WEDNESDAY	=> trans('Wed'),
	DAY_FRIDAY	=> trans('Fri'),
	DAY_SATURDAY	=> trans('Sat'),
	DAY_SUNDAY	=> trans('Sun'),
);

define('MEDIUM_COPPER',0);
define('MEDIUM_WIRELESS',1);
define('MEDIUM_FIBER',2);

$LINKTYPES = array(
	MEDIUM_COPPER	=> trans('copper'),
	MEDIUM_WIRELESS	=> trans('wireless'),
	MEDIUM_FIBER	=> trans('fiber'),
);

$NETTECHNOLOGIES = array(
//COPPER
	1 => array('name'=>'ADSL','medium'=>'1,2,3,4,5,6,7,8','speed'=>0,'connector'=>6),
	2 => array('name'=>'ADSL2','medium'=>'1,2,3,4,5,6,7,8','speed'=>1,'connector'=>6),
	3 => array('name'=>'ADSL2+','medium'=>'1,2,3,4,5,6,7,8','speed'=>2,'connector'=>6),
	4  => array('name'=>'VDSL','medium'=>'1,2,3,4,5,6,7,8','speed'=>3,'connector'=>3),
	5  => array('name'=>'VDSL2','medium'=>'1,2,3,4,5,6,7,8','speed'=>4,'connector'=>3),
	10 => array('name'=>'HDSL','medium'=>'1,2,3,4,5,6,7,8','speed'=>5,'connector'=>3),
	11 => array('name'=>'PDH','medium'=>'1,2,3,4,5,6,7,8','speed'=>5,'connector'=>3),
	12 => array('name'=>'POTS/ISDN','medium'=>'1,2,3,4,5,6,7,8','speed'=>6,'connector'=>6),
	6  => array('name'=>'10 Mb/s Ethernet','medium'=>'5,6,7,8','speed'=>7,'connector'=>2),
	7  => array('name'=>'100 Mb/s Ethernet','medium'=>'5,6,7,8','speed'=>8,'connector'=>2),
	8  => array('name'=>'1 Gigabit Ethernet','medium'=>'6,7,8','speed'=>9,'connector'=>1),
	9  => array('name'=>'10 Gigabit Ethernet','medium'=>'7,8','speed'=>10,'connector'=>1),
	50 => array('name'=>'(EURO)DOCSIS 1.x)','medium'=>'51','speed'=>11,'connector'=>8),
	51 => array('name'=>'(EURO)DOCSIS 2.x)','medium'=>'51','speed'=>12,'connector'=>8),
	52 => array('name'=>'(EURO)DOCSIS 3.x)','medium'=>'51','speed'=>13,'connector'=>8),
//RADIO

//FIBER
	200 => array('name'=>'CWDM','medium'=>'201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218','speed'=>10,'connector'=>'201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243'),
#	1 => array('name'=>'','medium'=>MEDIUM_COPPER,'speed'=>'','connector'=>'1'),
	
);

$NETSPEEDS = array(
	0 => array(8,1),
	1 => array(12,1),
	2 => array(24,3.5),
	3 => array(52,16),
	4 => array(200,200),
	5 => array(2,2),
	6 => array(1,1),
	7 => array(10,10),
	8 => array(100,100),
	9 => array(1000,1000),
	10 => array(10000,10000),
	11 => array(50,10),
	12 => array(50,30),
	13 => array(1600,216),
);

$LINKTECHNOLOGIES = array(
	MEDIUM_COPPER => array(
		1 => 'ADSL',
		2 => 'ADSL2',
		3 => 'ADSL2+',
		4 => 'VDSL',
		5 => 'VDSL2',
		10 => 'HDSL',
		11 => 'PDH',
		12 => 'POTS/ISDN',
		6 => '10 Mb/s Ethernet',
		7 => '100 Mb/s Fast Ethernet',
		8 => '1 Gigabit Ethernet',
		9 => '10 Gigabit Ethernet',
		50 => '(EURO)DOCSIS 1.x',
		51 => '(EURO)DOCSIS 2.x',
		52 => '(EURO)DOCSIS 3.x',
	),
	MEDIUM_WIRELESS => array(
		100 => 'WiFi - 2,4 GHz',
		101 => 'WiFi - 5 GHz',
		102 => 'WiMAX',
		103 => 'LMDS',
		104 => 'radiolinia',
		105 => 'CDMA',
		106 => 'GPRS',
		107 => 'EDGE',
		108 => 'HSPA',
		109 => 'HSPA+',
		110 => 'DC-HSPA+',
		111 => 'MC-HSPA+',
		112 => 'LTE',
		113 => 'UMTS',
		114 => 'DMS',
	),
	MEDIUM_FIBER => array(
		200 => 'CWDM',
		201 => 'DWDM',
		202 => 'SDH',
		203 => '10 Mb/s Ethernet',
		204 => '100 Mb/s Fast Ethernet',
		205 => '1 Gigabit Ethernet',
		206 => '10 Gigabit Ethernet',
		210 => '40 Gigabit Ethernet',
		207 => '100 Gigabit Ethernet',
		208 => 'EPON',
		209 => 'GPON',
		211 => 'ATM',
		212 => 'PDH',
		250 => '(EURO)DOCSIS 1.x',
		251 => '(EURO)DOCSIS 2.x',
		252 => '(EURO)DOCSIS 3.x',
	),
);

$LINKSPEEDS = array(
	10000		=> trans('10Mbit/s'),
	25000		=> trans('25Mbit/s'),
	54000		=> trans('54Mbit/s'),
	100000		=> trans('100Mbit/s'),
	200000		=> trans('200Mbit/s'),
	300000		=> trans('300Mbit/s'),
	1000000		=> trans('1Gbit/s'),
	10000000	=> trans('10Gbit/s'),
);

$BOROUGHTYPES = array(
	1 => trans('municipal commune'),
	2 => trans('rural commune'),
	3 => trans('municipal-rural commune'),
	4 => trans('city in the municipal-rural commune'),
	5 => trans('rural area to municipal-rural commune'),
	8 => trans('estate in Warsaw-Centre commune'),
	9 => trans('estate'),
);

$PASSWDEXPIRATIONS = array(
	0	=> trans('never expires'),
	7	=> trans('week'),
	14	=> trans('2 weeks'),
	21	=> trans('21 days'),
	31	=> trans('month'),
	62	=> trans('2 months'),
	93	=> trans('quarter'),
	183	=> trans('half year'),
	365	=> trans('year')
);

$NETELEMENTSTATUSES = array(
	0	=> trans('existing'),
	1	=> trans('under construction'),
	2	=> trans('planned')
);

$NETELEMENTTYPES = array(
	0	=> trans('active'),
	1	=> trans('passive'),
	2	=> trans('cable'),
	3	=> trans('splitter'),
	4	=> trans('multiplexer'),
	99	=> trans('clients computer')
);


$NETPORTTYPES = array( 
	0	=> trans('copper'),
	1	=> trans('fiber'),
	2	=> trans('wireless'),
	3	=> trans('tray'),
	4	=> trans('in'),
	5	=> trans('out'),
	6	=> trans('SFP'),
	7	=> trans('SFP+')
);

$NETCONNECTORS = array(
	// COPPER
	1 => '8P8C',
	2 => '8P4C',
	3 => '8P2C',
	4 => '6P4C',
	5 => '4P4C',
	6 => '4P2C',
	7 => 'BNC',
	8 => 'F',
	// RADIO

	// FIBER
	200 => 'SC/FLAT',
	201 => 'SC/PC',
	202 => 'SC/UPC',
	203 => 'SC/APC',
	210 => 'LC/FLAT',
	211 => 'LC/PC',
	212 => 'LC/UPC',
	213 => 'LC/APC',
	220 => 'FC/FLAT',
	221 => 'FC/PC',
	222 => 'FC/UPC',
	223 => 'FC/APC',
	230 => 'ST/FLAT',
	231 => 'ST/PC',
	232 => 'ST/UPC',
	233 => 'ST/APC',
	240 => 'E2000/FLAT',
	241 => 'E2000/PC',
	242 => 'E2000/UPC',
	243 => 'E2000/APC'
);

$NETCABLETYPES = array (
	MEDIUM_COPPER => array (
		1 => trans('twisted-pair'),
		2 => trans('coaxial'),
	),
	MEDIUM_FIBER => array (
		1 => 'jednotubowy',
		2 => 'wielotubowy',
		3 => 'łatwy dostęp',
		4 => 'samonośny',
		5 => 'doziemny',
	),
);

$NETWIRETYPES = array (
	// COPPER
        1 => trans('UTP cat. 1'),
        2 => trans('UTP cat. 2'),
        3 => trans('UTP cat. 3'),
        4 => trans('UTP cat. 4'),
        5 => trans('UTP cat. 5'),
        6 => trans('UTP cat. 5a'),
        7 => trans('UTP cat. 7'),
        8 => trans('UTP cat. 7a'),
	50 => trans('thicknet'),
	51 => trans('thinnet'),
	// RADIO
        101 => trans('802.11b/g'),
        102 => trans('802.11a/an/ac'),
        103 => trans('Microwave'),
        104 => trans('WiMax'),
        105 => trans('GSM'),
	// FIBE
	200 => trans('single-mode G.652.A'),
	202 => trans('single-mode G.652.B'),
	203 => trans('single-mode G.652.C'),
	204 => trans('single-mode G.652.D'),
	205 => trans('single-mode G.653.A'),
	205 => trans('single-mode G.653.B'),
	206 => trans('single-mode G.654.A'),
	207 => trans('single-mode G.654.B'),
	208 => trans('single-mode G.654.C'),
	209 => trans('single-mode G.655.A'),
	210 => trans('single-mode G.655.B'),
	211 => trans('single-mode G.655.C'),
	212 => trans('single-mode G.655.D'),
	213 => trans('single-mode G.655.E'),
	214 => trans('single-mode G.656'),
	215 => trans('single-mode G.657.A1'),
	216 => trans('single-mode G.657.A2'),
	217 => trans('single-mode G.657.B2'),
	218 => trans('single-mode G.657.B3'),
	250 => trans('multi-mode FFDI'),
	251 => trans('multi-mode OM1'),
	252 => trans('multi-mode OM2'),
	253 => trans('multi-mode OM3'),
	254 => trans('multi-mode OM4'),
);		

$COPERCOLORSSCHEMAS = array (
	1	=> array(
			label => 'standard',
			colors => array (
				1  => trans('white').'/'.trans('blue'),
				2  => trans('white').'/'.trans('orange'),
				3  => trans('white').'/'.trans('green'),
				4  => trans('white').'/'.trans('brown'),
				5  => trans('white').'/'.trans('slate'),
                                6  => trans('red').'/'.trans('blue'),
                                7  => trans('red').'/'.trans('orange'),
                                8  => trans('red').'/'.trans('green'),
                                9  => trans('red').'/'.trans('brown'),
                                10 => trans('red').'/'.trans('slate'),
                                11 => trans('black').'/'.trans('blue'),
                                12 => trans('black').'/'.trans('orange'),
                                13 => trans('black').'/'.trans('green'),
                                14 => trans('black').'/'.trans('brown'),
                                15 => trans('black').'/'.trans('slate'),
                                16 => trans('yellow').'/'.trans('blue'),
                                17 => trans('yellow').'/'.trans('orange'),
                                18 => trans('yellow').'/'.trans('green'),
                                19 => trans('yellow').'/'.trans('brown'),
                                20 => trans('yellow').'/'.trans('slate'),
                                21 => trans('violet').'/'.trans('blue'),
                                22 => trans('violet').'/'.trans('orange'),
                                23 => trans('violet').'/'.trans('green'),
                                24 => trans('violet').'/'.trans('brown'),
                                25 => trans('violet').'/'.trans('slate'),
			    )
	    )
);

define('RED',1);
define('GREEN',2);
define('BLUE',3);
define('YELLOW',4);
define('WHITE',5);
define('SLATE',6);
define('BROWN',7);
define('VIOLET',8);
define('AQUA',9);
define('BLACK',10);
define('ORANGE',11);
define('PINK',12);

$FIBEROPTICCOLORSCHEMAS = array(
	1	=> array(
			label => 'IEC-60304',
			fibers => array(
				1  => RED,
				2  => GREEN,
				3  => BLUE,
				4  => YELLOW,
				5  => WHITE,
				6  => SLATE,
				7  => BROWN,
				8  => VIOLET,
				9  => AQUA,
				10 => BLACK,
				11 => ORANGE,
				12 => PINK
			),
                        tubes => array(
                                1  => RED,
                                2  => GREEN,
                                3  => BLUE,
                                4  => YELLOW,
                                5  => WHITE,
                                6  => SLATE,
                                7  => BROWN,
                                8  => VIOLET,
                                9  => AQUA,
                                10 => BLACK,
                                11 => ORANGE,
                                12 => PINK
                        ),

		),		
	2	=> array(
			label => 'Telefonika',
			fibers => array(
                                1  => RED,
                                2  => GREEN,
                                3  => BLUE,
                                4  => WHITE,
                                5  => VIOLET,
                                6  => ORANGE,
                                7  => SLATE,
                                8  => YELLOW,
                                9  => BROWN,
                                10 => PINK,
                                11 => BLACK,
                                12 => AQUA
			),
                        tubes => array(
                                1  => RED,
                                2  => GREEN,
                                3  => WHITE,
                                4  => WHITE,
                                5  => WHITE,
                                6  => WHITE,
                                7  => WHITE,
                                8  => WHITE,
                                9  => WHITE,
                                10 => WHITE,
                                11 => WHITE,
                                12 => WHITE
                        ),

		),
);


$NETNODETYPES = array(
	0	=> 'budynek biurowy',
	2	=> 'budynek mieszkalny',
	1	=> 'budynek przemysłowy',
	11	=> 'budynek usługowy',
	12	=> 'budynek użyteczności publicznej',
	3	=> 'obiekt sakralny',
	13	=> 'obiekt sieci elektroenergetycznej',
	5	=> 'wieża',
	4	=> 'maszt',
	10	=> 'komin',
	6	=> 'kontener',
	7	=> 'szafa uliczna',
	14	=> 'słup',
	8	=> 'skrzynka',
	9	=> 'studnia kablowa',
);

$NETNODEOWNERSHIPS = array(
	0	=> 'węzeł własny',
	1	=> 'węzeł współdzielony z innym podmiotem',
	2	=> 'węzeł obcy',
	3	=> 'węzeł kliencki',
);

$USERPANEL_ID_TYPES = array(
	1	=> array(
		'label' => trans('Customer ID:'),
		'selection' => trans('Customer ID and PIN'),
	),
	2	=> array(
		'label' => trans('Phone number:'),
		'selection' => trans('Phone number and PIN'),
	),
	3	=> array(
		'label' => trans('Document number:'),
		'selection' => trans('Document number and PIN'),
	),
	4	=> array(
		'label' => trans('Customer e-mail:'),
		'selection' => trans('Customer e-mail and PIN'),
	),
);

define('EVENT_OTHER', 1);
define('EVENT_NETWORK', 2);
define('EVENT_SERVICE', 3);
define('EVENT_INSTALLATION', 4);
define('EVENT_MEETING', 5);

$EVENTTYPES = array(
	EVENT_SERVICE      => trans('service<!event>'),
	EVENT_INSTALLATION => trans('installation'),
	EVENT_NETWORK      => trans('network'),
	EVENT_MEETING      => trans('meeting'),
	EVENT_OTHER        => trans('other')
);

define('SESSIONTYPE_PPPOE', 1);
define('SESSIONTYPE_DHCP', 2);
define('SESSIONTYPE_EAP', 4);
define('SESSIONTYPE_WIFI', 8);
define('SESSIONTYPE_VOIP', 16);

$SESSIONTYPES = array(
	SESSIONTYPE_PPPOE => array(
		'label' => trans('PPPoE Client'),
		'tip' => 'Enable/disable PPPoE Server Client'
	),
	SESSIONTYPE_DHCP => array(
		'label' => trans('DHCP Client'),
		'tip' => 'Enable/disable DHCP Server Client'
	),
	SESSIONTYPE_EAP => array(
		'label' => trans('EAP Client'),
		'tip' => 'Enable/disable EAP Server Client'
	),
	SESSIONTYPE_WIFI => array(
		'label' => trans('WiFi AP Client'),
		'tip' => 'Enable/disable WiFi AP Client access'
	),
	SESSIONTYPE_VOIP => array(
		'label' => trans('VoIP Gateway'),
		'tip' => 'Enable/disable VoIP Gateway access'
	),
);

if(isset($SMARTY))
{
	$SMARTY->assign('_CTYPES',$CTYPES);
	$SMARTY->assign('_CSTATUSES', $CSTATUSES);
	$SMARTY->assign('_DOCTYPES', $DOCTYPES);
	$SMARTY->assign('_PERIODS', $PERIODS);
	$SMARTY->assign('_GUARANTEEPERIODS', $GUARANTEEPERIODS);
	$SMARTY->assign('_NUM_PERIODS', $NUM_PERIODS);
	$SMARTY->assign('_RT_STATES', $RT_STATES);
	$SMARTY->assign('_CONFIG_TYPES', $CONFIG_TYPES);
	$SMARTY->assign('_MESSENGERS', $MESSENGERS);
	$SMARTY->assign('_TARIFFTYPES', $TARIFFTYPES);
	$SMARTY->assign('_PAYTYPES', $PAYTYPES);
	$SMARTY->assign('_CONTACTTYPES', $CONTACTTYPES);
	$SMARTY->assign('_DISCOUNTTYPES', $DISCOUNTTYPES);
	$SMARTY->assign('_DAYS', $DAYS);
	$SMARTY->assign('_LINKTYPES', $LINKTYPES);
	$SMARTY->assign('_LINKTECHNOLOGIES', $LINKTECHNOLOGIES);
	$SMARTY->assign('_LINKSPEEDS', $LINKSPEEDS);
	$SMARTY->assign('_BOROUGHTYPES', $BOROUGHTYPES);
	$SMARTY->assign('_PASSWDEXPIRATIONS', $PASSWDEXPIRATIONS);
	$SMARTY->assign('_NETELEMENTSTATUSES', $NETELEMENTSTATUSES);
	$SMARTY->assign('_NETELEMENTTYPES', $NETELEMENTTYPES);
	$SMARTY->assign('_NETPORTTYPES', $NETPORTTYPES);
	$SMARTY->assign('_NETCONNECTORS', $NETCONNECTORS);
	$SMARTY->assign('_NETCABLETYPES', $NETCABLETYPES);
	$SMARTY->assign('_NETWIRETYPES', $NETWIRETYPES);
	$SMARTY->assign('_FIBEROPTICCOLORSCHEMAS', $FIBEROPTICCOLORSCHEMAS);
	$SMARTY->assign('_NETNODETYPES', $NETNODETYPES);
	$SMARTY->assign('_NETNODEOWNERSHIPS', $NETNODEOWNERSHIPS);
	$SMARTY->assign('_USERPANEL_ID_TYPES', $USERPANEL_ID_TYPES);
	$SMARTY->assign('_EVENTTYPES', $EVENTTYPES);
	$SMARTY->assign('_SESSIONTYPES', $SESSIONTYPES);
}

define('DEFAULT_NUMBER_TEMPLATE', '%N/LMS/%Y');

// Investment project types
define('INV_PROJECT_REGULAR', 0);
define('INV_PROJECT_SYSTEM', 1)

?>
