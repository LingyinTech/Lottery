<?php
/**
 * Created by PhpStorm.
 * User: xiehuanjin
 * Date: 2016/12/1
 * Time: 14:23
 */

namespace lingyin\lottery;

/**
 * Class Lottery
 * @package lingyin\lottery
 *
 * 抽奖核心类
 *
 * 只是实现抽奖动作，不包含前置较验，不包含抽后处理
 */
abstract class Lottery
{
    /**
     * 奖品信息列表
     *
     * @var array
     */
    private $prize_list = [];

    /**
     * 默认奖品
     *
     * @var array
     */
    private $default_prize;

    public function __construct($prize_list = [])
    {
        if (!empty($prize_list)) {
            $this->setPrizeList($prize_list);
        }
    }

    /**
     * 设置奖品
     *
     * @param $prize_list array
     *      格式如下
     *      [
     *          ['index' => 1, 'name' => 'FREE GIFT', 'num' => 6, 'probabilities' => 0.325000, 'is_default' => 0]
     *      ]
     */
    public function setPrizeList($prize_list)
    {
        foreach ($prize_list as $prize) {
            $this->prize_list[$prize['index']] = $prize;
            if ($prize['is_default'] == 1) {
                $this->default_prize = $prize;
            }
        }
    }

    public function runLottery()
    {
        $data = ['code' => 0, 'msg' => 'success'];
        if ($check = $this->beforeLottery() !== true) {
            return $check;
        }

        if (empty($this->prize_list)) {
            $data = ['code' => 1, 'msg' => 'please set prize first.'];
            return $data;
        }

        $prize_probabilities = $this->getProbabilities();
        $prize = $this->getRandProbabilities($prize_probabilities);
        if ($prize == 0 && !empty($this->default_prize)) {
            $data['msg'] = '未中奖，取默认奖品' . $this->default_prize['index'];
            $prize = $this->default_prize['id'];
        }

        $data['prize'] = $prize;
        if ($check = $this->afterLottery($data) !== true) {
            return $check;
        }

        return $data;
    }

    /**
     * 抽奖前较验，根据需要可实现该方法
     *
     * @return mixed
     */
    public function beforeLottery()
    {
        return true;
    }

    /**
     * 抽奖后较验，根据需要可实现该方法
     *
     * @param $data
     * @return mixed
     */
    public function afterLottery($data)
    {

        return true;
    }

    /**
     * 根据概率,随机取值
     *
     * @param $proArr
     * @return int
     */
    private function getRandProbabilities($proArr)
    {
        $result = 0;
        //概率数组的总概率精度
        $randNum = mt_rand(0, 100000000) / 1000000;
        $count = 0.0000001;
        foreach ($proArr as $key => $proCur) {
            if ($proCur == 0) {
                //概率为0的商品不可得
                continue;
            }
            $nowcount = $count + $proCur;
            if ($randNum >= $count && $randNum < $nowcount) {
                $result = $key;
                break;
            }
            $count = $nowcount;
        }
        return $result;
    }

    /**
     * 提取所有相应的概率
     * @return array     ["奖品keys"=>"概率值","coupons_100"=>"0"]
     */
    private function getProbabilities()
    {
        if (empty($this->prize_list) || !is_array($this->prize_list)) return [];
        return array_column($this->prize_list, "probabilities", "index");
    }

}