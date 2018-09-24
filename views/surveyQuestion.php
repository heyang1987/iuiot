<?php
	
	class Question{
		
		private $id;
		private $session;
		private $text;
		private $optionstype;
		private $options;
		private $requiredText;
		private $formatting;
	
	
		public function __construct($id, $text, $optionstype, $optionsName, $required, $formatting, $surveynr, $session){
			$this->id = $id;
			$this->session = $session;
			$this->optionstype = $optionstype;

			//hack for skipped questions
			if($required > 100){
				$r = $required - 100;
				$query = "
							SELECT
								obj
							FROM
								events
							WHERE
								sessionId = '".$this->session->id."'
							AND
								action = 'skip'
							AND
								obj = '".$r."'
				";
				$result = Database::getInstance()->performQuery($query);
				if($result){
					$this->requiredText = "skipped";
				}else {
					$this->requiredText = "required";
				}
			//set required (real part)
			}else if($required > 0){
				$this->requiredText = "required";
			}else{
				$this->requiredText = "optional";
			}
			if($formatting == ''){
				$this->formatting == '';
			}else{
				$this->formatting = ' '.$formatting;
			}
			
			$this->options = $this->makeOptions($optionstype,$optionsName);
			$this->text = $text;
		}
		
		public function getHTML(){
			if($this->requiredText == "skipped"){
				return ' ';
			}
			
			$HTML = '';
			
			$HTML .= '<div class="'.$this->requiredText.' question '.$this->optionstype.$this->formatting.'" id="q_'.$this->id.'"><div class="questionText'.$this->formatting.'">' . $this->text . '</div>';
			$HTML .= $this->options;
			$HTML .= '</div>';
			return $HTML;
		}
		
		private function makeOptions($optiontype,$optionsName){
			$options = '<div class="other" id="'.$this->id.'"></div>';
			switch($optiontype){
				case 'radio':
				case 'checkbox':
					$values = $this->getValues($optionsName);
					$counter = 0;
					$options = '<div class="'.$optiontype.'" id="'.$this->id.'">';
					foreach($values as $value){
						//header row hack
						$checksubs='';
						$checked='';
						$chklbl='';
						
						$formatArray = explode(" ",$this->formatting);
						
						
						
						//defaults condition
						if($this->formatting <> ""){
							if(count($formatArray)>0){
								if(($formatArray[1]=="enable" & $value['value']==1) | ($formatArray[1]=="disable" & $value['value']==0)){
									$checked=' checked';
								}
							}
						}
						
						$options .= '<div class="option l' . sizeof($values) . $chklbl. '">';
						$optionsLabel = '<div class="label"><label for="'.$this->id.'_'.$value['value'].'">'.$value['text'].'</label></div>';
						
						$optionsInput = '<input type="'.$optiontype.'" name="'.$this->id.'" value="'.$value['value'].'" id="'.$this->id.'_'.$value['value'].'"'.$checksubs.$checked.' />';

						$options .= $optionsLabel;
						$options .= $optionsInput;
						$options .= '</div>';
					}
					$options .= '</div>';
					break;
				case 'twocolumn checkbox':
				case 'twocolumn radio':
					$values = $this->getValues($optionsName);
					$counter = 0;
					$options = '<div class="'.$optiontype.'" id="'.$this->id.'">';
					foreach($values as $value){
						$counter++;
						if($counter == 1){
							$options .= '<div class="checkleft">';
						}elseif($counter == 5){
							$options .= '</div><div class="checkright">';
						}
						$options .= '<span class="option l' . sizeof($values) . '">';
						$options .= '<div class="label"><label for="'.$this->id.'_'.$value['value'].'">'.$value['text'].'</label></div>';
						$options .= '<input type="'.$optiontype.'" name="'.$this->id.'" value="'.$value['value'].'" id="'.$this->id.'_'.$value['value'].'" />';
						$options .= '</span>';
						if($counter == sizeof($values)){
							$options .= '</div>';
						}
					}
					$options .= '</div>';
					break;
				case 'dropdown_adaptive_long':
				case 'dropdown_adaptive_short':
				case 'dropdown':
					$values = $this->getValues($optionsName);
					$options = '<div class="'.$optiontype.'" id="'.$this->id.'">';
					$options .= '<select onchange="checkForm()">';
					$options .= '<option value="none"></option>';
					foreach($values as $value){
						$options .= '<option value="'.$value['value'].'">'.$value['text'].'</option>';
					}
					$options .= '</select></div>';
					break;
					
				case '1-10':
				case '1-100':
				case 'int':
				case 'othertext':
				case 'text':
					$options = '<div class="'.$optiontype.'" id="'.$this->id.'">';
					$options .= '<input type="text" class="input'.$optiontype.'" name="'.$this->id.'" id="'.$this->id.'_text" onkeypress="if(event.keyCode == 13){return false;}" />';
					$formatArray = explode(" ",$this->formatting);
					$options .= '</div>';
					break;
				
				case 'textarea':
					$options = '<div class="textarea" id="'.$this->id.'">';
					$options .= '<textarea name="'.$this->id.'" id="'.$this->id.'_text"></textarea>';
					$options .= '</div>';
					break;
				case 'trackbuttons':
					$options = '<div class="buttonsdiv" id="'.$this->id.'">';
					$options .= '<div class="appbutton" id="nobtn" onClick="submitFormVar(\'no\')">&nbsp;</div><div class="appbutton" id="yesbtn"  onClick="submitFormVar(\'yes\')"></div>';
					$options .= '</div>';
					break;
				case 'buttons':
					$options = '<div class="buttonsdiv" id="'.$this->id.'">';
					$options .= '<div class="appbutton" id="notbtn" onClick="submitFormVar(\'no\')">&nbsp;</div><div class="appbutton" id="okbtn"  onClick="submitFormVar(\'yes\')"></div>';
					$options .= '</div>';
					break;
				case 'nextbutton':
				case 'exitbutton':
				case 'loginbutton':
					$options = '<div class="buttonsdiv" id="'.$this->id.'">';
					$options .= '<div class="appbutton" id="'.$optiontype.'" onClick="submitFormVar(\'yes\')">&nbsp;</div>';
					$options .= '</div>';
					break;
			}
			return $options;
		}
		
		private function getValues($optionsName){
			$condArray = explode(" ",$this->session->condition);
			$cond = implode("','",$condArray);
			$query = "
				SELECT SQL_CACHE
					value, text
				FROM
					_options
				WHERE
					name = '$optionsName'
				AND
					(cond IN ('$cond') or cond = '') 
				ORDER BY
					rank ASC
			";
			$result = Database::getInstance()->performQuery($query);
			return $result;
		}
		
		private function getAdaptive(){
			$prevId = $this->id+1;
			$sessId = $this->session->id;
			$condArray = explode(" ",$this->session->condition);
			$cond = implode("','",$condArray);
			$query = "
				SELECT
					_adaptive.option AS o
				FROM
					answers, _adaptive
				WHERE
					sessionId = '$sessId'
				AND
					questionId = '$prevId'
				AND
					answers.answer = _adaptive.answer
				AND
					(cond IN ('$cond') or cond = '') 
			";
			$result = Database::getInstance()->performQuery($query);
			$options = array();
			foreach($result as $o){
				array_push($options,$o['o']);
			}
			return $options;
		}
	}
	
?>