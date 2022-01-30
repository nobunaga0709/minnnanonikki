<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETパラメータを取得
//----------------------------------
//現在のページ
$currentPageNum = (!empty($_GET['p'])) ? (int)$_GET['p'] : 1;
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
//キーワード
$keyword = (!empty($_GET['search_keyword'])) ? $_GET['search_keyword'] : '';
debug('キーワード：'.$keyword);
//パラメータに不正な値が入っていないかチェック
if(!is_int($currentPageNum)){
    error_log('エラー発生：指定ページに不正な値が入りました。');
    header("Location:index.php");
}
//表示件数
$listSpan = 10;
//現在の表示レコード先頭を算出
$currentMinNum = ($currentPageNum-1)*$listSpan;
//DBから商品データを取得
$dbProductData = getProductList($currentMinNum, $sort ,$keyword);

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
      $sitetitle = 'HOME';
      require('head.php'); 
    ?>

<body class="page-logined page-2colum">
    <?php 
        require ('header.php');
        ?>
    <div id="contents" class="site-width">
        <h1 class="page-title">日記一覧</h1>
        <section id="search">
            <form action="" method="get" class="form">
                <div class="selectbox">
                    <span class="icn_select"></span>
                    <select name="sort">
                        <option value="0" class="choice">選択してください</option>
                        <option value="1" <?php if(!empty($_GET['sort']) && $_GET['sort']==1) echo 'selected' ; ?> >新しい順</option>
                        <option value="2" <?php if(!empty($_GET['sort']) && $_GET['sort']==2) echo 'selected' ; ?> >古い順</option>
                    </select>
                </div>
                <input type="text" name="search_keyword" placeholder="キーワード" value="<?php echo $keyword; ?>">
                <div class="btn-container">
                    <input type="submit" class="btn" value="検索">
                </div>
            </form>
        </section>
        <section id="main">
            <div class="search-title">
                <div class="search-left">
                    <?php echo $dbProductData['total']; ?>件の投稿が見つかりました
                </div>
                <div class="search-right">
                    <span>
                        <?php echo (!empty($dbProductData['data'])) ? $currentMinNum+1 : 0; ?></span>~<span>
                        <?php echo $currentMinNum+(!empty($dbProductData['data'])) ? count($dbProductData['data']) : 0; ?></span>件/<span>
                        <?php echo $dbProductData['total']; ?></span>件中
                </div>
            </div>
            <ul class="diary-list">
                <?php if(!empty($dbProductData['data'])) foreach($dbProductData['data'] as $key => $val):?>
                <li class="diary-info">
                    <p class="writer-info">
                        <span class="writed-date">
                            <?php echo $val['date']; ?></span>
                        <span class="writer-name">
                            <?php echo getUser($val['user_id'])['username']; ?></span>
                    </p>
                    <p class="diary-title">
                        <a href="productDetail.php?p_id=<?php echo $val['id']; ?>">
                            <?php echo $val['title']; ?></a>
                    </p>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="pagination">
                <?php 
                        $totalPageNum = (int)$dbProductData['total_page'];   //総ページ数
                        $pageColNum = 5;   //表示するページ数

                        //現在のページが、総ページ数と同じかつ総ページ数が表示項目数以上なら、左にリンク4個だす
                        if($currentPageNum === $totalPageNum && $totalPageNum > $pageColNum){
                            $minPageNum = $currentPageNum-4;
                            $maxPageNum = $currentPageNum;
                        //現在のページが、総ページ数マイナス１かつ総ページ数が表示項目数以上なら左にリンク3個、右に1個出す
                        }elseif($currentPageNum === $totalPageNum-1 && $totalPageNum > $pageColNum){
                            $minPageNum = $currentPageNum-3;
                            $maxPageNum = $currentPageNum+1;
                        //現在のページが、2ページ目かつ総ページ数が表示項目数以上なら左にリンク1個、右に3個だす
                        }elseif($currentPageNum === 2 && $totalPageNum > $pageColNum){
                            $minPageNum = $currentPageNum-1;
                            $maxPageNum = $currentPageNum+3;
                        //現在のページが、1ページ目かつ総ページ数が表示項目数以上なら右にリンク4個だす
                        }elseif($currentPageNum === 1 && $totalPageNum > $pageColNum){
                            $minPageNum = $currentPageNum;
                            $maxPageNum = $currentPageNum+4;
                        //総ページ数が表示ページ数より少ない場合はMaxを総ページ数、Minを1にする
                        }elseif($totalPageNum < $pageColNum){
                            $minPageNum = 1;
                            $maxPageNum = $totalPageNum;
                        //以上以外は左右にリンク2個
                        }else{
                            $minPageNum = $currentPageNum-2;
                            $maxPageNum = $currentPageNum+2;
                        }
                    ?>
                <ul class="pagination-list">
                    <?php
                        if($currentPageNum !== 1){
                            echo '<li class="list-item"><a href="?p=1'.appendGetParam(array('p_id')).'">&lt;</a></li>';
                        }
                        for($i = $minPageNum; $i <= $maxPageNum; $i++){
                            echo '<li class="list-item ';
                            if($i === $currentPageNum){echo 'active';}
                            echo '"><a href="?p='.$i.appendGetParam(array('p_id')).'">'.$i.'</a></li>';
                        }
                        if($currentPageNum !== $maxPageNum && $maxPageNum > 1){
                            echo '<li class="list-item"><a href="?p='.$totalPageNum.appendGetParam(array('p_id')).'">&gt;</a></li>';
                        }
                        ?>
                </ul>
            </div>
        </section>
    </div>
    <footer id="footer">
        Copyright みんなの日記.All Rights Reserved.
    </footer>

</body>

</html>
