<?php
/*
 * Created on 2011-12-18
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class question_exam
{
	public $G;

	public function __construct(&$G)
	{
		$this->G = $G;
	}

	public function _init()
	{
		if(!$this->init)
		{
			$this->sql = $this->G->make('sql');
			$this->db = $this->G->make('db');
			$this->ev = $this->G->make('ev');
			$this->html = $this->G->make('html');
			$this->basic = $this->G->make('basic','exam');
			$this->exam = $this->G->make('exam','exam');
			$this->section = $this->G->make('section','exam');
			$this->tpl = $this->G->make('tpl');
			$this->selectorder = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N');
			$this->tpl->assign('selectorder',$this->selectorder);
			$this->init = 1;
		}
	}

	public function parse($question)
	{
		$questype = $this->basic->getQuestypeById($question['questiontype']);
		if($questype['questsort'])return $this->subjective($question);
		switch($questype['questchoice'])
		{
			case '1':
			case '4':
			return $this->objective($question);
			break;

			default:
			return $this->objective($question,true);
			break;
		}
	}

	public function subjective($question)
	{
		$r = array('title'=>$question['question'],'selectlist'=>false,'selects'=>'','answer'=>$question['questionanswer'],'describe'=>$question['questiondescribe']);
		return $r;
	}

	public function objective($question,$isMultiple = false)
	{
		$r = array('title'=>$question['question'],'describe'=>$question['questiondescribe'],'type' => $question['questiontype']);
		$question['questionselect'] = explode("\n",$question['questionselect']);
		if(!$question['questionselect'][0])
		{
			$question['questionselect'] = array('对','错');
		}
		$r['selectlist'] = $question['questionselect'];
		$values = array();
		foreach($question['questionselect'] as $id => $select)
		{
			//$values[] = array('key'=>$this->selectorder[$id].':'.$select,'value'=>$this->selectorder[$id]);
			$values[] = array('key'=>$this->selectorder[$id],'value'=>$this->selectorder[$id]);
		}
		if($isMultiple)
		{
			$args = array('pars'=>array(array('key'=>'name','value'=>'question['.$question['questionid'].']')),'values'=>$values);
			$r['selects'] = $this->html->checkBoxArray($args);
		}
		else
		{
			$args = array('pars'=>array(array('key'=>'name','value'=>'question['.$question['questionid'].']')),'values'=>$values);
			$r['selects'] = $this->html->radio($args);
		}
		$r['answer'] = explode("\n",$question['questionanswer']);
		sort($r['answer']);
		foreach($r['answer'] as $id=>$p)
		{
			$r['answer'][$id] = trim($p);
		}
		$r['answer'] = implode('',$r['answer']);
		return $r;
	}

	//获取某些指定知识点的试题列表
	public function getRandQuestionListByKnowid($knowid,$typeid)
	{
		$data = array('DISTINCT questions.questionid',array('questions','quest2knows'),array("quest2knows.qkknowsid IN ({$knowid})","quest2knows.qktype = 0","quest2knows.qkquestionid = questions.questionid","questions.questiontype = '{$typeid}'","questions.questionstatus = 1"),false,false,false);
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		$t = array();
		foreach($r as $p)
		{
			$t[] = $p['questionid'];
		}
		return $t;
	}

	//获取某些指定知识点的题帽题列表
	public function getRandQuestionRowsListByKnowid($knowid,$typeid,$number = false)
	{
		if($number)
		$data = array('DISTINCT questionrows.qrid',array('questionrows','quest2knows'),array("quest2knows.qkknowsid IN ({$knowid})","quest2knows.qktype = 1","quest2knows.qkquestionid = questionrows.qrid","questionrows.qrtype = '{$typeid}'","questionrows.qrnumber <= '{$number}'","questionrows.qrstatus = 1"),false,false,false);
		else
		$data = array('DISTINCT questionrows.qrid',array('questionrows','quest2knows'),array("quest2knows.qkknowsid IN ({$knowid})","quest2knows.qktype = 1","quest2knows.qkquestionid = questionrows.qrid","questionrows.qrtype = '{$typeid}'","questionrows.qrstatus = 1"),false,false,false);
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		$t = array();
		foreach($r as $p)
		{
			$t[] = $p['qrid'];
		}
		return $t;
	}

	//获取试题列表
	public function getRandQuestionList($args = 1)
	{
		if(!is_array($args))$args = array();
		$args[] = "questions.questionstatus = 1";
		$args[] = "quest2knows.qkquestionid = questions.questionid";
		$args[] = "quest2knows.qktype = 0";
		$data = array('DISTINCT questions.questionid',array('questions','quest2knows'),$args,false,false,false);
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		$t = array();
		foreach($r as $p)
		{
			$t[] = $p['questionid'];
		}
		return $t;
	}

	//获取特殊试题列表
	public function getRandQuestionRowsList($args = 1)
	{
		if(!is_array($args))$args = array();
		$args[] = "questionrows.qrstatus = 1";
		$args[] = "quest2knows.qkquestionid = questionrows.qrid";
		$args[] = "quest2knows.qktype = 1";
		$data = array('DISTINCT questionrows.qrid',array('questionrows','quest2knows'),$args,false,false,false);
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		$t = array();
		foreach($r as $p)
		{
			$t[] = $p['qrid'];
		}
		return $t;
	}

	//根据ID获取特殊试题编号
	public function getSpecialQuestionById($questionid)
	{
		$data = array('questionid','questions',array("questionparent = '{$questionid}'","questionstatus = 1"),false,"questionsequence ASC");
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		$t = array(0 => $questionid);
		foreach($r as $p)
		{
			$t[] = $p['questionid'];
		}
		return $t;
	}

	//根据科目和地区信息获取知识点
	public function getKnowsBySubjectAndAreaid($subjectid,$areaid)
	{
		$data = array('esknowsids','examsection',array("essubjectid = '{$subjectid}'","esareaid = '{$areaid}'"));
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		foreach($r as $p)
		{
			$t[] = $p['esknowsids'];
		}
		$n = implode(',',$t);
		$data = array("knowsid","knows",array("knowsid IN ({$n})","knowsstatus = 1"));
		$sql = $this->sql->makeSelect($data);
		$r = $this->db->fetchAll($sql);
		foreach($r as $p)
		{
			$m[] = $p['knowsid'];
		}
		return implode(',',$m);
	}

	public function selectScaleQuestions($examid,$basic)
	{
		$exam = $this->exam->getExamSettingById($examid);
		if(!$exam['examsetting']['scalemodel'])
		return $this->selectQuestions($examid,$basic);
		$settings = $exam['examsetting'];
		foreach($settings['examscale'] as $key => $p)
		{
			$s1 = explode("\n",$p);
			foreach($s1 as $s2)
			{
				$s2 = trim($s2,"\n\r");
				$s2 = explode(":",$s2);
				if($s2[2])$num = explode(',',$s2[2]);
				else $num = $s2[1];
				$knowsids = explode(',',$s2[0]);
				$tmp = array();
				foreach($basic['basicknows'] as $bp)
				{
					foreach($bp as $tbp)
					{
						$tmp[] = $tbp;
					}
				}
				$knowsids = array_intersect ($knowsids, $tmp);
				$knowsids = implode(',',$knowsids);
				if(!$knowsids)$knowsids = '0';
				if(is_array($num))
				{
					$number = array('1'=>intval($num[0]),'2'=>intval($num[1]),'3'=>intval($num[2]));
					arsort($number);
					$par = 0;
					foreach($number as $nkey => $t)
					{
						if(!$par)
						{
							$par++;
							$trand = rand(1,4);
							if($trand < 3)
							{
								$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrlevel = '{$nkey}'","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'"));
								if(count($qrs))
								{
									$qrid = $qrs[array_rand($qrs,1)];
									$questionrow[$key][] = $qrid;
									$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
									$t = intval($t - $qr['qrnumber']);
								}
							}
						}
						if($t)
						{
							$r = $this->getRandQuestionList(array("quest2knows.qkknowsid IN ({$knowsids})","questions.questionlevel = '{$nkey}'","questions.questiontype = '{$key}'"));
							if(is_array($r))
							{
								if((count($r) >= $t))
								{
									if($t <= 1)
									{
										$question[$key][] = $r[array_rand($r,1)];
									}
									else
									{
										$ts = array_rand($r,$t);
										foreach($ts as $tmp)
										{
											$question[$key][] = $r[$tmp];
										}
									}
								}
								else
								{
									foreach($r as $tmp)
									$question[$key][] = $tmp;
								}
							}
						}
						while($t)
						{
							$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrlevel = '{$nkey}'","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'","questionrows.qrnumber > 0"));
							if(count($qrs))
							{
								$qrid = $qrs[array_rand($qrs,1)];
								$questionrow[$key][] = $qrid;
								$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
								$t = intval($t - $qr['qrnumber']);
							}
							else
							break;
						}
					}
				}
				else
				{
					$par = 0;
					$t = $num;
					if(!$par)
					{
						$par++;
						$trand = rand(1,4);
						if($trand < 3)
						{
							$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'"));
							if(count($qrs))
							{
								$qrid = $qrs[array_rand($qrs,1)];
								$questionrow[$key][] = $qrid;
								$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
								$t = intval($t - $qr['qrnumber']);
							}
						}
					}
					if($t)
					{
						$r = $this->getRandQuestionList(array("quest2knows.qkknowsid IN ({$knowsids})","questions.questiontype = '{$key}'"));
						if(is_array($r))
						{
							if((count($r) >= $t))
							{
								if($t <= 1)
								{
									$question[$key][] = $r[array_rand($r,1)];
								}
								else
								{
									$ts = array_rand($r,$t);
									foreach($ts as $tmp)
									{
										$question[$key][] = $r[$tmp];
									}
								}
							}
							else
							{
								foreach($r as $tmp)
								$question[$key][] = $tmp;
							}
						}
					}
					while($t)
					{
						$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'"));
						if(count($qrs))
						{
							$qrid = $qrs[array_rand($qrs,1)];
							$questionrow[$key][] = $qrid;
							$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
							$t = intval($t - $qr['qrnumber']?$qr['qrnumber']:1);
						}
						else
						break;
					}
				}
			}
		}
		return array('question'=>$question,'questionrow'=>$questionrow,'setting'=>$exam);
	}

	//筛选随机试题
	public function selectQuestions($examid,$basic)
	{
		$exam = $this->exam->getExamSettingById($examid);
		if($exam['examsetting']['scalemodel'])
		return $this->selectScaleQuestions($examid,$basic);
		$knowsids = '';
		foreach($basic['basicknows'] as $p)
		{
			$knowsids .= trim(implode(',',$p),', ').',';
		}
		$knowsids = trim($knowsids,', ');
		$settings = $exam['examsetting'];
		foreach($settings['questype'] as $key => $p)
		{
			$number = array('1'=>$p['easynumber'],'2'=>$p['middlenumber'],'3'=>$p['hardnumber']);
			arsort($number);
			$par = 0;
			foreach($number as $nkey => $t)
			{
				if(!$par)
				{
					$par++;
					$trand = rand(1,4);
					if($trand < 3)
					{
						$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrlevel = '{$nkey}'","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'"));
						if(count($qrs))
						{
							$qrid = $qrs[array_rand($qrs,1)];
							$questionrow[$key][] = $qrid;
							$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
							$t = intval($t - $qr['qrnumber']);
						}
					}
				}
				if($t)
				{
					$r = $this->getRandQuestionList(array("quest2knows.qkknowsid IN ({$knowsids})","questions.questionlevel = '{$nkey}'","questions.questiontype = '{$key}'"));
					if(is_array($r))
					{
						if((count($r) >= $t))
						{
							if($t <= 1)
							{
								$question[$key][] = $r[array_rand($r,1)];
							}
							else
							{
								$ts = array_rand($r,$t);
								foreach($ts as $tmp)
								{
									$question[$key][] = $r[$tmp];
								}
							}
						}
						else
						{
							foreach($r as $tmp)
							$question[$key][] = $tmp;
						}
					}
				}
				while($t)
				{
					$nouserid = implode(',',$questionrow[$key]);
					$tmpargs = array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrlevel = '{$nkey}'","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'","questionrows.qrnumber > 0");
					if($nouserid)$tmpargs[] = "questionrows.qrid NOT IN ({$nouserid}) ";
					$qrs = $this->getRandQuestionRowsList($tmpargs);
					if(count($qrs))
					{
						$qrid = $qrs[array_rand($qrs,1)];
						$questionrow[$key][] = $qrid;
						$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
						$t = intval($t - $qr['qrnumber']);
					}
					else
					break;
				}
			}
		}
		return array('question'=>$question,'questionrow'=>$questionrow,'setting'=>$exam);
	}

	public function selectQuestionsByKnows($knowsid,$qt)
	{
		$knowsids = $knowsid;
		foreach($qt as $key => $t)
		{
			$par = 0;
			if(!$par)
			{
				$par++;
				$trand = rand(1,4);
				if($trand < 3)
				{
					$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'"));
					if(count($qrs))
					{
						$qrid = $qrs[array_rand($qrs,1)];
						$questionrow[$key][] = $qrid;
						$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
						$t = intval($t - $qr['qrnumber']);
					}
				}
			}
			if($t)
			{
				$r = $this->getRandQuestionList(array("quest2knows.qkknowsid IN ({$knowsids})","questions.questiontype = '{$key}'"));
				if(is_array($r))
				{
					if((count($r) >= $t))
					{
						if($t <= 1)
						{
							$question[$key][] = $r[array_rand($r,1)];
						}
						else
						{
							$ts = array_rand($r,$t);
							foreach($ts as $tmp)
							{
								$question[$key][] = $r[$tmp];
							}
						}
					}
					else
					{
						foreach($r as $tmp)
						$question[$key][] = $tmp;
					}
				}
			}
			while($t)
			{
				$qrs = $this->getRandQuestionRowsList(array("quest2knows.qkknowsid IN ({$knowsids})","questionrows.qrtype = '{$key}'","questionrows.qrnumber <= '{$t}'","questionrows.qrnumber > 0"));
				if(count($qrs))
				{
					$qrid = $qrs[array_rand($qrs,1)];
					$questionrow[$key][] = $qrid;
					$qr = $this->exam->getQuestionRowsByArgs("qrid = '{$qrid}'");
					$t = intval($t - $qr['qrnumber']);
				}
				else
				break;
			}
		}
		$r = array('question'=>$question,'questionrow'=>$questionrow);
		return $r;
	}
}
?>
