<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS;
use LSYS\Curl\Result;
use LSYS\Curl\ResultJSONP;
use LSYS\Curl\ResultJSON;
use LSYS\Curl\ResultXML;
use LSYS\Curl\ZipDecode;
class Curl{
    //请求方式
    const METHOD_GET=1;
    const METHOD_POST=2;
    const METHOD_PUT=3;
    const METHOD_DELETE=4;
    //数据类型
    const DATA_AUTO=0;//根据method选择
    const DATA_GET=1;
    const DATA_POST=2;
    //数据是否压缩
    const ACCEPT_ENCODING_AUTO=1;
    const ACCEPT_ENCODING_NO=2;
    //接受数据类型
    const RESULT_FORMAT_DEFAULT=0;//返回result对象
    const RESULT_FORMAT_JSON=1;//返回resultjson对象
    const RESULT_FORMAT_JSONP=2;//返回resultjsonp对象
    const RESULT_FORMAT_XML=3;//返回resultxml对象
    public static $opts=array(
        CURLOPT_USERAGENT      => 'LSYS Client/1.0 (compatible; Bulid From:shan.liu@msn.com)',
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
    );
    protected $_opts=array();
    protected $_header=false;
    protected $_format=self::RESULT_FORMAT_DEFAULT;
    protected $_accept_encoding=self::ACCEPT_ENCODING_AUTO;
    protected $_ch;
    /**
     * 对CURL扩展进行封装
     * 方便进行统一的调试输出等
     * @param string $url
     */
    public function __construct($url=null,$method=null,$format=null){
        $this->_opts = self::$opts;
        $this->setReferer();
        $url!==null&&$this->setUrl($url);
        $method!=null&&$this->setMethod($method);
        $format!=null&&$this->setResultFormat($format);
    }
    /**
     * @param int $status
     * @return $this
     */
    public function setResultFormat($format){
        $this->_format=$format;
        return $this;
    }
    /**
     * @param int $status
     * @return $this
     */
    public function setResultHeader($status){
        $this->_header=boolval($status);
        return $this;
    }
    /**
     * @param string $useragent
     * @return $this
     */
    public function setUseragent($useragent=null){
        if ($useragent===null)unset($this->_opts[CURLOPT_USERAGENT]);
        else $this->_opts[CURLOPT_USERAGENT]=$useragent;
        return $this;
    }
    /**
     * @param int $status
     * @return $this
     */
    public function setTimeout($time){
        if ($time<=0)return $this;
        $this->_opts[CURLOPT_CONNECTTIMEOUT]=$time;
        $this->_opts[CURLOPT_TIMEOUT]=$time;
        return $this;
    }
    /**
     * @param bool $status
     * @return $this
     */
    public function setAcceptEncoding($accept_encoding=true){
        $this->_accept_encoding=$accept_encoding;
        return $this;
    }
    /**
     * @param bool $status
     * @return $this
     */
    public function setReferer($status=true){
        if($status){
            $this->_opts[CURLOPT_REFERER]=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
        }else unset($this->_opts[CURLOPT_REFERER]);
        return $this;
    }
    /**
     * 设置CURL OPTION
     * @param int $option
     * @param mixed $value
     * @return $this
     */
    public function setOpt($option,$value=null){
        if (is_array($option)){
            foreach ($option as $k=>$v){
                $this->_opts[$k]=$v;
            }
        }else{
            $this->_opts[$option]=$value;
        }
        return $this;
    }
    /**
     * 设置CURL URL
     * @param int $option
     * @param mixed $value
     * @return $this
     */
    public function setUrl($url){
        $this->_opts[CURLOPT_URL]=$url;
        return $this;
    }
    /**
     * 获取 CURL OPTION
     * @param int $option
     * @param mixed $value
     * @return $this
     */
    public function getOpt($option=null){
        if ($this->_accept_encoding==self::ACCEPT_ENCODING_AUTO){
            $this->_opts[CURLOPT_HTTPHEADER][]='Accept-Encoding:gzip';
        }
        switch ($this->_format){
            case self::RESULT_FORMAT_JSONP:
                $this->_opts[CURLOPT_HTTPHEADER][]='Accept:application/javascript';
            break;
            case self::RESULT_FORMAT_JSON:
                $this->_opts[CURLOPT_HTTPHEADER][]='Accept:application/json';
            break;
            case self::RESULT_FORMAT_XML:
                $this->_opts[CURLOPT_HTTPHEADER][]='Accept:text/xml,application/xml';
            break;
        }
        if (isset($this->_opts[CURLOPT_HTTPHEADER])){
            $this->_opts[CURLOPT_HTTPHEADER]=array_unique($this->_opts[CURLOPT_HTTPHEADER]);
        }
        $this->_opts[CURLOPT_HEADER]=$this->_header;
        $this->_opts[CURLOPT_RETURNTRANSFER]=true;
        if ($option===null) return $this->_opts;
        if (!isset($this->_opts[$option]))return NULL;
        return $this->_opts[$option];
    }
    /**
     * 设置CA证书
     * @param string $ca_path
     * @return $this
     */
    public function setCa($ca_path){
        if (!is_file($ca_path))return $this;
        $this->_opts[CURLOPT_CAINFO]=$ca_path;
        return $this;
    }
    /**
     * 设置SSL证书
     * @param string $sslcert_path
     * @param string $sslkey_path
     * @return $this
     */
    public function setSsl($sslcert_path,$sslkey_path){
        $this->_opts[CURLOPT_SSLCERTTYPE]='PEM';
        $this->_opts[CURLOPT_SSLCERT]=$sslcert_path;
        $this->_opts[CURLOPT_SSLKEYTYPE]='PEM';
        $this->_opts[CURLOPT_SSLKEY]=$sslkey_path;
        return $this;
    }
    /**
     * 设置请求JSON消息头
     * @return $this
     */
    public function setXmlhttprequest(){
        $this->_opts[CURLOPT_HTTPHEADER][]="X-Requested-With: XMLHttpRequest";
        return $this;
    }
    /**
     * 设置是否检查HTTPS证书
     * @param string $sslcert_path
     * @param string $sslkey_path
     * @return $this
     */
    public function verifySsl($status){
        $status=$status?1:0;
        $this->_opts[CURLOPT_SSL_VERIFYHOST]=$status;
        $this->_opts[CURLOPT_SSL_VERIFYPEER]=$status;
        return $this;
    }
    /**
     * 设置请求方式
     * @param int $method
     * @return $this
     */
    public function setMethod($method){
        switch($method) {
            case self::METHOD_GET:
                $this->_opts[CURLOPT_POST]=FALSE;
                break;
            case self::METHOD_POST:
                $this->_opts[CURLOPT_POST]=true;
                break;
            case self::METHOD_PUT:
                $this->_opts[CURLOPT_CUSTOMREQUEST]='PUT';
                break;
            case self::METHOD_DELETE:
                $this->_opts[CURLOPT_CUSTOMREQUEST]='DELETE';
                break;
        }
        return $this;
    }
    /**
     * 设置上传数据
     * @param string $data
     * @param int $data_type
     * @return $this
     */
    public function setData($data,$data_type=self::DATA_AUTO){
        if ($data_type==self::DATA_AUTO){
            if (isset($this->_opts[CURLOPT_POST])){
                switch($this->_opts[CURLOPT_POST]) {
                    case self::METHOD_PUT:
                    case self::METHOD_POST:
                        $data_type=self::DATA_POST;
                    break;
                    case self::METHOD_GET:
                    case self::METHOD_DELETE:
                        $data_type=self::DATA_GET;
                    break;
                }
            }else $data_type=self::DATA_GET;
        }
        switch ($data_type){
            case self::DATA_GET:
                if (is_array($data))$data=http_build_query($data);
                else $data=strval($data);
                $url=$this->_opts[CURLOPT_URL];
                $this->_opts[CURLOPT_URL].=(strpos($url, "?")===false?"?":"&").$data;
                break;
            case self::DATA_POST:
                if (empty($this->_opts[CURLOPT_CUSTOMREQUEST]))$this->_opts[CURLOPT_POST]=TRUE;
                $this->_opts[CURLOPT_POSTFIELDS]=$data;
                break;
        }
        return $this;
    }
    /**
     * 设置上传文件
     * @param string $file_path
     * @param string $file_name
     * @param string $mimetype
     * @return $this
     */
    public function setFile($file_path, $file_name,$mimetype = null){
        if (isset($this->_opts[CURLOPT_POSTFIELDS])){
            if (is_string($this->_opts[CURLOPT_POSTFIELDS])){
                $this->_opts[CURLOPT_POSTFIELDS]=parse_str($this->_opts[CURLOPT_POSTFIELDS]);
            }else{
                $this->_opts[CURLOPT_POSTFIELDS]=(array)$this->_opts[CURLOPT_POSTFIELDS];
            }
        }else $this->_opts[CURLOPT_POSTFIELDS]=array();
        $this->_opts[CURLOPT_POSTFIELDS][$file_name]=curl_file_create($file_path,$mimetype);
        return $this;
    }
    /**
     * 设置代理
     * @param string $proxy_ip
     * @param string $proxy_port
     * @param string $proxy_user
     * @param string $proxy_auth
     * @return $this
     */
    public function setProxy($proxy_ip, $proxy_port,$proxy_user = null,$proxy_auth=CURLAUTH_BASIC){
        $this->_opts[CURLOPT_PROXY]=$proxy_ip;
        $this->_opts[CURLOPT_PROXYPORT]=$proxy_port;
        if(!empty($proxy_auth)){
            $this->_opts[CURLOPT_PROXYAUTH]=$proxy_auth;
            $this->_opts[CURLOPT_PROXYUSERPWD]=$proxy_user;
        }
        return $this;
    }
    /**
     * 获取连接资源
     */
    public function getCh(){
        if (!is_resource($this->_ch)){
            $this->_ch=curl_init();
        }
        return $this->_ch;
    }
    /**
     * 执行CURL请求
     * @return \LSYS\Curl\ResultJSONP|\LSYS\Curl\ResultJSON|\LSYS\Curl\ResultXML|\LSYS\Curl\Result
     */
    public function exec(){
        $ch=$this->getCh();
        curl_setopt_array($ch, $this->getOpt());
        return self::parseResult($this, curl_exec($ch));
    }
    /**
     * 把CURL请求结果解析成RESULT对象
     */
    public static function parseResult(Curl $curl,$data){
        $ch=$curl->getCh();
        $headers=[];
        $code=$http_code=0;
        if ($data===false){
            $status=false;
            $code=curl_errno($ch);
            $data=curl_error($ch);
        }else{
            $status=true;
            $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($curl->_header){
                $hs=curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($data, 0, $hs);
                $header=explode("\r\n", $header);
                foreach ($header as $v){
                    if(empty($v))continue;
                    $v=explode(":", $v);
                    if (count($v)==1)$headers[]=$v[0];
                    else $headers[array_shift($v)]=implode(":", $v);
                }
                $data=substr($data, $hs);
            }
            if ($curl->_accept_encoding==self::ACCEPT_ENCODING_AUTO){
                $data=(new ZipDecode($data))->getData();
            }
        }
        switch ($curl->_format){
            case self::RESULT_FORMAT_JSONP:
                return new ResultJSONP($status,$code,$http_code,$headers,$data);
            case self::RESULT_FORMAT_JSON:
                return new ResultJSON($status,$code,$http_code,$headers,$data);
            break;
            case self::RESULT_FORMAT_XML:
                return new ResultXML($status,$code,$http_code,$headers,$data);
            break;
            default:
                return new Result($status,$code,$http_code,$headers,$data);
            break;
        }
    }
    /**
     * 关闭CURL连接
     */
    public function close(){
        if (is_resource($this->_ch))curl_close($this->_ch);
        $this->_ch=null;
    }
    public function __destruct(){
        $this->close();
    }
}