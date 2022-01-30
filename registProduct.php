<?php
//共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　日記登録・編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');
//================================
// 画面処理
//================================
//GETパラメータがあるかチェック
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : "";
//DBデータ取得
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : "";
debug('DBデータ：'.print_r($dbFormData,true));

//新規登録画面か編集画面かの判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;    //trueなら編集画面
//パラメータ改ざんチェック
if(!empty($p_id) && empty($dbFormData)){
    debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
    header("Location:mypage.php");
}

//POST送信確認
if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST情報：'.print_r($_POST, true));
    debug('FILE情報：'.print_r($_FILES, true));
    
    //変数にユーザー情報を格納
    $name = $_POST['title'];
    $date = $_POST['date'];
    $content = $_POST['content'];
    
    debug('変数：'.print_r($name, true));
    
    //更新の場合はDBの情報と一致しているかどうか確認
    if(!empty($dbFormData)){
        if($dbFormData['title'] !== $name){
            //未入力チェック
            validRequired($name, 'title');
            //最大文字数チェック
            validMaxLen($name, 'title');
        }
        if($dbFormData['date'] !== $date){
            //未入力チェック
            validRequired($date, 'date');
        }
        if($dbFormData['content'] !== $content){
            //未入力チェック
            validRequired($content, 'content');
        }
    }else{
        //未入力チェック
        validRequired($name, 'title');
        validRequired($date, 'date');
        validRequired($content, 'content');
        //最大文字数チェック
        validMaxLen($name, 'title');
    }
    if(empty($err_msg)){
        debug('バリデーションOKです');
        
        //例外処理
        try{
            //DB接続
            $dbh = dbConnect();
            //SQL文作成 edit_flgによって分岐
            if($edit_flg){
                debug('DB更新です');
                $sql = 'UPDATE diary SET title = :name, date = :date, content = :content WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
                $data = array(':name' => $name, ':date' => $date, ':content' => $content, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
            }else{
                debug('DB登録です');
                $sql = 'INSERT INTO diary (title, date, content, user_id) VALUES (:name, :date, :content, :u_id)';
                $data = array(':name' => $name, ':date' => $date, ':content' => $content, ':u_id' => $_SESSION['user_id']);
            }
            debug('SQL:'.$sql);
            debug('流し込みデータ：'.print_r($data,true));
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            //クエリ成功の場合
            if($stmt){
                $_SESSION['msg_success'] = SUC04;
                debug('マイページへ遷移します');
                header("Location:mypage.php");
            }
        }catch (Exception $e){
            error_log('エラー発生：'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
    $sitetitle = ($edit_flg) ? '日記を編集する' : '日記を書く';
    require('head.php'); 
?>

<body class="page-logined page-2colum">
    <!-- ヘッダー -->
    <?php
          require('header.php'); 
        ?>

    <div id="contents" class="site-width">
        <h1 class="page-title">
            <?php echo $sitetitle; ?>
        </h1>
        <section id="main">
            <div class="form-container">
                <form class="form" action="" method="post">
                    <div class="area-msg">
                        <?php 
                            if(!empty($err_msg['common'])) echo $err_msg['common']; 
                            ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['title'])) echo 'err';
                                      ?>">
                        日記の題名
                        <input type="text" name="title" value="<?php echo getFormData('title'); ?>">
                    </label>
                    <div class="area-msg">
                        <?php 
                            if(!empty($err_msg['title'])) echo $err_msg['title']; 
                            ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['date'])) echo 'err';
                                          ?>">
                        日付
                        <input type="date" name="date" value="<?php echo getFormData('date'); ?>">
                    </label>
                    <div class="area-msg">
                        <?php 
                            if(!empty($err_msg['date'])) echo $err_msg['date']; 
                            ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['content'])) echo 'err';
                                      ?>">
                        本文
                        <textarea name="content" cols="50" rows="10"><?php echo getFormData('content'); ?></textarea>
                    </label>
                    <div class="area-msg">
                        <?php 
                            if(!empty($err_msg['content'])) echo $err_msg['content']; 
                            ?>
                    </div>
                    <div class="btn-container">
                        <input type="submit" class="btn" value="<?php echo ($edit_flg) ? '更新する' : '投稿する';?>">
                    </div>
                </form>
            </div>
        </section>
        <section id="sideMenu">
            <ul>
                <li><a href="registProduct.php">日記を書く</a></li>
                <li><a href="mypage.php">マイページ</a></li>
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
