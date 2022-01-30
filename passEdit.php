<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData, true));

// POST送信されている場合
if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST情報：'.print_r($_POST,true));
    
    //変数にユーザー情報を代入
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];
    
    //未入力チェック
    validRequired($pass_old,'pass_old');
    validRequired($pass_new,'pass_new');
    validRequired($pass_new_re,'pass_new_re');
    
    if(empty($err_msg)){
        debug('未入力チェックOK');
        
        //古いパスワードのチェック
        validMinLen($pass_old,'pass_old');
        validMaxLen($pass_old,'pass_old');
        validHalf($pass_old,'pass_old');
        //新しいパスワードのチェック
        validMinLen($pass_new,'pass_new');
        validMaxLen($pass_new,'pass_new');
        validHalf($pass_new,'pass_new');
        
        //古いパスワードとDBのパスワードを照合
        if(!password_verify($pass_old,$userData['pass'])) {
            $err_msg['pass_old'] = MSG12;
        }
        //新しいパスワードと古いパスワードが同じかどうかチェック
        if($pass_old === $pass_new){
            $err_msg['pass_new'] = MSG13;
        }
        //パスワードとパスワード再入力が同じかどうかチェック
        validMatch($pass_new,$pass_new_re,'pass_new_re');
        
        if(empty($err_msg)){
            debug('バリデーションチェックOK');
            
            //例外処理
            try{
                //DB接続
                $dbh = dbConnect();
                //SQL文作成
                $sql = 'UPDATE users SET pass = :pass WHERE id = :id';
                $data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':id' => $_SESSION['user_id']);
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
                
                //クエリ成功の場合
                if($stmt){
                    $_SESSION['msg_success'] = SUC01;
                    
                    //メールを送信
                    $username = ($userData['username']) ? $userData['username'] : '名無し';
                    $from = 'nekomenobouya@gmail.com';
                    $to = $userData['email'];
                    $subject = 'パスワード変更通知｜みんなの日記';
                    $phrase = <<<EOT
{$username}さん
パスワードが変更されました。

///////////////////////////////////////
みんなの日記
URL   http://nobunaga0914.com
E-mail   nekomenobouya@gmail.com
///////////////////////////////////////
EOT;
                    sendmail($from, $to, $subject, $phrase);
                    header("Location:mypage.php");
                }
            }catch (Exception $e){
                error_log('エラー発生：'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}

?>
<?php
    $sitetitle = 'パスワード変更';
    require('head.php'); 
?>

<body class="page-logined page-2colum">
    <!-- ヘッダー -->
    <?php
          require('header.php'); 
        ?>

    <div id="contents" class="site-width">
        <h1 class="page-title">パスワード変更</h1>
        <section id="main">
            <div class="form-container">
                <form action="" method="post" class="form">
                    <div class="area-msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
                        現在のパスワード
                        <input type="password" name="pass_old">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass_old'])) echo $err_msg['pass_old']; ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
                        新しいパスワード
                        <input type="password" name="pass_new">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass_new'])) echo $err_msg['pass_new']; ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
                        新しいパスワード（再入力）
                        <input type="password" name="pass_new_re">
                    </label>
                    <div class="area-msg">
                        <?php if(!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re']; ?>
                    </div>
                    <div class="btn-container">
                        <input type="submit" class="btn" value="変更する">
                    </div>
                </form>
            </div>
        </section>
        <section id="sideMenu">
            <ul>
                <li><a href="registProduct.php">日記を書く</a></li>
                <li><a href="mypege.php">マイページ</a></li>
                <li><a href="profEdit.php">プロフィール編集</a></li>
                <li><a href="passEdit.php">パスワード変更</a></li>
                <li><a href="withdraw.php">退会</a></li>
            </ul>
        </section>
    </div>
    <footer id="footer">
        Copyright みんなの日記.All Rights Reserved.
    </footer>

</body>

</html>
