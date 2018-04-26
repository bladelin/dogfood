<?php
/**
 * String Helper
 *
 * Sometimes origin php function can't handle all of situation especially chinese words utf8 or Big5...
 * just put any string method here for rainy day.
 *
 * @author Jun.lin <xuanjunlin@gmail.com>
 */

class StringHelper
{

    public static function proguardData($orderData = array(), $proguardFields)
    {
       foreach ($orderData as $key => $val) {
            if (isset($proguardFields[$key])) {
                $resData[$key] = StringHelper::utf8_protectedStr($val, $proguardFields[$key]);
            } else {
                $resData[$key] = $val;
            }
        }
        return $resData;
    }

    public static function proguardArray($orderDataArray = array(), $proguardFields)
    {
        $resDataArray = array();
        foreach ($orderDataArray as $id => $orderData) {
            $resDataArray[$id] = self::proguardData($orderData, $proguardFields);
        }
        return $resDataArray;
    }

    public static function utf8_protectedStr($str, $posArray=array(1,4,9,17), $commonSymbol = '*')
    {
        foreach($posArray as $pos)
            $str = StringHelper::utf8_substr_replace($str, $commonSymbol, $pos, 1);
        return $str;
    }

    public static function utf8_substr_replace($original, $replacement, $position, $length)
    {
        $startString = mb_substr($original, 0, $position, "UTF-8");
        $endString = mb_substr($original, $position + $length, mb_strlen($original), "UTF-8");
        $out = $startString . $replacement . $endString;
        return $out;
    }
}


