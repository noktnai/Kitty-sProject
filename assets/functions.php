<?php
session_start();
if (preg_match('/functions/', $_SERVER['PHP_SELF'])) {
    alert('不正なアクセスです', 'CAUTION');
    header('Location:../index.php');
}

//都道府県
$regions = ["北海道" => ["北海道"], '東北' => ['青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県'], '関東' => ['茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県'], '中部' => ['新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県'], '近畿' => ['三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県'], '中国' => ['鳥取県', '島根県', '岡山県', '広島県', '山口県'], '四国' => ['徳島県', '香川県', '愛媛県', '高知県'], '九州' => ['福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']];
//カテゴリー
$categories = ['現金', '財布', '鍵', '携帯電話', '証明書・カード類', 'アクセサリー類', '時計類', '雨具類', '衣類・眼鏡', '食料品類', '医療・化粧品類', 'その他'];

//pdoの取得
function getPDO()
{
    static $pdo;
    $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
    if (preg_match('/heroku/', $_SERVER['HTTP_HOST'])) {
        $pdo = new PDO('mysql:host=us-cdbr-east-02.cleardb.com;dbname=heroku_c356dc99892b5ac', 'b4f8ee3200a178', '41b5b3b6', $option);
    } else {
        $pdo = new PDO('mysql:host=localhost;dbname=kittydb', 'kitty', 'pro02', $option);
    }
    return $pdo;
}

//XSS対策
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}
/**
 * @param string $target php_self
 */
function getNav($target)
{
    static $nav;
    if (preg_match('/authentication/', $target)) {
        $nav = [['link' => '../../index.php', 'name' => 'サイト']];
    } elseif (preg_match('/company/', $target)) {
        $nav = [['link' => 'management.php', 'name' => '管理画面'], ['link' => '../../index.php', 'name' => 'サイト'], ['link' => 'authentication/login.php', 'name' => 'ログアウト']];
    } else {
        $nav = [['link' => 'index.php', 'name' => '検索TOP'], ['link' => 'description.php', 'name' => 'サイト概要'], ['link' => 'company/management.php', 'name' => '企業様']];
    };
    return $nav;
}

/**
 * @param string $message alert message
 * @param string $type SUCCESS or ERROR or CAUTION
 */
function alert(string $message, string $type)
{
    unset($_SESSION['alert']);
    static $alert;
    switch ($type) {
        case 'SUCCESS':
            $alert = ['message' => $message, 'class' => 'alert alert-success', 'continue' => true];
            break;
        case 'CAUTION':
            $alert = ['message' => $message, 'class' => 'alert alert-warning', 'continue' => true];
            break;
        case 'ERROR':
            $alert = ['message' => $message, 'class' => 'alert alert-danger', 'continue' => false];
            break;
    }
    $_SESSION['alert'] = $alert;
}
/**
 * 1件取得
 * @param mixed $param 必要なパラメーター
 * @param string $query 実行するsql構文
 * @return int|array 0 or 配列
 */

function read($param, $query)
{
    if (!is_array($param)) $param = [$param];
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare($query);
        $stmt->execute($param);
        $result = $stmt->fetch();
        if ($result) return $result;
    } catch (PDOException $e) {
        alert('データーベース接続エラー'.$e, 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}
/**
 * 複数取得
 * @param mixed $param 必要なパラメーター
 * @param string $query 実行するsql構文
 * @return int|array 0 or 多次元配列
 */
function readAll($param, $query)
{
    if (!is_array($param)) $param = [$param];
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare($query);
        $stmt->execute($param);
        $result = $stmt->fetchAll();
        if ($result) return $result;
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}
/**
 * 挿入|更新|削除
 * @param mixed $param 必要なパラメーター
 * @param string $query 実行するsql構文
 */
function connect($param, string $query)
{
    if (!is_array($param)) $param = [$param];
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare($query);
        $stmt->execute($param);
        return 1;
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
        return 0;
    } finally {
        unset($pdo);
    }
}

/**
 * 企業情報の取得
 */
function readCompany(int $id)
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("select * from companies where id=:id limit 1");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) return $result;
        alert('不正なアクセスです', 'CAUTION');
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}

/**
 * 落とし物全件の取得
 * @param int $id 企業ID
 * @return array|int 落とし物|
 */
function readObjects(int $id)
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("select * from objects where company_id=:company_id");
        $stmt->bindValue(":company_id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}

function readObjectListFromCategory(string $category)
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("select distinct name from objects where category=:category");
        $stmt->bindValue(":category", $category, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}

//拾得物の呼び出し
function readObjectData(int $id)
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("select * from objects where id =:id limit 1");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result and ($result['company_id'] === $_SESSION['id'])) return $result;
        alert('不正なアクセスです', 'CAUTION');
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}

//仮登録情報の呼び出し
function readPreCompanyData(string $token)
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("select mail from pre_companies where token = :token and datetime > now() - interval 24 hour limit 1");
        $stmt->bindValue(":token", $token, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) return $result['mail'];
        alert('無効なURLです', 'CAUTION');
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}

function updateDetails(string $details, int $id)
{
    //detailsを更新
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("update companies set details = :details  where id = :id");
        $stmt->execute(array(":details" => $details, ":id" => $id));
        alert('更新が完了しました', 'SUCCESS');
    } catch (PDOException $e) {
        alert('データーベース接続エラー', 'ERROR');
    } finally {
        unset($pdo);
    }
    return 0;
}

class CompanyTable
{
    public $id;
    public $name;
    public $tel;
    public $postal;
    public $prefecture;
    public $city;
    public $town;
    public $details;
    public $mail;
    public $password;
    public $objects;

    function __construct($id)
    {
        if (is_numeric($id)) {
            $company = $this->getCompany($id);
            if ($company) {
                foreach ($this as $key => $value) {
                    $this->$key = $company[$key];
                }
            }
        } else {
            $this->id = null;
        }
    }
    //企業情報の呼び出し
    private function getCompany(int $id)
    {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("select * from companies where id=:id limit 1");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result) return $result;
            alert('不正なアクセスです', 'CAUTION');
        } catch (PDOException $e) {
            alert('データーベース接続エラー', 'ERROR');
        } finally {
            unset($pdo);
        }
        return 0;
    }
}

class ObjectTable
{
}

class Company
{
    static function readFromId(int $id)
    {

        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("select * from companies where id=:id limit 1");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result) return $result;
            alert('不正なアクセスです', 'CAUTION');
        } catch (PDOException $e) {
            alert('データーベース接続エラー', 'ERROR');
        } finally {
            unset($pdo);
        }
        return 0;
    }

    static function readAllFromName(string $name)
    {
        $name = '%' . $name . '%';
        try {
            $pdo = getPDO(); //pdo取得
            $stmt = $pdo->prepare("select id, name, details from companies where name like :name limit 10");
            $stmt->bindValue(":name", $name, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            alert('データーベース接続エラー', 'ERROR');
        } finally {
            unset($pdo);
        }
        return 0;
    }

    static function readAllFromArea()
    {
    }

    static function delete()
    {
    }

    static function write()
    {
    }

    static function update()
    {
    }
}
class Item
{
    /**
     * 落とし物IDよりデータ1件を取得
     */
    static function read(int $id)
    {
    }

    /**
     * 企業IDよりデータ全件を取得
     */
    static function readAll(int $id)
    {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("select * from objects where company_id=:company_id");
            $stmt->bindValue(":company_id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            alert('データーベース接続エラー', 'ERROR');
        } finally {
            unset($pdo);
        }
        return 0;
    }
}
