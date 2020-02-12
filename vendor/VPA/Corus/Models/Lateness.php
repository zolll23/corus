<?php

namespace VPA\Corus\Models;

class Lateness extends \VPA\Model {


    /**
    * Помечаем сотрудника, как опоздавшего
    *
    * profileID - ID сотрудника
    * @var int
    *
    * @return int
    **/
    public function markLateness(int $profileID):bool
    {
	// Проверяем, не стоит ли уже опоздание
	$latenessID = $this->getLateness($profileID);
	if ($latenessID) {
	    return false;
	}
	$query = sprintf("INSERT INTO `lateness`(`profile_id`,`date`) VALUES('%d','%s')", $profileID, date('Y-m-d'));
        $result = $this->db->query($query);
	return ($this->db->affected_rows() > 0);
    }

    public function getLateness(int $profileID):int
    {
	$query = sprintf("SELECT `id` FROM `lateness` WHERE `profile_id`=%d AND `date`='%s'",$profileID,date('Y-m-d'));
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }
	return 0;
    }

}