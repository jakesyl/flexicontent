<?php
/**
 * @version 1.5 stable $Id: category.php 171 2010-03-20 00:44:02Z emmanuel.danan $
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

/**
 * FLEXIcontent Component Category Model
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since		1.0
 */
class FlexicontentModelCategory extends JModelAdmin
{
	/**
	 * Category data
	 *
	 * @var object
	 */
	var $_category = null;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int category identifier
	 */
	function setId($id)
	{
		// Set category id and wipe data
		$this->_id	    	= $id;
		$this->_category	= null;
	}
	
	/**
	 * Overridden get method to get properties from the category
	 *
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return 	mixed 				The value of the property
	 * @since	1.0
	 */
	function get($property, $default=null)
	{
		if ($this->_loadCategory()) {
			if(isset($this->_category->$property)) {
				return $this->_category->$property;
			}
		}
		return $default;
	}

	/**
	 * Method to get category data
	 *
	 * @access	public
	 * @return	array
	 * @since	1.0
	 */
	function &getCategory()
	{
		if ($this->_loadCategory())
		{

		}
		else  $this->_initCategory();

		return $this->_category;
	}

	/**
	 * Method to load category data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function _loadCategory()
	{
		// Lets load the category if it doesn't already exist
		if (empty($this->_category))
		{
			$query = 'SELECT *'
					. ' FROM #__categories'
					. ' WHERE id = '.$this->_id
					;
			$this->_db->setQuery($query);
			$this->_category = $this->_db->loadObject();

			return (boolean) $this->_category;
		}
		return true;
	}

	/**
	 * Method to initialise the category data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function _initCategory()
	{
		// Lets load the category if it doesn't already exist
		if (empty($this->_category))
		{
			$category = new stdClass();
			$category->id					= 0;
			$category->parent_id			= 0;
			$category->title				= null;
			$category->name					= null;
			$category->alias				= null;
			//$category->image				= JText::_( 'FLEXI_CHOOSE_IMAGE' );
			$category->section				= FLEXI_CATEGORY;
			$category->image_position		= 'left';
			$category->description			= null;
			$category->published			= 1;
			$category->editor				= null;
			$category->ordering				= 0;
			$category->access				= 0;
			$category->params				= null;
			$category->count				= 0;
			$this->_category				= $category;
			return (boolean) $this->_category;
		}
		return true;
	}

	/**
	 * Method to checkin/unlock the category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function checkin()
	{
		if ($this->_id) {
			$category = $this->getTable();
			return $category->checkin($this->_id);
		}
		return false;
	}

	/**
	 * Method to checkout/lock the category
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the item out
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the category with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$category = $this->getTable();
			if(!$category->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			return true;
		}
		return false;
	}

	/**
	 * Tests if the category is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.0
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadCategory())
		{
			if ($uid) {
				return ($this->_category->checked_out && $this->_category->checked_out != $uid);
			} else {
				return $this->_category->checked_out;
			}
		} elseif ($this->_id < 1) {
			return false;
		} else {
			JError::raiseWarning( 0, 'UNABLE LOAD DATA');
			return false;
		}
	}

	/**
	 * Method to store the category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function store($data)
	{
		$pk		= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew	= true;
		
		$category = $this->getTable();
		
		// Load the row if saving an existing category.
		if ($pk > 0) {
			$category->load($pk);
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($category->parent_id != $data['jform']['parent_id'] || $data['id'] == 0) {
			$category->setLocation($data['jform']['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if (!$isNew && $data['id'] == 0 && $category->parent_id == $data['parent_id']) {
			$m = null;
			$data['alias'] = '';
			if (preg_match('#\((\d+)\)$#', $table->title, $m)) {
				$data['title'] = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $category->title);
			} else {
				$data['title'] .= ' (2)';
			}
		}
		
		// bind it to the table
		if (!$category->bind($data['jform'])) {
			$this->setError(500, $this->_db->getErrorMsg() );
			return false;
		}
		
		// Bind the rules.
		if (isset($data['rules'])) {
			$rules = new JRules($data['rules']);
			$category->setRules($rules);
		}

		if (!$category->id) {
			$category->ordering = $category->getNextOrder();
		}
		
		//$params			= JRequest::getVar( 'params', null, 'post', 'array' );
		$params			= $data["jform"]["params"];
		$copyparams		= JRequest::getVar( 'copycid', null, 'post', 'int' );
		
		if ($copyparams) {
			$category->params = $this->getParams($copyparams);
		} else{
			// Build parameter INI string
			if (is_array($params))
			{
				$txt = array ();
				foreach ($params as $k => $v) {
					if (is_array($v)) {
						$v = implode('|', $v);
					}
					$txt[] = "$k=$v";
				}
				$category->params = implode("\n", $txt);
			}
		}

		// Make sure the data is valid
		if (!$category->check()) {
			$this->setError($category->getError());
			return false;
		}

		// Store it in the db
		if (!$category->store()) {
			$this->setError(500, $this->_db->getErrorMsg() );
			return false;
		}
		
		// Rebuild the tree path.
		if (!$category->rebuildPath($category->id)) {
			$this->setError($category->getError());
			return false;
		}
		
		/*if (FLEXI_ACCESS) {
			FAccess::saveaccess( $category, 'category' );
		}*/

		$this->_category	=& $category;
		return true;
	}
	
	/**
	 * Method to get the parameters of another category
	 *
	 * @access	public
	 * @params	int			id of the category
	 * @return	string		ini string of params
	 * @since	1.5
	 */
	function getParams($id) {
		$query 	= 'SELECT params FROM #__categories'
				. ' WHERE id = ' . (int)$id
				;
		$this->_db->setQuery($query);
		$copyparams = $this->_db->loadResult();
		
		return $copyparams;
	}
	
	/**
	 * Method to copy category parameters
	 *
	 * @param 	int 	$id of target
	 * @param 	string 	$params to copy
	 * @return 	boolean	true on success
	 * 
	 * @since 1.5
	 */
	function copyParams($id, $params)
	{
		$query 	= 'UPDATE #__categories'
				. ' SET params = ' . $this->_db->Quote($params)
				. ' WHERE id = ' . (int)$id
				;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			return false;
		}
		return true;
	}
	
	/**
	 * Method to get the row form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		// Initialise variables.
		$extension	= $this->getState('flexicontent_categories.extension');

		// Get the form.
		$form = $this->loadForm('com_flexicontent.flexicontent_categories'.$extension, 'category', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Modify the form based on Edit State access controls.
		if (empty($data['extension'])) {
			$data['extension'] = $extension;
		}

		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_flexicontent.edit.'.$this->getName().'.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
	
	/**
	 * Method to get a category.
	 *
	 * @param	integer	An optional id of the object to get, otherwise the id from the model state is used.
	 * @return	mixed	Category data object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null) {
		if ($result = parent::getItem($pk)) {
			// Prime required properties.
			if (empty($result->id)) {
				$result->parent_id	= $this->getState('flexicontent_categories.parent_id');
				$result->extension	= $this->getState('flexicontent_categories.extension');
			}

			// Convert the metadata field to an array.
			$registry = new JRegistry();
			$registry->loadJSON($result->metadata);
			$result->metadata = $registry->toArray();

			// Convert the created and modified dates to local user time for display in the form.
			jimport('joomla.utilities.date');
			$tz	= new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if (intval($result->created_time)) {
				$date = new JDate($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toMySQL(true);
			} else {
				$result->created_time = null;
			}

			if (intval($result->modified_time)) {
				$date = new JDate($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toMySQL(true);
			} else {
				$result->modified_time = null;
			}
		}

		return $result;
	}
	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	*/
	public function getTable($type = 'flexicontent_categories', $prefix = '', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		if (!($parentId = $app->getUserState('com_flexicontent.edit.'.$this->getName().'.parent_id'))) {
			$parentId = JRequest::getInt('parent_id');
		}
		$this->setState('flexicontent_categories.parent_id', $parentId);

		if (!($extension = $app->getUserState('com_flexicontent.edit.'.$this->getName().'.extension'))) {
			$extension = JRequest::getCmd('extension', 'com_content');
		}
		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_flexicontent.edit.'.$this->getName().'.id'))) {
			$cid = JRequest::getVar('cid', array(0));
			$pk = (int)$cid[0];
		}
		$this->setState($this->getName().'.id', $pk);


		$this->setState('flexicontent_categories.extension', $extension);
		$parts = explode('.',$extension);
		// extract the component name
		$this->setState('flexicontent_categories.component', $parts[0]);
		// extract the optional section name
		$this->setState('flexicontent_categories.section', (count($parts)>1)?$parts[1]:null);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_flexicontent');
		$this->setState('params', $params);
	}
}
?>
