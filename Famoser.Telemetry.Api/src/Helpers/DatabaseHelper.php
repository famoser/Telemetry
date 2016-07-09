<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 15:25
 */

namespace Famoser\MassPass\Helpers;


use Famoser\MassPass\Models\Entities\Application;
use Famoser\MassPass\Models\Entities\Base\BaseEntity;
use Famoser\MassPass\Models\Entities\Event;
use Famoser\MassPass\Models\Entities\Log;
use Famoser\MassPass\Models\Entities\User;
use Interop\Container\ContainerInterface;
use PDO;
use Slim\Container;

class DatabaseHelper
{
    /*
     * @var \PDO
     */
    private $database;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initializeDatabase();
    }

    /**
     * @return \PDO
     */
    private function getConnection()
    {
        return $this->database;
    }

    private function constructPdo($path)
    {
        $pdo = new PDO("sqlite:" . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    private function executeScripts(PDO $connection, $scriptsPath, $executeBefore = null)
    {
        if ($executeBefore != null) {
            $connection->query($executeBefore);
        }
        $files = scandir($scriptsPath);
        foreach ($files as $file) {
            if (substr($file, -3) == "sql") {
                $queries = file_get_contents($scriptsPath . "/" . $file);
                if (strpos($queries, ";") > 0) {
                    $queryArray = explode(";", $queries);
                    foreach ($queryArray as $item) {
                        if (trim($item) != "") {
                            $connection->query($item);
                        }
                    }
                } else {
                    $connection->query($queries);
                }
            }
        }
    }

    private static $activePathKey = 'path';

    public static function setPathKey($newPathKey)
    {
        DatabaseHelper::$activePathKey = $newPathKey;
    }

    public function initializeDatabase()
    {
        $tempFilePath = $this->container["settings"]["data_path"] . "/.db_created";
        if (!file_exists($tempFilePath)) {
            $this->initializeScriptsFolder("/sql/initialize");
            file_put_contents($tempFilePath, time());
        }

        $tempFilePath = $this->container["settings"]["data_path"] . "/.db_updated";
        if (!file_exists($tempFilePath)) {
            $this->initializeScriptsFolder("/sql/applications", "DELETE FROM applications");
            file_put_contents($tempFilePath, time());
        }

        $activePath = $this->container["settings"]["data_path"] . "/" . $this->container['settings']['db'][DatabaseHelper::$activePathKey];
        $this->database = $this->constructPdo($activePath);
        return true;
    }

    private function initializeScriptsFolder($path, $executeBefore = null)
    {
        $testPath = $this->container["settings"]["data_path"] . "/" . $this->container['settings']['db']['test_path'];
        $prodPath = $this->container["settings"]["data_path"] . "/" . $this->container['settings']['db']["path"];

        $scriptsPath = $this->container["settings"]["asset_path"] . $path;

        $this->executeScripts($this->constructPdo($testPath), $scriptsPath, $executeBefore);
        $this->executeScripts($this->constructPdo($prodPath), $scriptsPath, $executeBefore);
    }

    private function createQuery(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null, $limit = 1000)
    {
        $sql = "SELECT * FROM " . $entity->getTableName();
        if ($where != null) {
            $sql .= " WHERE " . $where;
        }
        if ($orderBy != null) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $sql .= " LIMIT " . $limit;
        return $sql;
    }

    private function executeAndFetch(BaseEntity $entity, $sql, $parameters)
    {
        try {
            LogHelper::log($sql . "     " . json_encode($parameters), "DatabaseHelper" . uniqid() . ".txt");
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($parameters)) {
                return false;
            }
            return $request->fetchAll(PDO::FETCH_CLASS, get_class($entity));
        } catch (\Exception $ex) {
            LogHelper::log($ex->getMessage() . "     " . $ex->getTraceAsString() . "     " . $sql . "     " . json_encode($parameters), "DatabaseHelper.txt");
        }
        return null;
    }

    /**
     * @param BaseEntity $entity
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return User[]|Application[]|Event[]|Log[]|bool
     */
    public function getFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null, $limit = 1000)
    {
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, $limit);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        return $res;
    }

    /**
     * @param BaseEntity $entity
     * @param string $property
     * @param mixed[] $values
     * @param bool $invertIn
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return User[]|Application[]|Event[]|Log[]|bool
     */
    public function getWithInFromDatabase(BaseEntity $entity, $property, $values, $invertIn = false, $where = null, $parameters = null, $orderBy = null, $limit = 1000)
    {
        if ($parameters == null)
            $parameters = [];
        if ($where == null)
            $where = " ";
        else
            $where .= " AND ";
        $variables = [];
        for ($i = 0; $i < count($values); $i++) {
            $parameters[":" . $property . $i] = $values[$i];
            $variables[] = ":" . $property . $i;
        }
        $where .= $property . (($invertIn) ? " NOT" : "") . " IN (" . implode(",", $variables) . ")";
        var_dump($where);
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, $limit);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        return $res;
    }

    /**
     * @param BaseEntity $entity
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return Application|Event|Log|User|bool
     */
    public function getSingleFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null, $limit = 1000)
    {
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, $limit);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        if (count($res) > 0)
            return $res[0];
        return null;
    }

    /**
     * @param BaseEntity $entity
     * @return bool
     */
    public function saveToDatabase(BaseEntity $entity)
    {
        $properties = (array)$entity;
        LogHelper::log(json_encode($properties, JSON_PRETTY_PRINT) . "\n\n\n" . json_encode($entity, JSON_PRETTY_PRINT), "DatabaseHelper_" . $entity->getTableName() . '_' . time() . "_" . uniqid() . ".txt");
        unset($properties["id"]);
        if ($entity->id > 0) {
            //update
            $sql = "UPDATE " . $entity->getTableName() . " SET ";
            foreach ($properties as $key => $val) {
                $sql .= $key . "=:" . $key . ",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= " WHERE id=:id";
            $properties = (array)$entity;
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($properties)) {
                return false;
            }

        } else {
            //create
            $sql = "INSERT INTO " . $entity->getTableName() . "(";
            foreach ($properties as $key => $val) {
                $sql .= $key . ",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ") VALUES (";
            foreach ($properties as $key => $val) {
                $sql .= ":" . $key . ",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ")";
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($properties)) {
                return false;
            }
            $entity->id = $this->getConnection()->lastInsertId();
        }
        return true;
    }

    /**
     * @param BaseEntity $entity
     * @return bool
     */
    public function deleteFromDatabase(BaseEntity $entity)
    {
        $sql = "DELETE FROM " . $entity->getTableName() . " WHERE id=:id";
        $params = array("id" => $entity->id);
        $prep = $this->getConnection()->prepare($sql);
        return $prep->execute($params);
    }
}