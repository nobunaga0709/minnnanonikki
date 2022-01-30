<header>
    <div class="site-width">
        <h1><a href="index.php">みんなの日記</a></h1>
        <nav id="top-nav">
            <ul>
                <?php 
                if(!empty($_SESSION['user_id'])){
                ?>
                    <li><a href="logout.php">ログアウト</a></li>
                    <li><a href="mypage.php">マイページ</a></li>
                <?php
                } else {
                ?>
                    <li><a href="login.php">ログイン</a></li>
                    <li><a href="signup.php">ユーザー登録</a></li>
                <?php    
                }
                ?>         
            </ul>
        </nav>
    </div>
</header>