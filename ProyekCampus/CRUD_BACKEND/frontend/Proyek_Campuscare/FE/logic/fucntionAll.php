<?php
function base_url(){
    return "http://localhost/2405029_KHOIRUL_FRONTEND";
}
function url_segment($segment){
    $url="http://".$_SERVER["SERVER_NAME"]."".$_SERVER['REQUEST_URI'];
    $base= str_replace(base_url(), "", $url);
    $pecah = explode("/", $base);
    if(empty($pecah[$segment])){
        $dataku="";
    }else{
        $dataku=$pecah[$segment];
    }
    return $dataku;
}
function potong_text($tulisan, $count){
    $count=$count;
    $length= strlen($tulisan);
    if($length>$count){
        $i = $count;
        while($i!=0){
            if(substr($tulisan, $i,1)==" "){
                break;
            }else{

            }
            $i= $i-1;
        }
        $tulisan=substr($tulisan,0,$i);
        $tulisan=$tulisan;
    }else{
        $tulisan=$tulisan;
    }
    return $tulisan;
}
?>