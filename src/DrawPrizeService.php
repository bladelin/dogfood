<?php
/**
 * DrawPrizeService
 * 抽獎程式，項目用有限量之狀況
 */
class DrawPrizeService
{
    private $prize_arr = [];

    public function __construct($prize_array)
    {
        $this->prize_arr = $prize_array;
    }

    public function dump($arr)
    {
        echo '<pre>'.print_r($arr,TRUE).'</pre>';
    }

    /*概率算法
    proArr array(100,200,300，400)
    */
    private function getRand($proArr)
    {
        $result = '';
        $proSum = array_sum($proArr);
        if ($proSum < 1) {
            return false;
        }
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    /**
     * 列出中獎率
     */
    public function getStatInfo()
    {
    }

    /**
     * 列出獎項總數
     * @return [type]
     */
    public function getTotalPrize()
    {
        $sum = 0;
        foreach ($this->prize_arr as $key => $val) {
            $sum += $val['v'];
        }
        return $sum;
    }

    public function getPrize()
    {
        foreach ($this->prize_arr as $key => $val) {
            $arr[$key] = $val['v'];
        }

        $ridk = $this->getRand($arr);

        if ($ridk===false) {
            return false;
        }
        $res = $this->prize_arr[$ridk];

        if ($this->prize_arr[$ridk] > 0) {
            $this->prize_arr[$ridk]['v']--;
        }
        return $res;
    }

    public function getLog()
    {
        return $this->prize_arr;
    }

    public function setLog($log)
    {
        $this->prize_arr = $log;
    }

}
