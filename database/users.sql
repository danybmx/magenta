CREATE TABLE `users` (
`id` int(11) auto_increment primary key,
`rol_id` int(11),
`username` varchar(255),
`password` varchar(255),
`mail` varchar(255),
`name` varchar(255),
`lastname` varchar(255),
`created_at` datetime,
`modified_at` datetime
);