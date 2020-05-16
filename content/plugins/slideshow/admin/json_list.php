<?php
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/bc-admin/include/inc_load.php');
require_once(rel_admin_path.'/control_login.php');
require_once('general_tags.php');
require_once(rel_client_path.'/include/lib/Zebra_Mptt.php');
$mptt = new Zebra_Mptt();
 /* to transform a string in a json strin */
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/x-json");

//////////////////////////////////////////////////////////////
////////////////////    SET VARIABLES    ////////////////////
/////////////////////////////////////////////////////////////
$table = $table_name; /* request of table where script reads data */
$counter = 0; /* start counter */
if(isset($_POST['rp'])){
  $counter = $_POST['rp']; /* request of item per page (it's generated by flexigred plugin */
}

//////////////////////////////////////////////////////////////
//////////////////// SEARCH VARIABLES   //////////////////////
/////////////////////////////////////////////////////////////
$sqlsearch = ''; /* it's important for advanced search */
$sqlsearch_or = ''; /* it contains condition with or clausole in query (in this case it's not important to set)*/

/////////////////////////////////////////////////////////////////////////////////////////////////////////////

$order_record = 'id'; /* record to sort data (if it's not specified in js code in files_upload.php) */
$order_verse = 'desc'; /* sort order (if it's not specified in js code in files_upload.php) */

/* some conditions for pagination (non change them) */
if (isset($_POST['sortname']) && $_POST['sortname'] != 'undefined'){ 
 $order_record = $_POST['sortname'];
}
if (isset($_POST['sortorder']) && $_POST['sortorder'] != 'undefined'){ 
 $order_verse = $_POST['sortorder'];
}
$pag = 1;
if (isset($_POST['page'])){ 
 $pag = $_POST['page'];
}
if (is_numeric($pag) == 0 || $pag < '1'){ 
 $pag = 1;
}
$limit_end = $counter;
$limit_start = ($pag - 1) * $limit_end;

if ($sqlsearch_or != ''){
  $sqlsearch = $sqlsearch . '('.$sqlsearch_or; 
}

 if ($sqlsearch != ''){ 
   if ($sqlsearch_or != ''){
    $sqlsearch = ' Where ' . mb_substr($sqlsearch, 0, mb_strlen($sqlsearch) - 4).')';
   }else{
    $sqlsearch = ' Where ' . mb_substr($sqlsearch, 0, mb_strlen($sqlsearch) - 5);
   }
 }
  $sql = ' select * from '.$table.' ' . $sqlsearch;
  
  //$sql = ' select * from '.$table.' ' . $sqlsearch;
  
  
  $rs_result = execute($sql);   
  $initial_array = array();
  
while ($rs = mysql_fetch_array($rs_result)) {
	  	
   $img_del = '<span class="btn btn-danger action-delete-custom" id="'.$rs['id'].'" style="cursor:pointer;margin-right:5px;" rel-tooltip="tooltip" title="'.$lang_['table']['FLEX_ACTION_DELETE'].'"><i class="icon-trash icon-white"></i> </span>';
   $img_edit = '<span class="btn btn-success action-edit" id="'.$rs['id'].'" style="cursor:pointer;margin-right:10px;" rel-tooltip="tooltip" title="'.$lang_['table']['FLEX_ACTION_EDIT'].'"><i class="icon-edit icon-white"></i> </span>';
   $img_enabled = '<span class="btn action-enable'.($rs['active'] ? ' processed btn-success' : ' btn-warning').'" id="'.$rs['id'].'" style="cursor:pointer;margin-right:10px;" rel-tooltip="tooltip" title="'.($rs['active'] ? $lang_['pl_slideshow']['TOOLTIP_DISABLED'] : $lang_['pl_slideshow']['TOOLTIP_ENABLED']).'"><i class="icon-white icon-eye-'.($rs['active'] ? 'open' : 'close').'"></i></span>';
   $status = $rs['active'] ? 'icon-check' : 'icon-close';
   $slect_checkbox = '<input type="checkbox" id="'.$rs['id'].'" value="'.$rs['id'].'" name="box_delete[]" class="check_selezione" align="absmiddle" />';
	/* here start the part that provides to return data ----- from here to... (NOT CHANGE/DELETE ID ADN SELECT_CHECKBOX KEY, NOT DELETE ACTIONS KEY-YOU CAN EDIT IT ONLY)*/  
	  $initial_array[] = array(
	   "id" => $rs['id'],
	   "name" => str_json($rs['name']),
	   "active" => str_json('<span style="display:none">'.$rs['active'].'</span><i class="icon icon-color '.$status.'"></i>'),
	   "slect_checkbox" => str_json($slect_checkbox),
	   "actions" => str_json($img_enabled.$img_edit.$img_del)
	  );   
}
	$result  = '{';
	$result .= '"page":'.$pag.',';
	$result .= '"total":'.count($initial_array).'';	
	if(!empty($initial_array)){
		$result .= ",\"rows\":[";
		$initial_array = array_msort($initial_array, array($order_record=>mb_strtolower($order_verse) == 'asc' ? SORT_ASC : SORT_DESC));	
		$initial_array = array_slice($initial_array,$limit_start,$limit_end);
		foreach($initial_array as $item){
		   $result .= '{';
		   $result .= '"id":"'.$item['id'].'",';
		   $result .= '"cell":{';
		   $result .= '"id":"'.$item['id'].'",';
		   $result .= '"check":"'.$item['slect_checkbox'].'",';
		/* ... here no change 
			  
		if you want add other data please follow the code below:
		   e.g. if a record named xxx is in table and you want add its to date you do this:
		   $result .= '"xxx":"'.str_json($rs['xxx']).'",';
		   after in "index.php" file in this directory integrate js code in this way :
		   {display: 'XXX', name : 'xxx', width : 180, sortable : true, align: 'left'},
		*/
		   $result .= '"name":"'.$item['name'].'",';
		   $result .= '"active":"'.$item['active'].'",';
		   $result .= '"actions":"'.$item['actions'].'"';
		/* from here to the end no change */   
		   $result .= '}},';   
		}
		$result = mb_substr($result,0,mb_strlen($result)-1); 
		$result .= "]";		
	} 
$result .= "}"; 
echo $result;
?>