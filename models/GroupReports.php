<?php
class GroupReports extends ActiveRecord\Model
{
	static $belongs_to = array(
		array('groups','class_name'=>'Groups','foreign_key'=>'group_id')
	);
}
