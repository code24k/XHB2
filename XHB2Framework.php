<?php
/**
 * XHB2 framework
 * 本框架可以免费用于个人、商业项目，但禁止二次修改、打包再发布。
 * 包括但不限于申请著作权等提交代码不得包含本框架。
 * 开源仓库地址：
 * https://gitee.com/code24k/XHB2
 * https://github.com/code24k/XHB2
 * 
 * 启动实例
 * date_default_timezone_set("Asia/Shanghai");
 * header("Content-type: text/html; charset=utf-8");
 * $application = new \XHB2\Application();
 * $application->Run();
 */
namespace XHB2;

$GLOBALS["XHB2"]["RootPath"] = dirname(__DIR__);
$GLOBALS["XHB2"]["FrameworkPath"] = $GLOBALS["XHB2"]["RootPath"]."/Framework";
$GLOBALS["XHB2"]["StoragePath"] = $GLOBALS["XHB2"]["RootPath"]."/App/Storage";
$GLOBALS["XHB2"]["CachePath"] = $GLOBALS["XHB2"]["StoragePath"]."/Cache"; 
$GLOBALS["XHB2"]["LogPath"] = $GLOBALS["XHB2"]["StoragePath"]."/Log";
$GLOBALS["XHB2"]["CookiePath"] = "/";
$GLOBALS["XHB2"]["CookieDomain"] = $_SERVER["SERVER_NAME"];
$GLOBALS["XHB2"]["SessionPath"] = $GLOBALS["XHB2"]["StoragePath"]."/Session";
$GLOBALS["XHB2"]["SessionValidity"] = 40;
$GLOBALS["XHB2"]["SessionID"] = md5($GLOBALS["XHB2"]["RootPath"]);
$GLOBALS["XHB2"]["ConfigPath"] = $GLOBALS["XHB2"]["RootPath"]."/App/Config";
$GLOBALS["XHB2"]["TranslatePath"] = $GLOBALS["XHB2"]["RootPath"]."/App/Translate";

if (!function_exists("StatusSuccess")) {
    function StatusSuccess($message = "success", $data = []) {
        return [
            "Code" => 1,
            "Message" => $message,
            "Data" => $data
        ];
    }
}
if (!function_exists("StatusFailure")) {
    function StatusFailure($message = "failure", $data = []) {
        return [
            "Code" => 0,
            "Message" => $message,
            "Data" => $data
        ];
    }
}
if (!function_exists("DD")) {
    function DD($var) {
        if ($var === null) 
            exit("null");
        if (is_bool($var)) {
            if ($var == true) exit("true"); else exit("false");
        }
        echo "<pre>";
        print_r($var);
        echo "</pre>";
        exit();
    }
}
if (!function_exists("Error")) {
    function Error($error){
        $errorLog = "<br />[".date("Y/m/d H:i:s")."] ERROR:".$error;
        echo $error;
        $errorLog = str_replace("<br />", "\n",$errorLog);
        \XHB2\File::Instance()->WriteStorageLog($errorLog);
        exit();
    }
}
if (!function_exists("URL")) {
    function URL($language, $path){
        $config = Config("Route");
        if($config["Default"] == "ParamterRoute"){
            return "/?{$language}-{$path}";
        }
        return "/";
    }
}
if (!function_exists("TranslatePath")) {
    function TranslatePath(){
        return $GLOBALS["XHB2"]["TranslatePath"];
    }
}
if (!function_exists("ConfigPath")) {
    function ConfigPath(){
        return $GLOBALS["XHB2"]["ConfigPath"];
    }
}
if (!function_exists("RootPath")) {
    function RootPath(){
        return $GLOBALS["XHB2"]["RootPath"];
    }
}
if (!function_exists("StoragePath")) {
    function StoragePath(){
        if (!is_dir($GLOBALS["XHB2"]["StoragePath"])) {
            mkdir($GLOBALS["XHB2"]["StoragePath"], "0777", true);
        }
        return $GLOBALS["XHB2"]["StoragePath"];
    }
}
if (!function_exists("LogPath")) {
    function LogPath(){
        if (!is_dir($GLOBALS["XHB2"]["LogPath"])) {
            mkdir($GLOBALS["XHB2"]["LogPath"], "0777", true);
        }
        return $GLOBALS["XHB2"]["LogPath"];
    }
}
if (!function_exists("CachePath")) {
    function CachePath(){
        if (!is_dir($GLOBALS["XHB2"]["CachePath"])) {
            mkdir($GLOBALS["XHB2"]["CachePath"], "0777", true);
        }
        return $GLOBALS["XHB2"]["CachePath"];
    }
}
if (!function_exists("SessionPath")) {
    function SessionPath(){
        if (!is_dir($GLOBALS["XHB2"]["SessionPath"])) {
            mkdir($GLOBALS["XHB2"]["SessionPath"], "0777", true);
        }
        return $GLOBALS["XHB2"]["SessionPath"];
    }
}
if (!function_exists("Output")) {
    function Output($str) {
        echo $str;
    }
}
if (!function_exists("Value")) {
    function Value($value) {
        return $value instanceof \Closure ? $value() : $value;
    }
}
if (!function_exists("Config")) {
    function Config($configName) {
        $config = require ConfigPath()."/{$configName}.Config.php";
        if(is_array($config))
            return $config;
        else
            return [];
    }
}
if (!function_exists("Translate")) {
    function Translate($translate, $key) {
        $config = require TranslatePath()."/{$translate}.Translate.php";
        if(array_key_exists($key, $config)){
            return $config[$key];
        }
        return "{".$key."}";
    }
}

function CreateId() {
    $lockFile = CachePath() . "/CreateId.lock";
    $fp = fopen($lockFile, "w");
    if (flock($fp, LOCK_EX)) {
        try {
            $id = (int) (microtime(true) * 10000);
            usleep(1);
        } finally {
            flock($fp, LOCK_UN);
        }
    } else {
        $id = (int) (microtime(true) * 10000) + rand(1, 1000);
    }
    fclose($fp);
    return $id;
}

class View {

    public static function Instance() {
        return new self();
    }

    public static function Make($ui, $assign = array()) {
        $uiName = str_replace(".","/", $ui);
        $tplpath = RootPath()."/App/View/{$uiName}.Template.php";
        if (!file_exists($tplpath)) {
            return Error("模板不存在{$tplpath}");
        }
        if (count($assign)) {
            foreach ($assign as $key => $var) {
                $$key = $var;
            }
        }
        include_once $tplpath;
    }

    public function Output($container) {
        return file_put_contents("php://output", $container);
    }

}

class Header {

    public static function Instance() {
        return new self();
    }

    public function HeaderJson() {
        header("Content-type: application/json");
    }

    public function AccessControl() {
        header("Access-Control-Allow-Origin:*");
    }

    public function HeaderUtf8() {
        header("Content-type: text/html; charset=utf-8");
    }

    public function Header500() {
        $this->headerUtf8();
        return \XHB2\View::Instance()->Output("500");
    }

    public function Header404() {
        return \XHB2\View::Instance()->Output("<h1>404</h1>");
    }

    public function Header403() {
        return \XHB2\View::Instance()->Output("<h1>Forbidden 403</h1>");
    }

}

class Request {
    public $m_Module;
    public $m_Controller;
    public $m_Method;
    public $m_ParamGet = [];
    public $m_ParamPost = [];
    public $m_Translate;

    public function __construct(){
        $config = Config("Translate");
        $this->m_Translate = $config["Default"] ? $config["Default"] : "en";
    }

    public static function Instance(){
        return new self();
    }

    function GetRequestMethod() {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function Get($key) {
        return array_key_exists($key, $this->m_ParamGet)?$this->m_ParamGet[$key]:null;
    }

    public function Post($key) {
        return array_key_exists($key, $this->m_ParamPost)?$this->m_ParamPost[$key]:null;
    }

    public function PostArray() {
        return is_array($this->m_ParamPost) && count($this->m_ParamPost) > 0 ? $this->m_ParamPost : [];
    }

    public function Stream(){
        if(!array_key_exists("Stream", $this->m_ParamPost) || $this->m_ParamPost["Stream"] == ""){
            return [];
        }
        $base64Decode = base64_decode($this->m_ParamPost["Stream"]);
        if($base64Decode == ""){
            return [];
        }
        return json_decode($base64Decode, true);
    }

    public function GetTranslate(){
        return $this->m_Translate;
    }

    public function GetParseUrlParamIndex(int $id){
        $urlQuery = parse_url($this->GetCurrentUrl(), PHP_URL_QUERY);
        if($urlQuery === null || $urlQuery == ""){
            return "";
        }
        $urlArray = explode("-", $urlQuery);
        if(!is_array($urlArray) || count($urlArray) < 4){
            return "";
        }
        if($id > count($urlArray)){
            return "";
        }
        return $urlArray[$id];
    }

    function GetCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $path = $_SERVER['REQUEST_URI'];
        return $protocol . $host . $path;
    }

    public function GetParseUrlParams(string $url="") {
        if($url == ""){
            $url = $this->GetCurrentUrl();
        }
        $params = [];
        $query = parse_url($url, PHP_URL_QUERY);
        if (!$query) {
            return $params;
        }
        $paramPairs = explode('-', $query);
        foreach ($paramPairs as $pair) {
            $item = explode('@@', $pair);
            if (count($item) === 2) {
                $params[$item[0]] = $item[1];
            }
        }
        return $params;
    }

    public function GetParseUrlParam(string $key=""){
        $getParseUrlParams = $this->GetParseUrlParams();
        if(is_array($getParseUrlParams) && array_key_exists($key, $getParseUrlParams)){
            return $getParseUrlParams[$key];
        }
        return "";
    }

    function AppendUrlParams(array $params=[], string $url="") {
        if($url == ""){
            $url = $this->GetCurrentUrl();
        }
        $parsedUrl = parse_url($url);
        $currentParams = [];
        if (isset($parsedUrl['query'])) {
            $paramPairs = explode('-', $parsedUrl['query']);
            foreach ($paramPairs as $pair) {
                $item = explode('@@', $pair);
                if (count($item) === 2) {
                    $currentParams[$item[0]] = $item[1];
                }
            }
        }
        $allParams = array_merge($currentParams, $params);
        $newQuery = [];
        foreach ($allParams as $key => $value) {
            $newQuery[] = "{$key}@@{$value}";
        }
        $queryString = implode('-', $newQuery);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        if($urlQuery === null || $urlQuery == ""){
            return "";
        }
        $urlArray = explode("-", $urlQuery);
        if(!is_array($urlArray) || count($urlArray) < 4){
            return "";
        }
        $urlQuery = implode("-", array_slice($urlArray, 0, 4));
        if(count($newQuery) > 0){
            return "{$scheme}{$host}{$path}?{$urlQuery}-{$queryString}";
        }
        return "{$scheme}{$host}{$path}?{$urlQuery}";
    }
}

class Response {

    public static function Instance(){
        return new self();
    }

    public function Json($array = []) {
        \XHB2\Header::Instance()->HeaderJson();
        return json_encode($array);
    }

    public function Jsonp(Request $request, $array = []) {
        return "jsoncallback(" . json_encode($array) . ")";
    }

    public function ResetSeoSetting(){
        $this->SetSeoTitle();
        $this->SetSeoKeywords();
        $this->SetSeoDescription();
        $this->SetMenuTitle();
    }

    public function SetSeoTitle($title = "") {
        $GLOBALS["XHB2"]["Seo"]["Title"] = $title;
        return true;
    }

    public function GetSeoTitle() {
        return $GLOBALS["XHB2"]["Seo"]["Title"];
    }

    public function SetMenuTitle($title = "") {
        $GLOBALS["XHB2"]["Menu"]["Title"] = $title;
        return true;
    }

    public function GetMenuTitle() {
        return $GLOBALS["XHB2"]["Menu"]["Title"];
    }

    public function SetSeoKeywords($keywords = "") {
        $GLOBALS["XHB2"]["Seo"]["Keywords"] = $keywords;
        return true;
    }

    public function GetSeoKeywords() {
        return $GLOBALS["XHB2"]["Seo"]["Keywords"];
    }

    public function SetSeoDescription($description = "") {
        $GLOBALS["XHB2"]["Seo"]["Description"] = $description;
        return true;
    }

    public function GetSeoDescription() {
        return $GLOBALS["XHB2"]["Seo"]["Description"];
    }

    public function GenerateNum() {
        return md5(uniqid(mt_rand(), true));
    }

    public static function Output($output, $isEncode = true){
        if(is_array($output) || is_object($output)){
            if($isEncode)
                file_put_contents('php://output', base64_encode(json_encode($output)));
            else
                file_put_contents('php://output', json_encode($output));
            return;
        }
        file_put_contents('php://output', $output);
    }
}

class File {

    public static function Instance(){
        return new self();
    }

    public function SetSession($key, $val) {
        $SessionPath = SessionPath();
        if (!is_dir($SessionPath)) {
            mkdir($SessionPath, 0777, true);
        }
        $sessionFile = $SessionPath . $key;
        return file_put_contents($sessionFile, serialize($val));
    }

    public function GetSession($key) {
        $sessionFile = SessionPath() . "/" . $key;
        if (!file_exists($sessionFile)) {
            return null;
        }
        $str = file_get_contents($sessionFile);
        if (!$str) {
            return null;
        }
        return unserialize($str);
    }

    public function WriteStorageLog($message) {
        $logDir = LogPath();
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logPath = $logDir . "/" . date("Ymd") . ".log";
        if (!file_exists($logPath)) {
            touch($logPath);
        }
        return file_put_contents($logPath, $message, FILE_APPEND);
    }

    public function WriteFileCache($key, $data) {
        $logDir = CachePath();
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $cachePath = $logDir . "/" . $key . ".cache";
        if (!file_exists($cachePath)) {
            touch($cachePath);
        }
        return file_put_contents($cachePath, $data);
    }

    public function ReadFileCache($key) {
        $cachePath = CachePath() . "/" . $key . ".cache";
        if (!file_exists($cachePath)) {
            return serialize([]);
        }
        return file_get_contents($cachePath);
    }
}

class Cookie {

    public static function Instance() {
        return new self();
    }

    public function Set($key, $val, $time) {
        return static::create($key, $val, time() + $time);
    }

    public function Get($key) {
        if (isset($_COOKIE[$key]) && $_COOKIE[$key]) {
            return $_COOKIE[$key];
        }
        return null;
    }

    public function Create($key, $val, $time, $path = "/", $domain = "") {
        if ($domain != "") {
            setcookie($key, $val, $time, $path, $domain);
            return true;
        }
        setcookie($key, $val, time() + $time, $path);
        return true;
    }

    public function IsCookie($key) {
        return key_exists($key, $_COOKIE);
    }

}

class SessionContainer {

    private $container = [];

    public function __construct(array $value = []) {
        foreach ($value as $key => $val) {
            $this->container[$key] = $val;
        }
    }

    public function Set($key, $value, $expirationTime = 0) {
        $this->container[$key] = [
            "Value" => $value,
            "Expiration_time" => time() + $expirationTime
        ];
        return true;
    }

    public function Get($key) {
        if (!array_key_exists($key, $this->container)) {
            return null;
        }
        if ($this->container[$key]["ExpirationTime"] <= time()) {
            return null;
        }
        return $this->container[$key]["Value"];
    }

    public function Remove($key) {
        unset($this->container[$key]);
        return true;
    }

    public function All() {
        return $this->container;
    }

}

// $db = new PostgreSQLManager('localhost', 'mydatabase', 'username', 'password');
// try {
//     $db->BeginTransaction();
//     $db->Query("DELETE FROM users WHERE age > :age", ['age' => 100]);
//     $db->Commit();

// } catch (Exception $e) {
//     echo "错误: " . $e->getMessage();
// }
class PostgreSQLManager {
    private $pdo;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port;

    public function __construct(string $host, string $dbname, string $username, string $password, int $port = 5432) {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->Connect();
    }

    private function Connect() {
        try {
            $this->pdo = new \PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};user={$this->username};password={$this->password}",
                null,
                null,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'does not exist') !== false) {
                $this->CreateDatabase();
                $this->Connect(); // 重新连接
            } else {
                throw new \Exception("数据库连接失败: " . $e->getMessage());
            }
        }
    }

    private function CreateDatabase() {
        try {
            $pdo = new \PDO(
                "pgsql:host={$this->host};port={$this->port};dbname=postgres;user={$this->username};password={$this->password}",
                null,
                null,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
            $stmt->execute([$this->dbname]);
            
            if (!$stmt->fetch()) {
                $pdo->exec("CREATE DATABASE \"{$this->dbname}\"");
            }
        } catch (\PDOException $e) {
            throw new \Exception("创建数据库失败: " . $e->getMessage());
        }
    }

    public function Query(string $sql, array $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'does not exist') !== false && strpos($e->getMessage(), 'column') !== false) {
                return $this->Query($sql, $params); 
            } elseif (strpos($e->getMessage(), 'does not exist') !== false && strpos($e->getMessage(), 'relation') !== false) {
                return $this->Query($sql, $params);
            } else {
                throw new \Exception("SQL执行失败: " . $e->getMessage());
            }
        }
    }

    private function CreateEmptyTable(string $table) {
        try {
            $this->pdo->exec("CREATE TABLE \"$table\" (id SERIAL PRIMARY KEY)");
        } catch (\PDOException $e) {
            throw new \Exception("创建表失败: " . $e->getMessage());
        }
    }

    public function FetchRow(string $sql, array $params = []) {
        return $this->Query($sql, $params)->fetch();
    }

    public function FetchAll(string $sql, array $params = []) {
        return $this->Query($sql, $params)->fetchAll();
    }

    public function LastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function BeginTransaction() {
        $this->pdo->beginTransaction();
    }

    public function Commit() {
        $this->pdo->commit();
    }

    public function RollBack() {
        $this->pdo->rollBack();
    }
}

// $db = new MySQLManager('localhost', 'mydatabase', 'username', 'password');
// try {
//     $db->BeginTransaction();
//     $db->Query("DELETE FROM users WHERE age > :age", ['age' => 100]);
//     $db->Commit();

// } catch (Exception $e) {
//     echo "错误: " . $e->getMessage();
// }
class MySQLManager {
    private $pdo;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;

    public function __construct(string $host, string $dbname, string $username, string $password, string $charset = 'utf8mb4') {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;
        $this->Connect();
    }

    private function Connect() {
        try {
            $this->pdo = new \PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}",
                $this->username,
                $this->password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $this->CreateDatabase();
                $this->Connect();
            } else {
                throw new \Exception("数据库连接失败: " . $e->getMessage());
            }
        }
    }

    private function CreateDatabase() {
        try {
            $pdo = new \PDO(
                "mysql:host={$this->host};charset={$this->charset}",
                $this->username,
                $this->password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbname}` DEFAULT CHARACTER SET {$this->charset}");
        } catch (\PDOException $e) {
            throw new \Exception("创建数据库失败: " . $e->getMessage());
        }
    }

    public function Query(string $sql, array $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                return $this->Query($sql, $params);
            } elseif (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                return $this->Query($sql, $params);
            } else {
                throw new \Exception("SQL执行失败: " . $e->getMessage());
            }
        }
    }

    private function CreateEmptyTable(string $table) {
        try {
            $this->pdo->exec("CREATE TABLE `$table` (id INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET={$this->charset}");
        } catch (\PDOException $e) {
            throw new \Exception("创建表失败: " . $e->getMessage());
        }
    }

    public function FetchRow(string $sql, array $params = []) {
        return $this->Query($sql, $params)->fetch();
    }

    public function FetchAll(string $sql, array $params = []) {
        return $this->Query($sql, $params)->fetchAll();
    }

    public function LastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function BeginTransaction() {
        $this->pdo->beginTransaction();
    }

    public function Commit() {
        $this->pdo->commit();
    }

    public function RollBack() {
        $this->pdo->rollBack();
    }
}

// $db = new SQLiteManager('data/mydatabase.db');
// try {
//     $db->Query("INSERT INTO users (name, age) VALUES (:name, :age)", [
//         'name' => '李四',
//         'age' => 30
//     ]);
//     // 查询数据
//     $users = $db->FetchAll("SELECT * FROM users");
//     print_r($users);
// } catch (Exception $e) {
//     echo "错误: " . $e->getMessage();
// }
class SQLiteManager {
    private $db;
    private $dbPath;

    public function __construct(string $dbPath) {
        $this->dbPath = $dbPath;
        $this->Connect();
    }

    private function Connect() {
        try {
            $this->db = new \PDO("sqlite:{$this->dbPath}");
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("数据库连接失败: " . $e->getMessage());
        }
    }

    public function Query(string $sql, array $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'no such column') !== false) {
                return $this->Query($sql, $params);
            }
            throw new \Exception("SQL执行失败: " . $e->getMessage());
        }
    }

    public function Insert(string $table, array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        return $this->Query($sql, $data);
    }

    private function GetTableColumns(string $table): array {
        try {
            $stmt = $this->db->query("PRAGMA table_info($table)");
            $columns = $stmt->fetchAll();
            
            return array_column($columns, 'name');
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'no such table') !== false) {
                $this->CreateEmptyTable($table);
                return [];
            }
            throw $e;
        }
    }

    private function CreateEmptyTable(string $table) {
        try {
            $this->db->exec("CREATE TABLE $table (id INTEGER PRIMARY KEY AUTOINCREMENT)");
        } catch (\PDOException $e) {
            throw new \Exception("创建表失败: " . $e->getMessage());
        }
    }

    public function FetchRow(string $sql, array $params = []) {
        return $this->Query($sql, $params)->fetch();
    }

    public function FetchAll(string $sql, array $params = []) {
        return $this->Query($sql, $params)->fetchAll();
    }

    public function __destruct() {
        $this->db = null;
    }
}

class DatabaseManager {
    private $m_DatabaseManager = null;

    public function __construct(){
        $config = Config("Database");
        if($config["Driver"] == "SQLite"){
            $this->m_DatabaseManager = new SQLiteManager(RootPath()."/App/Database/".$config["SQLite"]["DBName"]);
        }
    }

    public function __destruct(){
        $this->m_DatabaseManager = null;
    }

    public static function Instance(){
        return new self();
    }

    public function Query(string $sql, array $params = []) {
        if($this->m_DatabaseManager != null){
            return $this->m_DatabaseManager->Query($sql, $params);
        }
        return null;
    }

    public function FetchRow(string $sql, array $params = []) {
        if($this->m_DatabaseManager != null){
            return $this->m_DatabaseManager->FetchRow($sql, $params);
        }
        return null;
    }

    public function FetchAll(string $sql, array $params = []) {
        if($this->m_DatabaseManager != null){
            return $this->m_DatabaseManager->FetchAll($sql, $params);
        }
        return null;
    }

    public function Insert(string $table, array $data) {
        if($this->m_DatabaseManager != null){
            return $this->m_DatabaseManager->Insert($table, $data);
        }
        return null;
    }
}

class Session {

    public static function Instance() {
        return new self();
    }

    public function Set($key, $val) {
        return static::create($key, $val);
    }

    public function Get($key) {
        $unserializeStream = $this->UnserializeStream();
        if (!$unserializeStream || !is_object($unserializeStream)) {
            return null;
        }
        return $unserializeStream->Get($key);
    }

    public function All() {
        $unserializeStream = $this->UnserializeStream();
        if (!$unserializeStream || !is_object($unserializeStream)) {
            return null;
        }
        return $unserializeStream->All();
    }

    public function UnserializeStream() {
        $cookieId = \XHB2\Cookie::Instance()->Get("8D389F03329339045B952035E1BFDD96");
        if (!$cookieId) {
            return null;
        }
        return \XHB2\File::Instance()->GetSession($cookieId);
    }

    public function Create($key, $value) {
        $unserializeStream = static::UnserializeStream();
        if (!$unserializeStream || !is_object($unserializeStream)) {
            $unserializeStream = new \XHB2\SessionContainer();
        }
        $unserializeStream->Set($key, $value, $GLOBALS["XHB2"]["SessionValidity"]);
        return \XHB2\File::Instance()->setSession(\XHB2\Session::Instance()->CookieId(), $unserializeStream);
    }

    public function CreateSessionIDVal() {
        return \XHB2\Response::Instance()->GenerateNum();
    }

    public function CookieId() {
        $cookieId = \XHB2\Cookie::Instance()->Get($GLOBALS["XHB2"]["SessionID"]);
        if (!$cookieId) {
            $cookieId = $this->CreateSessionIDVal();
            \XHB2\Cookie::Instance()->Create($GLOBALS["XHB2"]["SessionID"],
            $cookieId,
            Value(function() {
                return time() + (60 * 60 * 24 * 365);
            }),
            $GLOBALS["XHB2"]["CookiePath"], 
            $GLOBALS["XHB2"]["CookieDomain"]);
        }
        return $cookieId;
    }

    public function Remove($key) {
        $unserializeStream = $this->UnserializeStream();
        if (!$unserializeStream || !is_object($unserializeStream)) {
            return true;
        }
        if ($unserializeStream->Remove($key)) {
            return \XHB2\File::Instance()->SetSession($this->CookieId(), $unserializeStream);
        }
        return false;
    }
}

class Application{

    public function Run(){
        set_error_handler([$this, "ErrorHandler"]);
        set_exception_handler([$this, "ExceptionHandler"]);
        $uri = explode("XHB2", $_SERVER["REQUEST_URI"]);
        if(count($uri) != 1)
            return Error("路由参数不正确");
        $requestUri = explode("-", $uri[0]);
        if(count($requestUri) < 4)
            return Error("模块参数有误");
        $requestUri[0] = str_replace("/?","",$requestUri[0]);
        $constLanguage = $requestUri[0];
        $constModule = $requestUri[1];
        $constController = "\\App\\Controller\\{$constModule}\\{$requestUri[2]}Controller";
        $constMethod = $requestUri[3];
        spl_autoload_register(function($className) {
            if (strpos($className, "Model") !== false) {
                $className = str_replace("//", "/", str_replace("\\", "/", preg_replace("/Model$/", "", $className)));
                $filename = RootPath()."/{$className}.Model.php";
                if (file_exists($filename)) 
                    require_once($filename);
            }
            if (strpos($className, "Controller") !== false) {
                $className = str_replace("//", "/", str_replace("\\", "/", preg_replace("/Controller$/", "", $className)));
                $filename = RootPath()."/{$className}.Controller.php";
                if (file_exists($filename)) 
                    require_once($filename);
            }
        });
        try{
            $controller = new $constController();
            if (!is_object($controller)) {
                return Error("不存在的控制器");
            }
            if (!method_exists($controller, $constMethod)) {
                return Error("不存在的控制器方法");
            }
            Response::Instance()->ResetSeoSetting();
            $request = new \XHB2\Request();
            $request->m_Module = $constModule;
            $request->m_Controller = $constController;
            $request->m_Method = $constMethod;
            $request->m_ParamGet = $requestUri;
            $request->m_ParamPost = count($_POST)>0?$_POST:[];
            $request->m_Translate = $constLanguage;
            call_user_func_array([$controller, $constMethod], [$request]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        
    }

    public function ExceptionCatchError() {
        $_error = error_get_last();
        if ($_error && in_array($_error["type"], array(1, 4, 16, 64, 256, 4096, E_ALL))) {
            $conf = $GLOBALS["XHB2"]["config"];
            $message = str_replace("\n", "<br />", $this->ExceptionStringFormat($_error["message"], $_error["line"], $_error["file"]));
            return Error($message);
        }
    }

    public function ExceptionStringFormat($message, $getLine, $getFile, $getTraceAsString = null) {
        $message = "[" . date("Y-m-d H:i:s") . "]抛出异常:" . $message . "\n";
        if ($getLine) {
            $message .= "异常行号 " . $getLine . "\n";
        }
        if ($getFile) {
            $message .= "所在文件 " . $getFile . "\n";
        }
        if ($getTraceAsString) {
            $message .= $getTraceAsString . "\n";
        }
        return str_replace(rootPath(), "", $message);
    }

    function ErrorHandler($errno, $errstr, $errfile, $errline) {
        if (error_reporting() === 0) {
            return false;
        }
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    function ExceptionHandler($exception) {
        http_response_code(500);
        $message = "<br />错误: " . $exception->getMessage() . " 在文件 " . $exception->getFile() . " 第 " . $exception->getLine() . " 行";
        Error($message);
    }

}
?>