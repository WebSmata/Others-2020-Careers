<?php

	/**
	 * Class to handle admin
	 */

	class admin
	{
		// Properties

		/**
		 * @var int The admin ID from the database
		 */
		public $adminid = null;

		/**
		 * @var string The name of the admin
		 */
		public $name = null;

		/**
		 * @var string The email of the admin
		 */
		public $email = null;

		/**
		 * @var string The password of the admin
		 */
		public $password = null;

		/**
		 * @var string When the admin is to be / was registered
		 */
		public $created = null;

		/**
		 * @var string When the admin is to be / was updated
		 */
		public $updated = null;


		/**
		 * Sets the object's properties using the values in the supplied array
		 *
		 * @param assoc The property values
		 */

		public function __construct( $data=array() ) 
		{ 
			if ( isset( $data['adminid'] ) ) $this->adminid = (int) $data['adminid'];
			if ( isset( $data['name'] ) ) $this->name = $data['name'];
			if ( isset( $data['email'] ) ) $this->email = $data['email'];
			if ( isset( $data['password'] ) ) $this->password = md5($data['password']);
			if ( isset( $data['created'] ) ) $this->created = $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = $data['updated'];
		}

		/**
		 * Sets the object's properties using the edit form post values in the supplied array
		 *
		 * @param assoc The form post values
		 */

		public function storeFormValues ( $params ) 
		{
			// Store all the parameters
			$this->__construct( $params );

			// Parse and store the publication date
			if ( isset($params['created']) ) {
				$created = explode ( '-', $params['created'] );

				if ( count($created) == 3 ) {
					list ( $y, $m, $d ) = $created;
					$this->created = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		/**
		 * Returns a admin object matching the given admin ID
		 *
		 * @param int The admin ID
		 * @return admin|false The admin object, or false if the record was not found or there was a problem
		 */

		public static function getById( $adminid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM admins WHERE adminid = :adminid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":adminid", $adminid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new admin( $row );
		}

		/**
		 * signin a admin
		 * @param string handle
		 * @param string password
		 */

		public static function signinuser( $email, $password ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM admins WHERE email = :email AND password = :password";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":email", $email, PDO::PARAM_INT );
			$st->bindValue( ":password", $password, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) {
				$_SESSION['loggedin_level'] = $row['level'];
				$_SESSION['loggedin_adminidame'] = $row['name'];
				$_SESSION['loggedin_adminid'] = $row['adminid'];
				return true;
			}	else return false;
		}

		/**
		 * Returns all (or a range of) admin objects in the DB
		 *
		 * @param int Optional The number of rows to return (default=all)
		 * @return Array|false A two-element array : results => array, a list of admin objects; totalRows => Total number of articles
		 */

		public static function getList( $level ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM admins ORDER BY created DESC";

			$st = $conn->prepare( $sql );
			$st->bindValue( ":level", $level, PDO::PARAM_INT );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$admin = new admin( $row );
				$list[] = $admin;
			}

			$conn = null;
			return $list;
		}

		/**
		 * Inserts the current admin object into the database, and sets its ID property.
		 */

		public function insert() 
		{
			if ( !is_null( $this->adminid ) ) trigger_error ( "admin::insert(): Attempt to insert an admin object that already has its ID property set (to $this->adminid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO admins ( name, email, password, created ) VALUES ( :name, :email, :password, :created )";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":name", $this->name, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":password", $this->password, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->adminid = $conn->lastInsertId();
			$conn = null;
			return $this->adminid;
		}

		/**
		* Updates the current admin object in the database.
		*/

		public function update() 
		{
			if ( is_null( $this->adminid ) ) trigger_error ( "admin::update(): Attempt to update an admin object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE admins SET name=:name, email=:email, updated=:updated WHERE adminid =:adminid";
			
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":name", $this->name, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		/**
		* Deletes the current admin object from the database.
		*/

		public function delete() 
		{
			if ( is_null( $this->adminid ) ) trigger_error ( "admin::delete(): Attempt to delete an admin object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM admins WHERE adminid = :adminid LIMIT 1" );
			$st->bindValue( ":adminid", $this->adminid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
