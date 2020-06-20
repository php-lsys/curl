<?php
namespace LSYS\Curl;
class ResultJSONP extends ResultJSON{
    protected $_name;
    public function __construct(bool $http_status,?int $code,?int $http_code,array $header,$data){
        $len=strpos($data, '(');
        $this->_name=substr($data, 0,$len);
        $data=substr($data, $len);
        $data=rtrim($data,');');
        parent::__construct($http_status,$code,$http_code,$header,$data);
    }
    /**
     * 数据转换为数据
     * @return string
     */
    public function funName():?string{
        return $this->_name;
    }
}