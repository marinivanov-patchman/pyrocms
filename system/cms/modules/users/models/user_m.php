<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * The User model.
 *
 * @author PyroCMS Dev Team
 * @package PyroCMS\Core\Modules\Users\Models
 */
class User_m extends Cartalyst\Sentry\Users\Eloquent\User
{
	protected $table = 'users';
	
    /**
     * Disable updated_at and created_at on table
     *
     * @var boolean
     */
    public $timestamps = false;

	/**
	 * Returns the relationship between users and groups.
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function groups()
	{
		return $this->belongsToMany('Cartalyst\Sentry\Groups\Eloquent\Group', 'users_groups', 'user_id');
	}

	/**
	 * Find a user based from their username
	 *
	 * @param 	array $username Username of the user
	 * @return  $this
	 */
	public function findByUsername($username)
	{
		return $this
			->whereRaw('LOWER(username) = ?', array(strtolower($username)))
			->first();
	}

	/**
	 * Find a user based from their email
	 *
	 * @param 	array $username Username of the user
	 * @return  $this
	 */
	public function findByEmail($email)
	{
		return $this
			->whereRaw('LOWER(email) = ?', array(strtolower($email)))
			->first();
	}

	/**
	 * Check if user is activated
	 *
	 * @return bool
	 */
	public function isActivated()
	{
		return (bool) $this->is_activated;
	}

	/**
	 * Get recent users
	 *
	 * @return     array
	 */
	public function getRecent()
	{
		return $this
			->orderBy('created_on', 'desc')
			->all();
	}

	/**
	 * Get all user objects
	 *
	 * @return object
	 */
	public function getAll()
	{
		return $this
			->with('profiles')
			->groupBy('users.id')
			->all();
	}

	/**
	 * Create a new user
	 *
	 * @param array $input
	 * @return int|true
	 */
	public function add($input = array())
	{
		return parent::insertDOESNTEXIST(array(
			'email' => $input->email,
			'password' => $input->password,
			'salt' => $input->salt,
			'role' => empty($input->role) ? 'user' : $input->role,
			'active' => 0,
			'lang' => $this->config->item('default_language'),
			'activation_code' => $input->activation_hash,
			'created_on' => now(),
			'last_login' => now(),
			'ip' => $this->input->ip_address()
		));
	}

	/**
	 * Checks if the user is a super user - has
	 * access to everything regardless of permissions.
	 *
	 * @return bool
	 */
	public function isAdmin()
	{
		$permissions = $this->getMergedPermissions();

		if ( ! array_key_exists('admin', $permissions))
		{
			return false;
		}

		return $permissions['admin'] == 1;
	}

	public function isSuperUser()
	{
		throw new Exception('NOPE! Use isAdmin() instead.');
	}

	/**
	 * Update the last login time
	 */
	public function updateLastLogin()
	{
		$this->last_login = now();
		$this->save();
	}

	/**
	 * Activate a newly created user
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function activateUser()
	{
		$this->is_activated = true;
		$this->activation_code = null;
		$this->save();
	}

}