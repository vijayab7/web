<?php
 require_once(dirname(__FILE__).'/../config/config.php');
 require_once(dirname(__FILE__).'/../function.php');
 try{
 session_start();

 if(isset($_SESSION['USER']) && $_SESSION['USER']['auth_type'] == 1){
  header('Location:/admin/user_list.php');
  exit;
 }
$err = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  check_token();
  //入力値を取得
  $user_no = $_POST['user_no'];
  $password = $_POST['password'];

  if (!$user_no) {
    $err['user_no'] = '管理者番号を入力してください。';
  }elseif(!preg_match('/^[0-9]+$/',$user_no)){
    $err['user_no'] = '社員番号を正しく入力してください。';
  }elseif(mb_strlen($user_no,'utf-8') >20){
    $err['user_no'] = '社員番号が長すぎます。';
  }

  if (!$password) {
    $err['password'] = 'パスワードを入力してください。';
  }
  if(empty($err)){
    $pdo = connect_db();

    $sql = "SELECT * from user where user_no=:user_no AND auth_type = 1 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_no',$user_no,PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();
    // var_dump($user);
    // exit;
    if($user && password_verify($password,$user['password'])){
    $_SESSION['USER'] =$user;
    header('Location:/admin/user_list.php');
  exit;

   
  }else{
    $err['password'] = '認証に失敗しました。';
  }

  }

} else {

  $user_no = "";
  $password = "";
  set_token();

}
}catch(Exception $e){
  header('Location:/error.php');
  exit;
  }
?>
<!doctype html>
<html lang="ja">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">

  <!-- Original CSS-->
  <link rel="stylesheet" href="/css/style.css">


  <title>ログイン</title>
</head>

<body class="text-center bg-primary">
  <div>
    <img class="mb-4" src="../img/logo.svg" width="80" height="80">
  </div>
  <form class="border rounded bg-white form-login" method="post">
    <h1 class="h3 my-3">Login</h1>
    <div class="form-group pt-3">
      <input type="text" class="form-control rounded-pill <?php if (isset($err['user_no']))
        echo 'is-invalid'; ?>"
        name="user_no" value="<?= $user_no ?>" placeholder="社員番号" Required>
      <div class="invalid-feedback">
        <?= $err['user_no'] ?>
      </div>
    </div>
    <div class="form-group">
      <input type="password" class="form-control rounded-pill <?php if (isset($err['password']))
        echo 'is-invalid'; ?>"
        name="password" placeholder="パスワード">
      <div class="invalid-feedback">
        <?= $err['password'] ?>
      </div>
    </div>
    <button type="submit" class="btn btn-info text-white rounded-pill px-5 my-4">ログイン</button>
    <input type="hidden" name="CSRF_TOKEN" value="<?= $_SESSION['CSRF_TOKEN']?>">

  </form>

  <!-- Optional JavaScript; choose one of the two! -->

  <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
    integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
    crossorigin="anonymous"></script>

  
</body>

</html>