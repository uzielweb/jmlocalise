<?php
/**
 * @package     Com_Localise
 * @subpackage  model
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Localise\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Localise\Administrator\Helper\LocaliseHelper;

/**
 * Languages Model class for the Localise component
 *
 * @since  1.0
 */
class LanguagesModel extends ListModel
{
	// protected $filter_fields = array('tag', 'client', 'name');

	protected $context = 'com_localise.languages';

	protected $items;

	protected $languages;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.5
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'tag',
				'client',
				'name',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app  = Factory::getApplication();
		$data = $app->input->get('filters', array(), 'array');

		if (empty($data))
		{
			$data           = array();
			$data['select'] = $app->getUserState('com_localise.select');
		}
		else
		{
			$app->setUserState('com_localise.select', $data['select']);
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$search = InputFilter::getInstance()->clean($search, 'TRIM');
		$search = strtolower($search);

		if ($search)
		{
			$app->setUserState('filter.search', strtolower($search));
		}
		else
		{
			$app->setUserState('filter.search', '');
		}

		$this->setState('filter.client', isset($data['select']['client']) ? $data['select']['client'] : '');

		$this->setState('filter.tag', isset($data['select']['tag']) ? $data['select']['tag'] : '');

		$this->setState('filter.name', isset($data['select']['name']) ? $data['select']['name'] : '');

		// Load the parameters.
		$params = ComponentHelper::getParams('com_localise');
		$this->setState('params', $params);

		// Call auto-populate parent method
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get the row form.
	 *
	 * @return  mixed  JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.

		Form::addFormPath(JPATH_COMPONENT . '/forms');
		Form::addFieldPath(JPATH_COMPONENT . '/field');
		$form = Form::getInstance('com_localise.languages', 'languages', array('control' => 'filters','event'   => 'onPrepareForm'));

		// Check for an error.
		if ($form instanceof Exception)
		{
			$this->setError($form->getMessage());

			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_localise.select', array());

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind(array('select' => $data));
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_localise.languages.filter.search', array());

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind(array('search' => $data));
		}

		return $form;
	}

	/**
	 * Get the items (according the filters and the pagination)
	 *
	 * @return  array  array of object items
	 */
	public function getItems()
	{
		if (!isset($this->items))
		{
			$languages = $this->getLanguages();
			$count     = count($languages);
			$start     = $this->getState('list.start');
			$limit     = $this->getState('list.limit');

			if ($start > $count)
			{
				$start = 0;
			}

			if ($limit == 0)
			{
				$start = 0;
				$limit = null;
			}

			$this->items = array_slice($languages, $start, $limit);
		}

		return $this->items;
	}

	/**
	 * Get total number of languages (according to filters)
	 *
	 * @return  int  number of languages
	 */
	public function getTotal()
	{
		return count($this->getLanguages());
	}

	/**
	 * Get all languages (according to filters)
	 *
	 * @return   array  array of object items
	 */
	protected function getLanguages()
	{
		if (!isset($this->languages))
		{
			$this->languages = array();
			$client          = $this->getState('filter.client');
			$tag             = $this->getState('filter.tag');
			$search          = $this->getState('filter.search');

			if (empty($client))
			{
				$clients = array('site', 'administrator');

				if (LocaliseHelper::hasInstallation())
				{
					$clients[] = 'installation';
				}
			}
			else
			{
				$clients = array($client);
			}

			foreach ($clients as $client)
			{
				if (empty($tag))
				{
					$folders = Folder::folders(
						constant('LOCALISEPATH_' . strtoupper($client)) . '/language',
							'.',
							false,
							false,
							array('.svn', 'CVS','.DS_Store','__MACOSX','pdf_fonts','overrides')
					);
				}
				else
				{
					$folders = Folder::folders(
						constant('LOCALISEPATH_' . strtoupper($client)) . '/language',
							'^' . $tag . '$',
							false,
							false,
							array('.svn','CVS','.DS_Store','__MACOSX','pdf_fonts','overrides')
						);
				}

				foreach ($folders as $folder)
				{
					// Move to first
					$id = LocaliseHelper::getFileId(constant('LOCALISEPATH_' . strtoupper($client)) . "/language/$folder/$folder.xml");

					// If it was not found a file.
					if ($id < 1)
					{
						continue;
					}

					//$model = \JModelLegacy::getInstance('Language', 'LocaliseModel', array('ignore_request' => true));
					$model = new LanguageModel();
					$model->getState();
					$model->setState('language.tag', $folder);
					$model->setState('language.client', $client);
					$model->setState('language.id', $id);

					$language = $model->getItem();

					if (empty($search) || preg_match("/$search/i", $language->name))
					{
						$this->languages[] = $language;
					}
				}
			}

			$ordering = $this->getState('list.ordering')
				? $this->getState('list.ordering')
				: 'name';
			$this->languages = ArrayHelper::sortObjects(
				$this->languages,
				$ordering, $this->getState('list.direction') == 'DESC' ? -1 : 1
			);
		}

		return $this->languages;
	}

	/**
	 * Cleans out _localise table.
	 *
	 * @return  bool True on success
	 *
	 * @throws	Exception
	 * @since   1.0
	 */
	public function purge()
	{
		// Get the localise data
		$query = $this->_db->getQuery(true);
		$query->select("l.id");
		$query->from("#__localise AS l");
		$query->join('LEFT', '#__assets AS ast ON ast.id = l.asset_id');
		$query->order('ast.rgt DESC');
		$this->_db->setQuery($query);

		try
		{
			$data = $this->_db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage());
		}

		foreach ($data as $key => $value)
		{
			$id = $value->id;

			// Get the localise table
			$table = Table::getInstance('LocaliseTable', '\\Joomla\\Component\\Localise\\Administrator\\Table\\');

			// Load it before delete.
			$table->load($id);

			// Delete
			try
			{
				$table->delete($id);
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException($e->getMessage());
			}
		}

		Factory::getApplication()->enqueueMessage(Text::_('COM_LOCALISE_PURGE_SUCCESS'));

		return true;
	}
}
