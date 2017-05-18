<?php
namespace joas\taitaja;

class Plugin extends \craft\base\Plugin {
    public function init()
    {
        parent::init();
    }

    public function beforeInstall(): bool {
        /*
        $results = \Craft::$app->db->createCommand()
            ->createTable("{{%visitors}}", [
                "number" => "INT(5) AUTO_INCREMENT",
                "ip" => "CHAR(15)",
                "dateCreated" => "TIMESTAMP",
                "dateUpdated" => "TIMESTAMP",
                "uid" => "CHAR(36)",
                "PRIMARY KEY (number)"
            ])
            ->execute();
        return $results == 0;
        */
        return true;
    }

    public function beforeUninstall(): bool {
        /*
        $results = \Craft::$app->db->createCommand()
            ->dropTable("{{%visitors}}")
            ->execute();
        return $results == 0;
        */
        return true;
    }

    public function defineTemplateComponent()
	{
		return Variable::class;
	}
}