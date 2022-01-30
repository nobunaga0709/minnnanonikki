<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//POST送信がある場合
if(!empty($_POST)) {
    debug('POST送信があります');
    //例外処理
    try {
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
        $sql2 = 'UPDATE diary SET delete_flg = 1 WHERE user_id = :us_id';
        //データ流し込み
        $data = array(':us_id' => $_SESSION['user_id']);
        //クエリ実行
        $stmt1 = queryPost($dbh, $sql1, $data);
        $stmt2 = queryPost($dbh, $sql2, $data);
        
        //クエリ実行成功の場合
        if($stmt1 && $stmt2){
            //セッション削除
            session_destroy();
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            debug('トップページへ遷移します');
            header("Location:index.php");
        } else {
            debug('クエリが失敗しました');
            $err_msg['common'] = MSG07;
        }
    } catch (Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


    <?php
      $sitetitle = '退会';
      require('head.php'); 
    ?>
    <body class="page-withdraw page-2colum">
        <!-- ヘッダー -->
        <?php
          require('header.php'); 
        ?>

        <div id="contents" class="site-width">
            <section id="main">
                <div class="form-container">
                    <form action="" method="post" class="form">
                        <h2 class="title">退会</h2>
                        <div class="area-msg">
                            <?php
                            if(!empty($err_msg['common'])) echo $err_msg['common'];
                            ?>
                        </div>
                        <div class="btn-container">
                            <input type="submit" class="btn" value="退会する" name="submit">
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