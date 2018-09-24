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
		private $scenario = false;
		
		
		private $tooltips = [
			"smart home security system" => "<span class='term' onmouseover='hover(\"over\",\"[p]-security\")' onmouseout='hover(\"out\",\"[p]-security\")'>smart home security system<span class='tip'>Allows you to check the security of your home. It automatically locks and opens your doors. You can talk to people at the door and let them in remotely. It can identify a walk-in guest and notifies you accordingly.</span></span>",
			"smart assistant" => "<span class='term' onmouseover='hover(\"over\",\"[p]-assistant\")' onmouseout='hover(\"out\",\"[p]-assistant\")'>smart assistant<span class='tip'>Provides a gateway to online resources. It can play music, order products, and answer questions by searching the Internet. It can control and learn the status of your other smart home devices. It can be used as an intercom, and make hands-free phone calls.</span></span>", 
			"smart TV" => "<span class='term' onmouseover='hover(\"over\",\"[p]-tv\")' onmouseout='hover(\"out\",\"[p]-tv\")'>smart TV<span class='tip'>Automatically records shows. It can suggest shows based on who is watching. It provides updates about email, weather, upcoming events and the status of other smart devices. It can automatically play messages when the intended recipient enters the house.</span></span>",
			"smart washing machine" => "<span class='term' onmouseover='hover(\"over\",\"[p]-washer\")' onmouseout='hover(\"out\",\"[p]-washer\")'>smart washing machine<span class='tip'>Adjusts the washing cycle based on the load. Automatically runs when electricity rates are lowest. It tumbles clothes in fresh air after the cycle is over until you are ready to unload. It can remind family members of their laundry chores.</span></span>",
			"smart refrigerator" => "<span class='term' onmouseover='hover(\"over\",\"[p]-fridge\")' onmouseout='hover(\"out\",\"[p]-fridge\")'>smart refrigerator<span class='tip'>Senses when a product spoils or needs to be replenished, and reminds you when it is time to buy fresh groceries. It also tracks your family‘s diet, like how much candy your kids are eating.</span></span>",
			"smart HVAC system" => "<span class='term' onmouseover='hover(\"over\",\"[p]-hvac\")' onmouseout='hover(\"out\",\"[p]-hvac\")'>smart HVAC system<span class='tip'>Programs itself based on season, body temperature, and daily activities (e.g. at home, away, asleep). It adjusts the temperature in each room based on the preferences of its occupants. It keeps track of energy savings.</span></span>",
			"smart alarm clock" => "<span class='term' onmouseover='hover(\"over\",\"[p]-alarm\")' onmouseout='hover(\"out\",\"[p]-alarm\")'>smart alarm clock<span class='tip'>Gives feedback on sleeping patterns. It knows when you have to go to work and informs you of current traffic. It gently wakes you by adjusting the lights and turning on your favorite music. In case of bad weather, it can automatically call an Uber.</span></span>",
			"smart lighting system" => "<span class='term' onmouseover='hover(\"over\",\"[p]-light\")' onmouseout='hover(\"out\",\"[p]-light\")'>smart lighting system<span class='tip'>Turn on automatically when you enter a room, and adjust to your preferences and the incoming sunlight. They can adjust when you want to wake up, read, concentrate, or relax. They synchronize with your smart TV.</span></span>",
			
			"a location sensor" => "<span class='term' onmouseover='hover(\"over\",\"[p]-locsensor\")' onmouseout='hover(\"out\",\"[p]-locsensor\")'>a location sensor<span class='tip'>A smart device with a location sensor can detect your exact location in the house.</span></span>",
			"a camera" => "<span class='term' onmouseover='hover(\"over\",\"[p]-camera\")' onmouseout='hover(\"out\",\"[p]-camera\")'>a camera<span class='tip'>A smart device with a camera can detect your presence and identify you. The camera can also be used to interact with the device via gestures.</span></span>",
			"a microphone" => "<span class='term' onmouseover='hover(\"over\",\"[p]-mic\")' onmouseout='hover(\"out\",\"[p]-mic\")'>a microphone<span class='tip'>A smart device with a microphone can detect your presence and identify you. The microphone can also be used to interact with the device via speech commands.</span></span>",
			"your smart phone/watch" => "<span class='term' onmouseover='hover(\"over\",\"[p]-phone\")' onmouseout='hover(\"out\",\"[p]-phone\")'>your smart phone/watch<span class='tip'>A smart device can connect to your smart phone or watch to detect your exact location in- and outside the house. Your phone or watch can be used to control the smart device, and may receive notifications with updates about the device.</span></span>",
			
			"at home" => "<span class='term' onmouseover='hover(\"over\",\"[p]-home\")' onmouseout='hover(\"out\",\"[p]-home\")'>at home<span class='tip tipsmall'>This device may operate differently depending on whether you are at home or not.</span></span>",
			"your location" => "<span class='term' onmouseover='hover(\"over\",\"[p]-location\")' onmouseout='hover(\"out\",\"[p]-location\")'>your location<span class='tip tipsmall'>This device may operate differently depending on where you are inside your house.</span></span>",
			"automate its operations" => "<span class='term' onmouseover='hover(\"over\",\"[p]-automate\")' onmouseout='hover(\"out\",\"[p]-automate\")'>automate its operations<span class='tip tipsmall'>This device may automate its operations, based on collected data.</span></span>",
			"timely alerts" => "<span class='term' onmouseover='hover(\"over\",\"[p]-alert\")' onmouseout='hover(\"out\",\"[p]-alert\")'>timely alerts<span class='tip tipsmall'>This device may give you alerts about its operations.</span></span>",
			
			"locally" => "<span class='term' onmouseover='hover(\"over\",\"[p]-local\")' onmouseout='hover(\"out\",\"[p]-local\")'>locally<span class='tip tipsmall'>The data used in this scenario is stored locally on the smart device.</span></span>",
			"remote server" => "<span class='term' onmouseover='hover(\"over\",\"[p]-remote\")' onmouseout='hover(\"out\",\"[p]-remote\")'>remote server<span class='tip tipsmall'>The data used in this scenario is stored on a remote server.</span></span>",
			"optimize the service" => "<span class='term' onmouseover='hover(\"over\",\"[p]-optimize\")' onmouseout='hover(\"out\",\"[p]-optimize\")'>optimize the service<span class='tip tipsmall'>Over time, this device will use the collected data to optimize its operations (for example, to learn when to turn on and off automatically).</span></span>",
			"give you insight" => "<span class='term' onmouseover='hover(\"over\",\"[p]-insight\")' onmouseout='hover(\"out\",\"[p]-insight\")'>give you insight<span class='tip tipsmall'>Over time, this device will use the collected data to give you insight into your behavior (for example, how often and at what times you use it).</span></span>",
			"recommend you other services" => "<span class='term' onmouseover='hover(\"over\",\"[p]-services\")' onmouseout='hover(\"out\",\"[p]-services\")'>recommend you other services<span class='tip tipsmall'>Over time, this device will use the collected data to recommend you other services (for exampe, a new application, device, or subscription service).</span></span>",
			
			"shared with third parties" => "<span class='term' onmouseover='hover(\"over\",\"[p]-third\")' onmouseout='hover(\"out\",\"[p]-third\")'>shared with third parties<span class='tip tipsmall'>Data collected by this device will be shared with third-party affiliates.</span></span>",
		];
		
		
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
						_questions.required = 1 OR _questions.cond = 'w'
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
				$this->questionsLeft += 126;
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
					
				
				//scenario hack!
				$abs = '';
				$pre = '';
				
				if($this->title == "Scenario"){
					$this->scenario = true;
					$this->title .= " " . $this->pagenr . " out of 13";
					$abs .= ' class="scenario"';
					$pre = '<div id="preTitle">&nbsp;</div>';
				}
					
				$HTML .= $pre.'<div id="surveyTitle"'.$abs.'>' . $this->title . '</div>';
				
				
				if($this->explanation <> ""){
					$HTML .= '<div id="explanation">' . $this->explanation . '</div>';
				}
				$HTML .= '</div>';
				$HTML .= '<div id="questions"><div id="questionsInner"><form onClick="checkForm()" onkeyup="checkForm()">';
				
				global $mc;
				
				//UNCOMMENT TO TURN ON CACHING
				//$k = 'iot_'.$this->id.':'.$this->session->condition.':'.$this->page->pagenr;
				//$h = apc_fetch($k);
				//if(!$h){
					$h = $this->page->getHTML();
					//apc_store($k, $h);
				//}
				$HTML .= $h;
				$HTML = str_replace("IDVAL",$this->session->userId,$HTML);

				//tooltip detection
				if($this->scenario){
					$HTML = $this->replaceToolTip($HTML);
				}
				
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
		
		private function replaceToolTip($text){
			foreach($this->tooltips as $code => $tip){
				$text = str_replace($code, $tip, $text);
			}
			$text = str_replace("[p]",$this->pagenr,$text);
			return $text;
		}
		
	}

?>