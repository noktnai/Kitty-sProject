<?php
include_once("../assets/functions.php"); //include_once -> result.php内でfunction読み込みの為

$company_name = isset($_GET['name']) ? $_GET['name'] : 0; // 必須
$category = isset($_GET['categories']) ? $_GET['categories'] : 0; // 必須
if ($company_name) {
} else if ($category) {
    $object_name = empty($_GET['objects']) ? 0 : $_GET['objects']; // 任意
    $prefecture = $_GET['prefectures']; // 必須
    $city = $_GET['cities']; // 必須
    $town = empty($_GET['towns']) ? 0 : $_GET['towns']; // 任意
    $datetime = empty($_GET['towns']) ? 0 : $_GET['towns']; // 任意
    if ($datetime) {
        $datetime_first = $datetime . ' 00:00:00'; // 該当日の午前0時
        $datetime_last = $datetime . ' 23:59:59'; // 該当日の午後23時59分
    }
} else {
    alert('不正なアクセスです', 'CAUTION');
    header('Location:top.php');
    exit;
}

/* 検索-関数 */ // 未着手
if ($prefecture) {
    if ($prefecture !== '都道府県を選択してください') {
        $query .= " and prefecture=:prefecture and city=:city and town  like :town";
        $value = array_merge(array(
            ":prefecture" => $prefecture,
            ":city" => $city,
            ":town" => $town . "%",
        ));
        array_push($keywords, $prefecture . $city . $town);
    }
    if ($category !== "カテゴリーを選択してください") {
        $query .= "  and category=:category";
        $value = array_merge($value, array("category" => $category));
        array_push($keywords, $category);
    }
    $_SESSION['data']['keywords'] = $keywords;
    $_SESSION['data']['results'] = searchCompanies($query, $value);
} else {
    alert('不正なアクセスです', 'CAUTION');
}
header("Location:result.php");

/**
 * @param string $plus_query
 * @param array $plus_value
 * @return array|int
 */
function searchCompanies(string $plus_query, array $plus_value)
{
    global $name;
    $query = "select distinct companies.id, companies.name, companies.details from objects join companies on objects.company_id = companies.id where (companies.name like :name or objects.name like :name)" . $plus_query . " order by objects.datetime desc";
    $value = array_merge(array(":name" => $name), $plus_value);
    print_r($value);
    try {
        $pdo = getPDO(); //pdo取得
        //$pdo->query("set session sql_mode=(select replace(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        $stmt = $pdo->prepare($query);
        $stmt->execute($value);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        alert($e . 'データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}
