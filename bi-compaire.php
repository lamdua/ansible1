<?php
require "common.php";

echo "git";
//added by Pho 28-5-07
//if (!$_SESSION['UID']) exo_redirect("lec-login.php");

$table = "inscription_sur_choix";

$ID = exo_code($_REQUEST["ID"]);
$page = exo_code($_REQUEST["page"]);
$act = exo_code($_REQUEST["act1"]);

$db_bi = exo_connect("_bi");

//////////////////
//select value from inscription_sur_choix,sejour,country,formula
$sql_sejour = "	select $db_name.$table.* ,stay.ID sID,stay.FK_formula,stay.FK_country,country.label as co_name,stay.code_period,country.code country_code,formula.code fcode,
				'' as ai_name,formula.label as fo_name,
				departure_date as date_start,return_date as date_end, '0' as FK_airport,Mid(formula.code,4,2) as 1to1_hrs_per_week,Right(formula.code,1) as 1to1_part_per_family,
				voyage.*,voyage2.transportation_mode_client v2_mode,voyage2.departure_city_client v2_dep, voyage2.fee v2_fee
				from $db_name.$table inner join stay on $db_name.$table.FK_sejour = stay.ID 
				inner join formula on stay.FK_formula = formula.ID
				inner join country on stay.FK_country = country.ID 
				left join voyage on $db_name.$table.voyage_code_1 = voyage.code
				left join voyage voyage2 ON voyage.code_linking=voyage2.code 
				where $table.ID = ".$ID;
$sejour = exo_get_array($sql_sejour,$db_bi);
////////

//////////////////////////////////////////////////
$top=feed_top("inscription_sur_choix",$ID,"view","",$top,$dr);
$top->val["VALUE_lastname"]=strtoupper($top->val["VALUE_lastname"]);
$top->val["VALUE_firstname"]=ucfirst(strtolower($top->val["VALUE_firstname"]));

if ($top->val["VALUE_alive"]=="non" || trim($top->val["VALUE_info"])!="") 
	$top->val["VALUE_health_pbm"]= '<img src="img/health.gif" alt="" width="11" height="11" />&nbsp;';
///////////////////////////////////////////////////////////////////////


$top->val["VALUE_prev_block_start"] = "<!--";
$top->val["VALUE_prev_block_end"] = "-->";

if ($sejour["prev_total_fee"] > 0){
	$is_prev = true;
	$sql_sejour_p = "	select $db_name.$table.* ,stay.ID sID,stay.FK_formula,stay.FK_country,country.label as co_name,country.code country_code,
				'' as ai_name,formula.label as fo_name,
				departure_date as date_start,return_date as date_end, '0' as FK_airport,'0' as 1to1_hrs_per_week,'0' as 1to1_part_per_family,
				voyage.*,voyage2.transportation_mode_client v2_mode,voyage2.departure_city_client v2_dep, voyage2.fee v2_fee
				from $db_name.$table inner join stay on $db_name.$table.prev_FK_sejour = stay.ID
				left join country on stay.FK_country = country.ID 
				left join formula on stay.FK_formula = formula.ID
				left join voyage on $db_name.$table.prev_voyage_code_1 = voyage.code
				left join voyage voyage2 ON voyage.code_linking=voyage2.code 
				where $db_name.$table.ID = ".$ID;
	$prev_sejour = exo_get_array($sql_sejour_p,$db_bi);
	$top->val["VALUE_prev_block_start"] = "";
	$top->val["VALUE_prev_block_end"] = "";
	if($prev_sejour["prev_FK_extra_3"]){
	   $top->val["VALUE_pre_insurance"]="Assurance Annulation";
	   $pre_assurance=$prev_sejour["prev_assur_fee"];
	}else{
	   $top->val["VALUE_pre_insurance"]="Pas d'assurance";
	   $pre_assurance=0;
	}
	if($prev_sejour["prev_FK_extra_2"]){
	   $top->val["VALUE_pre_rapat"]="Assurance Assistance";
       $pre_assistance= $prev_sejour["prev_assis_fee"];
	}else{
	   $top->val["VALUE_pre_rapat"]="Pas d'assurance";
	   $pre_assistance=0;
	}
}

//create string sejour
if($sejour["country_code"]=='USA'){
	$top->val["VALUE_sejour"] = $sejour["co_name"]." ".stripslashes($sejour["ai_name"]).", ".stripslashes($sejour["fo_name"]);
}else{
	$top->val["VALUE_sejour"] = $sejour["co_name"].", ".stripslashes($sejour["fo_name"]);
}

if ($is_prev) {
	if($prev_sejour["country_code"]=='USA'){
		$top->val["VALUE_prev_sejour"] = $prev_sejour["co_name"]." ".stripslashes($prev_sejour["ai_name"]).", ".stripslashes($prev_sejour["fo_name"]);
	}else{
		$top->val["VALUE_prev_sejour"] = $prev_sejour["co_name"].", ".stripslashes($prev_sejour["fo_name"]);
	}
	
	$top->val["VALUE_fee_diff"] = $sejour["total_fee"] - $sejour["prev_total_fee"];
	if ($top->val["VALUE_fee_diff"] >0) $top->val["VALUE_fee_diff"] = "+".$top->val["VALUE_fee_diff"];
}

$top->val["VALUE_total_fee_num"] = $sejour["total_fee"];
$top->val["VALUE_voyage_fee_num"] = $sejour["voyage_fee"];


$top->val["VALUE_trans"] = stripslashes($sejour["departure_city_client"]);
$v = $sejour["transportation_mode_client"].' vers '.$sejour["arrival_city"];
if ($sejour["v2_mode"]) $v.= " puis ".$sejour["v2_mode"]." au d&eacute;part de ".$sejour["v2_dep"]."";

$top->val["VALUE_city"] = $v ;

if ($is_prev){
			$v = $prev_sejour["transportation_mode_client"].' vers '.$prev_sejour["arrival_city"];
		if ($prev_sejour["v2_mode"]) $v.= " puis ".$prev_sejour["v2_mode"]." au d&eacute;part de ".$prev_sejour["v2_dep"]."";

	$top->val["VALUE_prev_trans"] = stripslashes($prev_sejour["trans_name"]);
		
	$top->val["VALUE_prev_city"] = $v;
}

if($sejour["FK_extra_3"]>0){
   	$top->val["VALUE_insurance"]="Assurance Annulation";
    $assurance=$sejour["assur_fee"];
}else{
   $top->val["VALUE_insurance"]="Pas d'assurance";
   $assurance=0;
}
if($sejour["FK_extra_2"]>0){
   $top->val["VALUE_rapat"]="Assurance Assistance";
   $assistance= $sejour["assis_fee"];
}else{
   $top->val["VALUE_rapat"]="Pas d'assurance";
   $assistance=0;
}

//added 25-11-10: option4
$db = exo_connect('_bi');
if ($sejour["prev_FK_extra_4"]){
   	$top->val["VALUE_pre_option4"]=exo_decode(exo_get_elt("select label from extra where ID='".$sejour["prev_FK_extra_4"]."'"));
    $prev_opt4=$sejour["prev_option4_fee"];
}
else{
   	$top->val["VALUE_pre_option4"]="Pas d'option 4";
    $prev_opt4=0;
}
if ($sejour["FK_extra_4"]){
   	$top->val["VALUE_option4"]=exo_decode(exo_get_elt("select label from extra where ID='".$sejour["FK_extra_4"]."'"));
    $opt4=$sejour["option4_fee"];
}else{
   	$top->val["VALUE_option4"]="Pas d'option 4";
    $opt4=0;
}

//added 10-03-14: reduction
$total_reduc = 0;
for ($i=1; $i<4; $i++){
	if ($r = $sejour["FK_reduction_$i"]){
		$reduc = exo_get_array("select * from supplement_reduction where ID='$r'");
		if ($reduc['amount']){
			$tmp = new tpl_value;
			$tmp->val['VALUE_reduc'] = exo_decode($reduc['label']);
			if ($reduc['type']=='R') {
				$total_reduc -= $reduc['amount'];
				$tmp->val['VALUE_reduc_fee'] = -$reduc['amount'];
			}
			else {
				$total_reduc += $reduc['amount'];
				$tmp->val['VALUE_reduc_fee'] = '+'.$reduc['amount'];
			}
			$top->block['REDUC'][] = $tmp;
		}
	}
}

$top->val["VALUE_stay_fee"]=$sejour["stay_fee"] - $prev_sejour["prev_stay_fee"];
if ($top->val["VALUE_stay_fee"]>0) $top->val["VALUE_stay_fee"] = "+".$top->val["VALUE_stay_fee"];
$top->val["VALUE_voyage_fee"]=$sejour["voyage_fee"] - $prev_sejour["prev_voyage_fee"];
if ($top->val["VALUE_voyage_fee"]>0)$top->val["VALUE_voyage_fee"]="+".$top->val["VALUE_voyage_fee"];
$top->val["VALUE_assurance_fee"]=$assurance - $pre_assurance;
if ($top->val["VALUE_assurance_fee"]>0)$top->val["VALUE_assurance_fee"]="+".$top->val["VALUE_assurance_fee"];
$top->val["VALUE_assistance_fee"]=$assistance - $pre_assistance;
if ($top->val["VALUE_assistance_fee"]>0)$top->val["VALUE_assistance_fee"]="+".$top->val["VALUE_assistance_fee"];
$top->val["VALUE_option4_fee"]=$opt4 - $prev_opt4;
if ($top->val["VALUE_option4_fee"]>0)$top->val["VALUE_option4_fee"]="+".$top->val["VALUE_option4_fee"];

$top->val["VALUE_total_fee"]=($sejour["stay_fee"] - $prev_sejour["prev_stay_fee"]) + ($sejour["voyage_fee"] - $prev_sejour["prev_voyage_fee"]) + ($assurance - $pre_assurance) + ($assistance - $pre_assistance) + ($opt4 - $prev_opt4) + $total_reduc;
if ($top->val["VALUE_total_fee"]>0) $top->val["VALUE_total_fee"]="+".$top->val["VALUE_total_fee"];

echo write_template("bi-compaire.html", $top);
?>