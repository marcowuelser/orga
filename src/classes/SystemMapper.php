<?php
declare(strict_types=1);

use \Monolog\Logger as Logger;

require_once("classes/DbMapperAbs.php");

class SystemMapper extends DbMapperAbs
{
    public function __construct(PDO $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->table = "s_system";
        $this->name_single = "system";
        $this->name_multi = "system";
    }

    // Query helpers

    protected function onInsert(array $data) : array
    {
        return array();
    }

    protected function onUpdate(array $data) : array
    {
        $fields = array();
        $this->requireBool("maintenance", $data, $fields);
        return $fields;
    }

    protected function onPatch(array $data) : array
    {
        $fields = array();
        $this->optionalBool("maintenance", $data, $fields);
        return $fields;
    }

    protected function toPublicData(array $data) : array
    {
        unset($data['id']);
        $data['maintenance'] = $data['maintenance'] == 0 ? false : true;
        $data['name'] = Constants::ORGA_SERVER_NAME;
        $data['version'] = Constants::ORGA_SERVER_VERSION;
        return $data;
    }
}
