<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//DBから登録情報を取得
$dbMyDiary = getOneProduct($_SESSION['user_id']);
?>

    <?php
      $sitetitle = 'マイページ';
      require('head.php'); 
    ?>
    
    <body class="page-logined page-2colum">
        <!-- ヘッダー -->
        <?php
          require('header.php'); 
        ?>

        <div id="contents" class="site-width">
            <h1 class="page-title">マイページ</h1>
            <section id="main">
                <h2 class="title">投稿履歴</h2>
                <ul class="diary-log-list">
                    <?php if(!empty($dbMyDiary)) {foreach ($dbMyDiary as $key => $val): ?> 
                        <li class="diary-log">
                            <p class="log-right"><?php echo $val['date']; ?></p>
                            <p class="log-left"><a href="productDetail.php?p_id=<?php echo $val['id']; ?>"><?php echo $val['title']; ?></a></p>
                        </li>
                    <?php endforeach; ?>
                    <?php }else{echo 'あなたの日記は投稿されていません';} ?>
                </ul>
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