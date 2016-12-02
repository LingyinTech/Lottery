<?php
/**
 * Created by PhpStorm.
 * User: xiehuanjin
 * Date: 2016/12/1
 * Time: 14:23
 */

namespace lingyin\lottery;


class Lottery
{
    /**
     * 抽奖标识
     *
     * @var string
     */
    private $lottery_id;

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

    public function __construct($lottery_id,$prize_list = [])
    {
        $this->lottery_id = $lottery_id;
        if(!empty($prize_list)){
            $this->setPrizeList($prize_list);
        }
    }

    /**
     * 设置奖品
     *
     * @param $prize_list array
     *      格式如下
     *      [
     *          ['id' => 1, 'name' => 'FREE GIFT', 'num' => 6, 'probabilities' => 0.325000, 'is_default' => 0]
     *      ]
     */
    public function setPrizeList($prize_list){
        foreach ($prize_list as $prize){
            $this->prize_list[$prize['id']] = $prize;
            if($prize['is_default'] == 1){
                $this->default_prize = $prize;
            }
        }
    }

    public function runLottery(){
        $data = ['code'=>0,'msg'=>'success'];

        if(empty($this->prize_list)){
            $data = ['code'=>1,'msg'=>'please set prize first.'];
            return $data;
        }

        $prize_probabilities = $this->getProbabilities($this->prize_list);
        $prize = $this->getRandProbabilities($prize_probabilities);
        if($prize == 0 && !empty($this->default_prize)){
            $prize = $this->default_prize['id'];
        }

        $data['prize'] = $prize;

        return $data;
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
     * @param  array $lotteryList 奖品信息
     * @return array     ["奖品keys"=>"概率值","coupons_100"=>"0"]
     */
    private function getProbabilities($lotteryList)
    {
        if (empty($lotteryList) || !is_array($lotteryList)) return [];

        return array_column($lotteryList, "probabilities", "id");
    }

}