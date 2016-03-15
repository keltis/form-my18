<style>
.u_info{
border-bottom: 1px solid #c4c4c4;
color: #333333;
font-family: Verdana;
font-size: 12px;
line-height: 1.6;
}

.u_zakaz{
font-family: Verdana;
font-size: 12px;
line-height: 1.6;
border-bottom: 1px solid #c4c4c4;

}
.u_dop{
padding-top: 10px;
font-family: Verdana;
font-size: 12px;
line-height: 1.6;
}

		.tooltip_zakaz {
			outline: none;
			cursor: help;
			text-decoration: none;
			position: relative;
		    padding       : 0px 7px;
			margin-left   : 10px;
			z-index		: 1;
			background: url(../templates/baum_lyceum/images/spr.png) no-repeat;
		}
		.tooltip_zakaz span {
			margin-left: -999em;
			position: absolute;
		}
		.tooltip_zakaz:hover span {
			position: absolute; 
			left: 1em; 
			top: 2em;
			z-index: 99;
			margin-left: 0;
			width: 250px;
			background: #fffaaa;
			color: #333333;
		}
	.popup
	{
		font-family:tahoma;
		background: #fff;
		position: absolute;
		text-align: left;
		font-size: 14px;
		margin: 0 auto;
		top:34%;
		left:33%;
		padding: 0;
		border-radius: 4px;
		width: 440px;
		height: 180px;
	}
	.window_overlay_box 
	{
		background: none repeat scroll 0 0 rgba(0, 0, 0, 0.25);
		height: 100%;
		left: 0;
		position: fixed;
		top: 0;
		width: 100%;
		z-index: 100;
		display: none;
	}
	.amount
	{
		width: 128px;
	}
	.closer
	{
		font-family: tahmoa;
		background: url("/templates/baum_lyceum/images/zagnewsbg2.png") repeat-x;
		width: 100%;
		height: 40px;
		border-radius: 4px;
		padding-top: 4px;
		text-align: center;
		color:#eee;
		font-size:16px;
	}
		</style>
<?php


		define( '_JEXEC', 1 );
		define('JPATH_BASE', dirname(__FILE__) );
		define( 'DS', DIRECTORY_SEPARATOR );
		require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
		require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
		$mainframe = JFactory::getApplication('site');
		$db = JFactory::getDBO();
		$mainframe->initialise();
		$user = JFactory::getUser();
		$uid = $user->get('id');
		$name = $user->get('name');
		$username = $user->get('username');
		$groups = $user->get('groups');
		
		foreach ($groups as $key=>$val) {$rid = $val;}

	$api_pass = '123456'; // пароль для ease food сервера

	$db->setQuery("SELECT * FROM #__user_profiles WHERE user_id='$uid'");
	$arr = $db->loadRowList();
		foreach ($arr as $val) {
			switch ($val[1]) {
				case 'profile.cart_number' : $card_num =  json_decode($val[2]); break;
				case 'profile.fio' : $fullnames =  json_decode($val[2]); break;
				case 'profile.group' : $group =  json_decode($val[2]); break;
				case 'profile.status_g' : $status_g =  json_decode($val[2]); break;
			}
		}


include_once ('function.php');

//вычисляем даты
$today=date("Y-m-d");
$weekLenhth = 7;
$todayDate = mktime();
$todayDay = date('d', $todayDate)+1;
$todayMonth = date('m', $todayDate);
$todayYear = date('Y', $todayDate);
$todayDayNum = date('w', $todayDate);
$lastDay = date('d', $todayDate)-6;
$nextDay = date('d', $todayDate)+8;

$laststartWeek = mktime(0,0,0,$todayMonth,$todayDay-$todayDayNum,$todayYear);
$lastendWeek = mktime(0,0,0,$todayMonth,$todayDay+($weekLenhth-$todayDayNum-1),$todayYear);
$lastlaststartWeek = mktime(0,0,0,$todayMonth,$lastDay-$todayDayNum,$todayYear);
$nextstartWeek = mktime(0,0,0,$todayMonth,$nextDay-$todayDayNum,$todayYear);

$current_first_day = date('Y-m-d',$laststartWeek);
$last_first_day = date('Y-m-d',$lastlaststartWeek);
$next_week = date('Y-m-d',$nextstartWeek);

$db->setQuery("SELECT event_date FROM food_log WHERE user_id = '".$uid."' AND message = 'заказ успешно принят' ORDER BY event_date DESC LIMIT 1");
$dat = $db->loadResult();
$db->setQuery("SELECT parameters FROM food_log WHERE user_id = '".$uid."' AND message = 'заказ успешно принят' ORDER BY event_date DESC LIMIT 1");
$parameters = $db->loadResult();
$block_info = efapi("/dh_customer?uid=$card_num&password=$api_pass");
$info = simplexml_load_string($block_info);
echo '<div class="u_info">';
	foreach($info->customer as $inf)
	{

		$cardId = str_replace("-", "", $inf->uid);
		echo 'Ваш номер карты: <strong>'.$inf->uid.'</strong><a class="tooltip_zakaz">
		<span>Если номер карты не совпадает с Вашим: обратитесь в службу тех. поддержки!</span></a><br/>';
		$dostup = $inf->balance-$inf->process;
		echo 'На счету: <strong>'.$inf->balance.' р.</strong><a class="tooltip_zakaz"><span>Внимание! Cписание средств происходит в день выдачи заказа!</span></a></br>';
		echo 'Зарезервировано : <strong>'.$inf->process.' руб.</strong><a class="tooltip_zakaz"><span>Это средства зарезервированные для оплаты Ваших заказов</span></a></br>';
		echo 'Свободно для заказа: <strong>'.$dostup.' руб.</strong></br>';
		//if($uid == 134)
		//{
		echo '<a href="#" id="upBill" onclick="upBill(); return false;">Пополнить счет</a>';
		echo '<div class="window_overlay_box" id="window_overlay_box"><div>
		<div class="popup" id="upBillPopup">

<div class="closer">Пополнение счета</div>
<div class="body" align="center">
<table>
	<tr>
		<td>Сумма платежа:</td>
		<td>
			<input type="text" name="amount" id="amount" onKeyUp="isAmount(); return false;"/> руб.
			<span id="error" style="color: red; display:none;">Сумма должна быть больше 10 руб.</span>
		</td>
	</tr>
	<tr>
		<td>Способ оплаты</td>
		<td>
			<select name="payway" id="payway">
				<optgroup label="Банковские карты">
					<option value="499669">VISA, MasterCard</option>
				</optgroup>
				<optgroup label="Электронные системы">
					<option value="775856">SitePokupok.ru</option>
					<option value="1015">Монета.Ру</option>
					<option value="1020">Яндекс.Деньги</option>
					<option value="1017">WebMoney</option>
				</optgroup>
				<optgroup label="Банковские системы">
					<option value="587412">Интернет-банк "Альфа-Клик"</option>
					<option value="661709">Интернет-банк "Промсвязьбанк"</option>
				</optgroup>
				<optgroup label="Платежные терминалы">
					<option value="510801">Сбербанк</option>
				</optgroup>
			</select>
		</td>
	</tr>
</table>
<input type="checkbox" name="rule" id="rule" onchange="clickChek(); return false;"/>
<label for="rule" style="font-size:11px;">Я ознакомился с <a style="font-size: 11px;" href="https://www.moneta.ru/info/d/ru/public/users/offer.htm" target="_blank">правилами оплаты</a> и подтверждаю свое согласие.</label></br>
<div align="center">
<input type="button" id="fail" value="отмена" style="width:100px;" onclick="closePopup();"/>
<input type="button" id="ok" value="далее" style="width:100px;" onclick="sucessPay();" disabled/>
</div></div></div></div></div>';

echo '<script>

	function clickChek()
	{
		var value = document.getElementById("amount").value;
		amount = value*1;
		if(document.getElementById(\'rule\').checked && amount >= 10)
			document.getElementById(\'ok\').removeAttribute(\'disabled\',\'\');
		else
			document.getElementById(\'ok\').setAttribute(\'disabled\',\'disabled\');
	}

	function isAmount()
	{
		var value = document.getElementById("amount").value;
		amount = value*1;
		if(amount < 10 )
		{
			document.getElementById(\'error\').style.display=\'block\';
			document.getElementById(\'ok\').setAttribute(\'disabled\',\'disabled\');
		}
		else
		{
			document.getElementById(\'error\').style.display=\'none\';
			
			if(document.getElementById(\'rule\').checked)
				document.getElementById(\'ok\').removeAttribute(\'disabled\',\'\');
			else
				document.getElementById(\'ok\').setAttribute(\'disabled\',\'disabled\');
		}
	}

	function upBill()
	{
		document.getElementById(\'window_overlay_box\').style.display=\'block\';
	}

	function closePopup()
	{
		document.getElementById(\'window_overlay_box\').style.display=\'none\';
	}

	function sucessPay()
	{	
		var blank;
		var cardId = "'.$cardId.'";
		var value = document.getElementById("amount").value;
		var payway = document.getElementById("payway").value;
				
		amount = value*1;
		document.getElementById(\'window_overlay_box\').style.display=\'none\';

		if (amount)
		{
			var href = "https://www.moneta.ru/assistant.htm?MNT_ID=9019&MNT_AMOUNT=" + amount.toFixed(2) + "&MNT_SUBSCRIBER_ID=" + cardId + "&paymentSystem.unitId=" + payway + "&MNT_SUCCESS_URL=http%3A%2F%2Fmy18.ru%2Fpersonal-page%3Fmode%3Dsuccesspay&MNT_FAIL_URL=http%3A%2F%2Fmy18.ru%2Fpersonal-page%3Fmode%3Dfailpay";
			if(payway == 775856 || payway == 1015 || payway == 1020 || payway == 1017)
				window.open(href,\'_blank\');
			else
				window.location.href = href;
		}
	}
</script>';

	//	}
			
	}
echo '
</div>';

if (((int)$group == 0) || ((int)$group > 7))
{
	echo '<div class="u_zakaz">
	<a href="ordering-food">Заказать питание</a><a class="tooltip_zakaz"><span>Заказ питания осуществляется <strong> со вторника до полудня пятницы!</strong> По всем техническим неполадкам обращаться по номеру службы технической поддержки 38-60-60.</span></a><br/>';
} else {
	echo '<div class="u_zakaz">
<a href="zakaz-pitaniya">Заказать питание</a><a class="tooltip_zakaz"><span>Заказ питания осуществляется <strong> со вторника до полудня пятницы!</strong> По всем техническим неполадкам обращаться по номеру службы технической поддержки 38-60-60.</span></a><br/>';
}

$path_d=strrpos($dat, " ");
$date_z = substr ($dat, 0, $path_d); 
list($YY,$mm,$dd)=explode("-",$date_z);
$d_z=date('d.m.Y',mktime(0,0,0,$mm,$dd,$YY));
if ($d_z=='01.01.1970') $d_z='';
echo 'Дата последнего заказа:  '.$d_z.'<br/>
Следующая неделя: ';

if ($dat<$next_week && $dat>$current_first_day)
	echo '<span style="color: blue;">заказ принят!</span><br/>';
else 
	echo '<span style="color: red;">заказов нет!</span> <br/>';
	
echo '</div>';
	echo'<div class="u_dop">
			<a href="zakaz-pitaniya?id=7">Отчет о списании средств</a><br/>
			<a href="zakaz-pitaniya?id=15773">Мои заказы</a><br/>
<a href="zakaz-pitaniya?id=16334">Список терминалов</a><br/>

		</div>';

?>