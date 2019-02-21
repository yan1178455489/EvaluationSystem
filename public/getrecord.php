<?php
header("Content-type=text/json;charset=UTF-8");
$conn = mysql_connect("localhost", "root", "root") or die("连接数据库的过程失败！");
mysql_query("set names utf-8");
mysql_select_db("eplatform");


$resultset = mysql_query("select topn, runtime  from record order by topn", $conn);
////////////////////////////////////////////////准备数据进行装填
$data = array();
////////////////////////////////////////////////实体类
class Record{
    public $topn;
    public $runtime;
}
////////////////////////////////////////////////处理
while($row = mysql_fetch_array($resultset, MYSQL_ASSOC)) {
    $record = new Record();
    $record->topn = $row['topn'];
    $record->runtime = $row['runtime'];
    $data[] = $record;
}
$conn.close();
// 返回JSON类型的数据
//var_dump($data)
echo json_encode($data)
?>