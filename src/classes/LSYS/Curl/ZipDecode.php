<?php
namespace LSYS\Curl;
class ZipDecode{
    private $_data;
    /**
     * 将ZIP压缩数据解压
     * @param string $data
     */
    public function __construct($data){
        $this->_data=$data;
    }
    /**
     * 是否需要解压
     * @return boolean
     */
    public function status(){
        $strInfo = @unpack("C2chars", substr($this->_data,0,2));
        return isset($strInfo['chars1'])&&isset($strInfo['chars1'])&&intval($strInfo['chars1'].$strInfo['chars2'])==31139;
    }
    public function getData(){
        if ($this->status()){
            $data=$this->_gzdecode($this->_data);
            if ($data!==false)return $data;
        }
        return $this->_data;
    }
    /**
     * 进行GZIP解压
     * @param string $data
     */
    protected function _gzdecode($data){
        $flags = ord ( substr ( $data, 3, 1 ) );
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack ( 'v', substr ( $data, 10, 2 ) );
            $extralen = $extralen [1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) $headerlen = strpos ( $data, chr ( 0 ), $headerlen ) + 1;// Filename
        if ($flags & 16) $headerlen = strpos ( $data, chr ( 0 ), $headerlen ) + 1;// Comment
        if ($flags & 2) $headerlen += 2;// CRC at end of file
        return  @gzinflate ( substr ( $data, $headerlen ) );
    }
}