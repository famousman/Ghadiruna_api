<?php
/**
 * @package Ghadiruna API Package - beta
 * warning all copyright reserved for Saeb Khanzadeh (skhanzadeh.ir|saeb.bnam@gmail.com)
 * this class is for send content to ghadiruna.com social network.
 * it does not work for two-step login activated accounts.
 * you can create new account with strong password and make it admin and use it for sending contents.
 * @author Saeb Khanzadeh <saeb.bnam@gmail.com> <skhanzadeh.ir>
 * @version 2.1
 *
 */

class ghadiruna_api
{
    private  $actionlogin_url='includes/ajax/core/signin.php';
    private  $sendpost_url='includes/ajax/posts/post.php';
    private  $upload_url='includes/ajax/data/upload.php';
    private  $reaction_url='includes/ajax/posts/reaction.php';
    private  $likedpages_url='pages/liked';
    private  $base_url='https://www.ghadiruna.com/';
    private  $username;
    private  $password;
    private  $logindata;
    private  $result;
    private  $cookie_file;
    private  $cookiedir='cookies';
    public  $secretid;
    public function __construct(array $data)
    {
        $this->username=$data['username'];
        $this->password=$data['password'];
        $this->logindata=array(
            "username_email"=> $this->username,
            "password"=> $this->password,
            "remember"=>"on");
        $this->cookie_file= __DIR__."/".$this->cookiedir."/".$this->username."_cookie.dat";
        if(!is_dir(__DIR__."/".$this->cookiedir)){
            mkdir(__DIR__."/".$this->cookiedir);
        }

        if(!file_exists($this->cookie_file)){
            fopen($this->cookie_file, "w+");

        }
    }
    public function login(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url.$this->actionlogin_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->logindata));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); // Stores cookies in the temp file
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Ch-Ua: ^^';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'Dnt: 1';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?1';
        $headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Mobile Safari/537.36';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Origin: '.$this->base_url;
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: '.$this->base_url;
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200) {
            $result=json_encode(array("failed to login"));
            if ($result) {
                $result=json_encode(array($result));
            }
        }
        curl_close($ch);
        $this->getDataSession();
        if ($result=='{"callback":"window.location.reload();"}') {
            $result=["ok"=>true,"description"=>'reload page' ] ;
        }elseif (curl_errno($ch)) {
            $result=["ok"=>false,"description"=>'Error:' . curl_error($ch) ] ;
        }else {
            $result=["ok"=>false,"description"=>json_decode($result,true) ];
        }
        $this->result=$result;
        return $this->result;
    }
    public function getDataSession(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Accepts all CAs
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Ch-Ua: ^^';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'Dnt: 1';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?1';
        $headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Mobile Safari/537.36';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Origin: '.$this->base_url;
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: '.$this->base_url;
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpcode != 200) {
            $result=json_encode(array("failed access to data"));
            if ($result) {
                $result=json_encode(array($result));
            }
        }
        elseif (curl_errno($ch)) {
            $result=["ok"=>false,"description"=>'Error:' . curl_error($ch) ] ;
        }else{
            $secret= (preg_match_all('/var secret = "([a-zA-Z_0-9]+)";/ms',$result,$secrets)) ? $secrets[1][0] : null;
            $this->secretid=$secret;
            $result=["ok"=>true,"description"=>array('secret'=>$secret) ] ;
        }
        $this->result=$result;
        curl_close($ch);
        return $this->result;
    }

    /**
     * @method bool|array sendtext() sendtext(array $data) move to sendmessage()
     * @method bool|array sendpost() sendpost(array $data) move to sendmessage()
     * @method bool|array sendMessage() sendMessage(array $data) move to sendmessage()
     * @method bool|array sendvoice() sendvoice(array $data) move to sendaudio()
     * @method bool|array sendAudio() sendAudio(array $data) move to sendaudio()
     * @method bool|array sendPhoto() sendPhoto(array $data) move to sendphoto()
     * @method bool|array sendimage() sendimage(array $data) move to sendphoto()
     * @method bool|array sendpicture() sendpicture(array $data) move to sendphoto()
     * @method bool|array sendVideo() sendVideo(array $data) move to sendvideo()
     *
     * @param $name
     * @param $arguments
     * @return array|bool[]
     */
    public function __call($name, $arguments)
    {
        switch (strtolower($name)){
            case 'sendtext':
            case 'sendpost':
            case 'sendmessage':
                return $this->sendMessage($arguments[0]);
            case 'sendvoice':
            case 'sendaudio':
                return $this->sendaudio($arguments[0]);
            case 'sendphoto':
            case 'sendimage':
            case 'sendpicture':
                return $this->sendphoto($arguments[0]);
            case 'sendvideo':
                return $this->sendvideo($arguments[0]);
            default:
                return ['ok'=>false,'description'=>'unknown request'];
        }
    }
    /**
     * @example <code> gapi_beta::sendphoto(array('handle'=>'page','id'=>102,'photo'=>'image.jpg','message'=>'sample code')); </code>
     *
     * @param array $data
     * @param string $data['chat_type'] optional
     * @param string $data['handle']
     * @param string $data['photo']
     * @param string $data['location']
     * @param string $data['album']
     * @param string $data['feeling_action'] optional
     * @param string $data['feeling_value']
     * @param string $data['video_thumbnail']
     * @return array|bool[]
     */
    public function sendphoto(array $data){
        $data['file']=$this->uploadPhoto(array('file'=>$data['photo']));
        $data['photos']=str_ireplace('"file"','"source"',$data['file']);
        $temp=json_decode($data['photos'],true);
        $temp[$temp['source']]=$temp;
        $temp[$temp['source']]['blur']=0;
        unset($temp['source']);
        unset($data['photo']);
        $data['photos']=json_encode($temp);
        unset($data['file']);
        if(isset($data['chat_type'])) {
            $data['handle'] = $data['chat_type'];
            unset($data['chat_type']);
        }
        $data['handle']=(isset($data['handle'])) ? $data['handle']:'';
        $data['location']=(isset($data['location'])) ? $data['location']:'';
        $data['album']=(isset($data['album'])) ? $data['album']:'';
        $data['feeling_value']=(isset($data['feeling_value'])) ? $data['feeling_value']:'';
        $data['video_thumbnail']=(isset($data['video_thumbnail'])) ? $data['video_thumbnail']:'';
        if(isset($data['chat_id'])) {
            $data['id'] = $data['chat_id'];
            unset($data['chat_id']);
        }
        if(!array_key_exists('handle',$data) and $data['handle']>1 and is_string($data['handle'])) return ["ok"=>false,"description"=>'handle is not valid' ];
        if(!array_key_exists('id',$data) and $data['id']>1 and is_int($data['id'])) return ["ok"=>false,"description"=>'id is not valid' ];
        return $this->sendAPIRequest($data);

    }
    /**
     * @example <code> gapi_beta::sendvideo(array('handle'=>'page','id'=>102,'video'=>'video.mp4','message'=>'sample code')); </code>
     *
     * @param array $data
     * @param string $data['chat_type'] optional
     * @param string $data['handle']
     * @param string $data['video']
     * @param string $data['location']
     * @param string $data['album']
     * @param string $data['feeling_action'] optional
     * @param string $data['feeling_value']
     * @param string $data['video_thumbnail']
     * @return array|bool[]
     */
    public function sendvideo(array $data){
        $data['file']=$this->uploadVideo(array('file'=>$data['video']));
        $data['video']=str_ireplace('"file"','"source"',$data['file']);
        unset($data['file']);
        if(isset($data['chat_type'])) {
            $data['handle'] = $data['chat_type'];
            unset($data['chat_type']);
        }
        $data['handle']=(isset($data['handle'])) ? $data['handle']:'';
        $data['location']=(isset($data['location'])) ? $data['location']:'';
        $data['album']=(isset($data['album'])) ? $data['album']:'';
        $data['feeling_value']=(isset($data['feeling_value'])) ? $data['feeling_value']:'';
        $data['video_thumbnail']=(isset($data['video_thumbnail'])) ? $data['video_thumbnail']:'';
        if(isset($data['chat_id'])) {
            $data['id'] = $data['chat_id'];
            unset($data['chat_id']);
        }
        if(!array_key_exists('handle',$data) and $data['handle']>1 and is_string($data['handle'])) return ["ok"=>false,"description"=>'handle is not valid' ];
        if(!array_key_exists('id',$data) and $data['id']>1 and is_int($data['id'])) return ["ok"=>false,"description"=>'id is not valid' ];
        return $this->sendAPIRequest($data);

    }
    /**
     * @example <code> gapi_beta::sendmessage(array('handle'=>'page','id'=>102,'audio'=>'audio.mp3','message'=>'sample code')); </code>
     *
     * @param array $data
     * @param string $data['chat_type'] optional
     * @param string $data['handle']
     * @param string $data['audio']
     * @param string $data['location']
     * @param string $data['album']
     * @param string $data['feeling_action'] optional
     * @param string $data['feeling_value']
     * @param string $data['video_thumbnail']
     * @return array|bool[]
     */
    public function sendaudio(array $data){
        $data['file']=$this->uploadAudio(array('file'=>$data['audio']));
        $data['audio']=str_ireplace('"file"','"source"',$data['file']);
        unset($data['file']);
        if(isset($data['chat_type'])) {
            $data['handle'] = $data['chat_type'];
            unset($data['chat_type']);
        }
        $data['handle']=(isset($data['handle'])) ? $data['handle']:'';
        $data['location']=(isset($data['location'])) ? $data['location']:'';
        $data['album']=(isset($data['album'])) ? $data['album']:'';
        $data['feeling_value']=(isset($data['feeling_value'])) ? $data['feeling_value']:'';
        $data['video_thumbnail']=(isset($data['video_thumbnail'])) ? $data['video_thumbnail']:'';
        if(isset($data['chat_id'])) {
            $data['id'] = $data['chat_id'];
            unset($data['chat_id']);
        }
        if(!array_key_exists('handle',$data) and $data['handle']>1 and is_string($data['handle'])) return ["ok"=>false,"description"=>'handle is not valid' ];
        if(!array_key_exists('id',$data) and $data['id']>1 and is_int($data['id'])) return ["ok"=>false,"description"=>'id is not valid' ];
        return $this->sendAPIRequest($data);

    }
    /**
     * @example <code> gapi_beta::sendmessage(array('handle'=>'page','id'=>102,'message'=>'sample code')); </code>
     *
     * @param array $data
     * @param string $data['chat_type']
     * @param string $data['handle']
     * @param integer $data['id']
     * @param string $data['location']
     * @param string $data['album']
     * @param string $data['feeling_action'] optional
     * @param string $data['feeling_value']
     * @param string $data['video_thumbnail']
     * @return array|bool[]
     */
    public function sendMessage(array $data){
        if(isset($data['chat_type'])) {
            $data['handle'] = $data['chat_type'];
            unset($data['chat_type']);
        }
        $data['handle']=(isset($data['handle'])) ? $data['handle']:'';
        $data['location']=(isset($data['location'])) ? $data['location']:'';
        $data['album']=(isset($data['album'])) ? $data['album']:'';
        $data['feeling_value']=(isset($data['feeling_value'])) ? $data['feeling_value']:'';
        $data['video_thumbnail']=(isset($data['video_thumbnail'])) ? $data['video_thumbnail']:'';
        if(isset($data['chat_id'])) {
            $data['id'] = $data['chat_id'];
            unset($data['chat_id']);
        }
        if(!array_key_exists('handle',$data) and $data['handle']>1 and is_string($data['handle'])) return ["ok"=>false,"description"=>'handle is not valid' ];
        if(!array_key_exists('id',$data) and $data['id']>1 and is_int($data['id'])) return ["ok"=>false,"description"=>'id is not valid' ];
        return $this->sendAPIRequest($data);

    }
    /**
     * @param array $data
     * @param string $data['chat_type']
     * @param string $data['handle']
     * @param string $data['location']
     * @param string $data['album']
     * @param string $data['feeling_action']
     * @param string $data['feeling_value']
     * @param string $data['feeling_value']
     * @param string $data['video_thumbnail']
     * @param string $data['video'] | optional
     * @param string $data['audio'] | optional
     * @param string $data['image'] | optional
     * @return array|bool[]
     */
    private function sendAPIRequest(array $data){
        if(key_exists('message',$data))
            $data['message']=strip_tags($data['message']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Accepts all CAs
        curl_setopt($ch, CURLOPT_URL, $this->base_url.$this->sendpost_url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Ch-Ua: ^^';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'Dnt: 1';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?1';
        $headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Mobile Safari/537.36';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Origin: '.$this->base_url;
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: '.$this->base_url;
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200) {
            $result=json_encode(array("an error was encountered"));
            if ($result) {
                $result=json_encode(array($result));
            }
        }
        $result=json_decode($result,true);
        if(array_key_exists('post',$result)){
            strlen($result['post']);
        }
        if(stripos($result['post'],'Delete Post')>10){
            preg_match('/posts\/who_shares\.php\?post_id=([0-9]+)/mi',$result['post'],$matches);
            $result=["ok"=>true,'message_id'=>$matches[1] ];
        }
        else{
            $result=["ok"=>false,"description"=>$result[0]];
        }
        if (curl_errno($ch)) {
            $result=["ok"=>false,"description"=>'Error:' . curl_error($ch) ] ;
        }
        curl_close($ch);
        $this->result=$result;
        return $result;
    }
    /**
     * @example <code> gapi_beta::reaction(array('no'=>'delete_post','id'=>102)); </code>
     *
     * @param array $data
     * @param string $data['no']
     * @param integer $data['id']
     * @return bool|array
     */
    public function reaction(array $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Accepts all CAs
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_URL, $this->base_url.$this->reaction_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Ch-Ua: ^^Chromium^^\";v=^^\"92^^\",';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'Dnt: 1';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?1';
        $headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Mobile Safari/537.36';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Origin: https://www.ghadiruna.com';
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: https://www.ghadiruna.com/';
        $headers[] = 'Accept-Language: en-US,en;q=0.9,fa;q=0.8,ar;q=0.7';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpcode != 200) {
            $result=json_encode(array("an error was encountered"));
            if ($result) {
                $result=json_encode(array($result));
            }
        }



        $result=json_decode($result,true);
        if(array_key_exists('post',$result)){
            strlen($result['post']);
        }
        if(stripos($result['post'],'Delete Post')>10){
            $result=["ok"=>true];
        }else{
            $result=["ok"=>false,"description"=>$result[0]];
        }
        if (curl_errno($ch)) {
            $result=["ok"=>false,"description"=>'Error:' . curl_error($ch) ] ;
        }
        curl_close($ch);
        $this->result=$result;
        return $result;

    }
    /**
     * @param array $data['file']
     * @return bool|array
     */
    private function uploadPhoto(array $data){
        if(array_key_exists('photo',$data)){ $data['file']=$data['photo']; unset($data['photo']); }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $data['file']);
        if(!strstr($mime_type,'image'))
            return ["ok"=>false,"description"=>'Image is not valid'];
        $filedata = new CurlFile(realpath($data['file']), $mime_type);
        $content=array(
            'verified'=>false,
            'type'=>'photos',
            'handle'=>'publisher',
            'multiple'=>false,
            'secret'=>$this->secretid,
            'file'=>$filedata,
        );
        return $this->upload($content);
    }
    /**
     * @param array $data
     * @param string $data['file']
     * @return bool|array
     */
    private function uploadVideo(array $data){
        if(array_key_exists('video',$data)){ $data['file']=$data['video']; unset($data['video']); }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $data['file']);
        if(!strstr($mime_type,'video'))
            return ["ok"=>false,"description"=>'Video is not valid'];
        $filedata = new CurlFile(realpath($data['file']), $mime_type);
        $content=array(
            'verified'=>false,
            'type'=>'video',
            'handle'=>'publisher',
            'multiple'=>false,
            'secret'=>$this->secretid,
            'file'=>$filedata,
        );
        return $this->upload($content);
    }
    /**
     * @param array $data
     * @param string $data['file']
     * @return bool|array
     */
    private function uploadAudio(array $data){
        if(array_key_exists('audio',$data)){ $data['file']=$data['audio']; unset($data['audio']); }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $data['file']);
        if(!strstr($mime_type,'audio'))
            return ["ok"=>false,"description"=>'Audio is not valid'];
        $filedata = new CurlFile(realpath($data['file']), $mime_type);
        $content=array(
            'verified'=>false,
            'type'=>'audio',
            'handle'=>'publisher',
            'multiple'=>false,
            'secret'=>$this->secretid,
            'file'=>$filedata,
        );
        return $this->upload($content);
    }
    /**
     * @param array $data
     * @param bool $data['verified']
     * @param string $data['type']
     * @param string $data['handle']
     * @param bool $data['multiple']
     * @param array $data['file']
     * @return bool|array
     */
    private function upload($data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url.$this->upload_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Accepts all CAs
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Content-Type: multipart/form-data';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    public function getLikedPages(){
        $re1 = '/<span class="js_user-popover" data-uid="([0-9]+)" data-type="page"> <a class="h6" href="(https:\/\/www.ghadiruna.com\/pages\/([a-zA-Z_.0-9]{2,}))">(.*)<\/a> <\/span>/mi';
        return $this->getMediaList($this->likedpages_url, $re1);

    }
    public function getLikedGroups(){
        $re1='/<a class="h6" href="(https:\/\/www.ghadiruna.com\/groups\/([a-zA-Z_.0-9]{2,}))">(.*)<\/a> <div>(.*)<\/div> <\/div> <div class="mt10"> <button type="button" class="btn btn-sm btn-success btn-delete js_leave-group" data-id="([0-9]+)" data-privacy="public"> <i class="fa fa-check mr5"><\/i>Joined/mi';
        return $this->getMediaList('groups/joined', $re1);

    }
    private function getMediaList($url,$re){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Accepts all CAs
        curl_setopt($ch, CURLOPT_URL, $this->base_url.$url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file); //Uses cookies from the temp file
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array();
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Sec-Ch-Ua: ^^';
        $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
        $headers[] = 'Dnt: 1';
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?1';
        $headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Mobile Safari/537.36';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'Origin: '.$this->base_url;
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: '.$this->base_url;
        $headers[] = 'Accept-Language: en-US,en;q=0.9';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        preg_match_all($re, $result, $matches, PREG_SET_ORDER, 0);
        $this->result=$matches;
        return $matches;

    }

}