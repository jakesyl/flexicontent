<?php
/**
 * @version 1.5 beta 5 $Id: view.html.php 183 2009-11-18 10:30:48Z vistamedia $
 * @package Joomla
 * @subpackage FLEXIcontent
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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Stats View
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */
class FlexicontentViewStats extends JView
{
	/**
	 * Creates the Entrypage
	 *
	 * @since 1.0
	 */
	function display( $tpl = null )
	{
		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= & JFactory::getDocument();
		$pane   	= & JPane::getInstance('Tabs');
		//$user 		= & JFactory::getUser();
		
		// Get data from the model
		$genstats 	= & $this->get( 'Generalstats' );
		$popular	= & $this->get( 'Popular' );
		$rating		= & $this->get( 'Rating' );
		$worstrating= & $this->get( 'WorstRating' );
		$favoured	= & $this->get( 'Favoured' );
		$statestats	= & $this->get( 'Statestats' );
		$votesstats	= & $this->get( 'Votesstats' );
		$creators	= & $this->get( 'Creators' );
		$editors	= & $this->get( 'Editors' );

		//build toolbar
		JToolBarHelper::title( JText::_( 'FLEXI_STATISTICS' ), 'stats' );
		JToolBarHelper::Back();

		//add css and submenu to document
		$document->addStyleSheet('components/com_flexicontent/assets/css/flexicontentbackend.css');
		
		if (FLEXI_ACCESS) {
			$user =& JFactory::getUser();
			$CanCats 		= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'categories', 'users', $user->gmid) : 1;
			$CanTypes 		= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'types', 'users', $user->gmid) : 1;
			$CanFields 		= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'fields', 'users', $user->gmid) : 1;
			$CanTags 		= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'tags', 'users', $user->gmid) : 1;
			$CanArchives 	= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'archives', 'users', $user->gmid) : 1;
			$CanFiles	 	= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'files', 'users', $user->gmid) : 1;
			$CanStats	 	= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'stats', 'users', $user->gmid) : 1;
			$CanRights	 	= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexiaccess', 'manage', 'users', $user->gmid) : 1;
			$CanTemplates	= ($user->gid < 25) ? FAccess::checkComponentAccess('com_flexicontent', 'templates', 'users', $user->gmid) : 1;
		} else {
			$CanCats 		= 1;
			$CanTypes 		= 1;
			$CanFields		= 1;
			$CanTags 		= 1;
			$CanArchives	= 1;
			$CanFiles		= 1;
			$CanStats		= 1;
			$CanRights		= 1;
			$CanTemplates	= 1;
		}

		if (!$CanStats) {
			$mainframe->redirect('index.php?option=com_flexicontent', JText::_( 'FLEXI_NO_ACCESS' ));
		}
		
		//Create Submenu
		JSubMenuHelper::addEntry( JText::_( 'FLEXI_HOME' ), 'index.php?option=com_flexicontent');
		JSubMenuHelper::addEntry( JText::_( 'FLEXI_ITEMS' ), 'index.php?option=com_flexicontent&view=items');
		if ($CanTypes)		JSubMenuHelper::addEntry( JText::_( 'FLEXI_TYPES' ), 'index.php?option=com_flexicontent&view=types');
		if ($CanCats) 		JSubMenuHelper::addEntry( JText::_( 'FLEXI_CATEGORIES' ), 'index.php?option=com_flexicontent&view=categories');
		if ($CanFields) 	JSubMenuHelper::addEntry( JText::_( 'FLEXI_FIELDS' ), 'index.php?option=com_flexicontent&view=fields');
		if ($CanTags) 		JSubMenuHelper::addEntry( JText::_( 'FLEXI_TAGS' ), 'index.php?option=com_flexicontent&view=tags');
		if ($CanArchives) 	JSubMenuHelper::addEntry( JText::_( 'FLEXI_ARCHIVE' ), 'index.php?option=com_flexicontent&view=archive');
		if ($CanFiles) 		JSubMenuHelper::addEntry( JText::_( 'FLEXI_FILEMANAGER' ), 'index.php?option=com_flexicontent&view=filemanager');
		if ($CanTemplates) 	JSubMenuHelper::addEntry( JText::_( 'FLEXI_TEMPLATES' ), 'index.php?option=com_flexicontent&view=templates');
		if ($CanStats)		JSubMenuHelper::addEntry( JText::_( 'FLEXI_STATISTICS' ), 'index.php?option=com_flexicontent&view=stats', true);

		$this->assignRef('pane'			, $pane);
		$this->assignRef('genstats'		, $genstats);
		$this->assignRef('popular'		, $popular);
		$this->assignRef('rating'		, $rating);
		$this->assignRef('worstrating'	, $worstrating);
		$this->assignRef('favoured'		, $favoured);
		$this->assignRef('statestats'	, $statestats);
		$this->assignRef('votesstats'	, $votesstats);
		$this->assignRef('creators'		, $creators);
		$this->assignRef('editors'		, $editors);

		parent::display($tpl);
	}
	
	/**
	 * Creates the buttons view
	 *
	 * @param string $link targeturl
	 * @param string $image path to image
	 * @param string $text image description
	 * @param boolean $modal 1 for loading in modal
	 */
	function quickiconButton( $link, $image, $text, $modal = 0 )
	{
		//initialise variables
		$lang 		= & JFactory::getLanguage();
  		?>

		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<?php
				if ($modal == 1) {
					JHTML::_('behavior.modal');
				?>
					<a href="<?php echo $link.'&amp;tmpl=component'; ?>" style="cursor:pointer" class="modal" rel="{handler: 'iframe', size: {x: 650, y: 400}}">
				<?php
				} else {
				?>
					<a href="<?php echo $link; ?>">
				<?php
				}

					echo JHTML::_('image', 'administrator/components/com_flexicontent/assets/images/'.$image, $text );
				?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
}