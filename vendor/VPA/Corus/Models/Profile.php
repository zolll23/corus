<?php

namespace VPA\Corus\Models;

class Profile extends \VPA\Model {


    /**
    * Получаем информацию о профиле
    *
    * profileID - ID сотрудника
    * @var int
    *
    * @return array
    **/
    public function getProfile(int $profileID):array
    {
	$query = sprintf("SELECT `id`,`login`,`name`,`last_name`,`offset` FROM `profile` WHERE `id`=%d LIMIT 1",$profileID);
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
	return [];
    }
}