<?php

require 'Util.php';

define('chaveCaptcha', '');
define('proxy' , '');
define('usuario', '');
define('senha', '');
define('css', '<style>
html, body {
    height: 100%;
}
body, h1, h2, h3, h4, h5, h6, p, ul, li, form {
    border: 0;
    padding: 0;
    margin: 0;
    font-family: "Verdana", Arial, Trebuchet MS;
}

#all {
    min-height: 99%;
    position: relative;
}
#container {
    width: 969px;
    margin: 0 auto;
}
/*.nav-consulta {
    width: 100%;
    height: 63px;
    float: left;
    position: relative;
}*/
.content {
    padding: 0px;
    width: 969px;
    margin-top: 0px;
}
.help-back {
    float: right;
    width: 100%;
    margin: 10px 0 -1px 0;
}
.cnt-resultado {
    border: 1px solid #dde3e6;
}
.cnt-resultado th strong.blue {
    font-size: 17px;
    color: #013d65;
}
.div-img-vel-dash {
    padding: 7px 3px 7px 7px;
}
.decisao-mensagem {
    font-size: 11px;
    color: #000000;
    padding: 7px 3px 7px 7px;
}
.cnt-resultado td {
    font-size: 11px;
    color: #000000;
}
.content strong.blue {
    font-size: 12px !important;
    color: #013d65;
}
img {
    border: 0;
}
ul {
    list-style: none;
}
.imagem-score {
    background: #fff;
    color: #fff;
    padding: 15px;
    position: relative;
    max-width: 210px;
    max-height: 160px;
    margin: -145px 0px 0px 682px !important;
    z-index: 1;
}
.main-score {
    position: relative;
    width: 100%;
}
.valor-score-laranja::before {
    border-left-color: #e36714;
}
.valor-score::before {
    width: 0;
    height: 0;
    border: 15px solid transparent;
        border-left-color: transparent;
    content: "";
    right: -29px;
    top: 52px;
    position: absolute;
}

.valor-score {
    color: #fff;
    padding: 15px;
    position: relative;
    width: 150px;
    height: 100px;
    border-radius: 10px 10px 10px 10px;
    -moz-border-radius: 10px 10px 10px 10px;
    -webkit-border-radius: 10px 10px 10px 10px;
    border: 0px solid #c40827;
}
.valor-score span {
    display: inline-block;
    font-size: 28px;
    font-weight: bold;
    text-align: center;
    line-height: 1.5;
    margin-top: 13px;
    margin-left: 25px;
}
.descricao-score {
    background: #fff;
    color: #000;
    padding: 15px;
    position: relative;
    width: 430px;
    height: 100px;
    border-radius: 10px 10px 10px 10px;
    -moz-border-radius: 10px 10px 10px 10px;
    -webkit-border-radius: 10px 10px 10px 10px;
    border: 1px solid #dde3e6;
    margin: -132px 0px 0px 200px;
}
.descricao-score span {
    display: inline-block;
    font-size: 11px;
    font-weight: bold;
    margin-top: 40px;
}
.probabilidade-score span {
    display: inline-block;
    font-size: 11px;
    font-weight: bold;
    text-align: center;
}
.score-imagem-valor-inicial {
    position: relative;
    top: -44px;
    left: 683px;
    background-color: white;
    width: 10px;
    z-index: 2;
}
.score-imagem-valor-final {
    position: relative;
    top: -58px;
    left: 912px;
    background-color: white;
    width: 10px;
    z-index: 2;
}
.probabilidade-score {
    background: #fff;
    color: #000;
    padding: 5px;
    position: relative;
    margin: -34px 0px 0px 680px !important;
    z-index: 2;
}
.valor-score-laranja {
    background: #e36714;
    border: 0px solid #e36714;
}
.cnt-resultado th {
    font-size: 17px;
    font-weight: normal;
    color: #013d65;
    text-align: left;
}
.cnt-resultado tr.blue td {
    background: #e9edee;
}
.help-back {
    float: right;
    width: 100%;
    margin: 10px 0 -1px 0;
}
.help-back ul {
    float: right;
}
h2 {
    font-size: 25px;
    color: #013d65;
    margin-bottom: 15px;
}
.help-back span {
    float: right;
    font-size: 10px;
    margin: 5px 10px 0px 0px;
    padding: 0px;
}

</style>');
function resolveCaptcha($proxy)
{
    $url = 'http://2captcha.com/in.php?key='.chaveCaptcha.'&method=userrecaptcha&googlekey=6LdILFcUAAAAAMM3XN6QEzBvkzIop--D52TDgviF&pageurl=https://web2.bvsnet.com.br/transacional/login.php&here=now&json=1&proxy='.$proxy;

    $ver = Util::curl($url, null, null, false);
    $chave = Util::corta($ver, '1,"request":"', '"');

    $url = 'http://2captcha.com/res.php?key=1c98c10b6316dd5982da4df1f3ed7bdf&action=get&id='.$chave;

    $ver = Util::curl($url, null, null, false);
    for ($i=0; $i < 50; $i++) { 
        sleep(2);
        $ver = Util::curl($url, null, null, false);

        if(stristr($ver, 'CAPCHA_NOT_REA')){            
            continue;
        }
        elseif(stristr($ver, 'OK|'))
        {
            $ver = explode('OK|', $ver);
            return $ver[1];  
            break;
        }else{
            return false;
        }
    }
}

function logar($usuario, $senha, $token, $proxy)
{
    $url     = 'https://web2.bvsnet.com.br/transacional/login.php';
    $cookies = '';
    $post    = '';
    $referer = '';

    $login = Util::curl($url, $cookies, null, true, $referer, false, $proxy);
    $cookies = Util::getCookies($login);

    $url = 'https://web2.bvsnet.com.br/transacional/autenticacao.php';
    $ref = 'https://web2.bvsnet.com.br/transacional/login.php';

    $post = 'lk_codig='.$usuario.'&lk_senha='.$senha.'&lk_width=123&lk_suaft=&cd_usuario='.$usuario.'&cd_cpf=&cd_senha='.$senha.'&email=&g-recaptcha-response='.$token.'&lk_manut=https://www.servicodeprotecaoaocredito.com.br/bvs_login.htm&lk_urlesquecisenha=https://www.bvsnet.com.br/cgi-bin/db2www/NETPO101.mbr/RecuperaSenha';
    $send = Util::curl($url, $cookies, $post, true, $ref, false, $proxy);

    if(stristr($send, 'CODIGO DO OPERADOR NAO CADAS')){
        return 'error';
    }
    elseif(stristr($send, 'cation: menu.ph')){
        return $cookies;
    }elseif(stristr($send, 'name="frmMenuPME"')){
            
        $url = 'https://www2.bvsnet.com.br/login.php';
        $frm = Util::parseForm($send);
        $send = Util::curl($url, $cookies, http_build_query($frm), true, $url, false, $proxy);

        if(stristr($send, 'cation: home.ph')){
            $url = 'https://www2.bvsnet.com.br/home.php';
            $send = Util::curl($url, $cookies, null, true, $url, false, $proxy);
            return $cookies;
        }
    }else{
         return false;
    }
}

function validarLogin($cookie, $proxy)
{
    $url = 'https://web2.bvsnet.com.br/transacional/menu.php';
    $ver = Util::curl($url, $cookie, null, true, $url, false, $proxy);
    //return $ver;// echo $ver;

    if(stristr($ver, 'Consultar por:')){
        return true;
    }else{
        return false;// 'nao existe cons..';
    }
}

function consultar($doc, $cookie, $proxy)
{
    $url = 'https://web2.bvsnet.com.br/transacional/produtos.php?p=ACERTA_COMPLETO&t=F';
    $ver = Util::curl($url, $cookie, null, true, null, false, $proxy);

    $url = Util::corta($ver, "action='", "'");
    $frm = Util::parseForm($ver);
    // echo $url;
    // echo "\n\n";
    // print_r($frm);
    // echo "\n\n\n\n";
    $ver = Util::curl($url, $cookie, http_build_query($frm), true, null, false, $proxy);
    $cookie = Util::getCookies($ver);
    $token  = Util::corta($ver, 'name="_csrf" value="', '"');

    $url = 'https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resultadoConsulta';
    $post = "quantidade=&valor=&documento={$doc}&documentos=%2C%2C%2C%2C%2C&cepConfirmacao=&opcaoCpf=doc&nomeFormulario=Acerta+Completo&consulta=doc&cpf1=&cpf2=&cpf3=&cpf4=&cpf5=&multiplasPaginas=true&comboScoreResult=&comboCreditoResult=&comboTipoCredito=OU&txtTelefone=&txtTelefone=&chkCheque=on&chequeSimples=simples&cheque=S&cmc7Mascara1=&cmc7Mascara2=&cmc7Mascara3=&cmc7TotalChequesMascara=&cmc7ValorMascara=&bancoMascara=&agenciaMascara=&contaCorrenteMascara=&digitoContaMascara=&numeroChequeMascara=&digitoChequeMascara=&totalChequeMascara=&dataChequeMascara=&valorMascara=&_csrf=".$token;
    $ref = 'https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/formularioAcertaCompleto';

    $ver = Util::curl($url, $cookie, $post, false, $ref, false, $proxy);
//    $ver = str_replace('href="/', 'href="https://acerta.bvsnet.com.br/', $ver);
   $ver = str_replace('src="resources/', 'src="https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resources/', $ver);
    $ver = str_replace('</title>', '</title>'.css, $ver);
    // $ver = str_replace('src="/FamiliaAcertaPFWeb/', 'src="https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/', $ver);
    
    $ver = str_replace('"./resources/', '"https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resources/', $ver);
    $ver = str_replace('"resources/', '"https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resources/', $ver);

    // $ver = str_replace('help-back"', 'help-back" style="display:none;"', $ver);
    // $ver = str_replace('divImgVelDash"', 'divImgVelDash" style="display:none;"', $ver);
    
    return $ver;
}

$_GET['doc'] = '11111111111';

if(isset($_GET['doc']))
{
    $usuario = usuario;
    $senha   = senha;
    $cookie  = file_get_contents('cookie.txt');
    $proxy   = proxy;

    $con = validarLogin($cookie, $proxy);

    if($con){
        $ver = consultar($_GET['doc'], $cookie, $proxy);
        if(stristr($ver, 'ES CONFIDENCIAIS - S')){
            echo $ver;
        }else{
            //se nao tiver cookie resolver e logar...
            $token   = resolveCaptcha($proxy);

            $cookie   = logar($usuario, $senha, $token, $proxy);
            if($cookie === 'error'){
                die('error');
            }
            $con = validarLogin($cookie, $proxy);
            file_put_contents('cookie.txt', $cookie);

            if($con){
                $ver = consultar($_GET['doc'], $cookie, $proxy);
                echo $ver;
                die;
            }

        }
        echo $ver;
        die;
    }else{


        //se nao tiver cookie resolver e logar...
        $token   = resolveCaptcha($proxy);
        $cookie   = logar($usuario, $senha, $token, $proxy);
        if($cookie === 'error'){
            die('error');
        }
        $con = validarLogin($cookie, $proxy);

        file_put_contents('cookie.txt', $cookie);

        if($con){
            $ver = consultar($_GET['doc'], $cookie, $proxy);
            if(stristr($ver, 'ES CONFIDENCIAIS - S')){
                echo $ver;
                die;
            }
        }    
    }
}else{
    echo "Acerta Completo";
}


// echo $con;

// se for ok, salvar cookie...

die;


echo $logar;
