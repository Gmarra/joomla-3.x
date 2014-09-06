<?php
/**
* @package PortalPadrao
* @subpackage com_agendadirigentes
*
* @copyright Copyright (C) 2005 - 2014 Joomla Calango. All rights reserved.
* @license GNU General Public License version 2 or later; see LICENSE.txt
*/
 
// impedir acesso direto ao arquivo
defined('_JEXEC') or die;

// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * DirigenteList Model
 */
class AgendaDirigentesModelCargos extends JModelList
{
        public function __construct($config = array())
        {
            if (empty($config['filter_fields']))
            {
                $config['filter_fields'] = array(
                    'id', 'a.id',
                    'name', 'a.name',
                    'published', 'a.published',
                    'title', 'b.title', 'catid'
                );
            }

            parent::__construct($config);
        }
        
        /**
         * Method to build an SQL query to load the list data.
         *
         * @return      string  An SQL query
         */
        protected function getListQuery()
        {
                // Create a new query object.           
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                // Select some fields from the cargos table
                $query
                    ->select('DISTINCT a.id, a.name, a.catid, a.published, b.title AS titleCategory')
                    ->from( $db->quoteName('#__agendadirigentes_cargos', 'a') )
                    ->join('INNER', $db->quoteName('#__categories', 'b')
                        . ' ON (' . $db->quoteName('b.id') . ' = ' . $db->quoteName('a.catid') . ')'
                    );

                // Filter by published state
                $published = $this->state->get('filter.published');
                if (is_numeric($published))
                {
                    $query->where('a.published = ' . (int) $published);
                }
                elseif ($published === '')
                {
                    $query->where('(a.published IN (0, 1))');
                }

                // Filter by category.
                $catid = $this->getState('filter.catid');
                if (is_numeric($catid))
                {
                    $query->where('a.catid = ' . (int) $catid);
                }

                // Filter by search in title
                $search = $this->getState('filter.search');
                if (!empty($search))
                {
                    if (stripos($search, 'id:') === 0)
                    {
                        $query->where('a.id = ' . (int) substr($search, 3));
                    }
                    else
                    {
                        $search = $db->quote('%' . $db->escape($search, true) . '%');
                        $query->where('(a.name LIKE ' . $search . ')');
                    }
                }

                // Add the list ordering clause.
                $orderCol = $this->state->get('list.ordering', 'a.id');
                $orderDirn = $this->state->get('list.direction', 'DESC');
                $query->order($db->escape($orderCol . ' ' . $orderDirn));

                return $query;
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
            // Load the filter state.
            $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
            $this->setState('filter.search', $search);

            $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
            $this->setState('filter.published', $published);

            $catid = $this->getUserStateFromRequest($this->context . '.filter.catid', 'filter_catid', '');
            $this->setState('filter.catid', $catid);

            // List state information.
            parent::populateState('a.id', 'DESC');
        }
}