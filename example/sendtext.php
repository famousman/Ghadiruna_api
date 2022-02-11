<?php
$username='ghadiruna username';
$password='ghadiruna username';
$ghbot=new ghadiruna_api(array("username"=>$username, "password"=>$password ) ); // set username and password
$result=$ghbot->login(); // start login into the account
//$pages=$ghbot->getLikedPages(); // get liked pages
//$type='page';

$groups=$ghbot->getLikedGroups(); // get liked groups
$type='group';
foreach ($groups as $dis){
    $text.="$dis[2] \n";
    $text.=" code: ".$dis[5]." \n_______\n";
}
if (isset($dis)) {
    $media_id=$dis[5];
    echo nl2br($text);
    $content=array(
        "handle"=>$type, //page group
        "id"=>$media_id,
        "album"=>"",
        "location"=>"",
        "feeling_value"=>"",
        "video_thumbnail"=>"",
        "message"=>'your text',

    );
    $result = $ghbot->sendMessage($content);
}else{
    echo nl2br("failed");
}