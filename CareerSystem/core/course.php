<?php

	class course
	{ 
		public $courseid = null;
		public $name = null;
		public $code = null;
		public $category = null;
		public $level = null;
		public $meangrade = null;
		public $requirements = null;
		public $created = null;
		public $updated = null;
		
		public function __construct( $data=array() ) 
		{
			if ( isset( $data['courseid'] ) ) $this->courseid = (int) $data['courseid'];
			if ( isset( $data['name'] ) ) $this->name =  $data['name'];
			if ( isset( $data['code'] ) ) $this->code = $data['code'];
			if ( isset( $data['category'] ) ) $this->category = $data['category'];
			if ( isset( $data['level'] ) ) $this->level = $data['level'];
			if ( isset( $data['meangrade'] ) ) $this->meangrade = $data['meangrade'];
			if ( isset( $data['requirements'] ) ) $this->requirements = $data['requirements'];
			if ( isset( $data['created'] ) ) $this->created = $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = $data['updated'];
		}

		public function storeFormValues ( $params ) 
		{
			$this->__construct( $params );

			if ( isset($params['created']) ) {
				$created = explode ( '-', $params['created'] );

				if ( count($created) == 3 ) {
					list ( $y, $m, $d ) = $created;
					$this->created = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		public static function getById( $courseid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM courses WHERE courseid = :courseid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":courseid", $courseid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new course( $row );
		}

		public static function getList() 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			
			$sql = 'SELECT * FROM courses ORDER BY courseid DESC';
			
			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$course = new course( $row );
				$list[] = $course;
			}

			$sql = "SELECT FOUND_ROWS() AS totalRows";
			$totalRows = $conn->query( $sql )->fetch();
			$conn = null;
			return $list;
		}

		public function insert() 
		{
			if ( !is_null( $this->courseid ) ) trigger_error ( "course::insert(): Attempt to insert an course object that already has its ID property set (to $this->courseid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO courses ( name, code, category, level, meangrade, requirements, created ) VALUES ( :name, :code, :category, :level, :meangrade, :requirements, :created)";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":name", $this->name, PDO::PARAM_STR );
			$st->bindValue( ":code", $this->code, PDO::PARAM_STR );
			$st->bindValue( ":category", $this->category, PDO::PARAM_STR );
			$st->bindValue( ":level", $this->level, PDO::PARAM_STR );
			$st->bindValue( ":meangrade", $this->meangrade, PDO::PARAM_STR );
			$st->bindValue( ":requirements", $this->requirements, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->courseid = $conn->lastInsertId();
			$conn = null;
			return $this->courseid;
		}

		public function update() 
		{
			if ( is_null( $this->courseid ) ) trigger_error ( "course::update(): Attempt to update an course object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE courses SET name=:name, code=:code, category=:category, level=:level, meangrade=:meangrade, requirements=:requirements, updated=:updated WHERE courseid = :courseid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":name", $this->name, PDO::PARAM_STR );
			$st->bindValue( ":code", $this->code, PDO::PARAM_STR );
			$st->bindValue( ":category", $this->category, PDO::PARAM_STR );
			$st->bindValue( ":level", $this->level, PDO::PARAM_STR );
			$st->bindValue( ":meangrade", $this->meangrade, PDO::PARAM_STR );
			$st->bindValue( ":requirements", $this->requirements, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":courseid", $this->courseid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{
			if ( is_null( $this->courseid ) ) trigger_error ( "course::delete(): Attempt to delete an course object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM courses WHERE courseid = :courseid LIMIT 1" );
			$st->bindValue( ":courseid", $this->courseid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
