<?php

/**
 * Author: Sneha Inguva
 * Date: 9/11/2014
 * [resource_api description]
 * @param  [associative array]  $request   [breakdown of request uri]
 * @param  [string]  $call_type [create,update,or retrieve]
 * @param  [db connection of mysqldb]  $con [description]
 * @param  boolean $vals [description]
 * @return [boolean false or array of result] [description]
 */
function resource_api($request,$call_type,$con,$vals = false){
	
	$result = false; 

	/*Global response and response code variables*/
	global $response_code,$response;

	/*Valid post parameters*/
	$valid_var  = array('articles' => array('perooz_article_id','article_title','perooz_author_id','perooz_source_id','source_hyperlink','article_hyperlink','approved'),
							 'notes' => array('perooz_note_id','perooz_article_id','perooz_contributor_id','note_type_id','inline_text','note_text','perooz_notegroup_id','sort_order','approved'),
							 'notegroups' => array('perooz_notegroup_id','perooz_article_id','note_text_overlap'),
							 'sources' => array('perooz_source_id','source_name','source_site','source_type_id'),
							 'contributors' => array('perooz_contributor_id','perooz_user_id','bio','photo','profession','country','stance','bio_hyperlink','first_name','last_name'),
							 'authors' => array('perooz_author_id','first_name','last_name') ); 

	 /*Renaming of post variables to parameter variables*/
	 $post_to_db = array('articles'  => array('perooz_article_id' => 'PeroozArticleId',
	 	                     				  'article_title' => 'ArticleTitle', 
	 	                     				  'perooz_author_id' => 'PeroozAuthorId',
											  'perooz_source_id' => 'PeroozSourceId',
	 					 					  'source_hyperlink' => 'SourceHyperlink',
	 					 					  'article_hyperlink' => 'ArticleHyperlink',
	 					 					  'approved' => 'Approved'),
	 					 'notes' => array('perooz_note_id' => 'PeroozNoteId',
	 					 				  'perooz_article_id' => 'PeroozArticleId',
	 					 				  'perooz_contributor_id' => 'PeroozContributorId',
	 					 				  'note_type_id' => 'NoteTypeId',
	 					 				  'inline_text' => 'InlineText',
	 					 				  'note_text' => 'NoteText',
	 					 				  'perooz_notegroup_id' => 'PeroozNoteGroupId',
	 					 				  'sort_order' => 'SortOrder',
	 					 				  'approved' => 'Approved'),
	 					 'notegroups' => array('perooz_notegroup_id' => 'PeroozNoteGroupId',
	 					 					   'perooz_article_id' => 'PeroozArticleId',
	 					 					   'note_text_overlap' => 'NoteTextOverlap'),
	 					 'sources' => array('perooz_source_id' => 'PeroozSourceId',
	 					 					'source_name' => 'SourceName',
	 					 					'source_site' => 'SourceSite',
	 					 					'source_type_id' => 'SourceTypeId'),
	 					 'contributors' => array('perooz_contributor_id' => 'PeroozContributorId',
	 					 						 'perooz_user_id' => 'PeroozUserId',
	 					 						 'bio' => 'Bio',
	 					 						 'photo' => 'Photo',
	 					 						 'profession' => 'Profession',
	 					 						 'country' => 'Country',
	 					 						 'stance' => 'PoliticalStance',
	 					 						 'bio_hyperlink' => 'BioHyperlink',
	 					 						 'first_name' => 'FirstName',
	 					 						 'last_name' => 'LastName'),
	 					 'authors' => array('perooz_author_id' => 'PeroozAuthorId',
	 					 					'first_name' => 'AuthorFirstName',
	 					 					'last_name' => 'AuthorLastName'));

	 $subresource_to_fxn = array('note_lists' => 'set_note_list','notegroup_lists' => 'set_notegroup_list');

	/*If post parameters present, ensure they are valid*/
	if ($vals){
		$resource = $request['resource'];
		$valid_param = $valid_var[$resource];
		foreach($vals as $key => $val){
			if (!in_array($key,$valid_param)){
				$response_code = 400;
				$response['message'] = 'Invalid resource parameters.';
				return $result; 
			}
		}
	}

	/*Initiliaze the object class*/
	$resource_class = substr($request['resource'],0,-1);
	$obj = new $resource_class(array('api' => true));

	$resource_id_name = 'perooz_'.$resource_class.'_id';

	/*If new resource is created*/
	if ($call_type == 'create'){

		/*Set object values using setter*/
		foreach($vals as $key=> $val){
			$obj->$key = $val;
		}

		/*Insert object into the db*/
		$result = $obj->insert_db($con);
	}

	/*If new resource is updated*/
	if ($call_type == 'update'){

		/*Set relevant values to be updated*/
		$values = array();
		$index_translator = $post_to_db[$request['resource']];

		foreach ($vals as $key => $val) {
			$ex = ':'.$index_translator[$key];
			$values[$ex] = $val;
		}
		$values[':'.$index_translator[$resource_id_name]] = $request['resource_id'];

		/*Update to system and return value*/
		$result = $obj->update_to_db($values,$con);
	}

	/*If new resource is retrieve*/
	if ($call_type == 'retrieve'){

		/*Set the object id value*/
		$obj->$resource_id_name = $request['resource_id'];
		
		/*Ensure that the object has been properly pulled from db*/
		$set_result = $obj->set_from_db($con);
		if (!$set_result){
			return $result; 
		}

		if (array_key_exists('subresource',$request)){

			/*Grab subresource function name*/
			$subresource = $request['subresource'];
			$subresource_name = substr($request['subresource'],0,-1);
			$function = $subresource_to_fxn[$subresource];

			$fxn_result = false; 

			/*Check for relevant query parameters*/
			if (array_key_exists('query_params',$request)){ //if query parameters
				$query_params = $request['query_params'];
				if (array_key_exists('max',$query_params)){
					if (array_key_exists('start',$query_params)){
						$fxn_result = $obj->$function($con,true,$query_params['max'],$query_params['start']);
					}else{
						$fxn_result = $obj->$function($con,true,$query_params['max']);
					}
				}
			}else{ //if no query parameters
				$fxn_result = $obj->$function($con);
			}

			/*Call function to set sub-resource and ensure properly pulled from db*/
			if (!$fxn_result){
				$response_code = 400;
				$response['message'] = 'Subresource request error.';
				return $result;
			}

			$result = $obj->$subresource_name; 

		}else{

			/*Iterate through values and return them*/
			$vals = array();
			$resource_vals = $valid_var[$request['resource']];
			foreach($resource_vals as $resource_val){
				$vals[$resource_val] = $obj->$resource_val; 
			}
			$result = $vals;
		}

	}
	
	return $result; 

}

?>