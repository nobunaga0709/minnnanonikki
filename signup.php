<?php 

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//POST送信されているかチェック
if(!empty($_POST)){
    //変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];
    
    //未入力チェック
    validRequired($email,'email');
    validRequired($pass,'pass');
    validRequired($pass_re,'pass_re');
    
    if(empty($err_msg)){
        //email形式チェック
        validEmail($email,'email');
        //email重複チェック
        validEmailDup($email);
        //emailの最大文字数チェック
        validMaxLen($email,'email');
        
        //パスワードの半角英数字チェック
        validHalf($pass,'pass');
        //パスワードの最小文字数チェック
        validMinLen($pass,'pass');
        //パスワードの最大文字数チェック
        validMaxLen($pass,'pass');
        
        if(empty($err_msg)){
            //パスワードとパスワード再入力が同じかどうか
            validMatch($pass,$pass_re,'pass');
            
            if(empty($err_msg)){
                //例外処理
                try {
                    //DBへ接続
                    $dbh = dbConnect();
                    //SQL文作成
                    $sql = 'INSERT INTO users (email,pass,login_time,create_date)VALUES(:email,:pass,:login_time,:create_date)';
                    $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
                    //クエリ実行
                    $stmt = queryPost($dbh, $sql, $data);
                    //クエリ成功の場合
                    if($stmt){
                        //ログイン有効期限（デフォルトを1時間とする）
                        $sesLimit = 60*60;
                        //最終ログイン日時を現在日時に
                        $_SESSION['login_date'] = time();
                        $_SESSION['login_limit'] = $sesLimit;
                        //ユーザーIDを格納
                        $_SESSION['user_id'] = $dbh->lastInsertId();
                        
                        debug('セッション変数の中身：'.print_r($_SESSION,true));
                        header("Location:mypage.php"); //マイページへ

                    }
                } catch (Exception $e) {
                    error_log('エラー発生：'. $e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}
?>


    <?php
      $sitetitle = 'ユーザー登録';
      require('head.php'); 
    ?>
    <body class="page-signup page-1colum">
        <!-- ヘッダー -->
        <?php
          require('header.php'); 
        ?>

        <div id="contents" class="site-width">
            <section id="main">
                <div class="form-container">
                    <form action="" method="post" class="form">
                        <h2 class="title">ユーザー登録</h2>
                        <div class="area-msg">
                            <?php 
                            if(!empty($err_msg['common'])) echo $err_msg['common']; 
                            ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['email'])) echo 'err';
                                      ?>">
                            Email
                            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; 
                                                                   ?>">
                        </label>
                        <div class="area-msg">
                            <?php 
                            if(!empty($err_msg['email'])) echo $err_msg['email']; 
                            ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; 
                                      ?>">
                            パスワード
                            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; 
                                                                   ?>">
                        </label>
                        <div class="area-msg">
                            <?php 
                            if(!empty($err_msg['pass'])) echo $err_msg['pass']; 
                            ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['pass'])) echo 'err';
                                      ?>">
                            パスワード（再入力）
                            <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; 
                                                                   ?>">
                        </label>
                        <div class="area-msg">
                            <?php 
                            if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; 
                            ?>
                        </div>
                        <div class="btn-container">
                            <input type="submit" class="btn" value="登録">
                        </div>

                    </form>
                </div>
            </section>
        </div>
        <footer id="footer">
            Copyright みんなの日記.All Rights Reserved.
        </footer>
    </body>
</html>