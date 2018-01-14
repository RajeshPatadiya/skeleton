<?php
/**
 * CodeIgniter Skeleton
 *
 * A ready-to-use CodeIgniter skeleton  with tons of new features
 * and a whole new concept of hooks (actions and filters) as well
 * as a ready-to-use and application-free theme and plugins system.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018, Kader Bouyakoub <bkader@mail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package 	CodeIgniter
 * @author 		Kader Bouyakoub <bkader@mail.com>
 * @copyright	Copyright (c) 2018, Kader Bouyakoub <bkader@mail.com>
 * @license 	http://opensource.org/licenses/MIT	MIT License
 * @link 		https://github.com/bkader
 * @since 		Version 1.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bkader_metadata Class
 *
 * Handles all operation done on metadata.
 *
 * @package 	CodeIgniter
 * @subpackage 	Skeleton
 * @category 	Libraries
 * @author 		Kader Bouyakoub <bkader@mail.com>
 * @link 		https://github.com/bkader
 * @copyright	Copyright (c) 2018, Kader Bouyakoub (https://github.com/bkader)
 * @since 		Version 1.0.0
 * @version 	1.0.0
 */
class Bkader_metadata extends CI_Driver
{
	/**
	 * Initialize class preferences.
	 * @access 	public
	 * @return 	void
	 */
	public function initialize()
	{
		log_message('info', 'Bkader_metadata Class Initialized');
	}

	// ------------------------------------------------------------------------

	/**
	 * Create multiple metadata for a given entity.
	 * @access 	public
	 * @param 	int 	$guid 	the entity's ID.
	 * @param 	mixed 	$meta 	string or array of name => value.
	 * @param 	mixed 	$value
	 * @return 	bool
	 */
	public function create($guid, $meta, $value = null)
	{
		// We make sure the entity exists and $meta is provided.
		if ( ! $this->_parent->entities->get($guid) OR empty($meta))
		{
			return false;
		}

		// Turn things into an array.
		(is_array($meta)) OR $meta = array($meta => $value);


		// Prepare out array of metadata.
		$data = array();

		// Loop through elements and fill $data.
		foreach ($meta as $key => $val)
		{
			/**
			 * The reason we are doing this check is to allow
			 * the user use the following structure:
			 * @example:
			 * 
			 * update_meta(1, array(
			 *     'phone' => '0123456789',
			 *     'address', // <-- See this!
			 *     'company' => 'Company Name',
			 * ));
			 *
			 * Both "phone" and "company" will use their respective 
			 * value while "address" and all other metadata using 
			 * the same structure will use $value.
			 */
			if (is_int($key))
			{
				$key = $val;
				$val = $value;
			}

			// We make sure it does not exists first.
			if( ! $this->get($guid, $key))
			{
				$data[] = array(
					'guid'  => $guid,
					'name'  => $key,
					'value' => to_bool_or_serialize($val),
				);
			}
		}

		// Proceed only if $data is not empty.
		return ( ! empty($data))
			? ($this->ci->db->insert_batch('metadata', $data) > 0)
			: false;
	}

	// ------------------------------------------------------------------------

	/**
	 * Update a single or multiple metadat.
	 * @access 	public
	 * @param 	int 	$guid 	the entity's ID.
	 * @param 	mixed 	$meta 	string or array of name => value.
	 * @param 	mixed 	$value
	 * @return 	bool
	 */
	public function update($guid, $meta, $value = null)
	{
		// Make sure the entity exists and metadata are provided.
		if ( ! $this->_parent->entities->get($guid) OR empty($meta))
		{
			return false;
		}

		// Turn things into an array.
		(is_array($meta)) OR $meta = array($meta => $value);

		// Loop through all, update if found, create if not.
		foreach ($meta as $key => $val)
		{
			/**
			 * The reason we are doing this check is to allow
			 * the user use the following structure:
			 * @example:
			 * 
			 * update_meta(1, array(
			 *     'phone' => '0123456789',
			 *     'address', // <-- See this!
			 *     'company' => 'Company Name',
			 * ));
			 *
			 * Both "phone" and "company" will use their respective 
			 * value while "address" and all other metadata using 
			 * the same structure will use $value.
			 */
			if (is_int($key))
			{
				$key = $val;
				$val = $value;
			}

			// Check if the metadata exists first.
			$md = $this->get($guid, $key);

			// Found by same value? Nothing to do.
			if ($md && $md->value == $val)
			{
				continue;
			}

			// Found by different value? Update it.
			if ($md)
			{
				$this->ci->db
					->where('guid', $guid)
					->where('name', $key)
					->set('value', to_bool_or_serialize($val))
					->update('metadata');
			}
			else
			{
				$this->create($guid, $key, $val);
			}
		}

		return ($this->ci->db->affected_rows() > 0);
	}

	// ------------------------------------------------------------------------

	/**
	 * Update a single or multiple metadata.
	 * @access 	public
	 * @return 	boolean
	 *
	 * @example:
	 * $this->app->metadata->update_by(
	 * 		array('guid' => 1, 'name' => 'var_name'),
	 *   	array(
	 *   		'value'  => 'new_value',
	 *   		'params' => 'new_params'
	 *   	)
	 * );
	 */
	public function update_by()
	{
		// Let's first collect method arguments.
		$args = func_get_args();

		// If there are not, nothing to do.
		if (empty($args))
		{
			return false;
		}

		/**
		 * Data to update is always the last argument
		 * and it must be an array.
		 */
		$data = array_pop($args);
		if ( ! is_array($data) OR empty($data))
		{
			return false;
		}

		// Prepare the value and params.
		(isset($data['value'])) && $data['value'] = to_bool_or_serialize($data['value']);

		// Prepare our query.
		$this->ci->db->set($data);

		// If there are any arguments left, they will use as WHERE clause.
		if ( ! empty($args))
		{
			// Get rid of nasty deep array.
			(is_array($args[0])) && $args = $args[0];

			// Add the WHERE clause.
			$this->ci->db->where($args);
		}

		// Proceed to update an return TRUE if all went good.
		$this->ci->db->update('metadata');
		return ($this->ci->db->affected_rows() > 0);
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete a single or multiple metadata.
	 * @access 	public
	 * @return 	boolean
	 * 
	 * @example:
	 * To delete all metadata of an entity, just pass the ID.
	 * To delete a specific metadata, pass its name as the second parameter.
	 * To delete multiple one, pass their array as the second parameter or
	 * you can pass successive names.
	 */
	public function delete()
	{
		// Collect method arguments and make sure there are any.
		$args = func_get_args();
		if (empty($args))
		{
			return false;
		}

		// The entity's ID is always the first argument.
		$guid = array_shift($args);

		// Make sure it's numeric and the entity exists.
		if ( ! is_numeric($guid) OR ! $this->_parent->entities->get($guid))
		{
			return false;
		}

		// Proceed to deleting.
		$this->ci->db->where('guid', $guid);

		// There are some arguments left?
		if ( ! empty($args))
		{
			// Get rid of nasty deep array.
			(is_array($args[0])) && $args = $args[0];

			$this->ci->db->where_in('name', $args);
		}

		$this->ci->db->delete('metadata');
		return ($this->ci->db->affected_rows() > 0);
	}

	// ------------------------------------------------------------------------

	/**
	 * Retrieve a single or multiple metadata of the selected entity.
	 * @access 	public
	 * @param 	int 	$guid 	The entiti'y id.
	 * @param 	string 	$name 	The metadata name
	 * @param 	bool 	$single Whether to return the metadata value.
	 * @return 	mixed
	 */
	public function get($guid, $name = null, $single = false)
	{
		// A single metadata to retrieve?
		if ( ! empty($name))
		{
			$meta = $this->get_by(array(
				'guid' => $guid,
				'name' => $name,
			));

			// Return the value or the whole object if found.
			return ($meta && $single === true) ? $meta->value : $meta;
		}

		// Multiple metadata.
		return $this->get_many('guid', $guid);
	}

	// ------------------------------------------------------------------------

	/**
	 * Retrieve a single metadata by arbitrary WHERE clause.
	 * @access 	public
	 * @param 	mixed 	$field
	 * @param 	mixed 	$match
	 * @return 	object if found, else null
	 */
	public function get_by($field, $match = null)
	{
		(is_array($field)) OR $field = array($field => $match);

		$meta = $this->ci->db->get_where('metadata', $field, 1)->row();

		// Found?
		if ($meta)
		{
			$meta->value = from_bool_or_serialize($meta->value);
		}

		return $meta;
	}

	// ------------------------------------------------------------------------

	/**
	 * Retrieve multiple metadata by arbitrary WHERE clause.
	 * @access 	public
	 * @param 	mixed 	$field
	 * @param 	mixed 	$match
	 * @return 	array of objects if found, else null
	 */
	public function get_many($field = null, $match = null)
	{
		// Argument provided?
		if ( ! empty($field))
		{
			if (is_array($field))
			{
				$this->ci->db->where($field);
			}
			elseif (is_array($match))
			{
				$this->ci->db->where_in($field, $match);
			}
			else
			{
				$this->ci->db->where($field, $match);
			}
		}

		// Proceed to retrieving.
		$meta = $this->ci->db->get('metadata')->result();

		// Found? Format the value.
		if ($meta)
		{
			foreach ($meta as &$_meta)
			{
				$_meta->value = from_bool_or_serialize($_meta->value);
			}
		}

		// Return the final result.
		return $meta;
	}
	

	// ------------------------------------------------------------------------

	/**
	 * This method deletes all metadata that have to owners.
	 * @access 	public
	 * @return 	boolean
	 */
	public function purge()
	{
		$this->ci->db
			->where_not_in('guid', $this->_parent->entities->get_all_ids())
			->delete('metadata');

		return ($this->ci->db->affected_rows() > 0);
	}

}

// ------------------------------------------------------------------------

if ( ! function_exists('add_meta'))
{
	/**
	 * Helper function to create a new meta data for the selected entity.
	 * @param 	int 	$guid 	The entity's ID.
	 * @param 	mixed 	$meta 	The metadata name or an associative array.
	 * @param 	mixed 	$value 	The metadata value.
	 * @return 	boolean
	 */
	function add_meta($guid, $meta, $value = null)
	{
		return get_instance()->app->metadata->create($guid, $meta, $value);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('update_meta'))
{
	/**
	 * Update a single or multiple metadata for the selected entity.
	 * @param 	int 	$guid 	The entity's ID.
	 * @param 	mixed 	$meta 	The metadata name or associative array.
	 * @param 	mixed 	$value 	The metadata value.
	 * @return 	boolean.
	 */
	function update_meta($guid, $meta, $value = null)
	{
		return get_instance()->app->metadata->update($guid, $meta, $value);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('update_meta_by'))
{
	/**
	 * Update a single or multiple metadata.
	 * @return 	boolean
	 */
	function update_meta_by()
	{
		return call_user_func_array(
			array(get_instance()->app->metadata, 'update_by'),
			func_get_args()
		);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('delete_meta'))
{
	/**
	 * Delete a single or multiple metadata for the selected entity.
	 * @return 	boolean
	 */
	function delete_meta()
	{
		return call_user_func_array(
			array(get_instance()->app->metadata, 'delete'),
			func_get_args()
		);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_meta'))
{
	/**
	 * Retrieve a single or multiple metadata for the selected entity.
	 * @param 	int 	$guid 	The entity's ID.
	 * @param 	mixed 	$name 	The metadata name or array.
	 * @param 	bool 	$single Whether to retrieve the value instead of the object.
	 * @return 	mixed 	depends on the value of the metadata.
	 */
	function get_meta($guid, $name = null, $single = false)
	{
		return get_instance()->app->metadata->get($guid, $name, $single);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_meta_by'))
{
	/**
	 * Retrieve a single metadata by arbitrary WHERE clause.
	 * @param 	mixed 	$field
	 * @param 	mixed 	$match
	 * @return 	object if found, else null
	 */
	function get_meta_by($field, $match = null)
	{
		return get_instance()->app->metadata->get_by($field, $match);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_many_meta'))
{
	/**
	 * Retrieve multiple metadata by arbitrary WHERE clause.
	 * @access 	public
	 * @param 	mixed 	$field
	 * @param 	mixed 	$match
	 * @return 	array of objects if found, else null
	 */
	function get_many_meta($field, $match = null)
	{
		return get_instance()->app->metadata->get_many($field, $match);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('purge_meta'))
{
	/**
	 * Clean up metadata table from meta that have no existing entities.
	 * @return 	boolean
	 */
	function purge_meta()
	{
		return get_instance()->app->metadata->purge();
	}
}