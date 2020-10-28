<?php
	function tableCreate( $table,  $variables = array() ) 
	{
		try {
			$fields = array();
			$values = array();
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE IF NOT EXISTS ". $table;
			foreach( $variables as $field ) $fields[] = $field;
			$fields = ' (' . implode(', ', $fields) . ')';      
			$sql .= $fields;
			$conn->exec( $sql );
		} catch(PDOException $exception) {
			$as_err['errno'] = 3;
			$as_err['errtitle'] = 'Database action failed';
			$as_err['errsumm'] = 'Creating the table '. $table . ' failed';
			$as_err['errfull'] = $exception->getMessage();
		}
		$conn = null;
	}
	
	function createTables()
	{
		tableCreate( 'admins', 
			array(//adminid, name, email, password, created, updated
				'adminid int(11) NOT NULL AUTO_INCREMENT',
				'name varchar(50) NOT NULL',
				'email varchar(200) NOT NULL',
				'password text NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (adminid)',
				'UNIQUE email_address(email)',
			)
		);
		
		tableCreate( 'courses',  
			array(//name, code, category, level, meangrade, requirements, created, updated
				'courseid int(11) NOT NULL AUTO_INCREMENT',
				'name varchar(100) NOT NULL',
				'code varchar(100) NOT NULL',
				'category varchar(50) NOT NULL',
				'level varchar(50) NOT NULL',
				'meangrade varchar(50) NOT NULL',
				'requirements varchar(100) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (courseid)',
				'UNIQUE course_code(code)',
			)
		);
		
		tableCreate( 'options',
			array(
				'optionid int(11) NOT NULL AUTO_INCREMENT',
				'title varchar(100) NOT NULL',
				'content varchar(2000) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (optionid)',
			)
		); 
		
		tableCreate( 'students',  
			array(//studentid, fullname, idnumber, kcseyear, kcsegrade, kcsescores, email, password, created, updated
				'studentid int(11) NOT NULL AUTO_INCREMENT',
				'fullname varchar(50) NOT NULL',
				'idnumber varchar(50) NOT NULL',
				'kcseyear varchar(10) NOT NULL',
				'kcsegrade varchar(10) NOT NULL',
				'kcsescores varchar(100) NOT NULL',
				'email varchar(100) NOT NULL',
				'password int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (studentid)',
				'UNIQUE email_address(email)',
			)
		);
		
	}
	createTables();
	
	function checkTables( $table ) 
	{
		$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
		$sql = "SELECT * FROM " . $table . " LIMIT 1";
		$st = $conn->prepare( $sql );
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if ( $row ) return 0;
		else return 1;
	}
	
	function splitByLines( $strLine )
	{
		$strlines = explode( "\r\n", $strLine );
		return $strlines;
	}
	