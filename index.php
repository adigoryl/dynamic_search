<!doctype html>
<html>
	<head>
		<title>Adrian Wesek</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"> </script>
		<link rel="stylesheet" href="styles.css">
		<script type="text/javascript">

		/* 	A listner that triggers an AJAX request to a parser.php file on a form's submit button click.
			On success returns a data collection that is needed to be sorted and displayed
		*/	$("document").ready(function(){
				$('#search').on('submit', function(){
					var input = $(this).serialize();
					$.ajax({
						url: 'parser.php',
						dataType: 'json',
						type: 'post',
						data: input,
						success: function(data){
							$('#error').html("");
							sortAndDisplay(data);
						},
						error : function(){ //occurs when no data returned
							$('#error').html("We couldn't match your domain. Please try a different one or submit an empty box to see all results.");
							$('#results').html("");
							$("#pageButtons").empty();
						}});
				return false;				
				});
			});
			
		/*	A corpus that is used to make the functions calls that are necessary to sort and display the data
			divided into pages.
			@param data The collection we need to sort.	
		*/	function sortAndDisplay(data){
				$sortedByDomains = sortDomains(data);
				$sortedByEmails = sortByEmails($sortedByDomains, "email");
				paginator($sortedByEmails, 20);
				$("#1").trigger("click"); //display the first page
			}
		
		/*	Used to dynamically create buttons and attach event listiners each, which trigger on a click
			and display appropiate data set assigned to each button.
			@param sortedData A sorted array that is ready to be split and displayed into pages
			@param perPage The number of elements we want on each page
		*/	function paginator(sortedData, perPage){
				$("#pageButtons").empty(); //clean the previous set of buttons
				var chunkedArray = chunkArray(sortedData, perPage);
				$pagesNo = chunkedArray.length; //check how mnay pages we need to display data
				for(i = 1; i <= $pagesNo; i++){ 
					$button = $('<button/>',{
						text: i, 
						id: i,
						class: 'paginationLinks',
						click: function(){
							$id = this.id;
							$id = parseInt($id); //string into integer
							$data = intoTable(chunkedArray[$id-1]);
							$('#results').html($data);
							//highlight the current active button
							$(this).siblings().removeClass('activeButton');
							$(this).addClass('activeButton');
						}
					});
					$("#pageButtons").append($button);	
				}				
			}
		
		/*	Used to sort a dataset based on an email property in ASC order, while maintaning the order of Domains within the list,
			this function should only take a list that has sorted Domains.
			@param data A dataset that is sorted based on domains.
			@param prop A property name within the object of the 'data' that we will sort based on.
			@return result An array that is sorted based on a property while maitaning the Domains order
		*/	function sortByEmails(data, prop){
				var result = [];
				var splitByDoms = [];
				$currDom = extractDomain(data[0]);
				
				$.each(data, function(i, object){
					$nextDom = extractDomain(object);
					if($currDom == $nextDom){
						splitByDoms.push(object);
					}else{
						$.merge(result, sortASC(splitByDoms, prop));
						$currDom = $nextDom;
						clearArray(splitByDoms);
					}	
				});
				//merges a first&only or last group of domains - needed here as would never enter into else statment where sorting and merging is done
				$.merge(result, sortASC(splitByDoms, prop));
				return result;
			}
						
		/* 	Used to put an array into table.
			@param data The content we want to put into a table.
			@return contentTable The data merged with html tags used to display a table.
		*/	function intoTable(data){
				$currDom = extractDomain(data[0]);
				$contentTable = '<tr> <th>Email</th> <th>Number of Orders</th> <th>Order Total</th> </tr>';
				$contentTable += '<tr> <th colspan="3">' + $currDom + '</th> </tr>';
				
					$.each(data, function(i, object){
						$nextDom = extractDomain(object);
						if($currDom == $nextDom){
							$contentTable += '<tr>'
								+ '<td>' + data[i].email + '</td>'
								+ '<td>' + data[i].no_of_orders + '</td>'
								+ '<td>' + data[i].total_order_val + '</td>'
							+ '</tr>';
						}else{
							$contentTable += '<tr> <th colspan="3">' + $nextDom + '</th> </tr>';
							$currDom = $nextDom;
						}
					});
				return $contentTable;
			}		
			
		/*	Used to sort an array in an ASC order.
			@param array List of object to sort.
			@param prop A property of an array's object, that we want to sort based on.
			@return array Sorted array in an ASC order
		*/	function sortASC(array, prop){
				array.sort(function(a, b){
					return (a[prop] > b[prop]) ? 1 : ((a[prop] < b[prop]) ? -1 : 0);
				});
				return array;
			}
			
		/*	Used to sort a dataset in ASC order based on a domain name; extracted from an object's property.
			@param data An array of data to sort.
			@return data Sorted array based on domain names of an email property.
		*/	function sortDomains(data){
				data = data.sort(function(curr, next){
					$currDomain = extractDomain(curr);
					$nextDomain = extractDomain(next);
					// if curr is greater then return 1, otherwise, if curr is smaller then return -1, otherwise return 0
					return ($currDomain > $nextDomain) ? 1 : (($currDomain < $nextDomain) ? -1 : 0);
				});
				return data;
			}
			
		/* 	Used to extract a domain part from an email address.
			@param o A signle object containing an email field.
			@return A domain from the email.
		*/	function extractDomain(o){
				$domain = o.email.split('@'); //split returns left side of an email under $domain[0] and right under $domain[1]
				return $domain[1].toLowerCase();
			}
			
		/*	Used to divide a big array into sub-arrays of given size.
			@param array The big array that we want to split
			@param chunkSize The maximum size of the sub-arrays
			@return results An array contaning the sub-arrays
		*/	function chunkArray(array, chunkSize){
				var results = [];
				while (array.length) {
					results.push(array.splice(0, chunkSize));
				}
				return results;
			}
				
		/*	A function used to clear an array of arrays.
			@param a An array to clear.
		*/	function clearArray(a){
				while(a.length > 0) {
					a.pop();
				}
			}
			
		</script>
	</head>
	
	<body>	
		<form action="index.php" method="post" id="search">
			<span>Enter a domain name:</span><br>
			<input type="text" name="domain" placeholder="e.g. able.com">
			<input type="submit" value="Search">
		</form>
		<br/>
		<div id="error"></div>
		<div id="currentPage"></div><br>
		<div id="pageButtons"></div><br>
		<div id="results"></div>

	</body>
</html>

