<?php
include('../assets/functions.php');
$title = '管理画面';
include('../assets/_inc/header.php');
include('nav.php');
$company = read_companyData($_SESSION['id']);
?>
    <main>
        <h1><?= $title ?></h1>
        <div>
            <table>
                <thead>
                <tr>
                    <th colspan="2"><?= $name = h($company['name']) ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?= h($company['address_first']) . h($company['address_second']) . h($company['address_third']) ?></td>
                    <td>TEL：<a href="tel:<?= h($company['tel']) ?>"><?= h($company['tel']) ?></a></td>
                </tr>
                <tr>
                    <td>
                        <iframe src=" https://maps.google.co.jp/maps?output=embed&q=<?= $name ?>"></iframe>
                    </td>
                    <td><?= h($company['details']) ?></td>
                </tr>
                </tbody>
            </table>
            <div>

            </div>
            <a href="">企業データの編集</a>
            <h2>落とし物一覧</h2>
            <a href="register.php">新規登録</a>
            <?php if (read_objectList($_SESSION['id'])) { ?>
                <table>
                    <thead>
                    <tr>
                        <th>名前</th>
                        <th>詳細</th>
                        <th>ジャンル</th>
                        <th>登録日時</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (read_objectList($_SESSION['id']) as $row): ?>
                        <form name="edit<?= h($row['id']) ?>" action="register.php" method="get">
                            <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        </form>
                        <tr>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['details']) ?></td>
                            <td><?= h($row['category']) ?></td>
                            <td><?= h($row['datetime']) ?></td>
                            <td class="edit" onClick="document.edit<?= h($row['id']) ?>.submit();return false;">編集</td>
                            <td class="delete">削除</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>落とし物が登録されていません。</p>
            <?php } ?>
        </div>
    </main>
    <script src="../assets/js/jquery-3.5.1.js"></script>
    <script src="../assets/js/company.js"></script>
<?php include('../assets/_inc/footer.php') ?>