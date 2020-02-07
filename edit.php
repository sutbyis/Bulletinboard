<?php 
// データベースの接続情報
define( 'DB_HOST', 'localhost');
define( 'DB_USER', 'root');
define( 'DB_PASS', 'root');
define( 'DB_NAME', 'board');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$message_id = null;
$mysqli = null;
$sql = null;
$res = null;
$error_message = array();
$message_data = array();

//セッション開始
session_start();

//ログインセッションの確認管理者としてログインできているか確認する。

if( empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true ) {
	
	// ログインページへリダイレクト
	header("Location: ./admin.php");
}

if( !empty($_GET['message_id']) && empty($_POST['message_id'])) {
//GETパラメータで渡された投稿IDをサニタイズして、$message_idに代入します。
  $message_id = (int)htmlspecialchars($_GET['message_id'], ENT_QUOTES);
  // データベースに接続
	$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);
	
	// 接続エラーの確認
	if( $mysqli->connect_errno ) {
		$error_message[] = 'データベースの接続に失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
	} else {
	
		// データの読み込み
		$sql = "SELECT * FROM message WHERE id = $message_id";//全カラムのmessageテーブルのid
		$res = $mysqli->query($sql);
		
		if( $res ) {
			$message_data = $res->fetch_assoc();//該当する投稿を取得できたら連想配列形式で$message_dataにデータを代入します。
		} else {
		
			// データが読み込めなかったら一覧に戻る
			header("Location: ./admin.php");
		}
		
		$mysqli->close();
	}
} elseif( !empty($_POST['message_id']) ) {

	$message_id = (int)htmlspecialchars( $_POST['message_id'], ENT_QUOTES);
	
	if( empty($_POST['view_name']) ) {
		$error_message[] = '表示名を入力してください。';
	} else {
		$message_data['view_name'] = htmlspecialchars($_POST['view_name'], ENT_QUOTES);
	}
	
	if( empty($_POST['message']) ) {
		$error_message[] = 'メッセージを入力してください。';
	} else {
		$message_data['message'] = htmlspecialchars($_POST['message'], ENT_QUOTES);
	}

	if( empty($error_message) ) {
	
// ここにデータベースに保存する処理が入る
    // データベースに接続
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
		// 接続エラーの確認
		if( $mysqli->connect_errno ) {
			$error_message[] = 'データベースの接続に失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
		} else {
      // UPDATE文で更新する「表示名」と「メッセージ」のデータをセット
      // SET句はデータを更新したいカラムと値をセットで指定します。
      // WHERE句に投稿IDを指定して更新する投稿データを検索する
			$sql = "UPDATE message SET view_name = '$message_data[view_name]', message= '$message_data[message]' WHERE id =  $message_id";
			$res = $mysqli->query($sql);
		}
		
		$mysqli->close();
		
		// 更新に成功したら一覧に戻る
		if( $res ) {
			header("Location: ./admin.php");
		}
	}
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板 管理者ページ（投稿の編集）</title>
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
.btn_cancel {
	display: inline-block;
	margin-right: 10px;
	padding: 10px 20px;
	color: #555;
	font-size: 86%;
	border-radius: 5px;
	border: 1px solid #999;
}
.btn_cancel:hover {
	color: #999;
	border-color: #999;
	text-decoration: none;
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
<h1>ひと言掲示板 管理者ページ（投稿の編集）</h1>
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
        <input id="view_name" type="text" name="view_name" value="<?php if( !empty($message_data['view_name']) ){ echo $message_data['view_name']; } ?>">
        <!-- labelのfor method とinputのidが結びつき転送している-->
        <!-- PHPで入力データを判別するためのname属性と、label要素に関連づけるためのid属性を同じように指定します。 -->
    </div>
    <!-- ひと言メッセージを入力するエリア -->
	<div>
		<label for="message">ひと言メッセージ</label>
		<textarea id="message" name="message"><?php if( !empty($message_data['message']) ){ echo $message_data['message']; } ?></textarea>
        <!-- textarea要素は複数行のテキストを入力するためのフォームです。
先ほどのinput要素は1行分のなのでたくさんのテキストを入力したいときはやや不便、こちらの要素であれば改行をしても快適に入力することができます。 -->
	</div>
  <a class="btn_cancel" href="admin.php">キャンセル</a>
    <!-- フォームに入力されたデータを送信するボタン -->
	<input type="submit" name="btn_submit" value="更新">
    <!-- value属性が登場しましたが、ここで指定した名前がボタンのラベルとして表示されます。 -->
    <input type="hidden" name="message_id" value="<?php echo $message_data['id']; ?>">
    <!--input要素のtype属性を「hidden」にして非表示の項目-->
</form>
</body>
</html>