
<?php
    $server = "localhost";
    $dbname = "CSexp1_cs13079DB";
    $user = "CSexp1_cs13079";
    $pass = "passwordA4";
    $tablename = "cs13079_part2";

    $Addr2_code = "null";
    //$all_data =$_REQUESR["all_data"];
   
    $Addr2 = htmlspecialchars($_REQUEST["Addr"]);
    if($Addr2 == NULL){echo '<script>alert("入力がありません");location.href="form2.php"</script>';}
    if (!$link = mysql_connect($server, $user, $pass)) {
        echo 'Could not connect to mysql';
        exit;
    }
    if (!mysql_select_db($dbname, $link)) {
        echo 'Could not select database';
        exit;
    }

    mysql_set_charset('utf8', $link);

    if(preg_match("/^[0-9]+$/",$Addr2)){
        $Addr2_code = "zip";
    }else if (preg_match("/^(?:\xE3\x82[\xA1-\xBF]|\xE3\x83[\x80-\xB6])+$/", $Addr2)) {
        $Addr2_code = "kana1,kana2,kana3";

    }else {
        $Addr2_code = "addr1,addr2,addr3";
    }


    $sql = "SELECT zip from  " . "$tablename" . " WHERE CONCAT("."$Addr2_code". ") LIKE \"%" . "$Addr2" . "%\" LIMIT 1";
    if (!$result = mysql_query($sql)) {
        echo "DB Error, could not query the database\n";
        echo 'MySQL Error: ' . mysql_error();
        exit;
    }

    $num    = mysql_num_rows($result);
    if($num == 0){
        echo '<script>alert("検索結果がありませんでした");location.href="form2.php"</script>';
    }
?>
<html>
<head>
<title>検索結果一覧</title>
<meta name='viewport' content='initial-scale=1.0, user-scalable=no' />
<script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
</head>
<body>


<div class="box">
<h2>住所,郵便番号一覧</h2>
 <?php echo "[";
    if($Addr2_code == "zip"){

        echo "郵便番号:";
    }else{
        echo "住所:";
    }
    echo $Addr2. "]" . "の結果一覧<br /><br />";
    ?>
    住所をクリックすると地図上に表示されます。
<br />

</div>

</body>
</html>
</br>

<style type="text/css">
    .table2 tr:hover {background-color: yellow;}
    .table2 {
        width: 600px;
        border: none;
        border-top: solid 1px #666;
        border-bottom: solid 1px #666;
        border-collapse: separate;
        border-spacing: 0 4px;
        background: #f5f5f5;
        border-right: solid 1px #666;
        border-left: solid 1px #666;
    }
 
    .table2 th {
        vertical-align: middle;
        height: 50px;
        width: 120px;
        border-right: solid 1px #666;
        border-left: solid 1px #666;
        margin: 0;
        text-align: center;
        color: #333;
        font-size: 16px;
        font-weight: bold;
    }
 
    .table2 td {
        padding: 0;
        margin: 0;
        height: 30px;
        width: 120px;
        border-right: solid 1px #666;
        border-left: solid 1px #666;
        font-size: 16px;
        font-weight: bold;
        line-height: 16px;
        vertical-align: middle;
    }
 
    .table3 td {
        background-color: #ffff63;
        width: 30px;height:30px;
        font-weight: bold;
    }

    a         { display:block;width:100%;height:100%;}
    a:link    { color:#0000ff; text-decoration: underline; }
    a:visited { color:#0000ff; text-decoration: underline; }
    a:hover   {background-color:#ffcccc;}
    a:active  { color:#0000ff; text-decoration: none; }


    html, body { height:80%; width:100%; margin:0; padding:0; }
    #map { 
        height:100%; 
    }

    .box {
         border-bottom: 1px solid #FF9900;
          float: left;
          background-color: #FFFF99;
          width: 1304px;
    }


    html {
    background: url(green.jpg) no-repeat center center fixed;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
    }
    body { overflow: hidden; } 
</style>

<?php
    $page_num = $_REQUEST["page_num"];
    $all_data = $_REQUEST["all_data"];
    $Addr_geo = $_REQUEST["Addr_geo"];
    htmlspecialchars($_REQUEST["Addr"]);
    $Addr2 = $_REQUEST["Addr"];

    if($all_data == 0){
        $sql = "SELECT zip from  " . "$tablename" . " WHERE CONCAT("."$Addr2_code". ") LIKE \"%" . "$Addr2" . "%\"";
        $result = mysql_query($sql, $link);
        $all_data    = mysql_num_rows($result);
    }
    
    $max_num = ceil($all_data/10);
    $page_last = $max_num-$page_num;//最後と最後から一つ前のページで使用



    function strAddrToLatLng( $strAddr ){//住所→緯度、経度に変換
        $strRes = file_get_contents(
             'http://maps.google.com/maps/api/geocode/json'
            . '?address=' . urlencode( mb_convert_encoding( $strAddr, 'UTF-8' ) )
            . '&sensor=false&language=ja'
        );
    $aryGeo = json_decode( $strRes, TRUE );
    if ( !isset( $aryGeo['results'][0] )){
         return '';
    }
        $strLat = (string)$aryGeo['results'][0]['geometry']['location']['lat'];
        $strLng = (string)$aryGeo['results'][0]['geometry']['location']['lng'];
        return $strLat . ',' . $strLng;
    }


    $sql = "SELECT * from  " . "$tablename" . " WHERE CONCAT("."$Addr2_code". ") LIKE \"%" . "$Addr2" . "%\"";
    $sql .= "LIMIT " . $page_num*10 . ", 10";//続き
 
    $result = mysql_query($sql, $link);

    echo '<table class="table2" style="float:left"><tr bgcolor="#cccccc"><th>住所</th><th>郵便番号</th></tr>';
    while ($row = mysql_fetch_assoc($result)) {
        $zip = $row["zip"];
        $addr1 = $row["addr1"];
        $addr2 = $row["addr2"];
        $addr3 = $row["addr3"];

        echo '<tr bgcolor="white">';
        echo "<td>";
        echo "<a href = query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . $page_num . "&all_data=" . "$all_data" . "&Addr_geo=". "$addr1" . "$addr2" . "$addr3". ">";
        echo "$addr1" . "$addr2" . "$addr3" ."</td>";
        echo "<td>". "$zip" ."</td>"; 
        echo "</tr>";
    }
    echo "</table>";

    

    if($max_num > 1){
        echo '<table class="table3" border=\"1\" cellpadding="0">';

        if($max_num-$page_num == 2 && $max_num >= 5){//最後から一つ前のページのとき3つ前のリンクを表示
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num-3) . "&all_data=" . "$all_data" . ">";
            echo ($page_num-2) . "</a></td>";
        }
        if($max_num-$page_num ==  1 && $max_num >= 4){//最後のページのとき4つ前と3つ前のリンクを表示
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num-4) . "&all_data=" . "$all_data" . ">";
            echo ($page_num-3) . "</a></td>";

            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num-3) . "&all_data=" . "$all_data" . ">";
            echo ($page_num-2) . "</a></td>";
        }
        if($page_num >= 2){//現在のページの2つ前のリンクを表示
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num-2) . "&all_data=" . "$all_data" . ">";
            echo ($page_num-1) . "</a></td>";
        }
        if($page_num >= 1){//現在のページの１つ前のリンクを表示
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num-1) . "&all_data=" . "$all_data" . ">";
            echo ($page_num) . "</a></td>";
        }

        echo "<td>" . ($page_num+1) . "</a></td>";//現在のページ(遷移させないように)

        if($max_num-$page_num >  1 && $max_num >= 2){//現在のページの１つ後のリンクを表示
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num+1) . "&all_data=" . "$all_data" . ">";
            echo ($page_num+2) . "</a></td>";
        }

        if($max_num-$page_num >  2 && $max_num >= 3){//現在のページの２つ後のリンクを表示
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num+2) . "&all_data=" . "$all_data" . ">";
            echo ($page_num+3) . "</a></td>";
        }

        if($page_num <= 1 && $max_num >= 4){//１ページ目の時４ページ目の、２ページ目の時５ページ目のリンクを表示させる
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num+3) . "&all_data=" . "$all_data" . ">";
            echo ($page_num+4) . "</a></td>";
        }

        if($page_num == 0 && $max_num >= 5){//１ページ目の時５ページ目のリンクを表示させる
            echo "<td><a href =query3.php?Addr=" . $_REQUEST["Addr"] . "&page_num=" . ($page_num+4) . "&all_data=" . "$all_data" . ">";
            echo ($page_num+5) . "</a></td>";
        }

        echo "</tr>";
        echo "</table>";

        if($all_data > 10){
            echo "$max_num" . "ページの中の", $page_num + 1, "ページ目を表示";
        }
    }
        echo "(全" . "$all_data" . "件)";

    mysql_free_result($result);
    mysql_close($link);
?>

<form action="form2.php">
    <input type="submit" value="検索に戻る"/>
</form>

<div id='map' float:"right"></div>
<?xml version="1.0" encoding="utf-8"?>
<script type='text/javascript'>
/* ページ読み込み時に地図を初期化 */
function initialize() {
  var latlng=new google.maps.LatLng(<?php
    if($Addr_geo != null){
        echo strAddrToLatLng("$Addr_geo");
    }else{  
        echo strAddrToLatLng("静岡県浜松市中区城北３丁目５−１");
    }?>
    );



  /* 地図のオプション設定 */
  var myOptions = {
    zoom: 15, /*初期のズーム レベル */
    center: latlng, /* 地図の中心地点 */
    mapTypeId: google.maps.MapTypeId.HYBRID /* 地図タイプ */
  };
  /* 地図オブジェクト生成 */
  var map=new google.maps.Map(document.getElementById('map'), myOptions);
  var location = new google.maps.LatLng(<?php
    if($Addr_geo != null){
        echo strAddrToLatLng("$Addr_geo");
    }else{  
        echo strAddrToLatLng("静岡県浜松市中区城北３丁目５−１");
    }?>);
var marker = new google.maps.Marker(
    { map: map, position: location } );
}
</script>

<body onload='initialize()'>
  <div id='map'></div>
</body>