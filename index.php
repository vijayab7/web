<?php
require_once(dirname(__FILE__) . '/config/config.php');
require_once(dirname(__FILE__) . '/function.php');

try {

    session_start();



    if (!isset($_SESSION['USER'])) {
        redirect('/login.php');
    }

    $session_user = $_SESSION['USER'];

    $pdo = connect_db();

    $err = array();
    $target_date = ""; 
    if(!$target_date){
        $target_date = date('Y-m-d');
    }
     //$target_date = date('Y-m-d');

      // $sql = "SELECT * from work where user_id=:user_id AND date = :date LIMIT 1";
    // $stmt = $pdo->prepare($sql);
    // $stmt->bindValue(':user_id', (int) $session_user['id'], PDO::PARAM_INT);
    // $stmt->bindValue(':date', $target_date, PDO::PARAM_STR);
    // $stmt->execute();
    // $today = $stmt->fetch();
    // if (!$today) {
    //     $modal_view_flg = TRUE;
    // }
   

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $modal_view_flg = TRUE;
        $target_date = $_POST['target_date'];
        // var_dump($target_date);
        // exit;
        

        // if ($_POST['target_date']) {
        //     $target_date = $_POST['target_date'];
        // } else {
        //     $target_date = date('Y-m-d');
        // }

        $modal_start_time = $_POST['modal_start_time'];
        $modal_end_time = $_POST['modal_end_time'];
        $modal_break_time = $_POST['modal_break_time'];
        $modal_comment = $_POST['modal_comment'];

        if (!$modal_start_time) {
            $err['modal_start_time'] = '出勤を入力してください。';
        } elseif (!check_time_format($modal_start_time)) {
            $err['modal_start_time'] = '出勤を正しく入力してください。';
        }
        if (!$modal_end_time) {
            $err['modal_end_time'] = '退勤を入力してください。';
        } elseif (!check_time_format($modal_end_time)) {
            $err['modal_end_time'] = '退勤を正しく入力してください。';
        }
        if (!$modal_break_time) {
            $err['modal_break_time'] = '休憩を入力してください。';
        } elseif (!check_time_format($modal_break_time)) {
            $err['modal_break_time'] = '休憩を正しく入力してください。';
        }
        if (mb_strlen($modal_comment, 'utf-8') > 100) {
            $err['modal_comment'] = '業務内容が長すぎます。';
        }

        if (empty($err)) {
            $sql = "SELECT id from work where user_id=:user_id AND date = :date LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':user_id', (int) $session_user['id'], PDO::PARAM_INT);
            $stmt->bindValue(':date', $target_date, PDO::PARAM_STR);
            $stmt->execute();
            $work = $stmt->fetch();

            if ($work) {
                $sql = "UPDATE work SET start_time = :start_time, end_time = :end_time,break_time = :break_time,comment = :comment where id=:id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', (int) $work['id'], PDO::PARAM_INT);
                $stmt->bindValue(':start_time', $modal_start_time, PDO::PARAM_STR);
                $stmt->bindValue(':end_time', $modal_end_time, PDO::PARAM_STR);
                $stmt->bindValue(':break_time', $modal_break_time, PDO::PARAM_STR);
                $stmt->bindValue(':comment', $modal_comment, PDO::PARAM_STR);
                $stmt->execute();
                $work = $stmt->fetch();
            } else {

                $sql = "INSERT INTO work (user_id,date,start_time,end_time,break_time,comment)
                values(:user_id,:date,:start_time,:end_time,:break_time,:comment)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':user_id', (int) $session_user['id'], PDO::PARAM_INT);
                $stmt->bindValue(':date', $target_date, PDO::PARAM_STR);
                $stmt->bindValue(':start_time', $modal_start_time, PDO::PARAM_STR);
                $stmt->bindValue(':end_time', $modal_end_time, PDO::PARAM_STR);
                $stmt->bindValue(':break_time', $modal_break_time, PDO::PARAM_STR);
                $stmt->bindValue(':comment', $modal_comment, PDO::PARAM_STR);
                $stmt->execute();
                $work = $stmt->fetch();
            }
            $modal_view_flg = FALSE;

        }

    } else {

        $sql = "SELECT date,id,start_time,end_time,break_time,comment from work where user_id=:user_id AND date = :date LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int) $session_user['id'], PDO::PARAM_INT);
        $stmt->bindValue(':date', date('Y-m-d'), PDO::PARAM_STR);
        $stmt->execute();
        $today_work = $stmt->fetch();

        if ($today_work) {
            $modal_start_time = $today_work['start_time'];
            $modal_end_time = $today_work['end_time'];
            $modal_break_time = $today_work['break_time'];
            $modal_comment = $today_work['comment'];

            if ($modal_start_time != "00:00:00" && $modal_end_time != "00:00:00") {
                $modal_view_flg = FALSE;
            }
        } else {
            $modal_start_time = '';
            $modal_end_time = '';
            $modal_break_time = '01:00';
            $modal_comment = '';
        }
    }

    if (isset($_GET['m'])) {

        $yyyymm = $_GET['m'];
        $day_count = date('t', strtotime($yyyymm));
        if (count(explode('-', $yyyymm)) != 2) {
            throw new Exception('日付の指定が不正', 500);
        }
        $check_date = new DateTime($yyyymm . '-01');
        $start_date = new DateTime('first day of -11 month 00:00');
        $end_date = new DateTime('first day of this month 00:00');

        if ($check_date < $start_date || $end_date < $check_date) {
            throw new Exception('日付の範囲が不正', 500);
        }
        if ($check_date != $end_date) {
            $modal_view_flg = False;
        }
    } else {
        $yyyymm = date('Y-m');
        $day_count = date('t');
    }

    $sql = "SELECT date,id,start_time,end_time,break_time,comment from work where user_id= :user_id AND DATE_FORMAT(date,'%Y-%m') = :date";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', (int) $session_user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':date', $yyyymm, PDO::PARAM_STR);
    $stmt->execute();
    $work_list = $stmt->fetchAll(PDO::FETCH_UNIQUE);

    if($yyyymm == date('Y-m')){
       $sql = "SELECT * from work where user_id=:user_id AND date = :date LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', (int) $session_user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':date', $target_date, PDO::PARAM_STR);
    $stmt->execute();
    $today = $stmt->fetch();
    if (!$today) {
        $modal_view_flg = TRUE;
    }
}
    $page_title = '日報登録';
} catch (Exception $e) {
    redirect('/error.php');
}

?>
<!doctype html>
<html lang="ja">
<?php include('templates/head_tag.php') ?>

<body class="text-center bg-secondary">
    <?php include('templates/header.php') ?>
    <form class="border rounded bg-white form-time-table" action=index.php>
        <h1 class="h3 my-3">月別リスト</h1>
        <div class="float-left">
            <select class="form-control rounded-pill mb-3" name="m" onchange="submit(this.form)">
                <option value="<?= date('Y-m') ?>">
                    <?= date('Y/m') ?>
                </option>
                <?php for ($i = 1; $i < 12; $i++): ?>
                    <?php $target_yyyymm = strtotime("-{$i}months"); ?>
                    <option value="<?= date('Y-m', $target_yyyymm) ?>" <?php if ($yyyymm == date('Y-m', $target_yyyymm))
                           echo 'selected' ?>>
                        <?= date('Y/m', $target_yyyymm) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="float-right">
            <a href="logout.php"><button type="button" class="btn btn-primary rounded-pill px-5">ログアウト</button></a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr class="bg-light">
                    <th class="fix-col">日</th>
                    <th class="fix-col">出勤</th>
                    <th class="fix-col">退勤</th>
                    <th class="fix-col">休憩</th>
                    <th>業務内容</th>
                    <th class="fix-col"></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= $day_count; $i++): ?>
                    <?php
                    $starttime = "";
                    $end_time = "";
                    $break_time = "";
                    $comment = "";

                    if (isset($work_list[date('Y-m-d', strtotime($yyyymm . '-' . $i))])) {

                        $work = $work_list[date('Y-m-d', strtotime($yyyymm . '-' . $i))];
                        if ($work['start_time']) {
                            $starttime = date('H:i', strtotime($work['start_time']));
                        }
                        if ($work['end_time']) {
                            $end_time = date('H:i', strtotime($work['end_time']));
                        }
                        if ($work['break_time']) {
                            $break_time = date('H:i', strtotime($work['break_time']));
                        }
                        if ($work['comment']) {
                            $comment = mb_strimwidth($work['comment'], 0, 20, '...');
                        }
                    }
                    ?>
                    <tr>
                        <th scope="row">
                            <?= time_format_dw($yyyymm . '-' . $i) ?>
                        </th>
                        <td>
                            <?= $starttime ?>
                        </td>
                        <td>
                            <?= $end_time ?>
                        </td>
                        <td>
                            <?= $break_time ?>
                        </td>
                        <td>
                            <?= h($comment) ?>
                        </td>
                        <td><button type="button" class="btn btn-default h-auto py-0" data-toggle="modal"
                                data-target="#inputModal" data-day="<?= $yyyymm . '-' . sprintf('%02d', $i) ?>"><img
                                    class="mb-4" src="img/pencil_686119.png" width="20" height="20"></button></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </form>


    <!-- Modal -->
    <form method="POST">
        <div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <p></p>
                        <h5 class="modal-title" id="exampleModalLabel">日報登録</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="alert alert-primary" role="alert">
                                <?= date('n', strtotime($yyyymm)) ?> /<span id="modal_day">
                                    <?= time_format_dw($target_date) ?>
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-sm">
                                    <div class="input-group">
                                        <input type="text" class="form-control <?php if (isset($err['modal_start_time']))
                                            echo 'is-invalid'; ?> " placeholder="出勤" id="modal_start_time"
                                            name="modal_start_time" value="<?= format_time($modal_start_time) ?>">
                                        <div class="input-group-prepend">
                                            <button type="button" class="input-group-text" id="start_btn">打刻</button>
                                        </div>
                                        <div class="invalid-feedback">
                                            <?= $err['modal_start_time'] ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="input-group">
                                        <input type="text" class="form-control <?php if (isset($err['modal_end_time']))
                                            echo 'is-invalid'; ?> " placeholder="退勤" id="modal_end_time"
                                            name="modal_end_time" value="<?= format_time($modal_end_time) ?>">
                                        <div class="input-group-prepend">
                                            <button type="button" class="input-group-text" id="end_btn">打刻</button>
                                        </div>
                                        <div class="invalid-feedback">
                                            <?= $err['modal_end_time'] ?>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="input-group">
                                        <input type="text" class="form-control <?php if (isset($err['modal_break_time']))
                                            echo 'is-invalid'; ?> " placeholder="休憩" name="modal_break_time"
                                            id="modal_break_time" value="<?= format_time($modal_break_time) ?>">
                                        <div class="invalid-feedback">
                                            <?= $err['modal_break_time'] ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="form-group pt-3">
                                <textarea class="form-control <?php if (isset($err['modal_comment']))
                                    echo 'is-invalid'; ?> " name="modal_comment" id="modal_comment" rows="5"
                                    placeholder="業務内容"><?= $modal_comment ?></textarea>
                                <div class="invalid-feedback">
                                    <?= $err['modal_comment'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info text-white rounded-pill px-5">登録</button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="target_date" name="target_date" value= "<?= $target_date ?>">
    </form>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>
    <script>
        <?php if ($modal_view_flg): ?>
            var inputModal = new bootstrap.Modal(document.getElementById('inputModal'));
            inputModal.toggle();
        <?php endif; ?>
        $('#start_btn').click(function () {
            const now = new Date();
            const hour = now.getHours().toString().padStart(2, '0');
            const minute = now.getMinutes().toString().padStart(2, '0');
            $('#modal_start_time').val(hour + ':' + minute)
        })

        $('#end_btn').click(function () {
            const now = new Date();
            const hour = now.getHours().toString().padStart(2, '0');
            const minute = now.getMinutes().toString().padStart(2, '0');
            $('#modal_end_time').val(hour + ':' + minute)
        })

        $('#inputModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var target_day = button.data('day')
            //console.log(target_day)

            var day = button.closest('tr').children('th')[0].innerText
            var start_time = button.closest('tr').children('td')[0].innerText
            var end_time = button.closest('tr').children('td')[1].innerText
            var break_time = button.closest('tr').children('td')[2].innerText
            var comment = button.closest('tr').children('td')[3].innerText

            $('#modal_day').text(day)
            $('#modal_start_time').val(start_time)
            $('#modal_end_time').val(end_time)
            $('#modal_break_time').val(break_time)
            $('#modal_comment').val(comment)
            $('#target_date').val(target_day)

            $('#modal_start_time').removeClass('is-invalid')
            $('#modal_end_time').removeClass('is-invalid')
            $('#modal_break_time').removeClass('is-invalid')
            $('#modal_comment').removeClass('is-invalid')
        })
    </script>

</body>

</html>