<?php
// certuploadscript.php
// Upload and Rename File

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

$default = "DEFAULT";
$uid = $_POST['userid'];
$url = $_POST['url'];
$cert = $_POST['options'];
$date = $_POST['date'];

if (isset($_POST['submit']))
{
	global $wpdb;
	$wpdb->show_errors();

	$certdatabase=$wpdb->prefix.'certdatabase';

	$filename = $_FILES["file"]["name"];
	$file_basename = substr($filename, 0, strripos($filename, '.')); // get file extention
	$file_ext = substr($filename, strripos($filename, '.')); // get file name
	$filesize = $_FILES["file"]["size"];
	$allowed_file_types = array('.doc','.docx','.rtf','.pdf','.PDF','.jpg','.JPG','.JPEG','.png');

	//START OF UPDATED FILE CHECKER
	// first check if data exists with select query
	$datum = $wpdb->get_results("SELECT * FROM $certdatabase WHERE cert_type LIKE '$cert' AND userid = '$uid'");
	 if($wpdb->num_rows > 0) {
	 echo "result exists";
	 //BEGIN DELETING OF OLD ROWS FUNCTIONALITY HERE
	// LOOP THROUGH SELECTED ROWS IN DATABASE AND GET VALUE FOR THE OLD FILENAME
	foreach($datum as $datu){
		$oldfilename = $datu->filename;
		echo $oldfilename;

		//NOW DELETE ACTUAL FILE FROM SERVER
		$path= $_SERVER['DOCUMENT_ROOT'] ."/upload/certificates/$oldfilename"; 

		echo $path;

    if (file_exists($path)) {
        unlink($path);

				$wpdb->query('DELETE  FROM '.$wpdb->prefix.'certdatabase
               WHERE filename = "'.$oldfilename.'"');
							 echo "Delete from database successful!";

        echo "Deleted File";
    } else {
        // File not found.
        echo "File not found!";
    }

		}
	 }
	// if not exist in the database then insert it
	else{
	 echo "no results found";
	}
	//END OF UPDATED FILE CHECKER
	
	if (in_array($file_ext,$allowed_file_types) && ($filesize < 5000000))
	{	
		// Rename file
		//$newfilename = md5($file_basename) . $file_ext;
    	$unique = uniqid();
		//$newfilename = $uid . $file_ext;
        $newfilename = $unique . $file_ext;
    
		if (file_exists("upload/certificates/" . $newfilename))
		{
			// file already exists error
			echo "File uploaded successfully.";
        	move_uploaded_file($_FILES["file"]["tmp_name"], "upload/certificates/" . $newfilename);
        
			$data=array(
            'id' => $default,
        	'userid' => $uid, 
       		'filename' => $newfilename,
        	'cert_type' => $cert,
            'date' => $date);

     		$wpdb->insert( $certdatabase, $data);      

				//NEW CODE 
				//Update Meta to add certificate to checker database
				$yes = 'Yes';
				update_user_meta($uid, $cert, $yes); 

				//Update Meta to add certificate expiry date to checker database
				$exdate = 'Expiry';
				$fulldatemeta = $exdate . $cert;
				update_user_meta($uid, $fulldatemeta, $date); 
        
        	header( 'Location:'.$url ) ;
        	echo "File uploaded successfully.";
		}
		else
		{		
			move_uploaded_file($_FILES["file"]["tmp_name"], "upload/certificates/" . $newfilename);
        
 			$data=array(
            'id' => $default,
        	'userid' => $uid, 
       		'filename' => $newfilename,
        	'cert_type' => $cert,
            'date' => $date);

     		$wpdb->insert( $certdatabase, $data);        

				//NEW CODE 
				//Update Meta to add certificate to checker database
				$yes = 'Yes';
				update_user_meta($uid, $cert, $yes); 

				//Update Meta to add certificate expiry date to checker database
				$exdate = 'Expiry';
				$fulldatemeta = $exdate . $cert;
				update_user_meta($uid, $fulldatemeta, $date); 

							 
          header( 'Location:'.$url ) ;
        	echo "File uploaded successfully.";
		}
	}
	elseif (empty($file_basename))
	{	
		// file selection error
		echo "Please select a file to upload.";
	} 
	elseif ($filesize > 5000000)
	{	
		// file size error
		echo "The file you are trying to upload is too large.";
    	sleep(3);
    	header( 'Location:'.$url ) ;
	}
	else
	{
		// file type error
		echo "Only these file types are allowed for upload: " . implode(', ',$allowed_file_types);
		unlink($_FILES["file"]["tmp_name"]);
    	sleep(5);
    	header( 'Location:'.$url ) ;
	}
}

?>
