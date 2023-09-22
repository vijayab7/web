<?php
require_once(dirname(__FILE__) . '/../config/config.php');
require_once(dirname(__FILE__) . '/../function.php');
try {
    session_start();

    if (!isset($_SESSION['USER']) || $_SESSION['USER']['auth_type'] != 1) {
        redirect('/admin/login.php');
    }

    $pdo = connect_db();

    $sql = "SELECT * from user where auth_type = '0'";
    $stmt = $pdo->query($sql);
    $user_list = $stmt->fetchAll();
    $page_title = '社員一覧';
    // var_dump($user_list['id']);
    // exit;

} catch (Exception $e) {
    redirect('/error.php');
}
?>
<!doctype html>
<html lang="ja">

<?php include('../templates/head_tag.php') ?>

<body class="text-center bg-primary">

<?php include('../templates/user_header.php') ?>

    <form class="border rounded bg-white form-user-list" action=index.php>
        <h1 class="h3 my-3">社員一覧</h1>
        <div style="margin-left: 445px;">
            <a href="/admin/user_logout.php"><button type="button"
                    class="btn btn-secondary rounded-pill px-3">ログアウト</button></a>
        </div>
        <p> </p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">社員番号</th>
                    <th scope="col">社員名</th>
                    <!-- <th scope="col">権限</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_list as $user): ?>
                    <tr>
                        <td scope="row">
                            <?= $user['user_no'] ?>
                        </td>
                        <td><a href="/admin/user_result.php?id=<?= $user['id'] ?>">
                                <?= decryptWithFixedKey($user['name']) ?>
                            </a></td>
                        <!-- <td scope="row"><?php if ($user['auth_type'] == 1)
                            echo '管理者' ?></td> -->
                        </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>

    <!-- Option 2: jQuery, Popper.js, and Bootstrap JS
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
    -->
</body>

</html>