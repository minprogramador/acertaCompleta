<?php

abstract class Util
{
    public static function curl($url, $cookies, $post, $header=true, $referer=null, $follow=false, $proxy=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
        if(isset($referer)){ curl_setopt($ch, CURLOPT_REFERER,$referer); }
        else{ curl_setopt($ch, CURLOPT_REFERER,$url); }
        if($post)
        {
            if($post === 'x'){
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "");

            }else{
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
        }   

        if($proxy){
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        
        $headers[] = "Upgrade-Insecure-Requests: 1";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);

        $res = curl_exec( $ch);
        curl_close($ch);
        //return utf8_decode($res);
        return ($res);
    }
    
    public function parseForm($data)
    {
        $post = array();
        if(preg_match_all('/<input(.*)>/U', $data, $matches)){
            foreach($matches[0] as $input){
                if(!stristr($input, "name=")) continue;
                if(preg_match('/name=(".*"|\'.*\')/U', $input, $name))
                {
                    $key = substr($name[1], 1, -1);
                    if(preg_match('/value=(".*"|\'.*\')/U', $input, $value)) $post[$key] = substr($value[1], 1, -1);
                    else $post[$key] = "";
                }
            }
        }
        return $post;
    }

    public static function validate_rechapcha($response) {
        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        $query_data = [
            'secret' => '6Ldz7oYUAAAAAHQnRAP8M8b9FWyy_dWn9w4Ny_wu',
            'response' => $response,
            'remoteip' => (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'])
        ];

        // Collect and build POST data
        $post_data = http_build_query($query_data, '', '&');

        // Send data on the best possible way
        if (function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec')) {
            // Use cURL to get data 10x faster than using file_get_contents or other methods
            $ch = curl_init($verifyURL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-type: application/x-www-form-urlencoded'));
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            // If server not have active cURL module, use file_get_contents
            $opts = array('http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post_data
                )
            );
            $context = stream_context_create($opts);
            $response = file_get_contents($verifyURL, false, $context);
        }

        // Verify all reponses and avoid PHP errors
        if ($response) {
            $result = json_decode($response);
            if ($result->success === true) {
                return true;
            } else {
                return $result;
            }
        }

        return false;
    }

    public static function corta($str, $left, $right)
    {
        $str = substr ( stristr ( $str, $left ), strlen ( $left ) );
        @$leftLen = strlen ( stristr ( $str, $right ) );
        $leftLen = $leftLen ? - ($leftLen) : strlen ( $str );
        $str = substr ( $str, 0, $leftLen );
        return $str;
    }

    public static function getCookies($get)
    {
        preg_match_all('/ookie: (.*);/U',$get,$temp);
        $cookie = $temp[1];
        $cookies = implode('; ',$cookie);
        return $cookies;
    }

    public static function xss($data, $problem='')
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = strip_tags($data);

        if ($problem && strlen($data) == 0){ return ($problem); }
        return $data;
    }

    public static function Convert($res)
    {
        $res = preg_replace("/[^a-zA-Z0-9.]/", "", strtr($res, "áàãâéêíóôõúüçñÁÀÃÂÉÊÍÓÔÕÚÜÇÑ ", "aaaaeeiooouucnAAAAEEIOOOUUCN_"));
        return $res;
    }

    public static function DelAcento($string)
    {
        $a = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏàáâãäåçèéêëìíîïñòóôõöøùúû';
        $b = 'aaaaaaceeeeiiiiaaaaaaceeeeiiiinoooooouuu';
        $string = strtr($string, utf8_decode($a), $b);
        return utf8_encode($string);
    }

    public static function UrlPatch()
    {
        if($_SERVER['HTTP_HOST'])
        {
            if($_SERVER['REQUEST_URI'])
            {
                $url = $_SERVER['HTTP_HOST'].($_SERVER['REQUEST_URI']);
                $url = explode('/',$url);
                $url = 'http://'.$url[0].'/'.$url[1];

                if(strlen($url[1]) < 2)
                {
                    $url = $_SERVER['HTTP_HOST'];
                    $url = explode('/',$url);
                    $url = 'http://'.$url[0];
                }
            }
            else
            {
                $url = $_SERVER['HTTP_HOST'];
                $url = explode('/',$url);
                $url = 'http://'.$url[0];

            }
        }
        return $url;
    }

    public static function Msg($width=null,$tipo='erro',$titulo=null,$mensagem=null)
    {
        #tipo = ( erro || sucesso )
        if(isset($width))
        {
            $width = "style=\"width:$width;\"";
        }

        if(isset($titulo))
        {
            $titulo = "<strong>$titulo</strong><br>";
        }

        return "<center><div $width class=\"Mensagem $tipo\"> $titulo $mensagem</div></center>";
    }


    public static function conData($data)
    {
        return implode(!strstr($data, '/') ? "/" : "-", array_reverse(explode(!strstr($data, '/') ? "-" : "/", $data)));
    }

    public static function countData($data)
    {
        $data_inicial = date("Y-m-d");
        $data_final   = $data;

        $time_inicial = strtotime($data_inicial);
        $time_final   = strtotime($data_final);
        $diferenca    = $time_final - $time_inicial;

        $count = (int)floor( $diferenca / (60 * 60 * 24));
        return $count;
    }

    public static function dataToDias($data1, $data2)
    {
        $data_inicial = $data1;
        $data_final   = $data2;

        $time_inicial = strtotime($data_inicial);
        $time_final   = strtotime($data_final);
        $diferenca    = $time_final - $time_inicial;

        $count = (int)floor( $diferenca / (60 * 60 * 24));
        return $count;
    }

    public static function is_base64($str)
    {
        return (bool)preg_match('`^[a-zA-Z0-9+/]+={0,2}$`', $str);
    }

    public static function ConDh($data)
    {
        return strftime("%d/%m/%Y %H:%M:%S", strtotime($data));
    }

    public static function ir($ir,$msg=null,$icon=null)
    {
        if(isset($msg))
        {
            $_SESSION['AlertMng'] = $msg;
            $_SESSION['IconAler'] = $icon;
        }
        header("Location:".$ir);
        die;
    }

    public static function limpaTudo()
    {
        foreach( $_SESSION as $Index => $Data)
        {
            unset($_SESSION[$Index]);
        }

        foreach( $_COOKIE as $Index => $Data )
        {
            setcookie($Index, '', time()-172800);
        }
    }

    public static function getBrowser()
    {
        $u_agent  = $_SERVER['HTTP_USER_AGENT'];
        $bname    = 'Unknown';
        $platform = 'Unknown';
        $version  = "";

        if (preg_match('/linux/i', $u_agent))
        {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent))
        {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent))
        {
            $platform = 'windows';
        }

        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }
        elseif(preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Opera';
            $ub = "Opera";
        }
        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        $known   = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches))
        {
            //
        }

        $i = count($matches['browser']);
        if ($i != 1)
        {
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub))
            {
                $version= $matches['version'][0];
            }
            else
            {
                $version= $matches['version'][1];
            }
        }
        else
        {
            $version= $matches['version'][0];
        }

        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }

    public static function dec($get)
    {
        $conf = urldecode(base64_decode(urldecode(base64_decode(urlencode($get)))));
        return $conf;
    }

    public static function Valcpf($cpf)
    {
        $s = $cpf;
        $c = substr($s, 0, 9);
        $dv = substr($s, 9, 2);
        $d1 = 0;
        $v = false;

        for ($i = 0; $i < 9; $i++){$d1 = $d1 + substr($c, $i, 1) * (10 - $i);}
        if($d1 == 0)
        {
            return false;
            $v = true;
        }
        $d1 = 11 - ($d1 % 11);
        if($d1 > 9){$d1 = 0;}

        if(substr($dv, 0, 1) != $d1)
        {
            return false;
            $v = true;
        }

        $d1 = $d1 * 2;
        for ($i = 0; $i < 9; $i++){$d1 = $d1 + substr($c, $i, 1) * (11 - $i);}
        $d1 = 11 - ($d1 % 11);
        if($d1 > 9){$d1 = 0;}

        if(substr($dv, 1, 1) != $d1)
        {
            return false;
            $v = true;
        }
        if(!$v){return true;}
    }


    public static function en($string, $key='987654')
    {
        $result = "";
        for($i=0; $i<strlen($string); $i++)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char    = chr(ord($char)+ord($keychar));
            $result .=$char;
        }

        $salt_string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxys0123456789~!@#$^&*()_+`-={}|:<>?[]\;',./";
        $length = rand(19, 101);
        $salt   = "";
        for($i=0; $i<=$length; $i++)
        {
            $salt .= substr($salt_string, rand(0, strlen($salt_string)), 1);
        }
        $salt_length = strlen($salt);
        $end_length  = strlen(strval($salt_length));

        return base64_encode($result.$salt.$salt_length.$end_length);
    }

    public static function de($string, $key='987654')
    {
        $result      = "";
        $string      = base64_decode($string);
        $end_length  = intval(substr($string, -1, 1));
        $string      = substr($string, 0, -1);
        $salt_length = intval(substr($string, $end_length*-1, $end_length));
        $string      = substr($string, 0, $end_length*-1+$salt_length*-1);
        for($i=0; $i<strlen($string); $i++)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char    = chr(ord($char)-ord($keychar));
            $result .=$char;
        }
        return $result;
    }


    public static function valdocs($valor)
    {
        $valor = trim(rtrim($valor));
        $numbers = preg_replace('/[^0-9]/', '', $valor);

        if(empty($valor))
        {
            return false;
        }
        else
        {
            if (strlen($numbers) == 11)
            {
                $cpf = $numbers;

                if (strlen($cpf) != 11) { return false; }
                else if ($cpf == '00000000000' ||
                    $cpf == '11111111111' ||
                    $cpf == '22222222222' ||
                    $cpf == '33333333333' ||
                    $cpf == '44444444444' ||
                    $cpf == '55555555555' ||
                    $cpf == '66666666666' ||
                    $cpf == '77777777777' ||
                    $cpf == '88888888888' ||
                    $cpf == '99999999999') {
                    return false;
                }
                else
                {
                    for ($t = 9; $t < 11; $t++)
                    {
                        for ($d = 0, $c = 0; $c < $t; $c++)
                        {
                            $d += $cpf{$c} * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ($cpf{$c} != $d) { return false; }
                    }

                    return true;
                }
            }
            elseif (strlen($numbers) == 14)
            {
                $cnpj = preg_replace('/[^0-9]/', '', (string) $numbers);
                // Valida tamanho
                if (strlen($cnpj) != 14)
                    return false;
                // Valida primeiro dígito verificador
                for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
                {
                    $soma += $cnpj{$i} * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }

                $resto = $soma % 11;
                if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
                    return false;
                // Valida segundo dígito verificador
                for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
                {
                    $soma += $cnpj{$i} * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }

                $resto = $soma % 11;
                return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
            }
        }
    }

    public static function sendMail($de=null, $para, $assunto, $mensagem) {
        if($de == null) {
            $de = 'noreply@test.com';
        }

        $curl_post_data=array(
            'from'    => "LiveBusca <{$de}>",
            'to'      => $para,
            'subject' => $assunto,
            'text'    => $mensagem
        );

        $service_url = 'https://api.mailgun.net/v3/sandboxc37ca04f08204c248e3eeb9c48695270.mailgun.org/messages';
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "api:50137648bbe04b8784265c6bd9351a00-f45b080f-cc405a17");

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


        $curl_response = curl_exec($curl);
        $response = json_decode($curl_response);
        curl_close($curl);
        return $response;
    }
}