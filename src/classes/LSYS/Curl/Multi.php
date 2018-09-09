<?php
namespace LSYS\Curl;
use LSYS\Curl;
class Multi{
    protected $_curl=[];
    protected $_mh;
    /**
     * 批量发送http请求
     */
    public function __construct(){
        $this->_mh = curl_multi_init();
    }
    public function add(Curl $curl,callable $callback){
        $this->_curl[]=array($curl,$callback);
        return $this;
    }
    public function __destruct(){
        curl_multi_close($this->_mh);
    }
    public function exec(){
        $mh=$this->_mh;
        foreach ($this->_curl as list($curl,$callback)){
            $_ch=$curl->get_ch();
            curl_setopt_array($_ch, $curl->get_opt());
            curl_multi_add_handle($mh,$_ch);
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK)
        {
            while (curl_multi_exec($mh, $active) === CURLM_CALL_MULTI_PERFORM);
            if (curl_multi_select($mh) != -1)
            {
                do {
                    $mrc = curl_multi_exec($mh, $active);
//                     if ($mrc == CURLM_OK)
//                     {
//                         while($info = curl_multi_info_read($mh))
//                         {
//                             print_r($info);
//                         }
//                     }
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        foreach ($this->_curl as list($curl,$callback)){
            $ch=$curl->get_ch();
            if (curl_error($ch)){
                $result=Curl::parse_result($curl, false);
            }else{
                $result=Curl::parse_result($curl, curl_multi_getcontent($ch));
            }
            call_user_func($callback,$result);
            curl_multi_remove_handle($mh, $ch);
        }
    }
    /**
     * 批量请求静态方法
     * @param Curl[] $curl
     * @return Result[]
     */
    public static function run(array $curl){
        $mu=new static();
        $out=[];
        foreach ($curl as $k=>$v){
            assert($v instanceof Curl);
            $mu->add($v, function($result)use($k,&$out){
                $out[$k]=$result;
            });
        }
        $mu->exec();
        return $out;
    }
}