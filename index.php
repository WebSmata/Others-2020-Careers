<?php

	require( "config.php" );
	session_start();
	$open = isset( $_GET['open'] ) ? $_GET['open'] : "";

	$content = array();
	$content['sitename'] = strlen(as_option('sitename')) ? as_option('sitename') : SITENAME;
	$adminid = isset( $_SESSION['loggedin_adminid'] ) ? $_SESSION['loggedin_adminid'] : "";
	$level = isset( $_SESSION['loggedin_level'] ) ? $_SESSION['loggedin_level'] : "";
	$adminame = isset( $_SESSION['loggedin_adminidame'] ) ? $_SESSION['loggedin_adminidame'] : "";
	
	$courselevel['deg'] = 'Degree';
	$courselevel['dip'] = 'Diploma';
	$courselevel['cert'] = 'Certificate';

	$coursecats['eng'] = 'Engineering';
	$coursecats['med'] = 'Medical';
	$coursecats['law'] = 'Law';
	$coursecats['bs'] = 'Business';

	if ($open == 'install') {
		errMissingTables();
		exit();
	}
	
	if ( $open != "signin" && $open != "signout" && $open != "register" && !$adminid ) {
		$open = 'signin';
	}

	switch ( $open ) {
		case 'signin':
			require( CORE . "admin.php" );
			$content['admin'] = new admin;
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array( 
						'email' => array('label' => 'Email Address:', 'type' => 'text'),				
						'password' => array('label' => 'Password:', 'type' => 'password'),
					),
			
					'buttons' => array('signin' => array('label' => 'Login to your Account')),			
				);
			
			$content['title'] = "Login to Your Account";
			if ( isset( $_POST['signin'] ) ) {
				$adminid = admin::signinuser($_POST['email'], md5($_POST['password']));
				if (isset($adminid)) {
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Incorrect username or password. Please try again.";
				}
			}
			break;

		case 'register':
			require( CORE . "admin.php" );
			$content['admin'] = new admin;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array( 
						'name' => array('label' => 'Full Name:', 'type' => 'text', 'tags' => 'required '),
						'email' => array('label' => 'Email:', 'type' => 'email', 'tags' => 'required '),
						'password' => array('label' => 'Password:', 'type' => 'password', 'tags' => 'required '),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array('register' => array('label' => 'Register')),
				);
			
			$content['title'] = "Register as a Admin";
			if ( isset( $_POST['register'] ) ) {
				$admin = new admin;
				$admin->storeFormValues( $_POST );
				$adminid = $admin->insert();
				if ($adminid) {
					$_SESSION['loggedin_level'] = $_POST['level'];
					$_SESSION['loggedin_adminid'] = $adminid;
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Unable to register you at the moment. Please try again later.";
				}
			}
			break;
		
		case 'course_new'://name, code, category, level, requirements 
			require( CORE . "course.php" );
			$content['class'] = new course;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array(
						'name' => array('label' => 'Course Name:', 'type' => 'text', 'tags' => 'required '),
						'code' => array('label' => 'Course Code:', 'type' => 'text', 'tags' => 'required '),
						'category' => array('label' => 'Course Category:', 'type' => 'select', 'tags' => 'required ', 'options' => $coursecats, 'value' => 1),
						'level' => array('label' => 'Course Level:', 'type' => 'select', 'tags' => 'required ', 'options' => $courselevel, 'value' => 1),
						'meangrade' => array('label' => 'Mean Grade Requirement:', 'type' => 'text', 'tags' => 'required '),
						'requirements' => array('label' => 'Other Requirements for this Course (One per line i.e C+ in Mathematics):', 'type' => 'textarea', 'rows' => 2, 'tags' => 'required '),
					),
					
					'hidden' => array('admin' => 1),		
					'buttons' => array(
						'saveclose' => array('label' => 'Save & Close'),
						'saveadd' => array('label' => 'Save & Add'),
					),
				);
			
			$content['title'] = "Add a Course";
			if ( isset( $_POST['saveclose'] ) ) {
				$class = new course;
				$class->storeFormValues( $_POST );
				$courseid = $class->insert();
				if ($courseid) {
					header( "Location: index.php?open=course_all" );
				} else {
					$content['errorMessage'] = "Unable to add a course at the moment. Please try again later.";
				}
			} else if ( isset( $_POST['saveadd'] ) ) {
				$class = new course;
				$class->storeFormValues( $_POST );
				$courseid = $class->insert();
				if ($courseid) {
					header( "Location: index.php?open=class_new" );
				} else {
					$content['errorMessage'] = "Unable to add a course at the moment. Please try again later.";
				}
			}
			break;
			
		case 'course_view':
			require( CORE . "course.php" );
			$courseid = $_GET["courseid"];
			$course = course::getById( (int)$courseid );
			$content['title'] = "Edit Course";
			$content['link'] = '<a href="index.php?open=course_delete&&courseid='.$courseid.'" onclick="return confirm(\'Delete This Course? This action is irrevesible!\')" style="float:right;">DELETE Course</a>';	
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open.'&&courseid='.$courseid,
					'fields' => array(
						'name' => array('label' => 'Course Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $course->name),
						'code' => array('label' => 'Course Code:', 'type' => 'text', 'tags' => 'required ', 'value' => $course->code),
						'category' => array('label' => 'Course Category:', 'type' => 'select', 'tags' => 'required ', 'options' => $coursecats, 'value' => 1),
						'level' => array('label' => 'Course Level:', 'type' => 'select', 'tags' => 'required ', 'options' => $courselevel, 'value' => 1),
						'meangrade' => array('label' => 'Mean Grade Requirement:', 'type' => 'text', 'tags' => 'required ', 'value' => $course->meangrade),
						'requirements' => array('label' => 'Other Requirements for this Course (One per line i.e C+ in Mathematics):', 'type' => 'textarea', 'rows' => 2, 'tags' => 'required ', 'value' => $course->requirements),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
						'cancel' => array('label' => 'Cancel Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$class->storeFormValues( $_POST );
				$class->update();
				header( "Location: index.php?open=course_view&&courseid=".$courseid."&&status=changesSaved" );
			} elseif ( isset( $_POST['cancel'] ) ) {
				header( "Location: index.php?open=course_all" );
			} 
			break;
			
		case 'course_all':
			require( CORE . "course.php" );
			$adminid = $_SESSION["loggedin_adminid"];
			$dbitems = course::getList( $adminid );
			$listitems = array();
			foreach ( $dbitems as $dbitem ) {
				$listitems[$dbitem->courseid] = array($dbitem->name, $dbitem->code, $dbitem->category, $dbitem->level, $dbitem->requirements);
			}
			
			$content['title'] = "Courses (".count($dbitems).")";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'name', 'code', 'category', 'level', 'requirements' ),
					'items' => $listitems,
					'onclick' => 'open=course_view&&courseid=',
				);
			$content['link'] = '<a href="index.php?open=course_new" style="float:right">Add a Course</a>';
			
			break;
		
		case 'student_new':
			require( CORE . "student.php" );
			$html = '<table style="width: 560px;"><tr>';
			$html .= '<td style="width: 25%;"><label>KCSE Year: </label></td>';
			$html .= '<td style="width: 25%;"><select name="kcseyear" class="input_field" style="width: 100%;">
				<option value="2019">2019</option>
				<option value="2018">2018</option>
				<option value="2017">2017</option>
				<option value="2016">2016</option>
				<option value="2015">2015</option>
				<option value="2014">2014</option>
				<option value="2013">2013</option>
				<option value="2012">2012</option>
				<option value="2011">2011</option>
				<option value="2010">2010</option>
				</select></td>';
			$html .= '<td> </td>';
			$html .= '<td style="width: 25%;"><label>KCSE Mean Grade: </label></td>';
			$html .= '<td style="width: 25%;"><select name="kcsegrade" class="input_field" style="width: 100%;">
				<option value="A">A Plain</option>
				<option value="A-">A- (Minus)</option>
				<option value="B+">B+ (Plus)</option>
				<option value="B">B Plain</option>
				<option value="B-">B- (Minus)</option>
				<option value="C+">C+ (Plus)</option>
				<option value="C">C Plain</option>
				<option value="C-">C- (Minus)</option>
				<option value="D+">D+ (Plus)</option>
				<option value="D">D Plain</option>
				<option value="D-">D- (Minus)</option>
				<option value="E">E </option>
				</select></td>';
			$html .= '</tr></table>';
			$content['student'] = new student;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array(
						'fullname' => array('label' => 'Full Name:', 'type' => 'text', 'tags' => 'required '),
						'idnumber' => array('label' => 'ID Number:', 'type' => 'text', 'tags' => 'required '),
						'kcse' => array('label' => '', 'type' => 'custom', 'html' => $html),
						'kcsescores' => array('label' => 'Subject scores (One per line i.e Mathematics: C+):', 'type' => 'textarea', 'rows' => 2, 'tags' => 'required '),
						'email' => array('label' => 'Email Address:', 'type' => 'text', 'tags' => 'required '),
						'password' => array('label' => 'Password:', 'type' => 'password', 'tags' => 'required '),
					),
					
					'hidden' => array('admin' => 1),		
					'buttons' => array(
						'saveclose' => array('label' => 'Save & Close'),
						'saveadd' => array('label' => 'Save & Add'),
					),
				);
			
			$content['title'] = "Add a Student";
			if ( isset( $_POST['saveclose'] ) ) {
				$student = new student;
				$student->storeFormValues( $_POST );
				$studentid = $student->insert();
				if ($studentid) {
					header( "Location: index.php?open=student_all" );
				} else {
					$content['errorMessage'] = "Unable to add a student at the moment. Please try again later.";
				}
			} else if ( isset( $_POST['saveadd'] ) ) {
				$student = new student;
				$student->storeFormValues( $_POST );
				$studentid = $student->insert();
				if ($studentid) {
					header( "Location: index.php?open=student_new" );
				} else {
					$content['errorMessage'] = "Unable to add a student at the moment. Please try again later.";
				}
			}
			break;
		
		case 'student_view':
			require_once CORE .  'base.php';
			require( CORE . "student.php" );
			require( CORE . "course.php" );
			$studentid = $_GET["studentid"];
			$adminid = $_SESSION["loggedin_adminid"];
			$dbitems = course::getList( $adminid );
			
			$student = student::getById( (int)$studentid );
			$scores = implode(", ", splitByLines($student->kcsescores));
			$content['title'] = $student->fullname . ' (ID: '.$student->idnumber.')';
			$content['custom'] = '<table style="width: 100%; font-size: 18px;line-height: 25px;">
				<tr><td>Email  Address: </td><td> : </td><td>' . $student->email . '</td></tr>
				<tr><td>KCSE Year </td><td> : </td><td>' . $student->kcseyear . '</td></tr>
				<tr><td>Mean Grade </td><td> : </td><td>' . $student->kcsegrade . '</td></tr>
				<tr><td>Subject Scores </td><td> : </td><td>' . $scores . '</td></tr>
				</table><hr><br><h3>Available Courses for this student:</h3>';
			
			$listitems = array();
			foreach ( $dbitems as $dbitem ) {
				$listitems[$dbitem->courseid] = array($dbitem->name, $dbitem->code, $dbitem->category, $dbitem->level, $dbitem->requirements);
			}
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'name', 'code', 'category', 'level', 'requirements' ),
				'items' => $listitems,
				'onclick' => 'open=course_view&&courseid=',
			);
			$content['link'] = '<a href="index.php?open=student_edit&&studentid='.$studentid.'" style="float:right;">EDIT student</a>';
			break;
			
		case 'student_edit':
			require( CORE . "student.php" );
			$studentid = $_GET["studentid"];
			$student = student::getById( (int)$studentid );
			$content['title'] = "Edit student";
			$content['link'] = '<a href="index.php?open=student_delete&&studentid='.$studentid.'" onclick="return confirm(\'Delete This student? This action is irrevesible!\')" style="float:right;">DELETE student</a>';	
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open.'&&studentid='.$studentid,
					'fields' => array(
						'fullname' => array('label' => 'Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $student->fullname),
						'idnumber' => array('label' => 'ID Number:', 'type' => 'text', 'tags' => 'required ', 'value' => $student->idnumber),
						'kcseyear' => array('label' => 'KCSE Year:', 'type' => 'text', 'tags' => 'required ', 'value' => $student->kcseyear),
						'kcsegrade' => array('label' => 'KCSE Grade:', 'type' => 'text', 'tags' => 'required ', 'value' => $student->kcsegrade),
						'email' => array('label' => 'Email Address:', 'type' => 'text', 'tags' => 'required ', 'value' => $student->email),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
						'cancel' => array('label' => 'Cancel Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$student->storeFormValues( $_POST );
				$student->update();
				header( "Location: index.php?open=student_view&&studentid=".$studentid."&&status=changesSaved" );
			} elseif ( isset( $_POST['cancel'] ) ) {
				header( "Location: index.php?open=student_all" );
			} 
			break;
			
		case 'account':
			require( CORE . "admin.php" );
			$content['admin'] = admin::getById( (int)$_SESSION["loggedin_adminid"] );
			$content['title'] = $content['admin']->firstname . ' ' .$content['admin']->lastname.
			' '.($content['admin']->sex == 1 ? '(M)' : '(F)' );
			break;
			
		case 'signout';
			unset( $_SESSION['loggedin_level'] );
			unset( $_SESSION['loggedin_adminidame'] );
			unset( $_SESSION['loggedin_adminid'] );
			header( "Location: index.php" );
			break;
				
		case 'database';
			errMissingTables();
			break;
		 	
		case 'admin_all':
			require( CORE . "admin.php" );
			$admins = admin::getList(5);
			$listitems = array();
			foreach ( $admins as $admin ) {
				$listitems[] = array($admin->name, $admin->email, $admin->created);
			}
			
			$content['title'] = "Admins";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'Name', 'email', 'joined'), 
					'items' => $listitems,
				);
			break;
			
		case 'settings':
			$content['title'] = "Your Site Preferences";
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?open='.$open,
					'fields' => array( 
						'sitename' => array('label' => 'Site Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $content['sitename']),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$sitename = $_POST['sitename'];
				as_update_option('sitename', $sitename);
				
				$filename = "config.php";
				$lines = file($filename, FILE_IGNORE_NEW_LINES );
				$lines[12] = '	define( "SITENAME", "'.$sitename.'"  );';
				file_put_contents($filename, implode("\n", $lines));
		
				header( "Location: index.php?pg=settings&&status=changesSaved" );
			} 
			break;
		
		default:
			require( CORE . "student.php" );
			$dbitems = student::getList();
			$listitems = array();
			foreach ( $dbitems as $dbitem ) {
				$listitems[$dbitem->studentid] = array($dbitem->fullname, $dbitem->idnumber, $dbitem->kcseyear, $dbitem->kcsegrade, $dbitem->email);
			}
			
			$content['title'] = "Student (".count($listitems).")";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'full name', 'id. number', 'kcse year', 'kcse grade', 'email address' ),
					'items' => $listitems,
					'onclick' => 'open=student_view&&studentid=',
				);
			$content['link'] = '<a href="index.php?open=student_new" style="float:right">Add a Student</a>';
			
			break;
				
	}
	
	require ( CORE . "page_index.php" );