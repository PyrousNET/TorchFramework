<?php
class Groups extends ActiveRecord\Model
{
	static $table_name = 'groups';

	static $belongs_to = array(
		array('user_groups','class_name'=>'UserGroups','foreign_key'=>'group_id')
	);

	static $has_many = array(
		array('group_reports','class_name'=>'GroupReports','foreign_key'=>'group_id')
	);

}
