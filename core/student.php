<?php

	/**
	 * Class to handle student
	 */

	class student
	{ 		
		// Properties

		/**
		 * @var int The student ID from the database
		 */
		public $studentid = null;

		/**
		 * @var string The fullname of the student
		 */
		public $fullname = null;

		/**
		 * @var string The idnumber of the student
		 */
		public $idnumber = null;

		/**
		 * @var string The kcseyear of the student
		 */
		public $kcseyear = null;

		/**
		 * @var string The kcsegrade of the student
		 */
		public $kcsegrade = null;

		/**
		 * @var int The kcsescores of the student
		 */
		public $kcsescores = null;

		/**
		 * @var string The email of the student
		 */
		public $email = null;

		/**
		 * @var string The password of the student
		 */
		public $password = null;

		/**
		 * @var string When the student is to be / was registered
		 */
		public $created = null;

		/**
		 * @var string When the student is to be / was updated
		 */
		public $updated = null;


		/**
		 * Sets the object's properties using the values in the supplied array
		 *
		 * @param assoc The property values
		 */

		public function __construct( $data=array() ) 
		{
			if ( isset( $data['studentid'] ) ) $this->studentid = (int) $data['studentid'];
			if ( isset( $data['fullname'] ) ) $this->fullname =  $data['fullname'];
			if ( isset( $data['idnumber'] ) ) $this->idnumber = $data['idnumber'];
			if ( isset( $data['kcseyear'] ) ) $this->kcseyear = $data['kcseyear'];
			if ( isset( $data['kcsegrade'] ) ) $this->kcsegrade = $data['kcsegrade'];
			if ( isset( $data['kcsescores'] ) ) $this->kcsescores = $data['kcsescores'];
			if ( isset( $data['email'] ) ) $this->email = $data['email'];
			if ( isset( $data['password'] ) ) $this->password = md5($data['password']);
			if ( isset( $data['created'] ) ) $this->created = (int) $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = (int) $data['updated'];
		}

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
		 * Returns a student object matching the given student ID
		 *
		 * @param int The student ID
		 * @return student|false The student object, or false if the record was not found or there was a problem
		 */

		public static function getById( $studentid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM students WHERE studentid = :studentid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":studentid", $studentid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new student( $row );
		}

		/**
		 * signin a student
		 * @param string handle
		 * @param string password
		 */

		public static function signinuser( $email, $password ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM students WHERE email = :email AND password = :password";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":email", $email, PDO::PARAM_INT );
			$st->bindValue( ":password", $password, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) 
			{
				$_SESSION['loggedin_fullname'] = $row['fullname'];
				$_SESSION['loggedin_user'] = $row['studentid'];
				return true;
			}
			else return false;
		}

		/**
		 * Returns all (or a range of) student objects in the DB
		 *
		 * @param int Optional The number of rows to return (default=all)
		 * @return Array|false A two-element array : results => array, a list of student objects; totalRows => Total number of articles
		 */

		public static function getList() 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM students ORDER BY kcsegrade ASC";

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$student = new student( $row );
				$list[] = $student;
			}

			$conn = null;
			return $list;
		}

		/**
		 * Inserts the current student object into the database, and sets its ID property.
		 */

		public function insert() 
		{
			if ( !is_null( $this->studentid ) ) trigger_error ( "student::insert(): Attempt to insert an student object that already has its ID property set (to $this->studentid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO students ( fullname, idnumber, kcseyear, kcsegrade, kcsescores, email, password, created ) VALUES ( :fullname, :idnumber, :kcseyear, :kcsegrade, :kcsescores, :email, :password, :created )";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":fullname", $this->fullname, PDO::PARAM_STR );
			$st->bindValue( ":idnumber", $this->idnumber, PDO::PARAM_STR );
			$st->bindValue( ":kcseyear", $this->kcseyear, PDO::PARAM_STR );
			$st->bindValue( ":kcsegrade", $this->kcsegrade, PDO::PARAM_STR );
			$st->bindValue( ":kcsescores", $this->kcsescores, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":password", $this->password, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->studentid = $conn->lastInsertId();
			$conn = null;
			return $this->studentid;
		}

		/**
		* Updates the current student object in the database.
		*/

		public function update() 
		{
			if ( is_null( $this->studentid ) ) trigger_error ( "student::update(): Attempt to update an student object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE students SET fullname=:fullname, idnumber=:idnumber, kcseyear=:kcseyear, kcsegrade=:kcsegrade, kcsescores=:kcsescores, email=:email, updated=:updated WHERE studentid=:studentid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":fullname", $this->fullname, PDO::PARAM_STR );
			$st->bindValue( ":idnumber", $this->idnumber, PDO::PARAM_STR );
			$st->bindValue( ":kcseyear", $this->kcseyear, PDO::PARAM_STR );
			$st->bindValue( ":kcsegrade", $this->kcsegrade, PDO::PARAM_STR );
			$st->bindValue( ":kcsescores", $this->kcsescores, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":studentid", $this->studentid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		/**
		* Deletes the current student object from the database.
		*/

		public function delete() 
		{

			if ( is_null( $this->studentid ) ) trigger_error ( "student::delete(): Attempt to delete an student object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM students WHERE studentid = :studentid LIMIT 1" );
			$st->bindValue( ":studentid", $this->studentid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
