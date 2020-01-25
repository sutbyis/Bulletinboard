<?php 
// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', 'root');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message = null;// 投稿完了メッセージを表示します。
$error_message = array();//エラーメッセージを表示します。
$clean = array();//サニダイズ機能を追加。サニタイズの処理自体は、バリデーションの部分で「空じゃなかった場合」に実行されるようif文にelse文を加える形で追記しました。
// これで、もし未入力だった場合はエラーメッセージを作成し、入力があった場合のみサニタイズを行うというコードになります。

session_start();

if( !empty($_POST['btn_submit']) ) {
	
	// 表示名の入力チェック
	if( empty($_POST['view_name']) ) {
		$error_message[] = '表示名を入力してください。';
	} else {
		$clean['view_name'] = htmlspecialchars( $_POST['view_name'], ENT_QUOTES);

		// セッションに表示名を保存
		$_SESSION['view_name'] = $clean['view_name'];
	}
        // $clean['view_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['view_name']);
        // 改行コード「\r\n」「\n」「\r」をそれぞれ検索し、表示名の場合は空文字に置き換えて削除を行なっています。
    
    	// メッセージの入力チェック表示名の入力チェック(メッセージに記入がない時以下のエラーを表示)
	if( empty($_POST['message']) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
	}else {
		$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
    // 改行コード「\r\n」「\n」「\r」をそれぞれ検索し、ひと言メッセージでは「<br>」に置き換えました。
    }

        //表示の禁止処理。
        if( empty($error_message) ) {
        
            // データベースに接続(mysqliクラスのオブジェクトを作成してデータベースに接続)
         /*mysqliクラスのオブジェクトには、データベースへの接続情報として「ホスト名」「ユーザー名」「パスワード」「データベース名」を順に指定します。 */
            $mysqli = new mysqli( 'localhost', 'root', 'root', 'board');
       
        // 接続エラーの確認
	if( $mysqli->connect_errno ) {
			$error_message[] = '書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
	} else {
		// 文字コード設定
		$mysqli->set_charset('utf8');
		
		// 書き込み日時を取得
		$now_date = date("Y-m-d H:i:s");
		
       // データを登録するSQL作成
		$sql = "INSERT INTO message (view_name, message, post_date) VALUES ( '$clean[view_name]', '$clean[message]', '$now_date')";
		
		// データを登録
		$res = $mysqli->query($sql);
	
		if( $res ) {
			$success_message = 'メッセージを書き込みました。';
		} else {
			$error_message[] = '書き込みに失敗しました。';
		}
	
		// データベースの接続を閉じる
		$mysqli->close();
        }
     }
    }


// データベースに接続
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);


// 接続エラーの確認
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {
    
    $sql = "SELECT view_name,message,post_date FROM message ORDER BY post_date DESC";
    $res = $mysqli->query($sql);
	if( $res ) {
		$message_array = $res->fetch_all(MYSQLI_ASSOC);
	}
    $mysqli->close();
    
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板</title>
<style>
/*------------------------------
 Reset Style
 
------------------------------*/
html, body, div, span, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
abbr, address, cite, code,
del, dfn, em, img, ins, kbd, q, samp,
small, strong, sub, sup, var,
b, i,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, figcaption, figure,
footer, header, hgroup, menu, nav, section, summary,
time, mark, audio, video {
    margin:0;
    padding:0;
    border:0;
    outline:0;
    font-size:100%;
    vertical-align:baseline;
    background:transparent;
}
body {
    line-height:1;
}
article,aside,details,figcaption,figure,
footer,header,hgroup,menu,nav,section {
    display:block;
}
nav ul {
    list-style:none;
}
blockquote, q {
    quotes:none;
}
blockquote:before, blockquote:after,
q:before, q:after {
    content:'';
    content:none;
}
a {
    margin:0;
    padding:0;
    font-size:100%;
    vertical-align:baseline;
    background:transparent;
}
/* change colours to suit your needs */
ins {
    background-color:#ff9;
    color:#000;
    text-decoration:none;
}
/* change colours to suit your needs */
mark {
    background-color:#ff9;
    color:#000;
    font-style:italic;
    font-weight:bold;
}
del {
    text-decoration: line-through;
}
abbr[title], dfn[title] {
    border-bottom:1px dotted;
    cursor:help;
}
table {
    border-collapse:collapse;
    border-spacing:0;
}
hr {
    display:block;
    height:1px;
    border:0;
    border-top:1px solid #cccccc;
    margin:1em 0;
    padding:0;
}
input, select {
    vertical-align:middle;
}
/*------------------------------
Common Style
------------------------------*/
body {
	padding: 50px;
	font-size: 100%;
	font-family:'ヒラギノ角ゴ Pro W3','Hiragino Kaku Gothic Pro','メイリオ',Meiryo,'ＭＳ Ｐゴシック',sans-serif;
	color: #222;
	background: #f7f7f7;
}
a {
    color: #007edf;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
h1 {
	margin-bottom: 30px;
    font-size: 100%;
    color: #222;
    text-align: center;
}
/*-----------------------------------

-----------------------------------*/
label {
    display: block;
    margin-bottom: 7px;
    font-size: 86%;
}
input[type="text"],
textarea {
	margin-bottom: 20px;
	padding: 10px;
	font-size: 86%;
    border: 1px solid #ddd;
    border-radius: 3px;
    background: #fff;
}
input[type="text"] {
	width: 200px;
}
textarea {
	width: 50%;
	max-width: 50%;
	height: 70px;
}
input[type="submit"] {
	appearance: none;
    -webkit-appearance: none;
    padding: 10px 20px;
    color: #fff;
    font-size: 86%;
    line-height: 1.0em;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    background-color: #37a1e5;
}
input[type=submit]:hover,
button:hover {
    background-color: #2392d8;
}
hr {
	margin: 20px 0;
	padding: 0;
}
.success_message {
    margin-bottom: 20px;
    padding: 10px;
    color: #48b400;
    border-radius: 10px;
    border: 1px solid #4dc100;
}
.error_message {
    margin-bottom: 20px;
    padding: 10px;
    color: #ef072d;
    list-style-type: none;
    border-radius: 10px;
    border: 1px solid #ff5f79;
}
.success_message,
.error_message li {
    font-size: 86%;
    line-height: 1.6em;
}
/*-----------------------------------
掲示板エリア
-----------------------------------*/
article {
	margin-top: 20px;
	padding: 20px;
	border-radius: 10px;
	background: #fff;
}
article.reply {
    position: relative;
    margin-top: 15px;
    margin-left: 30px;
}
article.reply::before {
    position: absolute;
    top: -10px;
    left: 20px;
    display: block;
    content: "";
    border-top: none;
    border-left: 7px solid #f7f7f7;
    border-right: 7px solid #f7f7f7;
    border-bottom: 10px solid #fff;
}
	.info {
		margin-bottom: 10px;
	}
	.info h2 {
		display: inline-block;
		margin-right: 10px;
		color: #222;
		line-height: 1.6em;
		font-size: 86%;
	}
	.info time {
		color: #999;
		line-height: 1.6em;
		font-size: 72%;
	}
    article p {
        color: #555;
        font-size: 86%;
        line-height: 1.6em;
    }
@media only screen and (max-width: 1000px) {
    body {
        padding: 30px 5%;
    }
    input[type="text"] {
        width: 100%;
    }
    textarea {
        width: 100%;
        max-width: 100%;
        height: 70px;
    }
}
</style>
</head>
<body>
<h1>ひと言掲示板</h1>
<?php if( !empty($success_message) ): ?>
    <p class="success_message"><?php echo $success_message; ?></p>
<?php endif; ?>
<?php if( !empty($error_message) ): ?>
	<ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
			<li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<form method="post">
    <!-- 名前を表示する -->
	<div>
		<label for="view_name">表示名</label>
        <input id="view_name" type="text" name="view_name" value="<?php if(!empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
        <!-- labelのfor method とinputのidが結びつき転送している-->
        <!-- PHPで入力データを判別するためのname属性と、label要素に関連づけるためのid属性を同じように指定します。 -->
    </div>
    <!-- ひと言メッセージを入力するエリア -->
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"></textarea>
        <!-- textarea要素は複数行のテキストを入力するためのフォームです。
先ほどのinput要素は1行分のなのでたくさんのテキストを入力したいときはやや不便、こちらの要素であれば改行をしても快適に入力することができます。 -->
	</div>
    <!-- フォームに入力されたデータを送信するボタン -->
	<input type="submit" name="btn_submit" value="書き込む">
    <!-- value属性が登場しましたが、ここで指定した名前がボタンのラベルとして表示されます。 -->
</form>
<hr>
<section>
<!-- ここに投稿されたメッセージを表示 -->
<?php if( !empty($message_array) ): ?>
<?php foreach( $message_array as $value ): ?>
<article>
    <div class="info">
        <h2><?php echo $value['view_name']; ?></h2>
        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
    </div>
    <p><?php echo nl2br($value['message']); ?></p>
</article>
<?php endforeach; ?>
<?php endif; ?>
</section>
</body>
</html>