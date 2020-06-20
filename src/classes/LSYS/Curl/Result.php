<?php
namespace LSYS\Curl;
class Result{
    protected $_http_status;
    protected $_code;
    protected $_http_code;
    protected $_header;
    protected $_data;
    protected $_msg;
    public function __construct(bool $http_status,?int $code,?int $http_code,array $header=[],$data){
        $this->_http_status=boolval($http_status);
        $this->_code=$code;
        $this->_http_code=$http_code;
        $this->_header=$header;
        $this->_data=$data;
        if (!$this->getStatus()){
            $this->_msg=$data;
        }
    }
    /**
     * HTTP请求是否成功
     * @return bool
     */
    public function getHttpStatus():bool{
        return $this->_http_status;
    }
    /**
     * 处理结果状态
     * @return boolean
     */
    public function getStatus():bool{
        return $this->_http_status&&($this->_http_code>=200&&$this->_http_code<300);
    }
    /**
     * 错误码
     * @return int
     */
    public function getCode():?int{
        return $this->_code;
    }
    /**
     * HTTP码
     * @return int
     */
    public function getHttpCode():?int{
        return $this->_http_code;
    }
    /**
     * 数据
     * @return string
     */
    public function getData(){
        return $this->_data;
    }
    /**
     * 响应HEADER
     * @param string $key
     * @param mixed $default
     * @return string
     */
    public function getHeaders(?string $key=null,$default=null){
        if ($key===null)return $this->_header;
        if (!isset($this->_header[$key]))return $default; 
        return $this->_header[$key];
    }
    /**
     * 错误消息
     * @return string
     */
    public function getMsg():string{
        return $this->_msg;
    }
}