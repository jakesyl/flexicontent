<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2011 flexicontent.org
 * @license GNU/GPL v3
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
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'plgSearchFlexiadvsearch' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchFlexiadvsearchAreas' );

//Load the Plugin language file out of the administration
JPlugin::loadLanguage( 'plg_search_flexisearch', JPATH_ADMINISTRATOR);

/**
 * @return array An array of search areas
 */
function &plgSearchFlexiadvsearchAreas() {
	static $areas = array(
	'flexiadvsearch' => 'Content Advanced Search'
	);
	return $areas;
}

/**
 * Search method
 *
 * The sql must return the following fields that are
 * used in a common display routine: href, title, section, created, text,
 * browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if restricted to areas, null if search all
 */
function plgSearchFlexiadvsearch( $text, $phrase='', $ordering='', $areas=null )
{
	$mainframe = &JFactory::getApplication();

	$db		= & JFactory::getDBO();
	$user	= & JFactory::getUser();
	$gid	= (int) $user->get('aid');
	// Get the WHERE and ORDER BY clauses for the query
	$params 	= & $mainframe->getParams('com_flexicontent');
	$typeid_for_advsearch = $params->get('typeid_for_advsearch');
	$dispatcher =& JDispatcher::getInstance();

	// define section
	if (!defined('FLEXI_SECTION')) define('FLEXI_SECTION', $params->get('flexi_section'));

	// show unauthorized items
	$show_noauth = $params->get('show_noauth', 0);

	require_once(JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'helpers'.DS.'route.php');
	require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchFlexiadvsearchAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
	$plugin =& JPluginHelper::getPlugin('search', 'flexiadvsearch');
	$pluginParams = new JParameter( $plugin->params );

	// shortcode of the site active language (joomfish)
	$lang 		= JRequest::getWord('lang', '' );

	$limit 			= $pluginParams->def( 'search_limit', 50 );
	$filter_lang 	= $pluginParams->def( 'filter_lang', 1 );

	// Dates for publish up & down items
	$nullDate = $db->getNullDate();
	$date =& JFactory::getDate();
	$now = $date->toMySQL();

	$text = trim( $text );
	/*if ( $text == '' ) {
		return array();
	}*/
	$searchFlexicontent = JText::_( 'FLEXICONTENT' );
	if($text!='') {
		$text = $db->getEscaped($text);
		switch ($phrase) {
			case 'exact':
				//$text		= $db->Quote( '"'.$db->getEscaped( $text, true ).'"', false );
				$text		= $db->Quote( '"'.$db->getEscaped( $text, false ).'"', false );
				$where	 	= ' MATCH (ie.search_index) AGAINST ('.$text.' IN BOOLEAN MODE)';
				break;

			case 'all':
				$words = explode( ' ', $text );
				$newtext = '+' . implode( ' +', $words );
				//$text		= $db->Quote( $db->getEscaped( $newtext, true ), false );
				$text		= $db->Quote( $db->getEscaped( $newtext, false ), false );
				$where	 	= ' MATCH (ie.search_index) AGAINST ('.$text.' IN BOOLEAN MODE)';
				break;
			case 'any':
			default:
				//$text		= $db->Quote( $db->getEscaped( $text, true ), false );
				$text		= $db->Quote( $db->getEscaped( $text, false ), false );
				$where	 	= ' MATCH (ie.search_index) AGAINST ('.$text.' IN BOOLEAN MODE)';
				break;
		}
	}else $where = '0';

	switch ( $ordering ) {
		case 'oldest':
			$order = 'a.created ASC';
			break;

		case 'popular':
			$order = 'a.hits DESC';
			break;

		case 'alpha':
			$order = 'a.title ASC';
			break;

		case 'category':
			$order = 'c.title ASC, a.title ASC';
			break;

		case 'newest':
		default:
			$order = 'a.created DESC';
			break;
	}
	
	// Select only items user has access to if he is not allowed to show unauthorized items
	$joinaccess	= '';
	$andaccess	= '';
	if (!$show_noauth) {
		if (FLEXI_ACCESS) {
			$joinaccess .= ' LEFT JOIN #__flexiaccess_acl AS gc ON c.id = gc.axo AND gc.aco = "read" AND gc.axosection = "category"';
			$joinaccess .= ' LEFT JOIN #__flexiaccess_acl AS gi ON a.id = gi.axo AND gi.aco = "read" AND gi.axosection = "item"';
			$andaccess	.= ' AND (gc.aro IN ( '.$user->gmid.' ) OR c.access <= '. (int) $gid . ')';
			$andaccess  .= ' AND (gi.aro IN ( '.$user->gmid.' ) OR a.access <= '. (int) $gid . ')';
		} else {
			$andaccess  .= ' AND c.access <= '.$gid;
			$andaccess  .= ' AND a.access <= '.$gid;
		}
	}

	// filter by active language
	$andlang = '';
	if (FLEXI_FISH && $filter_lang) {
		$andlang .= ' AND ie.language LIKE ' . $db->Quote( $lang .'%' );
	}

	$query = "SELECT f.id,f.field_type,f.name,f.label,fir.value,fir.item_id"
		." FROM #__flexicontent_fields as f "
		." JOIN #__flexicontent_fields_type_relations as ftr ON f.id=ftr.field_id"
		." LEFT JOIN #__flexicontent_fields_item_relations as fir ON f.id=fir.field_id"
		." WHERE f.published='1' AND f.isadvsearch='1' AND ftr.type_id='{$typeid_for_advsearch}'"
	;
	$db->setQuery($query);
	$fields = $db->loadObjectList();
	$fields = is_array($fields)?$fields:array();
	$CONDITION = '';
	$OPERATOR = JRequest::getVar('operator', 'OR');
	$items = array();
	$resultfields = array();
	foreach($fields as $field) {
		if($field->item_id) {
			$fieldsearch = JRequest::getVar($field->name, array());
			//var_dump($field->name, $_REQUEST[$field->name]);
			//echo "<br />";
			//$fieldsearch = $mainframe->getUserStateFromRequest( 'flexicontent.serch.'.$field->name, $field->name, array(), 'array' );
			if(isset($fieldsearch[0]) && trim($fieldsearch[0])) {
				$fieldsearch = $fieldsearch[0];
				$fieldsearch = explode(" ", $fieldsearch);
				foreach($fieldsearch as $fsearch) {
					if((stristr($field->value, $fsearch)!== FALSE)) {
						$items[] = $field->item_id;
						$obj = new stdClass;
						$obj->label = $field->label;
						$obj->value = $field->value;
						$resultfields[$field->item_id][] = $obj;
						break;
					}
				}
				$results = $dispatcher->trigger( 'onFLEXIAdvSearch', array(&$field, $field->item_id, $fieldsearch));
				if(count($results)>0) {
					foreach($results as $r) {
						if($r) {
							$items[] = $field->item_id;
							$resultfields[$field->item_id] = $r;
						}
					}
				}
			}
		}
	}
	if(count($items)) {
		$items = array_unique($items);
		$items = "'".implode("','", $items)."'";
		$CONDITION = " {$OPERATOR} a.id IN ({$items}) ";
	}
	$query 	= 'SELECT DISTINCT a.id,a.title AS title, a.sectionid,'
		. ' a.created AS created,'
		. ' ie.search_index AS text,'
		. ' "2" AS browsernav,'
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'
		. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug,'
		. ' CONCAT_WS( " / ", '. $db->Quote($searchFlexicontent) .', c.title, a.title ) AS section'
		. ' FROM #__content AS a'
		. ' LEFT JOIN #__flexicontent_items_ext AS ie ON ie.item_id = a.id'
		. ' LEFT JOIN #__categories AS c ON c.id = a.catid'
		. $joinaccess
		. ' WHERE '
		. '('
		. '( '.$where.' )'
		. $CONDITION
		. ')'
		. ' AND a.state IN (1, -5)'
		. ' AND c.published = 1'
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		. $andaccess
		. $andlang
		. ' ORDER BY '. $order
	;
	$db->setQuery( $query, 0, $limit );
	$list = $db->loadObjectList();
	if(isset($list)) {
		foreach($list as $key => $row) {
			if($row->sectionid==FLEXI_SECTION) {
				if(isset($resultfields[$row->id]))
					foreach($resultfields[$row->id] as $r)
						$list[$key]->text .= "[br /]".$r->label.":[span=highlight]".$r->value."[/span]";
				$list[$key]->href = JRoute::_(FlexicontentHelperRoute::getItemRoute($row->slug, $row->categoryslug));
			}else
				$list[$key]->href = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
		}
	}
	return $list;
}
?>