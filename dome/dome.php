<?php
use LSYS\Curl;
use LSYS\Curl\Multi;
include_once __DIR__."/../vendor/autoload.php";
$curl1=new Curl('http://httpbin.org/json',Curl::METHOD_GET,Curl::RESULT_FORMAT_JSON);
$curl1->setData(['sta'=>'dd']);
$curl2=new Curl('http://httpbin.org/xml',Curl::METHOD_GET,Curl::RESULT_FORMAT_XML);
$data=Multi::run([$curl1,$curl2]);
list($res1,$res2)=$data;
print_r($res1);
print_r($res2);

