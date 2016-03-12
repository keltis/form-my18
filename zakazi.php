<?php

class my_orders { 

		//вычисляем даты
		public $today;
		public $weekLenhth;
		public $todayDate;
		public $todayDay;
		public $todayMonth;
		public $todayYear;
		public $todayDayNum;
		public $lastDay;
		public $nextDay;
		public $laststartWeek;
		public $lastendWeek;
		public $nextstartWeek;
		public $nextendWeek;
		public $current_first_day;
		public $current_month;
		public $current_last_day;
		public $next_week_start;
		public $next_week_end;	
		public $month_n;
		public $all_cost;	
		
	function __construct () {

		include_once ('function.php');
		echo '<style>';
		echo '.current_week {width: 48%; float: left;}';
		echo '.next_week {width: 48%; float: left; margin-left:10px;}';
		echo '.day_1 {background: #ccddff; height: 13px; padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #505050; margin: -10px;}';
		echo '.day_2 {background: #d5ccff; height: 13px; padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #505050; margin: -10px;}';
		echo '.day_3 {background: #e5ccff; height: 13px; padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #505050; margin: -10px;}';
		echo '.day_4 {background: #ffccf6; height: 13px; padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #505050; margin: -10px;}';
		echo '.day_5 {background: #ffccd4; height: 13px; padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #505050; margin: -10px;}';
		echo '.day_6 {background: #ffe5cc; height: 13px; padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #505050; margin: -10px;}';
		echo '.header {padding: 8px; font-family: Verdana; font-size: 14px; font-weight: bold; color: #2a87ac;}';
		echo '.day {padding: 10px; background:#FAFAFA; border: 1px solid #ccddff;}';
		echo '.item {font-family: Verdana; font-size: 12px; line-height: 1.2; font-weight: 300;}';
		echo '.all_cost {float: left; text-align: center; width: 100%; padding-top: 10px; padding-bottom: 10px; font-family: Verdana; font-size: 14px; font-weight: 600;}';
		echo '</style>';
		
		$this->today = date("Y-m-d");
		$this->weekLenhth = 7;
		$this->todayDate = mktime();
		$this->todayDay = date('d', $this->todayDate)+1;
		$this->todayMonth = date('m', $this->todayDate);
		$this->todayYear = date('Y', $this->todayDate);
		$this->todayDayNum = date('w', $this->todayDate);
		$this->lastDay = date('d', $this->todayDate)-6;
		$this->nextDay = date('d', $this->todayDate)+8;
			
		$laststartWeek = mktime(0,0,0,$this->todayMonth,$this->todayDay-$this->todayDayNum,$this->todayYear); // начало текущей недели
		$lastendWeek = mktime(0,0,0,$this->todayMonth,$this->todayDay-$this->todayDayNum+5,$this->todayYear); // конец текущей недели

		$nextstartWeek = mktime(0,0,0,$this->todayMonth,$this->nextDay-$this->todayDayNum,$this->todayYear); //начало следуще недели
		$nextendWeek = mktime(0,0,0,$this->todayMonth,$this->nextDay-$this->todayDayNum+5,$this->todayYear); // конец следущей недели

		$this->current_first_day = date('d',$laststartWeek);
		$this->current_month = date('n',$laststartWeek);
		$this->next_month = date('n',$nextstartWeek);
		$this->current_last_day = date('d',$lastendWeek);
		$this->next_week_start = date('d',$nextstartWeek);
		$this->next_week_end = date('d',$nextendWeek);	
		$this->month_n = month_name_label($this->current_month);
		$this->next_month_n = month_name_label($this->next_month);
	}
// ======================================= функция вывода заказов =================================
	function complex($xml, $executed) {
		
			echo '<table style="margin-top: 20px; text-align: left;">';
			foreach($xml->food_intake as $sub2)  // выводим наименование трапезы
				{
					if ($sub2['name'] == 'Завтрак') { // если завтрак
 				echo '<tr><th style="border-right: 1px solid; width: 49%; '.$executed.'">';
					echo '<strong>'.$sub2['name'].'</strong></br>';
					echo '<div class="item">';
					foreach($sub2->item as $sub3) // выводим состав комплекса
						{
						$attrs = $sub3->attributes();
						echo $attrs.'</br>';
						}
					echo '</div>';
    					echo '</br><strong>Стоимость: '.$sub2['cost'].' руб.</strong>';
						$this->all_cost += $sub2['cost'];
				echo '</th>';
					}
					elseif ($sub2['name'] == 'Обед') { // если обед
				echo '<th style="padding-left:10px; width: 49%; '.$executed.'">';			
					echo '<strong>'.$sub2['name'].'</strong></br>';
					echo '<div class="item">';
					foreach($sub2->item as $sub3) // выводим состав комплекса
						{
						$attrs = $sub3->attributes();
						echo $attrs.'</br>';
						}
					echo '</div>';
    					echo '</br><strong>Стоимость: '.$sub2['cost'].' руб.</strong>';
						$this->all_cost += $sub2['cost'];
				echo '</th></tr>';				
					}	
					elseif ($sub2['name'] == 'Свободный заказ') { // если обед
					echo '<th style="padding-left:10px; width: 49%; '.$executed.'">';			
					echo '<strong>'.$sub2['name'].'</strong></br>';
					echo '<div class="item">';
					foreach($sub2->item as $sub3) // выводим состав комплекса
						{
						$attrs = $sub3->attributes();
						echo $attrs.'</br>';
						}
					echo '</div>';
    					echo '</br><strong>Стоимость: '.$sub2['cost'].' руб.</strong>';
						$this->all_cost += (float)$sub2['cost'];
				echo '</th></tr>';				
					}	
				}	
			echo '</table>';
		}
	
// ========================================= функция на текущую неделю ================================
	function last_week () { // -------------- вычисляем даты текущей недели ------------------------
	  include ('/var/www/html/my18/plugins/settings.php');		
	  echo '<div class="current_week">';
	  echo '<div class="header">Меню на неделю с '.$this->current_first_day.' по '.$this->current_last_day.' '.$this->month_n.'</div>';
		for ($i=1; $i<=6; $i++) {
			$d = $i-1;
		$lastendWeek = mktime(0,0,0,$this->todayMonth,$this->todayDay-$this->todayDayNum+$d,$this->todayYear);
		$current_days = date('Y-m-d',$lastendWeek);
		$current_week_orders = efapi("/dh_future_order?uid=$card_num&d=$current_days&password=$api_pass");
		$file = efapi("/dh_order_history?uid=$card_num&d=$current_days&password=$api_pass");
		$ispoln = simplexml_load_string($file);
		if (isset($ispoln->food_intake)) $executed = 'color:#999;';
		else $executed = $executed = 'color:#000;';
		$xml = simplexml_load_string($current_week_orders);
		echo '<div class="day">';
				echo '<div class="day_'.$i.'">'.day_name($i).'</div>';		
			$this->complex($xml, $executed);
		echo '</div>';
		}

	  echo '</div>';

	}
// ========================================= функция на следующую неделю ================================
	function next_week () {  // -------------- вычисляем даты следующей недели ----------------------
	  include ('/var/www/html/my18/plugins/settings.php');
	  echo '<div class="next_week">';
	  echo '<div class="header">Меню на неделю с '.$this->next_week_start.' по '.$this->next_week_end.' '.$this->next_month_n.'</div>';
		for ($i=1; $i<=6; $i++) {
			$d = $i-1;
		$nextWeek = mktime(0,0,0,$this->todayMonth,$this->nextDay-$this->todayDayNum+$d,$this->todayYear);
		$next_day = date('Y-m-d',$nextWeek);
		$current_week_orders = efapi("/dh_future_order?uid=$card_num&d=$next_day&password=$api_pass");
		$xml = simplexml_load_string($current_week_orders);
		echo '<div class="day">';
				echo '<div class="day_'.$i.'">'.day_name($i).'</div>';		
			$this->complex($xml, '');
		echo '</div>';
		}
	  echo '</div>';
	}
}

// ================================= вывод класса и функций ========================= 
		
$my_obj = new my_orders;
$my_obj -> last_week ();
$my_obj -> next_week ();
if ($my_obj -> all_cost!='') {
echo '<div class="all_cost">Общая стоимость всех заказов: '.$my_obj -> all_cost.' руб.</div>';
}
?>