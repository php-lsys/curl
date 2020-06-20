<?php
namespace LSYS\Curl;
class ResultXML extends Result{
    protected $_arr=[];
    protected $_parse=true;
    public function __construct(bool $http_status,?int $code,?int $http_code,array $header,$data){
        parent::__construct($http_status,$code,$http_code,$header,$data);
        if ($this->getHttpStatus()){
            $xml=@simplexml_load_string($this->_data);
            if ($xml===false){
                $this->_parse=false;
                $this->_msg='xml parse fail';
            }else $this->_arr=json_decode(json_encode($xml),true);
        }
    }
    /**
     * 数据转换为数据
     * @return array
     */
    public function dataAsArray():array {
        return $this->_arr;
    }
    public function getStatus():bool{
        return $this->_parse&&parent::getStatus();
    }
}