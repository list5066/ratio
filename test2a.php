<?php

$DOCUMENT_ROOT = "path/to/DOCUMENT_ROOT/";
error_reporting(E_ALL|E_STRICT); // не забываем про E_STRICT если php <= 5.4.0
ini_set('display_errors', TRUE);


require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);


//function xmp($var,$vd = false)
//{
//    echo '<pre>';
//    if ($vd)
//    {
//        var_dump($var);
//    }
//    else
//    {
//        print_r($var);
//    }
//    echo '</pre>';
//}

if (CModule::IncludeModule('iblock') && CModule::IncludeModule('catalog') && CModule::IncludeModule('sale'))
{
            $site_id = "s1";
    
            /*
             * Выбор отложенных товаров пользователей 
             */
    
            $arBasketItems = array();
            $dbBasketItems = CSaleBasket::GetList(
                    array(),
                    array(
                            "LID" => $site_id,
                            "DELAY" => "Y",
                            ">USER_ID" => 0,
                            "ORDER_ID" => "NULL"
                        ),
                    false,
                    false,
                    array()
                );
            while ($arItem = $dbBasketItems->Fetch())
            {
                $arBasketItems[$arItem["USER_ID"]][$arItem["PRODUCT_ID"]] += 1;
            }
            

            /* 
             * Фильтруем товары, Не должны быть в заказах пользователя за последний месяц
             */
            
            $month_date = mktime(date("H"),date("i"),date("s"), date("n")-1, date("j"), date("Y"));
            $month_date = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $month_date);
            //$month_date = "07.07.2015 08:38:47";
            //xmp($month_date);
                        
            foreach ($arBasketItems as $user_id => $delayed_items)
            {
                $order_items = array();
                $res = CSaleOrder::GetList(array(),array("USER_ID" => $user_id, ">=DATE_INSERT" => $month_date), false, false, array("ID", "DATE_INSERT", "USER_ID", "CANCELED", "STATUS_ID", "PRICE"));
                while ($item = $res->Fetch())
                {
                    $dbBasketItems = CSaleBasket::GetList(
                        array(),
                        array(
                                "ORDER_ID" => $item["ID"]
                            ),
                        false,
                        false,
                        array("PRODUCT_ID", "NAME", "QUANTITY", "PRICE")
                    );
                    while ($arItem = $dbBasketItems->Fetch())
                    {
                        $order_items[$arItem["PRODUCT_ID"]] = 1;
                        unset($arBasketItems[$user_id][$arItem["PRODUCT_ID"]]);
                    }
                }
            }
            
            
            $arSite = CSite::GetByID($site_id)->Fetch();
            $SITE_SERVER_NAME = $arSite["SERVER_NAME"];
            
            /*
             * Отправка емайл пользователям
             */
            foreach ($arBasketItems as $user_id => $delayed_items)
            {
                    $arUser = $USER->GetByID($user_id)->Fetch();
                
                    $arEventFields = array();
                    $arEventFields["NAME"] = $arUser["NAME"];
                    $arEventFields["LAST_NAME"] = $arUser["LAST_NAME"];
                    $arEventFields["EMAIL"] = $arUser["EMAIL"];
                    $arEventFields["PRODUCTS"] = "";
                    
                    $res = CIBlockElement::GetList(array(), array("ID" => array_keys($delayed_items)), false, false, array("ID", "IBLOCK_ID", "ACTIVE", "DETAIL_PAGE_URL", "NAME"));
                    while ($item = $res->GetNext())
                    {
                        if ($item["ACTIVE"] == "Y")
                        {
                            $arEventFields["PRODUCTS"] .= '<a href="http://'.$SITE_SERVER_NAME.''.$item["DETAIL_PAGE_URL"].'">'.$item["NAME"].'</a><br>'."\n";
                        }
                    }
                    
                    //xmp($arEventFields);
                    if ($arEventFields["PRODUCTS"] != "" && $arEventFields["EMAIL"] != "")
                    {
                        $eid = CEvent::Send("WISHLIST_DELAYED", $site_id, $arEventFields);
                        //xmp($eid);
                        
                        /* TODO: здесь добавить фиксацию где-либо, что пользователю отправлено 
                         * уведомление по этим товарам, в следующем запуске уже не отправлять.
                         */
                    }
            }
}











require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");


