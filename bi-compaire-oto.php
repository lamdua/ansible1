<?php
require "common.php";

//added by Pho 28-5-07
//if (!$_SESSION['UID']) exo_redirect("lec-login.php");

$table = "inscription_sur_choix";

$ID = exo_code($_REQUEST["ID"]);
$page = exo_code($_REQUEST["page"]);
$act = exo_code($_REQUEST["act1"]);

$db_bi = exo_connect("_bi");

//////////////////
//select value from inscription_sur_choix,sejour,country,formula
$sql_sejour = "	select inscription_sur_choix.*,stay.ID sID,oto_tarif.FK_country,stay.FK_formula, oto_tarif.nb_course_hour, country.label co_name, 'ONE-TO-ONE INDIVIDUEL' fo_name
				,stay.code_period, date_from,date_to,month_from,month_to,date_format( STR_TO_DATE(CONCAT_WS('/',date_from,month_from,dyear), '%d/%m/%Y' ) , '%Y-%m-%d' ) dd_from,date_format( STR_TO_DATE(CONCAT_WS('/',date_to,month_to,dyear), '%d/%m/%Y' ) , '%Y-%m-%d' ) dd_to,CONCAT_WS('/',date_to,month_to) date_end, CONCAT_WS('/',date_from,month_from) date_start
				from $db_name.inscription_sur_choix 
				left join stay on inscription_sur_choix.FK_sejour = stay.ID 
				inner join oto_tarif ON inscription_sur_choix.FK_tarif=oto_tarif.ID  
				inner join country on oto_tarif.FK_country = country.ID 
				left join formula on stay.FK_formula = formula.ID  where inscription_sur_choix.ID = ".$ID;
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
$sql_sejour_p = "	select inscription_sur_choix.*,stay.ID sID,oto_tarif.FK_country,stay.FK_formula, oto_tarif.nb_course_hour, country.label co_name, 'ONE-TO-ONE INDIVIDUEL' fo_name
				,stay.code_period, date_from,date_to,month_from,month_to,date_format( STR_TO_DATE(CONCAT_WS('/',prev_date_from,prev_month_from,dyear), '%d/%m/%Y' ) , '%Y-%m-%d' ) dd_from,date_format( STR_TO_DATE(CONCAT_WS('/',prev_date_to,prev_month_to,dyear), '%d/%m/%Y' ) , '%Y-%m-%d' ) dd_to,CONCAT_WS('/',prev_date_to,prev_month_to) date_end, CONCAT_WS('/',prev_date_from,prev_month_from) date_start
				from $db_name.inscription_sur_choix 
				left join stay on inscription_sur_choix.FK_sejour = stay.ID 
				inner join oto_tarif ON inscription_sur_choix.FK_tarif=oto_tarif.ID  
				inner join country on oto_tarif.FK_country = country.ID 
				left join formula on stay.FK_formula = formula.ID  where inscription_sur_choix.ID = ".$ID;
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
//$l = $sejour["length_weeks"];
$l = ((strtotime($sejour["dd_to"]) - strtotime($sejour["dd_from"])) / (60 * 60 * 24))/7;

$top->val["VALUE_sejour"] = $sejour["co_name"].", ".stripslashes($sejour["fo_name"]).", $l semaine".($l>1?'s':'').", ".$sejour["nb_course_hour"]."h, du ".$sejour["date_start"]." au ".$sejour["date_end"];


if ($is_prev) {
	$l = ((strtotime($prev_sejour["dd_to"]) - strtotime($prev_sejour["dd_from"])) / (60 * 60 * 24))/7;
	$top->val["VALUE_prev_sejour"] = $prev_sejour["co_name"].", ".stripslashes($prev_sejour["fo_name"]).", $l semaine".($l>1?'s':'').", ".$prev_sejour["nb_course_hour"]."h, du ".$prev_sejour["date_start"]." au ".$prev_sejour["date_end"];
	
	$top->val["VALUE_fee_diff"] = $sejour["total_fee"] - $sejour["prev_total_fee"];
	if ($top->val["VALUE_fee_diff"] >0) $top->val["VALUE_fee_diff"] = "+".$top->val["VALUE_fee_diff"];
}

$top->val["VALUE_total_fee_num"] = $sejour["total_fee"];
$top->val["VALUE_voyage_fee_num"] = $sejour["voyage_fee"];


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

$top->val["VALUE_stay_fee"]=$sejour["stay_fee"] - $prev_sejour["prev_stay_fee"];
if ($top->val["VALUE_stay_fee"]>0) $top->val["VALUE_stay_fee"] = "+".$top->val["VALUE_stay_fee"];
$top->val["VALUE_assurance_fee"]=$assurance - $pre_assurance;
if ($top->val["VALUE_assurance_fee"]>0)$top->val["VALUE_assurance_fee"]="+".$top->val["VALUE_assurance_fee"];
$top->val["VALUE_assistance_fee"]=$assistance - $pre_assistance;
if ($top->val["VALUE_assistance_fee"]>0)$top->val["VALUE_assistance_fee"]="+".$top->val["VALUE_assistance_fee"];
$acc_fee='';
if($sejour["acc_transfert_fee"] > 0 && $sejour["acc_transfert_fee"]!=$sejour["prev_acc_transfert_fee"]){
	$top->val["VALUE_acc_trans"]="Accueil transfert ";
	$top->val["VALUE_acc_trans_fee"]=$sejour["acc_transfert_fee"] - $prev_sejour["prev_acc_transfert_fee"];
	if ($top->val["VALUE_acc_trans_fee"]>0) $top->val["VALUE_acc_trans_fee"] = "+".$top->val["VALUE_acc_trans_fee"];
	$acc_fee=($sejour["acc_transfert_fee"] - $prev_sejour["prev_acc_transfert_fee"]) ; 
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

$top->val["VALUE_total_fee"]=($sejour["stay_fee"] - $prev_sejour["prev_stay_fee"]) + ($assurance - $pre_assurance) + ($assistance - $pre_assistance) + $acc_fee + $total_reduc;
if ($top->val["VALUE_total_fee"]>0) $top->val["VALUE_total_fee"]="+".$top->val["VALUE_total_fee"];

echo write_template("bi-compaire-oto.html", $top);
?>