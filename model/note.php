<?php

/*PHP Note Class for note [either annonated or inline]*/
/*Date:6-1-2014*/
/*Author:Sneha Inguva*/

class note{

	/*Check if model is used in api*/
	protected $api = false;

	/*General class properties*/
	protected $note_id;
	protected $perooz_note_id;
	protected $article_id; 
	protected $perooz_article_id;
	protected $contributor_id; 
	protected $perooz_contributor_id;
	protected $note_type_id; //Value 1 - general, 2 - text-specific, aka inline
	protected $inline_text = null; //May be null
	protected $note_text; 
	protected $notegroup_id = null;
	protected $perooz_notegroup_id;
	protected $sort_order;
	protected $approved;
	protected $date; 

	/*General statements*/
	private $select_stmt = 'Select NoteId,PeroozNoteId,ArticleId,ContributorId,NoteTypeId,InlineText,NoteText,NoteGroupId,SortOrder,Approved From Notes Where ';
	private $insert_stmt = 'Insert Into Notes (PeroozNoteId,ArticleId,ContributorId,NoteTypeId,InlineText,NoteText,NoteGroupId,SortOrder,Approved) Values (:PeroozNoteId,:ArticleId,:ContributorId,:NoteTypeId,:InlineText,:NoteText,:NoteGroupId,:SortOrder,:Approved)';
	private $update_stmt = 'Update Notes Set';

	/*General parameter requirements for class properties-stored in associate_array*/
	private $prop_parameters = array(':PeroozNoteId' => PDO::PARAM_STR,
									 ':ArticleId' => PDO::PARAM_INT, 
									 ':ContributorId' => PDO::PARAM_INT, 
									 ':NoteTypeId' => PDO:: PARAM_INT, 
									 ':InlineText' => PDO::PARAM_STR, 
									 ':NoteText' => PDO::PARAM_STR, 
									 ':NoteGroupId' => PDO::PARAM_INT, 
									 ':SortOrder' => PDO::PARAM_INT,
									 ':NoteId' => PDO::PARAM_INT);


/*Constructor with a variable number of arguments*/
/*Either no parameter or passes in an associative array with the parameters*/
function __construct(){
	if (func_num_args() > 0){
		$args = func_get_args(0);
		foreach($args[0] as $key => $value){
			$this->$key = $value; 
		}
		if (isset($this->con)){
			if (isset($this->note_id) || isset($this->perooz_note_id)){
				$properly_set = $this->set_from_db($this->con);
				if (!$properly_set){
					echo 'This has not worked.';
				}
			}
		}
	}
	$this->date = time();
}

/*Destructor for note object*/
function __destruct(){
	
}

/*Get function for note object*/
public function __get($name){
	return $this->$name;
}

/*Set function for note object*/
public function __set($name, $value){
	$this->$name = $value;
}

/*Returns the last insert id if works, or returns boolean false*/
/*Inserts the value into the database*/
public function insert_db($con){

	$result = false; 

	/*Generate uuid*/
	$uid = $con->query('Select uuid()')->fetch();
	$this->perooz_note_id = 'pz_'.$uid['uuid()'];

	if ($this->api){

		/*Grab perooz article id*/
		$article_stmt = 'Select ArticleId From Articles Where PeroozArticleId=:PeroozArticleId';
		$article_val = array(':PeroozArticleId' => $this->perooz_article_id);
		$article_prop = array(':PeroozArticleId' => PDO::PARAM_STR);
		$article_result = $con->multi_query($article_stmt,$article_val,$article_prop);

		/*Grab contributor id*/
		$contributor_stmt = 'Select ContributorId From Contributors Where PeroozContributorId=:PeroozContributorId';
		$contributor_val = array(':PeroozContributorId' => $this->perooz_contributor_id);
		$contributor_prop = array(':PeroozContributorId' => PDO::PARAM_STR);
		$contributor_result = $con->multi_query($contributor_stmt,$contributor_val,$contributor_prop);

		/*Grab notegroupid*/
		$notegroup_stmt = 'Select NoteGroupId From NoteGroups Where PeroozNoteGroupId=:PeroozNoteGroupId';
		$notegroup_val = array(':PeroozNoteGroupId' => $this->perooz_notegroup_id);
		$notegroup_prop = array(':PeroozNoteGroupId' => PDO::PARAM_STR);
		$notegroup_result = $con->multi_query($notegroup_stmt, $notegroup_val,$notegroup_prop);	
		
		if (!empty($article_result) && !empty($contributor_result) && !empty($notegroup_result)){
			$values = array(':PeroozNoteId' => $this->perooz_note_id,
							':ArticleId' => $article_result[0]['ArticleId'], 
				            ':ContributorId' => $contributor_result[0]['ContributorId'], 
				            ':NoteTypeId' => $this->note_type_id, 
				            ':InlineText' => $this->inline_text,
				            ':NoteText' => $this->note_text, 
				            ':NoteGroupId' => $notegroup_result[0]['NoteGroupId'],
				            ':Approved' => $this->approved,
				            ':SortOrder' => $this->sort_order);

			foreach($values as $key => $value){
				$value_options[$key] = $this->prop_parameters[$key];
			}

			$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'Note');
		
		}

		return $result;

	}else{
		$values = array(':PeroozNoteId' => $this->perooz_note_id,
						':ArticleId' => $this->article_id, 
			            ':ContributorId' => $this->contributor_id, 
			            ':NoteTypeId' => $this->note_type_id, 
			            ':InlineText' => $this->inline_text,
			            ':NoteText' => $this->note_text, 
			            ':NoteGroupId' => $this->notegroup_id,
			            ':Approved' => $this->approved,
			            ':SortOrder' => $this->sort_order);

		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}
		
		$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'Note');

		return $result;
	}

}

/*Grabs values by pulling from the database*/
/*Returns an array of results mapped to object values and value true if successful*/
public function set_from_db($con){

	$returned_thing = false;
	
	/*Values and stmt set depending on whether pk or uuid provided*/
	if (isset($this->note_id)){
		$values = array(':NoteId' => $this->note_id);
		$final_stmt = $this->select_stmt.'NoteId=:NoteId';
	}elseif(isset($this->perooz_note_id)){
		$values = array(':PeroozNoteId' => $this->perooz_note_id);
		$final_stmt = $this->select_stmt.'PeroozNoteId=:PeroozNoteId';
	}
	
	/*object set from db*/
	foreach($values as $key => $value){
		$value_options[$key] = $this->prop_parameters[$key]; 
	}

	$result = $con->multi_query($final_stmt,$values,$value_options);
	
	if (!empty($result)){

		if ($this->api){

			/*Grab perooz article id*/
			$article_stmt = 'Select PeroozArticleId From Articles Where ArticleId=:ArticleId';
			$article_val = array(':ArticleId' => $result[0]['ArticleId']);
			$article_prop = array(':ArticleId' => PDO::PARAM_INT);
			$article_result = $con->multi_query($article_stmt,$article_val,$article_prop);

			/*Grab contributor id*/
			$contributor_stmt = 'Select PeroozContributorId From Contributors Where ContributorId=:ContributorId';
			$contributor_val = array(':ContributorId' => $result[0]['ContributorId']);
			$contributor_prop = array(':ContributorId' => PDO::PARAM_INT);
			$contributor_result = $con->multi_query($contributor_stmt,$contributor_val,$contributor_prop);

			/*Grab notegroupid*/
			$notegroup_stmt = 'Select PeroozNoteGroupId From NoteGroups Where NoteGroupId=:NoteGroupId';
			$notegroup_val = array(':NoteGroupId' => $result[0]['NoteGroupId']);
			$notegroup_prop = array(':NoteGroupId' => PDO::PARAM_INT);
			$notegroup_result = $con->multi_query($notegroup_stmt, $notegroup_val,$notegroup_prop);	
			
			if (!empty($article_result) && !empty($contributor_result) && !empty($notegroup_result)){
				$returned_thing = true;
				$this->note_id = $result[0]['NoteId'];
				$this->perooz_note_id = $result[0]['PeroozNoteId'];
				$this->perooz_article_id = $article_result[0]['PeroozArticleId'];
				$this->perooz_contributor_id = $contributor_result[0]['PeroozContributorId'];
				$this->note_type_id = $result[0]['NoteTypeId'];
				$this->inline_text = $result[0]['InlineText'];
				$this->note_text = $result[0]['NoteText'];
				$this->perooz_notegroup_id = $notegroup_result[0]['PeroozNoteGroupId'];
				$this->sort_order = $result[0]['SortOrder'];
				$this->approved = $result[0]['Approved'];
			}

		}else{
			$returned_thing = true; 

			$this->note_id = $result[0]['NoteId'];
			$this->perooz_note_id = $result[0]['PeroozNoteId'];
			$this->article_id = $result[0]['ArticleId'];
			$this->contributor_id = $contributor_result[0]['ContributorId'];
			$this->note_type_id = $result[0]['NoteTypeId'];
			$this->inline_text = $result[0]['InlineText'];
			$this->note_text = $result[0]['NoteText'];
			$this->notegroup_id = $notegroup_result[0]['NoteGroupId'];
			$this->sort_order = $result[0]['SortOrder'];
			$this->approved = $result[0]['Approved'];
		}

	}

	return $returned_thing;
}

/*Updates vvalues by pushing to db*/
/*Gives value true if successful, false if not*/
public function update_to_db($values,$con){

	$result = false; 
	$stmt_set = '';
	
	if (array_key_exists(':NoteId',$values)){
		$stmt_where = 'NoteId=:NoteId';
	}elseif(array_key_exists(':PeroozNoteId',$values)){
		$stmt_where = 'PeroozNoteId=:PeroozNoteId';
	}

	$count_value = 1;
	foreach ($values as $key => $value){
		if ($key == ':PeroozArticleId'){

			/*Grab perooz article id*/
			$article_stmt = 'Select ArticleId From Articles Where PeroozArticleId=:PeroozArticleId';
			$article_val = array(':PeroozArticleId' => $value);
			$article_prop = array(':PeroozArticleId' => PDO::PARAM_STR);
			$article_result = $con->multi_query($article_stmt,$article_val,$article_prop);

			if (!empty($article_result)){
				$key_final = ':ArticleId';
				$values_final[$key_final] = $article_result[0]['ArticleId'];
				$value_options[$key_final] = $this->prop_parameters[$key_final];
			}else{
				return $result;
			}
			
			
		}else if($key == ':PeroozContributorId'){
			
			/*Grab contributor id*/
			$contributor_stmt = 'Select ContributorId From Contributors Where PeroozContributorId=:PeroozContributorId';
			$contributor_val = array(':PeroozContributorId' => $value);
			$contributor_prop = array(':PeroozContributorId' => PDO::PARAM_STR);
			$contributor_result = $con->multi_query($contributor_stmt,$contributor_val,$contributor_prop);

			if (!empty($contributor_result)){
				$key_final = ':ContributorId';
				$values_final[$key_final] = $contributor_result[0]['ContributorId'];
				$value_options[$key_final] = $this->prop_parameters[$key_final];
			}else{
				return $result;
			}


		}else if($key == ':PeroozNoteGroupId'){

			/*Grab notegroupid*/
			$notegroup_stmt = 'Select NoteGroupId From NoteGroups Where PeroozNoteGroupId=:PeroozNoteGroupId';
			$notegroup_val = array(':PeroozNoteGroupId' => $value);
			$notegroup_prop = array(':PeroozNoteGroupId' => PDO::PARAM_STR);
			$notegroup_result = $con->multi_query($notegroup_stmt, $notegroup_val,$notegroup_prop);	

			if (!empty($notegroup_result)){
				$key_final = ':PeroozNoteGroupId';
				$values_final[$key_final] = $notegroup_result[0]['NoteGroupId'];
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