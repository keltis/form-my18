<?php

include_once ('plugins/settings.php');
include_once ('function.php');



/* 
Начало обработки формы.
*/
if (isset($_POST['go'])) {
    //print_r($_POST);
    $breakfastData = array();
    $prepareData = array();
    for ($i=1; $i < 7; $i++) {
        // BREAKFAST

        if(isset($_POST['b_id_foodtake_'.$i])){
            $breakfastData[$i]['config']['id_food_intake'] = $_POST['b_id_foodtake_'.$i];
        }
        if(isset($_POST['b_id_complex_'.$i])){
            $breakfastData[$i]['config']['id_complex'] = $_POST['b_id_complex_'.$i];
        }
        if(isset($_POST['date_'.$i])){
            $breakfastData[$i]['config']['date'] = $_POST['date_'.$i];
        }
        if((isset($_POST['b_id_complex_'.$i]))and(isset($_POST['breakfast_day_'.$i]))){
            $breakfastData[$i]['config']['status'] = $_POST['breakfast_day_'.$i];
        } elseif(isset($_POST['b_id_complex_'.$i])) {
            $breakfastData[$i]['config']['status'] = 'off';
        }

        // CONFIG
        if(isset($_POST['id_foodtake_'.$i])){
            $prepareData[$i]['config']['id_food_intake'] = $_POST['id_foodtake_'.$i];
        }
        if(isset($_POST['id_complex_'.$i])){
            $prepareData[$i]['config']['id_complex'] = $_POST['id_complex_'.$i];
        }
        if(isset($_POST['date_'.$i])){
            $prepareData[$i]['config']['date'] = $_POST['date_'.$i];
        }
        // ITEMS
        if((isset($_POST['cold_dish_day_'.$i])) and ((int)$_POST['cold_dish_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['cold_dish_day_'.$i];
        }
        if((isset($_POST['entrees_day_'.$i])) && ((int)$_POST['entrees_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['entrees_day_'.$i];
        }
        if((isset($_POST['second_dish_day_'.$i])) and ((int)$_POST['second_dish_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['second_dish_day_'.$i];
        }
        if((isset($_POST['garnish_day_'.$i])) and ((int)$_POST['garnish_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['garnish_day_'.$i];
        }
        if((isset($_POST['second_dish_without_garnish_day_'.$i])) and ((int)$_POST['second_dish_without_garnish_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['second_dish_without_garnish_day_'.$i];
        }
        if((isset($_POST['bakery_day_'.$i])) and ((int)$_POST['bakery_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['bakery_day_'.$i];
        }
        if((isset($_POST['confectionery_day_'.$i])) and ((int)$_POST['confectionery_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['confectionery_day_'.$i];
        }
        if((isset($_POST['drinks_day_'.$i])) and ((int)$_POST['drinks_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['drinks_day_'.$i];
        }
        if((isset($_POST['fruit_day_'.$i])) and ((int)$_POST['fruit_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['fruit_day_'.$i];
        }
        if((isset($_POST['bread_day_'.$i])) and ((int)$_POST['bread_day_'.$i] > 0)){
            $prepareData[$i]['item'][] = $_POST['bread_day_'.$i];
        }
    }

    $order = '<?xml version="1.0" encoding="utf-8"?>
 <order>';
 foreach ($breakfastData as  $day) {
     if($day['config']['status'] == 'on')
     {
        $order .= '<item date="'.$day['config']['date'].'" id_food_intake="'.$day['config']['id_food_intake'].'" id_complex="'.$day['config']['id_complex'].'">';
            $order .= '</item>';
     } else {
        $order .= '<item date="'.$day['config']['date'].'" id_food_intake="'.$day['config']['id_food_intake'].'" id_complex="0">';
            $order .= '</item>';
     }
 }
    foreach ($prepareData as $day) {
        if(isset($day['item'])){
            $order .= '<item date="'.$day['config']['date'].'" id_food_intake="'.$day['config']['id_food_intake'].'" id_complex="'.$day['config']['id_complex'].'">';
            foreach ($day['item'] as $item) {
                $order .= '<subitem id="'.$item.'" qty="1"/>';
            }
        $order .= '</item>';
        } else {
            $order .= '<item date="'.$day['config']['date'].'" id_food_intake="'.$day['config']['id_food_intake'].'" id_complex="0">';
            $order .= '</item>';
        }
    }

    
 $order .= '</order>';
    //echo $order;
    $order = base64_encode($order);
    $orde = efapi("/dh_order?uid=$card_num&password=123456&order=".$order); 
    $ore = simplexml_load_string($orde);
    $dates = date("Y-m-d H:i:s");
    if ($ore->result=='ok') 
    {
        Echo '<div class="success"><p>Ваш заказ <b>принят</b></p></div>';
        $db->setQuery("INSERT INTO food_log (event_date, message, parameters, program_point, user_id) VALUES ('".$dates."', 'заказ успешно принят', '".$order."', '".$_SERVER['HTTP_USER_AGENT']."', '".$uid."')");
        $db->query();
    } else {
        if ($ore->description == 'Insufficient funds'){
            echo '<div class="error"><p>Ваш заказ <b>не принят</b><br>На вашем счету недостаточно денег.</p></div>';
            $db->setQuery("INSERT INTO food_log (event_date, message, parameters, program_point, user_id) VALUES ('".$dates."', 'заказ не принят: недостаточно денег".$ore->description."', '".$order."', '".$_SERVER['HTTP_USER_AGENT']."', '".$uid."')");
        $db->query();
        } else {
            echo '<div class="error"><p>Ваш заказ <b>не принят</b><br>Неизвестная ошибка, обратитесь в службу ИКТ.</p></div>';
            $db->setQuery("INSERT INTO food_log (event_date, message, parameters, program_point, user_id) VALUES ('".$dates."', 'заказ не принят: недостаточно денег".$ore->description."', '".$order."', '".$_SERVER['HTTP_USER_AGENT']."', '".$uid."')");
        $db->query();
        }
        
    }
}
/*
Конец обработки формы.
*/

$ef = efapi("/dh_complexes?uid=$card_num&password=$api_pass"); // запрашиваем меню
$file = $ef;
$xml = simplexml_load_string($file);



//print_r($xml);
$day = 0;
foreach ($xml->date_complexes as $date_complexes) {
    foreach ($date_complexes->food_intake as $food_intake) {
        if ($food_intake['name'] == 'Завтрак')
        {
            $breakfast_complex[$day]['config']['id'] = $food_intake->attributes()->id;
            $breakfast_complex[$day]['config']['id_complex'] = $food_intake->complex->attributes()->id;
            $breakfast_complex[$day]['config']['ordered'] = $food_intake->complex->attributes()->ordered;
            $breakfast_complex[$day]['config']['price'] = $food_intake->complex->attributes()->price;
            $breakfast_complex[$day]['config']['date'] = $date_complexes->attributes()->date;
            $breakfast_complex[$day]['config']['week_day'] = $date_complexes->attributes()->week_day;
            $counter = 0;
            foreach ($food_intake->complex->item as $item) {
                $breakfast_complex[$day]['item'][$counter]['name'] = (string)$item->attributes()->name;
                $counter++;
            }
        }
        if (isset($food_intake->extra_complex)) {
            $extra_complex[$day]['config']['id'] = $food_intake->attributes()->id;
            $extra_complex[$day]['config']['id_complex'] = $food_intake->extra_complex->attributes()->id;
            $extra_complex[$day]['config']['sum'] = $food_intake->extra_complex->attributes()->sum;

            $extra_complex[$day]['config']['ordered'] = $food_intake->attributes()->ordered;
            $extra_complex[$day]['config']['order'] = $food_intake->attributes()->order;
            $extra_complex[$day]['config']['canceled'] = $food_intake->attributes()->canceled;
            $extra_complex[$day]['config']['date'] = $date_complexes->attributes()->date;
            $extra_complex[$day]['config']['week_day'] = $date_complexes->attributes()->week_day;
            $counter = 0;
            foreach ($food_intake->extra_complex->item as $item) {
                $extra_complex[$day]['item'][$counter]['id'] = $item->attributes()->id;
                $extra_complex[$day]['item'][$counter]['price'] = $item->attributes()->price;
                $extra_complex[$day]['item'][$counter]['name'] = $item->attributes()->name;
                $extra_complex[$day]['item'][$counter]['sel_qty'] = $item->attributes()->sel_qty;
                $extra_complex[$day]['item'][$counter]['category'] = $item->attributes()->category;
                $extra_complex[$day]['item'][$counter]['unit'] = $item->attributes()->unit;
                $counter++;
            }
        }
    }

    $day++;
}
//print_r($breakfast_complex);
$categoryLists = array();
$day = 0;
foreach ($extra_complex as $complex) {
    
    $counter = 0;
    foreach ($complex['item'] as $item) {
        if (in_array((string)$item['category'], $categoryLists[(int)$complex['config']['week_day']]))
        {
            
        } else {
            $categoryLists[(int)$complex['config']['week_day']][$counter] = (string)$item['category'];
            $counter++;
        }
    }
}
$countCategories = 0;
//print_r($categoryLists);
?>


<html>
    <head>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <link href="plugins/my_food/js/jquery.formstyler.css" rel="stylesheet" />
        <script src="plugins/my_food/js/jquery.formstyler.min.js"></script>
        <script src="plugins/my_food/js/jquery.textchange.min.js"></script>
        <meta charset="utf-8">
        <style>
            .food_container{
                    width: 97%;
                padding: 10px;
                background: #cdebf0;
                border-radius: 10px;
                box-shadow: 2px 2px 10px rgba(0,0,0,0.5);
                font: 14px Tahoma;
                margin-bottom: 30px;
            }
            .food_container select {
                max-width: 20%; /* Ширина списка в пикселах */
            }
            .breakfast {
                background: whitesmoke;
                border-radius: 3px;
                padding: 20px 5px;
                margin-top: 15px;
            }
            .b_header {
                position: relative;
                top: -15px;
                padding: 3px;
                border-bottom: 1px solid grey;
            }
            .b_header_check{
                position: relative;
                float: right;
                padding: 0px 0 0px 5px;
                margin: -2px;
            }
            .day_1 {background: #cdebf0;}
            .day_2 {background: #ccddff;}
            .day_3 {background: #d5ccff;}
            .day_4 {background: #e5ccff;}
            .day_5 {background: #ffccf6;}
            .day_6 {background: #ffccd4;}
            .day_7 {background: #ffe5cc;}

            .day-name{
                font-size: 26px;
            }
            .allSumWeek{
                text-align: center;
                font-size: 24px;
            }
            td {
                max-width: 200px;
            }
            .info, .success, .warning, .error, .validation {
                border: 2px solid;
                border-radius: 3px;
                margin: 10px auto;
                padding: 15px 10px 15px 0px;
                width: 97%;
                text-align: center;
                font: 16px Tahoma;
            }
            .info {
                color: #00529B;
                background-color: #BDE5F8;
            }
            .success {
                color: #4F8A10;
                background-color: #DFF2BF;
            }
            .error {
                color: #D8000C;
                background-color: #FFBABA;
            }
        </style>
        <script>
            (function ($) {
                $(function () {
                    $('select').styler({
                        selectSearch: true
                    });
                });
            })(jQuery);
        </script>
        <script>
            function resum(dayId, oldSum, newSum) {
                currentSum = $("#sum_order_day_" + dayId).text();
                console.log(currentSum);
                newText = (currentSum - oldSum) + newSum;
                console.log(newText);
                $("#sum_order_day_" + dayId).text(newText.toFixed(2));
                console.log($("#sum_order_day_" + dayId).text());
                resumAllWeek();
            }
            function resumAllWeek(){
                sum = 0;
                for (var i = 0; i < 7; i++) {
                    if ($("span").is("#sum_order_day_" + i))
                    {
                        sum = sum + Number($("#sum_order_day_" + i).text());
                    }
                }
                $("#allSum").text(sum.toFixed(2));

            }
            function checkAll()
            {
                for (var i = 0; i < 7; i++) {
                    console.log($("#dont_eat_garnish_day_" + i).text());
                    console.log($("#garnish_day_" + i + " option:selected").text());
                    console.log($("#dont_eat_second_dish_day_" + i).text());
                    console.log($("#second_dish_day_" + i + " option:selected").text());
                    if ($("div").is("#food_container_day_" + i))
                    {

                        if (($("#dont_eat_garnish_day_" + i).text() === $("#garnish_day_" + i + " option:selected").text()) && ($("#dont_eat_second_dish_day_" + i).text() !== $("#second_dish_day_" + i + " option:selected").text()))
                        {
                            console.log('NOT ALL GOOD');
                            alert('Вы не выбрали гарнир, выбрав второе блюдо');
                            return false;
                        } else {
                            console.log('ALL GOOD');
                        }
                    }
                }
                return true;

            }
            function day_name(day_num)
            {
                var arr_week_days = [];
                arr_week_days[0] = 'воскресенье'; // sunday
                arr_week_days[1] = 'понедельник'; // monday
                arr_week_days[2] = 'вторник';
                arr_week_days[3] = 'среду';
                arr_week_days[4] = 'четверг';
                arr_week_days[5] = 'пятницу';
                arr_week_days[6] = 'субботу';

                return arr_week_days[day_num];
            }
            $(document).ready(function () {
                document.getElementById('header').style.backgroundImage='url(http://my18.ru/templates/baum_lyceum_new/images/shapki/Sayt_11.jpg)';
                oldCostDish = new Array();
                costsDish = new Array();
                priceBreakfast = new Array();
<?php
foreach ($extra_complex as $complex) {
    foreach ($complex['item'] as $item) {
        ?>
                        costsDish[<?php echo $item['id'] ?>] = <?php echo $item['price'] ?>;
        <?php
    }
}
?>
<?php
foreach ($breakfast_complex as $complex) {
        ?>
                        priceBreakfast[<?php echo $complex['config']['week_day'] ?>] = <?php echo $complex['config']['price'] ?>;
        <?php
}
?>
    resumAllWeek();
                $('select').each(function (i, elem) {
                    qtyId = "#qty_" + this.id;
                    clickId = this.id;
                    if ($(qtyId).val() > 0)
                    {
                        qtyId = "#qty_" + clickId;
                        dayId = Number(clickId.match(/\d+/));
                        dishSelected = $("#" + clickId + " option:selected").text();
                        idDish = $("#" + clickId + " option:selected").val();
                        costDish = costsDish[idDish];
                        countDish = $(qtyId).val();

                        oldCostDish[clickId] = countDish * costDish;
                        if ($("li").is("#ordered_" + clickId)) {
                            $("#ordered_" + clickId).text(dishSelected + ' x ' + countDish + ' = ' + countDish * costDish + ' руб.');
                        } else {
                            $("#order_day_" + dayId).append('<li id="ordered_' + clickId + '" >' + dishSelected + ' x ' + countDish + ' = ' + countDish * costDish + ' руб.</li>');
                        }
                    } else {
                        dayId = Number(clickId.match(/\d+/));
                        $("#confectionery_day_" + dayId).attr("disabled","disabled").trigger('refresh');
                        $("#bakery_day_" + dayId).attr("disabled","disabled").trigger('refresh');
                    }
                });
                $('input:checkbox').each(function (i, elem) {
                    clickId = this.id;
                    dayId = Number(clickId.match(/\d+/));
                    priceBreakfast = Number($("#price_" + clickId).text());
                    console.log(clickId);
                    console.log(dayId);
                    if($('#'+clickId).is(':checked')){
                        $("#order_day_" + dayId).append('<li id="ordered_' + clickId + '" >Завтрак на ' + day_name(dayId) + ' x ' + '1' + ' = ' + priceBreakfast + ' руб.</li>');
                            resum(dayId, 0, priceBreakfast);
                    } else {
                        if ($("li").is("#ordered_" + clickId)) {
                            $("#ordered_" + clickId).remove();
                            resum(dayId, priceBreakfast,0);
                        }
                    }
                });
                $('input:checkbox').change(function () {
                    clickId = this.id;
                    dayId = Number(clickId.match(/\d+/));
                    priceBreakfast = Number($("#price_" + clickId).text());
                    console.log(clickId);
                    console.log(dayId);
                    if($('#'+clickId).is(':checked')){
                        $("#order_day_" + dayId).append('<li id="ordered_' + clickId + '" >Завтрак на ' + day_name(dayId) + ' x ' + '1' + ' = ' + priceBreakfast + ' руб.</li>');
                            resum(dayId, 0, priceBreakfast);
                    } else {
                        if ($("li").is("#ordered_" + clickId)) {
                            $("#ordered_" + clickId).remove();
                            resum(dayId, priceBreakfast,0);
                        }
                    }

                    //resum(dayId, 0, priceBreakfast);

                    
                });
                $('select').change(function () {
                    clickId = this.id;
                    qtyId = "#qty_" + clickId;
                    dayId = Number(clickId.match(/\d+/));
                    dishSelected = $("#" + clickId + " option:selected").text();
                    idDish = $("#" + clickId + " option:selected").val();
                    if (costsDish[idDish] === undefined)
                    {
                        costsDish[idDish] = 0;
                    }
                    costDish = costsDish[idDish];
                    $(qtyId).val(1);
                    countDish = $(qtyId).val();
                    console.log(clickId);
                    console.log($("#garnish_day_" + dayId + " :nth-child(2)"));
                    newSum = countDish * costDish;
                    if ($("#dont_eat_" + clickId).text() === $("#" + clickId + " option:selected").text())
                    {
                        $("#ordered_" + clickId).remove();
                    } else {
                        if ($("li").is("#ordered_" + clickId)) {
                            $("#ordered_" + clickId).text(dishSelected + ' x ' + countDish + ' = ' + countDish * costDish + ' руб.');
                        } else {
                            $("#order_day_" + dayId).append('<li id="ordered_' + clickId + '" >' + dishSelected + ' x ' + countDish + ' = ' + countDish * costDish + ' руб.</li>');
                        }
                    }
                    if (oldCostDish[clickId] === undefined)
                    {
                        oldCostDish[clickId] = 0;
                    }
                    resum(dayId, oldCostDish[clickId], newSum);
                    oldCostDish[clickId] = newSum;
                    if (clickId === ("second_dish_day_" + dayId))
                    {
                        if ($("#dont_eat_" + clickId).text() !== $("#" + clickId + " option:selected").text()) {
                            $("#garnish_day_" + dayId).prop('disabled', false).trigger('refresh');
                            $("#garnish_day_" + dayId + " :nth-child(2)").attr('selected', 'true').trigger('refresh');
                            $("#garnish_day_" + dayId).change();
                        } 
                        
                    }

                        if (($("#dont_eat_entrees_day_" + dayId).text() !== $("#entrees_day_" + dayId + " option:selected").text()) || ($("#dont_eat_second_dish_day_" + dayId).text() !== $("#second_dish_day_" + dayId + " option:selected").text())) {
                                $("#confectionery_day_" + dayId).attr("disabled",false).trigger('refresh');
                                $("#bakery_day_" + dayId).attr("disabled",false).trigger('refresh');
                        } else {
                            if (oldCostDish["confectionery_day_"+dayId] === undefined)
                           { 
                                oldCostDish["confectionery_day_"+dayId] = 0;
                            }
                            if (oldCostDish["bakery_day_"+dayId] === undefined)
                           { 
                                oldCostDish["bakery_day_"+dayId] = 0;
                            }
                                $("#confectionery_day_" + dayId).attr("disabled","disabled").trigger('refresh');
                                $("#dont_eat_confectionery_day_" + dayId).attr('selected', 'true').trigger('refresh');
                                 $("#ordered_confectionery_day_" + dayId).remove();
                                 console.log('пересчет Кондитерские изделия предыдущая цена: '+ oldCostDish["confectionery_day_"+dayId] + ' Новая цена: 0');
                                 resum(dayId, oldCostDish["confectionery_day_"+dayId], 0);
                                 oldCostDish["confectionery_day_"+dayId] = 0;
                                $("#bakery_day_" + dayId).attr("disabled","disabled").trigger('refresh');
                                $("#dont_eat_bakery_day_" + dayId).attr('selected', 'true').trigger('refresh');
                                 $("#ordered_bakery_day_" + dayId).remove();
                                 console.log('пересчет Выпечка предыдущая цена: '+ oldCostDish["bakery_day_"+dayId] + ' Новая цена: 0');
                                 resum(dayId, oldCostDish["bakery_day_"+dayId], 0);
                                 oldCostDish["bakery_day_"+dayId] = 0;
                    }


                    

                });

                $('input:number').change(function () {
                    qtyId = this.id;
                    clickId = qtyId.replace("qty_", "")
                    dayId = Number(clickId.match(/\d+/));
                    dishSelected = $("#" + clickId + " option:selected").text();
                    if ($("#" + clickId + " option:selected").text() === $("#" + clickId + " option:disabled").text())
                    {
                        $("#" + qtyId).val(0);
                    } else {
                        idDish = $("#" + clickId + " option:selected").val();
                        costDish = costsDish[idDish];
                        countDish = $("#" + qtyId).val();
                        if ($("li").is("#ordered_" + clickId)) {
                            $("#ordered_" + clickId).text(dishSelected + ' x ' + countDish + ' = ' + countDish * costDish + ' руб.');
                        } else {
                            $("#order_day_" + dayId).append('<li id="ordered_' + clickId + '" >' + dishSelected + ' x ' + countDish + ' = ' + countDish * costDish + ' руб.</li>');
                        }
                    }
                    resum(dayId);
                });
            });



        </script>

    </head>
    <body>
    
    <?php //print_r($extra_complex); ?>
    <?php if (count($extra_complex) > 0) {?>
        
    
        <form action="" method="POST" id="form-complex" onsubmit="return checkAll()">
            <?php foreach ($extra_complex as $complex) {
                $countCategories = 0;
            ?>
            <input name="day_<?php echo $complex['config']['week_day'] ?>" type="hidden" value="<?php echo $complex['config']['week_day'] ?>"></input>
            <input name="date_<?php echo $complex['config']['week_day'] ?>" type="hidden" value="<?php echo $complex['config']['date'] ?>"></input>
            <input name="id_complex_<?php echo $complex['config']['week_day'] ?>" type="hidden" value="<?php echo $complex['config']['id_complex'] ?>"></input>
            <input name="id_foodtake_<?php echo $complex['config']['week_day'] ?>" type="hidden" value="<?php echo $complex['config']['id'] ?>"></input>
                <div class="food_container day_<?php echo $complex['config']['week_day'] ?>" id="food_container_day_<?php echo $complex['config']['week_day'] ?>">
                    <table width="100%" style="text-align: center;">
                        <tbody>
                            <tr>
                                <td colspan="4" class="day-name">
                        <center>
                            <?php echo day_name((int) $complex['config']['week_day']); ?>
                        </center> 
                        </td>

                        </tr>
                        <tr >
                        <td rowspan="<?php echo round(count($categoryLists[(int)$complex['config']['week_day']])/2)*2; ?>" style="vertical-align: middle;">
                        <div class="breakfast">
                        <div class="b_header">
                        <strong>Заказать завтрак</strong>
                        <div class="b_header_check">
                        <input type="checkbox" <?php if((int)$breakfast_complex[0]['config']['ordered'] == 1) {echo 'checked';} ?> id="breakfast_day_<?php echo (int)$complex['config']['week_day']?>" name="breakfast_day_<?php echo (int)$complex['config']['week_day']?>">
                        </div>
                        </div>

                        <?php foreach ($breakfast_complex as $b_complex) {
                            if ($b_complex['config']['week_day']==$complex['config']['week_day']) {
                                ?>
                            <input name="b_id_complex_<?php echo $complex['config']['week_day'] ?>" type="hidden" value="<?php echo $b_complex['config']['id_complex'] ?>"></input>
                            <input name="b_id_foodtake_<?php echo $complex['config']['week_day'] ?>" type="hidden" value="<?php echo $b_complex['config']['id'] ?>"></input>
                        <?php
                                foreach ($b_complex['item'] as  $item) {
                                echo $item['name'].'<br>';
                            }
                            echo '<strong><span id="price_breakfast_day_'.(int)$complex['config']['week_day'].'">'.$b_complex['config']['price'].'</span> руб.</strong><br>';
                            }
                            
                        } ?>

                        </div>
                        </td>
                        </tr>
                        <tr>
                        <?php if (in_array('Холодные блюда', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Холодные блюда</strong><br>
                                <select class="width-200" id="cold_dish_day_<?php echo $complex['config']['week_day'] ?>" name="cold_dish_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_cold_dish_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать  холодное блюдо</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Холодные блюда') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?>><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_cold_dish_day_<?php echo $complex['config']['week_day'] ?>" name="qty_cold_dish_day_<?php echo $complex['config']['week_day'] ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>
                            </td>
                            <?php } ?>
                            <?php if (in_array('Первые блюда', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Первые блюда</strong><br>
                                <select class="width-200" id="entrees_day_<?php echo $complex['config']['week_day']; ?>" name="entrees_day_<?php echo $complex['config']['week_day']; ?>" size="4">
                                    <option selected id="dont_eat_entrees_day_<?php echo $complex['config']['week_day']; ?>">Не заказывать первое блюдо</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Первые блюда') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> >
                                                <?php echo $item['name'] ?> (<span><?php echo $item['price']; ?></span> руб.)  
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_entrees_day_<?php echo $complex['config']['week_day']; ?>" name="qty_entrees_day_<?php echo $complex['config']['week_day']; ?>" type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '" ';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>

                            </td>
                            <?php } ?>
                            <?php if (in_array('Вторые блюда', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Вторые блюда</strong><br>
                                <select class="width-200" id="second_dish_day_<?php echo $complex['config']['week_day'] ?>" name="second_dish_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_second_dish_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать  второе блюдо</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Вторые блюда') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                $qty = $item['sel_qty'];
                                                echo 'selected';
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_second_dish_day_<?php echo $complex['config']['week_day']; ?>" name="qty_second_dish_day_<?php echo $complex['config']['week_day']; ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>

                            </td>
                            <?php } ?>
                            <?php if (in_array('Гарниры', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Гарниры</strong><br>
                                <select class="width-200" id="garnish_day_<?php echo $complex['config']['week_day'] ?>" name="garnish_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_garnish_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать  гарнир</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Гарниры') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_garnish_day_<?php echo $complex['config']['week_day'] ?>" name="qty_garnish_day_<?php echo $complex['config']['week_day'] ?>" type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>
                            </td>
                            <?php } ?>
                            <?php if (in_array('Вторые блюда без гарнира', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Вторые блюда без гарнира</strong><br>
                                <select class="width-200" id="second_dish_without_garnish_day_<?php echo $complex['config']['week_day'] ?>" name="second_dish_without_garnish_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_second_dish_without_garnish_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать второе блюдо без гарнира</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Вторые блюда без гарнира') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                $qty = $item['sel_qty'];
                                                echo 'selected';
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_second_dish_without_garnish_day_<?php echo $complex['config']['week_day']; ?>" name="qty_second_dish_without_garnish_day_<?php echo $complex['config']['week_day']; ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>

                            </td>
                            <?php } ?>
                            <?php if (in_array('Выпечка', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Выпечка</strong><br>
                                <select class="width-200" id="bakery_day_<?php echo $complex['config']['week_day']; ?>" name="bakery_day_<?php echo $complex['config']['week_day']; ?>" size="4" >
                                    <option selected id="dont_eat_bakery_day_<?php echo $complex['config']['week_day']; ?>">Не заказывать  выпечку</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Выпечка') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> >
                                                <?php echo $item['name'] ?> (<?php echo $item['price']; ?> руб.)  
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_bakery_day_<?php echo $complex['config']['week_day']; ?>" name="qty_bakery_day_<?php echo $complex['config']['week_day']; ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>

                            </td>
                            <?php } ?>
                            <?php if (in_array('Кондитерские изделия', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Кондитерские изделия</strong><br>
                                <select class="width-200" id="confectionery_day_<?php echo $complex['config']['week_day'] ?>" name="confectionery_day_<?php echo $complex['config']['week_day'] ?>" size="4" >
                                    <option selected id="dont_eat_confectionery_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать  кондитерское изделие</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Кондитерские изделия') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)  </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_confectionery_day_<?php echo $complex['config']['week_day'] ?>" name="qty_confectionery_day_<?php echo $complex['config']['week_day'] ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>

                            </td>
                            <?php } ?>
                            <?php if (in_array('Напитки', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Напитки</strong><br>
                                <select class="width-200" id="drinks_day_<?php echo $complex['config']['week_day'] ?>" name="drinks_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_drinks_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать  напиток</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Напитки') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_drinks_day_<?php echo $complex['config']['week_day'] ?>" name="qty_drinks_day_<?php echo $complex['config']['week_day'] ?>" type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>
                            </td>
                            <?php } ?>
                            <?php if (in_array('Фрукты', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Фрукты</strong><br>
                                <select class="width-200" id="fruit_day_<?php echo $complex['config']['week_day'] ?>" name="fruit_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_fruit_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать  фрукт</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Фрукты') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_fruit_day_<?php echo $complex['config']['week_day'] ?>" name="qty_fruit_day_<?php echo $complex['config']['week_day'] ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>
                            </td>
                            <?php } ?>
                            <?php if (in_array('Хлеб', $categoryLists[(int)$complex['config']['week_day']]))
                             { 
                            if (($countCategories % 2 == 0) and ($countCategories != 0)) {
                                echo '</tr>
                        <tr>
                            <td colspan="4">
                                <div style="padding-top: 20px;"></div>
                            </td>
                        </tr>
                        <tr>'; 
                            }$countCategories++;
                            ?>
                            <td>
                                <strong>Хлеб</strong><br>
                                <select class="width-200" id="bread_day_<?php echo $complex['config']['week_day'] ?>" name="bread_day_<?php echo $complex['config']['week_day'] ?>" size="4">
                                    <option selected id="dont_eat_bread_day_<?php echo $complex['config']['week_day'] ?>">Не заказывать хлеб</option>
                                    <?php
                                    foreach ($complex['item'] as $item) {
                                        if ((string) $item['category'] == 'Хлеб') {
                                            ?>

                                            <option value="<?php echo $item['id'] ?>"  <?php
                                            if ($item['sel_qty'] > 0) {
                                                echo 'selected';
                                                $qty = $item['sel_qty'];
                                            }
                                            ?> ><?php echo $item['name'] ?> (<?php echo $item['price'] ?> руб.)</option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                </select>
                                <div style="padding-top: 15px; display:none">
                                    Количество:<br>
                                    <input id="qty_bread_day_<?php echo $complex['config']['week_day'] ?>" name="qty_bread_day_<?php echo $complex['config']['week_day'] ?>"  type="number" min="0" <?php
                                    if ($qty > 0) {
                                        echo 'value="' . $qty . '"';
                                        $qty = 0;
                                    } else {
                                        echo 'value="0" ';
                                    }
                                    ?>>  
                                </div>
                            </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td colspan="4" style="padding-top: 10px; text-align:right;">
                                <p style=""> Ваш заказ:</p>

                                <hr>  
                                <ul id="order_day_<?php echo $complex['config']['week_day'] ?>" style="list-style:none;">

                                </ul>
                                <p>Итого: <span id="sum_order_day_<?php echo $complex['config']['week_day'] ?>"><?php if (empty($complex['config']['sum'])) {echo '0.00';} else { echo $complex['config']['sum']; }?></span> руб.</p>
                            </td>


                        </tr>

                        </tbody></table>
                </div>
            <?php }
            ?>
            <div class="allSumWeek">
            Общая сумма вашего заказа: <span id="allSum">XXX</span> руб.<br>
            <input class="styler" name="go"  type="submit" value="Заказать" style="text-align:center; width:200px; margin-bottom: 10px;">
            </div>
            
        </form>
<?php
} else {?>
<div class="info"><p>Заказ питания в настоящее время не производится. <br>Заказ доступен с <b>вечера вторника</b> до <b>полудня пятницы</b>.</p></div>

<?php }
?>