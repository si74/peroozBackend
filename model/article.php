<?php

/*PHP Article class for article in db*/
/*Date: 6-4-2014*/
/*Author:Sneha Inguva*/

class article{

	/*Is this model being used in an api*/
	protected $api = false; 

	/*Usually autmoatically set in constructor or via set method*/
	protected $article_id;
	protected $perooz_article_id;
	protected $article_title;
	protected $author_id; 
	protected $perooz_author_id;
	protected $source_id;
	protected $perooz_source_id; 
	protected $source_hyperlink;
	protected $article_hyperlink;
	protected $approved;
	protected $date; 

	/*Not automatically set - must call function to get these values*/
	protected $note_list=array(); //general list of all notes
	protected $notegroup_list = array();  //general list of all note groups
	protected $notegroup_asslist=array(); //general associative array btw note groups and notes

	/*Statements to pull the important values*/
	private $insert_stmt = 'Insert Into Articles (PeroozArticleId,ArticleTitle,AuthorId,SourceId,SourceHyperlink,ArticleHyperlink,Approved) Values (:PeroozArticleId,:ArticleTitle,:AuthorId,:SourceId,:SourceHyperlink,:ArticleHyperlink,:Approved)';
	private $select_stmt = 'Select ArticleId,PeroozArticleId,ArticleTitle,AuthorId,SourceId,SourceHyperlink,ArticleHyperlink,Approved From Articles Where ';
	private $update_stmt = 'Update Articles Set';

	private $note_list_stmt = 'Select NoteId From Notes Where ArticleId=:ArticleId';
	private $notegroup_stmt = 'Select NoteGroupId From NoteGroups Where ArticleId=:ArticleId';
	private $notegroup_list_stmt = 'Select NoteId From Notes Where NoteGroupId=:NoteGroupId';

	private $note_list_stmt_pz = 'Select PeroozNoteId From Notes Where ArticleId=(Select ArticleId From Articles Where PeroozArticleId=:PeroozArticleId)';
	private $notegroup_stmt_pz = 'Select PeroozNoteGroupId From NoteGroups Where ArticleId=(Select ArticleId From Articles Where PeroozArticleId=:PeroozArticleId)';
	private $notegroup_list_stmt_pz = 'Select PeroozNoteId From Notes Where NoteGroupId=(Select NoteGroupId From NoteGroups Where PeroozNoteGroupId=:PeroozNoteGroupId)';

	private $search_stmt = 'Select PeroozArticleId,ArticleTitle,AuthorId,SourceId,SourceHyperlink,ArticleHyperlink,Approved From Articles Where ';

	private $prop_parameters = array(':PeroozArticleId' => PDO::PARAM_STR,
									 ':ArticleTitle' => PDO::PARAM_INT, 
									 ':AuthorId' => PDO::PARAM_INT, 
							    	 ':SourceId' => PDO::PARAM_INT, 
								     ':SourceHyperlink' => PDO::PARAM_STR, 
									 ':ArticleHyperlink' => PDO::PARAM_STR,
									 ':ArticleId' => PDO::PARAM_INT,
									 ':NoteGroupId' => PDO::PARAM_INT,
									 ':Approved' => PDO::PARAM_BOOL);

	
	/*Constructor with a variable number of arguments*/
	/*Article can be set using primary key or perooz uuid*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){ //value set from argument array
				$this->$key = $value; 
			}
			if (isset($this->con)){ //if database connection and key given, set from db
				if (isset($this->article_id) || isset($this->perooz_article_id)){
					$properly_set = $this->set_from_db($this->con);
					if (!$properly_set){
						echo 'This has not worked.';
					}
				}
			}
		}
		$this->date = time(); 
	}

	/*Destructor for article object*/
	function __destruct(){

	}

	/*Function get method*/
	public function __get($name){
		return $this->$name;
	}

	/*Function set method*/
	public function __set($name, $value){
		$this->$name = $value;
	}

	/*Insert object values to database*/
	public function insert_db($con){

		$result = false; 

		/*Set the article uuid*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_article_id = 'pz_'.$uid['uuid()']; 

		if ($this->api){

			//grab the author id given the perooz id
			$auth_stmt = 'Select AuthorId From Authors Where PeroozAuthorId=:PeroozAuthorId';
			$auth_val = array(':PeroozAuthorId' => $this->perooz_author_id);
			$auth_prop = array(':PeroozAuthorId' => PDO::PARAM_STR); 
			$auth_result = $con->multi_query($auth_stmt,$auth_val,$auth_prop);

			//grab the source id given the perooz id
			$source_stmt = 'Select SourceId From Sources Where PeroozSourceId=:PeroozSourceId';
			$source_val = array(':PeroozSourceId' => $this->perooz_source_id);
			$source_prop = array(':PeroozSourceId' => PDO::PARAM_STR);
			$source_result = $con->multi_query($source_stmt,$source_val,$source_prop);

			if (!empty($auth_result) && !empty($source_result)){
				$values = array(':PeroozArticleId' => $this->perooz_article_id,
								':ArticleTitle' => $this->article_title,
								':AuthorId' => $auth_result[0]['AuthorId'],
								':SourceId' => $source_result[0]['SourceId'],
								':SourceHyperlink' => $this->source_hyperlink,
								':ArticleHyperlink' => $this->article_hyperlink,
								':Approved' => $this->approved);
				
				foreach($values as $key => $value){
					$value_options[$key] = $this->prop_parameters[$key];
				}

				$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'Article');
			}

			return $result;

		}else{
			/*Complete the insert query*/
			$values = array(
				':PeroozArticleId' => $this->perooz_article_id,
				':ArticleTitle' => $this->article_title,
				':AuthorId' => $this->author_id,
				':SourceId' => $this->source_id,
				':SourceHyperlink' => $this->source_hyperlink,
				':ArticleHyperlink' => $this->article_hyperlink,
				':Approved' => $this->approved);
			
			foreach($values as $key => $value){
				$value_options[$key] = $this->prop_parameters[$key]; 
			}

			$result = $con->insert($this->insert_stmt, $values,$value_options,$this->api,'Article');

			return $result;
		}
		
	}

	/*Set object values from database*/
	public function set_from_db($con){

		$returned_thing = false; 

		/*Finalize query stmt and bound values based upon whether uid or primary key is being used*/
		if (isset($this->article_id)){
			$values = array(':ArticleId' => $this->article_id);
			$final_stmt = $this->select_stmt.'ArticleId=:ArticleId';
		}elseif(isset($this->perooz_article_id)){
			$values = array(':PeroozArticleId' => $this->perooz_article_id);
			$final_stmt = $this->select_stmt.'PeroozArticleId=:PeroozArticleId';
		}

		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($result)){

			if ($this->api){

				$auth_stmt = 'Select PeroozAuthorId From Authors Where AuthorId=:AuthorId';
				$auth_val = array(':AuthorId' => $result[0]['AuthorId']);
				$auth_prop = array(':AuthorId' => PDO::PARAM_INT);
				$auth_result = $con->multi_query($auth_stmt,$auth_val,$auth_prop);

				$source_stmt = 'Select PeroozSourceId From Sources Where SourceId=:SourceId';
				$source_val = array(':SourceId' => $result[0]['SourceId']);
				$source_prop = array(':SourceId' => PDO::PARAM_INT);
				$source_result = $con->multi_query($source_stmt,$source_val,$source_prop);

				if (!empty($auth_result) && !empty($source_result)){
					$returned_thing = true; 
					
					$this->perooz_article_id = $result[0]['PeroozArticleId'];
					$this->article_title = $result[0]['ArticleTitle'];
					$this->perooz_author_id = $auth_result[0]['PeroozAuthorId'];
					$this->perooz_source_id = $source_result[0]['PeroozSourceId'];
					$this->source_hyperlink = $result[0]['SourceHyperlink']; 
					$this->article_hyperlink = $result[0]['ArticleHyperlink'];
					$this->approved = $result[0]['Approved'];
				}

			}else{

				$returned_thing = true; 

				$this->article_id = $result[0]['ArticleId'];
				$this->perooz_article_id = $result[0]['PeroozArticleId'];
				$this->article_title = $result[0]['ArticleTitle'];
				$this->author_id = $result[0]['AuthorId'];
				$this->source_id = $result[0]['SourceId'];
				$this->source_hyperlink = $result[0]['SourceHyperlink']; 
				$this->article_hyperlink = $result[0]['ArticleHyperlink'];
				$this->approved = $result[0]['Approved'];
			}

		}

		return $returned_thing;
	}

	/*Update database values*/
	/*Argument $values specifies which values to update. 
	  In the form $values = array(':xxx' => yyy, ':bbb' => ccc)*/
	/*NOTE: UPDATE Cannot update perooz author id or perooz source id - not updated to handle api stuff*/
	public function update_to_db($values,$con){

		$result = false; 

		$stmt_set = '';

		if (array_key_exists(':ArticleId',$values)){
			$stmt_where = 'ArticleId=:ArticleId';
		}elseif(array_key_exists(':PeroozArticleId', $values)){
			$stmt_where = 'PeroozArticleId=:PeroozArticleId';
		}

		$count_value = 1; 
		foreach ($values as $key => $value){
			
			if ($key == ':PeroozAuthorId'){
				$auth_stmt = 'Select AuthorId From Authors Where PeroozAuthorId=:PeroozAuthorId';
				$auth_val = array(':PeroozAuthorId' => $value);
				$auth_prop = array(':PeroozAuthorId' => PDO::PARAM_STR);
				$auth_result = $con->multi_query($auth_stmt,$auth_val,$auth_prop);
				$key_final = ':AuthorId';
				if (!empty($auth_result)){
					$values_final[$key_final] = $auth_result[0]['AuthorId'];
					$value_options[$key_final] = $this->prop_parameters[$key_final];
				}else{
					return $result;
				}
			}else if($key == ':PeroozSourceId'){
				$source_stmt = 'Select SourceId From Sources Where PeroozSourceId=:PeroozSourceId';
				$source_val = array(':PeroozSourceId' => $value);
				$source_prop = array(':PeroozSourceId' => PDO::PARAM_STR);
				$source_result = $con->multi_query($source_stmt,$source_val,$source_prop);
				$key_final = ':SourceId';
				if (!empty($source_result)){
					$values_final[$key_final] = $source_result[0]['SourceId'];
					$value_options[$key_final] = $this->prop_parameters[$key_final];
				}else{
					return $result;
				}
			}else{
				$key_final = $key; 
				$values_final[$key_final] = $value;
				$value_options[$key_final] = $this->prop_parameters[$key_final];
			}

			
			if ($count_value == 1){
				$stmt_set = $stmt_set.substr($key_final,1).'='.$key_final; 
			}else{
				$stmt_set = $stmt_set.','.substr($key_final,1).'='.$key_final;
			}

			$count_value++;
		}

		$stmt = $this->update_stmt.' '.$stmt_set.' Where '.$stmt_where;
		$result = $con->update($stmt,$values_final,$value_options); 

		return $result;
	}

	/*STOPPED HERE!*/
	/*Get associative array of note groups to note list*/
	public function set_notegroup_asslist($con){

		$returned_thing = false;

		if ($this->api){
			$set_key1 = 'PeroozNoteGroupId';
			$set_key2 = 'PeroozNoteId';
			$stmt1 = $this->notegroup_stmt_pz;
			$stmt2 = $this->notegroup_list_stmt_pz;
			$values = array(':PeroozArticleId' => $this->perooz_article_id);
		}else{
			$set_key1 = 'NoteGroupId';
			$set_key2 = 'NoteId';
			$stmt1 = $this->notegroup_stmt;
			$stmt2 = $this->notegroup_list_stmt;
			$values = array(':ArticleId' => $this->article_id);
		}
	
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}
		$results = $con->multi_query($stmt1,$values,$value_options);

		if (!empty($results)){
			$returned_thing = true;
			foreach($results as $row){
				$temp_id = $row[$set_key1]; 
				if (!empty($temp_id)){
					$values2 = array(':'.$set_key1 => $temp_id);
					foreach($values2 as $key => $value2){
						$value_options2[$key] = $this->prop_parameters[$key];
					}
					$results2 = $con->multi_query($stmt2,$values2,$value_options2);
					foreach($results2 as $row){
						$temp_array[] = $row[$set_key2];
					}
					$this->notegroup_asslist[$temp_id] = $temp_array; 
				}
			}
		}

		return $returned_thing;

	}

	/*Get list of all note groups*/
	/*If perooz id's are to be returned, set to true*/
	public function set_notegroup_list($con, $count = null, $offset = null){

		$returned_thing = false;

		$stmt_end = '';  

		/*If limit and offset are given for retrieval of bulk objects*/
		if (isset($count)){
			if (isset($offset)){
				$stmt_end = 'LIMIT '.$offset.','.$count; 
			}else{
				$stmt_end = 'LIMIT '.$count; 
			}
		}

		/*Set final sql statement*/
		/*Bind values necessary for sql statement*/
		if ($this->api){
			$final_stmt = $this->notegroup_stmt_pz.' '.$stmt_end;
			$values = array(':PeroozArticleId' => $this->perooz_article_id);
		}else{
			$final_stmt = $this->notegroup_stmt.' '.$stmt_end;
			$values = array(':ArticleId' => $this->article_id);
		}

		foreach ($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		/*Complete query and obtain results*/
		$results = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($results)){
			$returned_thing = true; 
			if ($this->api){
				foreach($results as $row){
					$this->notegroup_list[] = $row['PeroozNoteGroupId'];
				}
			}else{
				foreach($results as $row){
					$this->notegroup_list[] = $row['NoteGroupId'];
				}
			}
		}

		return $returned_thing;
	}

	/*Get list of all notes for the article*/
	public function set_note_list($con,$count = null, $offset = null){

		$returned_thing = false;

		$stmt_end = ''; 

		/*If limit and offset are given for retrieval of bulk objects*/
		if (isset($count)){
			if (isset($offset)){
				$stmt_end = 'LIMIT '.$offset.','.$count; 
			}else{
				$stmt_end = 'LIMIT '.$count; 
			}
		}

		/*Set final sql statement*/
		/*Bind values necessary for pdo sql query*/
		if ($this->api){
			$final_stmt = $this->note_list_stmt_pz.' '.$stmt_end;
			$values = array(':PeroozArticleId' => $this->perooz_article_id);
		}else{
			$final_stmt = $this->note_list_stmt.' '.$stmt_end;
			$values = array(':ArticleId' => $this->article_id);
		}

		foreach ($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		/*Complete query and obtain results*/
		$results = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($results)){
			$returned_thing = true;
			if ($this->api){
				foreach($results as $row){
					$this->note_list[] = $row['PeroozNoteId'];
				}
			}else{
				foreach($results as $row){
					$this->note_list[] = $row['NoteId'];
				}
			}
		}
		
		return $returned_thing;
	}

	/*Basic search function to look for article by reasonable value - either ArticleHyperlink or ArticleTitle */
	public function search_article($values,$con){
		
		$returned_thing = false;
		
		$stmt='';

		/*Ensure that only one array key-value pair exists*/
		if (count($values) != 1){
			return $returned_thing;
		}

		/*Check the array key and create final statement*/
		if (array_key_exists('url',$values)){
			$stmt = $this->search_stmt.'ArticleHyperlink=:ArticleHyperlink';
			$values = array(':ArticleHyperlink' => $values['url']);
			$value_options = array(':ArticleHyperlink' => $this->prop_parameters[':ArticleHyperlink']);
		}elseif(array_key_exists('title', $values)){
			$stmt= $this->search_stmt.'ArticleTitle=:ArticleTitle';
			$values = array(':ArticleTitle' => $values['title']);
			$value_options = array(':ArticleTitle' => $this->prop_parameters[':ArticleTitle']);
		}

		/*Make select request*/
		if ($stmt){
			$results = $con->multi_query($stmt,$values,$value_options);
			if (!empty($results)){

				if ($this->api){

					$auth_stmt = 'Select PeroozAuthorId From Authors Where AuthorId=:AuthorId';
					$auth_val = array(':AuthorId' => $results[0]['AuthorId']);
					$auth_prop = array(':AuthorId' => PDO::PARAM_INT);
					$auth_result = $con->multi_query($auth_stmt,$auth_val,$auth_prop);

					$source_stmt = 'Select PeroozSourceId From Sources Where SourceId=:SourceId';
					$source_val = array(':SourceId' => $results[0]['SourceId']);
					$source_prop = array(':SourceId' => PDO::PARAM_INT);
					$source_result = $con->multi_query($source_stmt,$source_val,$source_prop);

					if (!empty($auth_result) && !empty($source_result)){
						$returned_thing = array('perooz_article_id' => $results[0]['PeroozArticleId'],
										    'article_title' => $results[0]['ArticleTitle'],
										    'perooz_author_id' => $auth_result[0]['PeroozAuthorId'],
										    'perooz_source_id' => $source_result[0]['PeroozSourceId'],
										    'source_hyperlink' => $results[0]['SourceHyperlink'],
										    'article_hyperlink' => $results[0]['ArticleHyperlink'],
										    'approved' => $results['Approved']);

					}
				}else{
					$returned_thing = array('perooz_article_id' => $results[0]['PeroozArticleId'],
										    'article_title' => $results[0]['ArticleTitle'],
										    'author_id' => $results[0]['AuthorId'],
										    'source_id' => $results[0]['SourceId'],
										    'source_hyperlink' => $results[0]['SourceHyperlink'],
										    'article_hyperlink' => $results[0]['ArticleHyperlink'],
										    'approved' => $results['Approved']);
				}

			}
		}
		 
		return $returned_thing;
	}

}

?>