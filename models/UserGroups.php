<?php
class UserGroups extends ActiveRecord\Model
{
	static $has_one = array(
		array('group',"class_name"=>'Groups','foreign_key'=>'group_id')
	);

	static $belongs_to = array(
		array('user','class_name'=>'Users', 'foreign_key'=>'user_id')
	);
}
