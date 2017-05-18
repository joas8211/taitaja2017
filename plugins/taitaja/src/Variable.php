<?php
namespace joas\taitaja;

use Craft;

class Variable {
    
    public function getDatabaseCredentials() {
        $dbhost = '';
        $dbusername = '';
        $dbpassword = '';
        $dbname = '';
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, "MYSQLCONNSTR_localdb") !== 0) {
                continue;
            }
            
            $dbhost = preg_replace("/^.*Data Source=(.+?);.*$/", "\\1", $value);
            $dbusername = preg_replace("/^.*User Id=(.+?);.*$/", "\\1", $value);
            $dbpassword = preg_replace("/^.*Password=(.+?)$/", "\\1", $value);
            $dbname = "taitaja";
        }

        if (empty($dbhost)) $dbhost = getenv('DB_SERVER');
        if (empty($dbusername)) $dbusername = getenv('DB_USER');
        if (empty($dbpassword)) $dbpassword = getenv('DB_PASSWORD');
        if (empty($dbname)) $dbname = getenv('DB_DATABASE');
        
        return (object) [
            "host" => $dbhost,
            "username" => $dbusername,
            "password" => $dbpassword,
            "database" => $dbname
        ];
    }

    public function login($username, $password) {
        if (!empty($password) && !empty($username)) {
            $conf = $this->getDatabaseCredentials();
            $sql = mysqli_connect($conf->host, $conf->username, $conf->password);
            $sql->select_db($conf->database);
            $sql->set_charset("utf8");
            $username_escaped = $sql->real_escape_string($username);
            $result = $sql->query("SELECT password FROM t_users WHERE username='$username_escaped';");
            if ($result->num_rows == 0) {
                return false;
            } else {
                $hash = $result->fetch_array()["password"];
                if (Craft::$app->getSecurity()->validatePassword($password, $hash)) {
                    $_SESSION["user"] = $username;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function loggedIn() {
        return isset($_SESSION["user"]);
    }

    public function user() {
        if ($this->loggedIn()) {
            $conf = $this->getDatabaseCredentials();
            $sql = mysqli_connect($conf->host, $conf->username, $conf->password);
            $sql->select_db($conf->database);
            $sql->set_charset("utf8");
            $username_escaped = $sql->real_escape_string($_SESSION["user"]);
            $result = $sql->query("SELECT * FROM t_users WHERE username='$username_escaped';");
            return $result->fetch_array();
        } else {
            return null;
        }
    }

    public function sports($group = 0) {
        $group = intval($group);
        $conf = $this->getDatabaseCredentials();
        $sql = mysqli_connect($conf->host, $conf->username, $conf->password);
        $sql->select_db($conf->database);
        $sql->set_charset("utf8");
        $result = $sql->query("SELECT * FROM t_sports".($group > 0 ? " WHERE sportgroup='$group'" : "").";");
        $sports = [];
        while ($row = $result->fetch_array()) {
            $sports[$row["id"]] = $row;
        }
        return $sports;
    }

    public function sportGroupPoints() {
        $points = [];
        $entries = $this->latestEntries();
        $sports = $this->sports();
        foreach ($entries as $entry) {
            $groupid = $sports[$entry["sport"]]["sportgroup"];
            if (!isset($points[$groupid]) || $entry["points"] > $points[$groupid]) {
                $points[$groupid] = $entry["points"];
            }
        }
        return $points;
    }

    public function calculatePoints($sport, $value) {
        $T1 = $sport["T1"];
        $a = $sport["a"];
        if ($value <= 0) {
            return 0;
        }
        switch($sport["formula"]) {
            case "1":
                return round(1010 / pow($value / $T1, $a) - 10);
            case "2":
                return round(1010 / pow($T1 / $value, $a) - 10);
            default:
                return 0;
        }
    }

    public function tier() {
        $points = $this->sportGroupPoints();
        if (count($points) < 4) {
            return "?";
        }
        $sum = 0;
        foreach ($points as $points) {
            $sum += $points;
        }
        if ($sum == 0) {
            return 41;
        } else {
            $tier = -$sum/100+41;
            if ($tier <= 1) {
                return 1;
            } else {
                return round($tier, 1);
            }
        }
    }

    public function saveEntries($entriesJSON) {
        $entries = json_decode($entriesJSON);
        $userid = $this->user()["id"];
        $conf = $this->getDatabaseCredentials();
        $sql = mysqli_connect($conf->host, $conf->username, $conf->password);
        $sql->select_db($conf->database);
        $sql->set_charset("utf8");
        $success = true;
        $sports = $this->sports();
        $last = $this->latestEntries();
        foreach ($entries as $sportid => $value) {
            if ($value != "" && intval($value) && (!isset($last[$sportid]) || $last[$sportid]["input"] != $value)) {
                $points = $this->calculatePoints($sports[$sportid], $value);
                if (!$sql->query("INSERT INTO t_entries (user, sport, input, points) VALUES ('$userid', '$sportid', '$value', '$points');")) {
                    return $sql->error;
                    $success = false;
                }
            }
        }
        return $success;
    }

    public function latestEntries() {
        $conf = $this->getDatabaseCredentials();
        $sql = mysqli_connect($conf->host, $conf->username, $conf->password);
        $sql->select_db($conf->database);
        $sql->set_charset("utf8");
        $userid = $this->user()["id"];
        $sports = $this->sports();
        $entries = [];
        foreach ($sports as $sport) {
            $sportid = intval($sport["id"]);
            $result = $sql->query("SELECT * FROM t_entries WHERE sport='$sportid' AND user='$userid' ORDER BY timestamp DESC LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $entry = $result->fetch_array();
                $entries[$entry["sport"]] = $entry;
            }
        }
        
        return $entries;
    }

    public function entries() {
        $conf = $this->getDatabaseCredentials();
        $sql = mysqli_connect($conf->host, $conf->username, $conf->password);
        $sql->select_db($conf->database);
        $sql->set_charset("utf8");
        $userid = $this->user()["id"];
        $result = $sql->query("SELECT t_entries.input as input, t_entries.timestamp as timestamp, t_sports.title as sport, t_sports.sportgroup as sportgroup FROM t_entries INNER JOIN t_sports ON t_sports.id = t_entries.sport WHERE t_entries.user='$userid' ORDER BY t_entries.timestamp DESC");

        return $result->fetch_all(MYSQLI_BOTH);
    }

    public function logout() {
        unset($_SESSION["user"]);
    }
}