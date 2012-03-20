<?php
// $Id: directory.php 5204 2010-09-06 20:10:52Z mageg $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: XOOPS Foundation                                                  //
// URL: http://www.xoops.org/                                                //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

	include ('header.php');
	xoops_loadLanguage('admin', 'songlist');
	
	xoops_cp_header();		

	$op = (!empty($_GET['op']) ? $_GET['op'] : (!empty($_POST['op']) ? $_POST['op'] : "default"));
	
	switch ($op) {
	    case "default":
	    default:
    	
			$indexAdmin = new ModuleAdmin();
			echo $indexAdmin->addNavigation(basename(__FILE__));
			
			$indexAdmin = new ModuleAdmin();	
		    $indexAdmin->addInfoBox(_AM_SONGLIST_PREFERENCES);
		    	        
		    $indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_POLLMODULE."</label>", ($isOK)?_AM_SONGLIST_AVAILABLE:_AM_SONGLIST_NOTAVAILABLE, ($isOK)?'Green':'Red');
		    
		    if(array_key_exists('imagemagick',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_IMAGEMAGICK."</label>", _AM_SONGLIST_AUTODETECTED.$imageLibs['imagemagick'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_IMAGEMAGICK."</label>", _AM_SONGLIST_NOTAVAILABLE, 'Red');
			}
			if(array_key_exists('netpbm',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_NETPDM."</label>", _AM_SONGLIST_AUTODETECTED.$imageLibs['netpbm'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_NETPDM."</label>", _AM_SONGLIST_NOTAVAILABLE, 'Red');
			}
			if(array_key_exists('gd1',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_GDLIB1."</label>", _AM_SONGLIST_AUTODETECTED.$imageLibs['gd1'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_GDLIB1."</label>", _AM_SONGLIST_NOTAVAILABLE, 'Red');
			}
			if(array_key_exists('gd2',$imageLibs)) {
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_GDLIB2."</label>", _AM_SONGLIST_AUTODETECTED.$imageLibs['gd2'], 'Green');
		    } else { 
		    	$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PREFERENCES, "<label>"._AM_SONGLIST_GDLIB2."</label>", _AM_SONGLIST_NOTAVAILABLE, 'Red');
			}
				      
	        $attach_path = XOOPS_ROOT_PATH . '/' . $GLOBALS['xforumModuleConfig']['dir_attachments'] . '/';
	        $path_status = forum_getPathStatus($attach_path);
	        $indexAdmin->addInfoBox(_AM_SONGLIST_PATHS);
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_PATHS, "<label>"._AM_SONGLIST_ATTACHPATH."</label>", $attach_path . ' ( ' . $path_status . ' )', 'Green');
	        $thumb_path = $attach_path . 'thumbs/'; // be careful
	        $path_status = forum_getPathStatus($thumb_path);
	        $indexAdmin->addInfoBoxLine(_AM_SONGLIST_PATHS, "<label>"._AM_SONGLIST_THUMBPATH."</label>", $thumb_path . ' ( ' . $path_status . ' )', 'Green');

	        $indexAdmin->addInfoBox(_AM_SONGLIST_BOARDSUMMARY);
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_BOARDSUMMARY, "<label>"._AM_SONGLIST_TOTALTOPICS."</label>", get_total_topics(), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_BOARDSUMMARY, "<label>"._AM_SONGLIST_TOTALPOSTS."</label>", get_total_posts(), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_BOARDSUMMARY, "<label>"._AM_SONGLIST_TOTALVIEWS."</label>", get_total_views(), 'Green');
			$criteria = new Criteria('approved', 0);
			$post_handler = xoops_getmodulehandler('post', 'xforum');
			$topic_handler = xoops_getmodulehandler('topic', 'xforum');
			
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_BOARDSUMMARY, "<label>"._AM_SONGLIST_POSTSWAITINGAPPROVAL."</label>", $post_handler->getCount($criteria), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_BOARDSUMMARY, "<label>"._AM_SONGLIST_TOPICWAITINGAPPROVAL."</label>", $topic_handler->getCount($criteria), 'Green');
			
	        $report_handler = xoops_getmodulehandler('report', 'xforum');
	        $indexAdmin->addInfoBox(_AM_SONGLIST_REPORT);
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_REPORT, "<label>"._AM_SONGLIST_REPORT_PENDING."</label>", $report_handler->getCount(new Criteria("report_result", 0)), 'Green');
			$indexAdmin->addInfoBoxLine(_AM_SONGLIST_REPORT, "<label>"._AM_SONGLIST_REPORT_PROCESSED."</label>", $report_handler->getCount(new Criteria("report_result", 1)), 'Green');
	
	        if ($GLOBALS['xforumModuleConfig']['email_digest'] > 0) {
	            $digest_handler = xoops_getmodulehandler('digest', 'xforum');
	           	$due = ($digest_handler->checkStatus()) / 60; // minutes
	            $prompt = ($due > 0)? sprintf(_AM_SONGLIST_DIGEST_PAST, $due):sprintf(_AM_SONGLIST_DIGEST_NEXT, abs($due));
	            $indexAdmin->addInfoBox(_AM_SONGLIST_DIGEST);
				$indexAdmin->addInfoBoxLine(_AM_SONGLIST_DIGEST, "<label>"._AM_SONGLIST_DIGEST_SEND."</label>", $prompt, 'Green');
				$indexAdmin->addInfoBoxLine(_AM_SONGLIST_DIGEST, "<label>"._AM_SONGLIST_DIGEST_ARCHIVE."</label>", $digest_handler->getDigestCount(), 'Green');
	        }
	
	    	if (!empty($GLOBALS['xforumModuleConfig']['enable_usermoderate'])){
				$moderate_handler = xoops_getmodulehandler('moderate', 'xforum');
				$moderate_handler->clearGarbage();
			}
    		echo $indexAdmin->renderIndex();
			
	        echo chronolabs_inline(false); 
	        xoops_cp_footer();
	        break;
	}

?>