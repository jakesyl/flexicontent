<?php
/**
 * @version 1.5 stable $Id: uninstall.php 517 2011-03-22 01:37:54Z emmanuel.danan@gmail.com $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * Original uninstall.php file
 * @package   Zoo Component
 * @author    YOOtheme http://www.yootheme.com
 * @copyright Copyright (C) 2007 - 2009 YOOtheme GmbH
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
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

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Executes additional uninstallation processes
 *
 * @since 1.0
 */
// init vars
$error = false;
$extensions = array();
$db =& JFactory::getDBO();

// Restore com_content component asset, as asset parent_id, for the top-level 'com_content' categories
$asset	= JTable::getInstance('asset');
$asset_loaded = $asset->loadByName('com_content');  // Try to load component asset for com_content
if (!$asset_loaded) {
	echo '<span style="font-weight: bold; color: darkred;" >Failed to load asset for com_content</span><br/>';
} else {
	$query = 'UPDATE #__assets AS s'
		.' JOIN #__categories AS c ON s.id=c.asset_id'
		.' SET s.parent_id='.$db->Quote($asset->id)
		.' WHERE c.parent_id=1 AND c.extension="com_content"';
	$db->setQuery($query);
	$db->query();
	if ($db->getErrorNum()) {
		echo $db->getErrorMsg();
		echo '<span style="font-weight: bold; color: darkred;" >Failed to load asset for com_content</span><br/>';
	} else {
		echo '<span style="font-weight: bold; color: darkgreen;" >Restored parent asset for top level categories</span><br/>';
	}
}



// additional extensions
$add_array =& $this->manifest->xpath('additional');
$add = NULL;
if(count($add_array)) $add = $add_array[0];
if ( is_a($add, 'JXMLElement') && count($add->children()) )
{
	$exts =& $add->children();
	foreach ($exts as $ext)
	{
		// set query
		switch ($ext->name()) {
			case 'plugin':
				$attribute_name = $ext->getAttribute('name');
				if( $ext->getAttribute('instfolder') ) {
					$query = 'SELECT * FROM #__extensions'
						.' WHERE type='.$db->Quote($ext->name())
						.'  AND element='.$db->Quote($ext->getAttribute('name'))
						.'  AND folder='.$db->Quote($ext->getAttribute('instfolder'));
					// query extension id and client id
					$db->setQuery($query);
					$res = $db->loadObject();

					$extensions[] = array(
						'name' => $ext->data(),
						'type' => $ext->name(),
						'id' => isset($res->extension_id) ? $res->extension_id : 0,
						'client_id' => isset($res->client_id) ? $res->client_id : 0,
						'installer' => new JInstaller(),
						'status' => false);
				}
				break;
			case 'module':
				$query = 'SELECT * FROM #__extensions WHERE type='.$db->Quote($ext->name()).' AND element='.$db->Quote($ext->getAttribute('name'));
				// query extension id and client id
				$db->setQuery($query);
				$res = $db->loadObject();
				$extensions[] = array(
					'name' => $ext->data(),
					'type' => $ext->name(),
					'id' => isset($res->extension_id) ? $res->extension_id : 0,
					'client_id' => isset($res->client_id) ? $res->client_id : 0,
					'installer' => new JInstaller(),
					'status' => false);
				break;
		}
	}
}

// uninstall additional extensions
for ($i = 0; $i < count($extensions); $i++) {
	$extension =& $extensions[$i];
	
	if ($extension['id'] > 0 && $extension['installer']->uninstall($extension['type'], $extension['id'], $extension['client_id'])) {
		$extension['status'] = true;
	}
}

?>
<h3><?php echo JText::_('Additional Extensions'); ?></h3>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('Extension'); ?></th>
			<th width="60%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach ($extensions as $i => $ext) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="key"><?php echo $ext['name']; ?> (<?php echo JText::_($ext['type']); ?>)</td>
				<td>
					<?php $style = $ext['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
					<span style="<?php echo $style; ?>"><?php echo $ext['status'] ? JText::_('Uninstalled successfully') : JText::_('Uninstall FAILED'); ?></span>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
