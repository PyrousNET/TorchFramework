-- Create Groups
CREATE TABLE  IF NOT EXISTS groups (
group_id BIGINT NOT NULL AUTO_INCREMENT,
PRIMARY KEY (group_id),
`name` VARCHAR(32));

INSERT INTO groups (`name`) VALUES 
('Dev'),
('Admin'),
('User');

-- Create Group Report Permissions
CREATE TABLE IF NOT EXISTS group_reports (
`group_report_id` BIGINT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(group_report_id),
`group_id` BIGINT NOT NULL,
`report_name` VARCHAR(2048) NOT NULL
);

-- Create table for associating users to groups
CREATE TABLE IF NOT EXISTS user_groups (
`user_group_id` BIGINT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(user_group_id),
`user_id` BIGINT NOT NULL,
`group_id` BIGINT NOT NULL);
