<?php
namespace LSYS\Curl;
class ResultJSONP extends ResultJSON{
    protected $_name;
    public function __construct($http_status,$code,$http_code,$header,$data){
        $len=strpos($data, '(');
        $this->_name=substr($data, 0,$len);
        $data=substr($data, $len);
        $data=rtrim($data,');');
        parent::__construct($http_status,$code,$http_code,$header,$data);
    }
    /**
     * 数据转换为数据
     * @return array
     */
    public function fun_name(){
        return $this->_name;
    }
}