<?php

require 'Util.php';

define('chaveCaptcha', '1c98c10b6316dd5982da4df1f3ed7bdf');

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

    $post = 'lk_codig='.$usuario.'&lk_senha='.$senha.'&lk_width=123&lk_suaft=&cd_usuario=AC218174&cd_cpf=&cd_senha=2821Gil&email=&g-recaptcha-response='.$token.'&lk_manut=https://www.servicodeprotecaoaocredito.com.br/bvs_login.htm&lk_urlesquecisenha=https://www.bvsnet.com.br/cgi-bin/db2www/NETPO101.mbr/RecuperaSenha';
    $send = Util::curl($url, $cookies, $post, true, $ref, false, $proxy);

    if(stristr($send, 'cation: menu.ph')){
        return $cookies;
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
    $post = "quantidade=&valor=&documento={$doc}&documentos=%2C%2C%2C%2C%2C{$doc}&cepConfirmacao=&opcaoCpf=doc&nomeFormulario=Acerta+Completo&consulta=doc&cpf1=&cpf2=&cpf3=&cpf4=&cpf5=&multiplasPaginas=true&comboScoreResult=&comboCreditoResult=&comboTipoCredito=OU&txtTelefone=&txtTelefone=&chkCheque=on&chequeSimples=simples&cheque=S&cmc7Mascara1=&cmc7Mascara2=&cmc7Mascara3=&cmc7TotalChequesMascara=&cmc7ValorMascara=&bancoMascara=&agenciaMascara=&contaCorrenteMascara=&digitoContaMascara=&numeroChequeMascara=&digitoChequeMascara=&totalChequeMascara=&dataChequeMascara=&valorMascara=&_csrf=".$token;
    $ref = 'https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/formularioAcertaCompleto';

    $ver = Util::curl($url, $cookie, $post, false, $ref, false, $proxy);
    $ver = str_replace('href="/', 'href="https://acerta.bvsnet.com.br/', $ver);
    $ver = str_replace('src="resources/', 'src="https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resources/', $ver);

    $ver = str_replace('src="/FamiliaAcertaPFWeb/', 'src="https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/', $ver);
    
    $ver = str_replace('"./resources/', '"https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resources/', $ver);
    $ver = str_replace('"resources/', '"https://acerta.bvsnet.com.br/FamiliaAcertaPFWeb/resources/', $ver);

    $ver = str_replace('help-back"', 'help-back" style="display:none;"', $ver);
    $ver = str_replace('divImgVelDash"', 'divImgVelDash" style="display:none;"', $ver);
    
    return $ver;
}

if(isset($_GET['doc']))
{

    $usuario = 'AC218174';
    $senha   = '2821Gil';
    $cookie  = file_get_contents('cookie.txt');
    $proxy   = '559d925baf:fG3PpwJo@br1.payweb.io:4444';

    $con = validarLogin($cookie, $proxy);
    if($con){
        $ver = consultar($_GET['doc'], $cookie, $proxy);

        if(stristr($ver, 'ES CONFIDENCIAIS - S')){
            echo $ver;
        }else{
            //se nao tiver cookie resolver e logar...
            $token   = resolveCaptcha($proxy);

            $cookie   = logar($usuario, $senha, $token, $proxy);

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

        $con = validarLogin($cookie, $proxy);
        file_put_contents('cookie.txt', $cookie);

        if($con){
            $ver = consultar($cookie, $proxy);
            echo $ver;
            die;
        }    
    }
}else{
    echo "Acerta Completo";
}


// echo $con;

// se for ok, salvar cookie...

die;


echo $logar;
