<?php
include('../assets/functions.php');

$id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$company = readCompanyData($id);
$objects = readObjectList($id);
$title = '管理画面';
include('../assets/_inc/header.php');
?>
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><?= h($company['name']) ?></h3>
                </div>
                <div class="card-body d-none d-sm-block">
                    <div class="row">
                    <div class="col-6">
                        <h5 class="mb-0 card-title " >住所</h5>
                        <p class="card-text">〒&nbsp;<?= h($company['postal']) . "<br>" . h($company['prefecture']) . h($company['city']) . h($company['town']) ?></p>
                        <h5 class="mb-0 card-title ">電話番号</h5>
                        <p class="card-text">TEL：<a href="tel:<?= str_replace('-', '', h($company['tel']))?>"><?= h($company['tel']) ?></a></p>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0 card-title ">営業時間等</h5>
                        <form name="opening-hours">
                        <textarea id ="details" class="w-100" rows="4"><?= h($company['details']); ?></textarea>
                        </form>
                        <button type = "button" class = "btn btn-success  btn-sm "  onclick="update()">更新</ button>
                    </div>
                    </div>
                    <iframe class ="mt-2 "width="100%" height="100%"
                            src=" https://maps.google.co.jp/maps?output=embed&q=<?= h($company['name']) ?>"></iframe>
                </div>
                <div class="card-body d-block d-sm-none">
                    <p class="card-text">
                        この画面ではご利用になれません
                    </p>
                </div>
            </div>
            <div class="card my-4 d-none d-sm-block">
                <div class="card-header">
                    <h4 class="card-title mb-0 d-inline"> 拾得物一覧 ／ </h4><h4 class="d-inline"><a href="register.php" class="text-decoration-none text-primary">新規追加</a></h4>
                </div>
                <div class="card-body">
                    <?php if ($objects): ?>
                        <table id="objects_table" class="table w-100">
                            <thead>
                            <tr>
                                <th>名前</th>
                                <th>発見日時</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($objects as $row): ?>
                                <tr class="edit list-group-item-action" data-href="register.php?id=<?= h($row['id']) ?>">
                                    <td><?= h($row['name']) ?></td>
                                    <td><?= date('Y年m月d日 H時i分', strtotime(h($row['datetime']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="mb-0">落とし物が登録されていません。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
<?php include('../assets/_inc/footer.php') ?>