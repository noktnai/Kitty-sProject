<?php
include('../assets/functions.php');
$id = isset($_POST['id']) ? $_POST['id'] : 0;

$id ? connect($id, "delete from objects where id = ?") : alert('不正なアクセスです', 'CAUTION');

header('Location:management.php');

function deleteObject()
{
    global $id;
    try {
        $pdo = getPDO(); //pdo取得
        $stmt = $pdo->prepare("delete from objects where id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        alert('落し物の削除が完了しました', 'SUCCESS');
    } catch (PDOException $e) {
        alert('データベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
}
