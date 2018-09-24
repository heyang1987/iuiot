<?php

	require_once($root . 'views/surveyPage.php');

	class Survey{
	
		private $session;
		public $id;
		private $pagenr;
		private $lastPage = 0;
		
		private $explanation;
		private $title;
		private $page;
		
		private $questionsLeft;
		private $questionsTotal;
		
		public static function isQuestion($var){
		//	return ($var['optiontype'] != "explanation");
		return 1;
		}

	
		public function __construct($session){
			$this->session = $session;
			$this->getProgress();
			$this->getHeader($this->id);
			$this->page = new Page($this->id, $this->session, $this->pagenr);
		}
		
		public function getProgress(){
			//where am I now?
			$condArray = explode(" ",$this->session->condition);
			$cond = implode("','",$condArray);
			$query = "
				SELECT 
					_questions.surveyId AS id, _surveylists.rank AS rank, page, _questions.optiontype AS optiontype
				FROM 
					_questions, _surveylists
				WHERE  
					_surveylists.surveyId = _questions.surveyId
				AND
					(
						_questions.required = 1
					)
								
				AND
					(_questions.cond IN ('$cond') or _questions.cond = '')
				AND
					_surveylists.experiment = '".$this->session->experiment."'
				AND
					(_surveylists.cond IN ('$cond') or _surveylists.cond = '')
				ORDER BY 
					_surveylists.rank ASC, page ASC
			";
			$result = Database::getInstance()->performQuery($query);
			$totalQuestions = array_filter($result,"Survey::isQuestion");
			$this->questionsTotal = sizeof($totalQuestions);
			
			$query = "
				SELECT 
					_questions.surveyId AS id, _surveylists.rank AS rank, page, _questions.optiontype AS optiontype, _questions.id AS qid
				FROM 
					_questions, _surveylists
				WHERE 
					id NOT IN(
						SELECT 
							questionId
						FROM
							answers
						WHERE 
							sessionId = '".$this->session->id."'
					)
				AND 
					_surveylists.surveyId = _questions.surveyId
				AND
					(
						_questions.required = 1
					OR
						_questions.required-10 IN(
							SELECT
								obj
							FROM
								events
							WHERE
								sessionId = '".$this->session->id."'
							AND
								action = 'outlier'
						)
					OR
						(
							_questions.required > 100
						AND
							_questions.required-100 NOT IN(
								SELECT
									obj
								FROM
									events
								WHERE
									sessionId = '".$this->session->id."'
								AND
									action = 'skip'
							)
						)
					)
								
				AND
					(_questions.cond IN ('$cond') or _questions.cond = '')
				AND 
					_surveylists.type = '".$this->session->state."'
				AND
					_surveylists.experiment = '".$this->session->experiment."'
				AND
					(_surveylists.cond IN ('$cond') or _surveylists.cond = '')
				ORDER BY 
					_surveylists.rank ASC, page ASC
			";
			$result = Database::getInstance()->performQuery($query);
			$realQuestions = array_filter($result,"Survey::isQuestion");
			$this->questionsLeft = sizeof($realQuestions);
			if($this->session->state == "presurvey"){
				$this->questionsLeft += 203;
			}
			if($result){
				$lowestRank = $result[0]['rank'];
				$lowestPage = $result[0]['page'];
				
				$lowestResult = array();
				foreach($result as $r){
					if($r['rank'] == $lowestRank && $r['page'] == $lowestPage){
						array_push($lowestResult,$r);
					}
				}
				
				$selectedResult = $lowestResult[rand(0,count($lowestResult)-1)];
				$this->id = $selectedResult['id'];
				
				$this->pagenr = $selectedResult['page'];
			//if there's no page left, we're done
			}else{
				$this->id = -1;
			}
		}
		
		public function getHTML(){
			$HTML = '';
			if($this->id > -1){
				
				$part = "";
				$counter = '<div id="counterOuter"><div id="counter" class="'.$this->questionsLeft.'-'.$this->questionsTotal.'"></div></div><div id="counterText">'.$part.'</div>';
				
				$HTML .= '<div id="surveyHeader">';
				$HTML .= '<div id="surveyTitle">' . $this->title . '</div>';
				
				
				
				$HTML .= '<div id="explanation">' . $this->explanation . '</div>';
				
				if($this->session->state == "midsurvey"){
					$HTML .= $counter;
				}
				
				$HTML .= '</div>';
				
				$HTML .= '<div id="questions"><div id="questionsInner"><form onClick="checkForm()" onkeyup="checkForm()">';
				
				global $mc;
				//$k = 'iotB_'.$this->id.':'.$this->session->condition.':'.$this->page->pagenr;
				//$h = xcache_get($k); //<- legacy
				//$h = apc_fetch($k); //<- online
				//if(!$h){
					$h = $this->page->getHTML();
					//xcache_set($k, $h); //<- legacy
					//apc_store($k, $h); //<- online
				//}
				$HTML .= $h;
				$HTML = str_replace("IDVAL",$this->session->userId,$HTML);
				
				$HTML .= '<div id="bottomRow">';
				if($this->session->state != "midsurvey"){
					$HTML .= $counter;
				}
				$HTML .= '<button type="button" id="nextButton" onClick="submitEnter()" class="disabled" />'; //disabled="true"
				$HTML .= '<label for="nextButton"><strong>Continue</strong></label>';
				$HTML .= '</div>';
				$HTML .= '</form>';
				
				$HTML .= '</div></div>';
				
				if($this->session->state == "midsurvey"){
					$HTML .= '</div></div>';
				}
				
				$HTML .= '<div class="clear" />';
				
			}else{
				$HTML .= $this->session->state.' done';
			}
			return $HTML;
		}
		
		private function getHeader($id){
			$query = "
				SELECT SQL_CACHE
					title, explanation
				FROM
					_surveys
				WHERE
					id = '$id'
			";
			$result = Database::getInstance()->performQuery($query);
			if($result){
				$this->title = $result[0]['title'];
				$this->explanation = $result[0]['explanation'];
			}
		}
		
		private function getException($num){
			$query = "
				SELECT SQL_CACHE
					obj
				FROM
					events
				WHERE
					sessionId = '".$this->session->id."'
				AND
					action='exception'
			";
			$result = Database::getInstance()->performQuery($query);
			if($result){
				foreach($result as $r){
					if($r['obj'] == $num){
						return count($result)+5;
					}
				}
			}
			return $num;
		}
		
		private function getLabel($cond,$exp){
			if($exp==1){
				return $this->soclabels[$cond-1];
			}else if($exp==2){
				return $this->weblabels[$cond-1];
			}
		}
		
		private function parseName($text){	
			$textArray = explode("_",$text);
			
			//find max person
			if($textArray[0] == "max"){
				$textArray[1] = $this->getOutlier($textArray[1]);
			}
			if($textArray[0] == "label"){
				return $this->getLabel($textArray[1],$this->session->experiment);
			}
			
			$names = implode("','",$textArray);
			
			$ses = $this->session->id;
			if($textArray[0] == "person" || $textArray[0] == "decision" || $textArray[0] == "max"){
				$query = "
					SELECT SQL_CACHE
						answer
					FROM
						answers
					WHERE
						questionId IN('$names')
					AND
						sessionId = '$ses'
				";
				$result = Database::getInstance()->performQuery($query);
				$nameArray = array();
				foreach($result as $r){
					if($r['answer'] != ""){
						array_push($nameArray, $r['answer']);
					}
				}
				return implode(", ",$nameArray);
			}else{
				return $text;
			}
		}
		
		private function getOutlier($g){
			$answers = array();
			$query = "
				SELECT details
				FROM events
				WHERE sessionId = '".$this->session->id."'
				AND action = 'outlier'
				AND obj = '".$g."'
			";
			$result = Database::getInstance()->performQuery($query);
			if($result){
				return $result[0]['details'];
			}
			return 0;
		}
	}

?>