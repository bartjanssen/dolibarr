<?php
/* Copyright (C) 2012      Nicolas Villa aka Boyquotes http://informetic.fr
 * Copyright (C) 2013      Florian Henry <florian.henry@open-concept.pro>
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
 *  \file       cron/cron/list.php
 *  \ingroup    cron
 *  \brief      Lists Jobs
 */


require '../main.inc.php';
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

// librairie jobs
require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/cron.lib.php';

$langs->load("admin");
$langs->load("cron");

if (!$user->rights->cron->read) accessforbidden();

/*
 * Actions
 */
$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');

$sortorder=GETPOST('sortorder','alpha');
$sortfield=GETPOST('sortfield','alpha');
$page=GETPOST('page','int');
$status=GETPOST('status','int');

//Search criteria
$search_label=GETPOST("search_label",'alpha');

if (empty($sortorder)) $sortorder="DESC";
if (empty($sortfield)) $sortfield="t.datenextrun";
if (empty($arch)) $arch = 0;

if ($page == -1) {
	$page = 0 ;
}

$limit = $conf->global->MAIN_SIZE_LISTE_LIMIT;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Do we click on purge search criteria ?
if (GETPOST("button_removefilter_x"))
{
	$search_label='';
}

$filter=array();
if (!empty($search_label)) {
	$filter['t.label']=$search_label;
}

// Delete jobs
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->cron->delete){

	//Delete de la tache cron
	$object = new Cronjob($db);
	$object->id=$id;
	$result = $object->delete($user);

	if ($result < 0) {
		setEventMessage($object->error,'errors');
	}
}

// Execute jobs
if ($action == 'confirm_execute' && $confirm == "yes" && $user->rights->cron->execute){

	//Execute jobs
	$object = new Cronjob($db);
	$job = $object->fetch($id);

	$result = $object->run_jobs($user->login);
	if ($result < 0) {
		setEventMessage($object->error,'errors');
	}

}


/*
 * View
 */
if (!empty($status)) {
	$pagetitle=$langs->trans("CronListActive");
}else {
	$pagetitle=$langs->trans("CronListInactive");
}

llxHeader('',$pagetitle);


// Form object for popup
$form = new Form($db);

if ($action == 'delete')
{
	$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id.'&status='.$status,$langs->trans("CronDelete"),$langs->trans("CronConfirmDelete"),"confirm_delete",'','',1);
	if ($ret == 'html') print '<br>';
}

if ($action == 'execute'){
	$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id.'&status='.$status,$langs->trans("CronExecute"),$langs->trans("CronConfirmExecute"),"confirm_execute",'','',1);
	if ($ret == 'html') print '<br>';
}


print_fiche_titre($pagetitle,'','setup');

print $langs->trans('CronInfo');

// liste des jobs creer
$object = new Cronjob($db);
$result=$object->fetch_all($sortorder, $sortfield, $limit, $offset, $status, $filter);
if ($result < 0) {
	setEventMessage($object->error,'errors');
}


print "<p><h2>";
print $langs->trans('CronWaitingJobs');
print "</h2></p>";

if (count($object->lines)>0) {
	
	print '<table width="100%" cellspacing="0" cellpadding="4" class="border">';
	print '<tr class="liste_titre">';	
	$arg_url='&page='.$page.'&status='.$status.'&search_label='.$search_label;
	print_liste_field_titre($langs->trans("CronLabel"),$_SERVEUR['PHP_SELF'],"t.label","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronTask"),'','',"",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronDtStart"),$_SERVEUR['PHP_SELF'],"t.datestart","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronDtEnd"),$_SERVEUR['PHP_SELF'],"t.dateend","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronDtLastLaunch"),$_SERVEUR['PHP_SELF'],"t.datelastrun","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronDtNextLaunch"),$_SERVEUR['PHP_SELF'],"t.datenextrun","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronFrequency"),'',"","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronNbRun"),$_SERVEUR['PHP_SELF'],"t.nbrun","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronLastResult"),$_SERVEUR['PHP_SELF'],"t.lastresult","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CronLastOutput"),$_SERVEUR['PHP_SELF'],"t.lastoutput","",$arg_url,'',$sortfield,$sortorder);
	print '<td></td>';
	
	print '</tr>';
	
	print '<form method="get" action="'.$url_form.'" name="search_form">'."\n";
	print '<input type="hidden" name="status" value="'.$status.'" >';
	print '<tr class="liste_titre">';
	
	
	
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_label" value="'.$search_label.'" size="10">';
	print '</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
	print '&nbsp; ';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
	print '</td>';
	
	print '</tr>';
	print '</from>';
	
	
	
	// Boucler sur chaque job
	$style='impair';
	foreach($object->lines as $line){
		// title profil
		if ($style=='pair') {$style='impair';}
		else {$style='pair';}
		
		print '<tr class="'.$style.'">';
		
		print '<td>';
		if(!empty($line->label)) {
			print '<a href="'.dol_buildpath('/cron/cron/card.php',1).'?id='.$line->id.'">'.$line->label.'</a>';
		} 
		else {
			print $langs->trans('CronNone');
		}
		print '</td>';
		
		print '<td>';
		if ($line->jobtype=='method') {
			print $langs->trans('CronModule').':'.$line->module_name.'<BR>';
			print $langs->trans('CronClass').':'. $line->classesname.'<BR>';
			print $langs->trans('CronObject').':'. $line->objectname.'<BR>';
			print $langs->trans('CronMethod').':'. $line->methodename;
			if(!empty($line->params)) {
				print '<BR/>'.$langs->trans('CronArgs').':'. $line->params;
			}
			
		}elseif ($line->jobtype=='command') {
			print $langs->trans('CronCommand').':'. dol_trunc($line->command);
			if(!empty($line->params)) {
				print '<BR/>'.$langs->trans('CronArgs').':'. $line->params;
			}
		}
		print '</td>';
		
		print '<td>';
		if(!empty($line->datestart)) {print dol_print_date($line->datestart,'dayhourtext');} else {print $langs->trans('CronNone');}
		print '</td>';
		
		print '<td>';
		if(!empty($line->dateend)) {print dol_print_date($line->dateend,'dayhourtext');} else {print $langs->trans('CronNone');}
		print '</td>';
		
		print '<td>';
		if(!empty($line->datelastrun)) {print dol_print_date($line->datelastrun,'dayhourtext');} else {print $langs->trans('CronNone');}
		print '</td>';
		
		print '<td>';
		if(!empty($line->datenextrun)) {print dol_print_date($line->datenextrun,'dayhourtext');} else {print $langs->trans('CronNone');}
		print '</td>';
		
		print '<td>';
		if($line->unitfrequency == "60") print $langs->trans('CronEach')." ".($line->frequency/$line->unitfrequency)." ".$langs->trans('Minutes');
		if($line->unitfrequency == "3600") print $langs->trans('CronEach')." ".($line->frequency/$line->unitfrequency)." ".$langs->trans('Hours');
		if($line->unitfrequency == "86400") print $langs->trans('CronEach')." ".($line->frequency/$line->unitfrequency)." ".$langs->trans('Days');
		if($line->unitfrequency == "604800") print $langs->trans('CronEach')." ".($line->frequency/$line->unitfrequency)." ".$langs->trans('Weeks');
		print '</td>';
		
		print '<td>';
		if(!empty($line->nbrun)) {print $line->nbrun;} else {print '0';}
		print '</td>';
		
		print '<td>';
		if(!empty($line->lastresult)) {print dol_trunc($line->lastresult);} else {print $langs->trans('CronNone');}
		print '</td>';
		
		print '<td>';
		if(!empty($line->lastoutput)) {print dol_trunc(nl2br($line->lastoutput),100);} else {print $langs->trans('CronNone');}
		print '</td>';
				
		print '<td>';
		if ($user->rights->cron->delete) {
			print "<a href=\"".dol_buildpath('/cron/cron/list.php',1)."?id=".$line->id."&status=".$status."&action=delete\" title=\"".$langs->trans('CronDelete')."\"><img src=\"".dol_buildpath('/cron/img/delete.png',1)."\" alt=\"".$langs->trans('CronDelete')."\" /></a>";
		} else {
			print "<a href=\"#\" title=\"".$langs->trans('NotEnoughPermissions')."\"><img src=\"".dol_buildpath('/cron/img/delete.png',1)."\" alt=\"".$langs->trans('NotEnoughPermissions')."\" /></a>";
		}
		if ($user->rights->cron->execute) {
			print "<a href=\"".dol_buildpath('/cron/cron/list.php',1)."?id=".$line->id."&status=".$status."&action=execute\" title=\"".$langs->trans('CronExecute')."\"><img src=\"".dol_buildpath('/cron/img/execute.png',1)."\" alt=\"".$langs->trans('CronExecute')."\" /></a>";
		} else {
			print "<a href=\"#\" title=\"".$langs->trans('NotEnoughPermissions')."\"><img src=\"".dol_buildpath('/cron/img/execute.png',1)."\" alt=\"".$langs->trans('NotEnoughPermissions')."\" /></a>";
		}
		print '</td>';
		
		print '</tr>';
	}
	print '</table>';
} else {
	print $langs->trans('CronNoJobs');
}

print "\n\n<div class=\"tabsAction\">\n";
if (! $user->rights->cron->create) {
	print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->transnoentitiesnoconv("NotEnoughPermissions")).'">'.$langs->trans("New").'</a>';
} else {
	print '<a class="butAction" href="'.dol_buildpath('/cron/card.php',1).'?action=create">'.$langs->trans("New").'</a>';
}
print '<br><br></div>';

llxFooter();
$db->close();