<?php

/*PHP Notegroup class for article in db*/
/*Date: 6-4-2014*/
/*Author:Sneha Inguva*/

class notegroup{

	/*See if model is being used in api*/
	protected $api = false; 

	/*Variables either set by constructor or using get/set functions*/
	protected $notegroup_id;
	protected $perooz_notegroup_id;
	protected $perooz_article_id;
	protected $article_id; 
	protected $note_text_overlap; 
	protected $date; 

	/*List of ids*/
	protected $note_list = array();

	/*Relevant sql statements for functions*/
	private $select_stmt = 'Select NoteGroupId,PeroozNoteGroupId,ArticleId,NoteTextOverlap From NoteGroups Where ';
	
	private $note_list_stmt = 'Select NoteId From Notes Where NoteGroupId=:NoteGroupId And Approved=1';
	private $note_list_stmt_pz = 'Select PeroozNoteId From Notes Where NoteGroupId=(Select NoteGroupId From NoteGroups Where PeroozNoteGroupId=:PeroozNoteGroupId) And Approved=1';

	private $insert_stmt = 'Insert Into NoteGroups (PeroozNoteGroupId,ArticleId,NoteTextOverlap) Values (:PeroozNoteGroupId,:ArticleId,:NoteTextOverlap)';
	private $update_stmt = 'Update NoteGroups Set';

	/*PDO binding parameters*/
	private $prop_parameters = array(':NoteGroupId' => PDO::PARAM_INT,
									 ':PeroozNoteGroupId' => PDO::PARAM_STR,
									 ':ArticleId' => PDO::PARAM_INT,
									 ':NoteTextOverlap' => PDO::PARAM_STR);

	/*constructor for notegroup*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){
				$this->$key = $value; 
			}
			if (isset($this->con)){
				if (isset($this->notegroup_id) || isset($this->perooz_notegroup_id)){
					$properly_set = $this->set_from_db($this->con);
					$note_list_set = $this->get_note_list($this->con);
					if (!$properly_set || !$note_list_set){
						echo 'This has not worked.';
					}
				}
			}
		}
		$this->date = time(); 
	}

	/*Set function*/
	public function __set($name,$value){
		return $this->$name = $value;
	}

	/*Get function*/
	public function __get($name){
		return $this->$name;
	}

	/*Set general thing from db*/
	/*Main argument is database connection*/
	public function set_from_db($con){

		$returned_thing = false;

		/*Set values and stmt based on pk or uuid*/
		if (isset($this->notegroup_id)){
			$values = array(':NoteGroupId' => $this->notegroup_id);
			$final_stmt = $this->select_stmt.'NoteGroupId=:NoteGroupId';
		}elseif(isset($this->perooz_notegroup_id)){
			$values = array(':PeroozNoteGroupId' => $this->perooz_notegroup_id);
			$final_stmt = $this->select_stmt.'PeroozNoteGroupId=:PeroozNoteGroupId';
		}

		/*Set object from db*/
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($result)){

			if ($this->api){

				/*Grab article id*/
				$article_stmt = 'Select PeroozArticleId From Articles Where ArticleId=:ArticleId';
				$article_val = array(':ArticleId' => $result[0]['ArticleId']);
				$article_prop = array(':ArticleId' => PDO::PARAM_INT);
				$article_result = $con->multi_query($article_stmt,$article_val,$article_prop);

				if (!empty($article_result)){

					$returned_thing = true;

					$this->notegroup_id = $result[0]['NoteGroupId'];
					$this->perooz_notegroup_id = $result[0]['PeroozNoteGroupId'];
					$this->perooz_article_id = $article_result[0]['PeroozArticleId']; 
					$this->note_text_overlap = $result[0]['NoteTextOverlap'];
				}

			}else{
				$returned_thing = true;

				$this->notegroup_id = $result[0]['NoteGroupId'];
				$this->perooz_notegroup_id = $result[0]['PeroozNoteGroupId'];
				$this->article_id = $result[0]['ArticleId']; 
				$this->note_text_overlap = $result[0]['NoteTextOverlap'];
			}
		}

		return $returned_thing;
	}

	/*Get note list*/
	/*argument passed in is the database connection*/
	public function set_note_list($con,$count = null, $offset = null){

		$returned_thing = false; 

		$stmt_end='';

		/*If limit and offset are given for retrieval of bulk objects*/
		if (isset($count)){
			if (isset($offset)){
				$stmt_end = 'LIMIT '.$offset.','.$count; 
			}else{
				$stmt_end = 'LIMIT '.$count; 
			}
		}

		/*Set final sql statement*/
		if ($this->api){
			$final_stmt = $this->note_list_stmt_pz.' '.$stmt_end;
			$values = array(':PeroozNoteGroupId' => $this->perooz_notegroup_id);
		}else{
			$final_stmt = $this->note_list_stmt.' '.$stmt_end;
			$values = array(':NoteGroupId' => $this->notegroup_id);
		}

		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

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

	/*Insert notegroup into database*/
	/*Passes in argument db connection*/
	public function insert_db($con){

		$result = false; 

		/*Set the notegroup uuid*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_notegroup_id = 'pz_'.$uid['uuid()'];

		if ($this->api){

			$article_stmt = 'Select ArticleId From Articles Where PeroozArticleId=:PeroozArticleId';
			$article_val = array(':PeroozArticleId' => $this->perooz_article_id);
			$article_prop = array(':PeroozArticleId' => PDO::PARAM_STR);
			$article_result = $con->multi_query($article_stmt,$article_val,$article_prop);

			if (!empty($article_result)){

				/*Insert into the database*/
				$values = array(':PeroozNoteGroupId' => $this->perooz_notegroup_id,
								':ArticleId' => $article_result[0]['ArticleId'],
								':NoteTextOverlap' => $this->note_text_overlap);

				foreach($values as $key => $value){
					$value_options[$key] = $this->prop_parameters[$key]; 
				}

				$result = $con->insert($this->insert_stmt, $values,$value_options,$this->api,'NoteGroup');
			}

			return $result;	

		}else{

			/*Insert into the database*/
			$values = array(':PeroozNoteGroupId' => $this->perooz_notegroup_id,
							':ArticleId' => $this->article_id,
							':NoteTextOverlap' => $this->note_text_overlap);

			foreach($values as $key => $value){
				$value_options[$key] = $this->prop_parameters[$key]; 
			}

			$result = $con->insert($this->insert_stmt, $values,$value_options,$this->api,'NoteGroup');

			return $result;

		}
		
	}

	/*Update notegroup object in the database*/
	/*Values passed in as associative array*/
	/*Passes in argument db connection*/
	public function update_to_db($values,$con){

		$result = false; 
		
		$stmt_set = '';

		if (array_key_exists(':NoteGroupId',$values)){
			$stmt_where = 'NoteGroupId=:NoteGroupId';
		}elseif(array_key_exists(':PeroozNoteGroupId', $values)){
			$stmt_where = 'PeroozNoteGroupId=:PeroozNoteGroupId';
		}

		$count_value = 1; 
		foreach ($values as $key => $value){

			if ($key == ':PeroozArticleId'){

				$article_stmt = 'Select ArticleId From Articles Where PeroozArticleId=:PeroozArticleId';
				$article_val = array(':PeroozArticleId' => $value);
				$article_prop = array(':PeroozArticleId' => PDO::PARAM_STR);
				$article_result = $con->multi_query($article_stmt,$article_val,$article_prop);
				$key_final = ':ArticleId'; 

				if (!empty($article_result)){
					$values_final[$key_final] = $article_result[0]['ArticleId'];
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

}

?>