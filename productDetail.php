<?php
//関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　日記詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');
//================================
//  画面処理
//================================
//DBから日記の情報を取得
$dbProductData = getProduct($_SESSION['user_id'], $_GET['p_id']);
$dbUserData = getUser($_SESSION['user_id']);

debug('画面表示処理終了
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$sitetitle = '日記の内容';
require('head.php'); 
?>

<body class="page-logined page-2colum">
    <!-- ヘッダー -->
    <?php
          require('header.php'); 
        ?>

    <div id="contents" class="site-width">
        <h1 class="page-title">
            <div class="diary-title"><?php echo $dbProductData['date']; ?></div>
            <div class="diary-title"><?php echo $dbUserData['username']; ?></div>
            <div class="diary-title"><?php echo $dbProductData['title']; ?></div>
        </h1>
        <section id="main">
            <h2 class="title">内容</h2>
            <p class="diary-contents">
                <?php echo $dbProductData['content']; ?>
            </p>
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
