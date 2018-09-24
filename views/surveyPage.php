<?php
	
	require_once($root . '/views/surveyQuestion.php');
	
	class Page{
	
		private $questions;
		
		public function __construct($surveynr, $session, $pagenr){
			$this->pagenr = $pagenr;
			$this->questions = $this->getQuestions($surveynr, $session, $pagenr);
		}
		
		public function getHTML(){
			$HTML = '';
			foreach($this->questions as $question){
				$HTML .= $question->getHTML();
			}
			
			
			return $HTML;
		}
		
		private function getQuestions($surveynr, $session, $pagenr){
			$condArray = explode(" ",$session->condition);
			$cond = implode("','",$condArray);
			$query = "
				SELECT SQL_CACHE
					id, text, optiontype, optionsName, required, formatting
				FROM
					_questions
				WHERE
					page = '$pagenr'
				AND
					surveyId = '$surveynr'
				AND
					(cond IN ('$cond') or cond = '') 
				ORDER BY
					rank ASC, rand() ASC
			";
			$result = Database::getInstance()->performQuery($query);
			$questions = array();
			foreach($result as $q){
				if($q['optiontype'] == "group"){
					//create main question
					if(sizeOf($persons) > 0){
						$names = "person_";
						foreach($persons as $p){
							$pid = $p['questionId']-100;
							$plabel = $pid;
							$names .= $plabel . "_";
						}
						
						$text = $q['text']."<br /><div class='groupcontents'>".$names."</div><br/>";
						
						$condArray = explode(" ",$session->condition);
						
						$question = new Question($q['id'], $text, "checkbox", "items", $q['required'], $q['formatting'], $surveynr, $session);
						array_push($questions,$question);
					}
					//create subquestions
					//todo
				}else{
					$question = new Question($q['id'], $q['text'], $q['optiontype'], $q['optionsName'], $q['required'], $q['formatting'], $surveynr, $session);
					array_push($questions,$question);
				}
				
			}
			return $questions;
		}
	}
?>