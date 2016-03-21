define('MEDIUM_COPPER',0);
define('MEDIUM_WIRELESS',1);
define('MEDIUM_FIBER',2);

$MEDIUMTYPES = array(
        MEDIUM_COPPER   => trans('copper'),
        MEDIUM_WIRELESS => trans('wireless'),
        MEDIUM_FIBER    => trans('fiber'),
);

$CONNECTORS=array('SC/PC',
		    'SC/APC',
		    'LC' );

$_SPEEDS=array('1/1', '2/2', '5/5', '10/10', '25/25', '54/54', '100/100', '1250/1250');

$_NETTECHNOLOGIES=array(
    array(	'name'=>'...', 
		'medium'=> MEDIUM_xxx,
		'speed_list'=>'0,1,2,4', 
		'connector_list'=>'0,1'
    ),

);
