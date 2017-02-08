<?php
class Users extends ActiveRecord\Model
{
	static $has_many = array(
		array('user_groups','class_name'=>'UserGroups','foreign_key'=>'user_id')
	);

}
