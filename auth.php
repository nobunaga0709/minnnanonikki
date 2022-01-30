<?php

//================================
// ログイン認証・自動ログアウト
//================================
//ログインしている場合
if(!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです');
    
    //現在日時がログイン有効期限（最終ログイン日時+有効期限）を超えていた場合
    if(time() > ($_SESSION['login_limit'] + $_SESSION['login_date']) ){
        debug('ログイン期限オーバーです');
        //セッションを削除
        session_destroy();
        //ログインページへ
        header("Location:login.php");
    } else{
        debug('ログイン有効期限内です');
        //最終ログイン日時を現在日時へ変更
        $_SESSION['login_date'] = time();
        
        //現在実行中のスクリプトファイル名がlogin.phpの場合
        //$_SERVER['PHP_SELF']はドメインからのパスを返すため、今回だと「/webukatu_practice03/login.php」が返ってくるので、
        //さらにbasename関数を使うことでファイル名だけを取り出せる
        if(basename($_SERVER['PHP_SELF']) === 'login.php') {
            debug('マイページへ遷移します');
            header("Location:mypage.php");
        }
    }
} else{
    debug('未ログインユーザーです');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php') {
        debug('ログインページへ遷移します');
        header("Location:login.php");
    }
}