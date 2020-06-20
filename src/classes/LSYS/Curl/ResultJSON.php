<?php
namespace LSYS\Curl;
class ResultJSON extends Result{
    protected $_arr=[];
    protected $_parse=true;
    public function __construct(bool $http_status,?int $code,?int $http_code,array $header,$data){
        parent::__construct($http_status,$code,$http_code,$header,$data);
        if ($this->getHttpStatus()){
            $data=@json_decode($this->_data,true);
            if (!is_array($data)){
                $this->_parse=false;
                $this->_msg=json_last_error_msg();
                $this->_code=json_last_error();
            }else $this->_arr=$data;
        }
    }
    /**
     * 数据转换为数据
     * @return array
     */
    public function dataAsArray():array{
        return $this->_arr;
    }
    public function getStatus():bool{
        return $this->_parse&&parent::getStatus();
    }
}