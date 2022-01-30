<?php
//関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
//  画面処理
//================================
//DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);

debug('取得したユーザー情報：'.print_r($dbFormData,true));

//POST送信されている場合
if(!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報:'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));
    
    //変数にユーザー情報を代入
    $username = $_POST['username'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $comment = $_POST['comment'];
    //画像をアップロードしパスを格納
    //一旦保留
    
    //DBの情報と入力された情報が異なる場合にバリデーションを行う
    if($dbFormData['username'] !== $username){
        //名前の最大文字数チェック
        validMaxLen($username, 'username');
    }
    if($dbFormData['age'] !== $age){
        //最大文字数チェック
        validMaxLen($age, 'age');
        //半角英数字チェック
        validHalf($age, 'age');
    }
    if($dbFormData['email'] !== $email){
        //emailの形式チェック
        validEmail($email, 'email');
        //emailの最大文字数チェック
        validMaxLen($email, 'email');
        //emailの重複チェック
        validEmailDup($email);
        //emailの未入力チェック
        validRequired($email, 'email');
    }
    
    if(empty($err_msg)){
        debug('バリデーションOKです');
        
        //例外処理
        try{
            //DB接続
            $dbh = dbConnect();
            //SQL文作成
            $sql = 'UPDATE users SET username = :u_name, age = :age, email = :email, comment = :comment WHERE id = :u_id';
            $data = array(':u_name' => $username, ':age' => $age, ':email' => $email, ':comment' => $comment, ':u_id' => $dbFormData['id']);
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            
            //クエリ成功の場合
            if($stmt){
                $_SESSION['msg_success'] = SUC02;
                debug('マイページへ遷移します');
                header("Location:mypage.php"); //マイページへ
            }
        } catch (Exception $e){
            error_log('エラー発生：'.$e->getMessage());
            $err_msg['common'] = MSG07;
          }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>
     <?php
      $sitetitle = 'プロフィール編集';
      require('head.php'); 
    ?>
    <body class="page-logined page-2colum">
        <!-- ヘッダー -->
        <?php
          require('header.php'); 
        ?>

        <div id="contents" class="site-width">
            <h1 class="page-title">プロフィール編集</h1>
            <section id="main">
                <div class="form-container">
                    <form action="" method="post" class="form">
                       <div class="area-msg">
                           <?php 
                           if(!empty($err_msg['common'])) echo $err_msg['common'];
                           ?>
                       </div>
                       <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
                            名前
                            <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
                            年齢
                            <input type="number" name="age" value="<?php echo getFormData('age'); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                            Email
                            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
                            自己紹介文
                            <textarea name="comment" cols="50" rows="5"><?php echo getFormData('comment'); ?></textarea>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['img'])) echo 'err'; ?>">
                            プロフィール画像
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['img'])) echo $err_msg['img']; ?>
                        </div>
                        <input type="submit" class="btn" value="保存する">
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