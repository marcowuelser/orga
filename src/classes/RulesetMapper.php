<?php

require_once("classes/DbMapperAbs.php");

class RulesetMapper extends DbMapperAbs
{
	public function __construct($app)
	{
		$this->db = $app->db;
		$this->logger = $app->logger;
		$this->table = "r_ruleset";
		$this->name_single = "ruleset";
		$this->name_multi = "rulesets";
	}

	protected function onInsert($data)
	{
		$fields = array();
		$this->requireString("name", $data, $fields);
		$this->requireString("description", $data, $fields);
		return $fields;
	}

	protected function onUpdate($data)
	{
		$fields = array();
		$this->requireString("name", $data, $fields);
		$this->requireString("description", $data, $fields);
		return $fields;
	}

	protected function onPatch($data)
	{
		$fields = array();
		$this->optionalString("name", $data, $fields);
		$this->optionalString("description", $data, $fields);
		return $fields;
	}

	protected function toPublicData($data)
	{
		$id = $data["id"];
		$data["uri"] = "/src/slim_test/src/public/api/v1/ruleset/$id";
		return $data;
	}
}