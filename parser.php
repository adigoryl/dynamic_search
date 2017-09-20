<?php

/*	Used to catch the form's submission data and to trigger an appropiate functions; based on the content.
*/	$searchData = htmlspecialchars($_POST['domain']);	
	$searchData = preg_replace('/\s/', '', $searchData);
	if(isset($searchData) && !empty($searchData)){
		fetchMatchingData($searchData);
	}else{
		echo fileIntoJson("orderdata"); //if empty field submited then display all records
	}
	
/*	Used to look for data that match the content the user is looking for.
	@param searchData The user's keyword that we check whether is contained within the array's elements
	@echo A JSON format string that is caught by the AJAX on index.php
*/	function fetchMatchingData($searchData){
		$jsonData = json_decode(fileIntoJson("orderdata")); //data into php array format
		foreach($jsonData as $current){
			$email = $current->email;
			$domain = strstr($email, '@', false); //look for @ and get its after @ content of a string
			if(strpos($domain, $searchData) !== false){ //check if this is the element we want (1st string contains the 2nd?)
				$matchedData[] = array(
					'id' => $current->id,
					'email' => $current->email,
					'no_of_orders' => $current->no_of_orders,
					'total_order_val' => $current->total_order_val
				);
			}
		}
		echo json_encode($matchedData); //let AJAX grab elements that matched our search
	}
	
/*	Used to put a file's content into an array, validate its data and return in a JSON format
	@param filename The name of the file within our root
	@return JSON format data from the file that passed the validation
*/	function fileIntoJson($filename){	
		$lineCheck 	 = '/^([1-9][0-9]{0,10})(:)([a-zA-Z0-9]+)(@)([a-zA-Z]+)(\\.)([a-zA-Z]+)(:)([1-9][0-9]{0,3})(:)([1-9][0-9]{0,3})/';
		$idCheck 	 = '/^[1-9][0-9]{0,15}$/';
		$fileToArray = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); //file to an array 
		for($i=0; $i<sizeof($fileToArray); $i++){
			$line = preg_replace('/\s/', '', $fileToArray[$i]); //get rid of spaces
			if(preg_match($lineCheck, $line)){ //check for lines with an appropiate format
				$pieces = explode(":", $line); // form a sub-string(s) of a line where ':' appears
				
				if((!preg_match($idCheck, $pieces[0])) &&  //double checks format by pieces
				   (!filter_var($pieces[1], FILTER_VALIDATE_EMAIL)) && 
				   (!is_int($pieces[2]) && $pieces[2] > 0) && 
				   (!is_numeric($pieces[3]) && $pieces[3] > -1 )){
						$i++;
				}else{//correct data format
					$jsonArrayFormat[] = array(
						'id' => $pieces[0],
						'email' => $pieces[1],
						'no_of_orders' => $pieces[2],
						'total_order_val' => $pieces[3]
					);				
				}	
			}; 
		}	
		return json_encode($jsonArrayFormat);		
	}
?>