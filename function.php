<?php
//====================================
// ログ
//====================================
// ログを取るか
ini_set('log_errors','on');
//ログの出力先を指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true; //開発中のみtrueにしておく

//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.print_r($str,true));
  }
}

//====================================
// セッション準備・セッション有効期限を伸ばす
//====================================
//セッションファイルの置き場を変更(/var/tmp/下に置くと30日は削除されない)
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ100分の1の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//====================================
// 画面表示処理開始ログ吐き出し関数
//====================================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

//エラーメッセージを定数に設定
define('MSG01','入力必須です');
define('MSG02','emailの形式で入力してください');
define('MSG03','パスワード（再入力）が間違っています');
define('MSG04','半角英数字で入力してください');
define('MSG05','6文字以上で入力してください');
define('MSG06','255文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08','そのEmailはすでに登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '購入しました！相手と連絡を取りましょう！');

//エラーメッセージ用の配列を用意
$err_msg = array();

//バリデーションチェック関数
    //未入力チェック
    function validRequired($str,$key){
        if($str === ""){
            global $err_msg;
            $err_msg[$key] = MSG01;
        }
    }
    //email形式チェック
    function validEmail($str,$key){
        if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
            global $err_msg;
            $err_msg[$key] = MSG02;
        }
    }
    //email重複チェック
    function validEmailDup($email){
        global $err_msg;
        //例外処理
        try {
            //DBへ接続
            $dbh = dbConnect();
            $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
            $data = array(':email' => $email);
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            //クエリ結果の値を取得
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!empty(array_shift($result))){
                $err_msg['email'] = MSG08;
            }
        } catch (Exception $e) {
            error_log('エラー発生：'. $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
    //パスワードとパスワード再入力は同じか
    function validMatch($str1,$str2,$key){
        if($str1 !== $str2){
            global $err_msg;
            $err_msg[$key] = MSG03;
        }
    }
    //半角英数字かどうか
    function validHalf($str,$key){
        if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
            global $err_msg;
            $err_msg[$key] = MSG04;
        }
    }
    //最小文字数チェック
    function validMinLen($str,$key,$min = 6){
        if(mb_strlen($str) < $min){
            global $err_msg;
            $err_msg[$key] = MSG05;
        }
    }
    //最大文字数チェック
    function validMaxLen($str,$key,$max = 255){
        if(mb_strlen($str) > $max){
            global $err_msg;
            $err_msg[$key] = MSG06;
        }
    }

//==============================
//データベース
//==============================

//DB接続関数
function dbConnect(){
    //DBへの接続準備
    $dsn = 'mysql:dbname=diaryservice;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
        //SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
        //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    //PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}

//SQL実行関数
function queryPost($dbh, $sql, $data){
    //クエリー作成
    $stmt = $dbh->prepare($sql);
    //プレースホルダに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました');
        debug('失敗したSQL：'.print_r($stmt,true));
        $err_msg['common'] = MSG07;
        return 0;
    }
    debug('クエリ成功');
    return $stmt;
}

//ユーザー情報を取得
function getUser($u_id){
    debug('ユーザー情報を取得します');
    //例外処理
    try {
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id ,username ,email ,age ,img ,comment ,login_time ,delete_flg ,update_date ,create_date FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
    }
}
//商品情報を取得
function getProduct($u_id, $p_id){
    debug('日記情報を取得します');
    debug('ユーザーID：'.$u_id);
    debug('日記ID:'.$p_id);
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id ,title ,content ,date ,img ,user_id ,delete_flg ,update_date FROM diary WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}
//フォーム入力保持
function getFormData($str, $flg = false){
    if($flg){
        $method = $_GET;
    } else {
        $method = $_POST;
    }
    global $dbFormData;
    //ユーザー情報がある場合
    if(!empty($dbFormData)) {
        //フォームのエラーがある場合
        if(!empty($err_msg[$str])){
            //POSTにデータがある場合
            if(isset($method[$str])){
                return $method[$str];
            }else{
                //POSTにデータがない場合（基本ありえない）
                return $dbFormData[$str];
            }
        }else{
            //POSTにデータがあり、それがDBのデータと違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return $method[$str];
            } else {
                return $dbFormData[$str];
            }
        }
    } else {
        if(isset($method[$str])){
            return $method[$str];
        }
    }
}

function getProductList($currentMinNum = 1, $sort, $keyword, $span = 10){
    debug('日記情報を取得します。');
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        if(empty($keyword)){
            $sql = 'SELECT id FROM diary';
        }else{
            $sql = 'SELECT id FROM diary WHERE title LIKE "%":keyword"%" OR content LIKE "%":keyword"%"';
        }
        if(!empty($sort)){
            switch($sort){
                case 1:
                    $sql .= ' ORDER BY date DESC';
                    break;
                case 2:
                    $sql .= ' ORDER BY date ASC';
                    break;
            }
        }
        if(!empty($keyword)){
            $data = array(':keyword' => $keyword);
        }else{
            $data = array();
        }
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst['total'] = $stmt->rowCount();    //総レコード数
        $rst['total_page'] = ceil($rst['total']/$span);    //総ページ数
        if(!$stmt){
            return false;
        }
        
        //ページング用のSQL文作成
        $sql = 'SELECT id ,title ,content ,date ,img ,user_id ,delete_flg ,update_date FROM diary';
        if(!empty($keyword)){
            $sql .= ' WHERE title LIKE "%":keyword"%" OR content LIKE "%":keyword"%"';
        }
        if(!empty($sort)){
            switch($sort){
                case 1:
                    $sql .= ' ORDER BY date DESC';
                    break;
                case 2:
                    $sql .= ' ORDER BY date ASC';
                    break;
            }
        }
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        $data = (!empty($keyword)) ? array(':keyword' => $keyword) : array();
        //debug('データ：'.print_r($data ,true));
        //debug('SQL：'.$sql);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            //クエリ結果のデータを全レコードを格納
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }
    }catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getOneProduct($u_id){
    debug('日記を取得します。');
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id, title, date, img, user_id, update_date, delete_flg FROM diary WHERE user_id = :u_id AND delete_flg = 0 ORDER BY date ASC';
        //データ流し込み
        $data = array(':u_id' => $u_id);
        debug('SQL:'.$sql);
        debug('データ：'.print_r($data, true));
        //SQL実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            //クエリ結果のデータを格納
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $phrase){
    if(!empty($to) && !empty($subject) && !empty($phrase)){
        //文字化けしないように設定（お決まりパターン）
        mb_language("Japanese"); //現在使っている言語を設定する
        mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械がわかる言葉への変換）するかを設定
        
        //メールを送信（送信結果はtrueかfalseで帰ってくる）
        $result = mb_send_mail($to, $subject, $phrase, "From:".$from);
        //送信結果を判定
        if($result){
            debug('メールを送信しました');
        }else{
            debug('【エラー発生】メールを送信に失敗しました');
        }
    }
}

//================================
// GETパラメーターの付与
//================================
function appendGetParam($arr_del_key = array()){
    //引数に付与から取り除きたいGETパラメーターのキーを取る
    if(!empty($_GET)){    //GETパラメーターが入っているかチェック
        $str = '?';      //変数を作っておく（?は共通部分）
        foreach($_GET as $key => $val){    //GETパラメーターを展開
            if(!in_array($key,$arr_del_key,true)){
                //削除したいGETパラメータじゃない場合の処理
                $str .= $key.'='.$val.'&';    //GETパラメーターの付与
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8");    //最後についてしまう&を削除している
        return $str;
    }
    
}
