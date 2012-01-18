<?php
/**
 * @version 1.2 $Id: helper.php 1088 2012-01-08 16:40:44Z ggppdk $
 * @package Joomla
 * @subpackage FLEXIcontent Module
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_flexicontent'.DS.'defineconstants.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'helpers'.DS.'route.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'classes'.DS.'flexicontent.helper.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'classes'.DS.'flexicontent.fields.php');
require_once (JPATH_SITE.DS.'modules'.DS.'mod_flexicontent'.DS.'classes'.DS.'datetime.php');

class modFlexicontentHelper
{
	
	function getList(&$params)
	{
		$db =& JFactory::getDBO();
		
		$Itemid		= JRequest::getInt('Itemid');
		if (!$Itemid) {
			$app = & JFactory::getApplication();
			$app->route();
			$cid 		= JRequest::getInt('cid');
			$id			= JRequest::getInt('id');
			$Itemid		= JRequest::getInt('Itemid');
		}
		//$force_curr_itemid = (int)$params->get('force_curr_itemid', 1);
		//$forced_itemid = ($force_curr_itemid) ? $Itemid	: 0;

		// get the component parameters
		$flexiparams =& JComponentHelper::getParams('com_flexicontent');
		
		// get module ordering parameters
		$ordering 				= $params->get('ordering');
		$count 					= (int)$params->get('count', 5);
		$featured				= (int)$params->get('count_feat', 1);

		// get module display parameters
		$moduleclass_sfx 		= $params->get('moduleclass_sfx');
		$layout 				= $params->get('layout');
		$add_ccs 				= $params->get('add_ccs');
		$add_tooltips 			= $params->get('add_tooltips', 1);
		
		// get other module parameters
		$method_curlang	= (int)$params->get('method_curlang', 1);
		
		// standard
		$display_title 		= $params->get('display_title');
		$link_title 			= $params->get('link_title');
		$cuttitle 				= $params->get('cuttitle');
		$display_date			= $params->get('display_date');
		$display_text 		= $params->get('display_text');
		$mod_readmore	 		= $params->get('mod_readmore');
		$mod_cut_text 		= $params->get('mod_cut_text');
		$mod_do_stripcat	= $params->get('mod_do_stripcat', 1);
		$mod_use_image 		= $params->get('mod_use_image');
		$mod_image 				= $params->get('mod_image');
		$mod_image_custom_source 		= $params->get('mod_image_custom_source');
		$mod_link_image		= $params->get('mod_link_image');
		$mod_width 				= (int)$params->get('mod_width', 80);
		$mod_height 			= (int)$params->get('mod_height', 80);
		$mod_method 			= (int)$params->get('mod_method', 1);
		// featured
		$display_title_feat 	= $params->get('display_title_feat');
		$link_title_feat 			= $params->get('link_title_feat');
		$cuttitle_feat 				= $params->get('cuttitle_feat');
		$display_date_feat		= $params->get('display_date_feat');
		$display_text_feat 		= $params->get('display_text');
		$mod_readmore_feat		= $params->get('mod_readmore_feat');
		$mod_cut_text_feat 		= $params->get('mod_cut_text_feat');
		$mod_do_stripcat_feat	= $params->get('mod_do_stripcat_feat', 1);
		$mod_use_image_feat 	= $params->get('mod_use_image_feat');
		$mod_link_image_feat 	= $params->get('mod_link_image_feat');
		$mod_width_feat 		= (int)$params->get('mod_width_feat', 140);
		$mod_height_feat 		= (int)$params->get('mod_height_feat', 140);
		$mod_method_feat 		= (int)$params->get('mod_method_feat', 1);

		// get module fields parameters
		$use_fields 			= $params->get('use_fields', 1);
		$display_label 			= $params->get('display_label');
		$fields = array_map( 'trim', explode(',', $params->get('fields')) );
		if ($fields[0]=='') $fields = array();
		
		// get fields that when empty cause an item to be skipped
		$skip_items = (int)$params->get('skip_items', 0);
		$skiponempty_fields = array_map( 'trim', explode(',', $params->get('skiponempty_fields')) );
		if ($skiponempty_fields[0]=='') $skiponempty_fields = array();
		
		if ($params->get('maxskipcount',50) > 100) {
  		$params->set('maxskipcount',100);
		}
		
		$striptags_onempty_fields = $params->get('striptags_onempty_fields');
		$onempty_fields_combination = $params->get('onempty_fields_combination');
		
		// featured
		$use_fields_feat 			= $params->get('use_fields_feat', 1);
		$display_label_feat 	= $params->get('display_label_feat');
		$fields_feat = array_map( 'trim', explode(',', $params->get('fields_feat')) );
		if ($fields_feat[0]=='') $fields_feat = array();

		$rows_arr = array();
		if (!is_array($ordering)) { $ordering = explode(',', $ordering); }
		foreach ($ordering as $ord) {
			$items_arr = modFlexicontentHelper::getItems($params, $ord);
			if (!$items_arr) continue;
			foreach ($items_arr as $cat_counter => $items) {
				for ($i=0; $i<count($items); $i++) {
					$items[$i]->featured = ($i < $featured) ? 1 : 0;
					$items[$i]->fetching = $ord;
					$rows_arr[$cat_counter][] = $items[$i];
				}
			}
		}
		
		// Impementation of Empty Field Filter.
		// The cost of the following code is minimal.
		// The big time cost goes into rendering the fields ... 
		// We need to create the display of the fields before examining if they are empty.
		// The hardcoded limit of max items skipped is 100.
		if ( $skip_items && count($skiponempty_fields) ) {
			// 0. Add skipfields to the list of fields to be rendered
			$fields_list = implode(',', $skiponempty_fields);
			$params->set('fields',$fields_list);
			
			foreach($rows_arr as $rows) {
				
				// 1. The filtered rows
				$filtered_rows = array();
				
				// 2. Get fields values for the items
				$items = & FlexicontentFields::getFields($rows, 'module', $params);
				
				// 3. Skip Items with empty fields (if this filter is enabled)
				foreach($items as $item) {
					if (!isset($order_count[$item->fetching]))    // Check to initialize counter for this ordering 
						$order_count[$item->fetching] = 0;
					if ($order_count[$item->fetching] >= $count)   // Check if enough encountered for this ordering 
						continue;
					
					// Now check for empty values on field that when empty, the item must be skipped
					if ($skip_items) {
						// Construct display values array
						$field_val = array();
						foreach($skiponempty_fields as $skipfieldname) {
							$field_val[$skipfieldname] = $item->fields[$skipfieldname]->display;
						}
						
						if ($onempty_fields_combination == 'any')
							$skip_item = 0;
						else //if ($skip_items && $onempty_fields_combination == 'all')
							$skip_item = 1;		    
						
						foreach($skiponempty_fields as $skipfieldname) {
							$val = $field_val[$skipfieldname];
							if ($striptags_onempty_fields) $val = strip_tags ($field_val[$skipfieldname]) ;
							$val = trim($val);
							if ( !$val) {
								if ($onempty_fields_combination=='any') {
									$skip_item = 1;
									break;
								}
							} else {
								if ($onempty_fields_combination == 'all') {
									$skip_item = 0;
									break;
								}
							}
						}
						if ($skip_item && count($skiponempty_fields)) {
							if(!isset($order_skipcount[$item->fetching]) ) $order_skipcount[$item->fetching] = 0;
							$order_skipcount[$item->fetching]++;
							continue;
						}
					}
					
					// 4. Increment counter for item's ordering and Add item to list of displayed items
					$order_count[$item->fetching]++;
					$filtered_rows[] = & $item;
				}
				$filtered_rows_arr[] = & $filtered_rows;
			}
		} else {
			$filtered_rows_arr = & $rows_arr;
		}
		
		// *** OPTIMIZATION: we only render the fields after skipping unwanted items
		if ( ($use_fields && count($fields)) || ($use_fields_feat && count($fields_feat)) ) {
			$all_fields = array();
		  if ($use_fields && count($fields))           $all_fields = array_merge($all_fields, $fields);
		  if ($use_fields_feat && count($fields_feat)) $all_fields = array_merge($all_fields, $fields_feat);
		  $all_fields = array_unique($all_fields);
		  $fields_list = implode(',', $all_fields);
		  $params->set('fields',$fields_list);
		}
		
		$lists_arr = array();
		foreach($filtered_rows_arr as $filtered_rows) {
			
			if ( ($use_fields && count($fields)) || ($use_fields_feat && count($fields_feat)) ) {
				$rows = & FlexicontentFields::getFields($filtered_rows, 'module', $params);
			} else {
				$rows = & $filtered_rows;
			}
			
			// For Debuging
			/*foreach ($order_skipcount as $skipordering => $skipcount) {
			  echo "SKIPS $skipordering ==> $skipcount<br>\n";
			}*/
	
			$lists	= array();
			foreach ( $ordering as $ord )
			{
				$lists[$ord]	= array();
			}
			
			$ord = "__start__";
			foreach ( $rows as $row )  // Single pass of rows
			{
			  if ($ord != $row->fetching) {  // Detect change of next ordering group
			    $ord = $row->fetching;
			    $i = 0;
			  }
			  
				if ($row->featured)
				{						
					// image processing
					$thumb = '';
					$thumb_rendered = '';
					if ($mod_use_image_feat) {
						if ($mod_image_custom_source) {
							list($fieldname, $varname) = preg_split('/##/',$mod_image_custom_source);
							$fieldname = trim($fieldname); $varname = trim($varname);
							$thumb_rendered = FlexicontentFields::getFieldDisplay($row, $fieldname, null, $varname);
							$thumb = '';
						} else if ($mod_image) {
							if (isset($row->image)) {
								$image	= unserialize($row->image);
								$src	= JURI::base(true) . '/' . $flexiparams->get('file_path') . '/' . $image['originalname'];
	
								$h		= '&amp;h=' . $mod_height_feat;
								$w		= '&amp;w=' . $mod_width_feat;
								$aoe	= '&amp;aoe=1';
								$q		= '&amp;q=95';
								$zc		= $mod_method_feat ? '&amp;zc=' . $mod_method_feat : '';
								$conf	= $w . $h . $aoe . $q . $zc;
	
								$thumb 	= JURI::base().'components/com_flexicontent/librairies/phpthumb/phpThumb.php?src='.$src.$conf;
							} else {
								$thumb	= '';
							}
						} else {
							$articleimage = flexicontent_html::extractimagesrc($row);
							if ($articleimage) {
							  $src	= $articleimage;
	
								$h		= '&amp;h=' . $mod_height_feat;
								$w		= '&amp;w=' . $mod_width_feat;
								$aoe	= '&amp;aoe=1';
								$q		= '&amp;q=95';
								$zc		= $mod_method_feat ? '&amp;zc=' . $mod_method_feat : '';
								$conf	= $w . $h . $aoe . $q . $zc;
	
	    					$base_url = (!preg_match("#^http|^https|^ftp#i", $src)) ?  JURI::base(true).'/' : '';
	    					$thumb = JURI::base().'components/com_flexicontent/librairies/phpthumb/phpThumb.php?src='.$base_url.$src.$conf;
			    		} else {
			    		  $thumb = '';
			    		}
						}
					}
					$lists[$ord]['featured'][$i]->id = $row->id;
					//date
					if ($display_date_feat == 1) {
						$dateformat = JText::_($params->get('date_format_feat', 'DATE_FORMAT_LC3'));
						if($dateformat == JText::_('custom'))
							$dateformat = $params->get('custom_date_format_feat', JText::_('DATE_FORMAT_LC3'));
						
						$date_fields_feat = $params->get('date_fields_feat', array());
	 			  	$date_fields_feat = !is_array($date_fields_feat) ? array($date_fields_feat) : $date_fields_feat;
	 			  	$lists[$ord]['featured'][$i]->date_created = "";
						if (in_array(1,$date_fields_feat)) { // Created
							$lists[$ord]['featured'][$i]->date_created .= $params->get('date_label_feat',1) ? '<span class="date_label_feat">'.JText::_('FLEXI_DATE_CREATED').':</span> ' : '';
							$lists[$ord]['featured'][$i]->date_created .= '<span class="date_value_feat">' . JHTML::_('date', $row->created, $dateformat) . '</span>';
						}
	 			  	$lists[$ord]['featured'][$i]->date_modified = "";
						if (in_array(2,$date_fields_feat)) { // Modified
							$lists[$ord]['featured'][$i]->date_modified .= $params->get('date_label_feat',1) ? '<span class="date_label_feat">'.JText::_('FLEXI_DATE_MODIFIED').':</span> ' : '';
							$modified_date = ($row->modified != $db->getNullDate()) ? JHTML::_('date', $row->modified, $dateformat) : JText::_( 'FLEXI_DATE_NEVER' );
							$lists[$ord]['featured'][$i]->date_modified .= '<span class="date_value_feat">' . $modified_date . '</span>';
						}
					}
					$lists[$ord]['featured'][$i]->image_rendered 	= $thumb_rendered;
					$lists[$ord]['featured'][$i]->image 	= $thumb;
					$lists[$ord]['featured'][$i]->hits = $row->hits;
					$lists[$ord]['featured'][$i]->link 		= JRoute::_(FlexicontentHelperRoute::getItemRoute($row->slug, $row->categoryslug/*, $forced_itemid*/).(($method_curlang == 1) ? "&lang=".substr($row->language ,0,2) : ""));
					$lists[$ord]['featured'][$i]->title 	= flexicontent_html::striptagsandcut($row->title, $cuttitle_feat);
					$lists[$ord]['featured'][$i]->fulltitle = $row->title;
					$lists[$ord]['featured'][$i]->text = ($mod_do_stripcat_feat)? flexicontent_html::striptagsandcut($row->introtext, $mod_cut_text_feat) : $row->introtext;
					$lists[$ord]['featured'][$i]->typename 	= $row->typename;
					$lists[$ord]['featured'][$i]->access 	= $row->access;
					$lists[$ord]['featured'][$i]->featured 	= 1;
					
					if ($use_fields_feat && $row->fields && $fields_feat) {
						foreach ($fields_feat as $field) {
							if ($display_label_feat) {
								$lists[$ord]['featured'][$i]->fields[$field]->label = @$row->fields[$field]->label ? $row->fields[$field]->label : '';
							}
							$lists[$ord]['featured'][$i]->fields[$field]->display 	= @$row->fields[$field]->display ? $row->fields[$field]->display : '';
						}
					}
					
					$i++;
				} else {
					// image processing
					$thumb = '';
					$thumb_rendered = '';
					if ($mod_use_image) {
						if ($mod_image_custom_source) {
							list($fieldname, $varname) = preg_split('/##/',$mod_image_custom_source);
							$fieldname = trim($fieldname); $varname = trim($varname);
							$thumb_rendered = FlexicontentFields::getFieldDisplay($row, $fieldname, null, $varname);
							$thumb = '';
						} else if ($mod_image) {
							if (isset($row->image)) {
								$image	= unserialize($row->image);
								$src	= JURI::base(true) . '/' . $flexiparams->get('file_path') . '/' . $image['originalname'];
	
								$h		= '&amp;h=' . $mod_height;
								$w		= '&amp;w=' . $mod_width;
								$aoe	= '&amp;aoe=1';
								$q		= '&amp;q=95';
								$zc		= $mod_method ? '&amp;zc=' . $mod_method : '';
								$conf	= $w . $h . $aoe . $q . $zc;
	
								$thumb 	= JURI::base().'components/com_flexicontent/librairies/phpthumb/phpThumb.php?src='.$src.$conf;
							} else {
								$thumb	= '';
							}
						} else {
							$articleimage = flexicontent_html::extractimagesrc($row);
							if ($articleimage) {
							  $src	= $articleimage;
	
								$h		= '&amp;h=' . $mod_height;
								$w		= '&amp;w=' . $mod_width;
								$aoe	= '&amp;aoe=1';
								$q		= '&amp;q=95';
								$zc		= $mod_method ? '&amp;zc=' . $mod_method : '';
								$conf	= $w . $h . $aoe . $q . $zc;
	
	    					$base_url = (!preg_match("#^http|^https|^ftp#i", $src)) ?  JURI::base(true).'/' : '';
	    					$thumb = JURI::base().'components/com_flexicontent/librairies/phpthumb/phpThumb.php?src='.$base_url.$src.$conf;
			    		} else {
			    		  $thumb = '';
			    		}
						}
					}
					$lists[$ord]['standard'][$i]->id = $row->id;
					//date
					if ($display_date == 1) {
						$dateformat = JText::_($params->get('date_format', 'DATE_FORMAT_LC3'));
						if($dateformat == JText::_('custom'))
							$dateformat = $params->get('custom_date_format', JText::_('DATE_FORMAT_LC3'));
							
						$date_fields = $params->get('date_fields', array());
	 			  	$date_fields = !is_array($date_fields) ? array($date_fields) : $date_fields;
	 			  	$lists[$ord]['standard'][$i]->date_created = "";
						if (in_array(1,$date_fields)) { // Created
							$lists[$ord]['standard'][$i]->date_created .= $params->get('date_label',1) ? '<span class="date_label">'.JText::_('FLEXI_DATE_CREATED').':</span> ' : '';
							$lists[$ord]['standard'][$i]->date_created .= '<span class="date_value">' . JHTML::_('date', $row->created, $dateformat) . '</span>';
						}
	 			  	$lists[$ord]['standard'][$i]->date_modified = "";
						if (in_array(2,$date_fields)) { // Modified
							$lists[$ord]['standard'][$i]->date_modified .= $params->get('date_label',1) ? '<span class="date_label">'.JText::_('FLEXI_DATE_MODIFIED').':</span> ' : '';
							$modified_date = ($row->modified != $db->getNullDate()) ? JHTML::_('date', $row->modified, $dateformat) : JText::_( 'FLEXI_DATE_NEVER' );
							$lists[$ord]['standard'][$i]->date_modified .= '<span class="date_value_feat">' . $modified_date . '</span>';
						}
					}
					$lists[$ord]['standard'][$i]->image_rendered 	= $thumb_rendered;
					$lists[$ord]['standard'][$i]->image 	= $thumb;
					$lists[$ord]['standard'][$i]->hits = $row->hits;
					$lists[$ord]['standard'][$i]->link 		= JRoute::_(FlexicontentHelperRoute::getItemRoute($row->slug, $row->categoryslug/*, $forced_itemid*/).(($method_curlang == 1) ? "&lang=".substr($row->language ,0,2) : ""));
					$lists[$ord]['standard'][$i]->title 	= flexicontent_html::striptagsandcut($row->title, $cuttitle);
					$lists[$ord]['standard'][$i]->fulltitle = $row->title;
					$lists[$ord]['standard'][$i]->text = ($mod_do_stripcat)? flexicontent_html::striptagsandcut($row->introtext, $mod_cut_text) : $row->introtext;
					$lists[$ord]['standard'][$i]->typename 	= $row->typename;
					$lists[$ord]['standard'][$i]->access 	= $row->access;
					$lists[$ord]['standard'][$i]->featured 	= 0;
	
					if ($use_fields && $row->fields && $fields) {
						foreach ($fields as $field) {
							if ($display_label) {
								$lists[$ord]['standard'][$i]->fields[$field]->label = @$row->fields[$field]->label ? $row->fields[$field]->label : '';
							}
							$lists[$ord]['standard'][$i]->fields[$field]->display 	= @$row->fields[$field]->display ? $row->fields[$field]->display : '';
						}
					}
	
					$i++;
				}
			}
			$lists_arr[] = $lists;
		}
		return $lists_arr;
	}

	function getItems(&$params, $ordering)
	{
		global $dump, $globalcats;

		$mainframe = &JFactory::getApplication();
		JPluginHelper::importPlugin('system', 'flexisystem');

		// For specific cache issues
		if (empty($globalcats)) {
			if (FLEXI_SECTION || FLEXI_CAT_EXTENSION) {
				if (FLEXI_CACHE) {
					// add the category tree to categories cache
					$catscache 	=& JFactory::getCache('com_flexicontent_cats');
					$catscache->setCaching(1); 		//force cache
					$catscache->setLifeTime(84600); //set expiry to one day
			    $globalcats = $catscache->call(array('plgSystemFlexisystem', 'getCategoriesTree'));
				} else {
					$globalcats = plgSystemFlexisystem::getCategoriesTree();
				}
			}
		}

		// Initialize variables
		$db				=& JFactory::getDBO();
		$user			=& JFactory::getUser();
		$gid			= !FLEXI_J16GE ? (int)$user->get('aid')  :  max($user->getAuthorisedViewLevels());
		$now			= $mainframe->get('requestTime');
		$nullDate 		= $db->getNullDate();
		$view			= JRequest::getVar('view');
		$option			= JRequest::getVar('option');
		$fparams 		=& $mainframe->getParams('com_flexicontent');
		$show_noauth 	= $fparams->get('show_noauth', 0);
		
		// $display_category_data
		$apply_config_per_category = (int)$params->get('apply_config_per_category', 0);
		
		// current item scope parameters
		$method_curitem	= (int)$params->get('method_curitem', 1);
			
		// current language scope parameters
		$method_curlang	= (int)$params->get('method_curlang', 1);
		
		// current item scope parameters
		$method_curuserfavs = (int)$params->get('method_curuserfavs', 1);
		
		// categories scope parameters
		$method_cat 		= (int)$params->get('method_cat', 1);
		$catids 			= $params->get('catids', array());
		$behaviour_cat 		= $params->get('behaviour_cat', 0);
		$treeinclude 		= $params->get('treeinclude');

		// types scope parameters
		$method_types 		= (int)$params->get('method_types', 1);
		$types 				= $params->get('types');
		$behaviour_types 	= $params->get('behaviour_types', 0);

		// authors scope parameters
		$method_auth 		= (int)$params->get('method_auth', 1);
		$authors 			= trim($params->get('authors'));
		$behaviour_auth 	= $params->get('behaviour_auth');
		
		// items scope parameters
		$method_items 		= (int)$params->get('method_items', 1);
		$items	 			= trim($params->get('items'));
		$behaviour_items 	= $params->get('behaviour_items', 0);
		$excluded_tags = $params->get('excluded_tags', array());
		$excluded_tags = (!is_array($excluded_tags)) ? array($excluded_tags) : $excluded_tags;
		$relitems_fields = $params->get('relitems_fields', array());
		$relitems_fields = (!is_array($relitems_fields)) ? array($relitems_fields) : $relitems_fields;
		
		// tags scope parameters
		$method_tags = (int)$params->get('method_tags', 1);
		$tag_ids = $params->get('tag_ids', array());
		$tag_ids = (!is_array($tag_ids)) ? array($tag_ids) : $tag_ids ;

		// date scope parameters
		$date_type 			= (int)$params->get('date_type', 0);
		$bdate 				= $params->get('bdate', '');
		$edate 				= $params->get('edate', '');
		$behaviour_dates 	= $params->get('behaviour_dates', 0);
		$date_compare 		= $params->get('date_compare', 0);
		// Server date		
		$sdate 			= explode(' ', $now);
		$cdate 			= $sdate[0] . ' 00:00:00';
		// Set date comparators
		if ($date_type == 1) { // modified
			$comp = 'modified';
		} elseif ($date_type == 2) { // publish up
			$comp = 'publish_up';
		} else { // created
			$comp = 'created';
		}
		
		// get module fetching parameters
		if ($params->get('skip_items',0) ) {
		  $count = (int)$params->get('maxskipcount', 50);
		} else {
		  $count = (int)$params->get('count', 5);
		}

		// get module display parameters
		$mod_image 			= $params->get('mod_image');
		

		$where  = ' WHERE c.published = 1';
		$where .= FLEXI_J16GE ? '' : ' AND i.sectionid = ' . FLEXI_SECTION;
		$where .= ' AND i.state IN ( 1, -5 )';
		$where .= ' AND ( i.publish_up = '.$db->Quote($nullDate).' OR i.publish_up <= '.$db->Quote($now).' )';
		$where .= ' AND ( i.publish_down = '.$db->Quote($nullDate).' OR i.publish_down >= '.$db->Quote($now).' )';

		// filter by permissions
		if (!$show_noauth) {
			if (FLEXI_ACCESS && class_exists('FAccess')) {
				$readperms = FAccess::checkUserElementsAccess($user->gmid, 'read');
				if (isset($readperms['item']) && count($readperms['item']) ) {
					$where .= ' AND ( i.access <= '.$gid.' OR i.id IN ('.implode(",", $readperms['item']).') )';
				} else {
					$where .= ' AND i.access <= '.$gid;
				}
			} else {
				$where .= ' AND i.access <= '.$gid;
			}
		}

		// *** NON-STATIC behavior, get current item information ***
		if ( ($behaviour_cat || $behaviour_auth || $behaviour_items || $behaviour_types || $date_compare) && ((($option == 'com_flexicontent') && ($view == FLEXI_ITEMVIEW)) || (($option == 'com_nutrition') && ($view == 'frAliment'))) )  {
			// initialize variables
			$cid 		= JRequest::getInt('cid');
			$id			= JRequest::getInt('id');
			$Itemid		= JRequest::getInt('Itemid');
			
			$q 	 		= 'SELECT c.*, ie.type_id, ie.language, GROUP_CONCAT(ci.catid SEPARATOR ",") as itemcats FROM #__content as c'
						. ' LEFT JOIN #__flexicontent_items_ext AS ie on ie.item_id = c.id'
						. ' LEFT JOIN #__flexicontent_cats_item_relations AS ci on ci.itemid = c.id'
						. ' WHERE c.id = ' . $id
						. ' GROUP BY ci.itemid'
						;
			$db->setQuery($q);
			$curitem	= $db->loadObject();

			// Get item dates
			if ($date_type == 1) {
				$idate = $curitem->modified;
			} elseif ($date_type == 2) {
				$idate = $curitem->publish_up;
			} else {
				$idate = $curitem->created;			
			}
			$idate 	= explode(' ', $idate);
			$cdate 	= $idate[0] . ' 00:00:00';
			$curritemcats = $idate 	= explode(',', $curitem->itemcats);
		}


		// current item scope
		$currid			= JRequest::getInt('id');
  	if ($method_curitem == 1) { // exclude method  ---  exclude current item
		  $where .=  ' AND i.id <> ' . $currid;
		} else if ($method_curitem == 2) { // include method  ---  include current item ONLY
		  $where .=  ' AND i.id = ' . $currid;
		} else {
		  // All Items including current
		}

		// current language scope
		$lang = JRequest::getWord('lang', '' );
		if(empty($lang)){
			$langFactory= JFactory::getLanguage();
			$tagLang = $langFactory->getTag();
			//Well, the substr is not even required as flexi saves the Joomla language tag... so we could have kept the $tagLang tag variable directly.
			$lang = substr($tagLang ,0,2);
		}
		if ($method_curlang == 1) { // exclude method  ---  exclude items of current language
			$where .= ' AND ie.language NOT LIKE ' . $db->Quote( $lang .'%' );
		} else if ($method_curlang == 2) { // include method  ---  include items of current language ONLY
			$where .= ' AND ie.language LIKE ' . $db->Quote( $lang .'%' );
		} else {
		  // Items of any language
		}
		
		// current user favourites scope
		$curruserid = (int)$user->get('id');
  	if ($method_curuserfavs == 1) { // exclude method  ---  exclude currently logged user favourites
			$join_favs  = ' LEFT OUTER JOIN #__flexicontent_favourites AS f ON f.itemid = i.id AND f.userid = '.$curruserid;
			$where .= ' AND f.itemid IS NULL';
		} else if ($method_curuserfavs == 2) { // include method  ---  include currently logged user favourites
			$join_favs  = ' LEFT JOIN #__flexicontent_favourites AS f ON f.itemid = i.id';
			$where .= ' AND f.userid = '.$curruserid;
		} else {
		  // All Items regardless of being favoured by current user
		  $join_favs = '';
		}
		
		// categories scope
		if (!$behaviour_cat) {
			$catids_list		= is_array($catids) ? implode(',', $catids) : $catids;
			
			if (!$catids && $method_cat > 1) {
				// empty ignore and issue a warning
				echo "<b>WARNING:</b> Misconfigured category scope, select at least one category or set category scope to ALL<br/>";
			} else if ($method_cat == 2) { // exclude method
				if ($apply_config_per_category) {
					echo "<b>WARNING:</b> Misconfiguration warning, APPLY CONFIGURATION PER CATEGORY is possible only if CATEGORY SCOPE is set to either (a) INCLUDE(static selection of categories) or (b) CURRENT CATEGORY<br/>";
					return;
				}
				$where .= ' AND c.id NOT IN (' . $catids_list . ')';
			} else if ($method_cat == 3) { // include method
				if (!$apply_config_per_category) {
					$where .= ' AND c.id IN (' . $catids_list . ')';
				} else {
					$catids_arr		= is_array($catids) ? $catids : array($catids);
					foreach($catids_arr as $catid) $multiquery_cats[] = ' AND c.id = '.$catid;
				}
			}
		} else {
			if (($option != 'com_flexicontent') || ($view != FLEXI_ITEMVIEW)) {
				return;
			} else {	
				
				if ($behaviour_cat == 2) {
					echo "<b>WARNING:</b> Misconfiguration warning, APPLY CONFIGURATION PER CATEGORY is possible only if CATEGORY SCOPE is set to either (a) INCLUDE(static selection of categories) or (b) CURRENT CATEGORY<br/>";
					return;
				}
				
				$menus		=& JApplication::getMenu('site', array());
				$menu 		=& $menus->getItem($Itemid);
				$routecat	=  @$menu->query['cid'] ? $menu->query['cid'] : 0;
				$cid		= $cid ? $cid : ($routecat ? $routecat : $curitem->catid);

				switch ($treeinclude) {
					// current category only
					case 0: 
						$catids_arr = array($cid);
					break;
					case 1: // current category + children
						$catids_arr = $globalcats[$cid]->descendantsarray;
					break;
					case 2: // current category + parents
						$catids_arr = $globalcats[$cid]->ancestorsarray;
					break;
					case 3: // current category + children + parents
						$catids_arr = array_unique(array_merge($globalcats[$cid]->descendantsarray, $globalcats[$cid]->ancestorsarray));						
					break;
					case 4: // all item's categories
						$catids_arr = $curritemcats;
					break;
				}
				
				if ($behaviour_cat == 1) {
					if (!$apply_config_per_category)
						$where .= ' AND c.id IN (' . implode(',', $curritemcats) . ')';
					else
						foreach($catids_arr as $catid) $multiquery_cats[] = ' AND c.id = '.$catid;
				} else {
					$where .= ' AND c.id NOT IN (' . implode(',', $curritemcats) . ')';
				}
			}
		}

		// types scope
		if (!$behaviour_types) {
			$types		= is_array($types) ? implode(',', $types) : $types;
			if (!$types && $method_types > 1) {
				// empty ignore and issue a warning
				echo "<b>WARNING:</b> Misconfigured types scope, select at least one item type or set types scope to ALL<br/>";
			} else if ($method_types == 2) { // exclude method
				$where .= ' AND ie.type_id NOT IN (' . $types . ')';		
			} else if ($method_types == 3) { // include method
				$where .= ' AND ie.type_id IN (' . $types . ')';		
			}
		} else {
			if (($option != 'com_flexicontent') || ($view != FLEXI_ITEMVIEW)) {
				return;
			} else {
				if ($behaviour_types == 1) {
					$where .= ' AND ie.type_id = ' . (int)$curitem->type_id;		
				} else {
					$where .= ' AND ie.type_id <> ' . (int)$curitem->type_id;		
				}
			}
		}

		// author scope
		if (!$behaviour_auth) {
			if (!$authors && $method_auth > 1) {
				// empty ignore and issue a warning
				echo "<b>WARNING:</b> Misconfigured author scope, select at least one author or set author scope to ALL<br/>";
			} else if ($method_auth == 2) { // exclude method
				$where .= ' AND i.created_by NOT IN (' . $authors . ')';		
			} else if ($method_auth == 3) { // include method
				$where .= ' AND i.created_by IN (' . $authors . ')';		
			}
		} else {
			if (($option != 'com_flexicontent') || ($view != FLEXI_ITEMVIEW)) {
				return;
			} else {			
				if ($behaviour_types == 1) {
					$where .= ' AND i.created_by = ' . (int)$curitem->created_by;		
				} else {
					$where .= ' AND i.created_by <> ' . (int)$curitem->created_by;		
				}
			}
		}

		// items scope
		if (!$behaviour_items) {
			if (!$items && $method_items > 1) {
				// empty ignore and issue a warning
				echo "<b>WARNING:</b> Misconfigured items scope, select at least one item or set items scope to ALL<br/>";
			} else if ($method_items == 2) { // exclude method
				$where .= ' AND i.id NOT IN (' . $items . ')';		
			} else if ($method_items == 3) { // include method
				$where .= ' AND i.id IN (' . $items . ')';		
			}
		} else if ($behaviour_items==2) {
			if (($option == 'com_flexicontent') && ($view == FLEXI_ITEMVIEW)) {
				unset($related);
				if (count($relitems_fields)) {
					$where2 = (count($relitems_fields) > 1) ? ' AND field_id IN ('.implode(',', $relitems_fields).')' : ' AND field_id = '.$relitems_fields[0];
					
					// select the item ids that have the common tags
					$query3 = 'SELECT DISTINCT value' .
							' FROM #__flexicontent_fields_item_relations' .
							' WHERE item_id = '.(int) $id .
							$where2
							;
					$db->setQuery($query3);
					$related = $db->loadResultArray();
					$related = is_array($related) ? array_map( 'intval', $related ) : $related;
				}
								
				if (isset($related) && count($related)) {
					$where .= (count($related) > 1) ? ' AND i.id IN ('.implode(',', $related).')' : ' AND i.id = '.$related[0];
				} else {
					return;
				}
			} else {
				return;
			}
		} else if ($behaviour_items==1) {
			if (($option == 'com_flexicontent') && ($view == FLEXI_ITEMVIEW)) {
				// select the tags associated to the item
				$query2 = 'SELECT tid' .
						' FROM #__flexicontent_tags_item_relations' .
						' WHERE itemid = '.(int) $id;
				$db->setQuery($query2);
				$tags = $db->loadResultArray();
				$tags = array_diff($tags,$excluded_tags);
				
				unset($related);
				if ($tags) {
					$where2 = (count($tags) > 1) ? ' AND tid IN ('.implode(',', $tags).')' : ' AND tid = '.$tags[0];
					
					// select the item ids that have the common tags
					$query3 = 'SELECT DISTINCT itemid' .
							' FROM #__flexicontent_tags_item_relations' .
							' WHERE itemid <> '.(int) $id .
							$where2
							;
					$db->setQuery($query3);
					$related = $db->loadResultArray();
				}
								
				if (isset($related) && count($related)) {
					$where .= (count($related) > 1) ? ' AND i.id IN ('.implode(',', $related).')' : ' AND i.id = '.$related[0];
				} else {
					return;
				}
			/*
			** BOF adaptation spécifique pour le com_nutrition de Valérie
			*/
			} elseif (($option == 'com_nutrition') && ($view == 'frAliment')) {
				// select the tags associated to the aliment
				$query2 = 'SELECT tid' .
						' FROM #__nut_fr_tags_item_relations' .
						' WHERE alimentid = '.(int) $id;
				$db->setQuery($query2);
				$tags = $db->loadResultArray();
				
				unset($related);
				if ($tags) {
					$where2 = (count($tags) > 1) ? ' AND tid IN ('.implode(',', $tags).')' : ' AND tid = '.$tags[0];
					
					// select the tags associated to the item
					$query3 = 'SELECT DISTINCT itemid' .
							' FROM #__flexicontent_tags_item_relations' .
							' WHERE itemid <> '.(int) $id .
							$where2
							;
					$db->setQuery($query3);
					$related = $db->loadResultArray();
				}
								
				if (isset($related) && count($related)) {
					$where .= (count($related) > 1) ? ' AND i.id IN ('.implode(',', $related).')' : ' AND i.id = '.$related[0];
				} else {
					return;
				}
			/*
			** EOF adaptation spécifique pour le com_nutrition de Valérie
			*/
			} else {			
				return;
			}
		}
		
		// tags scope
		if ($method_tags > 1) {
			if (!count($tag_ids)) {
				// empty ignore and issue a warning
				echo "<b>WARNING:</b> Misconfigured tags scope, select at least one tag or set tags scope to ALL<br/>";
			} else {
				$where2 = (count($tag_ids) > 1) ? ' AND tid IN ('.implode(',', $tag_ids).')' : ' AND tid = '.$tag_ids[0];
				
				// retieve item ids using the providen tags
				$query3 = 'SELECT DISTINCT itemid' .
						' FROM #__flexicontent_tags_item_relations' .
						' WHERE 1=1 ' .
						$where2
						;
				$db->setQuery($query3);
				$tagged = $db->loadResultArray();
			}
			
			if (isset($tagged)) {
				if ($method_tags == 2) { // exclude method
					$where .= (count($tagged) > 1) ? ' AND i.id NOT IN ('.implode(',', $tagged).')' : ' AND i.id = '.$tagged[0];
				} else if ($method_tags == 3) { // include method
					$where .= (count($tagged) > 1) ? ' AND i.id IN ('.implode(',', $tagged).')' : ' AND i.id <> '.$tagged[0];
				}
			}
		}
		
		// date scope
		if (!$behaviour_dates) {
			
			if ($edate && !FLEXIUtilities::isSqlValidDate($edate)) {
				echo "<b>WARNING:</b> Misconfigured date scope, you have entered invalid -END- date:<br>(a) Enter a valid date via callendar OR <br>(b) leave blank OR <br>(c) choose (non-static behavior) and enter custom offset e.g. five days ago (be careful with space character): -5 d<br/>";
				$edate = '';
			} else if ($edate) {
				$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' <= '.$db->Quote($edate).' )';
			}
			
			if ($bdate && !FLEXIUtilities::isSqlValidDate($bdate)) {
				echo "<b>WARNING:</b> Misconfigured date scope, you have entered invalid -BEGIN- date:<br>(a) Enter a valid date via callendar OR <br>(b) leave blank OR <br>(c) choose (non-static behavior) and enter custom offset e.g. five days ago (be careful with space character): -5 d<br/>";
				$bdate = '';
			} else if ($bdate) {
				$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote($bdate).' )';
			}
			
		} else {
			if ( (($option != 'com_flexicontent') || ($view != FLEXI_ITEMVIEW)) && ($date_compare == 1) ) {
				return;
			} else {
				switch ($behaviour_dates) 
				{
					case '1' : // custom offset
						if ($edate) {
							$edate = explode(' ', $edate);
							if (count($edate)!=2)
								echo "<b>WARNING:</b> Misconfigured date scope, you have entered invalid -END- date:Custom offset is invalid e.g. in order to enter five days ago (be careful with space character) use: -5 d<br/>";
							else
								$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote(date_time::shift_dates($cdate, $edate[0], $edate[1])).' )';
						}
						if ($bdate) {
							$bdate = explode(' ', $bdate);
							if (count($bdate)!=2)
								echo "<b>WARNING:</b> Misconfigured date scope, you have entered invalid -BEGIN- date:Custom offset is invalid e.g. in order to enter five days ago (be careful with space character) use: -5 d<br/>";
							else
								$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote(date_time::shift_dates($cdate, $bdate[0], $bdate[1])).' )';
						}
					break;

					case '2' : // same month
						$cdate = explode(' ', $cdate);
						$cdate = explode('-', $cdate[0]);
						$cdate = $cdate[0].'-'.$cdate[1].'-01 00:00:00';

						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote(date_time::shift_dates($cdate, 1, 'm')).' )';
						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote($cdate).' )';
					break;

					case '3' : // same year
						$cdate = explode(' ', $cdate);
						$cdate = explode('-', $cdate[0]);
						$cdate = $cdate[0].'-01-01 00:00:00';

						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote(date_time::shift_dates($cdate, 1, 'Y')).' )';
						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote($cdate).' )';
					break;

					case '4' : // previous month
						$cdate = explode(' ', $cdate);
						$cdate = explode('-', $cdate[0]);
						$cdate = $cdate[0].'-'.$cdate[1].'-01 00:00:00';

						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote($cdate).' )';
						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote(date_time::shift_dates($cdate, -1, 'm')).' )';
					break;

					case '5' : // previous year
						$cdate = explode(' ', $cdate);
						$cdate = explode('-', $cdate[0]);
						$cdate = $cdate[0].'-01-01 00:00:00';

						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote($cdate).' )';
						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote(date_time::shift_dates($cdate, -1, 'Y')).' )';
					break;

					case '6' : // next month
						$cdate = explode(' ', $cdate);
						$cdate = explode('-', $cdate[0]);
						$cdate = $cdate[0].'-'.$cdate[1].'-01 00:00:00';

						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote(date_time::shift_dates($cdate, 2, 'm')).' )';
						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote(date_time::shift_dates($cdate, 1, 'm')).' )';
					break;

					case '7' : // next year
						$cdate = explode(' ', $cdate);
						$cdate = explode('-', $cdate[0]);
						$cdate = $cdate[0].'-01-01 00:00:00';

						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' < '.$db->Quote(date_time::shift_dates($cdate, 2, 'Y')).' )';
						$where .= ' AND ( i.'.$comp.' = '.$db->Quote($nullDate).' OR i.'.$comp.' >= '.$db->Quote(date_time::shift_dates($cdate, 1, 'Y')).' )';
					break;
				}
			}
		}
		
		// EXTRA select and join for special fields: --image--
		if ($mod_image) {
			$select_image 	= ' value AS image,';
			$join_image 	= '	LEFT JOIN #__flexicontent_fields_item_relations AS firel'
							. '	ON ( i.id = firel.item_id AND firel.valueorder = 1 AND firel.field_id = '.$mod_image.' )';
		} else {
			$select_image	= '';
			$join_image		= '';
		}

		switch ($ordering) {
			case 'popular':
				$orderby = ' ORDER BY i.hits DESC';
			break;
				
			case 'commented':
				// handle jcomments integration
				if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php')) {
					echo "jcomments not installed, you need jcomments to use 'Most commented' ordering.<br>\n";
					$query = "";  // prevents output of any items
					break;
				}
				
				$query 	= 'SELECT i.*, ie.type_id, ie.language, count(com.object_id) AS nr, ty.name AS typename,'
						. $select_image
						. ' CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug,'
						. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
						. ' FROM #__content AS i'
						. ' LEFT JOIN #__flexicontent_items_ext AS ie on ie.item_id = i.id'
						. ' LEFT JOIN #__flexicontent_types AS ty on ie.type_id = ty.id'
						. ' LEFT JOIN #__jcomments AS com ON com.object_id = i.id'
						. ' LEFT JOIN #__flexicontent_cats_item_relations AS rel ON rel.itemid = i.id'
						. ' LEFT JOIN #__categories AS c ON c.id = rel.catid'
						. $join_favs
						. $join_image
						. $where .' '. ($apply_config_per_category ? '__CID_WHERE__' : '')
						. ' AND com.object_group = ' . $db->Quote('com_flexicontent')
						. ' AND com.published = 1'
						. ' GROUP BY i.id'
						. ' ORDER BY nr DESC'
						;
				break;

			case 'rated':
				$query 	= 'SELECT i.*, (cr.rating_sum / cr.rating_count) * 20 AS votes, ie.type_id, ie.language, ty.name AS typename,'
						. $select_image
						. ' CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug,'
						. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
						. ' FROM #__content AS i'
						. ' LEFT JOIN #__flexicontent_items_ext AS ie on ie.item_id = i.id'
						. ' LEFT JOIN #__flexicontent_types AS ty on ie.type_id = ty.id'
						. ' INNER JOIN #__content_rating AS cr ON cr.content_id = i.id'
						. ' LEFT JOIN #__flexicontent_cats_item_relations AS rel ON rel.itemid = i.id'
						. ' LEFT JOIN #__categories AS c ON c.id = rel.catid'
						. $join_favs
						. $join_image
						. $where .' '. ($apply_config_per_category ? '__CID_WHERE__' : '')
						. ' GROUP BY i.id'
						. ' ORDER BY votes DESC'
						;
				break;
				
			case 'added':
				$orderby = ' ORDER BY i.created DESC';
				break;

			case 'updated':
				$orderby = ' ORDER BY i.modified DESC';
				break;

			case 'alpha':
				$orderby = ' ORDER BY i.title ASC';
				break;

			case 'alpharev':
				$orderby = ' ORDER BY i.title DESC';
				break;

			case 'catorder':
				$orderby = ' ORDER BY rel.ordering ASC';
				break;

			case 'random':
				$orderby = ' ORDER BY RAND()';
				break;
			default:
				$orderby = '';
		}
		
		if (!isset($query)) {
			$query 	= 'SELECT i.*, ie.type_id, ie.language, ty.name AS typename,'
					. $select_image
					. ' CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug,'
					. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
					. ' FROM #__content AS i'
					. ' LEFT JOIN #__flexicontent_items_ext AS ie on ie.item_id = i.id'
					. ' LEFT JOIN #__flexicontent_types AS ty on ie.type_id = ty.id'
					. ' LEFT JOIN #__flexicontent_cats_item_relations AS rel ON rel.itemid = i.id'
					. ' LEFT JOIN #__categories AS c ON c.id = rel.catid'
					. $join_favs
					. $join_image
					. $where .' '. ($apply_config_per_category ? '__CID_WHERE__' : '')
					. ' GROUP BY i.id'
					. $orderby
					;
		}
		
		if (!isset($multiquery_cats)) $multiquery_cats = array("");
		foreach($multiquery_cats as $cat_where) {
			$q = str_replace('__CID_WHERE__', $cat_where, $query);
			$db->setQuery($q, 0, $count);
			$rows = $db->loadObjectList();
			if ( $db->getErrorNum() ) {
				$jAp=& JFactory::getApplication();
				$jAp->enqueueMessage(nl2br($query."\n".$db->getErrorMsg()."\n"),'error');
			}
			$rows_arr[] = $rows;
		}

		return $rows_arr;
	}

	function getCategoryData(&$params) {
		if (!$params->get('apply_config_per_category', 0)) return false;
		
		$db =& JFactory::getDBO();
		$cid  = JRequest::getInt('cid', 0);
		$view = JRequest::getVar('view');
		$config 	=& JFactory::getConfig();
		$currcat_custom_display = $params->get('currcat_custom_display', 0);
		
		if ($currcat_custom_display) {
			$catconf = new stdClass();
			$catconf->fallback_maincat  = $params->get('currcat_fallback_maincat', 0);
			$catconf->showtitle  = $params->get('currcat_showtitle', 0);
			$catconf->showdescr  = $params->get('currcat_showdescr', 0);
			$catconf->cuttitle   = (int)$params->get('currcat_cuttitle', 40);
			$catconf->cutdescr   = (int)$params->get('currcat_cutdescr', 200);
			$catconf->link_title	= $params->get('currcat_link_title');
			
			$catconf->show_image 		= $params->get('currcat_show_image');
			$catconf->image_source	= $params->get('currcat_image_source');
			$catconf->link_image		= $params->get('currcat_link_image');
			$catconf->image_width		= (int)$params->get('currcat_image_width', 80);
			$catconf->image_height	= (int)$params->get('currcat_image_height', 80);
			$catconf->image_method	= (int)$params->get('currcat_image_method', 1);
			$catconf->show_default_image = (int)$params->get('currcat_show_default_image', 0);  // parameter not added yet
			$catconf->readmore	= (int)$params->get('currcat_currcat_readmore', 1);
			
			if ($catconf->display && $catconf->fallback_maincat && !$cid && $view==FLEXI_ITEMVIEW) {
				$q = 'SELECT catid FROM #__content WHERE id = ' . $id;
				$db->setQuery($q);
				$cid = $db->loadResult();
			}
			if ($cid) $cids = array($cid);
		}
		
		if (empty($cids)) {
			$catconf = new stdClass();
			$cids = $params->get('catids', array());
			$cids = (!is_array($cids)) ? array($cids) : $cids;
			$catconf->showtitle  = $params->get('cats_showtitle', 0);
			$catconf->showdescr  = $params->get('cats_showdescr', 0);
			$catconf->cuttitle   = (int)$params->get('cats_cuttitle', 40);
			$catconf->cutdescr   = (int)$params->get('cats_cutdescr', 200);
			$catconf->link_title	= $params->get('cats_link_title');
			
			$catconf->show_image 		= $params->get('cats_show_image');
			$catconf->image_source	= $params->get('cats_image_source');
			$catconf->link_image		= $params->get('cats_link_image');
			$catconf->image_width 	= (int)$params->get('cats_image_width', 80);
			$catconf->image_height 	= (int)$params->get('cats_image_height', 80);
			$catconf->image_method 	= (int)$params->get('cats_image_method', 1);
			$catconf->show_default_image = (int)$params->get('cats_show_default_image', 0);  // parameter not added yet
			$catconf->readmore	= (int)$params->get('cats_currcat_readmore', 1);
		}
		
		if (empty($cids) || !count($cids)) return false;
		
		// initialize variables
		$q = 'SELECT c.id, c.title, c.description, c.params '
					. ( FLEXI_J16GE ? '' : ', c.image ' )  // NO image column in J1.6 and higher, image is in parameters
					. ', CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
					. ' FROM #__categories AS c'
					. ' WHERE c.id IN (' . implode(',', $cids) . ')';
					;
		$db->setQuery($q);
		$catdata_arr = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			$jAp=& JFactory::getApplication();
			$jAp->enqueueMessage(nl2br($query."\n".$db->getErrorMsg()."\n"),'error');
			return false;
		}
		
		jimport( 'joomla.html.parameter' );
		$joomla_image_path 	= FLEXI_J16GE ? $config->getValue('config.image_path', '') : $config->getValue('config.image_path', 'images'.DS.'stories');
		foreach( $catdata_arr as $i => $catdata ) {
			$catdata->params = new JParameter($catdata->params);
			
			// Category Title
			$catdata->title = flexicontent_html::striptagsandcut($catdata->title, $catconf->cuttitle);
			$catdata->showtitle = $catconf->showtitle;
			
			// Category image
			$catdata->image = FLEXI_J16GE ? $catdata->params->get('image') : $catdata->image;
			$catimage = "";
			if ($catconf->show_image) {
				
				$catdata->introtext = & $catdata->description;
				$catdata->fulltext = "";
				
				if ( $catconf->image_source && $catdata->image && JFile::exists( JPATH_SITE .DS. $joomla_image_path .DS. $catdata->image ) ) {
					$src = JURI::base(true)."/".$joomla_image_path."/".$catdata->image;
			
					$h		= '&amp;h=' . $catconf->image_height;
					$w		= '&amp;w=' . $catconf->image_width;
					$aoe	= '&amp;aoe=1';
					$q		= '&amp;q=95';
					$zc		= $catconf->image_method ? '&amp;zc=' . $catconf->image_method : '';
					$conf	= $w . $h . $aoe . $q . $zc;
			
					$catimage = JURI::base().'components/com_flexicontent/librairies/phpthumb/phpThumb.php?src='.$src.$conf;
				} else if ( $catconf->image_source!=1 && $src = flexicontent_html::extractimagesrc($catdata) ) {
		
					$h		= '&amp;h=' . $catconf->image_height;
					$w		= '&amp;w=' . $catconf->image_width;
					$aoe	= '&amp;aoe=1';
					$q		= '&amp;q=95';
					$zc		= $catconf->image_method ? '&amp;zc=' . $catconf->image_method : '';
					$conf	= $w . $h . $aoe . $q . $zc;
		
					$base_url = (!preg_match("#^http|^https|^ftp#i", $src)) ?  JURI::base(true).'/' : '';
					$catimage = JURI::base().'components/com_flexicontent/librairies/phpthumb/phpThumb.php?src='.$base_url.$src.$conf;
				}
				
				$catdata->image = $catimage;
			}
  		
			// Category Description
			if (!$catconf->showdescr) {
				unset($catdata->description);
			} else {
				$catdata->description = flexicontent_html::striptagsandcut($catdata->description, $catconf->cutdescr);
			}
			
			
			// Category Links (title and image links)
			if ($catconf->link_title || $catconf->link_image) {
				$catlink = JRoute::_(FlexicontentHelperRoute::getCategoryRoute($catdata->categoryslug));
				if ($catconf->link_title) $catdata->titlelink = $catlink;
				if ($catconf->link_image) $catdata->imagelink = $catlink;
			}
			
			$catdata_arr[$i] = $catdata;
			$catdata_arr[$i]->conf = $catconf;
		}
		
		return $catdata_arr;
	}
}
